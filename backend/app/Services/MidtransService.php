<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Pembayaran Midtrans Snap (VA Bank, QRIS, E-Wallet, kartu).
 * Butuh MIDTRANS_SERVER_KEY (+ CLIENT_KEY di frontend).
 */
class MidtransService
{
    public function configured(): bool
    {
        return config('services.midtrans.server_key') !== '' && config('services.midtrans.server_key') !== null;
    }

    protected function boot(): void
    {
        \Midtrans\Config::$serverKey = config('services.midtrans.server_key');
        \Midtrans\Config::$isProduction = (bool) config('services.midtrans.is_production', false);
        \Midtrans\Config::$isSanitized = true;
        \Midtrans\Config::$is3ds = true;
    }

    /** Referensi unik per pesanan, cth: BINAR-12-1727... */
    public static function refFor(Order $order): string
    {
        return 'BINAR-' . $order->id . '-' . $order->created_at->format('YmdHis');
    }

    /** Pilihan tombol -> filter metode Snap. COD = tunai di gerai (Midtrans tak punya COD murni). */
    public const CHANNELS = [
        'transfer' => ['bank_transfer'],
        'cod' => ['indomaret', 'alfamart'],
        'ewallet' => ['gopay', 'shopeepay', 'dana'],
        'qris' => ['qris'],
        'kartu' => ['credit_card'],
    ];

    public static function enabledFor(string $channel): array
    {
        return self::CHANNELS[$channel] ?? [];
    }

    protected function baseUrl(): string
    {
        return config('services.midtrans.is_production')
            ? 'https://api.midtrans.com'
            : 'https://api.sandbox.midtrans.com';
    }

    protected function coreHeaders(): array
    {
        return [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Authorization' => 'Basic ' . base64_encode(config('services.midtrans.server_key') . ':'),
        ];
    }

    /** Charge langsung (Core API) -> kode/QR tampil seketika, tanpa popup Snap. */
    public function chargeDirect(Order $order, string $channel, array $opts = []): array
    {
        if (! $this->configured()) throw new \RuntimeException('Midtrans belum dikonfigurasi (MIDTRANS_SERVER_KEY kosong).');
        $order->loadMissing(['user', 'product']);
        $gross = (int) round($order->grand_total);
        $ref = static::refFor($order);

        $body = [
            'payment_type' => 'qris',
            'transaction_details' => ['order_id' => $ref, 'gross_amount' => $gross],
            'item_details' => [[
                'id' => 'P' . $order->product_id,
                'price' => (int) round($order->total_harga / max($order->jumlah, 1)),
                'quantity' => (int) $order->jumlah,
                'name' => mb_substr($order->product->nama ?? 'Produk Batik', 0, 50),
            ], [
                'id' => 'ONGKIR',
                'price' => (int) round($order->shipping_cost),
                'quantity' => 1,
                'name' => 'Ongkos kirim ' . $order->expedition_label,
            ]],
            'customer_details' => [
                'first_name' => mb_substr($order->user->name ?? 'Pembeli', 0, 50),
                'email' => $order->user->email ?? '',
                'phone' => $order->no_hp ?? '',
            ],
            'custom_expiry' => [
                'order_time' => now()->format('Y-m-d H:i:s O'),
                'expiry_duration' => 24,
                'unit' => 'hour',
            ],
        ];

        switch ($channel) {
            case 'transfer':
                $bank = strtolower($opts['bank'] ?? 'bca');
                if (! in_array($bank, ['bca', 'bni', 'bri', 'permata'], true)) $bank = 'bca';
                $body['payment_type'] = 'bank_transfer';
                $body['bank_transfer'] = ['bank' => $bank];
                break;
            case 'ewallet':
                $ew = strtolower($opts['ewallet'] ?? 'gopay');
                $body['payment_type'] = in_array($ew, ['gopay', 'shopeepay'], true) ? $ew : 'gopay';
                break;
            case 'qris':
                $body['payment_type'] = 'qris';
                break;
            case 'cod':
                $store = strtolower($opts['store'] ?? 'alfamart');
                $body['payment_type'] = 'cstore';
                $body['cstore'] = [
                    'store' => in_array($store, ['alfamart', 'indomaret'], true) ? $store : 'alfamart',
                    'message' => 'Batik Binar #' . $order->id,
                ];
                break;
            case 'kartu':
                // kartu butuh tokenisasi 3DS di browser -> tetap lewat Snap
                throw new \RuntimeException('NEED_SNAP');
            default:
                throw new \RuntimeException('Metode tidak dikenal.');
        }

        try {
            $res = Http::timeout(30)->withHeaders($this->coreHeaders())->post($this->baseUrl() . '/v2/charge', $body);
        } catch (\Throwable $e) {
            Log::error('Midtrans charge gagal: ' . $e->getMessage());
            throw new \RuntimeException('Gagal menghubungi Midtrans.');
        }
        if ($res->status() === 406) throw new \RuntimeException('Transaksi ini sudah pernah dibuat. Buka ulang dari riwayat.');
        if (! $res->successful()) {
            Log::error('Midtrans charge ditolak: ' . substr($res->body(), 0, 300));
            throw new \RuntimeException('Midtrans menolak: ' . ($res->json('status_message') ?? 'periksa konfigurasi'));
        }

        $api = $res->json();
        $order->update([
            'payment_ref' => $ref,
            'midtrans_status' => $api['transaction_status'] ?? 'pending',
            'paid_at' => in_array($api['transaction_status'] ?? '', ['settlement', 'capture'], true) ? now() : null,
            'status' => in_array($api['transaction_status'] ?? '', ['settlement', 'capture'], true) ? 'diproses' : 'pending',
        ]);

        $display = $this->normalizeDisplay($api, $order->fresh());
        $order->update(['payment_data' => $display]);
        return $display;
    }

