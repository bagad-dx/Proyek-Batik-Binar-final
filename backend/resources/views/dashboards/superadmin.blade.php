@extends('layouts.panel')
@section('title', 'Dashboard Superadmin')
@section('content')
<div class="services-header"><span class="section-eyebrow-pill">DASHBOARD SUPERADMIN</span><h2 class="section-main-heading">Kelola User & Admin</h2><p style="color:var(--color-text-muted)">Halo, {{ auth()->user()->name }}.</p></div>
<div class="services-grid-6" style="grid-template-columns:repeat(5,1fr);">
  <div class="service-card"><div class="service-body"><h3 class="service-title">{{ $stats['total_users'] }}</h3><p class="service-desc">User</p></div></div>
  <div class="service-card"><div class="service-body"><h3 class="service-title">{{ $stats['total_admins'] }}</h3><p class="service-desc">Admin</p></div></div>
  <div class="service-card"><div class="service-body"><h3 class="service-title">{{ $stats['total_superadmins'] }}</h3><p class="service-desc">Superadmin</p></div></div>
  <div class="service-card"><div class="service-body"><h3 class="service-title">{{ $stats['products'] }}</h3><p class="service-desc">Produk</p></div></div>
  <div class="service-card"><div class="service-body"><h3 class="service-title">{{ $stats['orders'] }}</h3><p class="service-desc">Order</p></div></div>
</div>
<div class="backend-actions">
  <a href="{{ route('superadmin.users.index') }}" class="btn-pill btn-pill-royal"><span>Kelola User / Admin</span></a>
  <a href="{{ route('superadmin.chatbot.index') }}" class="btn-pill btn-pill-blue"><span>Kelola Chatbot</span></a>
  <a href="{{ route('admin.dashboard') }}" class="btn-pill btn-pill-blue"><span>Panel Admin</span></a>
</div>
<div class="backend-card"><h3 class="service-title">User Terbaru</h3>@foreach($latestUsers as $u)<div style="font-size:.9rem;border-top:1px solid var(--color-border-light);padding:8px 0;">{{ $u->name }} — {{ $u->email }} <span class="role-pill {{ $u->role }}">{{ $u->role }}</span></div>@endforeach</div>
@endsection
