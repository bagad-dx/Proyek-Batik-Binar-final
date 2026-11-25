<?php

require_once dirname(__DIR__) . '/native/database.php';

$search = trim((string) ($_GET['q'] ?? ''));
$jenisId = filter_input(INPUT_GET, 'jenis', FILTER_VALIDATE_INT);
$jenisId = $jenisId && $jenisId > 0 ? $jenisId : null;

$where = ['p.status = :status'];
$bindings = ['status' => 'aktif'];

if ($jenisId !== null) {
    $where[] = 'p.jenis_id = :jenis_id';
    $bindings['jenis_id'] = $jenisId;
}

if ($search !== '') {
    $where[] = '(p.nama LIKE :search_name OR p.deskripsi LIKE :search_description OR p.motif LIKE :search_motif OR p.bahan LIKE :search_bahan)';
    $searchTerm = '%' . $search . '%';
    $bindings['search_name'] = $searchTerm;
    $bindings['search_description'] = $searchTerm;
    $bindings['search_motif'] = $searchTerm;
    $bindings['search_bahan'] = $searchTerm;
}

$products = batikFetchAll(
    'SELECT p.id, p.nama, p.deskripsi, p.harga, p.stok, p.gambar, p.motif,
            p.bahan, p.ukuran, p.teknik, p.jenis_id,
            k.nama AS kategori_nama, j.label_display AS jenis_label
     FROM produk AS p
     LEFT JOIN kategori AS k ON k.id = p.category_id
     LEFT JOIN jenis_produk AS j ON j.id = p.jenis_id
     WHERE ' . implode(' AND ', $where) . '
     ORDER BY p.created_at DESC',
    $bindings
);
$jenisProduk = batikFetchAll(
    'SELECT id, label_display FROM jenis_produk ORDER BY id',
);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Belanja Batik | BATIK BINAR</title>
  <meta name="description" content="Belanja koleksi batik khas Indramayu di Batik Binar.">
  <link rel="canonical" href="https://batikbinar.id/belanja.php">
  <link rel="stylesheet" href="css/01-base.css">
  <link rel="stylesheet" href="css/03-header.css">
  <link rel="stylesheet" href="css/04-buttons.css">
  <link rel="stylesheet" href="css/05-home.css">
  <link rel="stylesheet" href="css/06-footer.css">
  <link rel="stylesheet" href="css/08-components.css">
  <link rel="stylesheet" href="css/09-shop.css">
  <link rel="stylesheet" href="css/14-animations.css">
  <link rel="stylesheet" href="css/15-responsive.css">
  <link rel="stylesheet" href="css/16-native.css">
  <link rel="icon" type="image/png" href="assets/logo-batik-binar.png">
