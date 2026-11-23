@extends('layouts.panel')
@section('title', 'Dashboard Superadmin')
@section('content')
{{-- Dashboard Superadmin — tampilan mengikuti referensi FrontEnd/admin dan superadmin/superadmin.html.
     Semua angka & daftar di bawah ini DATA LIVE (dari DashboardController),
     bukan data contoh referensi. --}}
<div class="section-header">
  <div>
    <div class="section-title">Dashboard Overview</div>
    <div class="section-sub">Selamat datang kembali, {{ auth()->user()->name ?? 'Super Admin' }}.</div>
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
    <div class="stat-icon orange">
      <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
    </div>
    <div>
      <div class="stat-value">{{ $stats['orders'] }}</div>
      <div class="stat-label">Total Pesanan</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon green">
      <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
    </div>
    <div>
      <div class="stat-value">{{ $stats['total_users'] }}</div>
      <div class="stat-label">Pengguna</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon purple">
      <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
    </div>
    <div>
      <div class="stat-value">{{ $stats['total_admins'] }}</div>
      <div class="stat-label">Total Admin</div>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon violet">
      <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"/></svg>
    </div>
    <div>
      <div class="stat-value" style="color:#7c3aed;">{{ $stats['total_superadmins'] }}</div>
      <div class="stat-label">Superadmin</div>
    </div>
  </div>
</div>

<div class="backend-actions">
  <a href="{{ route('superadmin.users.index') }}" class="btn btn-primary"><span>Kelola User / Admin</span></a>
  <a href="{{ route('superadmin.chatbot.index') }}" class="btn btn-accent"><span>Kelola Chatbot</span></a>
  <a href="{{ route('admin.dashboard') }}" class="btn btn-accent"><span>Panel Admin</span></a>
</div>

<div class="dash-grid">
<div class="card">
  <div class="card-header">
    <div class="card-title">User Terbaru</div>
    <a href="{{ route('superadmin.users.index') }}" class="btn btn-sm btn-outline">Lihat Semua</a>
  </div>
  <div class="table-wrap">
    <table class="admin-table">
      <thead>
        <tr>
          <th>Nama</th>
          <th>Email</th>
          <th>Role</th>
        </tr>
      </thead>
      <tbody>
        @forelse($latestUsers as $u)
          <tr>
            <td><strong>{{ $u->name }}</strong></td>
            <td>{{ $u->email }}</td>
            <td><span class="role-pill {{ $u->role }}">{{ $u->role }}</span></td>
          </tr>
        @empty
          <tr><td colspan="3" style="text-align:center;color:#64748b;">Belum ada user.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
<div class="card">
  <div class="card-header">
    <div class="card-title">Produk Terlaris</div>
  </div>
  {{-- Diisi admin-panel.js dari /api/products (terjual = order lunas). --}}
  <div class="top-products" id="topProducts">
    <div class="notif-empty">Memuat produk terlaris...</div>
  </div>
</div>
</div>
@endsection
