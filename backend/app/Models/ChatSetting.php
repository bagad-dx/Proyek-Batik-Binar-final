<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class ChatSetting extends Model
{
    protected $table = 'pengaturan_chat';
    protected $fillable = ['key', 'value'];
    public $timestamps = true;

    public const DEFAULTS = [
        'bot_name' => 'Binar',
        'is_active' => '1',
        'greeting' => 'Halo! Saya <b>Binar</b>, asisten Batik Binar. Tanya saya soal <b>best seller</b>, motif, harga, cara beli, atau ongkir!',
        'shopee_store_url' => 'https://id.shp.ee/ng6JKTM5',
        'ai_provider' => 'off',
        'ai_api_key' => '',
        'ai_model' => 'gemini-3.5-flash',
    ];

    public static function get(string $key): string
    {
        $val = static::where('key', $key)->value('value');
        return $val ?? static::DEFAULTS[$key] ?? '';
    }

    public static function set(string $key, ?string $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget('chat_settings');
    }
}