</head>
<body>
  <a class="skip-link" href="#main-content">Lewati ke konten utama</a>

  <header class="site-header" id="siteHeader">
    <div class="container nav-container">
      <a href="home.php" class="brand-logo" aria-label="Beranda Batik Binar">
        <img src="assets/logo-batik-binar.png" width="682" height="1024" alt="Batik Binar" class="brand-logo-img">
        <div class="brand-logo-text">
          <span class="brand-name">BATIK BINAR</span>
          <span class="brand-tag">Dermayon Heritage</span>
        </div>
      </a>
      <nav>
        <ul class="nav-menu" id="navMenu">
          <li><a href="home.php#beranda" class="nav-link">Home</a></li>
          <li><a href="home.php#tentang" class="nav-link">Tentang</a></li>
          <li><a href="home.php#proses" class="nav-link">Proses</a></li>
          <li><a href="home.php#motif" class="nav-link">Koleksi</a></li>
          <li><a href="galeri.php" class="nav-link">Galeri</a></li>
          <li><a href="profile.php" class="nav-link">Profil</a></li>
          <li><a href="belanja.php" class="nav-link active">Belanja</a></li>
        </ul>
      </nav>
      <div class="nav-actions">
        <a href="dashboard.php" class="nav-icon-btn" aria-label="Dashboard">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
            <circle cx="12" cy="7" r="4"></circle>
          </svg>
        </a>
      </div>
      <button class="menu-toggle-btn" id="menuToggleBtn" aria-label="Buka menu navigasi" type="button">
        <span></span><span></span><span></span>
      </button>
    </div>
  </header>

  <div class="mobile-backdrop" id="mobileBackdrop"></div>

  <main id="main-content">
    <section class="shop-hero">
      <div class="container">
        <h1 class="shop-hero-title">Belanja Koleksi Batik Binar</h1>
        <p class="shop-hero-desc">Temukan mahakarya wastra pesisir Dermayon — mulai dari kain panjang klasik, selendang elegan, busana modern pria dan wanita, hingga aksesoris batik Indramayu.</p>
      </div>
    </section>

    <section class="shop-toolbar">
      <div class="container">
        <div class="shop-toolbar-inner">
          <form class="shop-search-box" method="get" action="belanja.php">
            <?php if ($jenisId !== null): ?>
              <input type="hidden" name="jenis" value="<?= $jenisId ?>">
            <?php endif; ?>
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="11" cy="11" r="8"></circle>
              <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
            </svg>
            <input type="search" name="q" id="shopSearchInput" class="shop-search-input" placeholder="Cari produk batik..." value="<?= batikEscape($search) ?>">
            <span id="shopSearchCount" class="shop-search-count"><?= count($products) ?> produk</span>
          </form>

          <div id="shopFilterPills" class="shop-filter-pills">
            <a class="shop-filter-btn<?= $jenisId === null ? ' active' : '' ?>" href="belanja.php<?= $search !== '' ? '?q=' . rawurlencode($search) : '' ?>">Semua Produk</a>
            <?php foreach ($jenisProduk as $jenis): ?>
              <?php
              $jenisLabel = (string) $jenis['label_display'];
              $query = ['jenis' => (int) $jenis['id']];
              if ($search !== '') {
                  $query['q'] = $search;
              }
              ?>
              <a class="shop-filter-btn<?= $jenisId === (int) $jenis['id'] ? ' active' : '' ?>" href="belanja.php?<?= batikEscape(http_build_query($query)) ?>"><?= batikEscape($jenisLabel) ?></a>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </section>

    <section class="shop-grid-section">
      <div class="container">
        <div id="shopGrid" class="shop-grid">
          <?php foreach ($products as $product): ?>
            <?php
            $productId = (int) $product['id'];
            $productName = (string) $product['nama'];
            $category = (string) ($product['jenis_label'] ?: ($product['kategori_nama'] ?: 'Batik'));
            $image = batikImageUrl($product['gambar'] ?? null, 'assets/bg-homepage.jpg');
            $specs = array_filter([
                $product['teknik'] ?? null,
                $product['bahan'] ?? null,
                $product['ukuran'] ?? null,
            ]);
            ?>
            <article class="shop-product-card" data-id="<?= $productId ?>">
              <a class="shop-product-img" href="produk.php?id=<?= $productId ?>">
                <img src="<?= batikEscape($image) ?>" alt="<?= batikEscape($productName) ?>" loading="lazy" style="height:32vh;">
                <span class="shop-product-badge"><?= batikEscape($category) ?></span>
              </a>
              <div class="shop-product-body">
                <div class="shop-product-category"><?= batikEscape($category) ?></div>
                <h2 class="shop-product-name"><?= batikEscape($productName) ?></h2>
                <?php if ($specs): ?>
                  <div class="shop-product-specs"><?= batikEscape(implode(' · ', $specs)) ?></div>
                <?php endif; ?>
                <div class="shop-product-price"><span class="price-currency">Rp</span> <?= number_format((float) $product['harga'], 0, ',', '.') ?></div>
                <div class="shop-product-footer">
                  <span class="shop-product-teknik">Stok <?= (int) $product['stok'] ?></span>
                  <a class="shop-product-detail-btn" href="produk.php?id=<?= $productId ?>" aria-label="Lihat <?= batikEscape($productName) ?>">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                      <line x1="5" y1="12" x2="19" y2="12"></line>
                      <polyline points="12 5 19 12 12 19"></polyline>
                    </svg>
                  </a>
                </div>
              </div>
            </article>
          <?php endforeach; ?>
        </div>

        <div id="shopEmpty" class="shop-empty" style="<?= $products ? 'display:none;' : 'display:block;' ?>">
          <h3>Produk Tidak Ditemukan</h3>
          <p>Coba gunakan kata kunci atau filter kategori yang berbeda.</p>
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
