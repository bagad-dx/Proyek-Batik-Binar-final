@extends('layouts.panel')
@section('title', 'Pengaturan OTP')
@section('content')
@php
  $badge = function (bool $enabled, bool $ready) use ($status) {
      if (! $enabled) return ['Nonaktif', '#6B7280', '#F3F4F6'];
      if (! $ready && $status['local_fallback']) return ['Mode tes lokal', '#92400E', '#FEF3C7'];
      return $ready ? ['Aktif', '#065F46', '#ECFDF5'] : ['Belum siap', '#991B1B', '#FEF2F2'];
  };
  [$emailLabel, $emailColor, $emailBg] = $badge($status['email_enabled'], $status['email_ready']);
  [$waLabel, $waColor, $waBg] = $badge($status['wa_enabled'], $status['wa_ready']);
@endphp

<div class="services-header">
  <span class="section-eyebrow-pill">Admin &bull; OTP</span>
  <h2 class="section-main-heading">Pengaturan Pengiriman OTP</h2>
  <p style="color:var(--color-text-muted)">Kode OTP pendaftaran dikirim <b>nyata</b> ke email atau WhatsApp. Isi kredensial di bawah, lalu klik <b>Kirim Tes</b> untuk memastikan berhasil.</p>
</div>

<div style="display:flex;gap:14px;flex-wrap:wrap;margin-bottom:18px;">
  <div class="backend-card" style="flex:1;min-width:240px;padding:18px;">
    <div style="font-size:.78rem;color:var(--color-text-muted);font-weight:700;letter-spacing:.05em;">EMAIL / SMTP</div>
    <div style="display:flex;align-items:center;gap:10px;margin:8px 0;">
      <span style="background:{{ $emailBg }};color:{{ $emailColor }};font-weight:800;font-size:.78rem;padding:4px 12px;border-radius:999px;">{{ $emailLabel }}</span>
      <span style="font-size:.82rem;color:var(--color-text-muted);">{{ $status['mail_username'] ?: 'username belum diisi' }}</span>
    </div>
    <div style="font-size:.82rem;">Server: <b>{{ $status['mail_host'] }}:{{ $status['mail_port'] }}</b></div>
    <div style="font-size:.82rem;">Pengirim: <b>{{ $status['mail_from_address'] ?: '—' }}</b></div>
  </div>
  <div class="backend-card" style="flex:1;min-width:240px;padding:18px;">
    <div style="font-size:.78rem;color:var(--color-text-muted);font-weight:700;letter-spacing:.05em;">WHATSAPP / FONNTE</div>
    <div style="display:flex;align-items:center;gap:10px;margin:8px 0;">
      <span style="background:{{ $waBg }};color:{{ $waColor }};font-weight:800;font-size:.78rem;padding:4px 12px;border-radius:999px;">{{ $waLabel }}</span>
      <span style="font-size:.82rem;color:var(--color-text-muted);">{{ $s['fonnte_token_set'] || $s['env_fonnte_set'] ? 'token tersimpan' : 'token belum diisi' }}</span>
    </div>
    <div style="font-size:.82rem;">Kode berlaku <b>10 menit</b>, maks <b>5x</b> salah.</div>
    <div style="font-size:.82rem;">Kirim ulang minimal <b>{{ $resendSeconds }} detik</b>.</div>
  </div>
</div>

<div class="backend-card" style="margin-bottom:18px;border:2px solid {{ $status['local_fallback'] ? '#F59E0B' : '#E5DDCF' }};background:{{ $status['local_fallback'] ? '#FFFBEB' : '#fff' }};">
  <h3 style="margin-top:0;color:{{ $status['local_fallback'] ? '#92400E' : 'var(--color-primary)' }};">
    Mode Tes Lokal {{ $status['local_fallback'] ? '(AKTIF)' : '(nonaktif)' }}
  </h3>
  <p style="color:var(--color-text-muted);font-size:.88rem;margin-top:0;">
    Untuk pengembangan tanpa kredensial: kode OTP <b>tidak dikirim</b> ke email/WhatsApp dan <b>tidak tampil di layar</b>,
    hanya ditulis ke <code>storage/logs/laravel.log</code>. Matikan mode ini sebelum aplikasi dipakai orang lain.
  </p>
  <form method="POST" action="{{ route('admin.otp.local') }}">@csrf
    <label style="display:block;margin:8px 0;">
      <input type="checkbox" name="local_fallback" value="1" @checked($status['local_fallback'])>
      Aktifkan mode tes lokal (OTP hanya ke file log)
    </label>
    <div class="backend-actions">
      <button type="submit" class="btn-pill btn-pill-royal"><span>Simpan Mode Tes</span></button>
    </div>
  </form>
  <details style="margin-top:6px;font-size:.86rem;">
    <summary style="cursor:pointer;font-weight:800;color:var(--color-primary);">Cara melihat kode OTP di log</summary>
    <ol style="margin:10px 0 0 18px;line-height:1.7;">
      <li>Daftar seperti biasa di halaman <code>/register</code>.</li>
      <li>Buka file <code>backend/storage/logs/laravel.log</code>.</li>
      <li>Cari baris <code>[OTP MODE TES LOKAL]</code>; kode 6 digit ada di kolom <code>code</code>.</li>
      <li>Masukkan kode itu di halaman verifikasi OTP.</li>
    </ol>
  </details>
