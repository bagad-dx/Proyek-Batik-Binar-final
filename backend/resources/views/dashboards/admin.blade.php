@extends('layouts.panel')
@section('title', 'Dashboard Admin')
@section('content')
{{-- Dashboard Admin — tampilan mengikuti referensi FrontEnd/admin/admin.html.
     Semua angka & daftar di bawah ini DATA LIVE (dari DashboardController),
     bukan data contoh referensi. --}}
<div class="section-header">
  <div>
    <div class="section-title">Dashboard Overview</div>
    <div class="section-sub">Halo, {{ auth()->user()->name ?? 'Admin' }}. Semua kontrol konten ada di sini.</div>
  </div>
</div>

<div class="stats-grid cols-5">
  <div class="stat-card">
    <div class="stat-icon blue">
      <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
    </div>
    <div>
      <div class="stat-value">{{ $stats['products'] }}</div>
      <div class="stat-label">Total Produk</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon purple">
      <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
    </div>
    <div>
      <div class="stat-value">{{ $stats['galleries'] }}</div>
      <div class="stat-label">Galeri</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon orange">
      <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 2 7 12 12 22 7 12 2"/><polyline points="2 17 12 22 22 17"/><polyline points="2 12 12 17 22 12"/></svg>
    </div>
    <div>
      <div class="stat-value">{{ $stats['motifs'] }}</div>
      <div class="stat-label">Motif</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon green">
      <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
    </div>
    <div>
      <div class="stat-value">{{ $stats['orders'] }}</div>
      <div class="stat-label">Total Pesanan</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon red">
      <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
    </div>
    <div>
      <div class="stat-value">{{ $stats['pending_orders'] }}</div>
      <div class="stat-label">Pending</div>
    </div>
  </div>
</div>

<div class="backend-actions">
  <a href="{{ route('admin.products.index') }}" class="btn btn-primary"><span>Kelola Produk</span></a>
  <a href="{{ route('admin.galleries.index') }}" class="btn btn-accent"><span>Kelola Galeri</span></a>
  <a href="{{ route('admin.motifs.index') }}" class="btn btn-accent"><span>Kelola Motif</span></a>
  <a href="{{ route('admin.categories.index') }}" class="btn btn-outline"><span>Kategori</span></a>
  <a href="{{ route('admin.orders.index') }}" class="btn btn-primary"><span>Pesanan</span></a>
  <a href="{{ route('admin.settings.index') }}" class="btn btn-outline"><span>Link Shopee Toko</span></a>
  <a href="{{ route('admin.contents.index') }}" class="btn btn-outline"><span>Kelola Konten Website</span></a>
</div>

<div class="card">
  <div class="card-header">
    <div class="card-title">Pesanan Terbaru</div>
    <a href="{{ route('admin.orders.index') }}" class="btn btn-sm btn-outline">Lihat Semua</a>
  </div>
  <div class="table-wrap">
    <table class="admin-table">
      <thead>
        <tr>
          <th>ID</th>
          <th>Pembeli</th>
          <th>Produk</th>
          <th>Total</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        @forelse($latestOrders as $o)
          @php $b = ['pending' => ['badge-process', 'Pending'], 'diproses' => ['badge-process', 'Diproses'], 'dikirim' => ['badge-shipped', 'Dikirim'], 'selesai' => ['badge-done', 'Selesai'], 'batal' => ['badge-cancel', 'Dibatalkan']][$o->status] ?? ['badge-inactive', $o->status]; @endphp
          <tr>
            <td><strong>#{{ $o->id }}</strong></td>
            <td>{{ $o->user->name ?? '-' }}</td>
            <td>{{ $o->product->nama ?? '-' }} x{{ $o->jumlah }}</td>
            <td>Rp {{ number_format($o->grand_total, 0, ',', '.') }}</td>
            <td><span class="badge {{ $b[0] }}">{{ $b[1] }}</span></td>
          </tr>
        @empty
          <tr><td colspan="5" style="text-align:center;color:#64748b;">Belum ada pesanan.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection
