<!DOCTYPE html>
<html lang="id">
<head><meta charset="UTF-8"><title>Kode OTP Batik Binar</title></head>
<body style="font-family:Arial,sans-serif;background:#f4f1ea;padding:24px;">
  <div style="max-width:520px;margin:auto;background:#fff;border-radius:16px;padding:32px;border-top:6px solid #152A88;">
    <h2 style="color:#152A88;margin-top:0;">Halo, {{ $name }}!</h2>
    <p>Terima kasih telah mendaftar di <b>BATIK BINAR</b>. Masukkan kode OTP berikut untuk mengaktifkan akun Anda:</p>
    <div style="text-align:center;margin:24px 0;">
      <span style="font-size:2.2rem;font-weight:800;letter-spacing:10px;color:#152A88;background:#f4f1ea;padding:12px 24px;border-radius:12px;display:inline-block;">{{ $code }}</span>
    </div>
    <p>Kode berlaku <b>10 menit</b>. Jangan berikan kode ini ke siapa pun.</p>
    <p style="color:#888;font-size:.85rem;">Jika Anda tidak merasa mendaftar, abaikan email ini.</p>
  </div>
</body>
</html>
