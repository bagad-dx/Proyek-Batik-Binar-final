@extends('layouts.panel')
@section('title', 'Kelola Konten Website')
@section('content')
<div class="services-header">
  <span class="section-eyebrow-pill">Admin &bull; Konten</span>
  <h2 class="section-main-heading">Kelola Semua Postingan Website</h2>
  <p style="color:var(--color-text-muted)">Ubah teks & foto Home, Tentang, Proses, judul Koleksi/Galeri, banner ajakan, dan kontak — langsung tampil di website.</p>
</div>
<form method="POST" action="{{ route('admin.contents.update') }}" enctype="multipart/form-data">@csrf @method('PUT')
@foreach($groups as $section => $items)
<div class="backend-card" style="margin-bottom:18px;">
  <h3 class="service-title">{{ $labels[$section] ?? ucfirst($section) }}</h3>
  @foreach($items as $c)
  <div class="form-group">
    <label class="form-label">{{ $c->label }}</label>
    @if($c->type === 'text')
    <div class="input-wrapper"><input type="text" name="content[{{ $c->key }}]" value="{{ old('content.'.$c->key, $c->value) }}" class="form-control"></div>
    @elseif($c->type === 'textarea' || $c->type === 'html')
    <div class="input-wrapper"><textarea name="content[{{ $c->key }}]" class="form-control" rows="3">{{ old('content.'.$c->key, $c->value) }}</textarea></div>
    @if($c->type === 'html')<p class="service-meta-text">Boleh pakai HTML: &lt;br&gt;, &lt;strong&gt;, &lt;em&gt;, &lt;span class="text-accent"&gt;.</p>@endif
    @elseif($c->type === 'image')
    @php $cur = $c->value ? (str_starts_with($c->value, 'http') ? $c->value : asset('storage/'.$c->value)) : null; @endphp
    @if($cur)<div style="margin-bottom:8px;"><img src="{{ $cur }}" alt="" style="max-height:110px;border-radius:12px;border:1px solid var(--color-border-light);"></div>@endif
    <div class="input-wrapper" style="margin-bottom:8px;"><input type="text" name="image_url[{{ $c->key }}]" value="{{ str_starts_with($c->value ?? '', 'http') ? $c->value : '' }}" class="form-control" placeholder="URL gambar (https://...)"></div>
    <div class="input-wrapper"><input type="file" name="image_file[{{ $c->key }}]" class="form-control" accept="image/*"></div>
    <p class="service-meta-text">Upload mengalahkan URL. Kosongkan keduanya = biarkan seperti sekarang.</p>
    @endif
  </div>
  @endforeach
</div>
@endforeach
<div class="backend-actions" style="position:sticky;bottom:16px;">
  <button type="submit" class="btn-pill btn-pill-blue"><span>Simpan Semua Konten</span><span class="btn-arrow-circle"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg></span></button>
</div>
</form>
@endsection
