@extends('layouts.app')
@section('title', $product->nama . ' | BATIK BINAR')
@section('content')
@php $img = fn($path, $fb) => $path ? (str_starts_with($path,'http') ? $path : asset('storage/'.$path)) : $fb; @endphp
<div class="about-section" style="padding-top:10px;">
  <div class="about-grid">
    <div class="about-collage-wrapper reveal active">
      <div class="about-brush-splash"></div>
      <div class="service-card" style="overflow:hidden;">
        <div class="service-img-box" style="height:380px;"><img src="{{ $img($product->gambar, 'https://i.pinimg.com/1200x/7b/7d/a7/7b7da76e1d305f43cf9ddda3e83f8bc3.jpg') }}" alt="{{ $product->nama }}" style="height:100%;object-fit:cover;"></div>
      </div>
    </div>
    <div class="about-content">
      <span class="section-eyebrow-pill">{{ $product->category->nama ?? 'WASTRA' }} • {{ $product->motif ?? 'Motif Paoman' }}</span>
      <h2 class="section-main-heading">{{ $product->nama }}</h2>
      <p class="about-narrative">{{ $product->deskripsi ?? 'Koleksi otentik Batik Binar Paoman dengan teknik Complongan.' }}</p>
      <div class="backend-card" style="margin:16px 0;">
        <div style="font-size:1.6rem;font-weight:800;color:var(--color-primary);">Rp {{ number_format($product->harga,0,',','.') }}</div>
        <div style="font-size:.9rem;color:var(--color-text-muted);margin-top:6px;">Bahan: <b>{{ $product->bahan ?? '-' }}</b> • Stok: <b>{{ $product->stok }}</b> • Status: <b>{{ $product->status }}</b></div>
      </div>
      <div class="backend-card" style="margin:0 0 16px;border:2px solid var(--color-border);">
        <h3 class="service-title">Pilih Cara Belanja</h3>
        <p class="service-desc">Setelah login kamu punya 2 opsi:</p>
        <div class="backend-actions" style="margin-bottom:0;">
          @if($product->stok > 0)
          <a href="{{ route('user.checkout', $product) }}" class="btn-pill btn-pill-blue"><span>1. Belanja di Website</span><div class="btn-arrow-circle"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg></div></a>
          @else<span class="service-meta-text">Stok website habis</span>@endif
          @if(!empty($shopeeUrl))
          <a href="{{ $shopeeUrl }}" target="_blank" rel="noopener" class="btn-pill btn-pill-royal" style="background:linear-gradient(90deg,#EE4D2D,#FF7337);"><span>2. Beli via Shopee Seller</span><div class="btn-arrow-circle"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="7" y1="17" x2="17" y2="7"></line><polyline points="7 7 17 7 17 17"></polyline></svg></div></a>
          @endif
        </div>
      </div>
      <div class="backend-actions">
        <a href="{{ route('shop.index') }}" class="btn-pill btn-pill-royal"><span>Kembali Belanja</span></a>
      </div>
    </div>
  </div>
</div>
@if($related->count())
<div class="services-header" style="margin-top:20px;"><span class="section-eyebrow-pill">TERKAIT</span><h2 class="section-main-heading" style="font-size:1.4rem;">Produk Sejenis</h2></div>
<div class="services-grid-6" style="grid-template-columns:repeat(3,1fr);">
  @foreach($related as $r)
  <div class="service-card"><div class="service-img-box"><a href="{{ route('shop.show', $r) }}"><img src="{{ $img($r->gambar, 'https://i.pinimg.com/1200x/7b/7d/a7/7b7da76e1d305f43cf9ddda3e83f8bc3.jpg') }}" alt="{{ $r->nama }}"></a></div>
    <div class="service-body"><h3 class="service-title">{{ $r->nama }}</h3><div class="service-footer"><span class="service-meta-text">Rp {{ number_format($r->harga,0,',','.') }}</span><a class="btn-circle-arrow" href="{{ route('shop.show', $r) }}"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg></a></div></div>
  </div>
  @endforeach
</div>
@endif
@endsection
