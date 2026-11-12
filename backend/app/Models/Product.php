<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'produk';
    protected $fillable = [
        'user_id', 'category_id', 'jenis_id', 'nama', 'slug', 'deskripsi',
        'harga', 'stok', 'gambar', 'shopee_url', 'motif', 'bahan',
        'ukuran', 'teknik', 'status',
    ];

    protected $casts = ['harga' => 'decimal:2'];

    public function user() { return $this->belongsTo(User::class); }
    public function category() { return $this->belongsTo(Category::class); }
    public function jenis() { return $this->belongsTo(JenisProduk::class, 'jenis_id'); }
    public function orders() { return $this->hasMany(Order::class); }

    public function getGambarUrlAttribute(): ?string
    {
        if (! $this->gambar) return null;
        if (str_starts_with($this->gambar, 'http')) return $this->gambar;
        return asset('storage/' . $this->gambar);
    }
}
