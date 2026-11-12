<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

// Padanan tb_jenis_produk Raka (Kain Panjang, Selendang, Busana Pria,
// Busana Wanita, Aksesoris + label_display). Dicerminkan dari kategori.
class JenisProduk extends Model
{
    protected $table = 'jenis_produk';
    protected $fillable = ['slug', 'nama', 'label_display', 'deskripsi'];

    public function products() { return $this->hasMany(Product::class, 'jenis_id'); }
}
