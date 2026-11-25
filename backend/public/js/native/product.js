/* BATIK BINAR native frontend: Product. */
'use strict';

  /* ================= DETAIL PRODUK (produk.html) ================= */
  async function initNativeProduct() {
    const wrap = document.getElementById('pdWrap');
    if (!wrap) return;
    const id = new URLSearchParams(window.location.search).get('id');
    if (!id) { wrap.innerHTML = '<p class="service-desc">Produk tidak ditemukan.</p>'; return; }
    try {
      const p = await api('/products/' + id);
      document.getElementById('pdCrumbName').textContent = p.nama || 'Produk';
      document.title = (p.nama || 'Produk') + ' | BATIK BINAR';
      const img = imgUrl(p.gambar_url || p.gambar, FALLBACK_IMG);
      const cat = (p.category && p.category.nama) || 'Batik';
      const specs = [
        ['Motif', p.motif || '-'], ['Bahan', p.bahan || '-'],
        ['Ukuran', p.ukuran || '-'], ['Teknik', p.teknik || '-'],
        ['Kategori', cat], ['Stok', p.stok], ['Terjual', p.terjual || 0],
      ].map((s) => '<div style="display:flex;gap:10px;padding:7px 0;border-bottom:1px solid var(--color-border-light);font-size:.9rem;"><span style="min-width:90px;color:var(--color-text-muted);">' + s[0] + '</span><b>' + esc(String(s[1])) + '</b></div>').join('');
      wrap.innerHTML =
        '<div class="about-grid">' +
        '<div class="about-collage-wrapper"><div class="service-card" style="overflow:hidden;"><div class="service-img-box" style="height:420px;"><img src="' + esc(img) + '" alt="' + esc(p.nama) + '" style="height:100%;object-fit:cover;"></div></div></div>' +
        '<div class="about-content"><span class="section-eyebrow-pill">' + esc(cat) + '</span>' +
        '<h2 class="section-main-heading" style="font-size:2rem;">' + esc(p.nama) + '</h2>' +
        '<p class="service-meta-text">Terjual ' + (p.terjual || 0) + ' • Stok ' + p.stok + '</p>' +
        '<div style="font-size:1.8rem;font-weight:800;color:var(--color-primary);margin:10px 0;">Rp ' + Number(p.harga).toLocaleString('id-ID') + '</div>' +
        '<p class="about-narrative">' + esc(p.deskripsi || '') + '</p>' + specs +
        '<div class="backend-actions" style="align-items:center;"><div class="nb-qty"><button type="button" id="pdMin">−</button><b id="pdQty">1</b><button type="button" id="pdPlus">+</button></div>' +
        '<button type="button" class="btn-pill btn-pill-royal" id="pdCart" style="padding:10px 20px;"><span>+ Keranjang</span></button>' +
        '<button type="button" class="btn-pill btn-pill-blue" id="pdBuy" style="padding:10px 20px;"><span>Beli Sekarang</span></button></div>' +
        '</div></div>';
      const qtyEl = document.getElementById('pdQty');
      const getQty = () => Math.min(p.stok, Math.max(1, parseInt(qtyEl.textContent, 10) || 1));
      document.getElementById('pdMin').addEventListener('click', () => { qtyEl.textContent = Math.max(1, getQty() - 1); });
      document.getElementById('pdPlus').addEventListener('click', () => { qtyEl.textContent = Math.min(p.stok, getQty() + 1); });
      document.getElementById('pdCart').addEventListener('click', () => addToCart(p.id, getQty()));
      document.getElementById('pdBuy').addEventListener('click', () => {
        if (!store.token) { toast('Login dulu untuk membeli.', 'info'); setTimeout(() => { window.location.href = 'login.html'; }, 900); return; }
        window.location.href = 'checkout.html?product_id=' + p.id + '&qty=' + getQty();
      });
      // terkait se-kategori
      try {
        const rel = await api('/products?category_id=' + (p.category ? p.category.id : ''));
        const others = (rel.data || []).filter((x) => String(x.id) !== String(p.id)).slice(0, 4);
        const rw = document.getElementById('pdRelated');
        if (rw && others.length) {
          rw.innerHTML = others.map((r) =>
            '<div class="service-card"><div class="service-img-box" style="height:170px;"><a href="produk.html?id=' + r.id + '"><img src="' + esc(imgUrl(r.gambar_url || r.gambar, FALLBACK_IMG)) + '" alt="' + esc(r.nama) + '" style="height:100%;object-fit:cover;"></a></div>' +
            '<div class="service-body"><h3 class="service-title" style="font-size:.95rem;">' + esc(r.nama) + '</h3><div class="service-footer"><span class="service-meta-text">Rp ' + Number(r.harga).toLocaleString('id-ID') + '</span></div></div></div>'
          ).join('');
        } else if (rw) { rw.innerHTML = '<p class="service-desc">Tidak ada produk sejenis.</p>'; }
      } catch (e) {}
    } catch (err) {
      wrap.innerHTML = '<p class="service-desc">Produk tidak ditemukan.</p>';
    }
  }
