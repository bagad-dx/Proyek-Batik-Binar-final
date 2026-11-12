@extends('layouts.app')
@section('title', 'Katalog')
@section('content')
@php $img = fn($path, $fb) => $path ? (str_starts_with($path,'http') ? $path : asset('storage/'.$path)) : $fb; @endphp
<div class="services-header"><span class="section-eyebrow-pill">Belanja &bull; Kain Batik</span><h2 class="section-main-heading">Katalog Batik</h2></div>
<div class="services-grid-6">
  @foreach($products as $p)
  <div class="service-card">
    <div class="service-img-box"><a href="{{ route('shop.show', $p) }}"><img src="{{ $img($p->gambar ?? null, 'https://i.pinimg.com/1200x/7b/7d/a7/7b7da76e1d305f43cf9ddda3e83f8bc3.jpg') }}" alt="{{ $p->nama }}"></a></div>
    <div class="service-body"><h3 class="service-title">{{ $p->nama }}</h3><p class="service-meta-text">{{ $p->motif }} &bull; Stok {{ $p->stok }}</p><p class="service-desc">Rp {{ number_format($p->harga,0,',','.') }}</p>
      <div class="service-footer"><a href="{{ route('shop.show', $p) }}" class="btn-pill btn-pill-royal" style="padding:8px 16px;font-size:.8rem;"><span>Detail</span></a><a href="{{ route('user.checkout',$p) }}" class="btn-pill btn-pill-blue" style="padding:8px 16px;font-size:.8rem;"><span>Beli</span></a></div>
    </div>
  </div>
  @endforeach
</div>
<div style="margin-top:16px">{{ $products->links() }}</div>
@endsection
