@extends('layouts.panel')
@section('title', ($product->exists ? 'Edit' : 'Tambah') . ' Produk')
@section('content')
<div class="services-header">
  <span class="section-eyebrow-pill">Admin &bull; Produk</span>
  <h2 class="section-main-heading">{{ $product->exists ? 'Edit' : 'Tambah' }} Produk</h2>
</div>
<div class="backend-card" style="max-width:640px">
  <form method="POST" action="{{ $product->exists ? route('admin.products.update',$product) : route('admin.products.store') }}" enctype="multipart/form-data">
    @csrf @if($product->exists) @method('PUT') @endif
    <div class="form-group"><label class="form-label">Nama*</label><div class="input-wrapper"><input type="text" name="nama" value="{{ old('nama',$product->nama) }}" class="form-control" required></div></div>
    <div class="form-group"><label class="form-label">Kategori</label><div class="input-wrapper"><select name="category_id" class="form-control"><option value="">- Pilih -</option>@foreach($categories as $c)<option value="{{ $c->id }}" @selected(old('category_id',$product->category_id)==$c->id)>{{ $c->nama }}</option>@endforeach</select></div></div>
    <div class="form-group"><label class="form-label">Jenis Produk <span class="service-meta-text">(filter belanja ala Raka)</span></label><div class="input-wrapper"><select name="jenis_id" class="form-control"><option value="">- Ikuti Kategori -</option>@foreach($jenis ?? [] as $j)<option value="{{ $j->id }}" @selected(old('jenis_id',$product->jenis_id)==$j->id)>{{ $j->label_display }}</option>@endforeach</select></div></div>
    <div class="form-group"><label class="form-label">Deskripsi</label><div class="input-wrapper"><textarea name="deskripsi" class="form-control" rows="3">{{ old('deskripsi',$product->deskripsi) }}</textarea></div></div>
    <div class="form-group"><label class="form-label">Harga*</label><div class="input-wrapper"><input type="number" name="harga" value="{{ old('harga',$product->harga ?? 0) }}" class="form-control" required></div></div>
    <div class="form-group"><label class="form-label">Stok*</label><div class="input-wrapper"><input type="number" name="stok" value="{{ old('stok',$product->stok ?? 0) }}" class="form-control" required></div></div>
    <div class="form-group"><label class="form-label">Motif</label><div class="input-wrapper"><input type="text" name="motif" value="{{ old('motif',$product->motif) }}" class="form-control" placeholder="Iwak Etong"></div></div>
    <div class="form-group"><label class="form-label">Bahan</label><div class="input-wrapper"><input type="text" name="bahan" value="{{ old('bahan',$product->bahan) }}" class="form-control" placeholder="Katun Primissima"></div></div>
    <div class="form-group"><label class="form-label">Ukuran <span class="service-meta-text">(kolom etalase Raka)</span></label><div class="input-wrapper"><input type="text" name="ukuran" value="{{ old('ukuran',$product->ukuran) }}" class="form-control" placeholder="250 × 110 cm / S / M / L / XL"></div></div>
    <div class="form-group"><label class="form-label">Teknik <span class="service-meta-text">(kolom etalase Raka)</span></label><div class="input-wrapper"><input type="text" name="teknik" value="{{ old('teknik',$product->teknik) }}" class="form-control" placeholder="Tulis Complongan"></div></div>
    <div class="form-group"><label class="form-label">Link Shopee Produk <span class="service-meta-text">(opsional — tombol Shopee di halaman pembeli)</span></label><div class="input-wrapper"><input type="url" name="shopee_url" value="{{ old('shopee_url',$product->shopee_url) }}" class="form-control" placeholder="https://shopee.co.id/..."></div></div>
    <div class="form-group"><label class="form-label">Status</label><div class="input-wrapper"><select name="status" class="form-control"><option value="aktif" @selected(old('status',$product->status)=='aktif')>aktif</option><option value="nonaktif" @selected(old('status',$product->status)=='nonaktif')>nonaktif</option><option value="draft" @selected(old('status',$product->status)=='draft')>draft</option></select></div></div>
    <div class="form-group"><label class="form-label">Gambar (jpg/png max 2MB)</label><div class="input-wrapper"><input type="file" name="gambar" class="form-control"></div></div>
    <div class="backend-actions">
      <button type="submit" class="btn-pill btn-pill-blue"><span>Simpan</span><span class="btn-arrow-circle"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg></span></button>
      <a href="{{ route('admin.products.index') }}" class="btn-pill btn-pill-royal"><span>Kembali</span><span class="btn-arrow-circle"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg></span></a>
    </div>
  </form>
</div>
@endsection
