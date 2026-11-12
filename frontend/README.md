# BATIK BINAR — Frontend terpisah (native, tanpa framework)

Folder ini adalah salinan mandiri halaman native (`*.html`, `css/`, `assets/`,
`script.js`, `native-api.js`). Backend Laravel tetap di `../backend` (API + panel).

## Cara jalan (2 proses)

1. **Backend (API)** — MySQL XAMPP `batik_binar` harus nyala:
   ```powershell
   cd ..\backend
   php artisan serve --port=8000
   ```
2. **Frontend (statis)** — server apa saja, mis. VS Code Live Server atau:
   ```powershell
   cd frontend
   python -m http.server 5500
   # buka http://127.0.0.1:5500/index.html
   ```

## Konfigurasi

- `config.js` → `window.BINAR_API_BASE` = alamat backend
  (default `http://127.0.0.1:8000`). Frontend memanggil
  `BINAR_API_BASE + /api/...` dengan token Bearer (localStorage).
- Kosongkan (`''`) jika file html diserve gabung oleh `backend/public`
  (mode lama satu proses) — bridge otomatis pakai path relatif.
- CORS sudah dibuka di `backend/config/cors.php` + middleware
  `HandleCors` di `backend/bootstrap/app.php`, jadi fetch lintas-port lolos.

## Catatan

- Gambar produk: `BINAR_API_BASE + /storage/...` otomatis.
- Login web-session (Sanctum cookie) hanya aktif di mode gabung;
  mode pisah memakai token API (login.html sudah begitu).
- Jangan edit referensi `FrontEnd/PROYEK` — itu arsip murni.
