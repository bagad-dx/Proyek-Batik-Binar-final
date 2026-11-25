/* BATIK BINAR native frontend: Orders. */
'use strict';

  /* ================= SEMUA PESANAN (pesanan.html) ================= */
  function orderItemHtml(o) {
    const img = imgUrl((o.product && (o.product.gambar_url || o.product.gambar)) || null, FALLBACK_IMG);
    const nm = (o.product && o.product.nama) || 'Produk';
    const belum = o.status === 'pending' && o.payment_method === 'midtrans' && !o.paid_at;
    const label = belum ? 'Belum Bayar' : (STATUS_LABEL[o.status] || o.status);
    const deadline = belum && o.batas_bayar
      ? '<p style="font-size:.75rem;color:#92400E;">Bayar sebelum ' + new Date(o.batas_bayar).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }) + '</p>' : '';
    const cancel = o.status === 'pending'
      ? '<button type="button" data-cancel="' + o.id + '" class="btn-pill btn-pill-royal" style="padding:6px 14px;font-size:.75rem;">Batalkan</button>' : '';
    const repay = (belum && (o.snap_token || o.payment_data))
      ? '<button type="button" data-repay="' + o.id + '" class="btn-pill btn-pill-blue" style="padding:6px 14px;font-size:.75rem;">Bayar</button>' : '';
    const chatBtn = '<button type="button" data-sellerchat="' + o.id + '" class="btn-pill btn-pill-blue" style="padding:6px 14px;font-size:.75rem;">Chat Penjual</button>';
    return '<div class="dash-order-item" data-order-id="' + o.id + '" data-status="' + o.status + '" data-belum="' + (belum ? 1 : 0) + '"><div class="dash-order-img"><img src="' + esc(img) + '" alt=""></div>' +
      '<div class="dash-order-info"><h4>' + esc(nm) + ' x' + o.jumlah + '</h4><p>Rp ' + Number(o.total_harga).toLocaleString('id-ID') + '</p><p style="font-size:.75rem;color:var(--color-text-muted);">Via: <b>' + esc(o.metode_label || '-') + '</b></p>' + deadline + '</div>' +
      '<span class="dash-order-status ' + (STATUS_CLASS[o.status] || 'status-process') + '"' +
      (belum ? ' style="background:#FEF3C7;color:#92400E;"' : '') +
      (o.status === 'batal' && !belum ? ' style="background:#FEE2E2;color:#B91C1C;"' : '') + '>' + label + '</span>' + repay + chatBtn + cancel + '</div>';
  }
  function bindOrderButtons(root) {
    $$('[data-cancel]', root).forEach((b) => b.addEventListener('click', async () => {
      if (!confirm('Batalkan pesanan ini?')) return;
      try { await api('/orders/' + b.getAttribute('data-cancel') + '/cancel', { method: 'PATCH' }); toast('Pesanan dibatalkan.', 'success'); setTimeout(() => window.location.reload(), 900); }
      catch (err) { toast(err.message, 'error'); }
    }));
    $$('[data-repay]', root).forEach((b) => b.addEventListener('click', () => {
      window.location.href = 'pembayaran.html?order_id=' + b.getAttribute('data-repay');
    }));
    $$('[data-sellerchat]', root).forEach((b) => b.addEventListener('click', () => openSellerChat(Number(b.getAttribute('data-sellerchat')))));
  }
  async function initNativeOrders() {
    const list = document.getElementById('allOrdersList');
    if (!list) return;
    if (!store.token) { window.location.href = 'login.html'; return; }
    try {
      const orders = await api('/my-orders');
      let f = 'semua';
      const render = () => {
        const shown = orders.filter((o) => {
          if (f === 'semua') return true;
          if (f === 'belum') return o.status === 'pending' && o.payment_method === 'midtrans' && !o.paid_at;
          return o.status === f;
        });
        list.innerHTML = shown.length
          ? shown.map(orderItemHtml).join('')
          : '<p class="service-desc">Tidak ada pesanan pada filter ini.</p>';
        bindOrderButtons(list);
      };
      const pills = document.getElementById('orderFilterPills');
      if (pills) pills.addEventListener('click', (e) => {
        const btn = e.target.closest('.shop-filter-btn');
        if (!btn) return;
        $$('.shop-filter-btn', pills).forEach((b) => b.classList.remove('active'));
        btn.classList.add('active');
        f = btn.getAttribute('data-filter');
        render();
      });
      render();
      // Deep-link dari notifikasi: ?order_id= sorot pesanan, ?chat= buka chat penjual.
      const qp = new URLSearchParams(window.location.search);
      const hlOid = qp.get('order_id');
      if (hlOid) {
        const hlEl = list.querySelector('[data-order-id="' + hlOid + '"]');
        if (hlEl) { hlEl.style.outline = '2px solid var(--color-accent,#0B5FA5)'; hlEl.style.outlineOffset = '4px'; hlEl.scrollIntoView({ behavior: 'smooth', block: 'center' }); }
      }
      const chatOid = qp.get('chat');
      if (chatOid) { try { await openSellerChat(Number(chatOid)); } catch (e) {} }
    } catch (e) {
      if (String(e.message).includes('401') || String(e.message).includes('Unauthenticated')) {
        store.clear();
        window.location.href = 'login.html';
        return;
      }
      list.innerHTML = '<p class="service-desc">Gagal memuat pesanan: ' + esc(e.message) + '</p>';
    }
  }
