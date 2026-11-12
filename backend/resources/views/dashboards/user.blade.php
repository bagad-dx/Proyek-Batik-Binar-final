@extends('layouts.app')
@section('title', 'Dashboard User')
@php
  $me = auth()->user();
  $avatarUrl = $me->avatar
    ? (str_starts_with($me->avatar, 'http') ? $me->avatar : asset('storage/'.$me->avatar))
    : null;
  $inisial = strtoupper(substr($me->name ?? $me->username ?? '?', 0, 1));
@endphp
@section('fullwidth')
<section class="dash-hero">
  <div class="container">
    <div class="dash-hero-content">
      <div class="dash-avatar">
        @if($avatarUrl)<img src="{{ $avatarUrl }}" alt="Foto profil" style="width:100%;height:100%;border-radius:50%;object-fit:cover;">
        @else<svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>@endif
      </div>
      <div class="dash-hero-text">
        <h1 class="dash-hero-title">Selamat Datang, {{ $me->name }}!</h1>
        <p class="dash-hero-desc">Kelola akun, pesanan, dan preferensi batik Anda di satu tempat.</p>
      </div>
    </div>
  </div>
  <div class="hero-bottom-curve"><svg viewBox="0 0 1200 120" preserveAspectRatio="none"><path d="M0,0 C300,90 900,90 1200,0 L1200,120 L0,120 Z"></path></svg></div>
