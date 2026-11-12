<?php

use App\Models\SiteContent;

if (! function_exists('site_content')) {
    function site_content(string $key, ?string $default = null): ?string
    {
        return SiteContent::get($key, $default);
    }
}

if (! function_exists('site_image')) {
    function site_image(string $key, ?string $fallback = null): ?string
    {
        return SiteContent::image($key, $fallback);
    }
}

if (! function_exists('kirim_notifikasi')) {
    function kirim_notifikasi(int $userId, string $judul, string $pesan, string $tipe = 'info', ?string $refTipe = null, ?int $refId = null): void
    {
        \App\Models\Notifikasi::create([
            'user_id' => $userId, 'judul' => $judul, 'pesan' => $pesan,
            'tipe' => $tipe, 'referensi_tipe' => $refTipe, 'referensi_id' => $refId,
        ]);
    }
}