</div>

<div class="backend-card" style="margin-bottom:18px;">
  <h3 style="margin-top:0;color:var(--color-primary);">1. Email pengirim (Gmail SMTP)</h3>
  <form method="POST" action="{{ route('admin.otp.update') }}">@csrf @method('PUT')
    <input type="hidden" name="mail_mailer" value="smtp">
    <input type="hidden" name="wa_enabled" value="{{ $s['wa_enabled'] === '1' ? '1' : '0' }}">
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:14px;">
      <div class="form-group"><label class="form-label">Host SMTP</label><div class="input-wrapper"><input type="text" name="mail_host" value="{{ old('mail_host', $s['mail_host']) }}" class="form-control" placeholder="smtp.gmail.com"></div></div>
      <div class="form-group"><label class="form-label">Port</label><div class="input-wrapper"><input type="number" name="mail_port" value="{{ old('mail_port', $s['mail_port']) }}" class="form-control" placeholder="587" min="1" max="65535"></div></div>
      <div class="form-group"><label class="form-label">Enkripsi</label><div class="input-wrapper"><select name="mail_encryption" class="form-control">
        <option value="tls" @selected(old('mail_encryption', $s['mail_encryption']) === 'tls')>TLS (port 587)</option>
        <option value="ssl" @selected(old('mail_encryption', $s['mail_encryption']) === 'ssl')>SSL (port 465)</option>
        <option value="none" @selected(old('mail_encryption', $s['mail_encryption']) === 'none')>Tanpa enkripsi</option>
      </select></div></div>
    </div>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:14px;">
      <div class="form-group"><label class="form-label">Username / Email Gmail</label><div class="input-wrapper"><input type="email" name="mail_username" value="{{ old('mail_username', $s['mail_username']) }}" class="form-control" placeholder="emailkamu@gmail.com"></div></div>
      <div class="form-group"><label class="form-label">App Password (16 huruf)</label><div class="input-wrapper"><input type="password" name="mail_password" value="" class="form-control" placeholder="{{ $s['mail_password_set'] ? '•••••••• (tidak diubah)' : 'abcd efgh ijkl mnop' }}" autocomplete="new-password"></div></div>
    </div>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:14px;">
      <div class="form-group"><label class="form-label">Email Pengirim (From)</label><div class="input-wrapper"><input type="email" name="mail_from_address" value="{{ old('mail_from_address', $s['mail_from_address']) }}" class="form-control" placeholder="kosongkan = pakai username"></div></div>
      <div class="form-group"><label class="form-label">Nama Pengirim</label><div class="input-wrapper"><input type="text" name="mail_from_name" value="{{ old('mail_from_name', $s['mail_from_name']) }}" class="form-control" placeholder="Batik Binar"></div></div>
    </div>
    <label style="display:block;margin:8px 0;"><input type="checkbox" name="email_enabled" value="1" @checked($s['email_enabled'] === '1')> Aktifkan verifikasi lewat Email</label>
    @if($s['mail_password_set'])
      <label style="display:block;margin:8px 0;font-size:.85rem;color:#991B1B;"><input type="checkbox" name="clear_mail_password" value="1"> Hapus App Password yang tersimpan</label>
    @endif
    <div class="backend-actions">
      <button type="submit" class="btn-pill btn-pill-blue"><span>Simpan Pengaturan Email</span></button>
    </div>
  </form>
  <details style="margin-top:6px;font-size:.86rem;">
    <summary style="cursor:pointer;font-weight:800;color:var(--color-primary);">Cara membuat App Password Gmail (16 huruf)</summary>
    <ol style="margin:10px 0 0 18px;line-height:1.7;">
      <li>Buka <a href="https://myaccount.google.com/security" target="_blank" rel="noopener">myaccount.google.com/security</a> &rarr; aktifkan <b>Verifikasi 2 Langkah</b>.</li>
      <li>Buka <a href="https://myaccount.google.com/apppasswords" target="_blank" rel="noopener">Sandi aplikasi</a> &rarr; buat baru (nama: Batik Binar).</li>
      <li>Salin 16 huruf yang muncul (boleh dengan spasi), tempel di kolom App Password, lalu Simpan.</li>
      <li>Password Gmail biasa <b>tidak bisa</b> dipakai untuk SMTP.</li>
    </ol>
  </details>
