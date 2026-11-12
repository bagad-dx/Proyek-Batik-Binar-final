{{-- Halaman 404 Batik Binar — desain sama dengan situs (navy + motif) --}}
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Halaman Tidak Ditemukan | BATIK BINAR</title>
  <meta name="description" content="Halaman yang Anda cari tidak ditemukan di Batik Binar. Kembali ke beranda atau jelajahi galeri batik tulis Paoman.">
  <link rel="icon" type="image/png" href="/assets/logo-batik-binar.png">
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
      font-family: 'Plus Jakarta Sans', system-ui, -apple-system, 'Segoe UI', sans-serif;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 24px;
      color: #fff;
      background:
        radial-gradient(circle at 15% 20%, rgba(22, 135, 217, 0.35) 0%, transparent 45%),
        radial-gradient(circle at 85% 80%, rgba(30, 59, 179, 0.5) 0%, transparent 50%),
        linear-gradient(135deg, #0B1754 0%, #152A88 55%, #0B5FA5 100%);
    }
    main { text-align: center; max-width: 560px; }
    img { width: 96px; height: 96px; border-radius: 50%; object-fit: contain; padding: 4px; border: 3px solid rgba(255,255,255,.85); margin-bottom: 20px; background: #fff; }
    .code { font-size: 6rem; font-weight: 800; letter-spacing: -0.03em; line-height: 1; }
    h1 { font-size: 1.6rem; font-weight: 800; margin: 12px 0; }
    p { font-size: 1rem; line-height: 1.7; color: rgba(255,255,255,.85); margin-bottom: 28px; }
    .actions { display: flex; gap: 12px; justify-content: center; flex-wrap: wrap; }
    a { display: inline-block; padding: 13px 26px; border-radius: 9999px; font-weight: 700; text-decoration: none; font-size: .95rem; }
    a.primary { background: #fff; color: #152A88; }
    a.ghost { border: 2px solid rgba(255,255,255,.6); color: #fff; }
    a:hover { transform: translateY(-2px); }
    a:focus-visible { outline: 3px solid #fff; outline-offset: 3px; }
  </style>
</head>
<body>
  <main>
    <img src="/assets/logo-batik-binar.png" alt="Logo Batik Binar" width="96" height="96">
    <div class="code" aria-hidden="true">404</div>
    <h1>Halaman Tidak Ditemukan</h1>
    <p>Maaf, halaman yang Anda cari tidak ada atau sudah dipindahkan. Mungkin tautannya sudah berubah, atau Anda salah mengetik alamatnya. Mari kembali menjelajahi warisan batik tulis Paoman.</p>
    <div class="actions">
      <a class="primary" href="/">Kembali ke Beranda</a>
      <a class="ghost" href="/galeri.html">Jelajahi Galeri</a>
    </div>
  </main>
</body>
</html>
