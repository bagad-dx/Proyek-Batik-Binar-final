<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $table = 'pesanan';
    protected $fillable = [
        'user_id', 'product_id', 'jumlah', 'total_harga',
        'alamat', 'no_hp', 'payment_method', 'payment_ref', 'midtrans_status',
        'snap_token', 'payment_data', 'paid_at', 'batas_bayar', 'expedition',
        'shipping_cost', 'catatan', 'status',
    ];

    protected $casts = ['total_harga' => 'decimal:2', 'shipping_cost' => 'decimal:2', 'paid_at' => 'datetime', 'batas_bayar' => 'datetime', 'payment_data' => 'array'];

    public const PAYMENTS = [
        'transfer' => 'Transfer Bank',
        'cod' => 'COD (Bayar di Tempat)',
        'ewallet' => 'E-Wallet (DANA/OVO/GoPay)',
        'qris' => 'QRIS',
        'midtrans' => 'Midtrans (VA Bank / QRIS / E-Wallet)',
    ];

    public const STATUS_AKTIF = ['pending', 'diproses', 'dikirim'];
    public const STATUS_RIWAYAT = ['selesai', 'batal'];

    public const EXPEDITIONS = [
        'jne' => ['label' => 'JNE Reguler', 'ongkir' => 15000],
        'jnt' => ['label' => 'J&T Express', 'ongkir' => 12000],
        'pos' => ['label' => 'POS Indonesia', 'ongkir' => 10000],
        'sicepat' => ['label' => 'SiCepat', 'ongkir' => 13000],
        'gosend' => ['label' => 'GoSend Instan', 'ongkir' => 20000],
    ];

    public function user() { return $this->belongsTo(User::class); }
    public function product() { return $this->belongsTo(Product::class); }
    public function details() { return $this->hasMany(DetailPesanan::class, 'pesanan_id'); }

    public function getPaymentLabelAttribute(): string
    {
        return self::PAYMENTS[$this->payment_method] ?? $this->payment_method;
    }

    public function getExpeditionLabelAttribute(): string
    {
        return self::EXPEDITIONS[$this->expedition]['label'] ?? $this->expedition;
    }

    public function getGrandTotalAttribute(): float
    {
        return (float) $this->total_harga + (float) $this->shipping_cost;
    }

    /** Label metode yg dipilih pembeli, cth: "QRIS", "Transfer Bank BCA". */
    public function getMetodeLabelAttribute(): string
    {
        if (preg_match('/\[via:([a-z]+)(?::([a-z]+))?\]/', $this->catatan ?? '', $m)) {
            $base = ['transfer' => 'Transfer Bank', 'cod' => 'COD (Tunai Gerai)', 'ewallet' => 'E-Wallet', 'qris' => 'QRIS', 'kartu' => 'Kartu Kredit/Debit'][$m[1]] ?? null;
            if ($base) {
                $sub = ['bca' => 'BCA', 'bni' => 'BNI', 'bri' => 'BRI', 'permata' => 'Permata', 'gopay' => 'GoPay', 'shopeepay' => 'ShopeePay', 'alfamart' => 'Alfamart', 'indomaret' => 'Indomaret'][$m[2] ?? ''] ?? null;
                return $sub ? $base . ' ' . $sub : $base;
            }
        }
        return $this->payment_label;
    }

    protected $appends = ['metode_label'];

    /** Deadline 1 hari untuk Midtrans yg belum dibayar. */
    public static function batasBayarDefault(): \DateTime
    {
        return now()->addDay();
    }

    public function belumBayar(): bool
    {
        return $this->status === 'pending' && $this->payment_method === 'midtrans' && ! $this->paid_at;
    }

    /** Label status ala marketplace. */
    public function statusLabel(): string
    {
        if ($this->belumBayar()) return 'Belum Bayar';
        return match ($this->status) {
            'pending' => 'Menunggu',
            'diproses' => 'Diproses',
            'dikirim' => 'Dikirim',
            'selesai' => 'Selesai',
            'batal' => 'Batal',
            default => $this->status,
        };
    }

    /** Batalkan otomatis pesanan Midtrans yg lewat deadline (kembalikan stok). */
    public static function expireOverdueFor(int $userId): int
    {
        $n = 0;
        $orders = static::where('user_id', $userId)->where('status', 'pending')
            ->where('payment_method', 'midtrans')->whereNull('paid_at')
            ->whereNotNull('batas_bayar')->where('batas_bayar', '<', now())->get();
        foreach ($orders as $o) {
            $o->update(['status' => 'batal', 'midtrans_status' => 'expire']);
            if ($o->product) $o->product->increment('stok', $o->jumlah);
            kirim_notifikasi($userId, 'Pesanan #' . $o->id . ' kedaluwarsa',
                'Batas pembayaran 1 hari terlewati. Pesanan dibatalkan & stok dikembalikan.',
                'pembayaran', 'pesanan', $o->id);
            $n++;
        }
        return $n;
    }
}
