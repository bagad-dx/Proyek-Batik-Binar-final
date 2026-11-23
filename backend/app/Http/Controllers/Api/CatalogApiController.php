<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Gallery;
use App\Models\JenisProduk;
use App\Models\Motif;
use App\Models\Order;
use App\Models\Product;
use App\Models\TagGaleri;
use Illuminate\Http\Request;

class CatalogApiController extends Controller
{
    public function products(Request $request)
    {
        // Gabungan filter Raka (belanja.php: ?kategori=<id_jenis> & ?q=nama/deskripsi)
        // + filter PROYEK (category_id). jenis_id = padanan id_jenis Raka.
        $q = Product::with(['category', 'jenis'])->withSum(['orders as terjual' => fn ($q) => $q->whereIn('status', ['diproses', 'dikirim', 'selesai'])], 'jumlah')->where('status', 'aktif')->latest();
        if ($request->filled('search')) {
            $s = $request->search;
            $q->where(function ($w) use ($s) {
                $w->where('nama', 'like', '%' . $s . '%')
                  ->orWhere('deskripsi', 'like', '%' . $s . '%')
                  ->orWhere('motif', 'like', '%' . $s . '%')
                  ->orWhere('bahan', 'like', '%' . $s . '%');
            });
        }
        if ($request->filled('category_id')) $q->where('category_id', $request->category_id);
        if ($request->filled('jenis_id')) {
            $q->where(function ($w) use ($request) {
                $w->where('jenis_id', $request->jenis_id)
                  ->orWhereHas('jenis', fn ($j) => $j->where('id', $request->jenis_id));
            });
        }
        // Alias lama Raka: ?kategori=<id_jenis>
        if ($request->filled('kategori') && ! $request->filled('jenis_id') && ! $request->filled('category_id')) {
            $q->where('jenis_id', $request->kategori);
        }
        // Alias Raka: ?q=...
        if ($request->filled('q') && ! $request->filled('search')) {
            $s = $request->q;
            $q->where(function ($w) use ($s) {
                $w->where('nama', 'like', '%' . $s . '%')
                  ->orWhere('deskripsi', 'like', '%' . $s . '%');
            });
        }
        return response()->json($q->paginate(12));
    }

    /** Daftar jenis produk (tb_jenis_produk Raka) untuk pil filter belanja. */
    public function jenis()
    {
        return response()->json(JenisProduk::withCount(['products as produk_aktif_count' => function ($q) {
            $q->where('status', 'aktif');
        }])->orderBy('id')->get());
    }

    /** Daftar tag galeri (tb_tag_galeri Raka) untuk pil filter galeri. */
    public function tags()
    {
        return response()->json(TagGaleri::orderBy('id')->get());
    }

    public function productDetail($id)
    {
        $product = Product::with(['category', 'jenis'])->withSum(['orders as terjual' => fn ($q) => $q->whereIn('status', ['diproses', 'dikirim', 'selesai'])], 'jumlah')->findOrFail($id);
        return response()->json($product);
    }

    public function galleries(Request $request)
    {
        // Filter tag ala Raka (tb_tag_galeri) + pencarian judul/filosofi.
        $q = Gallery::with('motif')->where('is_published', true)->latest();
        if ($request->filled('tag') && $request->tag !== 'semua') {
            $q->where(function ($w) use ($request) {
                $w->where('tag', $request->tag)->orWhere('kategori', $request->tag);
            });
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $q->where(function ($w) use ($s) {
                $w->where('judul', 'like', '%' . $s . '%')
                  ->orWhere('deskripsi', 'like', '%' . $s . '%')
                  ->orWhere('filosofi', 'like', '%' . $s . '%');
            });
        }
        return response()->json($q->paginate(12));
    }

    public function motifs()
    {
        return response()->json(Motif::where('is_published', true)->latest()->paginate(12));
    }

    // user login required
    // ?scope=aktif (pending/diproses/dikirim) | riwayat (selesai/batal) | semua
    // ?status=pending (filter tunggal, menimpa scope)
    public function myOrders(Request $request)
    {
        Order::expireOverdueFor($request->user()->id);
        $q = Order::with(['product', 'details.product'])->where('user_id', $request->user()->id);
        if ($request->filled('status')) {
            $q->where('status', $request->status);
        } else {
            match ($request->query('scope', 'semua')) {
                'aktif' => $q->whereIn('status', Order::STATUS_AKTIF),
                'riwayat' => $q->whereIn('status', Order::STATUS_RIWAYAT),
                default => null,
            };
        }
        return response()->json($q->latest()->get());
    }

