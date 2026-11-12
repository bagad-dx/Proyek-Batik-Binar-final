<?php

namespace Database\Seeders;

use App\Models\ChatFaq;
use App\Models\ChatSetting;
use Illuminate\Database\Seeder;

class ChatbotSeeder extends Seeder
{
    public function run(): void
    {
        foreach (\App\Models\ChatSetting::DEFAULTS as $key => $value) {
            ChatSetting::firstOrCreate(['key' => $key], ['value' => $value]);
        }

        $faqs = [
            [
                'pertanyaan' => 'Jam operasional & garansi',
                'keywords' => 'jam buka, jam operasional, garansi, retur, tukar',
                'jawaban' => 'Toko buka <b>Senin–Sabtu 09.00–17.00 WIB</b>. Garansi tukar jika barang cacat/rusak saat tiba (lapor maks. 2 hari dengan foto).',
                'urutan' => 1,
            ],
            [
                'pertanyaan' => 'Promo',
                'keywords' => 'promo, diskon, potongan, voucher, gratis ongkir',
                'jawaban' => 'Promo mengikuti info terbaru di halaman <b>Belanja</b> dan galeri. Saat ini ongkir flat mulai Rp 10.000 via POS Indonesia!',
                'urutan' => 2,
            ],
        ];
        foreach ($faqs as $f) {
            ChatFaq::firstOrCreate(['pertanyaan' => $f['pertanyaan']], $f + ['is_active' => true]);
        }
    }
}
