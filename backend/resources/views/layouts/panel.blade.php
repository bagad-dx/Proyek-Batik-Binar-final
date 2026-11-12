<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('title', 'Panel Admin | BATIK BINAR')</title>
  @include('layouts.theme-css')
  <link rel="icon" type="image/png" href="{{ asset('assets/logo-batik-binar.png') }}">
  <style>
    /* ===== Panel: disamakan dengan tema website (royal blue + motif batik).
       Admin & superadmin SATU tema; pembeda hanya label role di topbar. ===== */
    .panel-topbar {
      background-color: var(--color-primary-dark);
      background-image:
        linear-gradient(180deg, rgba(11, 23, 84, 0.88) 0%, rgba(21, 42, 136, 0.80) 100%),
        url("{{ asset('assets/bg-homepage.jpg') }}");
      background-size: auto, 500px auto;
      background-repeat: no-repeat, repeat;
      background-position: center, center top;
      color: #fff;
    }
    .panel-topbar .container { display:flex; align-items:center; gap:12px; padding-top:10px; padding-bottom:10px; flex-wrap:wrap; }
    .panel-brand { display:flex; align-items:center; gap:10px; font-weight:800; letter-spacing:.04em; margin-right:auto; }
    .panel-brand img { width:36px; height:36px; border-radius:50%; background:#fff; object-fit:cover; border:2px solid rgba(255,255,255,.8); }
    .panel-nav { display:flex; gap:8px; flex-wrap:wrap; }
    .panel-nav a { color:#fff; font-size:.82rem; font-weight:700; padding:7px 14px; border-radius:9999px; background:rgba(255,255,255,.14); }
    .panel-nav a:hover { background:rgba(255,255,255,.28); }
    .panel-nav a.active { background:#fff; color:var(--color-primary); }
    .backend-main { padding: 0 0 60px; min-height: 70vh; }
    .main-pad { padding-top: 26px; }
    .role-pill { display:inline-block; padding:2px 12px; border-radius:9999px; font-size:.75rem; font-weight:700; background:var(--color-primary-light); color:var(--color-primary); }
    .flash-box { margin-bottom:18px; padding:12px 18px; border-radius:16px; font-size:.9rem; }
    .flash-success { background:#ECFDF5; border:1px solid #6EE7B7; color:#065F46; }
    .flash-error { background:#FEF2F2; border:1px solid #FCA5A5; color:#991B1B; }
    .backend-table { width:100%; border-collapse:collapse; font-size:.9rem; }
    .backend-table th { text-align:left; padding:10px 12px; background:var(--color-primary-light); color:var(--color-primary); }
    .backend-table td { padding:10px 12px; border-top:1px solid var(--color-border-light); vertical-align:top; }
    .backend-card { background:var(--color-card-bg); border:1px solid var(--color-border-light); border-radius:var(--radius-lg); box-shadow:var(--shadow-md); padding:24px; }
    .backend-actions { display:flex; gap:10px; flex-wrap:wrap; margin:18px 0; }
    .panel-footer { text-align:center; font-size:.8rem; color:var(--color-text-muted); padding:24px; }
    /* Panel tanpa header fixed: hero tidak perlu ruang atas sebesar halaman website */
    .hero-wrapper.nb-slim.panel-hero { padding-top:44px; padding-bottom:96px; }
    .backend-main { background-color: transparent; }
  </style>
</head>
@php
  $isSuper = request()->is('panel/superadmin*');
  $role = auth()->user()->role ?? null;
  // Judul hero per seksi panel (disamakan gaya halaman website: hero + lengkung).
  $heroMap = [
    'admin.dashboard' => ['Dashboard Admin', 'Ringkasan & kontrol jual beli.'],
    'admin.products.*' => ['Produk', 'Kelola postingan jual beli batik.'],
    'admin.galleries.*' => ['Galeri', 'Kelola foto galeri website.'],
    'admin.motifs.*' => ['Motif', 'Kelola khasanah motif batik.'],
    'admin.categories.*' => ['Kategori', 'Kelola kategori produk.'],
    'admin.jenis.*' => ['Jenis Produk', 'Kelola filter jenis belanja.'],
    'admin.orders.*' => ['Pesanan', 'Kelola pesanan pelanggan.'],
    'admin.settings.*' => ['Pengaturan Toko', 'Tautan toko & info jual beli.'],
    'admin.otp.*' => ['Pengaturan OTP', 'Kredensial Email & WhatsApp untuk verifikasi pendaftaran.'],
    'admin.chats.*' => ['Chat Penjual', 'Balas pesan pelanggan.'],
    'admin.contents.*' => ['Konten Website', 'Kelola teks & gambar website.'],
    'superadmin.dashboard' => ['Dashboard Superadmin', 'Kelola pengguna & sistem.'],
    'superadmin.users.*' => ['Pengguna', 'Kelola user, admin & banned.'],
    'superadmin.chatbot.*' => ['Chatbot', 'Kelola FAQ & asisten Binar.'],
  ];
  $heroTitle = 'Panel Pengelola';
  $heroDesc = 'Kelola Batik Binar.';
  foreach ($heroMap as $pattern => [$t, $d]) {
    if (request()->routeIs($pattern)) { $heroTitle = $t; $heroDesc = $d; break; }
  }
@endphp
<body class="panel">
  {{-- Samakan token native (localStorage) dengan sesi panel. --}}
  @if(session('web_token'))
  <script>try{localStorage.setItem('binar_token', @json(session('web_token')));localStorage.removeItem('binar_user');}catch(e){}</script>
  @endif
  <div class="panel-topbar">
    <div class="container">
      <a href="{{ $role === 'superadmin' ? route('superadmin.dashboard') : route('admin.dashboard') }}" class="panel-brand">
        <img src="{{ asset('assets/logo-batik-binar.png') }}" alt="Batik Binar">
        <span>BATIK BINAR • {{ $isSuper ? 'SUPERADMIN' : 'ADMIN' }}</span>
      </a>
      <nav class="panel-nav">
        @if($role === 'admin' || $role === 'superadmin')
          <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">Dashboard</a>
          <a href="{{ route('admin.products.index') }}" class="{{ request()->routeIs('admin.products.*') ? 'active' : '' }}">Produk</a>
          <a href="{{ route('admin.galleries.index') }}" class="{{ request()->routeIs('admin.galleries.*') ? 'active' : '' }}">Galeri</a>
          <a href="{{ route('admin.motifs.index') }}" class="{{ request()->routeIs('admin.motifs.*') ? 'active' : '' }}">Motif</a>
          <a href="{{ route('admin.orders.index') }}" class="{{ request()->routeIs('admin.orders.*') ? 'active' : '' }}">Pesanan</a>
          <a href="{{ route('admin.chats.index') }}" class="{{ request()->routeIs('admin.chats.*') ? 'active' : '' }}">Chat</a>
          <a href="{{ route('admin.contents.index') }}" class="{{ request()->routeIs('admin.contents.*', 'admin.categories.*', 'admin.jenis.*', 'admin.settings.*') ? 'active' : '' }}">Konten</a>
          <a href="{{ route('admin.jenis.index') }}" class="{{ request()->routeIs('admin.jenis.*') ? 'active' : '' }}">Jenis</a>
          <a href="{{ route('admin.otp.index') }}" class="{{ request()->routeIs('admin.otp.*') ? 'active' : '' }}">OTP</a>
        @endif
        @if($role === 'superadmin')
          <a href="{{ route('superadmin.users.index') }}" class="{{ request()->routeIs('superadmin.users.*') ? 'active' : '' }}">User</a>
          <a href="{{ route('superadmin.chatbot.index') }}" class="{{ request()->routeIs('superadmin.chatbot.*') ? 'active' : '' }}">Chatbot</a>
        @endif
        <a href="{{ route('home') }}" target="_blank" rel="noopener">Lihat Website</a>
      </nav>
      @auth
        <span class="role-pill" style="background:rgba(255,255,255,.9);">{{ auth()->user()->username }} • {{ auth()->user()->role }}</span>
        <form method="POST" action="{{ route('logout') }}" style="display:inline;">@csrf
          <button type="submit" style="background:#fff;color:var(--color-primary);font-weight:800;font-size:.8rem;padding:7px 16px;border-radius:9999px;">Logout</button>
        </form>
      @endauth
    </div>
  </div>

  {{-- Hero ala halaman website (home -> tentang): motif batik + lengkung transisi --}}
  <section class="hero-wrapper nb-slim panel-hero">
    <div class="container" style="text-align:center;position:relative;z-index:3;">
      <span class="hero-tagline-capsule">BATIK BINAR • {{ $isSuper ? 'SUPERADMIN' : 'ADMIN' }}</span>
      <h1 class="hero-heading">{{ $heroTitle }}</h1>
      <p class="hero-desc" style="margin:0 auto;">{{ $heroDesc }}</p>
    </div>
    <div class="hero-bottom-curve"><svg viewBox="0 0 1200 120" preserveAspectRatio="none"><path d="M0,0 C300,90 900,90 1200,0 L1200,120 L0,120 Z"></path></svg></div>
  </section>

  <main class="backend-main">
    <div class="container main-pad">
      @if(session('success'))<div class="flash-box flash-success">{{ session('success') }}</div>@endif
      @if(session('error'))<div class="flash-box flash-error">{{ session('error') }}</div>@endif
      @if($errors->any())
        <div class="flash-box flash-error"><ul style="margin-left:18px;list-style:disc;">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
      @endif
      @yield('content')
    </div>
  </main>

  <div class="panel-footer">&copy; 2026 Batik Binar Indonesia • Panel Pengelola</div>
</body>
</html>