</section>
@endsection
@section('content')
<div class="dash-grid">
  <div class="dash-main-col">
    <div class="dash-stats-row">
      <div class="dash-stat-card">
        <div class="dash-stat-icon" style="background: linear-gradient(135deg, #152A88, #1687D9);">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path><line x1="3" y1="6" x2="21" y2="6"></line><path d="M16 10a4 4 0 0 1-8 0"></path></svg>
        </div>
        <div class="dash-stat-info"><span class="dash-stat-number">{{ $stats['aktif'] }}</span><span class="dash-stat-label">Pesanan Aktif</span></div>
      </div>
      <div class="dash-stat-card">
        <div class="dash-stat-icon" style="background: linear-gradient(135deg, #0EA5E9, #38BDF8);">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
        </div>
        <div class="dash-stat-info"><span class="dash-stat-number">{{ $stats['wishlist'] }}</span><span class="dash-stat-label">Wishlist</span></div>
      </div>
      <div class="dash-stat-card">
        <div class="dash-stat-icon" style="background: linear-gradient(135deg, #10B981, #34D399);">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline></svg>
        </div>
        <div class="dash-stat-info"><span class="dash-stat-number">{{ $stats['riwayat'] }}</span><span class="dash-stat-label">Riwayat Pesanan</span></div>
      </div>
    </div>

    <div class="dash-section-card">
      <div class="dash-section-header">
        <h3>Pesanan Terbaru</h3>
        <a href="{{ route('user.orders.index') }}" class="dash-see-all">Lihat Semua →</a>
      </div>
      <div class="dash-orders-list">
        @forelse($myOrders as $o)
        @php
          $badge = $o->status === 'selesai' ? 'status-done' : ($o->status === 'dikirim' ? 'status-shipped' : 'status-process');
          $img = $o->product && $o->product->gambar_url ? $o->product->gambar_url : 'https://i.pinimg.com/1200x/7b/7d/a7/7b7da76e1d305f43cf9ddda3e83f8bc3.jpg';
        @endphp
        <div class="dash-order-item">
          <div class="dash-order-img"><img src="{{ $img }}" alt="{{ $o->product->nama ?? 'Produk' }}"></div>
          <div class="dash-order-info"><h4>{{ $o->product->nama ?? '-' }}</h4><p>x{{ $o->jumlah }} • {{ $o->product->motif ?? '' }}</p></div>
          <div class="dash-order-price">Rp {{ number_format($o->total_harga, 0, ',', '.') }}</div>
          <span class="dash-order-status {{ $badge }}">{{ $o->statusLabel() }}</span>
        </div>
        @empty<p class="service-desc">Belum ada pesanan. <a href="{{ route('user.catalog') }}">Belanja dulu</a></p>@endforelse
      </div>
    </div>

    <div class="services-header" style="margin-top:28px;"><span class="section-eyebrow-pill">TERBARU</span><h2 class="section-main-heading" style="font-size:1.4rem;">Produk Terbaru</h2></div>
    <div class="services-grid-6" style="grid-template-columns:repeat(3,1fr);">
      @foreach($products as $p)<div class="service-card"><div class="service-body"><h3 class="service-title">{{ $p->nama }}</h3><p class="service-desc">{{ $p->motif }}</p><div class="service-footer"><span class="service-meta-text">Rp {{ number_format($p->harga,0,',','.') }}</span><a class="btn-circle-arrow" href="{{ route('user.checkout',$p) }}"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg></a></div></div></div>@endforeach
    </div>
  </div>

  <div class="dash-side-col">
    <div class="dash-section-card dash-profile-card">
      <div class="dash-profile-header">
        <div class="dash-profile-avatar">
          @if($avatarUrl)<img src="{{ $avatarUrl }}" alt="Foto profil" style="width:100%;height:100%;border-radius:50%;object-fit:cover;">
          @else<svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>@endif
        </div>
        <div><h4 class="dash-profile-name">{{ $me->name }}</h4><p class="dash-profile-email">{{ $me->email }}</p></div>
      </div>
      <div class="dash-profile-details">
        <div class="dash-profile-row"><span class="dash-profile-label">Telepon</span><span class="dash-profile-value">{{ $me->phone ?? '-' }}</span></div>
        <div class="dash-profile-row"><span class="dash-profile-label">Bergabung</span><span class="dash-profile-value">{{ $me->created_at ? $me->created_at->translatedFormat('F Y') : '-' }}</span></div>
        <div class="dash-profile-row"><span class="dash-profile-label">Status</span><span class="dash-profile-value dash-member-badge">Member Aktif</span></div>
      </div>
      <form method="POST" action="{{ route('user.profile.avatar') }}" enctype="multipart/form-data" style="display:flex;gap:8px;margin-top:14px;">@csrf
        <input type="file" name="avatar" accept="image/jpeg,image/png,image/webp" required class="form-control" style="flex:1;min-width:0;">
        <button type="submit" class="btn-pill btn-pill-blue" style="padding:8px 16px;font-size:.8rem;flex:none;"><span>Ganti Foto</span></button>
      </form>
      <form method="POST" action="{{ route('user.profile.update') }}" style="margin-top:14px;border-top:1px solid var(--color-border-light);padding-top:14px;">@csrf @method('PUT')
        <h3 class="dash-section-title" style="margin-bottom:10px;">Edit Profil</h3>
        <div class="form-group"><label class="form-label">Nama Lengkap</label><div class="input-wrapper"><input type="text" name="name" value="{{ old('name', $me->name) }}" class="form-control" required></div></div>
        <div class="form-group"><label class="form-label">Gmail</label><div class="input-wrapper"><input type="email" name="email" value="{{ old('email', $me->email) }}" class="form-control" required></div></div>
        <div class="form-group"><label class="form-label">No HP</label><div class="input-wrapper"><input type="text" name="phone" value="{{ old('phone', $me->phone) }}" class="form-control" required></div></div>
        <div class="form-group"><label class="form-label">Alamat</label><div class="input-wrapper"><textarea name="alamat" rows="2" class="form-control">{{ old('alamat', $me->alamat) }}</textarea></div></div>
        <div class="form-group"><label class="form-label">Password Baru (kosongkan bila tak diubah)</label><div class="input-wrapper"><input type="password" name="password" class="form-control" autocomplete="new-password"></div></div>
        <div class="form-group"><label class="form-label">Konfirmasi Password Baru</label><div class="input-wrapper"><input type="password" name="password_confirmation" class="form-control" autocomplete="new-password"></div></div>
        <button type="submit" class="btn-pill btn-pill-royal" style="width:100%;justify-content:center;font-size:.88rem;"><span>Simpan Perubahan</span></button>
      </form>
    </div>

    <div class="dash-section-card">
      <h3 class="dash-section-title">Menu Cepat</h3>
      <div class="dash-quick-actions">
        <a href="{{ route('user.catalog') }}" class="dash-action-btn"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path><line x1="3" y1="6" x2="21" y2="6"></line><path d="M16 10a4 4 0 0 1-8 0"></path></svg><span>Belanja</span></a>
        <a href="{{ route('home') }}#galeri" class="dash-action-btn"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg><span>Galeri</span></a>
        <a href="{{ route('koleksi.index') }}" class="dash-action-btn"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg><span>Koleksi</span></a>
        <a href="{{ route('user.orders.index') }}" class="dash-action-btn"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"></circle><path d="M12 2v3M12 19v3M2 12h3M19 12h3"></path></svg><span>Pesanan</span></a>
      </div>
    </div>
  </div>
</div>
@endsection
