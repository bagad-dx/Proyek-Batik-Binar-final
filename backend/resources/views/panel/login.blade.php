@extends('layouts.auth')
@section('title', 'Login Panel | BATIK BINAR')
@section('content')
<div class="auth-gradient-page">
  <div class="auth-centered-card" style="border-top:5px solid var(--color-accent);">
    <div class="auth-card-top-nav">
      <a href="{{ route('home') }}" class="auth-card-logo">
        <img src="{{ asset('assets/logo-batik-binar.png') }}" alt="Logo Batik Binar"><span>BATIK BINAR • PANEL</span>
      </a>
      <a href="{{ route('home') }}" class="auth-back-link">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>
        Website
      </a>
    </div>
    <div class="auth-header"><span class="login-badge admin">PANEL PENGELOLA</span><h2>Login Admin / Superadmin</h2><p>Khusus pengelola. Akun user login lewat website utama.</p></div>
    <form method="POST" action="{{ route('panel.login.store') }}" novalidate>@csrf
      <div class="form-group">
        <label for="email" class="form-label">Email atau Username</label>
        <div class="input-wrapper"><input type="text" id="email" name="email" value="{{ old('email') }}" class="form-control" placeholder="bagadadmin" required autocomplete="username"></div>
      </div>
      <div class="form-group">
        <label for="password" class="form-label">Kata Sandi</label>
        <div class="input-wrapper">
          <input type="password" id="panelPassword" name="password" class="form-control" placeholder="Masukkan kata sandi" required autocomplete="current-password">
          <button type="button" class="password-toggle-btn" data-target="panelPassword" aria-label="Tampilkan kata sandi">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
          </button>
        </div>
      </div>
      <div class="form-options-row"><label class="checkbox-label"><input type="checkbox" name="remember"><span>Ingat saya</span></label></div>
      <button type="submit" class="btn-pill btn-pill-blue auth-submit-btn">
        <span>Masuk Panel</span>
        <div class="btn-arrow-circle"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg></div>
      </button>
      <p class="auth-switch-text">Bukan pengelola? <a href="{{ route('login') }}">Login User di website</a></p>
    </form>
  </div>
</div>
@endsection