    /** Batalkan pesanan sendiri (hanya pending). */
    public function cancelOrder(Request $request, Order $order)
    {
        if ((int) $order->user_id !== (int) $request->user()->id) {
            return response()->json(['message' => 'Bukan pesanan Anda.'], 403);
        }
        if ($order->status !== 'pending') {
            return response()->json(['message' => 'Hanya pesanan pending yang bisa dibatalkan.'], 422);
        }
        $order->update(['status' => 'batal']);
        if ($order->product) $order->product->increment('stok', $order->jumlah);
        return response()->json(['message' => 'Pesanan dibatalkan.', 'order' => $order->fresh()]);
    }

    public function storeOrder(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|exists:produk,id',
            'jumlah' => 'required|integer|min:1',
            'alamat' => 'required|string|min:10',
            'no_hp' => 'required|string',
            'payment_method' => 'required|in:transfer,cod,ewallet,qris,midtrans',
            'pay_channel' => 'nullable|in:transfer,cod,ewallet,qris,kartu',
            'bank' => 'nullable|in:bca,bni,bri,permata',
            'ewallet' => 'nullable|in:gopay,shopeepay',
            'store' => 'nullable|in:alfamart,indomaret',
            'expedition' => 'required|in:jne,jnt,pos,sicepat,gosend',
            'catatan' => 'nullable|string',
        ]);

        $product = Product::findOrFail($data['product_id']);
        if ($product->stok < $data['jumlah']) {
            return response()->json(['message' => 'Stok tidak mencukupi. Sisa: ' . $product->stok], 422);
        }

        $order = Order::create([
            'user_id' => $request->user()->id,
            'product_id' => $product->id,
            'jumlah' => $data['jumlah'],
            'total_harga' => $product->harga * $data['jumlah'],
            'alamat' => $data['alamat'],
            'no_hp' => $data['no_hp'],
            'payment_method' => 'midtrans',
            'expedition' => $data['expedition'],
            'shipping_cost' => Order::EXPEDITIONS[$data['expedition']]['ongkir'],
            'batas_bayar' => Order::batasBayarDefault(),
            'catatan' => ($data['catatan'] ?? '') . (isset($data['pay_channel']) ? ' [via:' . $data['pay_channel'] . (isset($data['bank']) ? ':' . $data['bank'] : '') . (isset($data['ewallet']) ? ':' . $data['ewallet'] : '') . (isset($data['store']) ? ':' . $data['store'] : '') . ']' : ''),
            'status' => 'pending',
        ]);

        $product->decrement('stok', $data['jumlah']);

        // Baris detail ala tb_detail_pemesanan Raka (1 header -> N item).
        $order->details()->create([
            'product_id' => $product->id,
            'jumlah' => $data['jumlah'],
            'harga_satuan' => $product->harga,
            'subtotal' => $product->harga * $data['jumlah'],
        ]);

        kirim_notifikasi($request->user()->id, 'Pesanan #' . $order->id . ' dibuat',
            $product->nama . ' x' . $data['jumlah'] . ' — silakan selesaikan pembayaran Midtrans.',
            'pesanan', 'pesanan', $order->id);

        $extra = [];
        $channel = $data['pay_channel'] ?? 'qris';
        if ($channel === 'kartu') {
            // kartu butuh form 3DS -> tetap via Snap terfilter
            try {
                $snap = app(\App\Services\MidtransService::class)->snapToken(
                    $order->fresh(),
                    \App\Services\MidtransService::enabledFor('kartu')
                );
                $extra = ['snap_token' => $snap['token'], 'redirect_url' => $snap['redirect_url'], 'client_key' => config('services.midtrans.client_key'), 'is_production' => (bool) config('services.midtrans.is_production')];
            } catch (\RuntimeException $e) {
                $extra = ['midtrans_error' => $e->getMessage()];
            }
        } else {
            try {
                $extra = ['direct_pay' => app(\App\Services\MidtransService::class)->chargeDirect($order->fresh(), $channel, [
                    'bank' => $data['bank'] ?? null,
                    'ewallet' => $data['ewallet'] ?? null,
                    'store' => $data['store'] ?? null,
                ])];
            } catch (\RuntimeException $e) {
                $extra = ['midtrans_error' => $e->getMessage()];
            }
        }

        return response()->json(['message' => 'Pesanan dibuat.', 'order' => $order->fresh()] + $extra, 201);
    }

    /**
     * Sinkronisasi status pembayaran dari Midtrans Status API, lalu kembalikan
     * status terbaru. Dipakai polling halaman pembayaran (localhost tidak bisa
     * menerima webhook). TIDAK PERNAH menandai lunas dari pengakuan pembeli:
     * hanya status resmi Midtrans (settlement / capture+accept) yang mengubah DB.
     */
    public function syncPaymentStatus(Request $request, Order $order, \App\Services\MidtransService $midtrans)
    {
        abort_unless((int) $order->user_id === (int) $request->user()->id, 403);
        $order->load('product');
        if ($order->status !== 'pending' || $order->paid_at) {
            return response()->json([
                'paid' => (bool) $order->paid_at || in_array($order->status, ['diproses', 'dikirim', 'selesai'], true),
                'status' => $order->status,
                'midtrans_status' => $order->midtrans_status,
                'order' => ['id' => $order->id, 'nama' => $order->product->nama ?? '-', 'jumlah' => $order->jumlah, 'total_harga' => $order->total_harga, 'shipping_cost' => $order->shipping_cost, 'grand_total' => $order->grand_total, 'batas_bayar' => $order->batas_bayar, 'paid_at' => $order->paid_at],
            ]);
        }
        $synced = $midtrans->syncStatus($order);
        if (! $synced) {
            return response()->json(['message' => 'Midtrans belum bisa dihubungi, coba lagi.'], 502);
        }
        $synced->load('product');
        return response()->json([
            'paid' => (bool) $synced->paid_at || $synced->status === 'diproses',
            'status' => $synced->status,
            'midtrans_status' => $synced->midtrans_status,
            'order' => ['id' => $synced->id, 'nama' => $synced->product->nama ?? '-', 'jumlah' => $synced->jumlah, 'total_harga' => $synced->total_harga, 'shipping_cost' => $synced->shipping_cost, 'grand_total' => $synced->grand_total, 'batas_bayar' => $synced->batas_bayar, 'paid_at' => $synced->paid_at],
        ]);
    }

    /** Data bayar susulan (kode/QR langsung) untuk pesanan milik sendiri. */
    public function paymentData(Request $request, Order $order, \App\Services\MidtransService $midtrans)
    {
        abort_unless((int) $order->user_id === (int) $request->user()->id, 403);
        $order->load('product');
        // Sinkronisasi dulu ke Midtrans: kalau webhook belum sampai (localhost),
        // status lunas di Midtrans tetap harus terbaca di halaman bayar.
        try {
            $synced = $midtrans->syncStatus($order);
            if ($synced) $order = $synced;
        } catch (\Throwable) {
        }
        if ($order->status !== 'pending' || $order->paid_at) {
            return response()->json([
                'paid' => (bool) $order->paid_at || in_array($order->status, ['diproses', 'dikirim', 'selesai'], true),
                'status' => $order->status,
                'order' => ['id' => $order->id, 'nama' => $order->product->nama ?? '-', 'jumlah' => $order->jumlah, 'batas_bayar' => $order->batas_bayar],
            ]);
        }
        $display = $midtrans->fetchPaymentDisplay($order);
        if (! $display) return response()->json(['message' => 'Data pembayaran belum tersedia.'], 422);
        $display['order'] = [
            'id' => $order->id,
            'nama' => $order->product->nama ?? '-',
            'jumlah' => $order->jumlah,
            'batas_bayar' => $order->batas_bayar,
            'gambar' => $order->product->gambar_url ?? null,
            'total_harga' => $order->total_harga,
            'shipping_cost' => $order->shipping_cost,
            'grand_total' => $order->grand_total,
            'metode_label' => $order->metode_label,
        ];
        if ($order->snap_token) {
            $display['snap_token'] = $order->snap_token;
            $display['client_key'] = config('services.midtrans.client_key');
            $display['is_production'] = (bool) config('services.midtrans.is_production');
        }
        return response()->json($display);
    }

    /** Webhook Midtrans (tanpa auth; diamankan signature_key). */
    public function midtransCallback(Request $request, \App\Services\MidtransService $midtrans)
    {
        try {
            $order = $midtrans->handleNotification($request->all());
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 403);
        }
        return response()->json(['message' => 'OK', 'status' => $order?->status]);
    }
}

