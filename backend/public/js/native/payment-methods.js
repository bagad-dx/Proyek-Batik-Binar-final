/* BATIK BINAR native frontend: Payment Methods. */
'use strict';

  /* ---------- pilihan metode ala Shopee ---------- */
  const NB_PAY = [
    { key: 'transfer', label: 'Transfer Bank', gw: false },
    { key: 'cod', label: 'Tunai di Gerai', gw: false },
    { key: 'ewallet', label: 'E-Wallet', gw: false },
    { key: 'qris', label: 'QRIS', gw: true },
    { key: 'kartu', label: 'Kartu Kredit/Debit', gw: true },
  ];
  function payGridHtml(selId, selected) {
    return '<div class="nb-paygrid" data-paygrid="' + selId + '">' + NB_PAY.map((m) =>
      '<button type="button" class="nb-paybtn' + ((selected || 'qris') === m.key ? ' active' : '') + '" data-pay="' + m.key + '">' + m.label + '</button>').join('') + '</div>' +
      '<input type="hidden" id="' + selId + '" value="' + (selected || 'qris') + '">';
  }
  function bindPayGrid(scope) {
    $$('[data-paygrid]', scope || document).forEach((g) => {
      const hid = document.getElementById(g.getAttribute('data-paygrid'));
      $$('[data-pay]', g).forEach((b) => b.addEventListener('click', () => {
        $$('[data-pay]', g).forEach((x) => x.classList.remove('active'));
        b.classList.add('active');
        if (hid) hid.value = b.getAttribute('data-pay');
      }));
    });
  }
  function payMethodFor(key) {
    const m = NB_PAY.find((x) => x.key === key);
    return (m && m.gw) ? 'midtrans' : (key || 'transfer');
  }
  /* Sub-pilihan per metode: bank / e-wallet / gerai. */
  function paySubHtml() {
    return '<div class="form-group" id="paySubWrap" style="display:none;"><label class="form-label" id="paySubLabel">Pilih</label><div class="input-wrapper"><select id="paySub" class="form-control"></select></div></div>';
  }
  const PAY_SUBS = {
    transfer: { label: 'Pilih Bank', name: 'bank', opts: [['bca', 'BCA'], ['bni', 'BNI'], ['bri', 'BRI'], ['permata', 'Permata']] },
    ewallet: { label: 'Pilih E-Wallet', name: 'ewallet', opts: [['gopay', 'GoPay'], ['shopeepay', 'ShopeePay']] },
    cod: { label: 'Pilih Gerai Tunai (bayar maks. 24 jam)', name: 'store', opts: [['alfamart', 'Alfamart'], ['indomaret', 'Indomaret']] },
  };
  function bindPaySub(scope, defKey) {
    const wrap = document.getElementById('paySubWrap');
    const sel = document.getElementById('paySub');
    const lab = document.getElementById('paySubLabel');
    const upd = (key) => {
      const cfg = PAY_SUBS[key || defKey || 'transfer'];
      if (!cfg) { wrap.style.display = 'none'; return; }
      lab.textContent = cfg.label;
      sel.innerHTML = cfg.opts.map((o) => '<option value="' + o[0] + '">' + o[1] + '</option>').join('');
      sel.setAttribute('name', cfg.name);
      wrap.style.display = 'block'; // sub-pilihan muncul SETELAH metode diklik
    };
    (scope || document).querySelectorAll('[data-paygrid] [data-pay]').forEach((b) => {
      b.addEventListener('click', () => upd(b.getAttribute('data-pay')));
    });
  }
  function paySubValue() {
    const sel = document.getElementById('paySub');
    if (!sel || sel.closest('#paySubWrap').style.display === 'none') return {};
    const o = {};
    o[sel.getAttribute('name')] = sel.value;
    return o;
  }
  /* Render kode/QR bayar langsung ala Shopee. */
  function renderDirectPay(box, d) {
    let h = '';
    if (d.kind === 'qr' && d.qr_url) {
      h = '<p class="service-desc" style="text-align:center;"><b>' + esc(d.title || 'Scan QR untuk Bayar') + '</b></p>' +
        '<div style="text-align:center;"><img src="' + esc(d.qr_url) + '" alt="QR Bayar" style="max-width:230px;border-radius:16px;border:1px solid var(--color-border-light);"></div>';
    } else if (d.kind === 'va') {
      h = '<p class="service-desc" style="text-align:center;"><b>' + esc(d.title || 'Virtual Account') + '</b></p>' +
        '<p style="text-align:center;font-size:1.5rem;font-weight:800;letter-spacing:2px;color:var(--color-primary);">' + esc(d.va_number) + '</p>' +
        '<div style="text-align:center;"><button type="button" class="btn-pill btn-pill-blue" data-copy="' + esc(d.va_number) + '" style="padding:8px 20px;font-size:.8rem;"><span>Salin Kode</span></button></div>';
    } else if (d.kind === 'code') {
      h = '<p class="service-desc" style="text-align:center;"><b>' + esc(d.title || 'Kode Bayar') + '</b></p>' +
        '<p style="text-align:center;font-size:1.5rem;font-weight:800;letter-spacing:2px;color:var(--color-primary);">' + esc(d.payment_code) + '</p>' +
        '<div style="text-align:center;"><button type="button" class="btn-pill btn-pill-blue" data-copy="' + esc(d.payment_code) + '" style="padding:8px 20px;font-size:.8rem;"><span>Salin Kode</span></button></div>';
    } else if (d.kind === 'redirect' && d.redirect_url) {
      h = '<div style="text-align:center;"><a class="btn-pill btn-pill-blue" href="' + esc(d.redirect_url) + '" style="padding:10px 22px;"><span>Lanjutkan ke Pembayaran</span></a></div>';
    } else {
      h = '<p class="service-desc">Menyiapkan pembayaran...</p>';
    }
    if (d.gross) h += '<p class="service-desc" style="text-align:center;">Total: <b>Rp ' + Number(d.gross).toLocaleString('id-ID') + '</b>' + (d.expires ? ' • Kedaluwarsa: ' + esc(d.expires) : '') + '</p>';
    box.innerHTML = h;
    box.style.display = 'block';
    const cp = box.querySelector('[data-copy]');
    if (cp) cp.addEventListener('click', () => {
      if (navigator.clipboard) navigator.clipboard.writeText(cp.getAttribute('data-copy'));
      toast('Kode disalin.', 'success');
    });
  }
  function loadScript(src) {
    return new Promise((resolve, reject) => {
      if (document.querySelector('script[src="' + src + '"]')) return resolve();
      const s = document.createElement('script');
      s.src = src; s.onload = resolve; s.onerror = reject;
      document.head.appendChild(s);
    });
  }
  async function payWithSnap(snapToken) {
    try {
      const meta = await loadMeta();
      if (!meta.midtrans_client_key) throw new Error('Client key Midtrans belum dikonfigurasi.');
      const host = meta.midtrans_is_production ? 'app.midtrans.com' : 'app.sandbox.midtrans.com';
      await loadScript('https://' + host + '/snap/snap.js');
      window.snap.pay(snapToken, {
        onSuccess: function () { toast('Pembayaran berhasil!', 'success'); setTimeout(() => { window.location.href = 'dashboard.html'; }, 1000); },
        onPending: function () { toast('Menunggu pembayaran...', 'info'); setTimeout(() => { window.location.href = 'dashboard.html'; }, 1000); },
        onClose: function () { toast('Jendela pembayaran ditutup.', 'info'); },
      });
    } catch (err) {
      toast(err.message || 'Gagal membuka pembayaran.', 'error');
    }
  }
