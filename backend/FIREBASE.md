# Login Google via Firebase — Batik Binar

## Konsep penting: "token Firebase" dibuat otomatis
Tidak ada token yang dibuat manual. Alurnya:
1. User klik **Masuk dengan Google** → popup akun Google (Firebase JS SDK).
2. Firebase membuat **ID Token** (JWT, kedaluwarsa 1 jam) otomatis.
3. Token dikirim ke backend (`POST /auth/google`) → diverifikasi dengan kunci publik Google → user login/didaftarkan sebagai `user`.

Yang kamu ambil dari Firebase Console hanyalah **konfigurasi web** (apiKey dkk), bukan token.

## Langkah di Firebase Console (sekali saja)
1. Buka https://console.firebase.google.com → **Add project** (nama mis. `batik-binar`, Analytics boleh off) → Create.
2. Menu **Build → Authentication → Get started** → tab **Sign-in method** → **Google** → **Enable** → isi support email → **Save**.
3. Menu **Authentication → Settings → Authorized domains** → **Add domain** → `127.0.0.1` (wajib agar popup tidak error `auth/unauthorized-domain` saat `php artisan serve`). Tambahkan juga `localhost` bila perlu.
4. Menu **Project settings (ikon gerigi) → General → Your apps → Web (`</>`)** → nickname `batik-binar-web` → **Register app** → salin isi `firebaseConfig`:
   ```js
   { apiKey, authDomain, projectId, storageBucket, messagingSenderId, appId }
   ```

## Isi ke backend `.env`
```env
FIREBASE_API_KEY=AIzaSy....
FIREBASE_AUTH_DOMAIN=batik-binar.firebaseapp.com
FIREBASE_PROJECT_ID=batik-binar
FIREBASE_STORAGE_BUCKET=batik-binar.appspot.com
FIREBASE_MESSAGING_SENDER_ID=123456789
FIREBASE_APP_ID=1:123456789:web:abcdef...
```
Lalu `php artisan config:clear`. Tidak perlu service-account JSON — verifikasi memakai kunci publik Google (di-cache 1 jam).

## Perilaku
- Email Google yang belum terdaftar → otomatis dibuatkan akun `user` (username dari email, password acak tak terpakai).
- Email yang sudah terdaftar (mis. `bagad@...`) → akun lama **ditautkan** (kolom `google_id`), bisa login via password maupun Google.
- Akun banned/nonaktif tetap ditolak. Google login selalu masuk sebagai `user` (tidak bisa naik role).
- API: `POST /api/auth/google` body `{ "idToken": "..." }` → balikan `{ user, token }` (Sanctum).

## Troubleshooting
| Pesan | Sebab & solusi |
|---|---|
| `FIREBASE_PROJECT_ID belum diisi` | `.env` belum diisi / belum `config:clear` |
| `auth/unauthorized-domain` | domain belum ditambah di Authorized domains |
| `popup-closed-by-user` | user menutup popup, normal |
| `Email Google belum terverifikasi` | akun Google tak terverifikasi (jarang) |
| 401 `Tanda tangan token tidak valid` | jam server meleset / token basi — ulangi login |
