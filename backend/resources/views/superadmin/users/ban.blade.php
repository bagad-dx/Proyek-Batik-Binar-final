@extends('layouts.panel')
@section('title', 'Ban Akun')
@section('content')
<div class="services-header">
  <span class="section-eyebrow-pill">Superadmin &bull; Banned</span>
  <h2 class="section-main-heading">Ban Akun</h2>
</div>
<div class="backend-card" style="max-width:640px;">
  <p class="service-desc">Akun <strong>{{ $user->name }}</strong> ({{ '@' . $user->username }} • {{ $user->email }} • <span class="role-pill {{ $user->role }}">{{ $user->role }}</span>) akan <b>diblokir total</b>: tidak bisa login (web & API), sesi aktif ditendang, token API dicabut.</p>
  <form method="POST" action="{{ route('superadmin.users.banStore', $user) }}">
    @csrf @method('PUT')
    <div class="form-group"><label class="form-label">Alasan banned*</label><div class="input-wrapper"><textarea name="ban_reason" class="form-control" rows="3" placeholder="Contoh: spam order fiktif" required>{{ old('ban_reason') }}</textarea></div></div>
    <div class="backend-actions">
      <button type="submit" class="btn-pill btn-pill-royal" style="background:linear-gradient(90deg,#5A1010,#B91C1C);"><span>Ban Sekarang</span></button>
      <a href="{{ route('superadmin.users.index') }}" class="btn-pill btn-pill-blue"><span>Batal</span></a>
    </div>
  </form>
</div>
@endsection
