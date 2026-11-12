@extends('layouts.panel')
@section('title', ($faq->exists ? 'Edit' : 'Tambah') . ' FAQ Chatbot')
@section('content')
<div class="services-header">
  <span class="section-eyebrow-pill">Superadmin &bull; Chatbot</span>
  <h2 class="section-main-heading">{{ $faq->exists ? 'Edit' : 'Tambah' }} Jawaban Otomatis</h2>
</div>
<div class="backend-card" style="max-width:640px;">
  <form method="POST" action="{{ $faq->exists ? route('superadmin.chatbot.update', $faq) : route('superadmin.chatbot.store') }}">
    @csrf @if($faq->exists) @method('PUT') @endif
    <div class="form-group"><label class="form-label">Judul Pertanyaan* <span class="service-meta-text">(untuk arsip admin)</span></label><div class="input-wrapper"><input type="text" name="pertanyaan" value="{{ old('pertanyaan', $faq->pertanyaan) }}" class="form-control" placeholder="Contoh: Promo bulan ini" required></div></div>
    <div class="form-group"><label class="form-label">Kata Kunci*</label><div class="input-wrapper"><input type="text" name="keywords" value="{{ old('keywords', $faq->keywords) }}" class="form-control" placeholder="promo, diskon, potongan harga" required></div><p class="service-meta-text">Pisahkan dengan koma. Bot menjawab jika <b>semua kata</b> dalam salah satu frasa muncul di chat pembeli. Contoh: <i>"diskon hari ini"</i> cocok untuk keyword <i>"diskon, promo akhir tahun"</i>.</p></div>
    <div class="form-group"><label class="form-label">Jawaban Bot*</label><div class="input-wrapper"><textarea name="jawaban" class="form-control" rows="4" required placeholder="Contoh: Promo bulan ini: gratis ongkir ...">{{ old('jawaban', $faq->jawaban) }}</textarea></div><p class="service-meta-text">Boleh pakai &lt;b&gt;, &lt;i&gt;, &lt;br&gt;.</p></div>
    <div style="display:flex;gap:16px;flex-wrap:wrap;">
      <div class="form-group" style="flex:1;min-width:120px;"><label class="form-label">Urutan</label><div class="input-wrapper"><input type="number" name="urutan" value="{{ old('urutan', $faq->urutan ?? 0) }}" class="form-control" min="0"></div></div>
      <div class="form-group" style="align-self:end;"><label class="checkbox-label"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $faq->is_active ?? true))><span>Aktif</span></label></div>
    </div>
    <div class="backend-actions">
      <button class="btn-pill btn-pill-royal" style="padding:10px 24px;"><span>Simpan</span></button>
      <a href="{{ route('superadmin.chatbot.index') }}" class="underline" style="font-size:.85rem;">Kembali</a>
    </div>
  </form>
</div>
@endsection
