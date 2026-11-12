<?php

namespace App\Services;

use App\Models\ChatSetting;
use App\Models\Motif;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\Http;

/**
 * Chat AI via Gemini REST API (tanpa SDK).
 * Gagal/mati -> panggil rule-based lama di controller.
 */
class AiChatService
{
    public function enabled(): bool
    {
        return ChatSetting::get('ai_provider') === 'gemini' && ChatSetting::get('ai_api_key') !== '';
    }

    /** @param array{role:string,text:string}[] $history */
    public function reply(string $message, array $history = []): ?string
    {
        $key = ChatSetting::get('ai_api_key');
        $model = ChatSetting::get('ai_model') ?: 'gemini-2.0-flash';
        if (! $key) return null;

        $contents = [];
        foreach (array_slice($history, -10) as $h) {
            $contents[] = [
                'role' => ($h['role'] ?? 'user') === 'bot' ? 'model' : 'user',
                'parts' => [['text' => mb_substr($h['text'] ?? '', 0, 1000)]],
            ];
        }
        $contents[] = ['role' => 'user', 'parts' => [['text' => mb_substr($message, 0, 1000)]]];

        try {
            $res = Http::timeout(60)->connectTimeout(15)->post(
                "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key=" . urlencode($key),
                [
                    'system_instruction' => ['parts' => [['text' => $this->systemPrompt()]]],
                    'contents' => $contents,
                    'generationConfig' => ['maxOutputTokens' => 500, 'temperature' => 0.7],
                ]
            );
            if (! $res->successful()) {
                \Illuminate\Support\Facades\Log::warning('Gemini gagal: HTTP ' . $res->status() . ' ' . substr($res->body(), 0, 200));
                return null;
            }
            $text = $res->json('candidates.0.content.parts.0.text');
            return $text ? $this->toHtml($text) : null;
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Gemini error: ' . $e->getMessage());
            return null;
        }
    }

    protected function systemPrompt(): string
    {
        $bot = ChatSetting::get('bot_name') ?: 'Binar';
        $products = Product::where('status', 'aktif')->latest()->take(15)->get()
            ->map(fn ($p) => "- {$p->nama} (Rp " . number_format($p->harga, 0, ',', '.') . ", stok {$p->stok}, motif {$p->motif}, bahan {$p->bahan})")->implode("\n");
        $motifs = Motif::where('is_published', true)->take(20)->get()
            ->map(fn ($m) => "- {$m->nama}: {$m->filosofi}")->implode("\n");
        $ongkir = collect(Order::EXPEDITIONS)->map(fn ($e) => "- {$e['label']}: Rp " . number_format($e['ongkir'], 0, ',', '.'))->implode("\n");
        $bayar = implode(', ', Order::PAYMENTS);
        $shopee = ChatSetting::get('shopee_store_url');

        return <<<PROMPT
Kamu adalah {$bot}, asisten ramah toko batik "Batik Binar" (Indramayu). Jawab SELALU bahasa Indonesia, singkat (maks 5 kalimat), hangat.
Aturan belanja: wajib login dulu; 2 opsi: (1) checkout di website (isi alamat, ekspedisi, pembayaran, pantau di Pesanan Saya), (2) Shopee seller: {$shopee}.
JANGAN mengarang harga/stok/motif di luar data berikut. Jika ditanya di luar batik/toko, tolak sopan dan arahkan ke topik batik.

PRODUK:
{$products}

MOTIF & FILOSOFI:
{$motifs}

ONGKIR FLAT:
{$ongkir}

PEMBAYARAN: {$bayar}

TOKO: Sentra Batik Paoman, Jl. Siliwangi No. 45, Indramayu. Email halo@batikbinar.id, WA +62 821-2345-6789. Buka Senin-Sabtu 09.00-17.00.
Teknik khas: Complongan (tusukan jarum mikro khas Indramayu). Status pesanan: pending -> diproses -> dikirim -> selesai.
PROMPT;
    }

    protected function toHtml(string $text): string
    {
        $text = e(trim($text));
        $text = preg_replace('/\*\*(.+?)\*\*/s', '<b>$1</b>', $text);
        $text = preg_replace('/^#{1,4}\s*/m', '', $text);
        $text = preg_replace('/^\s*[-*]\s+/m', '• ', $text);
        return nl2br($text, false);
    }
}
