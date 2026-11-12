<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PesanChat extends Model
{
    protected $table = 'pesan_chat';
    protected $fillable = ['percakapan_id', 'pengirim_id', 'pesan', 'dibaca_at'];
    protected $casts = ['dibaca_at' => 'datetime'];
    public function percakapan() { return $this->belongsTo(Percakapan::class, 'percakapan_id'); }
    public function pengirim() { return $this->belongsTo(User::class, 'pengirim_id'); }
}
