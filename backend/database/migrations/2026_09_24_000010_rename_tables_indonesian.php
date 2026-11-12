<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

/**
 * Bahasa Indonesia-kan nama tabel:
 * users->pengguna, categories->kategori, products->produk,
 * galleries->galeri, motifs->motif, orders->pesanan,
 * chat_settings->pengaturan_chat, chat_faqs->faq_chat,
 * site_contents->konten_website.
 * (Tabel bawaan framework: sessions, cache, jobs, password_reset_tokens,
 *  personal_access_tokens, migrations — dibiarkan agar Sanctum dsb tetap jalan.)
 */
return new class extends Migration
{
    protected array $map = [
        'users' => 'pengguna',
        'categories' => 'kategori',
        'products' => 'produk',
        'galleries' => 'galeri',
        'motifs' => 'motif',
        'orders' => 'pesanan',
        'chat_settings' => 'pengaturan_chat',
        'chat_faqs' => 'faq_chat',
        'site_contents' => 'konten_website',
    ];

    public function up(): void
    {
        foreach ($this->map as $from => $to) {
            if (Schema::hasTable($from) && ! Schema::hasTable($to)) {
                Schema::rename($from, $to);
            }
        }
    }

    public function down(): void
    {
        foreach (array_reverse($this->map) as $to => $from) {
            // $to = nama Indonesia, $from = nama Inggris (urutan map dibalik)
            if (Schema::hasTable($to) && ! Schema::hasTable($from)) {
                Schema::rename($to, $from);
            }
        }
    }
};
