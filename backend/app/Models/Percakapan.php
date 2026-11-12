<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Percakapan extends Model
{
    protected $table = 'percakapan';
    protected $fillable = ['user_id', 'order_id'];
    public function user() { return $this->belongsTo(User::class); }
    public function order() { return $this->belongsTo(Order::class, 'order_id'); }
    public function pesan() { return $this->hasMany(PesanChat::class, 'percakapan_id')->latest(); }
    public function pesanTerakhir() { return $this->hasOne(PesanChat::class, 'percakapan_id')->latestOfMany(); }

    public function belumDibacaOleh(int $userId): int
    {
        return $this->pesan()->where('pengirim_id', '!=', $userId)->whereNull('dibaca_at')->count();
    }
}
