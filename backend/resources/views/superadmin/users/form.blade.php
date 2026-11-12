@extends('layouts.panel')
@section('title', ($user->exists ? 'Edit' : 'Tambah') . ' User')
@section('content')
<div class="services-header">
  <span class="section-eyebrow-pill">Superadmin &bull; Pengguna</span>
  <h2 class="section-main-heading">{{ $user->exists ? 'Edit' : 'Tambah' }} User / Admin</h2>
</div>
<div class="backend-card" style="max-width:640px">
  <form method="POST" action="{{ $user->exists ? route('superadmin.users.update',$user) : route('superadmin.users.store') }}">
    @csrf @if($user->exists) @method('PUT') @endif
    <div class="form-group"><label class="form-label">Nama*</label><div class="input-wrapper"><input type="text" name="name" value="{{ old('name',$user->name) }}" class="form-control" required></div></div>
    <div class="form-group"><label class="form-label">Username*</label><div class="input-wrapper"><input type="text" name="username" value="{{ old('username',$user->username) }}" class="form-control" required></div></div>
    <div class="form-group"><label class="form-label">Email*</label><div class="input-wrapper"><input type="email" name="email" value="{{ old('email',$user->email) }}" class="form-control" required></div></div>
    <div class="form-group"><label class="form-label">No HP</label><div class="input-wrapper"><input type="text" name="phone" value="{{ old('phone',$user->phone) }}" class="form-control"></div></div>
    <div class="form-group"><label class="form-label">Role*</label><div class="input-wrapper"><select name="role" class="form-control">@foreach(['user','admin','superadmin'] as $r)<option value="{{ $r }}" @selected(old('role',$user->role ?? 'user')==$r)>{{ $r }}</option>@endforeach</select></div></div>
    <div class="form-group"><label class="form-label">Password {{ $user->exists ? '(kosongkan jika tidak diubah)' : '*' }}</label><div class="input-wrapper"><input type="password" name="password" class="form-control" @if(!$user->exists) required @endif></div></div>
    <div class="form-group"><label class="form-label">Konfirmasi Password</label><div class="input-wrapper"><input type="password" name="password_confirmation" class="form-control"></div></div>
    <div class="form-group"><label class="form-label"><input type="checkbox" name="is_active" value="1" @checked(old('is_active',$user->is_active ?? true))> Aktif</label></div>
    <div class="backend-actions">
      <button type="submit" class="btn-pill btn-pill-blue"><span>Simpan</span><span class="btn-arrow-circle"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg></span></button>
      <a href="{{ route('superadmin.users.index') }}" class="btn-pill btn-pill-royal"><span>Kembali</span><span class="btn-arrow-circle"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg></span></a>
    </div>
  </form>
</div>
@endsection
