<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SiteContent extends Model
{
    protected $table = 'konten_website';
    protected $fillable = ['section', 'key', 'label', 'type', 'value', 'order'];

    public static function get(string $key, ?string $default = null): ?string
    {
        $all = Cache::rememberForever('site_contents', fn () => static::pluck('value', 'key')->all());
        return $all[$key] ?? $default;
    }

    public static function set(string $key, ?string $value): void
    {
        static::where('key', $key)->update(['value' => $value]);
        Cache::forget('site_contents');
    }

    /** URL gambar: http langsung, path lokal via storage, kosong -> fallback. */
    public static function image(string $key, ?string $fallback = null): ?string
    {
        $v = static::get($key);
        if (! $v) return $fallback;
        if (str_starts_with($v, 'http')) return $v;
        return asset('storage/' . $v);
    }

    public static function flush(): void
    {
        Cache::forget('site_contents');
    }
}
