/* BATIK BINAR native frontend: Wishlist. */
'use strict';

  /* ================= WISHLIST MODAL (dashboard) ================= */
  function ensureWishModal() {
    let m = document.getElementById('nbWishModal');
    if (m) return m;
    m = document.createElement('div');
    m.id = 'nbWishModal'; m.className = 'nb-modal';
    m.innerHTML = '<div class="nb-modal-card"><div style="display:flex;align-items:center;gap:10px;margin-bottom:10px;"><h3 style="margin:0;flex:1;color:var(--color-primary);">Wishlist Saya</h3><button class="nb-drawer-close" type="button">&times;</button></div><div id="nbWishBody"></div></div>';
    document.body.appendChild(m);
    m.querySelector('.nb-drawer-close').addEventListener('click', () => m.classList.remove('open'));
    m.addEventListener('click', (e) => { if (e.target === m) m.classList.remove('open'); });
    return m;
  }
  async function openWishlistModal() {
    const m = ensureWishModal();
    const body = document.getElementById('nbWishBody');
    m.classList.add('open');
    body.innerHTML = '<p class="service-desc">Memuat...</p>';
    try {
      const items = await api('/wishlist');
      if (!items.length) { body.innerHTML = '<p class="service-desc">Wishlist kosong. Ketuk ♥ pada produk untuk menyimpan.</p>'; return; }
      body.innerHTML = items.map((w) => {
        const p = w.product || {};
        return '<div class="nb-wish-item"><img src="' + esc(imgUrl(p.gambar_url || p.gambar, FALLBACK_IMG)) + '" alt="">' +
          '<div class="inf"><b>' + esc(p.nama || '-') + '</b><span>Rp ' + Number(p.harga || 0).toLocaleString('id-ID') + '</span></div>' +
          '<button type="button" class="nb-mini-btn primary" data-tocart="' + p.id + '">+ Keranjang</button>' +
          '<button type="button" class="nb-mini-btn danger" data-unwish="' + p.id + '">Hapus</button></div>';
      }).join('');
      $$('[data-unwish]', body).forEach((b) => b.addEventListener('click', async () => {
        await api('/wishlist/' + b.getAttribute('data-unwish'), { method: 'DELETE' });
        wishSet.delete(String(b.getAttribute('data-unwish')));
        paintHearts(document);
        openWishlistModal();
      }));
      $$('[data-tocart]', body).forEach((b) => b.addEventListener('click', async () => {
        await addToCart(b.getAttribute('data-tocart'), 1);
        m.classList.remove('open');
      }));
    } catch (err) { body.innerHTML = '<p class="service-desc">' + esc(err.message) + '</p>'; }
  }
  function initWishlistUI() {
    // tombol Wishlist (menu cepat) -> modal
    $$('.dash-action-btn').forEach((a) => {
      const label = (a.querySelector('span') || {}).textContent || '';
      if (/wishlist/i.test(label)) {
        a.addEventListener('click', (e) => { e.preventDefault(); openWishlistModal(); });
      }
    });
  }
