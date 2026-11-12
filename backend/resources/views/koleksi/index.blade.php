@extends('layouts.app')
@section('title', 'Koleksi Motif | BATIK BINAR')
@section('content')
@php $img = fn($path, $fb) => $path ? (str_starts_with($path,'http') ? $path : asset('storage/'.$path)) : $fb; @endphp
<div class="services-header reveal">
  <span class="section-eyebrow-pill">MOTIF IKONIK PAOMAN</span>
  <h2 class="section-main-heading">Koleksi {{ $motifs->total() }} Motif Batik</h2>
  <p style="color:var(--color-text-muted);font-size:1.05rem;">Hasil alih rupa wastra asli Batik Binar. Klik untuk filosofi tiap motif.</p>
</div>
<div class="backend-card" style="margin-bottom:20px;">
  <form method="GET" style="display:flex;gap:10px;">
    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari motif, cth: merak..." class="form-control" style="flex:1;">
    <button class="btn-pill btn-pill-blue" style="padding:10px 22px;"><span>Cari</span></button>
  </form>
</div>
<div class="motif-cards-row">
  @forelse($motifs as $m)
  <div class="motif-destination-card" onclick="openMotifDetail('{{ addslashes($m->nama) }}','{{ addslashes($m->teknik) }}','{{ $img($m->gambar, asset('assets/bg-homepage.jpg')) }}','{{ addslashes(\Illuminate\Support\Str::limit($m->filosofi,180)) }}')">
    <img src="{{ $img($m->gambar, asset('assets/bg-homepage.jpg')) }}" alt="{{ $m->nama }}" class="motif-card-bg-img">
    <div class="motif-card-content"><span class="motif-location-tag">{{ $m->asal }}</span><h3 class="motif-card-title">{{ $m->nama }}</h3><div class="motif-card-bottom-row"><span class="motif-duration-badge">{{ $m->teknik }} • {{ $m->lama_pengerjaan }}</span><div class="btn-circle-arrow" style="width:32px;height:32px;"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg></div></div></div>
  </div>
  @empty<p>Tidak ada motif ditemukan.</p>@endforelse
</div>
<div style="margin-top:16px">{{ $motifs->links() }}</div>
<div class="modal-backdrop" id="previewModal" role="dialog" aria-modal="true">
  <div class="modal-container"><button class="modal-close-btn" id="modalCloseBtn" aria-label="Tutup preview modal" type="button">&times;</button>
    <div class="modal-image-wrapper"><img src="" alt="Pratinjau Batik Binar" id="modalImage"></div>
    <div class="modal-body"><div class="modal-tag" id="modalTag">Batik Paoman</div><h3 class="modal-title" id="modalTitle">Judul Motif</h3><p class="modal-description" id="modalDesc">Deskripsi detail filosofis karya batik.</p></div>
  </div>
</div>
@endsection
