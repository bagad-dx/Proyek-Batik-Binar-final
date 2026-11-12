@extends('layouts.panel')
@section('title', 'Kelola Motif')
@section('content')
<div class="services-header">
  <span class="section-eyebrow-pill">Admin &bull; Koleksi</span>
  <h2 class="section-main-heading">Motif / Koleksi</h2>
</div>
<div class="backend-actions">
  <a href="{{ route('admin.motifs.create') }}" class="btn-pill btn-pill-blue"><span>+ Tambah Motif</span><span class="btn-arrow-circle"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg></span></a>
</div>
<div class="backend-card" style="overflow-x:auto">
  <table class="backend-table">
    <thead><tr><th>Nama</th><th>Asal</th><th>Teknik</th><th>Aksi</th></tr></thead>
    <tbody>
    @foreach($motifs as $m)
      <tr>
        <td><strong>{{ $m->nama }}</strong><div class="service-meta-text">{{ $m->lama_pengerjaan }}</div></td>
        <td>{{ $m->asal }}</td>
        <td>{{ $m->teknik }}</td>
        <td style="text-align:center;white-space:nowrap">
          <a href="{{ route('admin.motifs.edit',$m) }}" class="btn-pill btn-pill-blue" style="padding:6px 14px;font-size:.8rem"><span>Edit</span></a>
          <form method="POST" action="{{ route('admin.motifs.destroy',$m) }}" style="display:inline" onsubmit="return confirm('Hapus?')">@csrf @method('DELETE')<button type="submit" class="btn-pill btn-pill-royal" style="padding:6px 14px;font-size:.8rem"><span>Hapus</span></button></form>
        </td>
      </tr>
    @endforeach
    </tbody>
  </table>
</div>
<div style="margin-top:16px">{{ $motifs->links() }}</div>
@endsection
