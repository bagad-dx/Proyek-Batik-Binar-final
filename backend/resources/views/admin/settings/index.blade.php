@extends('layouts.panel')
@section('title', 'Pengaturan Toko')
@section('content')
<div class="services-header">
  <span class="section-eyebrow-pill">Admin &bull; Toko</span>
  <h2 class="section-main-heading">Pengaturan Toko</h2>
  <p style="color:var(--color-text-muted)">Link ini dipakai tombol <b>Beli via Shopee</b> di halaman pembeli bila produk tidak punya link Shopee sendiri.</p>
</div>
<div class="backend-card" style="max-width:640px;">
  <form method="POST" action="{{ route('admin.settings.update') }}">@csrf @method('PUT')
    <div class="form-group"><label class="form-label">Link Toko Shopee Seller</label><div class="input-wrapper"><input type="url" name="shopee_store_url" value="{{ old('shopee_store_url', $shopeeStore) }}" class="form-control" placeholder="https://shopee.co.id/namatoko"></div></div>
    <div class="backend-actions">
      <button type="submit" class="btn-pill btn-pill-blue"><span>Simpan</span><span class="btn-arrow-circle"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg></span></button>
    </div>
  </form>
</div>
@endsection
