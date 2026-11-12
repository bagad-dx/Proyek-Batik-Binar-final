<?php

namespace Database\Seeders;

use App\Models\Motif;
use App\Models\User;
use Illuminate\Database\Seeder;

class KoleksiDriveSeeder extends Seeder
{
    public function run(): void
    {
        $adminId = User::where('role', 'admin')->value('id')
            ?? User::where('role', 'superadmin')->value('id');

        // [Nama Motif, file di storage/app/public/koleksi/]
        $items = [
            ['Ayam Alas', 'ayam-alas.png'], ['Banji Tepakk', 'banji-tepakk.png'],
            ['Banji', 'banji.png'], ['Bokong Semar', 'bokong-semar.png'],
            ['Cendrawasi', 'cendrawasi.png'], ['Ganggeng', 'ganggeng.png'],
            ['Iiwak Petek', 'iiwak-petek.png'], ['Iwakk Etong', 'iwakk-etong.png'],
            ['Jae Sarempang Kandang', 'jae-sarempang-kandang.png'], ['Jae Serampang', 'jae-serampang.png'],
            ['Jati Rombeng', 'jati-rombeng.png'], ['Kaapal Kandas', 'kaapal-kandas.png'],
            ['Kaawung', 'kaawung.png'], ['Kayuu Gorda', 'kayuu-gorda.png'],
            ['Kemban Beetah', 'kemban-beetah.png'], ['Kembang Gunda', 'kembang-gunda.png'],
            ['Kembang Kol', 'kembang-kol.png'], ['Kembang Pete', 'kembang-pete.png'],
            ['Kembang Suket', 'kembang-suket.png'], ['Kembang Kapas', 'kemmbang-kapas.png'],
            ['Kereta Kencana', 'kereta-kencana.png'], ['Lasem Urang', 'lasem-urang.png'],
            ['Lokcan', 'lokcan.png'], ['Manuk Bengkuk', 'manuk-bengkuk.png'],
            ['Manuk Drawes', 'manuk-drawes.png'], ['Merak Berunding', 'merak-berunding.png'],
            ['Merak Ngibing', 'merak-ngibing.png'], ['Obar Abir', 'oobar-abir.png'],
            ['Pacar Cina', 'pacar-cina.png'], ['Parang Teja', 'parang-teja.png'],
            ['Pentil Kuista', 'pentil-kuista.png'], ['Puyong', 'puyong.png'],
            ['Rajeg Wesi', 'rajeg-wesi.png'], ['Rama', 'rama.png'],
            ['Sawat Biskuit', 'sawat-biskuit.png'], ['Sawat Pentil Kuista', 'sawat-pentil-kuista.png'],
            ['Sawat Riwog', 'sawat-riwog.png'], ['Sawat Pengantin', 'sawatt-pengantin.png'],
            ['Sawet Riweh', 'sawet-riweh.png'], ['Sejuring', 'sejuring.png'],
            ['Sekar Niiyem', 'sekar-niiyem.png'], ['Siisik', 'siisik.png'],
            ['Srikit', 'srikit.png'], ['Srintil', 'srintil.png'],
            ['Sunggingan Manuk Tetingkring', 'sunggingan-manuk-tetingkring.png'], ['Swastika Sidomukti', 'swastika-sidomukti.png'],
            ['Teluki', 'teluki.png'], ['Teratai', 'teratai.png'],
            ['Tiga Negri', 'tiga-negri.png'], ['Udan Liris', 'udan-liris.png'],
        ];

        $norm = fn ($s) => strtolower(preg_replace('/[^a-z]/i', '', $s));
        $existing = Motif::all()->keyBy(fn ($m) => $norm($m->nama));

        foreach ($items as [$nama, $file]) {
            $data = [
                'user_id' => $adminId,
                'asal' => 'Paoman, Indramayu',
                'filosofi' => "Motif {$nama} khas pesisir Dermayon, Indramayu — hasil alih rupa wastra asli Batik Binar dengan teknik Tulis Complongan.",
                'lama_pengerjaan' => '40 Hari',
                'teknik' => 'Tulis Complongan',
                'gambar' => 'koleksi/' . $file,
                'is_published' => true,
            ];
            if (isset($existing[$norm($nama)])) {
                $existing[$norm($nama)]->update(['gambar' => $data['gambar']]);
            } else {
                Motif::firstOrCreate(['nama' => $nama], $data);
            }
        }
    }
}
