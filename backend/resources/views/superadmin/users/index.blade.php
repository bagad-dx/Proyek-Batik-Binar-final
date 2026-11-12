@extends('layouts.panel')
@section('title', 'Kelola User & Admin')
@section('content')
<div class="services-header">
  <span class="section-eyebrow-pill">Superadmin &bull; Pengguna</span>
  <h2 class="section-main-heading">Kelola User &amp; Admin</h2>
</div>
<div class="backend-actions">
  <a href="{{ route('superadmin.users.create') }}" class="btn-pill btn-pill-blue"><span>+ Tambah User/Admin</span><span class="btn-arrow-circle"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg></span></a>
</div>
<div class="backend-card" style="margin-bottom:18px">
  <form method="GET">
    <div class="form-group" style="display:flex;gap:10px;margin-bottom:0;flex-wrap:wrap">
      <div class="input-wrapper" style="flex:2;min-width:180px"><input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama/username/email" class="form-control"></div>
      <div class="input-wrapper" style="flex:1;min-width:140px"><select name="role" class="form-control"><option value="">Semua role</option>@foreach(['user','admin','superadmin'] as $r)<option value="{{ $r }}" @selected(request('role')==$r)>{{ $r }}</option>@endforeach</select></div>
      <div class="input-wrapper" style="flex:1;min-width:140px"><select name="status" class="form-control"><option value="">Semua status</option>@foreach(['aktif' => 'Aktif', 'nonaktif' => 'Nonaktif', 'banned' => 'Banned'] as $k => $v)<option value="{{ $k }}" @selected(request('status')==$k)>{{ $v }}</option>@endforeach</select></div>
      <button type="submit" class="btn-pill btn-pill-royal"><span>Filter</span><span class="btn-arrow-circle"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg></span></button>
    </div>
  </form>
</div>
<div class="backend-card" style="overflow-x:auto">
  <table class="backend-table">
    <thead><tr><th>Nama</th><th>Role</th><th>Status</th><th>Aksi</th></tr></thead>
    <tbody>
    @foreach($users as $u)
      <tr>
        <td><strong>{{ $u->name }}</strong> <span class="service-meta-text">{{ '@' . $u->username }}</span><div class="service-meta-text">{{ $u->email }} &bull; {{ $u->phone }}</div>@if($u->isBanned())<div class="service-meta-text" style="color:#B91C1C;">Alasan: {{ $u->ban_reason }}</div>@endif</td>
        <td style="text-align:center"><span class="role-pill {{ $u->role }}">{{ $u->role }}</span></td>
        <td style="text-align:center"><span class="role-pill {{ $u->isBanned() ? 'superadmin' : '' }}">{{ $u->statusLabel() }}</span></td>
        <td style="text-align:center;white-space:nowrap">
          <a href="{{ route('superadmin.users.edit',$u) }}" class="btn-pill btn-pill-blue" style="padding:6px 14px;font-size:.8rem"><span>Edit</span></a>
          @if($u->isBanned())
          <form method="POST" action="{{ route('superadmin.users.unban',$u) }}" style="display:inline">@csrf @method('PATCH')<button type="submit" class="btn-pill btn-pill-blue" style="padding:6px 14px;font-size:.8rem"><span>Unban</span></button></form>
          @else
          <a href="{{ route('superadmin.users.ban',$u) }}" class="btn-pill btn-pill-royal" style="padding:6px 14px;font-size:.8rem;background:linear-gradient(90deg,#5A1010,#B91C1C);"><span>Ban</span></a>
          <form method="POST" action="{{ route('superadmin.users.toggle',$u) }}" style="display:inline">@csrf @method('PATCH')<button type="submit" class="btn-pill btn-pill-royal" style="padding:6px 14px;font-size:.8rem"><span>{{ $u->is_active ? 'Nonaktifkan' : 'Aktifkan' }}</span></button></form>
          @endif
          <form method="POST" action="{{ route('superadmin.users.destroy',$u) }}" style="display:inline" onsubmit="return confirm('Hapus akun {{ $u->username }} ({{ $u->role }}) permanen?')">@csrf @method('DELETE')<button type="submit" class="btn-pill btn-pill-royal" style="padding:6px 14px;font-size:.8rem"><span>Hapus</span></button></form>
        </td>
      </tr>
    @endforeach
    </tbody>
  </table>
</div>
<div style="margin-top:16px">{{ $users->links() }}</div>
@endsection
