<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Gallery extends Model
{
    protected $table = 'galeri';
    protected $fillable = ['user_id', 'judul', 'kategori', 'deskripsi', 'gambar', 'is_published',
        'filosofi', 'tahun_pembuatan', 'motif_id', 'tag'];
    protected $casts = ['is_published' => 'boolean'];
    public function user() { return $this->belongsTo(User::class); }
    public function motif() { return $this->belongsTo(Motif::class, 'motif_id'); }
}
