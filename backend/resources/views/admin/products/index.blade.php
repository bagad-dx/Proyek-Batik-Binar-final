@extends('layouts.panel')
@section('title', 'Kelola Produk')
@section('content')
<div class="services-header">
  <span class="section-eyebrow-pill">Admin &bull; Jual Beli</span>
  <h2 class="section-main-heading">Produk Jual Beli</h2>
</div>
<div class="backend-actions">
  <a href="{{ route('admin.products.create') }}" class="btn-pill btn-pill-blue"><span>+ Tambah Produk</span><span class="btn-arrow-circle"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg></span></a>
</div>
<div class="backend-card" style="margin-bottom:18px">
  <form method="GET">
    <div class="form-group" style="display:flex;gap:10px;margin-bottom:0">
      <div class="input-wrapper" style="flex:1"><input type="text" name="search" value="{{ request('search') }}" placeholder="Cari produk..." class="form-control"></div>
      <button type="submit" class="btn-pill btn-pill-royal"><span>Cari</span><span class="btn-arrow-circle"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg></span></button>
    </div>
  </form>
</div>
<div class="backend-card" style="overflow-x:auto">
  <table class="backend-table">
    <thead><tr><th>Nama</th><th>Harga</th><th>Stok</th><th>Status</th><th>Aksi</th></tr></thead>
    <tbody>
    @foreach($products as $p)
      <tr>
        <td><strong>{{ $p->nama }}</strong><div class="service-meta-text">{{ $p->category->nama ?? '-' }} &bull; {{ $p->motif }}</div></td>
        <td>Rp {{ number_format($p->harga,0,',','.') }}</td>
        <td style="text-align:center">{{ $p->stok }}</td>
        <td style="text-align:center"><span class="role-pill">{{ $p->status }}</span></td>
        <td style="text-align:center;white-space:nowrap">
          <a href="{{ route('admin.products.edit',$p) }}" class="btn-pill btn-pill-blue" style="padding:6px 14px;font-size:.8rem"><span>Edit</span></a>
          <form method="POST" action="{{ route('admin.products.destroy',$p) }}" style="display:inline" onsubmit="return confirm('Hapus?')">@csrf @method('DELETE')<button type="submit" class="btn-pill btn-pill-royal" style="padding:6px 14px;font-size:.8rem"><span>Hapus</span></button></form>
        </td>
      </tr>
    @endforeach
    </tbody>
  </table>
</div>
<div style="margin-top:16px">{{ $products->links() }}</div>
@endsection
