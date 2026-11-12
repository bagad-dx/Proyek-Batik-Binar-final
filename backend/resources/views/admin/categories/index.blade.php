@extends('layouts.panel')
@section('title', 'Kategori')
@section('content')
<div class="services-header">
  <span class="section-eyebrow-pill">Admin &bull; Kategori</span>
  <h2 class="section-main-heading">Kategori Produk</h2>
</div>
<div class="backend-card" style="max-width:640px;margin-bottom:18px">
  <form method="POST" action="{{ route('admin.categories.store') }}">
    @csrf
    <div class="form-group"><label class="form-label">Nama Kategori*</label><div class="input-wrapper"><input type="text" name="nama" placeholder="Nama kategori" class="form-control" required></div></div>
    <div class="form-group"><label class="form-label">Deskripsi</label><div class="input-wrapper"><input type="text" name="deskripsi" placeholder="Deskripsi" class="form-control"></div></div>
    <div class="backend-actions"><button type="submit" class="btn-pill btn-pill-blue"><span>Tambah</span><span class="btn-arrow-circle"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg></span></button></div>
  </form>
</div>
<div class="backend-card" style="overflow-x:auto">
  <table class="backend-table">
    <thead><tr><th>Nama</th><th>Deskripsi</th><th>Aksi</th></tr></thead>
    <tbody>
    @foreach($categories as $c)
      <tr>
        <td><strong>{{ $c->nama }}</strong><div class="service-meta-text">{{ $c->products_count }} produk</div></td>
        <td>{{ $c->deskripsi }}</td>
        <td style="text-align:center;white-space:nowrap">
          <form method="POST" action="{{ route('admin.categories.destroy',$c) }}" style="display:inline" onsubmit="return confirm('Hapus?')">@csrf @method('DELETE')<button type="submit" class="btn-pill btn-pill-royal" style="padding:6px 14px;font-size:.8rem"><span>Hapus</span></button></form>
        </td>
      </tr>
    @endforeach
    </tbody>
  </table>
  <div style="margin-top:12px">{{ $categories->links() }}</div>
</div>
@endsection
