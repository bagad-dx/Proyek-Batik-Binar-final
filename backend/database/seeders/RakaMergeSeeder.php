<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\DetailPesanan;
use App\Models\Gallery;
use App\Models\JenisProduk;
use App\Models\Order;
use App\Models\Product;
use App\Models\TagGaleri;
use Illuminate\Database\Seeder;

// Hasil gabungan fitur native Raka (tb_jenis_produk, tb_tag_galeri,
// kolom etalase tb_etalase, filosofi tb_galeri, tb_detail_pemesanan).
// Idempotent: aman dijalankan ulang (firstOrCreate / update / NOT EXISTS).
class RakaMergeSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Jenis produk dicerminkan dari kategori (label_display = nama).
        foreach (Category::all() as $cat) {
            $jenis = JenisProduk::firstOrCreate(
                ['slug' => $cat->slug],
                ['nama' => $cat->nama, 'label_display' => $cat->nama, 'deskripsi' => $cat->deskripsi]
            );
            Product::where('category_id', $cat->id)->whereNull('jenis_id')
                ->update(['jenis_id' => $jenis->id]);
        }

        // 2. Tag galeri khas Raka.
        foreach (['Motif Batik', 'Proses Pembuatan', 'Detail Tekstur', 'Busana Modern', 'Sentra Paoman'] as $label) {
            TagGaleri::firstOrCreate(['label' => $label]);
        }

        // 3. Ukuran + teknik etalase Raka (isi kolom produk yang masih kosong).
        // Data identik query2.sql Raka / NativeShopSeeder (50 produk).
        $items = [
            ['Kain Iwak Etong Royal Blue', 'Tulis Complongan', '250 × 110 cm'],
            ['Kain Kapal Kandas Klasik', 'Tulis Halus', '250 × 110 cm'],
            ['Kain Merak Ngibing Premium', 'Tulis Sutra', '250 × 110 cm'],
            ['Kain Sawat Kembang Pesisir', 'Tulis Complongan', '250 × 110 cm'],
            ['Kain Mega Mendung Dermayon', 'Tulis Cap', '250 × 110 cm'],
            ['Kain Udang Windu Bahari', 'Tulis Halus', '250 × 110 cm'],
            ['Kain Singa Barong Paoman', 'Tulis Halus', '250 × 110 cm'],
            ['Kain Buketan Flora Laut', 'Tulis Cap', '250 × 110 cm'],
            ['Kain Lokcan Burung Phoenix', 'Tulis Halus', '250 × 110 cm'],
            ['Kain Ganggeng Rumput Laut', 'Cap Complongan', '250 × 110 cm'],
            ['Selendang Iwak Etong Mini', 'Tulis Halus', '200 × 50 cm'],
            ['Selendang Merak Biru Pesisir', 'Tulis Complongan', '200 × 50 cm'],
            ['Selendang Sawat Kembang', 'Cap Halus', '200 × 50 cm'],
            ['Selendang Sutra Kapal Kandas', 'Tulis Halus', '200 × 50 cm'],
            ['Selendang Mega Mendung Scarf', 'Cap', '180 × 45 cm'],
            ['Selendang Buketan Pastel', 'Cap Halus', '200 × 50 cm'],
            ['Selendang Udang Windu', 'Tulis Cap', '200 × 50 cm'],
            ['Selendang Lokcan Elegan', 'Tulis Halus', '200 × 50 cm'],
            ['Selendang Ganggeng Hijau Laut', 'Cap', '180 × 45 cm'],
            ['Selendang Singa Barong Mini', 'Tulis Halus', '200 × 50 cm'],
            ['Kemeja Slim Iwak Etong', 'Tulis Cap', 'S / M / L / XL'],
            ['Kemeja Formal Kapal Kandas', 'Tulis Halus', 'S / M / L / XL / XXL'],
            ['Kemeja Lengan Pendek Merak', 'Cap', 'S / M / L / XL'],
            ['Kemeja Batik Mega Mendung', 'Tulis Cap', 'S / M / L / XL'],
            ['Kemeja Premium Singa Barong', 'Tulis Halus', 'S / M / L / XL'],
            ['Kemeja Kasual Buketan', 'Cap', 'S / M / L / XL'],
            ['Kemeja Batik Lokcan Pria', 'Tulis Halus', 'S / M / L / XL / XXL'],
            ['Kemeja Sawat Kembang Pria', 'Cap Halus', 'S / M / L / XL'],
            ['Kemeja Ganggeng Slim Fit', 'Cap', 'S / M / L / XL'],
            ['Kemeja Udang Windu Executive', 'Tulis Cap', 'S / M / L / XL / XXL'],
            ['Dress Merak Ngibing Elegan', 'Tulis Halus', 'S / M / L / XL'],
            ['Blouse Iwak Etong Modern', 'Tulis Cap', 'S / M / L / XL'],
            ['Tunik Mega Mendung Dermayon', 'Tulis Cap', 'S / M / L / XL / XXL'],
            ['Rok Batik Kapal Kandas', 'Cap Halus', 'S / M / L / XL'],
            ['Dress Cocktail Sawat Kembang', 'Tulis Halus', 'S / M / L'],
            ['Blouse Buketan Flora Wanita', 'Cap', 'S / M / L / XL'],
            ['Outer Batik Lokcan Wanita', 'Tulis Halus', 'S / M / L / XL'],
            ['Kaftan Singa Barong Premium', 'Tulis Halus', 'Free Size'],
            ['Cullote Ganggeng Kasual', 'Cap', 'S / M / L / XL'],
            ['Kebaya Modern Udang Windu', 'Tulis Cap', 'S / M / L / XL'],
            ['Tote Bag Batik Iwak Etong', 'Cap', '40 × 35 × 10 cm'],
            ['Clutch Batik Merak Ngibing', 'Tulis', '28 × 16 cm'],
            ['Masker Batik Premium Set', 'Cap', 'Free Size (set 3)'],
            ['Dompet Batik Kapal Kandas', 'Cap', '20 × 10 cm'],
            ['Kipas Batik Tulis Mewah', 'Tulis', '23 cm (buka)'],
            ['Ikat Kepala Batik Unisex', 'Cap', 'Free Size'],
            ['Syal Batik Buketan Lebar', 'Cap Halus', '180 × 70 cm'],
            ['Pouch Batik Serbaguna', 'Cap', '25 × 15 cm'],
            ['Gantungan Kunci Batik Set', 'Cap', '8 × 4 cm (set 3)'],
            ['Bantal Sofa Batik Complongan', 'Cap Complongan', '45 × 45 cm'],
        ];
        foreach ($items as [$nama, $teknik, $ukuran]) {
            Product::where('nama', $nama)
                ->where(function ($q) { $q->whereNull('ukuran')->orWhereNull('teknik'); })
                ->update(['ukuran' => $ukuran, 'teknik' => $teknik]);
        }

        // 4. Lengkapi baris detail untuk pesanan lama (satu header -> satu item).
        foreach (Order::doesntHave('details')->get() as $order) {
            DetailPesanan::create([
                'pesanan_id' => $order->id,
                'product_id' => $order->product_id,
                'jumlah' => $order->jumlah,
                'harga_satuan' => $order->jumlah > 0 ? $order->total_harga / $order->jumlah : $order->total_harga,
                'subtotal' => $order->total_harga,
            ]);
        }

        // 5. Pastikan kolom tag galeri terisi (fallback = kategori).
        Gallery::whereNull('tag')->update(['tag' => \DB::raw('`kategori`')]);
    }
}
