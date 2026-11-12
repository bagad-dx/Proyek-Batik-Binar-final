@extends('layouts.app')
@section('title', 'Belanja Batik Binar')
@section('content')
@php $img = fn($path, $fb) => $path ? (str_starts_with($path,'http') ? $path : asset('storage/'.$path)) : $fb; @endphp
<div class="services-header reveal">
  <span class="section-eyebrow-pill">JUAL BELI • TOKO WASATRA</span>
  <h2 class="section-main-heading">Belanja Batik Otentik</h2>
  <p style="color:var(--color-text-muted);font-size:1.05rem;">Postingan dikelola admin. Klik gambar untuk detail, tombol Beli untuk checkout.</p>
</div>
<div class="backend-card" style="margin-bottom:20px;">
  <form method="GET" style="display:flex;gap:10px;flex-wrap:wrap;">
    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama / motif..." class="form-control" style="flex:2;min-width:200px;">
    <select name="category_id" class="form-control" style="flex:1;min-width:160px;"><option value="">Semua kategori</option>@foreach($categories as $c)<option value="{{ $c->id }}" @selected(request('category_id')==$c->id)>{{ $c->nama }}</option>@endforeach</select>
    <button class="btn-pill btn-pill-blue" style="padding:10px 22px;"><span>Cari</span></button>
  </form>
</div>
<div class="services-grid-6">
  @forelse($products as $p)
  <div class="service-card">
    <div class="service-img-box">
      <a href="{{ route('shop.show', $p) }}"><img src="{{ $img($p->gambar, 'https://i.pinimg.com/1200x/7b/7d/a7/7b7da76e1d305f43cf9ddda3e83f8bc3.jpg') }}" alt="{{ $p->nama }}"></a>
      <div class="service-icon-badge" style="font-size:.7rem;font-weight:800;">{{ $p->category->nama ?? 'Batik' }}</div>
    </div>
    <div class="service-body">
      <a href="{{ route('shop.show', $p) }}"><h3 class="service-title">{{ $p->nama }}</h3></a>
      <p class="service-desc">{{ \Illuminate\Support\Str::limit($p->deskripsi,80) }}</p>
      <p class="service-desc" style="font-size:.8rem;">Motif <b>{{ $p->motif ?? '-' }}</b> • Stok <b>{{ $p->stok }}</b></p>
      <div class="service-footer"><span class="service-meta-text">Rp {{ number_format($p->harga,0,',','.') }}</span>
        <div style="display:flex;gap:8px;align-items:center;">
          <a class="btn-circle-arrow" href="{{ route('shop.show', $p) }}" aria-label="Detail"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg></a>
          @if($p->stok > 0)<a href="{{ route('user.checkout', $p) }}" class="btn-pill btn-pill-blue" style="padding:8px 16px;font-size:.8rem;"><span>Beli</span></a>@else<span class="service-meta-text">Habis</span>@endif
        </div>
      </div>
    </div>
  </div>
  @empty<div class="service-card"><div class="service-body"><p class="service-desc">Tidak ada produk ditemukan.</p></div></div>@endforelse
</div>
<div style="margin-top:16px">{{ $products->links() }}</div>
@endsection
