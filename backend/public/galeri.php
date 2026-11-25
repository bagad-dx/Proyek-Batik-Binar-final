<?php
require_once dirname(__DIR__) . '/native/database.php';

$galleryMotifs = batikFetchAll(
    'SELECT nama FROM motif WHERE is_published = 1 ORDER BY nama'
);
$galleryTags = batikFetchAll(
    'SELECT label FROM tag_galeri ORDER BY label'
);
$galleryRows = batikFetchAll(
    'SELECT g.*, m.nama AS motif_nama
     FROM galeri AS g
     LEFT JOIN motif AS m ON m.id = g.motif_id
     WHERE g.is_published = 1
     ORDER BY g.created_at DESC'
);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Galeri Visual | BATIK BINAR</title>
  <meta name="description" content="Galeri visual Batik Binar. Jelajahi koleksi motif batik, proses pembuatan, detail tekstur, busana modern, dan sentra Paoman Indramayu.">
  <link rel="canonical" href="https://batikbinar.id/galeri.php">
  <meta property="og:title" content="Galeri Visual | BATIK BINAR">
  <meta property="og:description" content="Galeri visual Batik Binar. Jelajahi koleksi motif batik, proses pembuatan, detail tekstur, busana modern, dan sentra Paoman Indramayu.">
  <meta property="og:image" content="assets/og-batik-binar.jpg">
  <meta property="og:type" content="website">
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="Galeri Visual | BATIK BINAR">
  <meta name="twitter:description" content="Galeri visual Batik Binar. Jelajahi koleksi motif batik, proses pembuatan, detail tekstur, busana modern, dan sentra Paoman Indramayu.">
  <meta name="twitter:image" content="assets/og-batik-binar.jpg">
    <link rel="stylesheet" href="css/01-base.css">
  <link rel="stylesheet" href="css/03-header.css">
  <link rel="stylesheet" href="css/04-buttons.css">
  <link rel="stylesheet" href="css/06-footer.css">
  <link rel="stylesheet" href="css/08-components.css">
  <link rel="stylesheet" href="css/10-gallery.css">
  <link rel="stylesheet" href="css/11-overlays.css">
  <link rel="stylesheet" href="css/14-animations.css">
  <link rel="stylesheet" href="css/15-responsive.css">
  <link rel="icon" type="image/png" href="assets/logo-batik-binar.png">
