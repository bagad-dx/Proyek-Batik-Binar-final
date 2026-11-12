@extends('layouts.auth')
@section('title', 'Masuk ke Akun | BATIK BINAR')
@section('content')
<div class="auth-gradient-page">
  <div class="auth-centered-card">
    <div class="auth-card-top-nav">
      <a href="{{ route('home') }}" class="auth-card-logo">
        <img src="{{ asset('assets/logo-batik-binar.png') }}" alt="Logo Batik Binar"><span>BATIK BINAR</span>
      </a>
      <a href="{{ route('home') }}" class="auth-back-link">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>
        Beranda
      </a>
    </div>
    <div class="auth-header"><h2>Masuk ke Akun</h2></div>
    <form method="POST" action="{{ route('login') }}" novalidate>@csrf
      <div class="form-group">
        <label for="email" class="form-label">Email atau Username</label>
        <div class="input-wrapper"><input type="text" id="email" name="email" value="{{ old('email') }}" class="form-control" placeholder="nama@email.com / username" required autocomplete="username"></div>
      </div>
      <div class="form-group">
        <label for="password" class="form-label">Kata Sandi</label>
        <div class="input-wrapper">
          <input type="password" id="loginPassword" name="password" class="form-control" placeholder="Masukkan kata sandi Anda" required autocomplete="current-password">
          <button type="button" class="password-toggle-btn" data-target="loginPassword" aria-label="Tampilkan kata sandi">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
          </button>
        </div>
      </div>
      <div class="form-options-row">
        <label class="checkbox-label"><input type="checkbox" name="remember"><span>Ingat saya</span></label>
        <a href="#" class="forgot-link" onclick="showToast('Tautan pemulihan kata sandi telah dikirim ke email simulasi.', 'info'); return false;">Lupa password?</a>
      </div>
      <button type="submit" class="btn-pill btn-pill-royal auth-submit-btn">
        <span>Masuk ke Akun</span>
        <div class="btn-arrow-circle"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg></div>
      </button>
      <div class="auth-divider"><span>atau masuk dengan</span></div>
      <button type="button" class="google-auth-btn" onclick="signInWithGooglePop(this);">
        <svg class="google-icon" viewBox="0 0 24 24"><path fill="#4285F4" d="M23.745 12.27c0-.7-.06-1.4-.19-2.07H12v4.51h6.6c-.29 1.52-1.14 2.82-2.4 3.68v3.05h3.88c2.27-2.09 3.66-5.17 3.66-9.17z"/><path fill="#34A853" d="M12 24c3.24 0 5.95-1.08 7.93-2.91l-3.88-3.05c-1.08.72-2.45 1.16-4.05 1.16-3.12 0-5.77-2.1-6.72-4.93H1.25v3.15C3.26 21.36 7.33 24 12 24z"/><path fill="#FBBC05" d="M5.28 14.27c-.25-.72-.38-1.49-.38-2.27s.13-1.55.38-2.27V6.58H1.25C.45 8.16 0 9.94 0 12s.45 3.84 1.25 5.42l4.03-3.15z"/><path fill="#EA4335" d="M12 4.75c1.77 0 3.35.61 4.6 1.8l3.42-3.42C17.95 1.19 15.24 0 12 0 7.33 0 3.26 2.64 1.25 6.58l4.03 3.15c.95-2.83 3.6-4.98 6.72-4.98z"/></svg>
        Masuk dengan Google
      </button>
      <p class="auth-switch-text">Belum memiliki akun? <a href="{{ route('register') }}">Daftar sekarang</a></p>
      <div class="backend-card" style="margin-top:16px;font-size:.8rem;">Akun demo (password: <b>password</b>):<br>superadmin@batikbinar.id • admin@batikbinar.id • user@batikbinar.id</div>
    </form>
  </div>
</div>
@endsection
