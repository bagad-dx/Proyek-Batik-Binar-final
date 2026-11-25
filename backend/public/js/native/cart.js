/* BATIK BINAR native frontend: Cart. */
'use strict';

  /* ================= KERANJANG ================= */
  let wishSet = new Set();
  async function refreshWishlistState() {
    wishSet = new Set();
    if (!store.token) return;
    try {
      const items = await api('/wishlist');
      items.forEach((w) => wishSet.add(String(w.product_id)));
    } catch (e) {}
  }
  function paintHearts(root) {
    $$('.nb-heart', root || document).forEach((b) => {
      const on = wishSet.has(String(b.getAttribute('data-wish')));
      b.classList.toggle('liked', on);
      b.textContent = on ? '♥' : '♡';
      if (!b.dataset.bound) {
        b.dataset.bound = '1';
        b.addEventListener('click', async (e) => {
          e.stopPropagation();
          if (!store.token) { toast('Login dulu untuk wishlist.', 'info'); setTimeout(() => { window.location.href = 'login.html'; }, 900); return; }
          const pid = b.getAttribute('data-wish');
          try {
            if (wishSet.has(String(pid))) {
              await api('/wishlist/' + pid, { method: 'DELETE' });
              wishSet.delete(String(pid));
              toast('Dihapus dari wishlist.', 'info');
            } else {
              await api('/wishlist', { method: 'POST', body: { product_id: Number(pid) } });
              wishSet.add(String(pid));
              toast('Ditambah ke wishlist.', 'success');
            }
            paintHearts(document);
          } catch (err) { toast(err.message, 'error'); }
        });
      }
    });
  }

  async function addToCart(productId, jumlah) {
    if (!store.token) { toast('Login dulu untuk belanja.', 'info'); setTimeout(() => { window.location.href = 'login.html'; }, 900); return; }
    try {
      await api('/cart', { method: 'POST', body: { product_id: Number(productId), jumlah: jumlah || 1 } });
      toast('Masuk keranjang.', 'success');
      refreshCartBadge();
      openCartDrawer();
    } catch (err) { toast(err.message, 'error'); }
  }

  function ensureCartUI() {
    if (document.getElementById('nbCartDrawer')) return;
    const bd = document.createElement('div');
    bd.id = 'nbCartBackdrop';
    bd.className = 'nb-drawer-backdrop';
    bd.addEventListener('click', closeCartDrawer);
    const dr = document.createElement('div');
    dr.id = 'nbCartDrawer';
    dr.className = 'nb-drawer';
    dr.innerHTML = '<div class="nb-drawer-head"><h3>Keranjang Belanja</h3>' +
      '<button class="nb-drawer-close" type="button" aria-label="Tutup">&times;</button></div>' +
      '<div class="nb-drawer-body" id="nbCartBody"></div>' +
      '<div class="nb-drawer-foot" id="nbCartFoot"></div>';
    document.body.appendChild(bd);
    document.body.appendChild(dr);
    dr.querySelector('.nb-drawer-close').addEventListener('click', closeCartDrawer);
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeCartDrawer(); });
  }
  function openCartDrawer() { renderCartDrawer(); }
  function closeCartDrawer() {
    const dr = document.getElementById('nbCartDrawer');
    const bd = document.getElementById('nbCartBackdrop');
    if (dr) dr.classList.remove('open');
    if (bd) bd.classList.remove('open');
    document.body.style.overflow = '';
  }
  async function renderCartDrawer() {
    ensureCartUI();
    const dr = document.getElementById('nbCartDrawer');
    const bd = document.getElementById('nbCartBackdrop');
    const body = document.getElementById('nbCartBody');
    const foot = document.getElementById('nbCartFoot');
    dr.classList.add('open'); bd.classList.add('open');
    document.body.style.overflow = 'hidden';
    body.innerHTML = '<p class="service-desc">Memuat...</p>'; foot.innerHTML = '';
    try {
      const cart = await api('/cart');
      const items = cart.items || [];
      if (!items.length) {
        body.innerHTML = '<p class="service-desc">Keranjang kosong. <a href="belanja.php">Belanja dulu</a></p>';
        return;
      }
      window.__nbCartItems = items;
      body.innerHTML = '<label style="display:flex;align-items:center;gap:8px;font-size:.85rem;font-weight:700;margin-bottom:4px;"><input type="checkbox" id="nbSelectAll" checked> Pilih Semua</label>' +
        items.map((i) => {
        const p = i.product || {};
        return '<div class="nb-cart-item"><input type="checkbox" class="nb-pick" data-pick="' + i.id + '" checked>' +
          '<img src="' + esc(imgUrl(p.gambar_url || p.gambar, FALLBACK_IMG)) + '" alt="">' +
          '<div class="inf"><b>' + esc(p.nama || '-') + '</b><span>Rp ' + Number(p.harga || 0).toLocaleString('id-ID') + '</span></div>' +
          '<div class="nb-qty"><button type="button" data-dec="' + i.id + '">−</button><b>' + i.jumlah + '</b><button type="button" data-inc="' + i.id + '">+</button></div>' +
          '<button type="button" class="nb-cart-rm" data-rm="' + i.id + '" aria-label="Hapus">×</button></div>';
      }).join('');
      const updSelTotal = () => {
        const sel = $$('.nb-pick', body).filter((c) => c.checked).map((c) => Number(c.getAttribute('data-pick')));
        let total = 0, count = 0;
        (window.__nbCartItems || []).forEach((i) => {
          if (sel.includes(i.id)) { total += (i.product ? i.product.harga * i.jumlah : 0); count += i.jumlah; }
        });
        document.getElementById('nbSelTotal').textContent = rupiah(total);
        document.getElementById('nbSelCount').textContent = count;
        const all = document.getElementById('nbSelectAll');
        if (all) all.checked = sel.length === (window.__nbCartItems || []).length && sel.length > 0;
      };
      foot.innerHTML = '<div class="total"><span>Subtotal (<span id="nbSelCount">0</span> dipilih)</span><span id="nbSelTotal">Rp 0</span></div>' +
        '<div style="display:flex;gap:8px;"><button type="button" class="btn-pill btn-pill-blue" id="nbCheckoutBtn" style="padding:12px 20px;flex:1;justify-content:center;"><span>Checkout</span></button>' +
        '<button type="button" class="btn-pill btn-pill-royal" id="nbClearBtn" style="padding:12px 16px;"><span>Kosongkan</span></button></div>';
      document.getElementById('nbSelectAll').addEventListener('change', (e) => {
        $$('.nb-pick', body).forEach((c) => { c.checked = e.target.checked; });
        updSelTotal();
      });
      $$('.nb-pick', body).forEach((c) => c.addEventListener('change', updSelTotal));
      updSelTotal();
      $$('[data-inc]', body).forEach((b) => b.addEventListener('click', async () => {
        const row = b.closest('.nb-cart-item');
        const cur = parseInt(row.querySelector('.nb-qty b').textContent, 10) || 1;
        await api('/cart/' + b.getAttribute('data-inc'), { method: 'PUT', body: { jumlah: cur + 1 } });
        renderCartDrawer(); refreshCartBadge();
      }));
      $$('[data-dec]', body).forEach((b) => b.addEventListener('click', async () => {
        const row = b.closest('.nb-cart-item');
        const cur = parseInt(row.querySelector('.nb-qty b').textContent, 10) || 1;
        if (cur <= 1) return;
        await api('/cart/' + b.getAttribute('data-dec'), { method: 'PUT', body: { jumlah: cur - 1 } });
        renderCartDrawer(); refreshCartBadge();
      }));
      $$('[data-rm]', body).forEach((b) => b.addEventListener('click', async () => {
        await api('/cart/' + b.getAttribute('data-rm'), { method: 'DELETE' });
        renderCartDrawer(); refreshCartBadge();
      }));
      document.getElementById('nbClearBtn').addEventListener('click', async () => {
        if (!confirm('Kosongkan keranjang?')) return;
        await api('/cart', { method: 'DELETE' });
        renderCartDrawer(); refreshCartBadge();
      });
      document.getElementById('nbCheckoutBtn').addEventListener('click', () => {
        const sel = $$('.nb-pick', body).filter((c) => c.checked).map((c) => Number(c.getAttribute('data-pick')));
        if (!sel.length) { toast('Centang dulu barang yang mau dibeli.', 'info'); return; }
        window.location.href = 'checkout.html?ids=' + sel.join(',');
      });
    } catch (err) { body.innerHTML = '<p class="service-desc">' + esc(err.message) + '</p>'; }
  }
  async function renderCartCheckoutForm() {
    const body = document.getElementById('nbCartBody');
    const foot = document.getElementById('nbCartFoot');
    const meta = await loadMeta();
    const expData = meta.expeditions || {};
    const exps = Object.entries(expData).map(([k, v]) =>
      '<option value="' + esc(k) + '" data-ongkir="' + v.ongkir + '">' + esc(v.label) + ' — Rp ' + Number(v.ongkir).toLocaleString('id-ID') + '</option>').join('');
    const me = store.user || {};
    let cartSubtotal = 0;
    try {
      const sel = window.__nbCheckoutIds || [];
      cartSubtotal = (window.__nbCartItems || [])
        .filter((i) => sel.includes(i.id))
        .reduce((s, i) => s + (i.product ? i.product.harga * i.jumlah : 0), 0);
    } catch (e) {}
    body.innerHTML =
      '<button type="button" class="nb-mini-btn primary" id="nbBackCart">← Kembali</button>' +
      '<div class="form-group"><label class="form-label">No HP</label><div class="input-wrapper"><input type="text" id="nbCoHp" class="form-control" value="' + esc(me.phone || '') + '"></div></div>' +
      '<div class="form-group"><label class="form-label">Alamat Lengkap</label><div class="input-wrapper"><textarea id="nbCoAlamat" class="form-control" rows="2">' + esc(me.alamat || '') + '</textarea></div></div>' +
      '<div class="form-group"><label class="form-label">Ekspedisi</label><div class="input-wrapper"><select id="nbCoExp" class="form-control">' + exps + '</select></div></div>' +
      '<div class="form-group"><label class="form-label">Metode Pembayaran</label>' + payGridHtml('nbCoPay') + paySubHtml() + '</div>' +
      '<div class="nb-summary" id="nbCoSummary"></div>';
    foot.innerHTML = '<button type="button" class="btn-pill btn-pill-blue" id="nbCoSubmit" style="width:100%;justify-content:center;padding:12px;"><span>Bayar Sekarang</span></button>';
    document.getElementById('nbBackCart').addEventListener('click', renderCartDrawer);
    bindPayGrid(body);
    bindPaySub(body, 'transfer');
    const updTotal = () => {
      const opt = document.getElementById('nbCoExp').selectedOptions[0];
      const ongkir = opt && opt.dataset.ongkir ? parseInt(opt.dataset.ongkir, 10) : 0;
      document.getElementById('nbCoSummary').innerHTML =
        '<div class="row"><span>Subtotal Pesanan</span><span>' + rupiah(cartSubtotal) + '</span></div>' +
        '<div class="row"><span>Subtotal Pengiriman</span><span>' + rupiah(ongkir) + '</span></div>' +
        '<div class="row grand"><span>Total Pembayaran</span><span>' + rupiah(cartSubtotal + ongkir) + '</span></div>';
    };
    document.getElementById('nbCoExp').addEventListener('change', updTotal);
    updTotal();
    document.getElementById('nbCoSubmit').addEventListener('click', async () => {
      const btn = document.getElementById('nbCoSubmit');
      const payload = Object.assign({
        cart_ids: window.__nbCheckoutIds || [],
        alamat: document.getElementById('nbCoAlamat').value.trim(),
        no_hp: document.getElementById('nbCoHp').value.trim(),
        expedition: document.getElementById('nbCoExp').value,
        payment_method: 'midtrans',
        pay_channel: document.getElementById('nbCoPay').value,
      }, paySubValue());
      if (payload.alamat.length < 10) { toast('Alamat minimal 10 karakter.', 'error'); return; }
      if (!payload.no_hp) { toast('Isi nomor HP.', 'error'); return; }
      btn.disabled = true; btn.querySelector('span').textContent = 'Memproses...';
      try {
        const res = await api('/cart/checkout', { method: 'POST', body: payload });
        if (res.directs && res.directs.length === 1) {
          window.location.href = 'pembayaran.html?order_id=' + res.directs[0].order_id;
        } else if (res.directs && res.directs.length > 1) {
          closeCartDrawer(); refreshCartBadge();
          toast('Pesanan dibuat! Lihat kode bayar tiap pesanan di dashboard.', 'success');
          setTimeout(() => { window.location.href = 'dashboard.html'; }, 1200);
        } else if (res.snaps && res.snaps.length) { closeCartDrawer(); refreshCartBadge(); paySnapChain(res.snaps, res); }
        else { closeCartDrawer(); toast(res.midtrans_error || res.message, res.midtrans_error ? 'error' : 'success'); }
      } catch (err) { toast(err.message, 'error'); btn.disabled = false; btn.querySelector('span').textContent = 'Bayar Sekarang'; }
    });
  }
  async function paySnapChain(snaps, res) {    try {
      const meta = await loadMeta();
      if (!meta.midtrans_client_key) throw new Error('Client key Midtrans belum dikonfigurasi.');
      const host = meta.midtrans_is_production ? 'app.midtrans.com' : 'app.sandbox.midtrans.com';
      await loadScript('https://' + host + '/snap/snap.js');
      const next = (i) => {
        if (i >= snaps.length) { toast('Semua pembayaran selesai dibuat.', 'success'); setTimeout(() => { window.location.href = 'dashboard.html'; }, 1000); return; }
        window.snap.pay(snaps[i].snap_token, {
          onSuccess: () => next(i + 1),
          onPending: () => next(i + 1),
          onClose: () => { toast('Lanjutkan pembayaran dari dashboard.', 'info'); setTimeout(() => { window.location.href = 'dashboard.html'; }, 800); },
        });
      };
      next(0);
    } catch (err) { toast(err.message, 'error'); }
  }
  async function refreshCartBadge() {
    const badge = document.getElementById('nbCartCount');
    if (!store.token) { if (badge) badge.style.display = 'none'; return; }
    try {
      const cart = await api('/cart');
      if (badge) {
        badge.textContent = cart.count || 0;
        badge.style.display = cart.count > 0 ? 'flex' : 'none';
      }
    } catch (e) {}
  }
  function initCartDrawer() {
    const actions = document.querySelector('.site-header .nav-actions');
    if (!actions || document.getElementById('nbCartBtn')) return;
    const btn = document.createElement('button');
    btn.type = 'button'; btn.id = 'nbCartBtn';
    btn.className = 'nav-icon-btn nb-cart-fab';
    btn.setAttribute('aria-label', 'Keranjang');
    btn.innerHTML = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path><line x1="3" y1="6" x2="21" y2="6"></line><path d="M16 10a4 4 0 0 1-8 0"></path></svg><span class="nb-cart-count" id="nbCartCount" style="display:none;">0</span>';
    btn.addEventListener('click', () => {
      if (!store.token) { toast('Login dulu untuk membuka keranjang.', 'info'); setTimeout(() => { window.location.href = 'login.html'; }, 900); return; }
      renderCartDrawer();
    });
    actions.insertBefore(btn, actions.firstChild);
    refreshWishlistState().then(() => {
      paintHearts(document);
      const grid = document.getElementById('shopGrid');
      if (grid && API_PRODUCTS.length) paintHearts(grid);
    });
    refreshCartBadge();
  }
