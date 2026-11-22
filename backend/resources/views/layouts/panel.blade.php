<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('title', 'Panel Admin | BATIK BINAR')</title>
  @include('layouts.theme-css')
  <link rel="stylesheet" href="{{ asset('css/admin-panel.css') }}?v={{ filemtime(public_path('css/admin-panel.css')) }}">
  <link rel="icon" type="image/png" href="{{ asset('assets/logo-batik-binar.png') }}">
</head>
@php
  $isSuper = request()->is('panel/superadmin*');
  $role = auth()->user()->role ?? null;
  // Judul per seksi panel (dipakai sebagai judul topbar).
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

  {{-- ===== SIDEBAR (mengikuti referensi FrontEnd/admin/admin.html) ===== --}}
  <aside class="sidebar" id="sidebar">
    <div class="sidebar-logo">
      <div class="sidebar-logo-top">
        <img src="{{ asset('assets/logo-batik-binar.png') }}" alt="Batik Binar" class="sidebar-logo-img">
        <div class="sidebar-logo-text">Batik Binar<span>Pesisir Indramayu</span></div>
      </div>
      <span class="admin-badge">{{ $isSuper ? 'Superadmin Panel' : 'Admin Panel' }}</span>
    </div>
    <nav class="sidebar-nav">
      <div class="nav-label">Menu Utama</div>
      <a href="{{ route('admin.dashboard') }}" class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"><span class="nav-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/></svg></span>Dashboard</a>
      <a href="{{ route('admin.products.index') }}" class="nav-item {{ request()->routeIs('admin.products.*') ? 'active' : '' }}"><span class="nav-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg></span>Produk</a>
      <a href="{{ route('admin.galleries.index') }}" class="nav-item {{ request()->routeIs('admin.galleries.*') ? 'active' : '' }}"><span class="nav-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg></span>Galeri</a>
      <a href="{{ route('admin.motifs.index') }}" class="nav-item {{ request()->routeIs('admin.motifs.*') ? 'active' : '' }}"><span class="nav-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 2 7 12 12 22 7 12 2"/><polyline points="2 17 12 22 22 17"/><polyline points="2 12 12 17 22 12"/></svg></span>Motif</a>
      <a href="{{ route('admin.orders.index') }}" class="nav-item {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}"><span class="nav-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg></span>Pesanan</a>
      <a href="{{ route('admin.chats.index') }}" class="nav-item {{ request()->routeIs('admin.chats.*') ? 'active' : '' }}"><span class="nav-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg></span>Chat</a>
      <div class="nav-label" style="margin-top:16px">Kelola</div>
      <a href="{{ route('admin.contents.index') }}" class="nav-item {{ request()->routeIs('admin.contents.*', 'admin.categories.*', 'admin.jenis.*', 'admin.settings.*') ? 'active' : '' }}"><span class="nav-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg></span>Konten</a>
      <a href="{{ route('admin.jenis.index') }}" class="nav-item {{ request()->routeIs('admin.jenis.*') ? 'active' : '' }}"><span class="nav-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg></span>Jenis</a>
      <a href="{{ route('admin.otp.index') }}" class="nav-item {{ request()->routeIs('admin.otp.*') ? 'active' : '' }}"><span class="nav-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg></span>OTP</a>
      @if($role === 'superadmin')
      <div class="nav-label" style="margin-top:16px">Sistem</div>
      <a href="{{ route('superadmin.users.index') }}" class="nav-item {{ request()->routeIs('superadmin.users.*') ? 'active' : '' }}"><span class="nav-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg></span>User</a>
      <a href="{{ route('superadmin.chatbot.index') }}" class="nav-item {{ request()->routeIs('superadmin.chatbot.*') ? 'active' : '' }}"><span class="nav-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg></span>Chatbot</a>
      @endif
    </nav>
    <a href="{{ route('home') }}" target="_blank" rel="noopener" class="sidebar-back">&#8592; Kembali ke Situs</a>
    <div class="sidebar-user">
      <div class="sidebar-user-info">
        <div class="sidebar-avatar">{{ strtoupper(mb_substr(auth()->user()->name ?? 'A', 0, 1)) }}</div>
        <div>
          <div class="sidebar-user-name">{{ auth()->user()->name ?? 'Admin' }}</div>
          <div class="sidebar-user-email">{{ auth()->user()->email ?? '' }}</div>
        </div>
      </div>
      <form method="POST" action="{{ route('logout') }}">@csrf
        <button type="submit" class="btn-logout">Keluar / Logout</button>
      </form>
    </div>
  </aside>
  <div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

  {{-- ===== MAIN ===== --}}
  <div class="admin-main">
    <header class="topbar">
      <div class="topbar-left">
        <button class="hamburger" onclick="toggleSidebar()" aria-label="Toggle Menu">&#9776;</button>
        <div class="topbar-title">{{ $heroTitle }}</div>
      </div>
      <div class="topbar-right">
        <div class="topbar-clock">
          <strong id="clockTime">--:--:--</strong>
          <span id="clockDate">-- --- ----</span>
        </div>
        <a href="{{ route('admin.chats.index') }}" class="topbar-bell" aria-label="Chat Penjual" title="Chat Penjual">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>
        </a>
        <div class="topbar-avatar">{{ strtoupper(mb_substr(auth()->user()->name ?? 'A', 0, 1)) }}</div>
      </div>
    </header>

    <main class="admin-content">
      @if(session('success'))<div class="flash-box flash-success">{{ session('success') }}</div>@endif
      @if(session('error'))<div class="flash-box flash-error">{{ session('error') }}</div>@endif
      @if(isset($errors) && $errors->any())
        <div class="flash-box flash-error"><ul style="margin-left:18px;list-style:disc;">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
      @endif
      @yield('content')
      <div class="panel-footer">&copy; 2026 Batik Binar Indonesia • Panel Pengelola</div>
    </main>
  </div>

  <script src="{{ asset('js/admin-panel.js') }}?v={{ filemtime(public_path('js/admin-panel.js')) }}"></script>
</body>
</html>
