<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChatFaq;
use App\Models\ChatSetting;
use App\Models\Motif;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChatbotController extends Controller
{
    /**
     * POST /api/chat  { message: string }
     * Asisten Batik Binar: menjawab seputar produk, motif, cara beli, ongkir, dll.
     */
    public function chat(Request $request, \App\Services\AiChatService $ai)
    {
        // beri ruang untuk panggilan AI lambat (default PHP 30 dtk = fatal sebelum fallback)
        if ($ai->enabled()) @set_time_limit(90);

        $data = $request->validate([
            'message' => 'required|string|max:500',
            'history' => 'nullable|array|max:20',
            'history.*.role' => 'nullable|string|in:user,bot',
            'history.*.text' => 'nullable|string|max:1000',
        ]);
        $text = mb_strtolower(trim($data['message']));

        if (ChatSetting::get('is_active') !== '1') {
            return response()->json([
                'reply' => 'Maaf, layanan chat sedang nonaktif. Hubungi halo@batikbinar.id atau +62 821-2345-6789.',
                'products' => [],
                'suggestions' => [],
            ]);
        }

        // 0. FAQ kustom dari superadmin (prioritas tertinggi)
        if ($custom = $this->matchCustomFaq($text)) {
            return response()->json([
                'reply' => $custom,
                'products' => [],
                'suggestions' => $this->suggestions($text),
            ]);
        }

        // 1. AI (Gemini) bila diaktifkan; gagal -> turun ke aturan bawaan
        if ($ai->enabled()) {
            $history = collect($data['history'] ?? [])->map(fn ($h) => [
                'role' => $h['role'] ?? 'user',
                'text' => trim(strip_tags($h['text'] ?? '')),
            ])->filter(fn ($h) => $h['text'] !== '')->values()->all();
            if ($reply = $ai->reply($text, $history)) {
                return response()->json([
                    'reply' => $reply,
                    'products' => $this->cardsFor($text),
                    'suggestions' => $this->suggestions($text),
                ]);
            }
        }

        [$reply, $products] = $this->answer($text);

        return response()->json([
            'reply' => $reply,
            'products' => $products,
            'suggestions' => $this->suggestions($text),
        ]);
    }

    /** Kartu produk pendamping jawaban AI untuk topik belanja. */
    protected function cardsFor(string $text): array
    {
        if (str_contains($text, 'best seller') || str_contains($text, 'terlaris') || str_contains($text, 'rekomendasi') || str_contains($text, 'favorit') || str_contains($text, 'populer')) {
            return $this->productCards($this->bestSellers(3));
        }
        if (str_contains($text, 'katalog') || str_contains($text, 'harga') || str_contains($text, 'produk') || str_contains($text, 'koleksi')) {
            return $this->productCards(Product::where('status', 'aktif')->latest()->take(3)->get());
        }
        return [];
    }

    /** Cari jawaban FAQ kustom: cocok jika SEMUA kata dari salah satu keyword muncul. */
    protected function matchCustomFaq(string $text): ?string
    {
        $faqs = ChatFaq::where('is_active', true)->orderBy('urutan')->orderBy('id')->get();
        foreach ($faqs as $faq) {
            foreach ($faq->keywordList() as $keyword) {
                $words = preg_split('/\s+/', $keyword, -1, PREG_SPLIT_NO_EMPTY);
                $match = collect($words)->every(fn ($w) => str_contains($text, $w));
                if ($match && $keyword !== '') return $faq->jawaban;
            }
        }
        return null;
    }

    /** GET /api/chat/config — nama, sapaan & status bot untuk widget. */
    public function config()
    {
        return response()->json([
            'bot_name' => ChatSetting::get('bot_name'),
            'greeting' => ChatSetting::get('greeting'),
            'is_active' => ChatSetting::get('is_active') === '1',
        ]);
    }

    public function suggestions(?string $text = null): array    {
        return [
            'Best seller apa?',
            'Lihat motif batik',
            'Cara beli',
            'Biaya ongkir',
            'Metode pembayaran',
        ];
    }

    /** @return array{0:string, 1:array} */
    protected function answer(string $text): array
    {
        $has = fn (array $keys) => collect($keys)->contains(fn ($k) => str_contains($text, $k));

        // 1. Salam
        if ($has(['halo', 'hallo', 'hai', 'hi ', ' hi', 'pagi', 'siang', 'sore', 'malam', 'assalamu', 'permisi', 'kak', 'min '])) {
            return ['Halo! Saya <b>Binar</b>, asisten Batik Binar. Saya bisa bantu info <b>best seller</b>, motif & filosofinya, harga, cara beli, ongkir, dan pembayaran. Mau tanya apa dulu?', []];
        }

        // 2. Best seller / terlaris
        if ($has(['best seller', 'bestseller', 'terlaris', 'paling laku', 'paling laris', 'rekomendasi', 'favorit', 'populer'])) {
            $top = $this->bestSellers(3);
            if ($top->isEmpty()) {
                return ['Katalog kami sedang disiapkan admin. Silakan cek halaman <b>Belanja</b> berkala, ya!', []];
            }
            $list = $top->map(fn ($p, $i) => ($i + 1) . '. <b>' . e($p->nama) . '</b> — Rp ' . number_format($p->harga, 0, ',', '.'))->implode('<br>');
            return ["Koleksi <b>best seller</b> Batik Binar saat ini:<br>{$list}<br><br>Klik kartunya untuk detail & checkout!", $this->productCards($top)];
        }

        // 3. Motif tertentu / filosofi (dicek awal agar tidak tertelan intent katalog)
        $motif = Motif::where('is_published', true)->get()->first(fn ($m) => str_contains($text, mb_strtolower($m->nama)));
        if ($motif) {
            return ["Motif <b>{$motif->nama}</b> ({$motif->asal}): {$motif->filosofi} Teknik <b>{$motif->teknik}</b>, lama pengerjaan {$motif->lama_pengerjaan}.", []];
        }
        if ($has(['motif', 'filosofi', 'makna', 'iwak', 'etong', 'kapal', 'merak', 'corak'])) {
            $motifs = Motif::where('is_published', true)->take(5)->get();
            $list = $motifs->map(fn ($m) => '• <b>' . e($m->nama) . '</b> — ' . e(\Illuminate\Support\Str::limit($m->filosofi, 80)))->implode('<br>');
            return [$motifs->isEmpty() ? 'Motif ikonik kami antara lain <b>Iwak Etong</b>, <b>Kapal Kandas</b>, dan <b>Merak Ngibing</b>.' : "Motif ikonik Paoman:<br>{$list}<br><br>Tanya saya tentang salah satunya, misal: <i>filosofi iwak etong</i>", []];
        }

        // 4. Ongkir / ekspedisi (dicek sebelum katalog agar kata "berapa" tidak nyangkut)
        if ($has(['ongkir', 'ongkos kirim', 'ekspedisi', 'pengiriman', 'kurir', 'jne', 'j&t', 'jnt', 'pos ', 'sicepat', 'gosend', 'kirim'])) {
            $list = collect(Order::EXPEDITIONS)->map(fn ($e, $k) => '• <b>' . $e['label'] . '</b> — Rp ' . number_format($e['ongkir'], 0, ',', '.'))->implode('<br>');
            return ["Ongkir flat ke seluruh Indonesia:<br>{$list}<br><br>Total bayar = harga × jumlah + ongkir.", []];
        }

        // 5. Pembayaran
        if ($has(['bayar', 'pembayaran', 'payment', 'transfer', 'cod', 'qris', 'e-wallet', 'ewallet', 'dana', 'ovo', 'gopay'])) {
            $list = collect(Order::PAYMENTS)->map(fn ($l) => '• <b>' . $l . '</b>')->implode('<br>');
            return ["Metode pembayaran yang tersedia:<br>{$list}", []];
        }

        // 6. Daftar produk / harga / katalog
        if ($has(['katalog', 'daftar produk', 'semua produk', 'harga', 'jual apa', 'produk apa'])) {
            $products = Product::where('status', 'aktif')->latest()->take(5)->get();
            if ($products->isEmpty()) return ['Belum ada produk aktif. Coba lagi nanti, ya!', []];
            $list = $products->map(fn ($p) => '• <b>' . e($p->nama) . '</b> — Rp ' . number_format($p->harga, 0, ',', '.') . ' (stok ' . $p->stok . ')')->implode('<br>');
            return ["Ini koleksi kami:<br>{$list}<br><br>Belanja wajib login dulu ya, lalu checkout di halaman <b>Belanja</b>!", $this->productCards($products->take(3))];
        }

        // 7. Complongan / teknik / proses
        if ($has(['complong', 'teknik', 'proses', 'cara buat', 'cara membuat', 'jarum', 'canting', 'tulis', 'pewarna', 'alami'])) {
            return ['Keunikan kami adalah teknik <b>Complongan</b> khas Indramayu: ribuan tusukan jarum mikro di atas kain bermalam yang menciptakan efek rintik <i>cecek</i> magis. Tahapannya: Molani → Nyanting tulis → Complongan → Pewarnaan alami → Nglorod → Kurasi & sertifikasi. Butuh 40–60 hari per kain!', []];
        }

        // 9. Cara beli / checkout / order
        if ($has(['cara beli', 'cara order', 'cara memesan', 'cara pesan', 'checkout', 'beli', 'order', 'pesan', 'keranjang', 'transaksi'])) {
            $shopee = ChatSetting::get('shopee_store_url');
            $extra = $shopee ? "<br><b>Opsi 2 — Shopee:</b> bisa juga checkout lewat toko Shopee seller kami: {$shopee}" : '';
            return ['Cara beli gampang (wajib <b>login</b> dulu):<br><b>Opsi 1 — Website:</b> halaman <b>Belanja</b> → pilih produk → isi alamat & ekspedisi → <b>Bayar via Midtrans</b> langsung (VA Bank / QRIS / E-Wallet) → pantau di <b>Pesanan Saya</b>.' . $extra, []];
        }

        // 9b. Shopee
        if ($has(['shopee', 'marketplace', 'toko online', 'toko oren'])) {
            $shopee = ChatSetting::get('shopee_store_url');
            return [$shopee ? "Kami juga jualan di <b>Shopee</b>! Kunjungi toko seller kami: {$shopee}. Di halaman detail produk ada tombol <b>Beli via Shopee Seller</b>." : 'Toko Shopee kami segera hadir. Sementara belanja lewat website ya!', []];
        }

        // 10. Status / lacak pesanan
        if ($has(['status', 'lacak', 'tracking', 'pesanan saya', 'pesananku', 'sampai', 'kapan datang', 'diproses'])) {
            return ['Untuk melacak pesanan, <b>login</b> lalu buka <b>Pesanan Saya</b>. Statusnya berurutan: <i>pending → diproses → dikirim → selesai</i>. Ada kendala? Hubungi halo@batikbinar.id', []];
        }

        // 10. Stok
        if ($has(['stok', 'stoknya', 'tersedia', 'ready', 'habis', 'kosong'])) {
            return ['Stok tertera di tiap kartu produk dan halaman detail. Kalau habis, admin akan restock — pantau halaman <b>Belanja</b> ya!', []];
        }

        // 11. Bahan / kualitas / sertifikat / asli
        if ($has(['bahan', 'kain', 'katun', 'sutra', 'kualitas', 'asli', 'original', 'sertifikat', 'awet', 'luntur', 'cuci', 'rawat'])) {
            return ['Kami memakai <b>katun primissima</b> dan <b>sutra ATBM</b> dengan pewarna ramah lingkungan. Setiap kain tulis lolos <b>kurasi & sertifikat keaslian Paoman</b>. Perawatan: cuci tangan dengan lerak/shampo, jemur di tempat teduh.', []];
        }

        // 12. Lokasi / kontak / jam
        if ($has(['lokasi', 'alamat toko', 'dimana', 'di mana', 'toko', 'kontak', 'hubungi', 'telepon', 'email', 'jam', 'buka', 'instagram', 'sosmed'])) {
            return ['Sentra Batik Paoman, Jl. Siliwangi No. 45, Indramayu, Jawa Barat.<br>Email: halo@batikbinar.id | WA: +62 821-2345-6789.<br>Buka Senin–Sabtu 09.00–17.00 WIB.', []];
        }

        // 13. Harga spesifik produk? (nama produk disebut)
        $product = Product::where('status', 'aktif')->get()->first(fn ($p) => mb_strlen($p->nama) > 3 && str_contains($text, mb_strtolower($p->nama)));
        if ($product) {
            return ["<b>{$product->nama}</b>: Rp " . number_format($product->harga, 0, ',', '.') . ", stok {$product->stok}, motif {$product->motif}, bahan {$product->bahan}.", $this->productCards(collect([$product]))];
        }

        // 14. Terima kasih / dadah
        if ($has(['terima kasih', 'makasih', 'thanks', 'thank you', 'dadah', 'bye', 'sampai jumpa'])) {
            return ['Sama-sama! Senang bisa membantu melestarikan batik Indramayu. Sampai jumpa!', []];
        }

        // 15. Login / daftar
        if ($has(['login', 'masuk', 'daftar', 'register', 'akun', 'lupa password'])) {
            return ['Belanja wajib <b>login</b> dulu. Belum punya akun? <b>Daftar</b> gratis — cukup nama, email, dan no HP. Khusus pengelola ada gerbang <b>Login Admin</b> dan <b>Login Superadmin</b> terpisah.', []];
        }

        // Fallback
        return ['Hmm, saya kurang paham. Saya spesialis info <b>batik</b>: best seller, motif & filosofi, harga, cara beli, ongkir, pembayaran, dan status pesanan. Coba salah satu saran di bawah ya!', []];
    }

    protected function bestSellers(int $limit = 3)
    {
        $ids = Order::select('product_id', DB::raw('SUM(jumlah) as terjual'))
            ->groupBy('product_id')->orderByDesc('terjual')->limit($limit)->pluck('product_id');
        $top = Product::whereIn('id', $ids)->where('status', 'aktif')->get()->sortBy(fn ($p) => array_search($p->id, $ids->all()));
        if ($top->count() < $limit) {
            $fill = Product::where('status', 'aktif')->whereNotIn('id', $top->pluck('id'))
                ->latest()->take($limit - $top->count())->get();
            $top = $top->concat($fill);
        }
        return $top->values();
    }

    protected function productCards($products): array
    {
        return $products->map(fn ($p) => [
            'nama' => $p->nama,
            'harga' => 'Rp ' . number_format($p->harga, 0, ',', '.'),
            'stok' => $p->stok,
            'gambar' => $p->gambar ? (str_starts_with($p->gambar, 'http') ? $p->gambar : asset('storage/' . $p->gambar)) : null,
            'url' => route('shop.show', $p),
        ])->values()->all();
    }
}
