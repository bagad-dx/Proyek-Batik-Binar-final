<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Padanan tb_detail_pemesanan Raka: satu pesanan (header) boleh berisi
// N item produk. Baris dibuat otomatis saat checkout/order dibuat.
class DetailPesanan extends Model
{
    protected $table = 'detail_pesanan';
    protected $fillable = [
        'pesanan_id', 'product_id', 'jumlah', 'harga_satuan', 'subtotal',
    ];

    protected $casts = ['harga_satuan' => 'decimal:2', 'subtotal' => 'decimal:2'];

    public function order() { return $this->belongsTo(Order::class, 'pesanan_id'); }
    public function product() { return $this->belongsTo(Product::class, 'product_id'); }
}
