<?php

namespace Database\Seeders;

use App\Models\SiteContent;
use Illuminate\Database\Seeder;

class SiteContentSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            // HERO
            ['hero', 'hero_badge', 'Badge kecil hero', 'text', 'WARISAN • PESISIR • MAHASENI', 1],
            ['hero', 'hero_title', 'Judul hero (boleh HTML)', 'html', 'Warisan Batik <br><span class="text-accent">Indramayu</span>, Untuk <br>Generasi Masa Kini', 2],
            ['hero', 'hero_desc', 'Deskripsi hero', 'textarea', 'Menghadirkan keindahan wastra otentik pesisir Dermayon melalui harmoni motif maritim yang berani, keunikan jarum <em>Complongan</em>, dan rancangan modern untuk gaya hidup kontemporer Anda.', 3],
            ['hero', 'hero_btn1', 'Teks tombol 1', 'text', 'Belanja Sekarang', 4],
            ['hero', 'hero_btn2', 'Teks tombol 2', 'text', 'Jelajahi Koleksi', 5],
            // TENTANG
            ['tentang', 'tentang_eyebrow', 'Label atas', 'text', 'TENTANG KAMI', 1],
            ['tentang', 'tentang_heading', 'Judul', 'text', 'Mengapa Memilih Batik Binar Dermayon', 2],
            ['tentang', 'tentang_narrative1', 'Paragraf 1 (boleh HTML)', 'html', 'Di <strong>Batik Binar</strong>, kami meyakini bahwa batik lebih dari sekadar kain bercorak—ia adalah jembatan sejarah, martabat pesisir, dan doa ketulusan para pembatik Paoman, Indramayu.', 3],
            ['tentang', 'tentang_narrative2', 'Paragraf 2 (boleh HTML)', 'html', 'Dengan memadukan teknik langka <em>Complongan</em> (penusukan ribuan jarum mikro khas Indramayu), pewarnaan ramah lingkungan, dan rancangan siluet modern yang ergonomis, kami memastikan setiap lembar kain nyaman dikenakan serta bernilai seni tinggi.', 4],
            ['tentang', 'tentang_btn', 'Teks tombol', 'text', 'Pelajari Selengkapnya', 5],
            ['tentang', 'tentang_img1', 'Foto kolase 1', 'image', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSZfmOGClQiubBQW8V3PQIcRQjfK9tvMdOixdzr4qqIz5oEy_xRvgWSUMg&s=10', 6],
            ['tentang', 'tentang_img2', 'Foto kolase 2', 'image', 'https://i.pinimg.com/1200x/40/9f/af/409fafab21f085588a2c6cb3c254c245.jpg', 7],
            ['tentang', 'tentang_img3', 'Foto kolase 3', 'image', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTytAskEXFa5AgtYqdWMRj09YU2Vu7F6g6AMw9TiZno94uWkbqit3L0o4g&s=10', 8],
            // PROSES header
            ['proses', 'proses_eyebrow', 'Label atas', 'text', 'PROSES & KEUNGGULAN', 1],
            ['proses', 'proses_heading', 'Judul', 'text', 'Dedikasi Mahakarya Dari Hulu Ke Hilir', 2],
            ['proses', 'proses_desc', 'Deskripsi', 'textarea', 'Setiap karya Batik Binar melalui 6 tahapan autentik yang dikerjakan dengan ketelitian tinggi oleh empu pembatik Paoman.', 3],
        ];

        $steps = [
            ['1. Molani & Pola Khas', 'Menggambar sketsa motif fauna bahari dan flora pesisir Dermayon di atas kain mori primissima berkualitas tinggi.', 'Tahap Awal', 'https://i.pinimg.com/1200x/95/3d/de/953ddeb8fe354ddc006ed6cfa05e3367.jpg'],
            ['2. Nyanting Tulis Tangan', 'Menorehkan lilin malam panas menggunakan canting tembaga untuk mengunci presisi garis motif secara murni.', 'Canting Halus', 'https://i.pinimg.com/1200x/81/3a/90/813a9043d3306042c70ec20e578201b4.jpg'],
            ['3. Khas Complongan Jarum', 'Teknik khas Indramayu menusuk ribuan jarum mikro di atas kain malam untuk menciptakan efek rintik cecek magis.', 'Ikon Indramayu', 'https://i.pinimg.com/736x/39/2b/39/392b39f3a3af88a805254025c5c18b6b.jpg'],
            ['4. Pewarnaan Pesisir Alami', 'Pencelupan bertahap menggunakan ekstrak kayu mahoni, daun mangga, dan pigmen biru laut ramah lingkungan.', 'Warna Bahari', 'https://i.pinimg.com/1200x/64/cc/4c/64cc4ca864ca94f032dc5d48bdf1741e.jpg'],
            ['5. Nglorod Lilin Malam', 'Merebus kain ke dalam air mendidih untuk meluruhkan seluruh lilin malam hingga motif complongan muncul utuh.', 'Pelepasan Malam', 'https://i.pinimg.com/736x/eb/41/6f/eb416f442874f1808bc9a057065cde35.jpg'],
            ['6. Kurasi & Sertifikasi Mutu', 'Pengecekan kualitas tenun, kerapian jarum, dan pemberian label sertifikat keaslian wastra Paoman.', 'Sertifikasi Paoman', 'https://awsimages.detik.net.id/api/wm/2022/10/02/batik-indramayu_169.jpeg?wid=60&w=1200&v=1&t=jpeg'],
        ];
        foreach ($steps as $i => $s) {
            $n = $i + 1;
            $rows[] = ['proses', "proses_{$n}_title", "Tahap {$n} judul", 'text', $s[0], 10 + $n * 4];
            $rows[] = ['proses', "proses_{$n}_desc", "Tahap {$n} deskripsi", 'textarea', $s[1], 11 + $n * 4];
            $rows[] = ['proses', "proses_{$n}_tag", "Tahap {$n} label", 'text', $s[2], 12 + $n * 4];
            $rows[] = ['proses', "proses_{$n}_image", "Tahap {$n} foto", 'image', $s[3], 13 + $n * 4];
        }

        $rows = array_merge($rows, [
            // MOTIF header
            ['motif', 'motif_eyebrow', 'Label atas', 'text', 'MOTIF IKONIK PAOMAN', 1],
            ['motif', 'motif_heading', 'Judul', 'text', 'Koleksi Motif Autentik Pesisir Dermayon', 2],
            ['motif', 'motif_desc', 'Deskripsi', 'textarea', 'Temukan keindahan motif bahari khas Indramayu yang sarat akan makna filosofi laut, doa kelimpahan rezeki, dan ketangguhan hidup masyarakat pesisir.', 3],
            // GALERI header
            ['galeri', 'galeri_eyebrow', 'Label atas', 'text', 'GALERI WASATRA', 1],
            ['galeri', 'galeri_heading', 'Judul', 'text', 'Harmoni Tradisi & Gaya Hidup Modern', 2],
            ['galeri', 'galeri_desc', 'Deskripsi', 'textarea', 'Klik pada setiap foto untuk melihat detail tekstur rintik jarum complongan dan aplikasi busana kontemporer.', 3],
            // CTA
            ['cta', 'cta_tag', 'Label atas', 'text', 'GERAKAN PELESTARIAN BUDAYA', 1],
            ['cta', 'cta_heading', 'Judul (boleh HTML)', 'html', 'Kenali Warisan. <br>Hidupkan Budaya.', 2],
            ['cta', 'cta_desc', 'Deskripsi', 'textarea', 'Jadilah bagian dari langkah mulia merawat keagungan batik Indramayu. Dapatkan koleksi wastra otentik dan kenakan kebanggaan nusantara ke mana pun Anda melangkah.', 3],
            // KONTAK & FOOTER
            ['kontak', 'kontak_alamat', 'Alamat sentra', 'text', 'Sentra Batik Paoman, Jl. Siliwangi No. 45, Indramayu, Jawa Barat', 1],
            ['kontak', 'kontak_email', 'Email', 'text', 'halo@batikbinar.id', 2],
            ['kontak', 'kontak_telepon', 'Telepon/WA', 'text', '+62 821-2345-6789', 3],
            ['kontak', 'footer_tentang', 'Teks footer brand', 'textarea', 'Platform resmi pelestarian dan perwujudan modern batik pesisir Dermayon, Indramayu. Menghidupkan kembali teknik complongan dan motif maritim autentik.', 4],
        ]);

        foreach ($rows as [$section, $key, $label, $type, $value, $order]) {
            SiteContent::firstOrCreate(['key' => $key], compact('section', 'label', 'type', 'value', 'order'));
        }
        SiteContent::flush();
    }
}
