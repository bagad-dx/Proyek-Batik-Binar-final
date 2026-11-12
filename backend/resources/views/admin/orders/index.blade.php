@extends('layouts.panel')
@section('title', 'Pesanan Masuk')
@section('content')
<div class="services-header">
  <span class="section-eyebrow-pill">Admin &bull; Transaksi</span>
  <h2 class="section-main-heading">Pesanan Masuk</h2>
</div>
<div class="backend-card" style="margin-bottom:18px">
  <form method="GET">
    <div class="form-group" style="margin-bottom:0"><label class="form-label">Filter Status</label><div class="input-wrapper"><select name="status" class="form-control" onchange="this.form.submit()">
      <option value="">Semua status</option>
      @foreach(['pending','diproses','dikirim','selesai','batal'] as $s)<option value="{{ $s }}" @selected(request('status')==$s)>{{ $s }}</option>@endforeach
    </select></div></div>
  </form>
</div>
<div class="backend-card" style="overflow-x:auto">
  <table class="backend-table">
    <thead><tr><th>Pembeli</th><th>Produk</th><th>Pembayaran / Ekspedisi</th><th>Total</th><th>Status</th><th>Aksi</th></tr></thead>
    <tbody>
    @foreach($orders as $o)
      <tr>
        <td><strong>{{ $o->user->name ?? '-' }}</strong><div class="service-meta-text">{{ $o->no_hp }} &bull; {{ $o->alamat }}</div></td>
        <td>{{ $o->product->nama ?? '-' }} x{{ $o->jumlah }}</td>
        <td><div>{{ $o->payment_label }}</div><div class="service-meta-text">{{ $o->expedition_label }} (Rp {{ number_format($o->shipping_cost,0,',','.') }})</div></td>
        <td>Rp {{ number_format($o->grand_total,0,',','.') }}<div class="service-meta-text">barang Rp {{ number_format($o->total_harga,0,',','.') }}</div></td>
        <td><span class="role-pill">{{ $o->status }}</span></td>
        <td style="white-space:nowrap">
          <form method="POST" action="{{ route('admin.orders.update',$o) }}" style="display:inline-flex;gap:6px;align-items:center">@csrf @method('PUT')
            <select name="status" class="form-control" style="width:auto">@foreach(['pending','diproses','dikirim','selesai','batal'] as $s)<option value="{{ $s }}" @selected($o->status==$s)>{{ $s }}</option>@endforeach</select>
            <button type="submit" class="btn-pill btn-pill-blue" style="padding:6px 14px;font-size:.8rem"><span>Update</span></button>
          </form>
        </td>
      </tr>
    @endforeach
    </tbody>
  </table>
</div>
<div style="margin-top:16px">{{ $orders->links() }}</div>
@endsection
