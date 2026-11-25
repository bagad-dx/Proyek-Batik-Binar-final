<?php
require_once dirname(__DIR__) . '/native/database.php';

$productId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$productId = $productId && $productId > 0 ? $productId : 0;
$productRows = batikFetchAll(
    'SELECT p.*, k.nama AS kategori_nama, j.label_display AS jenis_label
     FROM produk AS p
     LEFT JOIN kategori AS k ON k.id = p.category_id
     LEFT JOIN jenis_produk AS j ON j.id = p.jenis_id
     WHERE p.id = :id AND p.status = :status
     LIMIT 1',
    ['id' => $productId, 'status' => 'aktif']
);
$product = $productRows[0] ?? null;
$relatedProducts = $product
    ? batikFetchAll(
        'SELECT id, nama, harga, gambar
         FROM produk
         WHERE status = :status AND id <> :id AND category_id = :category_id
         ORDER BY created_at DESC
         LIMIT 4',
        ['status' => 'aktif', 'id' => $product['id'], 'category_id' => $product['category_id']]
    )
    : [];
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Detail Produk | BATIK BINAR</title>
  <meta name="description" content="Detail produk batik Batik Binar Indramayu.">
  <link rel="canonical" href="https://batikbinar.id/produk.php">
  <meta property="og:title" content="Detail Produk | BATIK BINAR">
  <meta property="og:description" content="Detail produk batik Batik Binar Indramayu.">
  <meta property="og:image" content="assets/og-batik-binar.jpg">
  <meta property="og:type" content="website">
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="Detail Produk | BATIK BINAR">
  <meta name="twitter:description" content="Detail produk batik Batik Binar Indramayu.">
  <meta name="twitter:image" content="assets/og-batik-binar.jpg">
  <link rel="stylesheet" href="css/01-base.css">
  <link rel="stylesheet" href="css/03-header.css">
  <link rel="stylesheet" href="css/04-buttons.css">
  <link rel="stylesheet" href="css/05-home.css">
  <link rel="stylesheet" href="css/06-footer.css">
  <link rel="stylesheet" href="css/08-components.css">
  <link rel="stylesheet" href="css/09-shop.css">
  <link rel="stylesheet" href="css/12-dashboard.css">
  <link rel="stylesheet" href="css/14-animations.css">
  <link rel="stylesheet" href="css/15-responsive.css">
  <link rel="stylesheet" href="css/16-native.css">
  <link rel="icon" type="image/png" href="assets/logo-batik-binar.png">
</head>
<body>
  <a class="skip-link" href="#main-content">Lewati ke konten utama</a>

  <header class="site-header" id="siteHeader">
    <div class="container nav-container">
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
          <li><a href="galeri.html" class="nav-link">Galeri</a></li>
          <li><a href="profile.html" class="nav-link">Profil</a></li>
          <li><a href="belanja.php" class="nav-link active">Belanja</a></li>
        </ul>
      </nav>
      <div class="nav-actions">
        <a href="dashboard.html" class="nav-icon-btn" aria-label="Dashboard" style="background-color: var(--color-accent); color: white;">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
            <circle cx="12" cy="7" r="4"></circle>
          </svg>
        </a>
      </div>
      <button class="menu-toggle-btn" id="menuToggleBtn" aria-label="Buka menu navigasi" type="button">
        <span></span>
        <span></span>
        <span></span>
      </button>
    </div>
  </header>

  <div class="mobile-backdrop" id="mobileBackdrop"></div>

  <main id="main-content">
    <section class="hero-wrapper nb-slim">
      <div class="container" style="text-align:center;position:relative;z-index:3;">
        <span class="hero-tagline-capsule">BATIK BINAR • DERMAYON</span>
        <h1 class="hero-heading">Detail Produk</h1>
        <p class="hero-desc" style="margin:0 auto;">Spesifikasi lengkap wastra pilihan Anda.</p>
      </div>
      <div class="hero-bottom-curve"><svg viewBox="0 0 1200 120" preserveAspectRatio="none"><path d="M0,0 C300,90 900,90 1200,0 L1200,120 L0,120 Z"></path></svg></div>
    </section>
    <section class="shop-grid-section">
      <div class="container" style="max-width:1080px;">
        <p class="service-meta-text" id="pdCrumb" style="margin-bottom:14px;">Belanja / <b id="pdCrumbName">Produk</b></p>
        <div id="pdWrap">
          <?php if ($product): ?>
            <?php $productImage = batikImageUrl($product['gambar'] ?? null, 'assets/bg-homepage.jpg'); ?>
            <article class="service-card">
              <div class="service-img-box"><img src="<?= batikEscape($productImage) ?>" alt="<?= batikEscape($product['nama']) ?>"></div>
              <div class="service-body">
                <span class="service-meta-text"><?= batikEscape($product['jenis_label'] ?? $product['kategori_nama'] ?? 'Batik Binar') ?></span>
                <h2 class="service-title"><?= batikEscape($product['nama']) ?></h2>
                <p class="service-desc">Rp <?= number_format((float) $product['harga'], 0, ',', '.') ?></p>
                <p><?= nl2br(batikEscape($product['deskripsi'] ?? '')) ?></p>
                <p class="service-meta-text">Motif: <?= batikEscape($product['motif'] ?? '-') ?> · Stok: <?= (int) $product['stok'] ?></p>
                <button class="btn-pill btn-pill-blue" id="pdBuyBtn" type="button" data-id="<?= (int) $product['id'] ?>">Beli sekarang</button>
              </div>
            </article>
          <?php else: ?>
            <p class="service-desc">Produk tidak ditemukan atau tidak tersedia.</p>
          <?php endif; ?>
        </div>
        <div class="services-header" style="margin-top:36px;">
          <span class="section-eyebrow-pill">TERKAIT</span>
          <h2 class="section-main-heading" style="font-size:1.4rem;">Produk Sejenis</h2>
        </div>
        <div class="services-grid-6" id="pdRelated" style="grid-template-columns:repeat(4,1fr);">
          <?php foreach ($relatedProducts as $related): ?>
            <?php $relatedImage = batikImageUrl($related['gambar'] ?? null, 'assets/bg-homepage.jpg'); ?>
            <a class="service-card" href="produk.php?id=<?= (int) $related['id'] ?>">
              <div class="service-img-box"><img src="<?= batikEscape($relatedImage) ?>" alt="<?= batikEscape($related['nama']) ?>"></div>
              <div class="service-body">
                <h3 class="service-title"><?= batikEscape($related['nama']) ?></h3>
                <p class="service-desc">Rp <?= number_format((float) $related['harga'], 0, ',', '.') ?></p>
              </div>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
    </section>
  </main>

  <footer class="site-footer">
    <div class="container">
      <div class="footer-bottom">
        <p>&copy; 2026 Batik Binar Indonesia. Seluruh hak cipta dilindungi.</p>
      </div>
    </div>
  </footer>

  <script src="script.js"></script>
  <script src="native-api.js?v=15"></script>
</body>
</html>