    /** Ambil ulang data bayar transaksi yg sudah ada (untuk bayar susulan). */
    public function fetchPaymentDisplay(Order $order): ?array
    {
        if (! $this->configured() || ! $order->payment_ref) return null;
        try {
            $res = Http::timeout(20)->withHeaders($this->coreHeaders())
                ->get($this->baseUrl() . '/v2/' . $order->payment_ref . '/status');
            if (! $res->successful()) return $order->payment_data ?: null;
            $api = $res->json();
            $trx = $api['transaction_status'] ?? 'pending';
            if (in_array($trx, ['settlement', 'capture'], true)) return null; // sudah lunas
            if (in_array($trx, ['deny', 'cancel', 'expire'], true)) return null; // kedaluwarsa
            // status API tak selalu membawa QR/VA -> pakai data tersimpan
            return $order->payment_data ?: $this->normalizeDisplay($api, $order);
        } catch (\Throwable) {
            return $order->payment_data ?: null;
        }
    }

    /** Normalisasi respons Midtrans -> tampilan seragam. */
    protected function normalizeDisplay(array $api, Order $order): array
    {
        $gross = (int) round($order->grand_total);
        $base = ['gross' => $gross, 'order_ref' => $order->payment_ref, 'expires' => $api['expiry_time'] ?? null];
        $actions = collect($api['actions'] ?? []);
        $qr = $actions->firstWhere('name', 'generate-qr-code')['url']
            ?? $actions->firstWhere('name', 'qr-code')['url'] ?? null;

        if (! empty($api['qr_string']) || $qr) {
            return $base + ['kind' => 'qr', 'title' => 'Scan QR untuk Bayar', 'qr_url' => $qr, 'qr_string' => $api['qr_string'] ?? null];
        }
        if (! empty($api['va_numbers'][0]['va_number'])) {
            $v = $api['va_numbers'][0];
            return $base + ['kind' => 'va', 'title' => 'Virtual Account ' . strtoupper($v['bank'] ?? ''), 'bank' => strtoupper($v['bank'] ?? ''), 'va_number' => $v['va_number']];
        }
        if (! empty($api['permata_va_number'])) {
            return $base + ['kind' => 'va', 'title' => 'Virtual Account Permata', 'bank' => 'PERMATA', 'va_number' => $api['permata_va_number']];
        }
        if (! empty($api['payment_code'])) {
            return $base + ['kind' => 'code', 'title' => 'Kode Bayar ' . ucfirst($api['store'] ?? 'Gerai'), 'store' => $api['store'] ?? '', 'payment_code' => $api['payment_code']];
        }
        if (! empty($api['redirect_url'])) {
            return $base + ['kind' => 'redirect', 'title' => 'Lanjutkan Pembayaran', 'redirect_url' => $api['redirect_url']];
        }
        return $base + ['kind' => 'info', 'title' => 'Menunggu Pembayaran', 'status' => $api['transaction_status'] ?? 'pending'];
    }