</head>
<body>
  <a class="skip-link" href="#main-content">Lewati ke konten utama</a>

    <!-- header dan navbar -->
  <header class="site-header" id="siteHeader">
    <div class="container nav-container">
      <!-- Brand Logo using Official Logo Image -->
      <a href="index.html" class="brand-logo">
        <img src="assets/logo-batik-binar.png" width="682" height="1024" alt="Batik Binar" class="brand-logo-img">
        <div class="brand-logo-text">
          <span class="brand-name">BATIK BINAR</span>
          <span class="brand-tag">Dermayon Heritage</span>
        </div>
      </a>
      <nav>
        <ul class="nav-menu" id="navMenu">
          <li><a href="index.html#beranda" class="nav-link">Home</a></li>
          <li><a href="index.html#tentang" class="nav-link">Tentang</a></li>
          <li><a href="index.html#proses" class="nav-link">Proses</a></li>
          <li><a href="index.html#motif" class="nav-link">Koleksi</a></li>
          <li><a href="galeri.html" class="nav-link active">Galeri</a></li>
          <li><a href="profile.html" class="nav-link">Profil</a></li>
          <li><a href="belanja.php" class="nav-link">Belanja</a></li>
        </ul>
      </nav>
      <div class="nav-actions">
        <a href="dashboard.html" class="nav-icon-btn" aria-label="Dashboard" style="background-color: var(--color-accent); color: white;">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
            <circle cx="12" cy="7" r="4"></circle>
          </svg>
        </a>
        <a href="login.html" class="btn-pill btn-pill-blue" style="padding: 8px 18px; font-size: 0.88rem;">
          <span>Login</span>
          <div class="btn-arrow-circle" style="width: 22px; height: 22px;">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
              <line x1="5" y1="12" x2="19" y2="12"></line>
              <polyline points="12 5 19 12 12 19"></polyline>
            </svg>
          </div>
        </a>
      </div>
      <button class="menu-toggle-btn" id="menuToggleBtn" aria-label="Buka menu navigasi" type="button">
        <span></span><span></span><span></span>
      </button>
    </div>
  </header>

  <!-- Mobile Menu Backdrop -->
  <div class="mobile-backdrop" id="mobileBackdrop"></div>

  <main id="main-content">
    <!-- Gallery Hero Banner -->
    <section class="gallery-hero">
      <div class="container">
        <h1 class="gallery-hero-title">Galeri Visual Batik Binar</h1>
        <p class="gallery-hero-desc">
          Jelajahi koleksi visual, dari goresan canting maestro, teknik complongan legendaris, hingga aplikasi busana modern khas warisan pesisir Indramayu.
        </p>
        <!-- Search Bar -->
        <div class="gallery-search-box">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="11" cy="11" r="8"></circle>
            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
          </svg>
          <input type="text" id="galeriSearchInput" class="gallery-search-input" placeholder="Cari motif, busana..." autocomplete="off">
          <span class="gallery-search-count" id="galeriSearchCount">Memuat koleksi&hellip;</span>
        </div>
      </div>
    </section>

    <!-- Marquee / Walking Animation -->
    <div class="gallery-marquee-strip">
      <div class="gallery-marquee-track">
        <?php foreach (array_merge($galleryMotifs, $galleryMotifs) as $motif): ?>
          <span class="marquee-item"><?= batikEscape($motif['nama']) ?></span>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Filter Bar -->
    <div class="gallery-filter-bar">
      <button class="gallery-filter-btn active" data-filter="semua">Semua</button>
      <?php foreach ($galleryTags as $tag): ?>
        <button class="gallery-filter-btn" data-filter="<?= batikEscape($tag['label']) ?>"><?= batikEscape($tag['label']) ?></button>
      <?php endforeach; ?>
    </div>

    <!-- Gallery Grid -->
    <section class="gallery-page-section">
      <div class="container">
        <!-- ==========================================================================
             PANDUAN MENGGANTI FOTO GALERI:
             - Cukup ganti URL pada atribut: src="..." pada tag img di setiap kartu.
             - Anda juga bisa mengubah judul pada tag h4, tag span, serta data-desc="...".
             - Setiap foto di bawah sudah menggunakan gambar berbeda sesuai kategorinya.
             ========================================================================== -->
        <h2 class="sr-only">Koleksi Galeri Batik Binar</h2>
        <div id="galeriGrid" class="gallery-page-grid">
          <?php foreach ($galleryRows as $gallery):
            $tag = (string) ($gallery['tag'] ?: $gallery['kategori'] ?: 'Galeri');
            $title = (string) ($gallery['judul'] ?: $gallery['motif_nama'] ?: 'Galeri Batik Binar');
            $description = (string) ($gallery['filosofi'] ?: $gallery['deskripsi'] ?: '');
            $image = batikImageUrl($gallery['gambar'] ?? null, 'assets/bg-homepage.jpg');
            $year = !empty($gallery['tahun_pembuatan']) ? ' • ' . substr((string) $gallery['tahun_pembuatan'], 0, 4) : '';
          ?>
          <div class="gallery-page-card" data-category="<?= batikEscape($tag) ?>" data-title="<?= batikEscape($title) ?>" data-tag="<?= batikEscape($tag) ?>" data-desc="<?= batikEscape($description) ?>">
            <img src="<?= batikEscape($image) ?>" width="800" height="600" alt="<?= batikEscape($title) ?>" loading="lazy" decoding="async">
            <div class="gallery-page-overlay">
              <span class="gallery-page-tag"><?= batikEscape($tag . $year) ?></span>
              <h3 class="gallery-page-title"><?= batikEscape($title) ?></h3>
            </div>
          </div>
          <?php endforeach; ?>
          <?php if (!$galleryRows): ?>
            <p class="service-desc">Galeri belum memiliki koleksi yang dipublikasikan.</p>
          <?php endif; ?>
        </div>
        <!-- Empty State -->
        <div class="gallery-empty" id="galeriEmpty" style="display: none;">
          <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
            <circle cx="11" cy="11" r="8"></circle>
            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
          </svg>
          <h3>Tidak Ditemukan</h3>
          <p>Coba kata kunci lain atau reset filter pencarian Anda.</p>
        </div>
      </div>
    </section>
  </main>

  <!-- footer -->
  <footer class="site-footer">
    <div class="container">
      <div class="footer-top-grid">
        <div class="footer-brand">
          <img loading="lazy" decoding="async" src="assets/logo-batik-binar.png" width="682" height="1024" alt="Batik Binar Logo">
          <p>
            Platform resmi pelestarian dan perwujudan modern batik pesisir Dermayon, Indramayu. Menghidupkan kembali teknik complongan dan motif maritim autentik.
          </p>
          <div class="footer-social-links">
            <a href="https://www.instagram.com/bintangarutindramayu?stkn=MTF2ZGJvNXVnenlobQ==" class="social-btn" target="_blank" rel="noopener" aria-label="Instagram">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line></svg>
            </a>
            <a href="https://www.facebook.com/share/19sMPGLddk/" class="social-btn" target="_blank" rel="noopener" aria-label="Facebook">
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path></svg>
            </a>
            <a href="https://wa.me/6287870229801"
              class="social-btn"
              aria-label="WhatsApp"
              target="_blank" rel="noopener">

              <svg viewBox="0 0 24 24"
                  fill="none"
                  stroke="currentColor"
                  stroke-width="2"
                  width="20"
                  height="20">
                <path d="M20.5 3.5A11.8 11.8 0 0 0 12.1 0C5.6 0 .3 5.3.3 11.8c0 2.1.5 4.1 1.6 5.9L.2 24l6.5-1.7a11.8 11.8 0 0 0 5.4 1.3h.1c6.5 0 11.8-5.3 11.8-11.8 0-3.1-1.2-6.1-3.5-8.3z"/>
                <path d="M8.2 6.8c-.3-.7-.6-.7-.9-.7h-.8c-.3 0-.7.1-.9.4-.3.3-1.2 1.1-1.2 2.7s1.2 3.1 1.4 3.3c.2.2 2.3 3.7 5.7 5 .8.3 1.4.5 1.9.6.8.1 1.5.1 2 .1.6-.1 1.8-.7 2.1-1.4.3-.7.3-1.3.2-1.4-.1-.1-.3-.2-.7-.4l-2.1-1c-.3-.1-.6-.2-.8.2-.2.3-.8 1-1 1.2-.2.2-.4.2-.8.1-.4-.2-1.5-.6-2.8-1.8-1-.9-1.7-2-1.9-2.4-.2-.3 0-.5.2-.7.2-.2.4-.4.5-.6.2-.2.2-.4.3-.6.1-.2 0-.4-.1-.6z"/>
              </svg>

            </a>
          </div>
        </div>

        <div class="footer-col">
          <h3>Navigasi</h3>
          <ul class="footer-links-list">
            <li><a href="#beranda">Home</a></li>
            <li><a href="#tentang">Tentang Binar</a></li>
            <li><a href="#proses">Tahapan Proses</a></li>
            <li><a href="#motif">Koleksi Motif</a></li>
            <li><a href="galeri.html">Galeri Visual</a></li>
            <li><a href="profile.html">Profil</a></li>
            <li><a href="belanja.php">Belanja Batik</a></li>
          </ul>
        </div>

        <div class="footer-col">
          <h3>Akses Akun</h3>
          <ul class="footer-links-list">
            <li><a href="login.html">Masuk (Login)</a></li>
            <li><a href="register.html">Pendaftaran Akun</a></li>
            <li><a href="#">Sertifikat Keaslian</a></li>
            <li><a href="#">Panduan Perawatan</a></li>
            <li><a href="#">Kebijakan Privasi</a></li>
          </ul>
        </div>

        <div class="footer-col">
          <h3>Sentra & Kontak</h3>
          <div class="footer-contact-row">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
            <span>Sentra Batik Paoman, Jl. Kopral Yahya No.120, Paoman, Kec. Indramayu, Kabupaten Indramayu, Jawa Barat 45211</span>
          </div>
          <div class="footer-contact-row">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
            <span>halo@batikbinar.id</span>
          </div>
          <div class="footer-contact-row">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
            <span>+62 8787-0229-801</span>
          </div>
        </div>
      </div>

      <div class="footer-bottom">
        <p>&copy; 2026 Batik Binar Indonesia. Seluruh hak cipta dilindungi.</p>
        <div class="footer-bottom-links">
          <a href="#">Syarat & Ketentuan</a>
          <a href="#">Kebijakan Privasi</a>
        </div>
      </div>
    </div>
  </footer>

  <!-- lightbox modal -->
  <div class="modal-backdrop" id="previewModal" role="dialog" aria-modal="true">
    <div class="modal-container">
      <button class="modal-close-btn" id="modalCloseBtn" aria-label="Tutup preview modal" type="button">&times;</button>
      <div class="modal-image-wrapper">
        <img src="" width="800" height="600" alt="Pratinjau Batik Binar" id="modalImage">
      </div>
      <div class="modal-body">
        <div class="modal-tag" id="modalTag">Batik Paoman</div>
        <h3 class="modal-title" id="modalTitle">Judul Motif</h3>
        <p class="modal-description" id="modalDesc">Deskripsi detail filosofis karya batik.</p>
      </div>
    </div>
  </div>

  <!-- Search Overlay Modal -->
  <div class="search-overlay" id="searchOverlay">
    <div class="search-overlay-container">
      <div class="search-overlay-header">
        <h3>Cari Produk Batik Binar</h3>
        <button class="search-overlay-close" id="searchOverlayClose" type="button">&times;</button>
      </div>
      <div class="search-overlay-input-wrap">
        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
          <circle cx="11" cy="11" r="8"></circle>
          <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
        </svg>
        <input type="text" id="indexSearchInput" class="search-overlay-input" placeholder="Cari motif, busana, kain, aksesoris..." autocomplete="off">
      </div>
      <div class="search-overlay-results" id="indexSearchResults">
        <p class="search-overlay-hint">Ketik nama produk untuk mulai mencari...</p>
      </div>
    </div>
  </div>

  <script src="script.js"></script>
  <script src="native-api.js?v=15"></script>
</body>
</html>
