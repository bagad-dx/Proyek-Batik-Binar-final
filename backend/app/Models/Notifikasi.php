<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notifikasi extends Model
{
    protected $table = 'notifikasi';
    protected $fillable = ['user_id', 'judul', 'pesan', 'tipe', 'referensi_tipe', 'referensi_id', 'dibaca_at'];
    protected $casts = ['dibaca_at' => 'datetime'];
    protected $appends = ['url'];
    public function user() { return $this->belongsTo(User::class); }

    /** Tujuan klik notifikasi (deep-link ke halaman yang dimaksud). */
    public function getUrlAttribute(): string
    {
        if ($this->referensi_tipe === 'chat') {
            $orderId = \App\Models\Percakapan::whereKey($this->referensi_id)->value('order_id');
            return $orderId ? 'pesanan.html?chat=' . $orderId : 'pesanan.html';
        }
        if ($this->referensi_tipe === 'pesanan' && $this->referensi_id) {
            $order = \App\Models\Order::find($this->referensi_id);
            if ($order && $order->belumBayar()) return 'pembayaran.html?order_id=' . $order->id;
            return 'pesanan.html?order_id=' . $this->referensi_id;
        }
        return 'pesanan.html';
    }
}

