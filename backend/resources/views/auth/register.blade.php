@extends('layouts.auth')
@section('title', 'Daftar Akun Baru | BATIK BINAR')
@section('content')
<div class="auth-gradient-page">
  <div class="auth-centered-card" style="max-width:520px;">
    <div class="auth-card-top-nav">
      <a href="{{ route('home') }}" class="auth-card-logo"><img src="{{ asset('assets/logo-batik-binar.png') }}" alt="Logo Batik Binar"><span>BATIK BINAR</span></a>
      <a href="{{ route('home') }}" class="auth-back-link"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>Beranda</a>
    </div>
    <div class="auth-header"><h2>Buat Akun Baru</h2><p>Daftarkan diri Anda untuk menjadi bagian pelestarian wastra</p></div>
    <form method="POST" action="{{ route('register') }}" novalidate>@csrf
      <div class="form-group"><label for="name" class="form-label">Nama Lengkap</label><div class="input-wrapper"><input type="text" id="name" name="name" value="{{ old('name') }}" class="form-control" placeholder="Contoh: Raden Ayu Binar" required autocomplete="name"></div></div>
      <div class="form-group"><label for="username" class="form-label">Username</label><div class="input-wrapper"><input type="text" id="username" name="username" value="{{ old('username') }}" class="form-control" placeholder="Contoh: bagad" required autocomplete="username"></div></div>
      <div class="form-group"><label for="email" class="form-label">Alamat Email</label><div class="input-wrapper"><input type="email" id="email" name="email" value="{{ old('email') }}" class="form-control" placeholder="nama@email.com" required autocomplete="email"></div></div>
      <div class="form-group"><label for="phone" class="form-label">Nomor Telepon (WhatsApp)</label><div class="input-wrapper"><input type="tel" id="phone" name="phone" value="{{ old('phone') }}" class="form-control" placeholder="081234567890" required autocomplete="tel"></div></div>
      <div class="form-group"><label for="regPassword" class="form-label">Kata Sandi</label><div class="input-wrapper"><input type="password" id="regPassword" name="password" class="form-control" placeholder="Buat kata sandi aman (min. 6 karakter)" required autocomplete="new-password"><button type="button" class="password-toggle-btn" data-target="regPassword" aria-label="Tampilkan kata sandi"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg></button></div></div>
      <div class="form-group"><label for="password_confirmation" class="form-label">Konfirmasi Kata Sandi</label><div class="input-wrapper"><input type="password" id="password_confirmation" name="password_confirmation" class="form-control" placeholder="Ulangi kata sandi Anda" required autocomplete="new-password"></div></div>
      <div class="form-group"><label class="form-label">Kirim Kode Verifikasi (OTP) ke</label>
        <div style="display:flex;gap:10px;flex-wrap:wrap;">
          <label style="flex:1;min-width:150px;cursor:{{ $emailReady ? 'pointer' : 'not-allowed' }};opacity:{{ $emailReady ? '1' : '.5' }};">
            <input type="radio" name="channel" value="email" {{ $emailReady ? 'checked' : '' }} {{ $emailReady ? '' : 'disabled' }} required>
            <b>Email / Gmail</b><br><small style="color:#888;">{{ $emailReady ? 'Kode dikirim ke inbox' : 'Belum diisi admin' }}</small>
          </label>
          <label style="flex:1;min-width:150px;cursor:{{ $waReady ? 'pointer' : 'not-allowed' }};opacity:{{ $waReady ? '1' : '.5' }};">
            <input type="radio" name="channel" value="wa" {{ (! $emailReady && $waReady) ? 'checked' : '' }} {{ $waReady ? '' : 'disabled' }} required>
            <b>WhatsApp</b><br><small style="color:#888;">{{ $waReady ? 'Kode dikirim ke WA' : 'Belum diisi admin' }}</small>
          </label>
        </div>
        @if(! $emailReady && ! $waReady)
          <p class="form-error" style="display:block;margin-top:6px;">Verifikasi OTP belum aktif. Hubungi admin untuk mengisi pengaturan pengiriman OTP.</p>
        @else
          <p class="service-desc" style="margin-top:6px;">Kode 6 digit dikirim ke channel yang dipilih, berlaku 10 menit, maks 5x salah.</p>
        @endif
        @if($localFallback ?? false)
          <p class="service-desc" style="margin-top:6px;color:#92400E;font-weight:600;">Mode tes lokal aktif: kode OTP hanya ditulis ke storage/logs/otp-log.log, tidak dikirim ke email/WhatsApp.</p>
        @endif
      </div>
      <button type="submit" class="btn-pill btn-pill-royal auth-submit-btn" @if(! $emailReady && ! $waReady) disabled @endif><span>Daftar Sekarang</span><div class="btn-arrow-circle"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg></div></button>
      <div class="auth-divider"><span>atau daftar dengan</span></div>
      <button type="button" class="google-auth-btn" onclick="signInWithGooglePop(this);">
        <svg class="google-icon" viewBox="0 0 24 24"><path fill="#4285F4" d="M23.745 12.27c0-.7-.06-1.4-.19-2.07H12v4.51h6.6c-.29 1.52-1.14 2.82-2.4 3.68v3.05h3.88c2.27-2.09 3.66-5.17 3.66-9.17z"/><path fill="#34A853" d="M12 24c3.24 0 5.95-1.08 7.93-2.91l-3.88-3.05c-1.08.72-2.45 1.16-4.05 1.16-3.12 0-5.77-2.1-6.72-4.93H1.25v3.15C3.26 21.36 7.33 24 12 24z"/><path fill="#FBBC05" d="M5.28 14.27c-.25-.72-.38-1.49-.38-2.27s.13-1.55.38-2.27V6.58H1.25C.45 8.16 0 9.94 0 12s.45 3.84 1.25 5.42l4.03-3.15z"/><path fill="#EA4335" d="M12 4.75c1.77 0 3.35.61 4.6 1.8l3.42-3.42C17.95 1.19 15.24 0 12 0 7.33 0 3.26 2.64 1.25 6.58l4.03 3.15c.95-2.83 3.6-4.98 6.72-4.98z"/></svg>
        Daftar dengan Google
      </button>
      <p class="auth-switch-text">Sudah memiliki akun? <a href="{{ route('login') }}">Masuk di sini</a></p>
    </form>
  </div>
</div>
@endsection
