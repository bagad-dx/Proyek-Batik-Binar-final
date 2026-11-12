@extends('layouts.app')
@section('title', 'Bayar Pesanan | BATIK BINAR')
@section('content')
<div class="services-header">
  <span class="section-eyebrow-pill">Belanja &bull; Pembayaran</span>
  <h2 class="section-main-heading">Bayar via Midtrans</h2>
</div>
<div class="backend-card" style="max-width:640px;">
  <h3 class="service-title">{{ $order->product->nama ?? '-' }} x{{ $order->jumlah }}</h3>
  <p class="service-desc">Barang Rp {{ number_format($order->total_harga,0,',','.') }} + Ongkir Rp {{ number_format($order->shipping_cost,0,',','.') }}</p>
  <p style="font-size:1.3rem;font-weight:800;color:var(--color-primary);">Total: Rp {{ number_format($order->grand_total,0,',','.') }}</p>
  <p class="service-meta-text">Ref: {{ $order->payment_ref ?? '-' }} • Status Midtrans: {{ $order->midtrans_status ?? '-' }}</p>
  <div id="payDisplay" style="margin:16px 0;"></div>
  <div class="backend-actions">
    @if($order->snap_token)
    <button type="button" id="payNow" class="btn-pill btn-pill-blue"><span>Bayar Sekarang</span><span class="btn-arrow-circle"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg></span></button>
    @endif
    <a href="{{ route('user.orders.index') }}" class="btn-pill btn-pill-royal"><span>Pesanan Saya</span></a>
  </div>
</div>
<script>
(function () {
  var box = document.getElementById('payDisplay');
  var initial = @json(session('direct_pay'));
  function esc(s) { var d = document.createElement('div'); d.textContent = s == null ? '' : s; return d.innerHTML; }
  function render(d) {
    var h = '';
    if (d.kind === 'qr' && d.qr_url) {
      h = '<p class="service-desc"><b>' + esc(d.title || 'Scan QR untuk Bayar') + '</b></p>' +
        '<img src="' + esc(d.qr_url) + '" alt="QR Bayar" style="max-width:240px;border-radius:16px;border:1px solid var(--color-border-light);">';
    } else if (d.kind === 'va') {
      h = '<p class="service-desc"><b>' + esc(d.title || 'Virtual Account') + '</b></p>' +
        '<p style="font-size:1.6rem;font-weight:800;letter-spacing:2px;color:var(--color-primary);">' + esc(d.va_number) + ' <button type="button" id="copyVa" class="btn-pill btn-pill-blue" style="padding:4px 14px;font-size:.75rem;vertical-align:middle;"><span>Salin</span></button></p>';
    } else if (d.kind === 'code') {
      h = '<p class="service-desc"><b>' + esc(d.title || 'Kode Bayar') + '</b></p>' +
        '<p style="font-size:1.6rem;font-weight:800;letter-spacing:2px;color:var(--color-primary);">' + esc(d.payment_code) + ' <button type="button" id="copyVa" class="btn-pill btn-pill-blue" style="padding:4px 14px;font-size:.75rem;vertical-align:middle;"><span>Salin</span></button></p>';
    } else if (d.kind === 'redirect' && d.redirect_url) {
      h = '<a class="btn-pill btn-pill-blue" href="' + esc(d.redirect_url) + '"><span>Lanjutkan ke Pembayaran</span></a>';
    } else {
      h = '<p class="service-desc">Menyiapkan pembayaran...</p>';
    }
    if (d.gross) h += '<p class="service-meta-text">Total: Rp ' + Number(d.gross).toLocaleString('id-ID') + (d.expires ? ' • Kedaluwarsa: ' + esc(d.expires) : '') + '</p>';
    box.innerHTML = h;
    var cp = document.getElementById('copyVa');
    if (cp) cp.addEventListener('click', function () {
      var t = (d.va_number || d.payment_code || '');
      if (navigator.clipboard) navigator.clipboard.writeText(t);
      showToast('Kode disalin.', 'success');
    });
  }
  if (initial) { render(initial); return; }
  @if($order->snap_token)
  var s = document.createElement('script');
  s.src = 'https://app.{{ config('services.midtrans.is_production') ? '' : 'sandbox.' }}midtrans.com/snap/snap.js';
  s.setAttribute('data-client-key', '{{ config('services.midtrans.client_key') }}');
  s.onload = function () {
    window.snap.pay('{{ $order->snap_token }}', {
      onSuccess: function () { window.location.href = "{{ route('user.orders.index') }}"; },
      onPending: function () { window.location.href = "{{ route('user.orders.index') }}"; },
      onClose: function () {},
    });
  };
  document.head.appendChild(s);
  var pb = document.getElementById('payNow');
  if (pb) pb.addEventListener('click', function () {
    window.snap.pay('{{ $order->snap_token }}', {
      onSuccess: function () { window.location.href = "{{ route('user.orders.index') }}"; },
      onPending: function () { window.location.href = "{{ route('user.orders.index') }}"; },
      onClose: function () {},
    });
  });
  @else
  fetch("{{ route('user.orders.paymentData', $order) }}", { headers: { Accept: 'application/json' } })
    .then(function (r) { return r.json().then(function (d) { return { ok: r.ok, d: d }; }); })
    .then(function (x) {
      if (x.ok) render(x.d);
      else box.innerHTML = '<p class="service-desc">' + esc(x.d.message || 'Data pembayaran belum tersedia.') + '</p>';
    })
    .catch(function () { box.innerHTML = '<p class="service-desc">Gagal memuat data pembayaran.</p>'; });
  @endif
})();
</script>
@endsection
