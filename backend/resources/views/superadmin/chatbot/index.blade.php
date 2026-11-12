@extends('layouts.panel')
@section('title', 'Kelola Chatbot')
@section('content')
<div class="services-header">
  <span class="section-eyebrow-pill">Superadmin &bull; Chatbot</span>
  <h2 class="section-main-heading">Kelola Chatbot Binar</h2>
  <p style="color:var(--color-text-muted)">Atur nama, sapaan, status bot + daftar jawaban otomatis (FAQ). FAQ kustom diprioritaskan di atas jawaban bawaan.</p>
</div>

<div class="backend-card" style="max-width:640px;margin-bottom:20px;">
  <h3 class="service-title">Pengaturan Bot</h3>
  <form method="POST" action="{{ route('superadmin.chatbot.settings') }}">@csrf @method('PUT')
    <div class="form-group"><label class="form-label">Nama Bot*</label><div class="input-wrapper"><input type="text" name="bot_name" value="{{ old('bot_name', $settings['bot_name']) }}" class="form-control" required maxlength="50"></div></div>
    <div class="form-group"><label class="form-label">Sapaan Pembuka*</label><div class="input-wrapper"><textarea name="greeting" class="form-control" rows="3" required>{{ old('greeting', $settings['greeting']) }}</textarea></div><p class="service-meta-text">Boleh pakai tag &lt;b&gt;, &lt;i&gt;, &lt;br&gt;.</p></div>
    <div class="form-group"><label class="checkbox-label"><input type="checkbox" name="is_active" value="1" @checked($settings['is_active'] === '1')><span>Bot aktif (tampilkan & layani chat)</span></label></div>
    <h3 class="service-title" style="margin-top:18px;">Kecerdasan Buatan (opsional)</h3>
    <div class="form-group"><label class="form-label">Mode AI</label><div class="input-wrapper"><select name="ai_provider" class="form-control">
      <option value="off" @selected(old('ai_provider', $settings['ai_provider']) === 'off')>Mati — pakai aturan bawaan saja</option>
      <option value="gemini" @selected(old('ai_provider', $settings['ai_provider']) === 'gemini')>Gemini (butuh API key)</option>
    </select></div><p class="service-meta-text">Jika AI aktif & gagal (key salah/limit habis), bot otomatis turun ke jawaban aturan.</p></div>
    <div class="form-group"><label class="form-label">Model Gemini</label><div class="input-wrapper"><input type="text" name="ai_model" value="{{ old('ai_model', $settings['ai_model']) }}" class="form-control" placeholder="gemini-2.0-flash"></div></div>
    <div class="form-group"><label class="form-label">Gemini API Key {{ $settings['ai_key_set'] ? '(sudah terisi — isi lagi untuk mengganti)' : '(belum diisi)' }}</label><div class="input-wrapper"><input type="password" name="ai_api_key" value="" class="form-control" placeholder="AIza..." autocomplete="off"></div><p class="service-meta-text">Gratis di <b>aistudio.google.com → Get API key</b>. Kunci hanya tersimpan di server, tak terlihat pembeli.</p></div>
    <button class="btn-pill btn-pill-royal" style="padding:10px 24px;"><span>Simpan Pengaturan</span></button>
  </form>
  <form method="POST" action="{{ route('superadmin.chatbot.test') }}" style="margin-top:10px;">@csrf
    <button class="btn-pill btn-pill-blue" style="padding:10px 24px;"><span>Tes Koneksi AI</span></button>
  </form>
</div>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;flex-wrap:wrap;gap:10px;">
  <h3 class="service-title" style="margin:0;">Jawaban Otomatis (FAQ)</h3>
  <a href="{{ route('superadmin.chatbot.create') }}" class="btn-pill btn-pill-blue" style="padding:8px 18px;font-size:.85rem;"><span>+ Tambah FAQ</span></a>
</div>
<div class="backend-card" style="overflow-x:auto;">
  <table class="backend-table">
    <thead><tr><th>#</th><th>Pertanyaan</th><th>Kata Kunci</th><th>Status</th><th>Aksi</th></tr></thead>
    <tbody>
    @forelse($faqs as $f)
      <tr>
        <td>{{ $f->urutan }}</td>
        <td><strong>{{ $f->pertanyaan }}</strong><div class="service-meta-text">{{ \Illuminate\Support\Str::limit(strip_tags($f->jawaban), 80) }}</div></td>
        <td><span class="service-meta-text">{{ $f->keywords }}</span></td>
        <td>{{ $f->is_active ? 'Aktif' : 'Mati' }}</td>
        <td style="white-space:nowrap;">
          <a href="{{ route('superadmin.chatbot.edit', $f) }}" class="text-blue-700 underline" style="font-size:.82rem;">Edit</a>
          <form method="POST" action="{{ route('superadmin.chatbot.toggle', $f) }}" class="inline" style="display:inline;">@csrf @method('PATCH')<button class="underline" style="font-size:.82rem;color:#B45309;margin-left:8px;">{{ $f->is_active ? 'Matikan' : 'Aktifkan' }}</button></form>
          <form method="POST" action="{{ route('superadmin.chatbot.destroy', $f) }}" style="display:inline;" onsubmit="return confirm('Hapus FAQ ini?')">@csrf @method('DELETE')<button class="underline" style="font-size:.82rem;color:#B91C1C;margin-left:8px;">Hapus</button></form>
        </td>
      </tr>
    @empty
      <tr><td colspan="5" class="service-desc">Belum ada FAQ kustom. Bot memakai jawaban bawaan (best seller, motif, ongkir, dll otomatis dari database).</td></tr>
    @endforelse
    </tbody>
  </table>
</div>
<div style="margin-top:16px">{{ $faqs->links() }}</div>
@endsection
