@extends('layouts.panel')
@section('title', ($gallery->exists ? 'Edit' : 'Tambah') . ' Galeri')
@section('content')
<div class="services-header">
  <span class="section-eyebrow-pill">Admin &bull; Galeri</span>
  <h2 class="section-main-heading">{{ $gallery->exists ? 'Edit' : 'Tambah' }} Galeri</h2>
</div>
<div class="backend-card" style="max-width:640px">
  <form method="POST" action="{{ $gallery->exists ? route('admin.galleries.update',$gallery) : route('admin.galleries.store') }}" enctype="multipart/form-data">
    @csrf @if($gallery->exists) @method('PUT') @endif
    <div class="form-group"><label class="form-label">Judul*</label><div class="input-wrapper"><input type="text" name="judul" value="{{ old('judul',$gallery->judul) }}" class="form-control" required></div></div>
    <div class="form-group"><label class="form-label">Kategori*</label><div class="input-wrapper"><input type="text" name="kategori" value="{{ old('kategori',$gallery->kategori ?? 'umum') }}" class="form-control" required placeholder="Editorial Busana / Artisan Craft / Detail Tekstur"></div></div>
    <div class="form-group"><label class="form-label">Deskripsi</label><div class="input-wrapper"><textarea name="deskripsi" rows="3" class="form-control">{{ old('deskripsi',$gallery->deskripsi) }}</textarea></div></div>
    <div class="form-group"><label class="form-label">Filosofi <span class="service-meta-text">(kolom galeri Raka)</span></label><div class="input-wrapper"><textarea name="filosofi" rows="3" class="form-control" placeholder="Makna filosofis motif...">{{ old('filosofi',$gallery->filosofi) }}</textarea></div></div>
    <div class="form-group"><label class="form-label">Tahun Pembuatan <span class="service-meta-text">(kolom galeri Raka)</span></label><div class="input-wrapper"><input type="date" name="tahun_pembuatan" value="{{ old('tahun_pembuatan',$gallery->tahun_pembuatan) }}" class="form-control"></div></div>
    <div class="form-group"><label class="form-label">Tag <span class="service-meta-text">(tb_tag_galeri Raka)</span></label><div class="input-wrapper"><input type="text" name="tag" value="{{ old('tag',$gallery->tag ?? $gallery->kategori) }}" class="form-control" list="tagGaleriList" placeholder="Motif Batik / Proses Pembuatan / ..."></div><datalist id="tagGaleriList"><option>Motif Batik</option><option>Proses Pembuatan</option><option>Detail Tekstur</option><option>Busana Modern</option><option>Sentra Paoman</option></datalist></div>
    <div class="form-group"><label class="form-label">Motif Terkait <span class="service-meta-text">(relasi motif Raka)</span></label><div class="input-wrapper"><select name="motif_id" class="form-control"><option value="">- Tanpa motif -</option>@foreach($motifs ?? [] as $m)<option value="{{ $m->id }}" @selected(old('motif_id',$gallery->motif_id)==$m->id)>{{ $m->nama }}</option>@endforeach</select></div></div>
    <div class="form-group"><label class="form-label"><input type="checkbox" name="is_published" value="1" @checked(old('is_published',$gallery->is_published ?? true))> Publish</label></div>
    <div class="form-group"><label class="form-label">Gambar</label><div class="input-wrapper"><input type="file" name="gambar" class="form-control"></div></div>
    <div class="backend-actions">
      <button type="submit" class="btn-pill btn-pill-blue"><span>Simpan</span><span class="btn-arrow-circle"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg></span></button>
      <a href="{{ route('admin.galleries.index') }}" class="btn-pill btn-pill-royal"><span>Kembali</span><span class="btn-arrow-circle"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg></span></a>
    </div>
  </form>
</div>
@endsection
