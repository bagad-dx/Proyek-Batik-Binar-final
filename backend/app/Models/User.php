<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'pengguna';

    protected $fillable = [
        'name', 'username', 'email', 'password', 'role', 'phone', 'alamat', 'avatar', 'google_id', 'is_active',
        'banned_at', 'ban_reason', 'email_verified_at',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'banned_at' => 'datetime',
        ];
    }

    public const ROLES = ['user', 'admin', 'superadmin'];

    public function isBanned(): bool { return $this->banned_at !== null; }
    public function statusLabel(): string
    {
        if ($this->isBanned()) return 'Banned';
        return $this->is_active ? 'Aktif' : 'Nonaktif';
    }

    public function isSuperadmin(): bool { return $this->role === 'superadmin'; }
    public function isAdmin(): bool { return in_array($this->role, ['admin', 'superadmin'], true); }

    public function products() { return $this->hasMany(Product::class); }
    public function galleries() { return $this->hasMany(Gallery::class); }
    public function motifs() { return $this->hasMany(Motif::class); }
    public function orders() { return $this->hasMany(Order::class); }
}
