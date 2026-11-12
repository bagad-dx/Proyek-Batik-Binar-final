<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('title', 'BATIK BINAR - Warisan Batik Indramayu')</title>
  <meta name="description" content="Backend Batik Binar - Warisan Batik Indramayu Untuk Generasi Masa Kini">
  @include('layouts.theme-css')
  <link rel="icon" type="image/png" href="{{ asset('assets/logo-batik-binar.png') }}">
  <style>
    .backend-main { padding: 0 0 60px; min-height: 70vh; }
    .main-pad { padding-top: 130px; }
    .role-pill { display:inline-block; padding:2px 12px; border-radius:9999px; font-size:.75rem; font-weight:700; background:var(--color-primary-light); color:var(--color-primary); }
    .role-pill.admin { background:#E1F2FE; color:#0D70B9; }
    .role-pill.superadmin { background:#FEE2E2; color:#B91C1C; }
    .flash-box { margin-bottom:18px; padding:12px 18px; border-radius:16px; font-size:.9rem; }
    .flash-success { background:#ECFDF5; border:1px solid #6EE7B7; color:#065F46; }
    .flash-error { background:#FEF2F2; border:1px solid #FCA5A5; color:#991B1B; }
    .backend-table { width:100%; border-collapse:collapse; font-size:.9rem; }
    .backend-table th { text-align:left; padding:10px 12px; background:var(--color-primary-light); color:var(--color-primary); }
    .backend-table td { padding:10px 12px; border-top:1px solid var(--color-border-light); vertical-align:top; }
    .backend-card { background:var(--color-card-bg); border:1px solid var(--color-border-light); border-radius:var(--radius-lg); box-shadow:var(--shadow-md); padding:24px; }
    .backend-actions { display:flex; gap:10px; flex-wrap:wrap; margin:18px 0; }
    /* ===== Area ribbon: pembeda panel (satu tema royal blue website) ===== */
    .area-ribbon { color:#fff; font-size:.82rem; font-weight:700; letter-spacing:.12em; text-transform:uppercase; }
    .area-ribbon .container { display:flex; align-items:center; gap:10px; padding-top:10px; padding-bottom:10px; }
    .area-ribbon.admin { background:linear-gradient(90deg, var(--color-primary-dark), var(--color-accent)); }
    .area-ribbon.superadmin { background:linear-gradient(90deg, var(--color-primary-dark), var(--color-accent)); }
    .area-ribbon .dot { width:9px; height:9px; border-radius:50%; background:#fff; box-shadow:0 0 0 4px rgba(255,255,255,.25); }
    /* ===== Satu tema: tidak ada override marun, samakan warna website ===== */
    /* ===== Tema ADMIN: biru royal bawaan frontend + aksen sian ===== */
    body.theme-admin .site-header { border-bottom:3px solid var(--color-accent); }
    .login-badge { display:inline-block; padding:4px 14px; border-radius:9999px; font-size:.75rem; font-weight:800; letter-spacing:.1em; margin-bottom:10px; }
    .login-badge.admin { background:#E1F2FE; color:#0D70B9; }
    .login-badge.superadmin { background:#FEE2E2; color:#B91C1C; }
    /* ===== Tema ADMIN/SUPERADMIN: ruang ekstra untuk ribbon di header fixed ===== */
    body.theme-admin .main-pad, body.theme-superadmin .main-pad { padding-top: 158px; }
    .site-header .area-ribbon { margin-top: 10px; }
    .area-ribbon .container { flex-wrap: wrap; row-gap: 4px; }
    @media (max-width: 640px) {
      .area-ribbon { font-size: .7rem; letter-spacing: .06em; }
      body.theme-admin .main-pad, body.theme-superadmin .main-pad { padding-top: 172px; }
    }
  </style>
</head>
<body>
  {{-- Samakan token native (localStorage) dengan sesi web agar halaman native
    tidak menampilkan akun lama yang masih tersimpan di browser. --}}
  @if(session('web_token'))
  <script>try{localStorage.setItem('binar_token', @json(session('web_token')));localStorage.removeItem('binar_user');}catch(e){}</script>
  @endif
  {{-- Splash persis frontend awal, hanya di homepage --}}
  @if(request()->routeIs('home'))
  <div class="splash-screen" id="splashScreen">
    <div class="splash-ripple-bg"></div>
    <div class="splash-content">
      <div class="splash-logo-wrapper">
        <img src="{{ asset('assets/logo-batik-binar.png') }}" alt="Logo Resmi Batik Binar" class="splash-logo-img">
      </div>
      <p class="splash-tagline">“Melestarikan Warisan, Menghidupkan Budaya”</p>
      <div class="splash-progress-bar"><div class="splash-progress-fill"></div></div>
      <button class="splash-skip-btn" id="splashSkipBtn" type="button">Lewati & Masuk &rarr;</button>
    </div>
  </div>
  @endif
  <header class="site-header" id="siteHeader">
    <div class="container nav-container">
      <a href="{{ route('home') }}" class="brand-logo" aria-label="Beranda Batik Binar">
        <img src="{{ asset('assets/logo-batik-binar.png') }}" alt="Batik Binar" class="brand-logo-img">
        <div class="brand-logo-text">
          <span class="brand-name">BATIK BINAR</span>
          <span class="brand-tag">Dermayon Heritage</span>
        </div>
      </a>
      <nav>
        <ul class="nav-menu" id="navMenu">
          <li><a href="{{ route('home') }}#beranda" class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}">Home</a></li>
          <li><a href="{{ route('home') }}#tentang" class="nav-link">Tentang</a></li>
          <li><a href="{{ route('home') }}#proses" class="nav-link">Proses</a></li>
          <li><a href="{{ route('koleksi.index') }}" class="nav-link {{ request()->routeIs('koleksi.*') ? 'active' : '' }}">Koleksi</a></li>
          <li><a href="{{ route('home') }}#galeri" class="nav-link">Galeri</a></li>
          <li><a href="{{ route('shop.index') }}" class="nav-link {{ request()->routeIs('shop.*', 'user.checkout', 'user.checkout.*', 'user.catalog', 'user.orders.*') ? 'active' : '' }}">Belanja</a></li>
        </ul>
      </nav>
      <div class="nav-actions">
        @auth
          <a href="{{ route('user.orders.index') }}" class="nav-icon-btn" aria-label="Notifikasi" title="Notifikasi" style="position:relative;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>
            @if(!empty($unreadNotif))<span class="nb-cart-count" style="position:absolute;top:-4px;right:-4px;">{{ $unreadNotif }}</span>@endif
          </a>
          @php
            $navAv = auth()->user()->avatar;
            $navAvUrl = $navAv ? (str_starts_with($navAv, 'http') ? $navAv : asset('storage/'.$navAv)) : null;
          @endphp
          <a href="{{ route('user.dashboard') }}" title="Dashboard saya" style="display:inline-flex;align-items:center;gap:8px;">
            @if($navAvUrl)<img src="{{ $navAvUrl }}" alt="" style="width:38px;height:38px;border-radius:50%;object-fit:cover;border:2px solid #fff;box-shadow:0 4px 12px rgba(21,42,136,.25);">
            @else<span style="width:38px;height:38px;border-radius:50%;background:linear-gradient(135deg,#152A88,#1687D9);color:#fff;font-weight:800;display:inline-flex;align-items:center;justify-content:center;font-size:1rem;border:2px solid #fff;box-shadow:0 4px 12px rgba(21,42,136,.25);">{{ strtoupper(substr(auth()->user()->name ?? auth()->user()->username ?? '?', 0, 1)) }}</span>@endif
            <b style="font-size:.88rem;">{{ auth()->user()->name }}</b>
          </a>
          <form method="POST" action="{{ route('logout') }}" style="display:inline">@csrf
            <button type="submit" class="btn-pill btn-pill-royal" style="padding:8px 18px; font-size:.85rem;"><span>Logout</span></button>
          </form>
        @else
          <a href="{{ route('login') }}" class="btn-pill btn-pill-blue" style="padding:8px 18px; font-size:.88rem;">
            <span>Login</span>
            <div class="btn-arrow-circle" style="width:22px;height:22px;">
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
            </div>
          </a>
        @endauth
      </div>
      <button class="menu-toggle-btn" id="menuToggleBtn" aria-label="Buka menu navigasi" type="button"><span></span><span></span><span></span></button>
    </div>
  </header>
  <div class="mobile-backdrop" id="mobileBackdrop"></div>

  <main class="backend-main">
    @hasSection('fullwidth')
      @yield('fullwidth')
      <div class="container main-pad">
        @if(session('success'))<div class="flash-box flash-success">{{ session('success') }}</div>@endif
        @if(session('error'))<div class="flash-box flash-error">{{ session('error') }}</div>@endif
        @yield('content')
      </div>
    @else
    <div class="container main-pad">
      @if(session('success'))<div class="flash-box flash-success">{{ session('success') }}</div>@endif
      @if(session('error'))<div class="flash-box flash-error">{{ session('error') }}</div>@endif
      @if($errors->any())
        <div class="flash-box flash-error"><ul style="margin-left:18px;list-style:disc;">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
      @endif
      @yield('content')
    </div>
    @endif
  </main>

  <footer class="site-footer">
    <div class="container">
      <div class="footer-top-grid">
        <div class="footer-brand">
          <img src="{{ asset('assets/logo-batik-binar.png') }}" alt="Batik Binar Logo">
          <p>{{ site_content('footer_tentang', 'Platform resmi pelestarian dan perwujudan modern batik pesisir Dermayon, Indramayu. Menghidupkan kembali teknik complongan dan motif maritim autentik.') }}</p>
        </div>
        <div class="footer-col">
          <h4>Navigasi</h4>
          <ul class="footer-links-list">
            <li><a href="{{ route('home') }}">Home</a></li>
            <li><a href="{{ route('koleksi.index') }}">Koleksi</a></li>
            <li><a href="{{ route('user.catalog') }}">Belanja</a></li>
            <li><a href="{{ route('user.orders.index') }}">Pesanan Saya</a></li>
          </ul>
        </div>
        <div class="footer-col">
          <h4>Akses Akun</h4>
          <ul class="footer-links-list">
            <li><a href="{{ route('login') }}">Masuk (Login)</a></li>
            <li><a href="{{ route('register') }}">Pendaftaran Akun</a></li>
          </ul>
        </div>
        <div class="footer-col">
          <h4>Sentra & Kontak</h4>
          <div class="footer-contact-row"><span>{{ site_content('kontak_alamat', 'Sentra Batik Paoman, Jl. Kopral Yahya No.120, Paoman, Kec. Indramayu, Kabupaten Indramayu, Jawa Barat 45211') }}</span></div>
          <div class="footer-contact-row"><span>{{ site_content('kontak_email', 'halo@batikbinar.id') }}</span></div>
          <div class="footer-contact-row"><span>{{ site_content('kontak_telepon', '+62 821-2345-6789') }}</span></div>
        </div>
      </div>
      <div class="footer-bottom">
        <p>&copy; 2026 Batik Binar Indonesia. Seluruh hak cipta dilindungi.</p>
      </div>
    </div>
  </footer>
  @include('components.chat-widget')
  <script src="{{ asset('script.js') }}?v={{ filemtime(public_path('script.js')) }}"></script>
</body>
</html>
