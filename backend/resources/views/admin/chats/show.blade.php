@extends('layouts.panel')
@section('title', 'Balas Chat')
@section('content')
<div class="services-header">
  <span class="section-eyebrow-pill">Admin &bull; Chat</span>
  <h2 class="section-main-heading">{{ $chat->user->name ?? '-' }} @if($chat->order)<span style="font-size:1rem;">• Pesanan #{{ $chat->order_id }} ({{ $chat->order->product->nama ?? '' }})</span>@endif</h2>
</div>
<div class="backend-card" style="max-width:720px;">
  <div style="display:flex;flex-direction:column;gap:10px;margin-bottom:18px;max-height:420px;overflow-y:auto;">
    @forelse($messages as $m)
      <div style="align-self:{{ (int)$m->pengirim_id === (int)auth()->id() ? 'flex-end' : 'flex-start' }};max-width:80%;padding:10px 14px;border-radius:16px;font-size:.9rem;{{ (int)$m->pengirim_id === (int)auth()->id() ? 'background:var(--color-accent);color:#fff;' : 'background:var(--color-bg-alt);' }}">
        <div style="font-size:.72rem;opacity:.75;margin-bottom:2px;">{{ $m->pengirim->name ?? '' }} • {{ $m->created_at->format('d M H:i') }}</div>
        {{ $m->pesan }}
      </div>
    @empty
      <p class="service-desc">Belum ada pesan.</p>
    @endforelse
  </div>
  <form method="POST" action="{{ route('admin.chats.reply', $chat) }}">@csrf
    <div class="form-group"><div class="input-wrapper" style="display:flex;gap:8px;"><input type="text" name="pesan" class="form-control" placeholder="Tulis balasan..." required maxlength="1000" style="flex:1;"><button class="btn-pill btn-pill-blue" style="padding:10px 22px;"><span>Kirim</span></button></div></div>
  </form>
  <a href="{{ route('admin.chats.index') }}" class="underline" style="font-size:.85rem;">← Kembali</a>
</div>
@endsection
