<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

// 50 produk persis folder FrontEnd/PROYEK (belanja.html + script.js SHOP_PRODUCTS)
class NativeShopSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = User::where('role', 'admin')->value('id')
            ?? User::where('role', 'superadmin')->value('id');

        $cats = ['Kain Panjang', 'Selendang', 'Busana Pria', 'Busana Wanita', 'Aksesoris'];
        $catIds = [];
        foreach ($cats as $c) {
            $catIds[$c] = Category::firstOrCreate(
                ['slug' => Str::slug($c)],
                ['nama' => $c, 'deskripsi' => 'Kategori ' . $c . ' Batik Binar']
            )->id;
        }

        $items = [
            // [nama, kategori, harga, img, teknik, bahan, ukuran, desc]
            ['Kain Iwak Etong Royal Blue','Kain Panjang',1850000,'https://i.pinimg.com/1200x/1d/c7/6b/1dc76be61a727fc26275e2070a43a0a5.jpg','Tulis Complongan','Katun Primissima','250 × 110 cm','Kain panjang bermotif ikan etong khas laut Jawa Utara. Melambangkan kelimpahan rezeki bahari dan rasa syukur nelayan Dermayon. Dikerjakan dengan teknik complongan selama 40 hari.'],
            ['Kain Kapal Kandas Klasik','Kain Panjang',2100000,'https://i.pinimg.com/1200x/80/62/51/8062513ccda78d5b48f1285043239676.jpg','Tulis Halus','Katun Primissima','250 × 110 cm','Menceritakan kapal dagang kuno yang bersandar di muara Cimanuk. Simbol keteguhan hati dan pantang menyerah mengarungi samudra kehidupan.'],
            ['Kain Merak Ngibing Premium','Kain Panjang',2750000,'https://i.pinimg.com/1200x/8f/84/22/8f8422814f4175cf1907a40516b1abe7.jpg','Tulis Sutra','Sutra ATBM','250 × 110 cm','Visualisasi tarian merak dengan sayap mengembang penuh keanggunan. Dibuat di atas sutra halus ATBM dengan pewarnaan alami dari daun mangga dan kayu mahoni.'],
            ['Kain Sawat Kembang Pesisir','Kain Panjang',1650000,'https://i.pinimg.com/736x/9f/ff/79/9fff79bc00baa77371567b9d3a36de12.jpg','Tulis Complongan','Katun Primissima','250 × 110 cm','Motif bunga sawat yang mekar di tepi pantai Pantura. Menggambarkan keindahan alam pesisir Indramayu dan kesuburan tanah Dermayon.'],
            ['Kain Mega Mendung Dermayon','Kain Panjang',1950000,'https://i.pinimg.com/736x/ed/1b/c9/ed1bc9abe706ba2058fcf4b51df68761.jpg','Tulis Cap','Katun Primissima','250 × 110 cm','Adaptasi motif mega mendung versi pesisir Dermayon dengan gradasi biru laut yang khas dan sentuhan rintik complongan.'],
            ['Kain Udang Windu Bahari','Kain Panjang',1750000,'https://i.pinimg.com/1200x/7b/7d/a7/7b7da76e1d305f43cf9ddda3e83f8bc3.jpg','Tulis Halus','Katun Prima','250 × 110 cm','Motif udang windu melambangkan kemakmuran nelayan tambak di pesisir Indramayu. Dikerjakan dengan ketelitian tinggi oleh empu pembatik Paoman.'],
            ['Kain Singa Barong Paoman','Kain Panjang',3200000,'https://i.pinimg.com/1200x/40/9f/af/409fafab21f085588a2c6cb3c254c245.jpg','Tulis Halus','Sutra ATBM','250 × 110 cm','Motif singa barong yang gagah melambangkan keberanian dan kekuatan. Masterpiece dari sentra Paoman dengan pewarnaan alami eksklusif.'],
            ['Kain Buketan Flora Laut','Kain Panjang',1550000,'https://i.pinimg.com/736x/b4/de/f8/b4def8983ecfe86d12ca4a4d5f2be8dc.jpg','Tulis Cap','Katun Prima','250 × 110 cm','Rangkaian bunga dan flora laut yang disusun indah. Perpaduan warna biru pesisir dan hijau daun mangrove yang menyejukkan mata.'],
            ['Kain Lokcan Burung Phoenix','Kain Panjang',2850000,'https://i.pinimg.com/1200x/95/3d/de/953ddeb8fe354ddc006ed6cfa05e3367.jpg','Tulis Halus','Sutra Halus','250 × 110 cm','Motif burung phoenix atau lokcan yang melambangkan kebangkitan dan kemakmuran. Dipengaruhi oleh budaya Tionghoa pesisir utara Jawa.'],
            ['Kain Ganggeng Rumput Laut','Kain Panjang',1450000,'https://i.pinimg.com/736x/39/2b/39/392b39f3a3af88a805254025c5c18b6b.jpg','Cap Complongan','Katun Prima','250 × 110 cm','Motif rumput laut atau ganggeng yang bergoyang di dasar laut. Melambangkan kelenturan dan ketahanan hidup masyarakat pesisir.'],
            ['Selendang Iwak Etong Mini','Selendang',850000,'https://i.pinimg.com/1200x/1d/c7/6b/1dc76be61a727fc26275e2070a43a0a5.jpg','Tulis Halus','Katun Primissima','200 × 50 cm','Selendang elegan dengan motif iwak etong dalam ukuran compact. Cocok untuk aksen busana formal maupun casual.'],
            ['Selendang Merak Biru Pesisir','Selendang',950000,'https://i.pinimg.com/1200x/8f/84/22/8f8422814f4175cf1907a40516b1abe7.jpg','Tulis Complongan','Katun Prima','200 × 50 cm','Selendang bermotif merak ngibing dengan dominasi warna biru pesisir. Rintik complongan halus menambah dimensi tekstur yang unik.'],
            ['Selendang Sawat Kembang','Selendang',750000,'https://i.pinimg.com/736x/9f/ff/79/9fff79bc00baa77371567b9d3a36de12.jpg','Cap Halus','Katun Prima','200 × 50 cm','Selendang ringan dengan motif sawat kembang yang feminin. Pewarnaan natural menghasilkan gradasi biru yang lembut.'],
            ['Selendang Sutra Kapal Kandas','Selendang',1350000,'https://i.pinimg.com/1200x/80/62/51/8062513ccda78d5b48f1285043239676.jpg','Tulis Halus','Sutra ATBM','200 × 50 cm','Selendang sutra mewah dengan motif kapal kandas. Bahan sutra ATBM memberikan kilau alami yang anggun.'],
            ['Selendang Mega Mendung Scarf','Selendang',680000,'https://i.pinimg.com/736x/ed/1b/c9/ed1bc9abe706ba2058fcf4b51df68761.jpg','Cap','Katun Voile','180 × 45 cm','Scarf batik ringan dengan motif mega mendung versi Dermayon. Material voile yang breathable untuk gaya sehari-hari.'],
            ['Selendang Buketan Pastel','Selendang',720000,'https://i.pinimg.com/1200x/7b/7d/a7/7b7da76e1d305f43cf9ddda3e83f8bc3.jpg','Cap Halus','Katun Prima','200 × 50 cm','Selendang dengan motif buketan bunga dalam palet warna pastel biru. Cocok untuk hijab atau aksesori busana modern.'],
            ['Selendang Udang Windu','Selendang',800000,'https://i.pinimg.com/1200x/40/9f/af/409fafab21f085588a2c6cb3c254c245.jpg','Tulis Cap','Katun Prima','200 × 50 cm','Selendang bermotif udang windu dengan detail halus. Teknik tulis cap menghasilkan garis yang tegas namun tetap artistik.'],
            ['Selendang Lokcan Elegan','Selendang',1200000,'https://i.pinimg.com/1200x/95/3d/de/953ddeb8fe354ddc006ed6cfa05e3367.jpg','Tulis Halus','Sutra Halus','200 × 50 cm','Selendang sutra dengan motif lokcan burung phoenix. Keanggunan motif Tionghoa-Jawa yang menyatu harmonis.'],
            ['Selendang Ganggeng Hijau Laut','Selendang',650000,'https://i.pinimg.com/736x/39/2b/39/392b39f3a3af88a805254025c5c18b6b.jpg','Cap','Katun Voile','180 × 45 cm','Selendang ringan dengan motif ganggeng rumput laut. Perpaduan warna hijau laut dan biru yang menenangkan.'],
            ['Selendang Singa Barong Mini','Selendang',1100000,'https://i.pinimg.com/736x/b4/de/f8/b4def8983ecfe86d12ca4a4d5f2be8dc.jpg','Tulis Halus','Katun Primissima','200 × 50 cm','Selendang premium dengan motif singa barong dalam ukuran mini. Simbol keberanian yang cocok untuk acara formal.'],
            ['Kemeja Slim Iwak Etong','Busana Pria',1250000,'https://i.pinimg.com/1200x/1d/c7/6b/1dc76be61a727fc26275e2070a43a0a5.jpg','Tulis Cap','Katun Primissima','S / M / L / XL','Kemeja slim fit dengan motif iwak etong untuk pria modern. Potongan ergonomis dengan jahitan presisi untuk kenyamanan sepanjang hari.'],
            ['Kemeja Formal Kapal Kandas','Busana Pria',1450000,'https://i.pinimg.com/1200x/80/62/51/8062513ccda78d5b48f1285043239676.jpg','Tulis Halus','Katun Primissima','S / M / L / XL / XXL','Kemeja formal dengan motif kapal kandas klasik. Desain minimalis modern yang cocok untuk acara kantor dan pesta.'],
            ['Kemeja Lengan Pendek Merak','Busana Pria',980000,'https://i.pinimg.com/1200x/8f/84/22/8f8422814f4175cf1907a40516b1abe7.jpg','Cap','Katun Prima','S / M / L / XL','Kemeja lengan pendek casual dengan motif merak ngibing. Cocok untuk acara santai dan semi-formal.'],
            ['Kemeja Batik Mega Mendung','Busana Pria',1150000,'https://i.pinimg.com/736x/ed/1b/c9/ed1bc9abe706ba2058fcf4b51df68761.jpg','Tulis Cap','Katun Primissima','S / M / L / XL','Kemeja batik elegan dengan motif mega mendung Dermayon. Gradasi biru yang memikat untuk tampilan profesional.'],
            ['Kemeja Premium Singa Barong','Busana Pria',1850000,'https://i.pinimg.com/1200x/40/9f/af/409fafab21f085588a2c6cb3c254c245.jpg','Tulis Halus','Sutra ATBM','S / M / L / XL','Kemeja sutra premium dengan motif singa barong Paoman. Masterpiece yang cocok untuk acara istimewa dan pesta mewah.'],
            ['Kemeja Kasual Buketan','Busana Pria',880000,'https://i.pinimg.com/736x/b4/de/f8/b4def8983ecfe86d12ca4a4d5f2be8dc.jpg','Cap','Katun Prima','S / M / L / XL','Kemeja kasual dengan motif buketan flora. Desain relaxed fit yang nyaman untuk kegiatan sehari-hari.'],
            ['Kemeja Batik Lokcan Pria','Busana Pria',1550000,'https://i.pinimg.com/1200x/95/3d/de/953ddeb8fe354ddc006ed6cfa05e3367.jpg','Tulis Halus','Katun Primissima','S / M / L / XL / XXL','Kemeja formal dengan motif lokcan burung phoenix. Keanggunan budaya Tionghoa-Jawa dalam balutan modern.'],
            ['Kemeja Sawat Kembang Pria','Busana Pria',950000,'https://i.pinimg.com/736x/9f/ff/79/9fff79bc00baa77371567b9d3a36de12.jpg','Cap Halus','Katun Prima','S / M / L / XL','Kemeja batik dengan motif sawat kembang yang maskulin. Sentuhan bunga pesisir dalam gaya yang bold dan modern.'],
            ['Kemeja Ganggeng Slim Fit','Busana Pria',920000,'https://i.pinimg.com/736x/39/2b/39/392b39f3a3af88a805254025c5c18b6b.jpg','Cap','Katun Prima','S / M / L / XL','Kemeja slim fit dengan motif ganggeng rumput laut. Nuansa laut yang kalem untuk gaya sehari-hari.'],
            ['Kemeja Udang Windu Executive','Busana Pria',1350000,'https://i.pinimg.com/1200x/7b/7d/a7/7b7da76e1d305f43cf9ddda3e83f8bc3.jpg','Tulis Cap','Katun Primissima','S / M / L / XL / XXL','Kemeja executive dengan motif udang windu. Potongan regular fit yang nyaman untuk eksekutif modern.'],
            ['Dress Merak Ngibing Elegan','Busana Wanita',2250000,'https://i.pinimg.com/1200x/8f/84/22/8f8422814f4175cf1907a40516b1abe7.jpg','Tulis Halus','Sutra ATBM','S / M / L / XL','Dress elegan dengan motif merak ngibing di atas sutra ATBM. Siluet A-line yang anggun untuk acara gala dan pesta.'],
            ['Blouse Iwak Etong Modern','Busana Wanita',1150000,'https://i.pinimg.com/1200x/1d/c7/6b/1dc76be61a727fc26275e2070a43a0a5.jpg','Tulis Cap','Katun Primissima','S / M / L / XL','Blouse modern dengan motif iwak etong. Potongan peplum yang feminin dengan detail ruffles di pergelangan tangan.'],
            ['Tunik Mega Mendung Dermayon','Busana Wanita',1350000,'https://i.pinimg.com/736x/ed/1b/c9/ed1bc9abe706ba2058fcf4b51df68761.jpg','Tulis Cap','Katun Primissima','S / M / L / XL / XXL','Tunik longgar bermotif mega mendung Dermayon. Cocok untuk dipadupadankan dengan celana atau rok untuk gaya modest.'],
            ['Rok Batik Kapal Kandas','Busana Wanita',980000,'https://i.pinimg.com/1200x/80/62/51/8062513ccda78d5b48f1285043239676.jpg','Cap Halus','Katun Prima','S / M / L / XL','Rok A-line dengan motif kapal kandas. Potongan midi yang versatile untuk tampilan kasual hingga semi-formal.'],
            ['Dress Cocktail Sawat Kembang','Busana Wanita',1850000,'https://i.pinimg.com/736x/9f/ff/79/9fff79bc00baa77371567b9d3a36de12.jpg','Tulis Halus','Katun Primissima','S / M / L','Dress cocktail dengan motif sawat kembang. Desain off-shoulder yang menawan untuk pesta malam.'],
            ['Blouse Buketan Flora Wanita','Busana Wanita',920000,'https://i.pinimg.com/736x/b4/de/f8/b4def8983ecfe86d12ca4a4d5f2be8dc.jpg','Cap','Katun Prima','S / M / L / XL','Blouse casual dengan motif buketan flora pesisir. Potongan relaxed yang nyaman untuk kegiatan sehari-hari.'],
            ['Outer Batik Lokcan Wanita','Busana Wanita',1650000,'https://i.pinimg.com/1200x/95/3d/de/953ddeb8fe354ddc006ed6cfa05e3367.jpg','Tulis Halus','Katun Primissima','S / M / L / XL','Outer atau cardigan batik dengan motif lokcan. Layering piece yang sempurna untuk gaya berlapis modern.'],
            ['Kaftan Singa Barong Premium','Busana Wanita',2450000,'https://i.pinimg.com/1200x/40/9f/af/409fafab21f085588a2c6cb3c254c245.jpg','Tulis Halus','Sutra ATBM','Free Size','Kaftan sutra premium dengan motif singa barong. Busana mewah untuk acara istimewa dengan draping yang mengalir indah.'],
            ['Cullote Ganggeng Kasual','Busana Wanita',780000,'https://i.pinimg.com/736x/39/2b/39/392b39f3a3af88a805254025c5c18b6b.jpg','Cap','Katun Prima','S / M / L / XL','Cullote atau celana kulot bermotif ganggeng rumput laut. Nyaman dan stylish untuk tampilan sehari-hari.'],
            ['Kebaya Modern Udang Windu','Busana Wanita',1750000,'https://i.pinimg.com/1200x/7b/7d/a7/7b7da76e1d305f43cf9ddda3e83f8bc3.jpg','Tulis Cap','Katun Primissima','S / M / L / XL','Kebaya modern dengan sentuhan motif udang windu. Perpaduan tradisi dan modernitas untuk acara adat dan resmi.'],
            ['Tote Bag Batik Iwak Etong','Aksesoris',450000,'https://i.pinimg.com/1200x/1d/c7/6b/1dc76be61a727fc26275e2070a43a0a5.jpg','Cap','Katun Canvas + Batik','40 × 35 × 10 cm','Tote bag premium dengan panel batik motif iwak etong. Tas serbaguna dengan lapisan dalam kanvas tebal.'],
            ['Clutch Batik Merak Ngibing','Aksesoris',380000,'https://i.pinimg.com/1200x/8f/84/22/8f8422814f4175cf1907a40516b1abe7.jpg','Tulis','Batik + Kulit Sintetis','28 × 16 cm','Clutch elegan dengan panel batik motif merak ngibing. Dilengkapi strap rantai gold untuk tampilan mewah.'],
            ['Masker Batik Premium Set','Aksesoris',180000,'https://i.pinimg.com/736x/9f/ff/79/9fff79bc00baa77371567b9d3a36de12.jpg','Cap','Katun Prima 3-Ply','Free Size (set 3)','Set 3 masker batik premium dengan motif berbeda. Bahan katun 3-ply yang breathable dan bisa dicuci ulang.'],
            ['Dompet Batik Kapal Kandas','Aksesoris',320000,'https://i.pinimg.com/1200x/80/62/51/8062513ccda78d5b48f1285043239676.jpg','Cap','Batik + Kulit Sintetis','20 × 10 cm','Dompet panjang dengan panel batik motif kapal kandas. Banyak sekat untuk kartu dan uang kertas.'],
            ['Kipas Batik Tulis Mewah','Aksesoris',250000,'https://i.pinimg.com/1200x/7b/7d/a7/7b7da76e1d305f43cf9ddda3e83f8bc3.jpg','Tulis','Kain Batik + Bambu','23 cm (buka)','Kipas lipat mewah dengan kain batik tulis motif pesisir. Rangka bambu yang ringan dan kokoh.'],
            ['Ikat Kepala Batik Unisex','Aksesoris',150000,'https://i.pinimg.com/736x/ed/1b/c9/ed1bc9abe706ba2058fcf4b51df68761.jpg','Cap','Katun Prima','Free Size','Ikat kepala atau bandana batik unisex dengan motif mega mendung. Aksesori stylish untuk gaya streetwear.'],
            ['Syal Batik Buketan Lebar','Aksesoris',550000,'https://i.pinimg.com/736x/b4/de/f8/b4def8983ecfe86d12ca4a4d5f2be8dc.jpg','Cap Halus','Katun Voile','180 × 70 cm','Syal lebar bermotif buketan flora pesisir. Material voile ringan yang cocok untuk traveling dan outdoor.'],
            ['Pouch Batik Serbaguna','Aksesoris',220000,'https://i.pinimg.com/1200x/95/3d/de/953ddeb8fe354ddc006ed6cfa05e3367.jpg','Cap','Batik + Canvas','25 × 15 cm','Pouch serbaguna dengan panel batik motif lokcan. Cocok untuk menyimpan kosmetik, gadget, atau alat tulis.'],
            ['Gantungan Kunci Batik Set','Aksesoris',95000,'https://i.pinimg.com/736x/39/2b/39/392b39f3a3af88a805254025c5c18b6b.jpg','Cap','Kain Batik + Metal','8 × 4 cm (set 3)','Set 3 gantungan kunci dengan potongan kain batik berbeda motif. Souvenir khas Indramayu yang unik.'],
            ['Bantal Sofa Batik Complongan','Aksesoris',350000,'https://i.pinimg.com/1200x/40/9f/af/409fafab21f085588a2c6cb3c254c245.jpg','Cap Complongan','Katun + Isi Dakron','45 × 45 cm','Bantal sofa dekoratif dengan sarung batik motif complongan. Menambah sentuhan budaya pada interior rumah Anda.'],
        ];

        $known = ['Iwak Etong', 'Kapal Kandas', 'Merak Ngibing', 'Mega Mendung', 'Singa Barong', 'Buketan', 'Lokcan', 'Sawat Kembang', 'Ganggeng', 'Udang Windu'];

        foreach ($items as [$nama, $kat, $harga, $img, $teknik, $bahan, $ukuran, $desc]) {
            $motif = '-';
            foreach ($known as $k) {
                if (stripos($nama, $k) !== false) { $motif = $k; break; }
            }
            Product::firstOrCreate(['nama' => $nama], [
                'user_id' => $adminId,
                'category_id' => $catIds[$kat],
                'slug' => Str::slug($nama),
                'deskripsi' => $desc . ' Ukuran: ' . $ukuran . '.',
                'harga' => $harga,
                'stok' => 10,
                'gambar' => $img,
                'motif' => $motif,
                'bahan' => $bahan,
                'status' => 'aktif',
            ]);
        }
    }
}
