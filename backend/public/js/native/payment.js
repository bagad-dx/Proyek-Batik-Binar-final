/* BATIK BINAR native frontend: Payment. */
'use strict';

  /* Modal generik untuk menampilkan kode/QR bayar susulan. */
  function openPayModal(title, d) {
    let m = document.getElementById('nbPayModal');
    if (!m) {
      m = document.createElement('div');
      m.id = 'nbPayModal'; m.className = 'nb-modal';
      m.innerHTML = '<div class="nb-modal-card"><div style="display:flex;align-items:center;gap:10px;margin-bottom:10px;"><h3 style="margin:0;flex:1;color:var(--color-primary);font-size:1rem;" id="nbPayTitle"></h3><button class="nb-drawer-close" type="button">&times;</button></div><div id="nbPayBody"></div></div>';
      document.body.appendChild(m);
      m.querySelector('.nb-drawer-close').addEventListener('click', () => m.classList.remove('open'));
      m.addEventListener('click', (e) => { if (e.target === m) m.classList.remove('open'); });
    }
    document.getElementById('nbPayTitle').textContent = title;
    renderDirectPay(document.getElementById('nbPayBody'), d);
    m.classList.add('open');
  }

  /* ---------- halaman bayar ala marketplace ---------- */
  function payStepsFor(kind, total) {
    const nominal = 'Rp ' + Number(total || 0).toLocaleString('id-ID');
    const steps = {
      qr: ['Buka aplikasi e-wallet / m-banking apa pun', 'Pilih menu Scan QR / QRIS', 'Pindai kode QR di atas', 'Periksa nominal ' + nominal + ' lalu konfirmasi bayar'],
      va: ['Buka m-banking / ATM / internet banking', 'Pilih menu Transfer ke Virtual Account', 'Masukkan nomor VA di atas', 'Periksa nama merchant & nominal ' + nominal + ' lalu konfirmasi'],
      code: ['Kunjungi gerai tunai terdekat', 'Tunjukkan kode bayar di atas ke kasir', 'Bayar tunai persis ' + nominal, 'Simpan struk sebagai bukti pembayaran'],
    };
    return steps[kind] || ['Klik Bayar Sekarang untuk membuka halaman bayar aman', 'Pilih / lengkapi detail pembayaran', 'Selesaikan verifikasi bila diminta (mis. 3DS / OTP)'];
  }
  function payTimelineHtml() {
    const dot = (done, active) => '<span class="pay-dot' + (done ? ' done' : active ? ' active' : '') + '">' + (done ? '✓' : '') + '</span>';
    return '<div class="pay-timeline">' +
      '<div class="pay-step">' + dot(true) + '<div><b>Pesanan Dibuat</b><span>Menunggu pembayaran</span></div></div>' +
      '<div class="pay-line"></div>' +
      '<div class="pay-step">' + dot(false, true) + '<div><b>Bayar Sekarang</b><span>Sebelum batas waktu</span></div></div>' +
      '<div class="pay-line"></div>' +
      '<div class="pay-step">' + dot(false) + '<div><b>Pesanan Diproses</b><span>Otomatis setelah lunas</span></div></div></div>';
  }
  function payCountdownStart(batas, onExpire) {
    const el = document.getElementById('payCountdown');
    if (!el || !batas) return;
    const end = new Date(batas).getTime();
    const pad = (n) => String(n).padStart(2, '0');
    const tick = () => {
      const sisa = end - Date.now();
      if (sisa <= 0) {
        el.textContent = '00:00:00';
        clearInterval(window.__payTick);
        if (typeof onExpire === 'function') { onExpire(); return; }
        const w = document.getElementById('payWarn');
        if (w) w.innerHTML = '<b>Waktu habis.</b> Pesanan kedaluwarsa - silakan buat pesanan baru. <a href="belanja.php">Belanja lagi</a>';
        const btns = document.querySelectorAll('#payNowBtn,#payRefresh');
        btns.forEach((b) => { b.disabled = true; });
        return;
      }
      const h = Math.floor(sisa / 3600000), m = Math.floor(sisa % 3600000 / 60000), s = Math.floor(sisa % 60000 / 1000);
      el.textContent = pad(h) + ':' + pad(m) + ':' + pad(s);
    };
    clearInterval(window.__payTick);
    tick();
    window.__payTick = setInterval(tick, 1000);
  }
  async function initNativePayment() {
    const box = document.getElementById('payBox');
    if (!box) return;
    if (!store.token) { window.location.href = 'login.html'; return; }
    const oid = new URLSearchParams(window.location.search).get('order_id');
    if (!oid) { box.innerHTML = '<p class="service-desc">Pesanan tidak ditemukan.</p>'; return; }
    let pollId = null;
    const stopPolling = () => { if (pollId) { clearInterval(pollId); pollId = null; } };
    // Cek status ke Midtrans (sinkronisasi server). Satu-satunya jalan
    // mendeteksi lunas; tidak pernah menandai lunas dari pengakuan pembeli.
    const checkPaidStatus = async () => api('/orders/' + oid + '/payment-status');
    const showPaidInvoice = (chk) => {
      stopPolling();
      clearInterval(window.__payTick);
      const o = (chk && chk.order) || {};
      const paidAt = o.paid_at ? new Date(o.paid_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : '-';
      box.innerHTML = '<div style="text-align:center;padding:12px 0 4px;">' +
        '<div style="width:72px;height:72px;border-radius:50%;background:#DCFCE7;color:#15803D;display:inline-flex;align-items:center;justify-content:center;font-size:2rem;font-weight:800;">&#10003;</div>' +
        '<h3 class="service-title" style="margin:14px 0 4px;">Pembayaran Berhasil</h3>' +
        '<p class="service-desc">Pesanan #' + esc(String(oid)) + ' sudah lunas dan sedang diproses admin.</p></div>' +
        '<div class="nb-summary" style="max-width:460px;margin:12px auto 0;">' +
        '<div class="row"><span>Pesanan</span><span>#' + esc(String(oid)) + ' &bull; ' + esc(o.nama || 'Produk Batik') + ' x' + (o.jumlah || 1) + '</span></div>' +
        '<div class="row"><span>Total Bayar</span><span>' + rupiah(o.grand_total || 0) + '</span></div>' +
        '<div class="row"><span>Status</span><span>Lunas - Diproses</span></div>' +
        '<div class="row"><span>Dibayar pada</span><span>' + esc(paidAt) + '</span></div></div>' +
        '<div class="backend-actions" style="justify-content:center;margin-top:18px;"><a href="pesanan.html" class="btn-pill btn-pill-blue" style="padding:10px 22px;"><span>Lihat Pesanan</span></a> ' +
        '<a href="dashboard.html" class="btn-pill btn-pill-royal" style="padding:10px 22px;"><span>Kembali ke Dashboard</span></a></div>' +
        '<p class="service-desc" style="text-align:center;margin-top:12px;">Anda akan diarahkan ke dashboard...</p>';
      setTimeout(() => { window.location.href = 'dashboard.html'; }, 6000);
    };
    const onPayExpired = async () => {
      stopPolling();
      try {
        const chk = await checkPaidStatus();
        if (chk && (chk.paid || chk.status === 'diproses')) { showPaidInvoice(chk); return; }
      } catch (e) {}
      const w = document.getElementById('payWarn');
      if (w) w.innerHTML = '<b>Waktu habis.</b> Pesanan kedaluwarsa - silakan buat pesanan baru. <a href="belanja.php">Belanja lagi</a>';
      document.querySelectorAll('#payNowBtn,#payRefresh').forEach((b) => { b.disabled = true; });
    };
    const showPay = async (silent) => {
      try {
        const d = await api('/orders/' + oid + '/payment');
        if (d.paid || d.status === 'diproses') {
          showPaidInvoice({ paid: true, status: 'diproses', order: { id: oid, nama: (d.order && d.order.nama) || 'Produk Batik', jumlah: (d.order && d.order.jumlah) || 1, grand_total: (d.order && d.order.grand_total) || d.gross || 0, paid_at: null } });
          return true;
        }
        renderDirectPay(box.querySelector('#payDirect'), d);
        if ((!d.kind || d.kind === 'info') && d.snap_token) {
          try {
            const host = d.is_production ? 'app.midtrans.com' : 'app.sandbox.midtrans.com';
            await loadScript('https://' + host + '/snap/snap.js');
            window.snap.pay(d.snap_token, {
              onSuccess: async () => { try { const chk = await checkPaidStatus(); if (chk && chk.paid) { showPaidInvoice(chk); return; } } catch (e) {} window.location.href = 'dashboard.html'; },
              onPending: async () => { try { const chk = await checkPaidStatus(); if (chk && chk.paid) { showPaidInvoice(chk); return; } } catch (e) {} window.location.href = 'dashboard.html'; },
              onClose: () => {},
            });
          } catch (e) {}
        }
        return true;
      } catch (err) {
        if (!silent) box.querySelector('#payDirect').innerHTML = '<p class="service-desc">' + esc(err.message) + '</p>';
        return false;
      }
    };
    try {
      const chk0 = await checkPaidStatus().catch(() => null);
      if (chk0 && (chk0.paid || chk0.status === 'diproses')) { showPaidInvoice(chk0); return; }
      const d = await api('/orders/' + oid + '/payment');
      if (d.status === 'batal') {
        box.innerHTML = '<p class="service-desc" style="text-align:center;">Pesanan ini sudah dibatalkan/kedaluwarsa.</p><div class="backend-actions" style="justify-content:center;"><a href="pesanan.html" class="btn-pill btn-pill-blue" style="padding:10px 22px;"><span>Lihat Pesanan</span></a></div>';
        return;
      }
      if (d.paid || d.status === 'diproses') {
        showPaidInvoice({ paid: true, status: 'diproses', order: { id: oid, nama: (d.order && d.order.nama) || 'Produk Batik', jumlah: (d.order && d.order.jumlah) || 1, grand_total: (d.order && d.order.grand_total) || d.gross || 0, paid_at: null } });
        return;
      }
      const o = d.order || {};
      const grand = o.grand_total || d.gross || 0;
      const metode = o.metode_label || d.title || 'Midtrans';
      const img = imgUrl(o.gambar || null, FALLBACK_IMG);
      box.innerHTML = payTimelineHtml() +
        '<div class="pay-grid">' +
        '<div class="backend-card"><h3 class="service-title">Ringkasan Pesanan</h3>' +
        '<div style="display:flex;gap:12px;align-items:center;margin:10px 0 14px;"><img src="' + esc(img) + '" alt="" style="width:72px;height:72px;border-radius:14px;object-fit:cover;flex:none;">' +
        '<div><b style="font-size:.95rem;">' + esc(o.nama || 'Pesanan') + '</b><div class="service-meta-text">Pesanan #' + esc(String(oid)) + ' • x' + (o.jumlah || 1) + '</div>' +
        '<span class="pay-method-badge">' + esc(metode) + '</span></div></div>' +
        '<div class="nb-summary">' +
        '<div class="row"><span>Subtotal Produk</span><span>' + rupiah(o.total_harga || 0) + '</span></div>' +
        '<div class="row"><span>Ongkos Kirim</span><span>' + rupiah(o.shipping_cost || 0) + '</span></div>' +
        '<div class="row grand"><span>Total Bayar</span><span>' + rupiah(grand) + '</span></div></div>' +
        '<div class="backend-actions" style="margin:12px 0 0;"><a href="pesanan.html" class="btn-pill" style="padding:10px 20px;font-size:.82rem;"><span>Semua Pesanan</span></a> ' +
        '<button type="button" class="btn-pill btn-pill-royal" id="payCancelBtn" style="padding:10px 20px;font-size:.82rem;"><span>Batalkan</span></button></div></div>' +
        '<div class="backend-card" style="text-align:center;"><h3 class="service-title">Selesaikan dalam</h3>' +
        '<div class="pay-countdown" id="payCountdown">--:--:--</div>' +
        '<p class="service-meta-text" id="payWarn">Bayar sebelum ' + (o.batas_bayar ? new Date(o.batas_bayar).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : '-') + '</p>' +
        '<div id="payDirect"></div>' +
        '<div class="backend-actions" style="justify-content:center;margin-top:14px;"><button type="button" class="btn-pill btn-pill-blue" id="payNowBtn" style="padding:12px 28px;"><span>Bayar Sekarang</span></button> ' +
        '<button type="button" class="btn-pill btn-pill-royal" id="payRefresh" style="padding:10px 22px;"><span>Cek Status Pembayaran</span></button></div></div></div>' +
        '<div class="backend-card" style="margin-top:16px;"><h3 class="service-title">Cara Membayar</h3>' +
        '<ol class="pay-steps-list">' + payStepsFor(d.kind, grand).map((s) => '<li>' + esc(s) + '</li>').join('') + '</ol></div>';
      payCountdownStart(o.batas_bayar, onPayExpired);
      document.getElementById('payCancelBtn').addEventListener('click', async () => {
        if (!confirm('Batalkan pesanan ini? Stok dikembalikan.')) return;
        try {
          await api('/orders/' + oid + '/cancel', { method: 'PATCH' });
          toast('Pesanan dibatalkan.', 'success');
          setTimeout(() => { window.location.href = 'pesanan.html'; }, 900);
        } catch (err) { toast(err.message, 'error'); }
      });
      document.getElementById('payNowBtn').addEventListener('click', async (e) => {
        const btn = e.currentTarget;
        btn.disabled = true;
        btn.querySelector('span').textContent = 'Memuat...';
        await showPay();
        btn.disabled = false;
        btn.querySelector('span').textContent = 'Bayar Sekarang';
      });
      // Polling status lunas ke Midtrans tiap 2,5 detik (ala MiomiPay POS).
      pollId = setInterval(async () => {
        try {
          const chk = await checkPaidStatus();
          if (chk && (chk.paid || chk.status === 'diproses')) {
            toast('Pembayaran lunas! Pesanan diproses.', 'success');
            showPaidInvoice(chk);
          }
        } catch (e) {}
      }, 2500);
      // Cek instan saat halaman dibuka kembali / kembali dari browser.
      const onVisible = async () => {
        if (document.hidden) return;
        try {
          const chk = await checkPaidStatus();
          if (chk && (chk.paid || chk.status === 'diproses')) { toast('Pembayaran lunas! Pesanan diproses.', 'success'); showPaidInvoice(chk); }
        } catch (e) {}
      };
      document.addEventListener('visibilitychange', onVisible);
      window.addEventListener('focus', onVisible);
      document.getElementById('payRefresh').addEventListener('click', async (e) => {
        const btn = e.currentTarget;
        btn.disabled = true;
        const span = btn.querySelector('span');
        const oldLabel = span ? span.textContent : '';
        if (span) span.textContent = 'Mengecek...';
        try {
          const chk = await checkPaidStatus();
          if (chk.paid || chk.status === 'diproses') {
            toast('Pembayaran lunas! Pesanan diproses.', 'success');
            showPaidInvoice(chk);
          } else if (chk.status === 'batal') {
            toast('Pesanan ini sudah batal/kedaluwarsa.', 'error');
            setTimeout(() => { window.location.href = 'pesanan.html'; }, 1200);
          } else {
            toast('Belum terdeteksi. Selesaikan pembayaran dulu ya.', 'info');
          }
        } catch (err) { toast(err.message || 'Gagal mengecek status.', 'error'); }
        btn.disabled = false;
        if (span) span.textContent = oldLabel;
      });
      // tampilkan QR/kode bayar segera saat halaman dibuka
      await showPay(true);
    } catch (err) {
      box.innerHTML = '<p class="service-desc">' + esc(err.message) + '</p>';
    }
  }
