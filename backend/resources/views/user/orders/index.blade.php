@extends('layouts.app')
@section('title', 'Pesanan Saya')
@section('content')
<div class="services-header">
  <span class="section-eyebrow-pill">Belanja &bull; Riwayat</span>
  <h2 class="section-main-heading">Pesanan Saya</h2>
</div>
<div class="backend-card">
  @forelse($orders as $o)
  <div class="service-card" style="margin-bottom:14px">
    <div class="service-body">
      <h3 class="service-title">{{ $o->product->nama ?? '-' }} x{{ $o->jumlah }}</h3>
      <p class="service-desc">Barang Rp {{ number_format($o->total_harga,0,',','.') }} + Ongkir Rp {{ number_format($o->shipping_cost,0,',','.') }} = <b>Rp {{ number_format($o->grand_total,0,',','.') }}</b></p>
      <p class="service-meta-text">{{ $o->payment_label }} &bull; {{ $o->expedition_label }} &bull; {{ $o->created_at->format('d M Y H:i') }}</p>
      <p class="service-meta-text">Via: <b>{{ $o->metode_label }}</b></p>
      <p class="service-meta-text">{{ $o->alamat }} ({{ $o->no_hp }})</p>
      <div class="service-footer"><span class="role-pill"@if($o->belumBayar()) style="background:#FEF3C7;color:#92400E;"@endif>{{ $o->statusLabel() }}</span>
        @if($o->belumBayar() && $o->snap_token)
        <span class="service-meta-text">Bayar sebelum {{ $o->batas_bayar->format('d M Y H:i') }}</span>
        <a href="{{ route('user.orders.pay', $o) }}" class="btn-pill btn-pill-blue" style="padding:8px 18px;font-size:.8rem;"><span>Lakukan Pembayaran</span></a>
        @endif
        @if($o->status === 'pending')
        <form method="POST" action="{{ route('user.orders.cancel', $o) }}" style="display:inline;" onsubmit="return confirm('Batalkan pesanan ini? Stok akan dikembalikan.')">
          @csrf @method('PATCH')
          <button type="submit" class="btn-pill btn-pill-royal" style="padding:8px 18px;font-size:.8rem;background:linear-gradient(90deg,#5A1010,#B91C1C);"><span>Batalkan</span></button>
        </form>
        @endif
      </div>
    </div>
  </div>
  @empty
  <p class="service-desc">Belum ada pesanan. <a href="{{ route('user.catalog') }}" class="btn-pill btn-pill-blue" style="margin-left:8px"><span>Belanja dulu</span><span class="btn-arrow-circle"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg></span></a></p>
  @endforelse
</div>
<div style="margin-top:16px">{{ $orders->links() }}</div>
@endsection