</div>

<div class="backend-card" style="margin-bottom:18px;">
  <h3 style="margin-top:0;color:var(--color-primary);">2. WhatsApp (Fonnte)</h3>
  <form method="POST" action="{{ route('admin.otp.update') }}">@csrf @method('PUT')
    {{-- Kirim ulang nilai email agar tidak ikut terhapus saat menyimpan bagian WA. --}}
    <input type="hidden" name="mail_host" value="{{ $s['mail_host'] }}">
    <input type="hidden" name="mail_port" value="{{ $s['mail_port'] }}">
    <input type="hidden" name="mail_username" value="{{ $s['mail_username'] }}">
    <input type="hidden" name="mail_encryption" value="{{ $s['mail_encryption'] }}">
    <input type="hidden" name="mail_from_address" value="{{ $s['mail_from_address'] }}">
    <input type="hidden" name="mail_from_name" value="{{ $s['mail_from_name'] }}">
    <input type="hidden" name="email_enabled" value="{{ $s['email_enabled'] === '1' ? '1' : '0' }}">
    <div class="form-group"><label class="form-label">Token Fonnte</label><div class="input-wrapper"><input type="text" name="fonnte_token" value="" class="form-control" placeholder="{{ $s['fonnte_token_set'] ? '•••••••• (tidak diubah)' : 'token dari dashboard Fonnte' }}" autocomplete="off"></div></div>
    <label style="display:block;margin:8px 0;"><input type="checkbox" name="wa_enabled" value="1" @checked($s['wa_enabled'] === '1')> Aktifkan verifikasi lewat WhatsApp</label>
    @if($s['fonnte_token_set'])
      <label style="display:block;margin:8px 0;font-size:.85rem;color:#991B1B;"><input type="checkbox" name="clear_fonnte_token" value="1"> Hapus token yang tersimpan</label>
    @endif
    <div class="backend-actions">
      <button type="submit" class="btn-pill btn-pill-blue"><span>Simpan Pengaturan WhatsApp</span></button>
    </div>
  </form>
  <details style="margin-top:6px;font-size:.86rem;">
    <summary style="cursor:pointer;font-weight:800;color:var(--color-primary);">Cara mendapatkan token Fonnte</summary>
    <ol style="margin:10px 0 0 18px;line-height:1.7;">
      <li>Daftar di <a href="https://fonnte.com" target="_blank" rel="noopener">fonnte.com</a> (ada kuota trial gratis).</li>
      <li>Menu <b>Devices</b> &rarr; <b>Add device</b> &rarr; scan QR dengan WhatsApp nomor pengirim.</li>
      <li>Salin <b>Token</b> perangkat tersebut, tempel di kolom Token Fonnte, lalu Simpan.</li>
      <li>Pastikan status perangkat <b>Connected</b>; bila HP offline, OTP WA gagal terkirim.</li>
    </ol>
  </details>
</div>

<div class="backend-card">
  <h3 style="margin-top:0;color:var(--color-primary);">3. Kirim Tes (wajib setelah mengisi kredensial)</h3>
  <p style="color:var(--color-text-muted);font-size:.88rem;">Pesan uji dikirim nyata. Bila gagal, pesan error menunjukkan penyebabnya (login ditolak, koneksi, perangkat WA terputus, dll).</p>
  <form method="POST" action="{{ route('admin.otp.test') }}">@csrf
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:14px;">
      <div class="form-group"><label class="form-label">Channel</label><div class="input-wrapper"><select name="channel" class="form-control"><option value="email">Email</option><option value="wa">WhatsApp</option></select></div></div>
      <div class="form-group"><label class="form-label">Tujuan (email / nomor WA)</label><div class="input-wrapper"><input type="text" name="destination" class="form-control" placeholder="emailkamu@gmail.com atau 08xxxxxxxxxx" required></div></div>
    </div>
    <div class="backend-actions">
      <button type="submit" class="btn-pill btn-pill-royal"><span>Kirim Tes Sekarang</span></button>
    </div>
  </form>
</div>
@endsection
