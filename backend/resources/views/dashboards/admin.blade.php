@extends('layouts.panel')
@section('title', 'Dashboard Admin')
@section('content')
<div class="services-header"><span class="section-eyebrow-pill">DASHBOARD ADMIN</span><h2 class="section-main-heading">Kelola Jual Beli & Galeri</h2><p style="color:var(--color-text-muted)">Halo, {{ auth()->user()->name }}. Semua kontrol konten ada di sini.</p></div>
<div class="services-grid-6" style="grid-template-columns:repeat(5,1fr);">
  <div class="service-card"><div class="service-body"><h3 class="service-title">{{ $stats['products'] }}</h3><p class="service-desc">Produk</p></div></div>
  <div class="service-card"><div class="service-body"><h3 class="service-title">{{ $stats['galleries'] }}</h3><p class="service-desc">Galeri</p></div></div>
  <div class="service-card"><div class="service-body"><h3 class="service-title">{{ $stats['motifs'] }}</h3><p class="service-desc">Motif</p></div></div>
  <div class="service-card"><div class="service-body"><h3 class="service-title">{{ $stats['orders'] }}</h3><p class="service-desc">Order</p></div></div>
  <div class="service-card"><div class="service-body"><h3 class="service-title">{{ $stats['pending_orders'] }}</h3><p class="service-desc">Pending</p></div></div>
</div>
<div class="backend-actions">
  <a href="{{ route('admin.products.index') }}" class="btn-pill btn-pill-blue"><span>Kelola Produk</span></a>
  <a href="{{ route('admin.galleries.index') }}" class="btn-pill btn-pill-blue"><span>Kelola Galeri</span></a>
  <a href="{{ route('admin.motifs.index') }}" class="btn-pill btn-pill-blue"><span>Kelola Motif</span></a>
  <a href="{{ route('admin.categories.index') }}" class="btn-pill btn-pill-royal"><span>Kategori</span></a>
  <a href="{{ route('admin.orders.index') }}" class="btn-pill btn-pill-royal"><span>Pesanan</span></a>
  <a href="{{ route('admin.settings.index') }}" class="btn-pill btn-pill-royal"><span>Link Shopee Toko</span></a>
  <a href="{{ route('admin.contents.index') }}" class="btn-pill btn-pill-blue"><span>Kelola Konten Website</span></a>
</div>
<div class="backend-card"><h3 class="service-title">Pesanan Terbaru</h3>@forelse($latestOrders as $o)<div style="font-size:.9rem;border-top:1px solid var(--color-border-light);padding:8px 0;">{{ $o->user->name ?? '-' }} beli {{ $o->product->nama ?? '-' }} x{{ $o->jumlah }} [{{ $o->status }}]</div>@empty<p class="service-desc">Belum ada pesanan.</p>@endforelse</div>
@endsection
