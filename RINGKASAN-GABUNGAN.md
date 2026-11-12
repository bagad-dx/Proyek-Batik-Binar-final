# PROYEK-GABUNGAN — Batik Binar (PROYEK + fitur Proyek Ver Raka)

Hasil penggabungan: basis **PROYEK** (Laravel + frontend native) ditambah
fitur yang hanya ada di **Proyek Ver Raka** (native PHP + tabel `tb_*`).
Database `batik_binar` di XAMPP MySQL sudah ada → **tidak diimport ulang**,
hanya ditambah yang kurang (lihat `..\merge_raka.sql`).

## Isi folder ini (satu proyek gabungan)

- `backend/` — Laravel (termasuk `vendor/`, siap jalan)
- `frontend/` — halaman native (belanja, galeri, produk, dashboard, ...)
- `index.html`, `login.html`, `register.html`, `script.js`, `style.css` — salinan lama (tidak diubah)

## Yang digabung dari Raka → PROYEK

### Database (`batik_binar`, via `..\merge_raka.sql`, idempotent)

| Asal Raka | Hasil di PROYEK |
|---|---|
| `tb_jenis_produk` (5 jenis + `label_display`) | tabel baru `jenis_produk` (5 baris, cermin kategori) + `produk.jenis_id` |
| kolom `tb_etalase`: `ukuran`, `bahan`, `teknik` | `produk.ukuran`, `produk.teknik` terisi untuk 50 produk (`bahan` sudah ada) |
| `tb_tag_galeri` (5 tag) | tabel baru `tag_galeri` (5 baris) + `galeri.tag` |
| `tb_galeri`: `filosofi`, `tahun_pembuatan`, relasi motif | `galeri.filosofi`, `galeri.tahun_pembuatan`, `galeri.motif_id` + 50 baris dokumentasi filosofi (galeri 4 → 54) |
| `tb_pemesanan` + `tb_detail_pemesanan` (header–detail) | tabel baru `detail_pesanan`; 34 pesanan lama dilengkapi 1 baris detail; order/checkout baru selalu menulis detail |

### Backend Laravel

- Migrasi `2026_10_13_000001_merge_raka_features.php` (idempotent, sudah ter-apply)
- Model baru: `JenisProduk`, `TagGaleri`, `DetailPesanan`; relasi di `Product` (`jenis`), `Gallery` (`motif`), `Order` (`details`)
- Seeder `RakaMergeSeeder` (terdaftar di `DatabaseSeeder`, sudah dijalankan)
- API: `GET /api/jenis`, `GET /api/tags`; `/api/products` mendukung `?jenis_id=` / `?kategori=` (alias Raka) / `?q=` + cari di deskripsi; `/api/galleries` mendukung `?tag=` + cari filosofi + relasi motif; `myOrders`/dashboard menyertakan `details`
- `storeOrder`, `CartController@checkout`, `OrderController@store` (web) ikut menulis `detail_pesanan`
- Admin: form produk (+Ukuran, +Teknik, +Jenis), form galeri (+Filosofi, +Tahun, +Tag, +Motif), CRUD **Jenis Produk** baru (`/panel/admin/jenis`, link "Jenis" di panel)

### Frontend native (port kode PHP Raka → JS + API; diterapkan di `frontend/` dan `backend/public/`)

- `belanja.html` + `native-api.js`: pil filter dibaca live dari `/api/jenis` (dulu statis; di Raka dari `tb_jenis_produk`), URL `?jenis=` & `?q=` ala form GET Raka, pencarian mencakup deskripsi/ukuran/teknik, kartu + modal detail menampilkan Ukuran & Teknik
- `produk.html`: tabel spek +Ukuran, +Teknik
- `galeri.html`: marquee motif live dari `/api/motifs` (di Raka dari `tb_motif`), pil tag live dari `/api/tags` (di Raka dari `tb_tag_galeri`), grid live dari `/api/galleries` (filosofi + tahun + tag); gagal API → fallback statis lama tetap tampil
- Pemetaan kode PHP Raka: `komponen/database.php`+`koneksi.php` → `config.js` + `api()`; `fungsi.php numKeRupiah` → `rupiah()`; `belanja.php (?kategori/?q)` → `initNativeShop`; `galeri.php (marquee/tag)` → `initNativeGaleri`

## Cara jalan (lihat juga CARA-JALAN.txt)

1. Pastikan MySQL XAMPP aktif. DB `batik_binar` sudah termerge. Cadangan awal: `batik_binar.sql`.
2. `cd Proyek 1 gabung fitur\backend` lalu `php artisan serve --host=127.0.0.1 --port=8000`
3. Buka `http://127.0.0.1:8000/belanja.html`, `galeri.html`, `produk.html?id=5`, atau `frontend/belanja.html` (API base di `frontend/config.js`)
4. Jika DB di-reset dari `batik_binar.sql` lama: jalankan `..\merge_raka.sql` (via phpMyAdmin/MySQL) lalu `php artisan migrate --force` + `php artisan db:seed --class=Database\Seeders\RakaMergeSeeder --force`

## Verifikasi yang sudah dilakukan

- `merge_raka.sql` sukses: jenis 5, tag 5, detail 34, galeri 54, 50/50 produk ber-ukuran+teknik
- `php -l` 17 file PHP → OK; `node --check` 2 file JS → OK; `view:cache` Blade → OK
- `artisan migrate` + `RakaMergeSeeder` → OK; `GET /api/jenis`, `/api/tags`, `/api/products?jenis_id=1`, `/api/galleries?tag=Motif Batik` → 200 + data benar
- OTP real: `php artisan test --filter=OtpFlowTest` → 14 test lulus (email/WA terkirim, gagal kirim 502, cooldown 429, expiry 410, akun + token, halaman & simpan pengaturan admin)

## OTP pendaftaran (mode NYATA, bukan demo)

- `OtpService` mengirim kode nyata via SMTP Gmail atau WhatsApp Fonnte; tidak ada lagi `dev_code` di response dan kode tidak ditulis ke log.
- Kredensial diisi dari panel `/panel/admin/otp` (tabel `pengaturan_otp`) + tombol **Kirim Tes**; nilai `.env` dipakai bila panel kosong.
- Command uji: `php artisan otp:test <email/nomor> --channel=email|wa`.
- Cooldown kirim ulang 30 detik ditegakkan di server; kode di-hash, sekali pakai, maks 5x salah; `pengguna.email_verified_at` diisi setelah verifikasi.
- Panduan aktivasi: `CARA-SETTING-OTP.txt`.
