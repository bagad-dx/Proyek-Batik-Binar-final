<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Motif extends Model
{
    protected $table = 'motif';
    protected $fillable = [
        'user_id', 'nama', 'asal', 'filosofi',
        'lama_pengerjaan', 'teknik', 'gambar', 'is_published',
    ];
    protected $casts = ['is_published' => 'boolean'];
    public function user() { return $this->belongsTo(User::class); }
}
