@extends('layouts.panel')
@section('title', 'Kelola Galeri')
@section('content')
<div class="services-header">
  <span class="section-eyebrow-pill">Admin &bull; Galeri</span>
  <h2 class="section-main-heading">Galeri Batik</h2>
</div>
<div class="backend-actions">
  <a href="{{ route('admin.galleries.create') }}" class="btn-pill btn-pill-blue"><span>+ Tambah Galeri</span><span class="btn-arrow-circle"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg></span></a>
</div>
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:20px">
  @foreach($galleries as $g)
  <div class="service-card">
    <div class="service-body">
      <h3 class="service-title">{{ $g->judul }}</h3>
      <p class="service-meta-text">{{ $g->kategori }} &bull; {{ $g->is_published ? 'Publish' : 'Draft' }}</p>
      <p class="service-desc">{{ \Illuminate\Support\Str::limit($g->deskripsi, 100) }}</p>
      <div class="service-footer">
        <a href="{{ route('admin.galleries.edit',$g) }}" class="btn-pill btn-pill-blue" style="padding:6px 14px;font-size:.8rem"><span>Edit</span></a>
        <form method="POST" action="{{ route('admin.galleries.destroy',$g) }}" style="display:inline" onsubmit="return confirm('Hapus?')">@csrf @method('DELETE')<button type="submit" class="btn-pill btn-pill-royal" style="padding:6px 14px;font-size:.8rem"><span>Hapus</span></button></form>
      </div>
    </div>
  </div>
  @endforeach
</div>
<div style="margin-top:16px">{{ $galleries->links() }}</div>
@endsection
