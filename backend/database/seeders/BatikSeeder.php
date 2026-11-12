<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Gallery;
use App\Models\Motif;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class BatikSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Akun default tiap role
        $superadmin = User::firstOrCreate(['email' => 'superadmin@batikbinar.id'], [
            'name' => 'Super Admin', 'username' => 'superadmin', 'password' => Hash::make('password'),
            'role' => 'superadmin', 'phone' => '081234567890', 'is_active' => true,
        ]);
        $admin = User::firstOrCreate(['email' => 'admin@batikbinar.id'], [
            'name' => 'Admin Batik', 'username' => 'adminbatik', 'password' => Hash::make('password'),
            'role' => 'admin', 'phone' => '081234567891', 'is_active' => true,
        ]);
        User::firstOrCreate(['email' => 'user@batikbinar.id'], [
            'name' => 'User Demo', 'username' => 'userdemo', 'password' => Hash::make('password'),
            'role' => 'user', 'phone' => '081234567892', 'is_active' => true,
        ]);

        // backfill username akun demo lama (dibuat sebelum kolom username ada)
        foreach (['superadmin@batikbinar.id' => 'superadmin', 'admin@batikbinar.id' => 'adminbatik', 'user@batikbinar.id' => 'userdemo'] as $email => $uname) {
            User::where('email', $email)->whereNull('username')->update(['username' => $uname]);
        }
        // 1b. Akun dummy: bagad (user), bagadadmin (admin), bagadsuperadmin (superadmin), pw: 123
        foreach ([
            ['name' => 'Bagad', 'username' => 'bagad', 'email' => 'bagad@batikbinar.id', 'role' => 'user', 'phone' => '081200000001'],
            ['name' => 'Bagad Admin', 'username' => 'bagadadmin', 'email' => 'bagadadmin@batikbinar.id', 'role' => 'admin', 'phone' => '081200000002'],
            ['name' => 'Bagad Superadmin', 'username' => 'bagadsuperadmin', 'email' => 'bagadsuperadmin@batikbinar.id', 'role' => 'superadmin', 'phone' => '081200000003'],
        ] as $d) {
            User::updateOrCreate(['username' => $d['username']], $d + [
                'password' => Hash::make('123'), 'is_active' => true,
            ]);
        }

        // 2. Kategori
        $kain = Category::firstOrCreate(['slug' => 'kain'], ['nama' => 'Kain', 'deskripsi' => 'Kain batik tulis']);
        $sutra = Category::firstOrCreate(['slug' => 'sutra'], ['nama' => 'Sutra', 'deskripsi' => 'Kain sutra premium']);
        $busana = Category::firstOrCreate(['slug' => 'busana'], ['nama' => 'Busana Jadi', 'deskripsi' => 'Kemeja & gaun']);

        // 3. Produk contoh (postingan jual beli)
        $produk = [
            ['nama' => 'Kain Tulis Iwak Etong', 'category_id' => $kain->id, 'harga' => 850000, 'stok' => 10, 'motif' => 'Iwak Etong', 'bahan' => 'Katun Primissima'],
            ['nama' => 'Kain Panjang Kapal Kandas', 'category_id' => $kain->id, 'harga' => 950000, 'stok' => 8, 'motif' => 'Kapal Kandas', 'bahan' => 'Katun Primissima'],
            ['nama' => 'Sutra Merak Ngibing', 'category_id' => $sutra->id, 'harga' => 2500000, 'stok' => 5, 'motif' => 'Merak Ngibing', 'bahan' => 'Sutra ATBM'],
            ['nama' => 'Kemeja Batik Pria Minimalis', 'category_id' => $busana->id, 'harga' => 450000, 'stok' => 20, 'motif' => 'Iwak Etong', 'bahan' => 'Katun'],
        ];
        foreach ($produk as $p) {
            Product::firstOrCreate(['slug' => Str::slug($p['nama'])], [
                'user_id' => $admin->id, 'category_id' => $p['category_id'],
                'nama' => $p['nama'], 'deskripsi' => 'Koleksi otentik Batik Binar Paoman, teknik Complongan.',
                'harga' => $p['harga'], 'stok' => $p['stok'],
                'motif' => $p['motif'], 'bahan' => $p['bahan'], 'status' => 'aktif',
            ]);
        }

        // 4. Galeri contoh
        $galeri = [
            ['judul' => 'Elegansi Busana Sutra', 'kategori' => 'Editorial Busana'],
            ['judul' => 'Ketekunan Canting Maestro', 'kategori' => 'Artisan Craft'],
            ['judul' => 'Siluet Busana Wanita', 'kategori' => 'Contemporary Women'],
            ['judul' => 'Rintik Jarum Complongan', 'kategori' => 'Detail Tekstur'],
        ];
        foreach ($galeri as $g) {
            Gallery::firstOrCreate(['judul' => $g['judul']], [
                'user_id' => $admin->id, 'kategori' => $g['kategori'],
                'deskripsi' => 'Dokumentasi ' . $g['judul'] . ' Batik Binar.',
                'is_published' => true,
            ]);
        }

        // 5. Motif contoh
        $motif = [
            ['nama' => 'Iwak Etong', 'filosofi' => 'Ikan etong laut Jawa, simbol kelimpahan rezeki dan syukur nelayan Dermayon.', 'lama' => '40 Hari'],
            ['nama' => 'Kapal Kandas', 'filosofi' => 'Kapal dagang kuno muara Cimanuk, simbol keteguhan hati.', 'lama' => '45 Hari'],
            ['nama' => 'Merak Ngibing', 'filosofi' => 'Tarian merak, aura percaya diri dan keanggunan.', 'lama' => '60 Hari'],
        ];
        foreach ($motif as $m) {
            Motif::firstOrCreate(['nama' => $m['nama']], [
                'user_id' => $admin->id, 'asal' => 'Paoman, Indramayu',
                'filosofi' => $m['filosofi'], 'lama_pengerjaan' => $m['lama'],
                'teknik' => 'Tulis Complongan', 'is_published' => true,
            ]);
        }
    }
}
