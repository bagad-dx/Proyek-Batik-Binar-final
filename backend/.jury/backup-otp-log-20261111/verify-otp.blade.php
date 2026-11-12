@extends('layouts.auth')
@section('title', 'Verifikasi OTP | BATIK BINAR')
@section('content')
<div class="auth-gradient-page">
  <div class="auth-centered-card" style="max-width:520px;">
    <div class="auth-card-top-nav">
      <a href="{{ route('home') }}" class="auth-card-logo"><img src="{{ asset('assets/logo-batik-binar.png') }}" alt="Logo Batik Binar"><span>BATIK BINAR</span></a>
      <a href="{{ route('register') }}" class="auth-back-link"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>Kembali</a>
    </div>
    <div class="auth-header">
      <h2>Verifikasi Pendaftaran</h2>
      @if($localFallback ?? false)
        <p>Mode tes lokal: kode OTP ditulis ke <code>storage/logs/laravel.log</code>, bukan dikirim ke {{ $channel === 'wa' ? 'WhatsApp' : 'email' }}. Berlaku 10 menit, maks 5x salah.</p>
      @else
        <p>Kode OTP 6 digit dikirim ke <b>{{ $destMasked }}</b> via <b>{{ $channel === 'wa' ? 'WhatsApp' : 'Email' }}</b>. Berlaku 10 menit, maks 5x salah.</p>
      @endif
      @if($channel === 'email')
        <p class="service-desc" style="margin-top:6px;">Tidak menemukan email? Cek folder <b>Spam / Promosi</b>.</p>
      @endif
    </div>
    <form method="POST" action="{{ route('register.otp.verify') }}" novalidate>@csrf
      <div class="form-group"><label for="code" class="form-label">Kode OTP</label><div class="input-wrapper"><input type="text" id="code" name="code" class="form-control" placeholder="••••••" maxlength="6" inputmode="numeric" autocomplete="one-time-code" required autofocus style="text-align:center;font-size:1.4rem;font-weight:800;letter-spacing:8px;"></div></div>
      <button type="submit" class="btn-pill btn-pill-royal auth-submit-btn"><span>Verifikasi & Buat Akun</span><div class="btn-arrow-circle"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg></div></button>
    </form>
    <form method="POST" action="{{ route('register.otp.resend') }}" style="margin-top:10px;" id="resendForm">@csrf
      <div class="form-group"><label class="form-label">Tidak menerima kode? Kirim ulang ke</label>
        <div style="display:flex;gap:10px;flex-wrap:wrap;">
          <label style="flex:1;min-width:120px;cursor:{{ $emailReady ? 'pointer' : 'not-allowed' }};opacity:{{ $emailReady ? '1' : '.5' }};">
            <input type="radio" name="channel" value="email" {{ $channel === 'email' ? 'checked' : '' }} {{ $emailReady ? '' : 'disabled' }}>
            <b>Email</b> <small style="color:#888;">{{ $emailReady ? '' : '(belum diisi admin)' }}</small>
          </label>
          <label style="flex:1;min-width:120px;cursor:{{ $waReady ? 'pointer' : 'not-allowed' }};opacity:{{ $waReady ? '1' : '.5' }};">
            <input type="radio" name="channel" value="wa" {{ $channel === 'wa' ? 'checked' : '' }} {{ $waReady ? '' : 'disabled' }}>
            <b>WhatsApp</b> <small style="color:#888;">{{ $waReady ? '' : '(belum diisi admin)' }}</small>
          </label>
        </div>
      </div>
      <button type="submit" class="btn-pill btn-pill-blue" id="resendBtn" style="width:100%;justify-content:center;padding:12px;" @if($cooldown > 0) disabled @endif><span id="resendLabel">{{ $cooldown > 0 ? "Kirim ulang dalam {$cooldown}s" : 'Kirim Ulang Kode' }}</span></button>
    </form>
    <p class="auth-switch-text">Salah email/nomor? <a href="{{ route('register') }}">Ulangi pendaftaran</a></p>
  </div>
</div>
<script>
  // Hanya digit + auto-submit saat 6 digit (halaman verifikasi OTP web).
  document.addEventListener('DOMContentLoaded', () => {
    const input = document.getElementById('code');
    if (input) {
      input.addEventListener('input', () => {
        input.value = input.value.replace(/\D/g, '').slice(0, 6);
        if (input.value.length === 6) input.closest('form').submit();
      });
    }

    // Cooldown tombol kirim ulang (server tetap menolak bila terlalu cepat).
    const btn = document.getElementById('resendBtn');
    const label = document.getElementById('resendLabel');
    let wait = {{ (int) $cooldown }};
    const tick = () => {
      if (!btn || !label) return;
      if (wait <= 0) {
        btn.disabled = false;
        label.textContent = 'Kirim Ulang Kode';
        return;
      }
      btn.disabled = true;
      label.textContent = 'Kirim ulang dalam ' + wait + 's';
      wait--;
      setTimeout(tick, 1000);
    };
    tick();
  });
</script>
@endsection
