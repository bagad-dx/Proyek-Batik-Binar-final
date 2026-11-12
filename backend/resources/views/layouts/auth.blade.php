<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'BATIK BINAR')</title>
  @include('layouts.theme-css')
  <link rel="icon" type="image/png" href="{{ asset('assets/logo-batik-binar.png') }}">
  <style>
    /* Background gradasi + motif batik seperti dashboard */
    .auth-gradient-page {
      background-image:
        linear-gradient(135deg, rgba(11, 23, 84, 0.94) 0%, rgba(21, 42, 136, 0.90) 45%, rgba(30, 59, 179, 0.88) 80%, rgba(22, 135, 217, 0.92) 100%),
        url("{{ asset('assets/bg-homepage.jpg') }}");
      background-size: auto, 520px auto;
      background-repeat: no-repeat, repeat;
      background-position: center, center top;
      background-attachment: fixed, fixed;
    }
    .login-badge { display:inline-block; padding:4px 14px; border-radius:9999px; font-size:.75rem; font-weight:800; letter-spacing:.1em; margin-bottom:10px; }
    .login-badge.admin { background:#E1F2FE; color:#0D70B9; }
    .login-badge.superadmin { background:#FEE2E2; color:#B91C1C; }
    .auth-flash { position:fixed; top:18px; left:50%; transform:translateX(-50%); z-index:9999; padding:12px 22px; border-radius:16px; font-size:.9rem; box-shadow:0 10px 30px rgba(0,0,0,.2); max-width:90vw; }
    .auth-flash.success { background:#ECFDF5; border:1px solid #6EE7B7; color:#065F46; }
    .auth-flash.error { background:#FEF2F2; border:1px solid #FCA5A5; color:#991B1B; }
    /* Satu tema website: tanpa override warna per role */
    /* ===== Navbar website di halaman auth: brand kiri, pencarian + hamburger kanan ===== */
    .header-auth { position:absolute; top:0; left:0; right:0; z-index:900; padding:14px 0; background:transparent; }
    .header-auth .nav-container { display:flex; align-items:center; gap:14px; }
    .header-auth .brand-name { color:#fff; }
    .header-auth .brand-tag { color:rgba(255,255,255,.8); }
    .header-auth .nav-actions { margin-left:auto; display:flex; align-items:center; gap:10px; }
    .header-auth .nav-icon-btn { color:#fff; width:38px; height:38px; border-radius:50%; display:flex; align-items:center; justify-content:center; background:rgba(255,255,255,.15); backdrop-filter:blur(8px); }
    .header-auth .nav-icon-btn:hover { background:var(--color-accent); }
    .header-auth .menu-toggle-btn { display:flex; }
    .header-auth .menu-toggle-btn span { background:#fff; }
    .header-auth .nav-menu { display:none; position:absolute; top:100%; right:16px; background:#fff; border-radius:16px; box-shadow:0 20px 45px rgba(11,23,84,.25); padding:12px; min-width:210px; list-style:none; flex-direction:column; gap:4px; }
    .header-auth .nav-menu.open { display:flex; }
    .header-auth .nav-menu .nav-link { background:none; color:var(--color-primary); box-shadow:none; display:block; text-align:left; }
    .header-auth .nav-menu .nav-link:hover { background:var(--color-primary-light); transform:none; }
    .auth-searchbar { display:none; position:absolute; top:100%; left:0; right:0; padding:10px 24px; z-index:899; }
    .auth-searchbar.open { display:block; }
    .auth-searchbar form { max-width:520px; margin:0 auto; display:flex; gap:8px; background:rgba(255,255,255,.96); border-radius:9999px; padding:6px 6px 6px 20px; box-shadow:0 12px 30px rgba(11,23,84,.3); }
    .auth-searchbar input { flex:1; border:none; outline:none; background:transparent; font-size:.9rem; min-width:0; }
    .auth-searchbar button { background:var(--color-accent); color:#fff; font-weight:800; font-size:.85rem; border-radius:9999px; padding:9px 22px; }
  </style>
</head>
<body>
  {{-- Logout web ikut membersihkan token native di browser ini. --}}
  @if(session('clear_token'))
  <script>try{localStorage.removeItem('binar_token');localStorage.removeItem('binar_user');}catch(e){}</script>
  @endif
  {{-- Navbar website: brand kiri, pencarian + hamburger kanan --}}
  <header class="site-header header-auth" id="siteHeader">
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
          <li><a href="{{ route('home') }}" class="nav-link">Home</a></li>
          <li><a href="{{ route('home') }}#tentang" class="nav-link">Tentang</a></li>
          <li><a href="{{ route('home') }}#proses" class="nav-link">Proses</a></li>
          <li><a href="{{ route('koleksi.index') }}" class="nav-link">Koleksi</a></li>
          <li><a href="{{ route('home') }}#galeri" class="nav-link">Galeri</a></li>
          <li><a href="{{ route('shop.index') }}" class="nav-link">Belanja</a></li>
        </ul>
      </nav>
      <div class="nav-actions">
        <button class="nav-icon-btn" id="authSearchBtn" aria-label="Cari motif" type="button">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
        </button>
        <a href="{{ route('login') }}" class="btn-pill btn-pill-blue" style="padding:8px 18px; font-size:.88rem;">
          <span>Login</span>
          <div class="btn-arrow-circle" style="width:22px;height:22px;">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
          </div>
        </a>
      </div>
      <button class="menu-toggle-btn" id="menuToggleBtn" aria-label="Buka menu navigasi" type="button">
        <span></span><span></span><span></span>
      </button>
    </div>
    <div class="auth-searchbar" id="authSearchbar">
      <form action="{{ route('koleksi.index') }}" method="GET">
        <input type="text" name="search" placeholder="Cari motif batik, cth: merak..." autocomplete="off">
        <button type="submit">Cari</button>
      </form>
    </div>
  </header>
  <div class="mobile-backdrop" id="mobileBackdrop"></div>
  @if(session('success'))<div class="auth-flash success">{{ session('success') }}</div>@endif
  @if(session('error'))<div class="auth-flash error">{{ session('error') }}</div>@endif
  @if($errors->any())
    <div class="auth-flash error"><ul style="margin-left:18px;list-style:disc;">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
  @endif
  @yield('content')
  <script src="https://www.gstatic.com/firebasejs/10.12.2/firebase-app-compat.js"></script>
  <script src="https://www.gstatic.com/firebasejs/10.12.2/firebase-auth-compat.js"></script>
  <script>
    window.__FIREBASE__ = @json(config('services.firebase'));
    let __fbApp = null;
    function __fbInit() {
      const cfg = window.__FIREBASE__ || {};
      if (!cfg.api_key || !cfg.project_id) {
        showToast('Firebase belum dikonfigurasi. Isi FIREBASE_* di .env (lihat FIREBASE.md).', 'error');
        return null;
      }
      if (!__fbApp) {
        __fbApp = firebase.initializeApp({
          apiKey: cfg.api_key, authDomain: cfg.auth_domain, projectId: cfg.project_id,
          storageBucket: cfg.storage_bucket, messagingSenderId: cfg.messaging_sender_id, appId: cfg.app_id,
        });
      }
      return __fbApp;
    }
    async function __fbSendToken(idToken) {
      const res = await fetch("{{ route('auth.google') }}", {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json', 'Accept': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify({ idToken }),
      });
      const data = await res.json();
      if (!res.ok) throw new Error(data.message || 'Login Google gagal.');
      if (data.api_token) { try { localStorage.setItem('binar_token', data.api_token); localStorage.removeItem('binar_user'); } catch (e) {} }
      showToast('Login Google berhasil! Mengalihkan...', 'success');
      setTimeout(() => { window.location.href = data.redirect_url; }, 800);
    }
    function __fbFriendlyError(e) {
      const code = (e && e.code) || '';
      if (code === 'auth/internal-error') return 'Google menolak popup (biasanya cookie pihak ketiga diblokir / API key dibatasi). Mencoba mode redirect...';
      if (code === 'auth/unauthorized-domain') return 'Domain belum diizinkan: tambahkan 127.0.0.1 di Authentication → Settings → Authorized domains.';
      if (code === 'auth/popup-closed-by-user') return 'Popup ditutup. Silakan coba lagi.';
      if (code === 'auth/popup-blocked') return 'Popup diblokir browser. Izinkan popup untuk situs ini.';
      if (code === 'auth/operation-not-allowed') return 'Provider Google belum diaktifkan di Firebase Console (Authentication → Sign-in method → Google → Enable).';
      return (e && e.message) || 'Login Google dibatalkan/gagal.';
    }
    // Dipanggil tombol Google di halaman login/register.
    // Alur: popup Google -> ID Token (dibuat otomatis oleh Firebase) -> kirim ke backend.
    // Jika popup gagal (internal-error), otomatis fallback ke mode redirect.
    async function signInWithGooglePop(btn) {
      const app = __fbInit();
      if (!app) return;
      try {
        if (btn) btn.disabled = true;
        const cred = await app.auth().signInWithPopup(new firebase.auth.GoogleAuthProvider());
        await __fbSendToken(await cred.user.getIdToken());
      } catch (e) {
        if (e && e.code === 'auth/internal-error') {
          showToast(__fbFriendlyError(e), 'info');
          try { await app.auth().signInWithRedirect(new firebase.auth.GoogleAuthProvider()); return; }
          catch (e2) { showToast(__fbFriendlyError(e2), 'error'); }
        } else {
          showToast(__fbFriendlyError(e), 'error');
        }
        if (btn) btn.disabled = false;
      }
    }
    // Tangkap hasil mode redirect saat halaman dimuat ulang oleh Google.
    (function __fbCatchRedirect() {
      document.addEventListener('DOMContentLoaded', async () => {
        const cfg = window.__FIREBASE__ || {};
        if (!cfg.api_key || !cfg.project_id || typeof firebase === 'undefined') return;
        try {
          if (!__fbApp) {
            __fbApp = firebase.initializeApp({
              apiKey: cfg.api_key, authDomain: cfg.auth_domain, projectId: cfg.project_id,
              storageBucket: cfg.storage_bucket, messagingSenderId: cfg.messaging_sender_id, appId: cfg.app_id,
            });
          }
          const result = await __fbApp.auth().getRedirectResult();
          if (result && result.user) {
            showToast('Login Google berhasil! Mengalihkan...', 'success');
            await __fbSendToken(await result.user.getIdToken());
          }
        } catch (e) { showToast(__fbFriendlyError(e), 'error'); }
      });
    })();
  </script>
  <script>
    // Toggle bilah pencarian di navbar halaman auth
    document.addEventListener('DOMContentLoaded', () => {
      const btn = document.getElementById('authSearchBtn');
      const bar = document.getElementById('authSearchbar');
      if (!btn || !bar) return;
      btn.addEventListener('click', () => {
        bar.classList.toggle('open');
        const input = bar.querySelector('input');
        if (bar.classList.contains('open') && input) input.focus();
      });
      document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') bar.classList.remove('open');
      });
    });
  </script>
  <script src="{{ asset('script.js') }}?v={{ filemtime(public_path('script.js')) }}"></script>
</body>
</html>
