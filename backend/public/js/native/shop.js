/* BATIK BINAR native frontend: Shop. */
'use strict';

  /* ---------- belanja.php: grid + checkout dari API ---------- */
  let API_PRODUCTS = [];
  let API_META = null;
  async function loadMeta() {
    if (API_META) return API_META;
    try { API_META = await api('/meta'); } catch (e) { API_META = { expeditions: {}, payments: {}, shopee_store_url: '' }; }
    return API_META;
  }

  function productCard(p) {
    const img = imgUrl(p.gambar_url || p.gambar, FALLBACK_IMG);
    const cat = (p.category && p.category.nama) || 'Batik';
    return '<div class="shop-product-card" data-id="' + p.id + '">' +
      '<div class="shop-product-img"><img src="' + esc(img) + '" alt="' + esc(p.nama) + '" loading="lazy">' +
      '<span class="shop-product-badge">' + esc(cat) + '</span>' +
      '<button class="nb-heart" data-wish="' + p.id + '" aria-label="Wishlist" title="Wishlist">♡</button></div>' +
      '<div class="shop-product-body"><div class="shop-product-category">' + esc(cat) + '</div>' +
      '<h3 class="shop-product-name">' + esc(p.nama) + '</h3>' +
      ((p.teknik || p.bahan || p.ukuran) ? '<div class="shop-product-specs">' + esc([p.teknik, p.bahan, p.ukuran].filter(Boolean).join(' \u2022 ')) + '</div>' : '') +
      '<div class="shop-product-price"><span class="price-currency">Rp</span> ' + Number(p.harga).toLocaleString('id-ID') + '</div>' +
      '<div class="shop-product-footer"><span class="shop-product-teknik">Stok ' + p.stok + '</span><span style="display:flex;gap:6px;">' +
      '<button class="shop-product-detail-btn" data-add="' + p.id + '" aria-label="Tambah Keranjang" title="Tambah Keranjang"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path><line x1="3" y1="6" x2="21" y2="6"></line><path d="M16 10a4 4 0 0 1-8 0"></path></svg></button>' +
      '<button class="shop-product-detail-btn" aria-label="Lihat Detail"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg></button></span>' +
      '</div></div></div>';
  }

  function ensureShopModal() {
    let modal = document.getElementById('nativeShopModal');
    if (modal) return modal;
    modal = document.createElement('div');
    modal.id = 'nativeShopModal';
    modal.className = 'modal-backdrop';
    modal.innerHTML = '<div class="modal-container" style="max-width:560px;">' +
      '<button class="modal-close-btn" type="button" aria-label="Tutup">&times;</button>' +
      '<div class="modal-image-wrapper"><img src="" alt="" id="nativeShopImg"></div>' +
      '<div class="modal-body"><div class="modal-tag" id="nativeShopCat"></div>' +
      '<h3 class="modal-title" id="nativeShopTitle"></h3>' +
      '<p class="modal-description" id="nativeShopDesc"></p>' +
      '<div id="nativeShopBuy" style="display:flex;gap:10px;flex-wrap:wrap;margin-top:14px;"></div>' +
      '<div id="nativeCheckout" style="display:none;margin-top:14px;"></div>' +
      '</div></div>';
    document.body.appendChild(modal);
    modal.querySelector('.modal-close-btn').addEventListener('click', closeShopModal);
    modal.addEventListener('click', (e) => { if (e.target === modal) closeShopModal(); });
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeShopModal(); });
    return modal;
  }
  function closeShopModal() {
    const modal = document.getElementById('nativeShopModal');
    if (modal) modal.classList.remove('active');
    document.body.style.overflow = '';
  }

  function openShopDetailApi(id) {
    const p = API_PRODUCTS.find((x) => String(x.id) === String(id));
    if (!p) return;
    const modal = ensureShopModal();
    document.getElementById('nativeShopImg').src = imgUrl(p.gambar_url || p.gambar, FALLBACK_IMG);
    document.getElementById('nativeShopCat').textContent = (p.category && p.category.nama) || 'Batik Binar';
    document.getElementById('nativeShopTitle').textContent = p.nama;
    document.getElementById('nativeShopDesc').textContent =
      (p.deskripsi || '') + ' • Motif: ' + (p.motif || '-') + ' • Bahan: ' + (p.bahan || '-') +
      ' • Ukuran: ' + (p.ukuran || '-') + ' • Teknik: ' + (p.teknik || '-') +
      ' • Stok: ' + p.stok + ' • Rp ' + Number(p.harga).toLocaleString('id-ID');
    const buyBox = document.getElementById('nativeShopBuy');
    const coBox = document.getElementById('nativeCheckout');
    coBox.style.display = 'none';
    coBox.innerHTML = '';
    buyBox.innerHTML = '';
    const mkBtn = (label, cls, fn) => {
      const b = document.createElement('button');
      b.type = 'button'; b.className = 'btn-pill ' + cls;
      b.style.cssText = 'padding:10px 20px;font-size:.85rem;';
      b.innerHTML = '<span>' + label + '</span>';
      b.addEventListener('click', fn);
      buyBox.appendChild(b);
    };
    if (p.stok > 0) {
      mkBtn('Checkout Website', 'btn-pill-blue', () => {
        if (!store.token) { toast('Login dulu untuk checkout.', 'info'); setTimeout(() => { window.location.href = 'login.html'; }, 900); return; }
        renderCheckoutForm(p, coBox);
      });
    }
    loadMeta().then((meta) => {
      const url = p.shopee_url || meta.shopee_store_url;
      if (url) {
        const a = document.createElement('a');
        a.href = url; a.target = '_blank'; a.rel = 'noopener';
        a.className = 'btn-pill btn-pill-royal';
        a.style.cssText = 'padding:10px 20px;font-size:.85rem;background:linear-gradient(90deg,#EE4D2D,#FF7337);';
        a.innerHTML = '<span>Beli via Shopee</span>';
        buyBox.appendChild(a);
      }
    });
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
  }

  function renderCheckoutForm(p, box) {
    loadMeta().then((meta) => {
      const expData = meta.expeditions || {};
      const exps = Object.entries(expData).map(([k, v]) =>
        '<option value="' + esc(k) + '" data-ongkir="' + v.ongkir + '">' + esc(v.label) + ' — Rp ' + Number(v.ongkir).toLocaleString('id-ID') + '</option>').join('');
      const me = store.user || {};
      box.innerHTML =
        '<div class="form-group"><label class="form-label">Jumlah (stok ' + p.stok + ')</label><div class="input-wrapper"><input type="number" id="coJumlah" class="form-control" value="1" min="1" max="' + p.stok + '"></div></div>' +
        '<div class="form-group"><label class="form-label">No HP</label><div class="input-wrapper"><input type="text" id="coHp" class="form-control" placeholder="0812..." value="' + esc(me.phone || '') + '"></div></div>' +
        '<div class="form-group"><label class="form-label">Alamat Lengkap</label><div class="input-wrapper"><textarea id="coAlamat" class="form-control" rows="2">' + esc(me.alamat || '') + '</textarea></div></div>' +
        '<div class="form-group"><label class="form-label">Ekspedisi</label><div class="input-wrapper"><select id="coExp" class="form-control">' + exps + '</select></div></div>' +
        '<div class="form-group"><label class="form-label">Metode Pembayaran</label>' + payGridHtml('coPay') + paySubHtml() + '</div>' +
        '<div class="nb-summary" id="coSummary"></div>' +
        '<button type="button" class="btn-pill btn-pill-blue" id="coSubmit" style="padding:10px 22px;"><span>Bayar Sekarang</span></button>';
      box.style.display = 'block';
      bindPayGrid(box);
      bindPaySub(box, 'transfer');
      const updTotal = () => {
        const opt = document.getElementById('coExp').selectedOptions[0];
        const ongkir = opt && opt.dataset.ongkir ? parseInt(opt.dataset.ongkir, 10) : 0;
        const qty = Math.max(1, parseInt(document.getElementById('coJumlah').value || '1', 10));
        document.getElementById('coSummary').innerHTML =
          '<div class="row"><span>Subtotal Pesanan</span><span>' + rupiah(p.harga * qty) + '</span></div>' +
          '<div class="row"><span>Subtotal Pengiriman</span><span>' + rupiah(ongkir) + '</span></div>' +
          '<div class="row grand"><span>Total Pembayaran</span><span>' + rupiah(p.harga * qty + ongkir) + '</span></div>';
      };
      document.getElementById('coJumlah').addEventListener('input', updTotal);
      document.getElementById('coExp').addEventListener('change', updTotal);
      updTotal();
      document.getElementById('coSubmit').addEventListener('click', async () => {
        const payload = Object.assign({
          product_id: p.id,
          jumlah: Math.max(1, parseInt(document.getElementById('coJumlah').value || '1', 10)),
          alamat: document.getElementById('coAlamat').value.trim(),
          no_hp: document.getElementById('coHp').value.trim(),
          expedition: document.getElementById('coExp').value,
          payment_method: 'midtrans',
          pay_channel: document.getElementById('coPay').value,
        }, paySubValue());
        if (payload.alamat.length < 10) { toast('Alamat minimal 10 karakter.', 'error'); return; }
        if (!payload.no_hp) { toast('Isi nomor HP.', 'error'); return; }
        try {
          const o = await api('/orders', { method: 'POST', body: payload });
          if (o.direct_pay) {
            window.location.href = 'pembayaran.html?order_id=' + o.order.id;
          } else if (o.snap_token) {
            closeShopModal();
            payWithSnap(o.snap_token);
          } else {
            toast(o.midtrans_error || 'Gagal membuat pembayaran.', 'error');
          }
        } catch (err) { toast(err.message, 'error'); }
      });
    });
  }

  function initNativeShop() {
    const grid = document.getElementById('shopGrid');
    if (!grid) return;
    // ganti data statis dengan data live dari framework (markup kartu tetap sama)
    window.openShopDetail = openShopDetailApi;
    const count = document.getElementById('shopSearchCount');
    const empty = document.getElementById('shopEmpty');
    // ambil SEMUA halaman API agar 50 produk tampil seperti aslinya
    (async () => {
      let page = 1, pages = 1;
      API_PRODUCTS = [];
      try {
        do {
          const res = await api('/products?page=' + page);
          API_PRODUCTS = API_PRODUCTS.concat(res.data || []);
          pages = res.last_page || 1;
          page++;
        } while (page <= pages);
      } catch (e) { /* biarkan render statis script.js bila API mati */ }
      if (!API_PRODUCTS.length) return;
      // Pil filter gabungan Raka (tb_jenis_produk via /api/jenis, label_display);
      // pil statis dipertahankan sebagai fallback. Dukung ?jenis=<id|key> & ?q=
      // ala belanja.php Raka; pencarian mencakup deskripsi/ukuran/teknik.
      const CATKEY = { 'Kain Panjang': 'kain', Selendang: 'selendang', 'Busana Pria': 'pria', 'Busana Wanita': 'wanita', Aksesoris: 'aksesoris' };
      const catKey = (p) => CATKEY[(p.category && p.category.nama) || (p.jenis && p.jenis.label_display) || ''] || 'semua';
      const jenisIdOf = (p) => String(p.jenis_id || (p.jenis && p.jenis.id) || '');
      const pills = document.getElementById('shopFilterPills');
      const input = document.getElementById('shopSearchInput');
      const qs0 = new URLSearchParams(window.location.search);
      let f = qs0.get('jenis') || 'semua', q = (qs0.get('q') || '').toLowerCase();
      if (input && qs0.get('q')) input.value = qs0.get('q');
      try {
        const jenis = await api('/jenis');
        if (Array.isArray(jenis) && jenis.length && pills && !pills.dataset.live) {
          pills.dataset.live = '1';
          pills.innerHTML = '<button class="shop-filter-btn' + (f === 'semua' ? ' active' : '') + '" data-filter="semua">Semua Produk</button>' +
            jenis.map((j) => '<button class="shop-filter-btn' + (String(j.id) === String(f) ? ' active' : '') + '" data-filter="' + j.id + '">' + esc(j.label_display || j.nama) + '</button>').join('');
        }
      } catch (e) { /* pil statis tetap dipakai bila API mati */ }
      const render = () => {
        const list = API_PRODUCTS.filter((p) => {
          const okF = f === 'semua' || (/^\d+$/.test(String(f)) ? jenisIdOf(p) === String(f) : catKey(p) === f);
          const hay = ((p.nama || '') + ' ' + (p.motif || '') + ' ' + (p.bahan || '') + ' ' + (p.deskripsi || '') + ' ' + (p.ukuran || '') + ' ' + (p.teknik || '')).toLowerCase();
          return okF && (!q || hay.includes(q));
        });
        if (count) count.textContent = list.length + ' produk';
        if (!list.length) { grid.style.display = 'none'; if (empty) empty.style.display = 'block'; grid.innerHTML = ''; return; }
        grid.style.display = 'grid';
        if (empty) empty.style.display = 'none';
        grid.innerHTML = list.map(productCard).join('');
        paintHearts(grid);
        $$('.shop-product-card', grid).forEach((card) => {
          card.addEventListener('click', (e) => {
            const add = e.target.closest('[data-add]');
            if (add) { e.stopPropagation(); addToCart(add.getAttribute('data-add'), 1); return; }
            if (e.target.closest('.nb-heart')) return;
            window.location.href = 'produk.html?id=' + card.getAttribute('data-id');
          });
        });
      };
      if (input) input.addEventListener('input', () => { q = input.value.trim().toLowerCase(); render(); });
      if (pills) pills.addEventListener('click', (e) => {
        const btn = e.target.closest('.shop-filter-btn');
        if (!btn) return;
        $$('.shop-filter-btn', pills).forEach((b) => b.classList.remove('active'));
        btn.classList.add('active');
        f = btn.getAttribute('data-filter');
        render();
      });
      render();
    })();
  }
