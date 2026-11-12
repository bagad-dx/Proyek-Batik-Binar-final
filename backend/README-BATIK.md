# Backend Batik Binar (Laravel 13) — PROYEK/backend

## 1. Struktur
```
PROYEK/
  index.html, login.html, register.html, style.css, script.js (frontend statis lama)
  frontend-api-example.js (contoh fetch ke API)
  backend/ (Laravel)
```

## 2. Role & Hak Akses
| Role | Akses Blade | Akses API |
|---|---|---|
| `user` | `/user/dashboard`, `/user/katalog`, checkout, pesanan sendiri | `POST /api/orders`, `GET /api/my-orders` |
| `admin` | Semua user + `/admin/dashboard`, CRUD produk/galeri/motif/kategori, kelola pesanan | `GET /api/admin/check` |
| `superadmin` | Semua admin + `/superadmin/dashboard`, CRUD user/admin, aktif-nonaktif akun | `GET /api/superadmin/check` |

Middleware: `role:user,admin,superadmin` (`app/Http/Middleware/EnsureRole.php`, alias di `bootstrap/app.php`).

## 3. Cara jalan (SQLite, tanpa setting)
```powershell
cd "E:\Tugas kulyeah\SEMESTER 3\Proyek1\PROYEK\backend"
php artisan migrate --seed
php artisan serve   # http://127.0.0.1:8000
```

Akun demo (password: `password`, kecuali akun bagad* = `123`):
- Web utama (user saja): `bagad` di `/login` (admin/superadmin DITOLAK di sini)
- Panel pengelola `/panel/login`: `bagadadmin` (admin), `bagadsuperadmin` (superadmin)

## 4. Endpoint penting
Web: `/`, `/login`, `/register`, `/dashboard` (auto-redirect per role)
API publik: `POST /api/register`, `POST /api/login`, `GET /api/products`, `GET /api/galleries`, `GET /api/motifs`
API token (Sanctum): `GET /api/me`, `POST /api/orders`, `GET /api/my-orders`

## 5. Database — MySQL XAMPP `batik_binar`
Koneksi `.env`: `DB_CONNECTION=mysql`, host `127.0.0.1:3306`, user `root` (tanpa password).
Tabel (bahasa Indonesia): `pengguna`, `kategori`, `produk`, `galeri`, `motif`, `pesanan`,
`pengaturan_chat`, `faq_chat`, `konten_website` (+ tabel bawaan framework).
File gambar tetap di `storage/app/public/` (koleksi 50 file utuh).

### Catatan Oracle
Kamu memilih Oracle, tapi PHP di laptop ini belum ada ekstensi `oci8` (`php -m` tidak menampilkan oci8/pdo_oci),
jadi backend default memakai SQLite agar langsung jalan untuk demo/dosen.
Kalau kampus mewajibkan Oracle:
1. Install Oracle XE + Oracle Instant Client, aktifkan `extension=oci8` di `php.ini` (XAMPP).
2. `composer require yajra/laravel-oci8`
3. Tambah koneksi `oracle` di `config/database.php` + set `.env`: `DB_CONNECTION=oracle`, `DB_HOST`, `DB_PORT=1521`, `DB_DATABASE=XE`, `DB_USERNAME`, `DB_PASSWORD`.
4. Migrasi ulang. Skema di `database/migrations/2026_09_24_*` kompatibel (hindari tipe enum di Oracle → ganti string bila perlu).
Alternatif ringan yang biasanya diterima dosen: ganti ke MySQL XAMPP (`DB_CONNECTION=mysql` di `.env`).

## 6. Upload gambar
`php artisan storage:link` sudah dijalankan. File tersimpan di `storage/app/public/{products,galleries,motifs}`.
