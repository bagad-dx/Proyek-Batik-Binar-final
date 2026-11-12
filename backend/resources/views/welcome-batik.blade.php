@extends('layouts.app')
@section('title', 'BATIK BINAR - Warisan Batik Indramayu Untuk Generasi Masa Kini')
@section('fullwidth')
@php
  $img = fn($path, $fallback) => $path ? (str_starts_with($path, 'http') ? $path : asset('storage/'.$path)) : $fallback;
@endphp
<main>
  {{-- HERO persis frontend awal --}}
  <section class="hero-wrapper" id="beranda">
    <div class="container hero-container">
      <div class="hero-content-col reveal">
        <span class="hero-tagline-capsule">{{ site_content('hero_badge', 'WARISAN • PESISIR • MAHASENI') }}</span>
        <h1 class="hero-heading">{!! site_content('hero_title', 'Warisan Batik <br><span class="text-accent">Indramayu</span>, Untuk <br>Generasi Masa Kini') !!}</h1>
        <p class="hero-desc">{!! site_content('hero_desc', 'Menghadirkan keindahan wastra otentik pesisir Dermayon.') !!}</p>
        <div style="display:flex;gap:12px;flex-wrap:wrap;">
          <a href="{{ route('shop.index') }}" class="btn-pill btn-pill-blue"><span>{{ site_content('hero_btn1', 'Belanja Sekarang') }}</span><div class="btn-arrow-circle"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg></div></a>
          <a href="#motif" class="btn-pill btn-pill-royal"><span>{{ site_content('hero_btn2', 'Jelajahi Koleksi') }}</span><div class="btn-arrow-circle"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg></div></a>
        </div>
      </div>
      <div class="hero-capsules-group reveal">
        <div class="capsules-row">
          <div class="capsule-card" onclick="location.href='{{ route('shop.index') }}'"><img src="https://i.pinimg.com/1200x/7b/7d/a7/7b7da76e1d305f43cf9ddda3e83f8bc3.jpg" alt="Batik Sutra Indramayu" class="capsule-img"><div class="capsule-overlay"><span class="capsule-label">Kain Sutra</span></div></div>
          <div class="capsule-card" onclick="location.href='#tentang'"><img src="https://i.pinimg.com/736x/ed/1b/c9/ed1bc9abe706ba2058fcf4b51df68761.jpg" alt="Canting Tulis Paoman" class="capsule-img"><div class="capsule-overlay"><span class="capsule-label">Tulis Paoman</span></div></div>
          <div class="capsule-card" onclick="location.href='{{ route('shop.index') }}'"><img src="https://i.pinimg.com/736x/1d/a9/a9/1da9a95dcad5e88499d42625ec9e7701.jpg" alt="Busana Modern Binar" class="capsule-img"><div class="capsule-overlay"><span class="capsule-label">Moda Modern</span></div></div>
        </div>
        <div class="capsules-nav-controls">
          <button class="capsule-nav-btn" id="capsulePrevBtn" aria-label="Sebelumnya"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"></polyline></svg></button>
          <button class="capsule-nav-btn" id="capsuleNextBtn" aria-label="Selanjutnya"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"></polyline></svg></button>
        </div>
      </div>
    </div>
    <div class="hero-bottom-curve"><svg viewBox="0 0 1200 120" preserveAspectRatio="none"><path d="M0,0 C300,90 900,90 1200,0 L1200,120 L0,120 Z"></path></svg></div>
  </section>

  {{-- TENTANG persis frontend --}}
  <section class="about-section" id="tentang">
    <div class="container"><div class="about-grid">
      <div class="about-collage-wrapper reveal"><div class="about-brush-splash"></div><div class="about-brush-splash-bottom"></div>
        <div class="about-collage-grid">
          <div class="collage-item collage-item-top"><img src="{{ site_image('tentang_img1', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSZfmOGClQiubBQW8V3PQIcRQjfK9tvMdOixdzr4qqIz5oEy_xRvgWSUMg&s=10') }}" alt="Pembatik Paoman"></div>
          <div class="collage-item collage-item-bottom-1"><img src="{{ site_image('tentang_img2', 'https://i.pinimg.com/1200x/40/9f/af/409fafab21f085588a2c6cb3c254c245.jpg') }}" alt="Detail Canting dan Kain"></div>
          <div class="collage-item collage-item-bottom-2"><img src="{{ site_image('tentang_img3', 'https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTytAskEXFa5AgtYqdWMRj09YU2Vu7F6g6AMw9TiZno94uWkbqit3L0o4g&s=10') }}" alt="Kain Batik Tulis Siap Pakai"></div>
        </div>
      </div>
      <div class="about-content reveal"><span class="section-eyebrow-pill">{{ site_content('tentang_eyebrow', 'TENTANG KAMI') }}</span>
        <h2 class="section-main-heading">{{ site_content('tentang_heading', 'Mengapa Memilih Batik Binar Dermayon') }}</h2>
        <p class="about-narrative">{!! site_content('tentang_narrative1') !!}</p>
        <p class="about-narrative">{!! site_content('tentang_narrative2') !!}</p>
        <a href="#proses" class="btn-pill btn-pill-royal"><span>{{ site_content('tentang_btn', 'Pelajari Selengkapnya') }}</span><div class="btn-arrow-circle"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg></div></a>
      </div>
    </div></div>
  </section>

  {{-- PROSES 6 tahap persis frontend --}}
  <section class="services-section" id="proses">
    <div class="container">
      <div class="services-header reveal"><span class="section-eyebrow-pill">{{ site_content('proses_eyebrow', 'PROSES & KEUNGGULAN') }}</span><h2 class="section-main-heading">{{ site_content('proses_heading', 'Dedikasi Mahakarya Dari Hulu Ke Hilir') }}</h2><p style="color:var(--color-text-muted);font-size:1.05rem;">{{ site_content('proses_desc') }}</p></div>
      <div class="services-grid-6">
        @for ($n = 1; $n <= 6; $n++)
        <div class="service-card reveal"><div class="service-img-box"><img src="{{ site_image("proses_{$n}_image") }}" alt="{{ site_content("proses_{$n}_title") }}"></div>
          <div class="service-body"><h3 class="service-title">{{ site_content("proses_{$n}_title") }}</h3><p class="service-desc">{{ site_content("proses_{$n}_desc") }}</p><div class="service-footer"><span class="service-meta-text">{{ site_content("proses_{$n}_tag") }}</span></div></div>
        </div>
        @endfor
      </div>
    </div>
  </section>

  {{-- MOTIF dinamis dari DB --}}
  <section class="motif-banner-section" id="motif">
    <div class="container"><div class="motif-banner-grid">
      <div class="motif-banner-content reveal"><span class="section-eyebrow-pill">{{ site_content('motif_eyebrow', 'MOTIF IKONIK PAOMAN') }}</span><h2 class="motif-banner-heading">{{ site_content('motif_heading', 'Koleksi Motif Autentik Pesisir Dermayon') }}</h2><p class="motif-banner-desc">{{ site_content('motif_desc') }}</p><div style="display:flex;gap:12px;flex-wrap:wrap;"><a href="{{ route('shop.index') }}" class="btn-pill btn-pill-blue"><span>Belanja Motif Ini</span><div class="btn-arrow-circle"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg></div></a><a href="{{ route('koleksi.index') }}" class="btn-pill btn-pill-royal"><span>Lihat Semua Koleksi</span><div class="btn-arrow-circle"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg></div></a></div></div>
      <div class="motif-cards-row reveal">
        @forelse($motifs as $m)
        <div class="motif-destination-card" onclick="openMotifDetail('{{ addslashes($m->nama) }}','{{ addslashes($m->teknik) }}','{{ $img($m->gambar, asset('assets/bg-homepage.jpg')) }}','{{ addslashes(\Illuminate\Support\Str::limit($m->filosofi,180)) }}')">
          <img src="{{ $img($m->gambar, asset('assets/bg-homepage.jpg')) }}" alt="{{ $m->nama }}" class="motif-card-bg-img">
          <div class="motif-card-content"><span class="motif-location-tag">{{ $m->asal }}</span><h3 class="motif-card-title">{{ $m->nama }}</h3><div class="motif-card-bottom-row"><span class="motif-duration-badge">{{ $m->teknik }} • {{ $m->lama_pengerjaan }}</span><div class="btn-circle-arrow" style="width:32px;height:32px;"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg></div></div></div>
        </div>
        @empty
        <div class="motif-destination-card"><div class="motif-card-content"><h3 class="motif-card-title">Iwak Etong</h3><p style="font-size:.85rem;">Motif bahari khas Paoman.</p></div></div>
        @endforelse
      </div>
    </div></div>
  </section>

  {{-- GALERI dinamis dari DB --}}
  <section class="gallery-section" id="galeri">
    <div class="container">
      <div class="services-header reveal"><span class="section-eyebrow-pill">{{ site_content('galeri_eyebrow', 'GALERI WASATRA') }}</span><h2 class="section-main-heading">{{ site_content('galeri_heading', 'Harmoni Tradisi & Gaya Hidup Modern') }}</h2><p style="color:var(--color-text-muted);font-size:1.05rem;">{{ site_content('galeri_desc') }}</p></div>
      <div class="gallery-grid-modern reveal">
        @forelse($galleries as $g)
        <div class="gallery-modern-card" onclick="openMotifDetail('{{ addslashes($g->judul) }}','{{ addslashes($g->kategori) }}','{{ $img($g->gambar, asset('assets/bg-homepage.jpg')) }}','{{ addslashes(\Illuminate\Support\Str::limit($g->deskripsi,180)) }}')">
          <img src="{{ $img($g->gambar, asset('assets/bg-homepage.jpg')) }}" alt="{{ $g->judul }}">
          <div class="gallery-modern-overlay"><span class="gallery-modern-tag">{{ $g->kategori }}</span><h4 class="gallery-modern-title">{{ $g->judul }}</h4></div>
        </div>
        @empty<p>Belum ada galeri. Admin dapat menambah dari dashboard.</p>@endforelse
      </div>
    </div>
  </section>

  {{-- CTA persis frontend --}}
  <section class="cta-section" id="cta">
    <div class="container"><div class="cta-banner-pill reveal"><div class="cta-banner-content">
      <span class="hero-tagline-capsule" style="color:#BAE6FD;">{{ site_content('cta_tag', 'GERAKAN PELESTARIAN BUDAYA') }}</span>
      <h2 class="cta-banner-heading">{!! site_content('cta_heading', 'Kenali Warisan. <br>Hidupkan Budaya.') !!}</h2>
      <p class="cta-banner-desc">{{ site_content('cta_desc') }}</p>
      <div class="cta-btn-group">
        @guest
        <a href="{{ route('register') }}" class="btn-pill btn-pill-white"><span>Daftar Sekarang</span><div class="btn-arrow-circle"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg></div></a>
        <a href="{{ route('login') }}" class="btn-pill btn-pill-blue"><span>Masuk ke Akun</span><div class="btn-arrow-circle"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg></div></a>
        @else
        <a href="{{ route('shop.index') }}" class="btn-pill btn-pill-white"><span>Mulai Belanja</span><div class="btn-arrow-circle"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg></div></a>
        <a href="{{ route('user.orders.index') }}" class="btn-pill btn-pill-blue"><span>Pesanan Saya</span><div class="btn-arrow-circle"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg></div></a>
        @endauth
      </div>
    </div></div></div>
  </section>
</main>

{{-- modal lightbox persis frontend --}}
<div class="modal-backdrop" id="previewModal" role="dialog" aria-modal="true">
  <div class="modal-container"><button class="modal-close-btn" id="modalCloseBtn" aria-label="Tutup preview modal" type="button">&times;</button>
    <div class="modal-image-wrapper"><img src="" alt="Pratinjau Batik Binar" id="modalImage"></div>
    <div class="modal-body"><div class="modal-tag" id="modalTag">Batik Paoman</div><h3 class="modal-title" id="modalTitle">Judul Motif</h3><p class="modal-description" id="modalDesc">Deskripsi detail filosofis karya batik.</p></div>
  </div>
</div>
@endsection
