@extends('layouts.panel')
@section('title', ($motif->exists ? 'Edit' : 'Tambah') . ' Motif')
@section('content')
<div class="services-header">
  <span class="section-eyebrow-pill">Admin &bull; Motif</span>
  <h2 class="section-main-heading">{{ $motif->exists ? 'Edit' : 'Tambah' }} Motif</h2>
</div>
<div class="backend-card" style="max-width:640px">
  <form method="POST" action="{{ $motif->exists ? route('admin.motifs.update',$motif) : route('admin.motifs.store') }}" enctype="multipart/form-data">
    @csrf @if($motif->exists) @method('PUT') @endif
    <div class="form-group"><label class="form-label">Nama*</label><div class="input-wrapper"><input type="text" name="nama" value="{{ old('nama',$motif->nama) }}" class="form-control" required placeholder="Iwak Etong"></div></div>
    <div class="form-group"><label class="form-label">Asal</label><div class="input-wrapper"><input type="text" name="asal" value="{{ old('asal',$motif->asal ?? 'Paoman, Indramayu') }}" class="form-control"></div></div>
    <div class="form-group"><label class="form-label">Filosofi</label><div class="input-wrapper"><textarea name="filosofi" rows="3" class="form-control">{{ old('filosofi',$motif->filosofi) }}</textarea></div></div>
    <div class="form-group"><label class="form-label">Lama Pengerjaan</label><div class="input-wrapper"><input type="text" name="lama_pengerjaan" value="{{ old('lama_pengerjaan',$motif->lama_pengerjaan) }}" class="form-control" placeholder="40 Hari"></div></div>
    <div class="form-group"><label class="form-label">Teknik</label><div class="input-wrapper"><input type="text" name="teknik" value="{{ old('teknik',$motif->teknik ?? 'Tulis Complongan') }}" class="form-control"></div></div>
    <div class="form-group"><label class="form-label"><input type="checkbox" name="is_published" value="1" @checked(old('is_published',$motif->is_published ?? true))> Publish</label></div>
    <div class="form-group"><label class="form-label">Gambar</label><div class="input-wrapper"><input type="file" name="gambar" class="form-control"></div></div>
    <div class="backend-actions">
      <button type="submit" class="btn-pill btn-pill-blue"><span>Simpan</span><span class="btn-arrow-circle"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg></span></button>
      <a href="{{ route('admin.motifs.index') }}" class="btn-pill btn-pill-royal"><span>Kembali</span><span class="btn-arrow-circle"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg></span></a>
    </div>
  </form>
</div>
@endsection
