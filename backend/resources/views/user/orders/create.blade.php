@extends('layouts.app')
@section('title', 'Checkout | BATIK BINAR')
@section('content')
@php
  use App\Models\Order;
  $expeditions = Order::EXPEDITIONS;
  $methods = [
    ['key' => 'transfer', 'label' => 'Transfer Bank', 'gw' => false],
    ['key' => 'cod', 'label' => 'COD', 'gw' => false],
    ['key' => 'ewallet', 'label' => 'E-Wallet', 'gw' => false],
    ['key' => 'qris', 'label' => 'QRIS', 'gw' => true],
    ['key' => 'kartu', 'label' => 'Kartu Kredit/Debit', 'gw' => true],
  ];
@endphp
<div class="about-section" style="padding-top:10px;">
  <div class="about-grid">
    <div class="about-content">
      <span class="section-eyebrow-pill">CHECKOUT • {{ $product->category->nama ?? 'WASTRA' }}</span>
      <h2 class="section-main-heading" style="font-size:1.8rem;">{{ $product->nama }}</h2>
      <p class="about-narrative">{{ \Illuminate\Support\Str::limit($product->deskripsi, 140) }}</p>
      <div class="backend-card">
        <div style="display:flex;justify-content:space-between;font-size:.9rem;padding:6px 0;"><span>Harga satuan</span><b>Rp {{ number_format($product->harga,0,',','.') }}</b></div>
        <div style="display:flex;justify-content:space-between;font-size:.9rem;padding:6px 0;border-top:1px solid var(--color-border-light);"><span>Ongkir (<span id="ongkirLabel">-</span>)</span><b id="ongkirVal">Rp 0</b></div>
        <div style="display:flex;justify-content:space-between;font-size:1.1rem;padding:10px 0 0;border-top:1px solid var(--color-border-light);color:var(--color-primary);"><span><b>Total Bayar</b></span><b id="grandTotal">Rp {{ number_format($product->harga,0,',','.') }}</b></div>
      </div>
    </div>
    <div class="auth-centered-card" style="margin:0;">
      <div class="auth-header" style="text-align:left;"><h2 style="font-size:1.3rem;">Data Pengiriman & Pembayaran</h2><p>Stok tersedia: {{ $product->stok }}</p></div>
      <form method="POST" action="{{ route('user.checkout.store', $product) }}" novalidate>@csrf
        <div class="form-group"><label class="form-label">Jumlah*</label><div class="input-wrapper"><input type="number" id="jumlah" name="jumlah" value="{{ old('jumlah', 1) }}" min="1" max="{{ $product->stok }}" class="form-control" required></div></div>
        <div class="form-group"><label class="form-label">Nomor HP / WhatsApp*</label><div class="input-wrapper"><input type="text" name="no_hp" value="{{ old('no_hp', auth()->user()->phone) }}" class="form-control" placeholder="081234567890" required></div></div>
        <div class="form-group"><label class="form-label">Alamat Lengkap*</label><div class="input-wrapper"><textarea name="alamat" class="form-control" rows="3" placeholder="Jalan, nomor rumah, kecamatan, kota/kabupaten, kode pos" required>{{ old('alamat', auth()->user()->alamat ?? '') }}</textarea></div></div>
        <div class="form-group"><label class="form-label">Jasa Ekspedisi*</label><div class="input-wrapper"><select name="expedition" id="expedition" class="form-control" required>
          <option value="">-- Pilih ekspedisi --</option>
          @foreach($expeditions as $key => $ex)<option value="{{ $key }}" data-ongkir="{{ $ex['ongkir'] }}" @selected(old('expedition')==$key)>{{ $ex['label'] }} — Rp {{ number_format($ex['ongkir'],0,',','.') }}</option>@endforeach
        </select></div></div>
        <div class="form-group"><label class="form-label">Metode Pembayaran*</label>
          <div class="nb-paygrid" id="payGrid">
            @foreach($methods as $i => $m)<button type="button" class="nb-paybtn{{ $i === 0 ? ' active' : '' }}" data-pay="{{ $m['key'] }}">{{ $m['label'] }}</button>@endforeach
          </div>
          <input type="hidden" name="payment_method" value="midtrans">
          <input type="hidden" name="pay_channel" id="payChannel" value="transfer">
          <p class="service-meta-text" style="margin-top:6px;">Semua metode dibayar via Midtrans (popup otomatis).</p>
        </div>
        <div class="form-group"><label class="form-label">Catatan (opsional)</label><div class="input-wrapper"><textarea name="catatan" class="form-control" rows="2" placeholder="Contoh: bungkus kado">{{ old('catatan') }}</textarea></div></div>
        <button type="submit" class="btn-pill btn-pill-blue auth-submit-btn"><span>Buat Pesanan</span><div class="btn-arrow-circle"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg></div></button>
      </form>
    </div>
  </div>
</div>
<script>
(function () {
  var harga = {{ $product->harga }};
  var jumlah = document.getElementById('jumlah');
  var ekspedisi = document.getElementById('expedition');
  function rupiah(n) { return 'Rp ' + Number(n).toLocaleString('id-ID'); }
  function hitung() {
    var opt = ekspedisi.options[ekspedisi.selectedIndex];
    var ongkir = opt && opt.dataset.ongkir ? parseInt(opt.dataset.ongkir, 10) : 0;
    var qty = Math.max(parseInt(jumlah.value || 1, 10), 1);
    document.getElementById('ongkirLabel').textContent = opt && opt.value ? opt.text.split(' — ')[0] : '-';
    document.getElementById('ongkirVal').textContent = rupiah(ongkir);
    document.getElementById('grandTotal').textContent = rupiah(harga * qty + ongkir);
  }
  jumlah.addEventListener('input', hitung); ekspedisi.addEventListener('change', hitung); hitung();
  // pilihan metode ala Shopee -> channel Midtrans
  var grid = document.getElementById('payGrid');
  var hid = document.getElementById('payChannel');
  grid.querySelectorAll('[data-pay]').forEach(function (b) {
    b.addEventListener('click', function () {
      grid.querySelectorAll('[data-pay]').forEach(function (x) { x.classList.remove('active'); });
      b.classList.add('active');
      hid.value = b.dataset.pay;
    });
  });
})();
</script>
<style>
.nb-paygrid{display:flex;flex-wrap:wrap;gap:8px;}
.nb-paybtn{border:1px solid var(--color-border);background:#fff;border-radius:10px;padding:9px 14px;font-size:.82rem;font-weight:700;cursor:pointer;color:var(--color-dark);}
.nb-paybtn.active{border:2px solid var(--color-accent);background:#E1F2FE;color:var(--color-primary);padding:8px 13px;}
</style>
@endsection
