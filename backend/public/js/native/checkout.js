/* BATIK BINAR native frontend: Checkout. */
'use strict';

  /* ================= CHECKOUT ALA SHOPEE (checkout.html) ================= */
  async function initNativeCheckout() {
    const page = document.getElementById('coPage');
    if (!page) return;
    if (!store.token) { window.location.href = 'login.html'; return; }
    const qs = new URLSearchParams(window.location.search);
    const singlePid = qs.get('product_id');
    const singleQty = Math.max(1, parseInt(qs.get('qty') || '1', 10));
    const idsParam = (qs.get('ids') || '').split(',').map((x) => Number(x)).filter((x) => x > 0);
    try {
      const [me, meta] = await Promise.all([api('/me'), loadMeta()]);
      let items = [];
      if (singlePid) {
        const p = await api('/products/' + singlePid);
        items = [{ id: 'single-' + p.id, product_id: p.id, jumlah: Math.min(singleQty, p.stok), product: p }];
      } else {
        const cart = await api('/cart');
        items = (cart.items || []).filter((i) => !idsParam.length || idsParam.includes(i.id));
        if (!items.length) { page.innerHTML = '<p class="service-desc">Tidak ada barang untuk checkout. <a href="belanja.php">Belanja dulu</a></p>'; return; }
      }
      const expOpts = Object.entries(meta.expeditions || {}).map(([k, v]) =>
        '<option value="' + esc(k) + '" data-ongkir="' + v.ongkir + '">' + esc(v.label) + ' — Rp ' + Number(v.ongkir).toLocaleString('id-ID') + '</option>').join('');
      const rows = items.map((i) => {
        const p = i.product || {};
        const sub = (p.harga || 0) * i.jumlah;
        return '<div style="display:grid;grid-template-columns:64px 1fr auto auto;gap:12px;align-items:center;padding:12px 0;border-bottom:1px solid var(--color-border-light);">' +
          '<img src="' + esc(imgUrl(p.gambar_url || p.gambar, FALLBACK_IMG)) + '" alt="" style="width:64px;height:64px;border-radius:12px;object-fit:cover;">' +
          '<div><b style="font-size:.9rem;">' + esc(p.nama || '-') + '</b><div class="service-meta-text">' + esc((p.category && p.category.nama) || '') + '</div></div>' +
          '<div style="text-align:right;"><div>Rp ' + Number(p.harga || 0).toLocaleString('id-ID') + '</div><div class="service-meta-text">x' + i.jumlah + '</div></div>' +
          '<div style="font-weight:800;color:var(--color-primary);">Rp ' + Number(sub).toLocaleString('id-ID') + '</div></div>';
      }).join('');
      const barangTotal = items.reduce((s, i) => s + ((i.product ? i.product.harga : 0) * i.jumlah), 0);
      page.innerHTML =
        '<div class="backend-card" style="margin-bottom:16px;"><h3 class="service-title">📍 Alamat Pengiriman</h3>' +
        '<p><b>' + esc(me.name || '') + ' (' + esc(me.phone || '-') + ')</b></p>' +
        '<p class="service-desc" id="coAddrText">' + esc(me.alamat || 'Belum ada alamat') + '</p>' +
        '<button type="button" class="nb-mini-btn primary" id="coAddrEdit">Ubah</button>' +
        '<div id="coAddrForm" style="display:none;margin-top:10px;"><div class="input-wrapper"><textarea id="coAddrNew" class="form-control" rows="2">' + esc(me.alamat || '') + '</textarea></div>' +
        '<div class="input-wrapper" style="margin-top:8px;"><input type="text" id="coHpNew" class="form-control" value="' + esc(me.phone || '') + '" placeholder="No HP"></div>' +
        '<button type="button" class="nb-mini-btn primary" id="coAddrSave" style="margin-top:8px;">Simpan Alamat</button></div></div>' +
        '<div class="backend-card" style="margin-bottom:16px;"><h3 class="service-title">Produk Dipesan</h3>' + rows + '</div>' +
        '<div class="backend-card" style="margin-bottom:16px;"><h3 class="service-title">Ekspedisi</h3>' +
        '<div class="input-wrapper"><select id="coExpPage" class="form-control">' + expOpts + '</select></div></div>' +
        '<div class="backend-card" style="margin-bottom:16px;"><h3 class="service-title">Metode Pembayaran</h3>' + payGridHtml('coPayPage') + paySubHtml() + '</div>' +
        '<div class="backend-card"><div class="nb-summary" id="coPageSummary"></div>' +
        '<button type="button" class="btn-pill btn-pill-blue" id="coPageSubmit" style="width:100%;justify-content:center;padding:14px;"><span>Buat Pesanan</span></button></div>';
      bindPayGrid(page);
      bindPaySub(page, 'transfer');
      const upd = () => {
        const opt = document.getElementById('coExpPage').selectedOptions[0];
        const ongkir = opt && opt.dataset.ongkir ? parseInt(opt.dataset.ongkir, 10) : 0;
        document.getElementById('coPageSummary').innerHTML =
          '<div class="row"><span>Subtotal Pesanan</span><span>' + rupiah(barangTotal) + '</span></div>' +
          '<div class="row"><span>' + (items.length > 1 ? 'Subtotal Pengiriman (1x, pesanan pertama dari ' + items.length + ' pesanan)' : 'Subtotal Pengiriman') + '</span><span>' + rupiah(ongkir) + '</span></div>' +
          '<div class="row grand"><span>Total Pembayaran</span><span>' + rupiah(barangTotal + ongkir) + '</span></div>';
      };
      document.getElementById('coExpPage').addEventListener('change', upd);
      upd();
      document.getElementById('coAddrEdit').addEventListener('click', () => {
        const f = document.getElementById('coAddrForm');
        f.style.display = f.style.display === 'none' ? 'block' : 'none';
      });
      document.getElementById('coAddrSave').addEventListener('click', async () => {
        try {
          const u = await api('/profile', { method: 'PUT', body: {
            name: me.name, email: me.email,
            phone: document.getElementById('coHpNew').value.trim(),
            alamat: document.getElementById('coAddrNew').value.trim(),
          }});
          try { localStorage.setItem('binar_user', JSON.stringify(u.user)); } catch (e) {}
          toast('Alamat disimpan.', 'success');
          setTimeout(() => window.location.reload(), 800);
        } catch (err) { toast(err.message, 'error'); }
      });
      document.getElementById('coPageSubmit').addEventListener('click', async (e) => {
        const btn = e.currentTarget;
        const payload = Object.assign({
          alamat: me.alamat || document.getElementById('coAddrNew').value.trim(),
          no_hp: me.phone || document.getElementById('coHpNew').value.trim(),
          expedition: document.getElementById('coExpPage').value,
          payment_method: 'midtrans',
          pay_channel: document.getElementById('coPayPage').value,
        }, paySubValue());
        if (!payload.no_hp || payload.alamat.length < 10) { toast('Lengkapi No HP & Alamat (Ubah) dulu.', 'error'); return; }
        btn.disabled = true;
        try {
          let res;
          if (singlePid) {
            const first = items[0];
            res = await api('/orders', { method: 'POST', body: {
              product_id: first.product_id, jumlah: first.jumlah,
              alamat: payload.alamat, no_hp: payload.no_hp, expedition: payload.expedition,
              payment_method: 'midtrans', pay_channel: payload.pay_channel,
              bank: payload.bank, ewallet: payload.ewallet, store: payload.store,
            }});
            if (res.direct_pay) { window.location.href = 'pembayaran.html?order_id=' + res.order.id; return; }
            if (res.snap_token) { closeShopModalSafe(); payWithSnap(res.snap_token); return; }
            toast(res.midtrans_error || res.message, res.midtrans_error ? 'error' : 'success');
          } else {
            res = await api('/cart/checkout', { method: 'POST', body: Object.assign({ cart_ids: items.map((i) => i.id) }, payload) });
            if (res.directs && res.directs.length === 1) { window.location.href = 'pembayaran.html?order_id=' + res.directs[0].order_id; return; }
            if (res.directs && res.directs.length > 1) { toast('Pesanan dibuat! Lihat kode bayar di dashboard.', 'success'); setTimeout(() => { window.location.href = 'dashboard.html'; }, 1200); return; }
            if (res.snaps && res.snaps.length) { paySnapChain(res.snaps, res); return; }
            toast(res.midtrans_error || res.message, res.midtrans_error ? 'error' : 'success');
          }
        } catch (err) { toast(err.message, 'error'); btn.disabled = false; }
      });
    } catch (err) {
      if (String(err.message).includes('401') || String(err.message).includes('Unauthenticated')) { window.location.href = 'login.html'; return; }
      page.innerHTML = '<p class="service-desc">' + esc(err.message) + '</p>';
    }
  }
  function closeShopModalSafe() { try { closeShopModal(); } catch (e) {} }