    /**
     * @param string[] $enabledPayments filter metode Snap (cth: ['qris'])
     * @return array{token:string, redirect_url:string}
     *
     * @throws \RuntimeException
     */
    public function snapToken(Order $order, array $enabledPayments = []): array
    {
        if (! $this->configured()) throw new \RuntimeException('Midtrans belum dikonfigurasi (MIDTRANS_SERVER_KEY kosong).');
        $this->boot();

        $order->loadMissing(['user', 'product']);
        $gross = (int) round($order->grand_total);
        $params = [
            'transaction_details' => [
                'order_id' => static::refFor($order),
                'gross_amount' => $gross,
            ],
            'item_details' => [[
                'id' => 'P' . $order->product_id,
                'price' => (int) round($order->total_harga / max($order->jumlah, 1)),
                'quantity' => (int) $order->jumlah,
                'name' => mb_substr($order->product->nama ?? 'Produk Batik', 0, 50),
            ], [
                'id' => 'ONGKIR',
                'price' => (int) round($order->shipping_cost),
                'quantity' => 1,
                'name' => 'Ongkos kirim ' . $order->expedition_label,
            ]],
            'customer_details' => [
                'first_name' => mb_substr($order->user->name ?? 'Pembeli', 0, 50),
                'email' => $order->user->email ?? '',
                'phone' => $order->no_hp ?? '',
                'shipping_address' => [
                    'address' => mb_substr($order->alamat ?? '', 0, 200),
                    'country_code' => 'IDN',
                ],
            ],
        ];
        if (! empty($enabledPayments)) {
            $params['enabled_payments'] = array_values($enabledPayments);
        }
        // samakan kedaluwarsa Snap dengan deadline toko (1 hari)
        $params['expiry'] = [
            'start_time' => now()->format('Y-m-d H:i:s O'),
            'unit' => 'hour',
            'duration' => 24,
        ];

        try {
            $snap = \Midtrans\Snap::getSnapToken($params);
        } catch (\Exception $e) {
            Log::error('Midtrans snap gagal: ' . $e->getMessage());
            throw new \RuntimeException('Gagal membuat pembayaran Midtrans: ' . $e->getMessage());
        }

        $order->update([
            'payment_ref' => $params['transaction_details']['order_id'],
            'snap_token' => $snap,
            'midtrans_status' => 'pending',
        ]);

        return [
            'token' => $snap,
            'redirect_url' => "https://app." . (config('services.midtrans.is_production') ? '' : 'sandbox.') . "midtrans.com/snap/v2/vtweb/{$snap}",
        ];
    }

    /**
     * Proses notifikasi webhook Midtrans. Return Order atau null bila diabaikan.
     *
     * @throws \RuntimeException bila signature salah
     */
    public function handleNotification(array $payload): ?Order
    {
        $serverKey = config('services.midtrans.server_key');
        $expect = hash('sha512', ($payload['order_id'] ?? '') . ($payload['status_code'] ?? '') . ($payload['gross_amount'] ?? '') . $serverKey);
        if (! hash_equals($expect, $payload['signature_key'] ?? '')) {
            throw new \RuntimeException('Signature Midtrans tidak valid.');
        }

        $order = Order::where('payment_ref', $payload['order_id'] ?? '')->first();
        if (! $order || $order->status !== 'pending') return $order;

        $trx = $payload['transaction_status'] ?? '';
        $fraud = $payload['fraud_status'] ?? 'accept';

        if ($trx === 'settlement' || ($trx === 'capture' && $fraud === 'accept')) {
            $order->update(['midtrans_status' => $trx, 'status' => 'diproses', 'paid_at' => now()]);
            kirim_notifikasi($order->user_id, 'Pembayaran #' . $order->id . ' lunas',
                'Terima kasih! Pesananmu sedang diproses admin.', 'pembayaran', 'pesanan', $order->id);
        } elseif (in_array($trx, ['deny', 'cancel', 'expire'], true)) {
            $order->update(['midtrans_status' => $trx, 'status' => 'batal']);
            if ($order->product) $order->product->increment('stok', $order->jumlah);
            kirim_notifikasi($order->user_id, 'Pembayaran #' . $order->id . ' gagal/kedaluwarsa',
                'Pesanan dibatalkan otomatis dan stok dikembalikan.', 'pembayaran', 'pesanan', $order->id);
        } else {
            $order->update(['midtrans_status' => $trx]);
        }
        return $order;
    }
}
