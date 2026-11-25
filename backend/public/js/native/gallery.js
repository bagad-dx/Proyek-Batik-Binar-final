/* BATIK BINAR native frontend: Gallery. */
'use strict';

  /* ---------- galeri.html: marquee motif + pil tag + grid live (port galeri.php Raka) ----------
     Marquee dibaca dari /api/motifs (ganti statis), pil dari /api/tags
     (tb_tag_galeri), kartu dari /api/galleries (filosofi+tahun+tag+motif
     ala tb_galeri). Gagal API = biarkan render statis script.js. */
  async function initNativeGaleri() {
    const grid = document.getElementById('galeriGrid');
    if (!grid) return;
    try {
      let page = 1, pages = 1;
      const names = [];
      do {
        const res = await api('/motifs?page=' + page);
        (res.data || []).forEach((x) => { if (x.nama) names.push(x.nama); });
        pages = res.last_page || 1;
        page++;
      } while (page <= pages);
      const track = document.querySelector('.gallery-marquee-track');
      if (track && names.length) {
        const half = names.map((n) => '<span class="marquee-item">' + esc(n) + '</span>').join('');
        track.innerHTML = half + half;
      }
    } catch (e) {}
    let items = [];
    try {
      let page = 1, pages = 1;
      do {
        const res = await api('/galleries?page=' + page);
        items = items.concat(res.data || []);
        pages = res.last_page || 1;
        page++;
      } while (page <= pages);
    } catch (e) {}
    if (!items.length) return;
    const card = (g) => {
      const img = imgUrl(g.gambar_url || g.gambar, FALLBACK_IMG);
      const tag = g.tag || g.kategori || 'Galeri';
      const title = g.judul || ((g.motif && g.motif.nama) || 'Galeri');
      const desc = g.filosofi || g.deskripsi || '';
      const year = g.tahun_pembuatan ? ' \u2022 ' + String(g.tahun_pembuatan).slice(0, 4) : '';
      return '<div class="gallery-page-card" data-category="' + esc(tag) + '" data-title="' + esc(title) + '" data-tag="' + esc(tag) + '" data-desc="' + esc(desc) + '">' +
        '<img src="' + esc(img) + '" alt="' + esc(title) + '" width="800" height="600" loading="lazy" decoding="async">' +
        '<div class="gallery-page-overlay"><span class="gallery-page-tag">' + esc(tag) + year + '</span>' +
        '<h4 class="gallery-page-title">' + esc(title) + '</h4></div></div>';
    };
    grid.innerHTML = items.map(card).join('');
    const count = document.getElementById('galeriSearchCount');
    if (count) count.textContent = items.length + ' koleksi';
    grid.querySelectorAll('.gallery-page-card').forEach((el) => {
      el.style.cursor = 'pointer';
      el.addEventListener('click', () => {
        const modal = document.getElementById('galeriModal');
        if (!modal) return;
        const mi = document.getElementById('galeriModalImg');
        if (mi) mi.src = el.querySelector('img').getAttribute('src');
        const t = (id, v) => { const n = document.getElementById(id); if (n) n.textContent = v; };
        t('galeriModalTag', el.getAttribute('data-tag'));
        t('galeriModalTitle', el.getAttribute('data-title'));
        t('galeriModalDesc', el.getAttribute('data-desc') || 'Koleksi mahakarya Batik Binar pesisir Dermayon, Indramayu.');
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
      });
    });
    const filterBar = document.querySelector('.gallery-filter-bar');
    if (filterBar) {
      try {
        const tags = await api('/tags');
        if (Array.isArray(tags) && tags.length) {
          filterBar.innerHTML = '<button class="gallery-filter-btn active" data-filter="semua">Semua</button>' +
            tags.map((t) => '<button class="gallery-filter-btn" data-filter="' + esc(t.label) + '">' + esc(t.label) + '</button>').join('');
        }
      } catch (e) {}
      const fresh = filterBar.cloneNode(true);
      filterBar.parentNode.replaceChild(fresh, filterBar);
      const input = document.getElementById('galeriSearchInput');
      let cf = 'semua', cq = '';
      const apply = () => {
        let n = 0;
        grid.querySelectorAll('.gallery-page-card').forEach((el) => {
          const tg = (el.getAttribute('data-tag') || '').toLowerCase();
          const okF = cf === 'semua' || tg === cf.toLowerCase();
          const hay = ((el.getAttribute('data-title') || '') + ' ' + (el.getAttribute('data-tag') || '') + ' ' + (el.getAttribute('data-desc') || '')).toLowerCase();
          const show = okF && (!cq || hay.includes(cq));
          el.style.display = show ? '' : 'none';
          if (show) n++;
        });
        if (count) { const tot = grid.querySelectorAll('.gallery-page-card').length; const filt = cf !== 'semua' || cq !== ''; count.textContent = filt ? n + ' dari ' + tot + ' koleksi' : tot + ' koleksi'; }
      };
      fresh.addEventListener('click', (e) => {
        const btn = e.target.closest('.gallery-filter-btn');
        if (!btn) return;
        fresh.querySelectorAll('.gallery-filter-btn').forEach((b) => b.classList.remove('active'));
        btn.classList.add('active');
        cf = btn.getAttribute('data-filter');
        apply();
      });
      if (input) {
        const ni = input.cloneNode(true);
        input.parentNode.replaceChild(ni, input);
        ni.addEventListener('input', () => { cq = ni.value.trim().toLowerCase(); apply(); });
      }
    }
  }

  /* Selaraskan token native ke sesi web (mode gabung saja): bila cookie sesi
     web milik akun berbeda (atau token basi), ganti token agar halaman
     native tidak menampilkan akun yang salah. Tanpa sesi web: diam. */
