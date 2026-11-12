@extends('layouts.panel')
@section('title', 'Chat Pembeli')
@section('content')
<div class="services-header">
  <span class="section-eyebrow-pill">Admin &bull; Chat</span>
  <h2 class="section-main-heading">Chat Pembeli</h2>
  <p style="color:var(--color-text-muted)">Balas pertanyaan pembeli seputar transaksi mereka.</p>
</div>
<div class="backend-card" style="overflow-x:auto;">
  <table class="backend-table">
    <thead><tr><th>Pembeli</th><th>Pesanan</th><th>Pesan Terakhir</th><th>Belum Dibaca</th><th>Aksi</th></tr></thead>
    <tbody>
    @forelse($chats as $c)
      @php $unread = $c->pesan()->where('pengirim_id', '!=', auth()->id())->whereNull('dibaca_at')->count(); @endphp
      <tr>
        <td><strong>{{ $c->user->name ?? '-' }}</strong><div class="service-meta-text">{{ $c->user->email ?? '' }}</div></td>
        <td>{{ $c->order?->product?->nama ?? '-' }}<div class="service-meta-text">#{{ $c->order_id }} • {{ $c->order?->status }}</div></td>
        <td>{{ \Illuminate\Support\Str::limit($c->pesanTerakhir?->pesan ?? '-', 60) }}</td>
        <td style="text-align:center;">@if($unread)<span class="role-pill superadmin">{{ $unread }}</span>@else - @endif</td>
        <td><a href="{{ route('admin.chats.show', $c) }}" class="btn-pill btn-pill-blue" style="padding:6px 14px;font-size:.8rem;"><span>Buka</span></a></td>
      </tr>
    @empty
      <tr><td colspan="5" class="service-desc">Belum ada percakapan.</td></tr>
    @endforelse
    </tbody>
  </table>
</div>
<div style="margin-top:16px">{{ $chats->links() }}</div>
@endsection
