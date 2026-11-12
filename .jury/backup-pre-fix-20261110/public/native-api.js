/**
 * BATIK BINAR - Native <-> Framework Bridge (HYBRID)
 * Halaman HTML native (byte-identik folder FrontEnd/PROYEK) berbicara
 * ke backend Laravel (Oracle XE) lewat JSON API. File ini satu-satunya
 * tambahan; style.css & script.js tidak diubah.
 */
(function () {
  'use strict';

  /* ---------- util ---------- */
  const $ = (s, r) => (r || document).querySelector(s);
  const $$ = (s, r) => Array.from((r || document).querySelectorAll(s));
  const rupiah = (n) => 'Rp ' + Number(n || 0).toLocaleString('id-ID');
  const esc = (s) => String(s == null ? '' : s).replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

  const store = {
    get token() { try { return localStorage.getItem('binar_token'); } catch (e) { return null; } },
    get user() { try { return JSON.parse(localStorage.getItem('binar_user') || 'null'); } catch (e) { return null; } },
    save(token, user) {
      try {
        localStorage.setItem('binar_token', token);
        localStorage.setItem('binar_user', JSON.stringify(user));
      } catch (e) {}
    },
    clear() { try { localStorage.removeItem('binar_token'); localStorage.removeItem('binar_user'); } catch (e) {} },
  };

  /* ---------- basis API (mode hybrid terpisah) ----------
     window.BINAR_API_BASE kosong = mode gabung (backend serve html + api
     satu proses). Isi window.BINAR_API_BASE via config.js saat frontend
     jalan terpisah, mis. 'http://127.0.0.1:8000'. */
  const API_BASE = String(
    window.BINAR_API_BASE || localStorage.getItem('binar_api_base') || ''
  ).replace(/\/$/, '');
  const SAME_ORIGIN = !API_BASE;

  async function api(path, opts) {
    opts = opts || {};
    const headers = Object.assign({ 'Content-Type': 'application/json', Accept: 'application/json' }, opts.headers || {});
    if (store.token) headers.Authorization = 'Bearer ' + store.token;
    const res = await fetch(API_BASE + '/api' + path, { method: opts.method || 'GET', headers, body: opts.body ? JSON.stringify(opts.body) : undefined });
    let data = null;
    try { data = await res.json(); } catch (e) {}
    if (!res.ok) {
      const msg = (data && (data.message || (data.errors ? Object.values(data.errors).flat().join(', ') : ''))) || ('HTTP ' + res.status);
      const err = new Error(msg);
      err.status = res.status;
      err.data = data;
      throw err;
    }
    return data;
  }

  /* Upload multipart (foto profil): tanpa JSON, pakai FormData. */
  async function apiForm(path, formData) {
    const headers = { Accept: 'application/json' };
    if (store.token) headers.Authorization = 'Bearer ' + store.token;
    const res = await fetch(API_BASE + '/api' + path, { method: 'POST', headers, body: formData });
    let data = null;
    try { data = await res.json(); } catch (e) {}
    if (!res.ok) {
      const msg = (data && (data.message || (data.errors ? Object.values(data.errors).flat().join(', ') : ''))) || ('HTTP ' + res.status);
      throw new Error(msg);
    }
    return data;
  }

  function toast(msg, type) {
    if (typeof showToast === 'function') showToast(msg, type || 'info');
    else alert(msg);
  }

  function imgUrl(gambar, fallback) {
    if (!gambar) return fallback;
    if (/^https?:\/\//.test(gambar)) return gambar;
    if (gambar.charAt(0) === '/') return API_BASE + gambar;
    return API_BASE + '/storage/' + gambar;
  }
  const FALLBACK_IMG = 'https://i.pinimg.com/1200x/7b/7d/a7/7b7da76e1d305f43cf9ddda3e83f8bc3.jpg';

  /* ---------- sesi web (hanya mode gabung; mode pisah pakai token API) ---------- */
  async function ensureWebSession(email, password) {
    if (!SAME_ORIGIN) return;
    try {
      await fetch(API_BASE + '/sanctum/csrf-cookie', { headers: { Accept: 'application/json' } });
      const m = document.cookie.match(/XSRF-TOKEN=([^;]+)/);
      await fetch(API_BASE + '/login', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json', Accept: 'application/json',
          'X-XSRF-TOKEN': m ? decodeURIComponent(m[1]) : '',
        },
        body: JSON.stringify({ email, password }),
      });
    } catch (e) {}
  }

  async function doLogout() {
    try { if (store.token) await api('/logout', { method: 'POST' }); } catch (e) {}
    if (!SAME_ORIGIN) { store.clear(); window.location.href = 'index.html'; return; }
    try {
      await fetch(API_BASE + '/sanctum/csrf-cookie', { headers: { Accept: 'application/json' } });
      const m = document.cookie.match(/XSRF-TOKEN=([^;]+)/);
      await fetch(API_BASE + '/logout', {
        method: 'POST',
        headers: { 'X-XSRF-TOKEN': m ? decodeURIComponent(m[1]) : '', Accept: 'application/json' },
      });
    } catch (e) {}
    store.clear();
    window.location.href = 'index.html';
  }

  /* ---------- header: status login ---------- */
  /* Navbar semua halaman native: tamu -> tombol Login; login -> foto profil
     (atau huruf inisial bila belum pasang foto) + nama + Logout. Ikon orang
     generik dibuang saat sudah login agar tidak ganda. */
  async function initHeaderAuth() {
    const wraps = $$('.nav-actions');
    if (!wraps.length) return;
    const wrap = wraps[0];
    const paintGuest = () => {
      if (!wrap.querySelector('a.btn-pill[href="login.html"]')) {
        const a = document.createElement('a');
        a.href = 'login.html';
        a.className = 'btn-pill btn-pill-blue';
        a.style.cssText = 'padding:8px 18px;font-size:.88rem;';
        a.innerHTML = '<span>Login</span>';
        wrap.appendChild(a);
      }
    };
    if (!store.token) { paintGuest(); return; }
    try {
      const me = await api('/me');
      try { localStorage.setItem('binar_user', JSON.stringify(me)); } catch (e) {}
      wrap.querySelectorAll('a.btn-pill[href="login.html"]').forEach((el) => el.remove());
      wrap.querySelectorAll('a.nav-icon-btn[href="dashboard.html"]').forEach((el) => el.remove());
      // foto profil bila ada, huruf inisial nama bila belum ada foto
      const initial = ((me.name || me.username || '?') + '').trim().charAt(0).toUpperCase();
      const av = me.avatar
        ? '<img src="' + esc(imgUrl(me.avatar, '')) + '" alt="Foto profil" style="width:38px;height:38px;border-radius:50%;object-fit:cover;border:2px solid #fff;box-shadow:0 4px 12px rgba(21,42,136,.25);">'
        : '<span style="width:38px;height:38px;border-radius:50%;background:linear-gradient(135deg,#152A88,#1687D9);color:#fff;font-weight:800;display:inline-flex;align-items:center;justify-content:center;font-size:1rem;border:2px solid #fff;box-shadow:0 4px 12px rgba(21,42,136,.25);">' + esc(initial) + '</span>';
      const prof = document.createElement('a');
      prof.href = 'dashboard.html';
      prof.title = 'Akun saya';
      prof.style.cssText = 'display:inline-flex;align-items:center;gap:8px;text-decoration:none;';
      prof.innerHTML = av + '<b style="font-size:.88rem;color:inherit;">' + esc(me.name || me.username) + '</b>';
      const out = document.createElement('button');
      out.type = 'button';
      out.className = 'btn-pill btn-pill-royal';
      out.style.cssText = 'padding:8px 18px;font-size:.88rem;';
      out.innerHTML = '<span>Logout</span>';
      out.addEventListener('click', doLogout);
      wrap.appendChild(prof);
      wrap.appendChild(out);
      initNotifBell(wrap);
    } catch (e) {
      store.clear();
      paintGuest();
    }
  }

  /* ---------- login.html ---------- */
  // rename SEBELUM script.js pasang handler (script ini jalan saat parsing,
  // handler script.js dipasang saat DOMContentLoaded) agar validasi+redirect
  // palsu tidak ikut jalan.
  (function () {
    const lf = document.getElementById('loginForm');
    if (lf) lf.id = 'loginApiForm';
    const rf = document.getElementById('registerForm');
    if (rf) rf.id = 'registerApiForm';
  })();

  /* ---------- login satu pintu: redirect otomatis mengikuti role ----------
     Akun terdaftar sebagai admin/superadmin -> panel pengelola,
     selain itu (termasuk pendaftar baru) -> beranda user. */
  function afterLoginRedirect(user) {
    const role = user && user.role;
    if (role === 'superadmin') return API_BASE + '/panel/superadmin/dashboard';
    if (role === 'admin') return API_BASE + '/panel/admin/dashboard';
    return 'index.html';
  }
  function afterLoginMessage(user) {
    const role = user && user.role;
    if (role === 'superadmin' || role === 'admin') return 'Berhasil masuk sebagai ' + role + '! Membuka panel pengelola...';
    return 'Berhasil masuk! Selamat datang di Batik Binar.';
  }

  function initNativeLogin() {
    const form = document.getElementById('loginApiForm');
    if (!form) return;
    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      const emailEl = document.getElementById('loginEmail');
      const passEl = document.getElementById('loginPassword');
      const email = emailEl.value.trim();
      if (typeof clearFieldError === 'function') { clearFieldError(emailEl); clearFieldError(passEl); }
      if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email) && email.length < 3) {
        if (typeof showFieldError === 'function') showFieldError(emailEl, 'Masukkan email atau username yang valid.');
        return;
      }
      if (!passEl.value) {
        if (typeof showFieldError === 'function') showFieldError(passEl, 'Kata sandi tidak boleh kosong.');
        return;
      }
      try {
        const data = await api('/login', { method: 'POST', body: { email, password: passEl.value } });
        store.save(data.token, data.user);
        await ensureWebSession(email, passEl.value);
        toast(afterLoginMessage(data.user), 'success');
        setTimeout(() => { window.location.href = afterLoginRedirect(data.user); }, 900);
      } catch (err) {
        toast(err.message, 'error');
      }
    });
  }

  /* ---------- register.html (2 langkah: data diri -> verifikasi OTP) ----------
     Kode OTP 6 digit dikirim NYATA ke Email (SMTP) atau WhatsApp (Fonnte),
     berlaku 10 menit, maks 5x salah. Bila pengiriman gagal, pendaftaran
     ditolak dengan pesan error (tidak ada kode demo di layar). */
  function initNativeRegister() {
    const form = document.getElementById('registerApiForm');
    if (!form) return;
    if (form.dataset.otpReady) return;
    form.dataset.otpReady = '1';

    injectOtpStyles();

    const otpCfg = { email_configured: false, wa_configured: false, resend_seconds: 30 };

    // --- Langkah 1: pilihan channel, disisipkan sebelum tombol submit ---
    const submitBtn = form.querySelector('button[type="submit"]');
    const channelBox = document.createElement('div');
    channelBox.className = 'form-group otp-channel-group';
    channelBox.innerHTML =
      '<label class="form-label">Kirim Kode Verifikasi (OTP) ke</label>' +
      '<div class="otp-channels">' +
      '<label class="otp-channel"><input type="radio" name="otpChannel" value="email" checked>' +
      '<span class="otp-channel-card"><b>Email / Gmail</b><small id="otpChannelEmailHint">Memeriksa...</small></span></label>' +
      '<label class="otp-channel"><input type="radio" name="otpChannel" value="wa">' +
      '<span class="otp-channel-card"><b>WhatsApp</b><small id="otpChannelWaHint">Memeriksa...</small></span></label>' +
      '</div>';
    if (submitBtn) form.insertBefore(channelBox, submitBtn);

    // --- Langkah 2: panel OTP (disembunyikan sampai kode diminta) ---
    const step1Nodes = Array.from(form.children).filter((el) => el !== channelBox && el.tagName !== 'SCRIPT');
    const otpPanel = document.createElement('div');
    otpPanel.id = 'otpPanel';
    otpPanel.style.display = 'none';
    otpPanel.innerHTML =
      '<div class="otp-panel-card">' +
      '<h3 class="otp-title">Verifikasi Pendaftaran</h3>' +
      '<p class="service-desc" id="otpInfo">Meminta kode...</p>' +
      '<div class="form-group"><label class="form-label" for="otpCode">Kode OTP (6 digit)</label>' +
      '<div class="input-wrapper"><input type="text" id="otpCode" class="form-control otp-input" ' +
      'placeholder="••••••" maxlength="6" inputmode="numeric" autocomplete="one-time-code"></div></div>' +
      '<p class="service-desc">Kode kedaluwarsa dalam <b id="otpTimer">10:00</b></p>' +
      '<div class="otp-actions">' +
      '<button type="button" class="btn-pill btn-pill-royal auth-submit-btn" id="otpVerifyBtn" style="flex:1;justify-content:center;"><span>Verifikasi & Buat Akun</span></button>' +
      '</div>' +
      '<div class="otp-actions">' +
      '<button type="button" class="nb-mini-btn" id="otpResendBtn">Kirim ulang kode (<span id="otpResendCount">30</span>s)</button>' +
      '<button type="button" class="nb-mini-btn" id="otpBackBtn">Ganti data / channel</button>' +
      '</div>' +
      '</div>';
    form.appendChild(otpPanel);

    const radio = (value) => form.querySelector('input[name="otpChannel"][value="' + value + '"]');

    function setChannelAvailability(cfg) {
      const emailOk = !!cfg.email_configured;
      const waOk = !!cfg.wa_configured;
      Object.assign(otpCfg, cfg || {});

      const emHint = document.getElementById('otpChannelEmailHint');
      const waHint = document.getElementById('otpChannelWaHint');
      const local = !!cfg.local_fallback;
      const hintFor = (ok, live) => (!ok ? 'Belum diisi admin' : (local ? 'Mode tes lokal: kode di laravel.log' : live));
      if (emHint) emHint.textContent = hintFor(emailOk, 'Kode dikirim ke inbox email');
      if (waHint) waHint.textContent = hintFor(waOk, 'Kode dikirim ke WhatsApp');

      [['email', emailOk], ['wa', waOk]].forEach(([key, ok]) => {
        const input = radio(key);
        if (!input) return;
        input.disabled = !ok;
        const card = input.closest('.otp-channel');
        if (card) card.style.opacity = ok ? '1' : '.45';
      });

      if (emailOk) radio('email').checked = true;
      else if (waOk) radio('wa').checked = true;

      if (!emailOk && !waOk) {
        const warn = document.createElement('p');
        warn.className = 'service-desc';
        warn.style.cssText = 'color:#991B1B;font-weight:600;margin-top:8px;';
        warn.textContent = 'Verifikasi OTP belum aktif. Hubungi admin untuk mengisi pengaturan Email/WhatsApp.';
        channelBox.appendChild(warn);
        if (submitBtn) submitBtn.disabled = true;
      }
    }

    // Tandai konfigurasi channel yang aktif (email/wa) dari backend.
    api('/otp/config').then(setChannelAvailability).catch(() => {
      const emHint = document.getElementById('otpChannelEmailHint');
      const waHint = document.getElementById('otpChannelWaHint');
      if (emHint) emHint.textContent = 'Gagal memeriksa status';
      if (waHint) waHint.textContent = 'Gagal memeriksa status';
    });

    const pending = { email: '', channel: 'email', seconds: 600, timerId: null, resendId: null, resendWait: 0 };
    const otpInput = () => document.getElementById('otpCode');

    const selChannel = () => {
      const r = form.querySelector('input[name="otpChannel"]:checked');
      return r ? r.value : 'email';
    };

    function showStep1() {
      stopTimers();
      otpPanel.style.display = 'none';
      step1Nodes.forEach((el) => { el.style.display = ''; });
      if (channelBox) channelBox.style.display = '';
      if (submitBtn) submitBtn.style.display = '';
    }

    function showStep2(email, channel, expiresIn, infoMsg, resendIn) {
      step1Nodes.forEach((el) => { el.style.display = 'none'; });
      if (channelBox) channelBox.style.display = 'none';
      if (submitBtn) submitBtn.style.display = 'none';
      otpPanel.style.display = 'block';
      const dest = channel === 'wa' ? 'WhatsApp' : 'email ' + email;
      document.getElementById('otpInfo').textContent =
        (infoMsg || 'Kode OTP dikirim ke ' + dest + '.') + ' Masukkan 6 digit kode.';
      const input = otpInput();
      input.value = '';
      setTimeout(() => input.focus(), 100);
      startCountdown(expiresIn || 600);
      startResendCooldown(resendIn || otpCfg.resend_seconds || 30);
      otpPanel.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    function startCountdown(sec) {
      stopTimers();
      pending.seconds = sec;
      const el = document.getElementById('otpTimer');
      const tick = () => {
        const m = Math.floor(pending.seconds / 60);
        const s = pending.seconds % 60;
        if (el) el.textContent = m + ':' + (s < 10 ? '0' : '') + s;
        if (pending.seconds <= 0) {
          toast('Kode kedaluwarsa. Minta kode baru.', 'error');
          stopTimers();
          return;
        }
        pending.seconds--;
      };
      tick();
      pending.timerId = setInterval(tick, 1000);
    }

    function startResendCooldown(sec) {
      const btn = document.getElementById('otpResendBtn');
      const count = document.getElementById('otpResendCount');
      pending.resendWait = sec;
      const tick = () => {
        if (pending.resendWait <= 0) {
          btn.disabled = false;
          btn.innerHTML = 'Kirim ulang kode';
          clearInterval(pending.resendId);
          return;
        }
        btn.disabled = true;
        btn.innerHTML = 'Kirim ulang kode (<span id="otpResendCount">' + pending.resendWait + '</span>s)';
        pending.resendWait--;
      };
      tick();
      pending.resendId = setInterval(tick, 1000);
      void count;
    }

    function stopTimers() {
      if (pending.timerId) clearInterval(pending.timerId);
      if (pending.resendId) clearInterval(pending.resendId);
      pending.timerId = pending.resendId = null;
    }

    async function requestOtp() {
      const v = (id) => document.getElementById(id).value.trim();
      const name = v('regName'), email = v('regEmail'), phone = v('regPhone');
      const pass = document.getElementById('regPassword').value;
      const pass2 = document.getElementById('regConfirmPassword').value;
      if (name.length < 3) { toast('Nama lengkap minimal 3 karakter.', 'error'); return; }
      if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) { toast('Masukkan alamat email yang valid.', 'error'); return; }
      if ((phone.replace(/\D/g, '') || '').length < 9) { toast('Nomor telepon minimal 9 digit angka.', 'error'); return; }
      if (pass.length < 6) { toast('Kata sandi minimal 6 karakter.', 'error'); return; }
      if (pass !== pass2) { toast('Konfirmasi kata sandi tidak cocok.', 'error'); return; }
      const terms = document.getElementById('regTerms');
      if (terms && !terms.checked) { toast('Centang persetujuan Syarat & Ketentuan dulu.', 'error'); return; }
      pending.channel = selChannel();
      submitBtn.disabled = true;
      try {
        const data = await api('/otp/request', {
          method: 'POST',
          body: {
            name, email, phone,
            password: pass, password_confirmation: pass2,
            channel: pending.channel,
          },
        });
        pending.email = data.email;
        pending.channel = data.channel;
        toast(data.message, 'success');
        showStep2(data.email, data.channel, data.expires_in, data.message, data.resend_in);
      } catch (err) {
        toast(err.message, 'error');
      } finally {
        submitBtn.disabled = false;
      }
    }

    async function verifyOtp() {
      const code = otpInput().value.replace(/\D/g, '');
      if (code.length !== 6) { toast('Masukkan 6 digit kode OTP.', 'error'); return; }
      const btn = document.getElementById('otpVerifyBtn');
      btn.disabled = true;
      try {
        const data = await api('/otp/verify', { method: 'POST', body: { email: pending.email, code } });
        store.save(data.token, data.user);
        await ensureWebSession(pending.email, document.getElementById('regPassword').value);
        stopTimers();
        toast('Akun terverifikasi! Selamat datang di Batik Binar.', 'success');
        setTimeout(() => { window.location.href = 'index.html'; }, 1200);
      } catch (err) {
        toast(err.message, 'error');
      } finally {
        btn.disabled = false;
      }
    }

    async function resendOtp() {
      if (!pending.email) return;
      try {
        const data = await api('/otp/resend', {
          method: 'POST', body: { email: pending.email, channel: pending.channel },
        });
        pending.channel = data.channel;
        toast(data.message, 'success');
        showStep2(data.email, data.channel, data.expires_in, data.message, data.resend_in);
      } catch (err) {
        toast(err.message, 'error');
      }
    }

    form.addEventListener('submit', (e) => { e.preventDefault(); requestOtp(); });
    otpPanel.addEventListener('click', (e) => {
      if (e.target.closest('#otpVerifyBtn')) verifyOtp();
      else if (e.target.closest('#otpResendBtn')) resendOtp();
      else if (e.target.closest('#otpBackBtn')) showStep1();
    });
    otpInput().addEventListener('input', (e) => {
      e.target.value = e.target.value.replace(/\D/g, '').slice(0, 6);
      if (e.target.value.length === 6) verifyOtp();
    });
    otpInput().addEventListener('keydown', (e) => {
      if (e.key === 'Enter') { e.preventDefault(); verifyOtp(); }
    });
  }

  /* Gaya tambahan untuk pilihan channel + panel OTP (tanpa ubah file CSS). */
  function injectOtpStyles() {
    if (document.getElementById('otpExtraCss')) return;
    const st = document.createElement('style');
    st.id = 'otpExtraCss';
    st.textContent =
      '.otp-channels{display:flex;gap:10px;flex-wrap:wrap;}' +
      '.otp-channel{flex:1;min-width:150px;cursor:pointer;}' +
      '.otp-channel input{position:absolute;opacity:0;pointer-events:none;}' +
      '.otp-channel-card{display:flex;flex-direction:column;gap:2px;padding:12px 14px;border:2px solid var(--color-border-light,#e5ddcf);border-radius:14px;background:#fff;transition:.2s;}' +
      '.otp-channel-card small{color:#888;font-size:.78rem;}' +
      '.otp-channel input:checked+.otp-channel-card{border-color:var(--color-primary,#152A88);background:rgba(21,42,136,.05);box-shadow:0 4px 14px rgba(21,42,136,.12);}' +
      '.otp-panel-card{background:#fff;border:2px solid var(--color-border-light,#e5ddcf);border-radius:18px;padding:22px;margin:6px 0 14px;box-shadow:0 10px 30px rgba(21,42,136,.08);}' +
      '.otp-title{margin:0 0 8px;font-size:1.15rem;color:var(--color-primary,#152A88);}' +
      '.otp-input{text-align:center;font-size:1.4rem !important;font-weight:800;letter-spacing:8px;}' +
      '.otp-actions{display:flex;gap:8px;margin-top:10px;}' +
      '.nb-mini-btn{border:1px solid var(--color-border-light,#e5ddcf);background:#fff;border-radius:999px;padding:8px 16px;font-size:.8rem;cursor:pointer;}' +
      '.nb-mini-btn.primary{background:var(--color-primary,#152A88);color:#fff;border-color:transparent;}' +
      '.nb-mini-btn:disabled{opacity:.6;cursor:not-allowed;}';
    document.head.appendChild(st);
  }

  /* Label & warna badge status pesanan (dipakai dashboard + semua pesanan). */
  const STATUS_CLASS = { pending: 'status-process', diproses: 'status-process', dikirim: 'status-shipped', selesai: 'status-done', batal: 'status-process' };
  const STATUS_LABEL = { pending: 'Pending', diproses: 'Diproses', dikirim: 'Dikirim', selesai: 'Selesai', batal: 'Batal' };

  /* ---------- pilihan metode ala Shopee ---------- */
  const NB_PAY = [
    { key: 'transfer', label: 'Transfer Bank', gw: false },
    { key: 'cod', label: 'COD', gw: false },
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
    cod: { label: 'Pilih Gerai Tunai', name: 'store', opts: [['alfamart', 'Alfamart'], ['indomaret', 'Indomaret']] },
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

  /* ---------- tombol Google (Firebase) ---------- */
  function initGoogleButtons() {
    const btns = $$('.google-auth-btn');
    if (!btns.length) return;
    btns.forEach((btn) => {
      btn.addEventListener('click', async () => {
        try {
          await loadScript('https://www.gstatic.com/firebasejs/10.12.2/firebase-app-compat.js');
          await loadScript('https://www.gstatic.com/firebasejs/10.12.2/firebase-auth-compat.js');
          const cfg = await (await fetch(API_BASE + '/api/firebase-config', { headers: { Accept: 'application/json' } })).json();
          if (!cfg.api_key) throw new Error('Firebase belum dikonfigurasi.');
          if (!window.__fbNative) {
            window.__fbNative = firebase.initializeApp({
              apiKey: cfg.api_key, authDomain: cfg.auth_domain, projectId: cfg.project_id,
              storageBucket: cfg.storage_bucket, messagingSenderId: cfg.messaging_sender_id, appId: cfg.app_id,
            });
          }
          btn.disabled = true;
          const cred = await window.__fbNative.auth().signInWithPopup(new firebase.auth.GoogleAuthProvider());
          const idToken = await cred.user.getIdToken();
          const data = await api('/auth/google', { method: 'POST', body: { idToken } });
          store.save(data.token, data.user);
          toast(afterLoginMessage(data.user), 'success');
          setTimeout(() => { window.location.href = afterLoginRedirect(data.user); }, 900);
        } catch (err) {
          toast(err.message || 'Login Google gagal.', 'error');
          btn.disabled = false;
        }
      });
    });
  }

  /* ---------- belanja.html: grid + checkout dari API ---------- */
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

  /* ---------- dashboard.html: data live ---------- */
  /* Avatar dashboard user: foto profil bila ada, inisial bila belum. */
  function paintDashAvatars(me) {
    const src = me && me.avatar ? imgUrl(me.avatar, '') : '';
    const initial = (((me && (me.name || me.username)) || '?') + '').trim().charAt(0).toUpperCase();
    const html = src
      ? '<img src="' + esc(src) + '" alt="Foto profil" style="width:100%;height:100%;border-radius:50%;object-fit:cover;">'
      : null;
    const heroAv = document.querySelector('.dash-avatar');
    if (heroAv && html) heroAv.innerHTML = html;
    const cardAv = document.querySelector('.dash-profile-avatar');
    if (cardAv && html) cardAv.innerHTML = html;
  }

  async function initNativeDashboard() {
    const hero = document.querySelector('.dash-hero-title');
    if (!hero || !document.querySelector('.dash-orders-list')) return;
    if (!store.token) { window.location.href = 'login.html'; return; }
    try {
      // 1 panggilan ringkas: profil + pesanan aktif + riwayat + wishlist + terbaru
      const s = await api('/dashboard/summary');
      const me = s.user || {};
      // Satu pintu: akun admin/superadmin bukan penghuni dashboard user.
      if (me.role === 'superadmin' || me.role === 'admin') {
        toast('Akun ' + me.role + ' diarahkan ke panel pengelola.', 'info');
        setTimeout(() => { window.location.href = afterLoginRedirect(me); }, 700);
        return;
      }
      hero.textContent = 'Selamat Datang, ' + (me.name || me.username) + '!';
      paintDashAvatars(me);
      const setText = (sel, txt) => { const el = $(sel); if (el) el.textContent = txt; };
      setText('.dash-profile-name', me.name || me.username);
      setText('.dash-profile-email', me.email || '');
      const rows = $$('.dash-profile-row');
      rows.forEach((row) => {
        const label = (row.querySelector('.dash-profile-label') || {}).textContent || '';
        const val = row.querySelector('.dash-profile-value');
        if (!val) return;
        if (/telepon/i.test(label)) val.textContent = me.phone || '-';
        if (/bergabung/i.test(label) && me.created_at) {
          const t = new Date(me.created_at);
          val.textContent = t.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }) +
            ', ' + t.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }).replace(':', '.');
        }
      });
      const orders = s.pesanan_terbaru || [];
      const nums = $$('.dash-stat-number');
      if (nums[0]) nums[0].textContent = s.pesanan_aktif || 0;
      if (nums[2]) nums[2].textContent = s.riwayat_pesanan || 0;
      const list = document.querySelector('.dash-orders-list');
      if (list) {
        if (!orders.length) {
          list.innerHTML = '<p class="service-desc">Belum ada pesanan. <a href="belanja.html">Belanja dulu</a></p>';
        } else {
          list.innerHTML = orders.slice(0, 5).map(orderItemHtml).join('');
          bindOrderButtons(list);
        }
      }
      const logoutBtn = document.querySelector('.nav-actions a.btn-pill[href="login.html"]');
      if (logoutBtn) logoutBtn.addEventListener('click', (e) => { e.preventDefault(); doLogout(); });
    } catch (e) {
      // Hanya sesi kedaluwarsa yang melempar ke login; error lain (mis.
      // jaringan) tidak boleh menghapus token + menendang user.
      if (String(e.message).includes('401') || String(e.message).includes('Unauthenticated')) {
        store.clear();
        window.location.href = 'login.html';
        return;
      }
      toast(e.message, 'error');
    }
  }

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
        body.innerHTML = '<p class="service-desc">Keranjang kosong. <a href="belanja.html">Belanja dulu</a></p>';
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
    // angka wishlist live di dashboard
    const nums = $$('.dash-stat-number');
    if (nums[1] && document.querySelector('.dash-orders-list') && store.token) {
      api('/wishlist').then((items) => { nums[1].textContent = items.length; }).catch(() => {});
    }
    // tombol Wishlist (menu cepat) -> modal
    $$('.dash-action-btn').forEach((a) => {
      const label = (a.querySelector('span') || {}).textContent || '';
      if (/wishlist/i.test(label)) {
        a.addEventListener('click', (e) => { e.preventDefault(); openWishlistModal(); });
      }
    });
  }

  /* ================= EDIT PROFIL MODAL (dashboard) ================= */
  function ensureProfileModal() {
    let m = document.getElementById('nbProfileModal');
    if (m) return m;
    m = document.createElement('div');
    m.id = 'nbProfileModal'; m.className = 'nb-modal';
    m.innerHTML = '<div class="nb-modal-card">' +
      '<div class="auth-card-top-nav" style="margin-bottom:6px;"><a class="auth-card-logo"><img src="assets/logo-batik-binar.png" alt="Logo Batik Binar"><span>BATIK BINAR</span></a>' +
      '<button class="nb-drawer-close" type="button" aria-label="Tutup" style="font-size:24px;line-height:1;color:var(--color-text-muted);background:none;border:none;cursor:pointer;">&times;</button></div>' +
      '<div class="auth-header" style="text-align:left;margin-bottom:8px;"><h2 style="font-size:1.35rem;">Edit Profil</h2><p>Perbarui data akun Batik Binar Anda</p></div>' +
      '<div class="form-group"><label class="form-label">Foto Profil</label><div style="display:flex;gap:12px;align-items:center;"><span id="nbPfAvatarPrev" style="width:64px;height:64px;border-radius:50%;background:linear-gradient(135deg,#152A88,#1687D9);color:#fff;font-weight:800;font-size:1.4rem;display:inline-flex;align-items:center;justify-content:center;overflow:hidden;flex:none;">?</span><div class="input-wrapper" style="flex:1;"><input type="file" id="nbPfAvatar" class="form-control" accept="image/jpeg,image/png,image/webp"></div></div><p class="service-meta-text">JPG/PNG/WebP, maks 2MB.</p></div>' +
      '<div class="form-group"><label class="form-label">Nama Lengkap</label><div class="input-wrapper"><input type="text" id="nbPfName" class="form-control" placeholder="Nama lengkap Anda"></div></div>' +
      '<div class="form-group"><label class="form-label">Gmail</label><div class="input-wrapper"><input type="email" id="nbPfEmail" class="form-control" placeholder="nama@email.com"></div></div>' +
      '<div class="form-group"><label class="form-label">No HP (WhatsApp)</label><div class="input-wrapper"><input type="text" id="nbPfPhone" class="form-control" placeholder="0812..."></div></div>' +
      '<div class="form-group"><label class="form-label">Alamat</label><div class="input-wrapper"><textarea id="nbPfAlamat" class="form-control" rows="2" placeholder="Jalan, kecamatan, kota..."></textarea></div></div>' +
      '<div class="auth-divider"><span>keamanan</span></div>' +
      '<div class="form-group"><label class="form-label">Password Baru (kosongkan bila tak diubah)</label><div class="input-wrapper"><input type="password" id="nbPfPass" class="form-control" autocomplete="new-password"></div></div>' +
      '<div class="form-group"><label class="form-label">Konfirmasi Password Baru</label><div class="input-wrapper"><input type="password" id="nbPfPass2" class="form-control" autocomplete="new-password"></div></div>' +
      '<button type="button" class="btn-pill btn-pill-royal auth-submit-btn" id="nbPfSave"><span>Simpan Perubahan</span><div class="btn-arrow-circle"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg></div></button></div>';
    document.body.appendChild(m);
    m.querySelector('.nb-drawer-close').addEventListener('click', () => m.classList.remove('open'));
    m.addEventListener('click', (e) => { if (e.target === m) m.classList.remove('open'); });
    return m;
  }
  function initProfileModal() {
    const btns = $$('a.btn-pill').filter((a) => /edit profil/i.test(a.textContent || ''));
    if (!btns.length || !document.querySelector('.dash-profile-card')) return;
    btns.forEach((b) => b.addEventListener('click', async (e) => {
      e.preventDefault();
      const m = ensureProfileModal();
      try {
        const me = await api('/me');
        document.getElementById('nbPfName').value = me.name || '';
        document.getElementById('nbPfEmail').value = me.email || '';
        document.getElementById('nbPfPhone').value = me.phone || '';
        document.getElementById('nbPfAlamat').value = me.alamat || '';
        const prev = document.getElementById('nbPfAvatarPrev');
        if (prev) {
          if (me.avatar) prev.innerHTML = '<img src="' + esc(imgUrl(me.avatar, '')) + '" alt="" style="width:100%;height:100%;object-fit:cover;">';
          else prev.textContent = ((me.name || me.username || '?') + '').trim().charAt(0).toUpperCase();
        }
        const fileEl = document.getElementById('nbPfAvatar');
        if (fileEl) {
          fileEl.value = '';
          fileEl.onchange = () => {
            const f = fileEl.files && fileEl.files[0];
            if (f && prev) {
              const rd = new FileReader();
              rd.onload = () => { prev.innerHTML = '<img src="' + rd.result + '" alt="" style="width:100%;height:100%;object-fit:cover;">'; };
              rd.readAsDataURL(f);
            }
          };
        }
      } catch (err) {}
      m.classList.add('open');
      document.getElementById('nbPfSave').onclick = async () => {
        const payload = {
          name: document.getElementById('nbPfName').value.trim(),
          email: document.getElementById('nbPfEmail').value.trim(),
          phone: document.getElementById('nbPfPhone').value.trim(),
          alamat: document.getElementById('nbPfAlamat').value.trim(),
        };
        const p1 = document.getElementById('nbPfPass').value;
        const p2 = document.getElementById('nbPfPass2').value;
        if (p1 || p2) { payload.password = p1; payload.password_confirmation = p2; }
        try {
          const fileEl = document.getElementById('nbPfAvatar');
          const file = fileEl && fileEl.files && fileEl.files[0];
          if (file) {
            const fd = new FormData();
            fd.append('avatar', file);
            const up = await apiForm('/profile/avatar', fd);
            try { localStorage.setItem('binar_user', JSON.stringify(up.user)); } catch (err2) {}
            paintDashAvatars(up.user || {});
          }
          const res = await api('/profile', { method: 'PUT', body: payload });
          const u = res.user || {};
          if (store.token) { try { localStorage.setItem('binar_user', JSON.stringify(u)); } catch (err2) {} }
          const nm = document.querySelector('.dash-profile-name');
          if (nm) nm.textContent = u.name || nm.textContent;
          m.classList.remove('open');
          toast('Profil diperbarui.', 'success');
          setTimeout(() => { window.location.reload(); }, 900);
        } catch (err) { toast(err.message, 'error'); }
      };
    }));
  }

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
  function payCountdownStart(batas) {
    const el = document.getElementById('payCountdown');
    if (!el || !batas) return;
    const end = new Date(batas).getTime();
    const pad = (n) => String(n).padStart(2, '0');
    const tick = () => {
      const sisa = end - Date.now();
      if (sisa <= 0) {
        el.textContent = '00:00:00';
        const w = document.getElementById('payWarn');
        if (w) w.innerHTML = '<b>Waktu habis.</b> Pesanan kedaluwarsa — silakan buat pesanan baru. <a href="belanja.html">Belanja lagi</a>';
        const btns = document.querySelectorAll('#payNowBtn,#payRefresh');
        btns.forEach((b) => { b.disabled = true; });
        clearInterval(window.__payTick);
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
    const showPay = async (silent) => {
      try {
        const d = await api('/orders/' + oid + '/payment');
        if (d.paid || d.status === 'diproses') {
          toast('Pembayaran lunas! Pesanan diproses.', 'success');
          setTimeout(() => { window.location.href = 'dashboard.html'; }, 1200);
          return true;
        }
        renderDirectPay(box.querySelector('#payDirect'), d);
        if ((!d.kind || d.kind === 'info') && d.snap_token) {
          try {
            const host = d.is_production ? 'app.midtrans.com' : 'app.sandbox.midtrans.com';
            await loadScript('https://' + host + '/snap/snap.js');
            window.snap.pay(d.snap_token, {
              onSuccess: () => { window.location.href = 'dashboard.html'; },
              onPending: () => { window.location.href = 'dashboard.html'; },
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
      const d = await api('/orders/' + oid + '/payment');
      if (d.paid || d.status === 'diproses') {
        toast('Pesanan ini sudah lunas.', 'success');
        setTimeout(() => { window.location.href = 'dashboard.html'; }, 1200);
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
        '<button type="button" class="btn-pill btn-pill-royal" id="payRefresh" style="padding:10px 22px;"><span>Saya Sudah Bayar</span></button></div></div></div>' +
        '<div class="backend-card" style="margin-top:16px;"><h3 class="service-title">Cara Membayar</h3>' +
        '<ol class="pay-steps-list">' + payStepsFor(d.kind, grand).map((s) => '<li>' + esc(s) + '</li>').join('') + '</ol></div>';
      payCountdownStart(o.batas_bayar);
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
      // segarkan bila sudah lunas
      const iv = setInterval(async () => {
        try {
          const chk = await api('/orders/' + oid + '/payment');
          if (chk.paid || chk.status === 'diproses') {
            clearInterval(iv);
            toast('Pembayaran lunas! Pesanan diproses.', 'success');
            setTimeout(() => { window.location.href = 'dashboard.html'; }, 1200);
          }
        } catch (e) {}
      }, 15000);
      document.getElementById('payRefresh').addEventListener('click', async () => {
        try {
          const chk = await api('/orders/' + oid + '/payment');
          if (chk.paid || chk.status === 'diproses') {
            clearInterval(iv);
            toast('Pembayaran lunas! Pesanan diproses.', 'success');
            setTimeout(() => { window.location.href = 'dashboard.html'; }, 1200);
          } else {
            toast('Belum terdeteksi. Selesaikan pembayaran dulu ya.', 'info');
          }
        } catch (e) { toast(e.message, 'error'); }
      });
      // tampilkan QR/kode bayar segera saat halaman dibuka
      await showPay(true);
    } catch (err) {
      box.innerHTML = '<p class="service-desc">' + esc(err.message) + '</p>';
    }
  }

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
    return '<div class="dash-order-item" data-status="' + o.status + '" data-belum="' + (belum ? 1 : 0) + '"><div class="dash-order-img"><img src="' + esc(img) + '" alt=""></div>' +
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
    } catch (e) {
      if (String(e.message).includes('401') || String(e.message).includes('Unauthenticated')) {
        store.clear();
        window.location.href = 'login.html';
        return;
      }
      list.innerHTML = '<p class="service-desc">Gagal memuat pesanan: ' + esc(e.message) + '</p>';
    }
  }

  /* ================= CHAT BINAR (native) ================= */
  let nbChatHistory = [];
  let nbChatGreeted = false;
  let nbChatActive = true;
  function nbChatAdd(html, who) {
    const body = document.getElementById('binarBody');
    const m = document.createElement('div');
    m.className = 'binar-msg ' + who;
    m.innerHTML = html;
    body.appendChild(m);
    body.scrollTop = body.scrollHeight;
    return m;
  }
  function nbChatChips(list) {
    const body = document.getElementById('binarBody');
    const w = document.createElement('div');
    w.className = 'binar-chips';
    list.forEach((s) => {
      const b = document.createElement('button');
      b.type = 'button'; b.className = 'binar-chip'; b.textContent = s;
      b.addEventListener('click', () => nbChatSend(s));
      w.appendChild(b);
    });
    body.appendChild(w);
    body.scrollTop = body.scrollHeight;
  }
  function nbChatProducts(list) {
    const body = document.getElementById('binarBody');
    list.forEach((p) => {
      const d = document.createElement('div');
      d.className = 'binar-prod';
      const img = imgUrl(p.gambar, FALLBACK_IMG);
      d.innerHTML = '<img src="' + esc(img) + '" alt=""><div class="p"><b>' + esc(p.nama) + '</b><span>' + esc(p.harga) + ' • stok ' + p.stok + '</span></div>';
      const b = document.createElement('button');
      b.type = 'button'; b.textContent = 'Lihat';
      b.addEventListener('click', () => {
        if (typeof openShopDetailApi === 'function' && API_PRODUCTS.length) {
          const found = API_PRODUCTS.find((x) => String(x.id) === String(p.url.match(/(\d+)\/?$/)?.[1] || ''));
          if (found) { openShopDetailApi(found.id); return; }
        }
        const m = String(p.url || '').match(/(\d+)\/?$/);
        window.location.href = m ? 'produk.html?id=' + m[1] : 'belanja.html';
      });
      d.appendChild(b);
      body.appendChild(d);
    });
    body.scrollTop = body.scrollHeight;
  }
  async function nbChatSend(text) {
    const input = document.getElementById('binarInput');
    const msg = (text !== undefined ? text : input.value).trim();
    if (!msg || !nbChatActive) return;
    nbChatAdd(esc(msg), 'user');
    if (input) input.value = '';
    nbChatHistory.push({ role: 'user', text: msg });
    const tp = nbChatAdd('<span class="binar-typing">Binar mengetik...</span>', 'bot');
    try {
      const res = await fetch(API_BASE + '/api/chat', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
        body: JSON.stringify({ message: msg, history: nbChatHistory.slice(-10) }),
      });
      const d = await res.json();
      tp.innerHTML = d.reply || 'Maaf, ada gangguan. Coba lagi ya!';
      nbChatHistory.push({ role: 'bot', text: tp.textContent });
      if (d.products && d.products.length) nbChatProducts(d.products);
      if (d.suggestions && d.suggestions.length) {
        const chips = document.createElement('div');
        chips.className = 'binar-chips';
        d.suggestions.slice(0, 4).forEach((s) => {
          const b = document.createElement('button');
          b.type = 'button'; b.className = 'binar-chip'; b.textContent = s;
          b.addEventListener('click', () => nbChatSend(s));
          chips.appendChild(b);
        });
        document.getElementById('binarBody').appendChild(chips);
      }
      document.getElementById('binarBody').scrollTop = document.getElementById('binarBody').scrollHeight;
    } catch (e) { tp.textContent = 'Koneksi bermasalah. Coba lagi ya!'; }
  }
  function initNativeChat() {
    if (document.getElementById('binarFab')) return;
    const fab = document.createElement('div');
    fab.id = 'binarFab'; fab.className = 'binar-chat-fab'; fab.title = 'Tanya Binar'; fab.textContent = '✦';
    const panel = document.createElement('div');
    panel.id = 'binarPanel'; panel.className = 'binar-chat-panel';
    panel.innerHTML = '<div class="binar-chat-head"><img src="assets/logo-batik-binar.png" alt="Binar">' +
      '<div class="t"><b id="binarName">Binar Assistant</b><span id="binarStatus">Online — spesialis batik</span></div>' +
      '<button id="binarClose" aria-label="Tutup chat">&times;</button></div>' +
      '<div class="binar-chat-body" id="binarBody"></div>' +
      '<div class="binar-chat-foot"><input type="text" id="binarInput" placeholder="Tanya soal batik..." autocomplete="off" maxlength="500">' +
      '<button id="binarSend" aria-label="Kirim">➤</button></div>';
    document.body.appendChild(fab);
    document.body.appendChild(panel);
    const toggle = (force) => {
      const open = force !== undefined ? force : !panel.classList.contains('open');
      panel.classList.toggle('open', open);
      if (open && !nbChatGreeted) {
        nbChatGreeted = true;
        fetch(API_BASE + '/api/chat/config', { headers: { Accept: 'application/json' } })
          .then((r) => r.json())
          .then((c) => {
            document.getElementById('binarName').textContent = (c.bot_name || 'Binar') + ' Assistant';
            if (!c.is_active) { nbChatActive = false; document.getElementById('binarStatus').textContent = 'Offline'; nbChatAdd('Maaf, layanan chat sedang nonaktif.', 'bot'); return; }
            nbChatAdd(c.greeting || 'Halo! Ada yang bisa saya bantu?', 'bot');
            nbChatChips(['Best seller apa?', 'Lihat motif batik', 'Cara beli', 'Biaya ongkir']);
          })
          .catch(() => nbChatAdd('Halo! Ada yang bisa saya bantu?', 'bot'));
      }
    };
    fab.addEventListener('click', () => toggle());
    document.getElementById('binarClose').addEventListener('click', () => toggle(false));
    document.getElementById('binarSend').addEventListener('click', () => nbChatSend());
    document.getElementById('binarInput').addEventListener('keydown', (e) => { if (e.key === 'Enter') nbChatSend(); });
  }

  /* ================= NOTIFIKASI ================= */
  function initNotifBell(wrap) {
    if (!wrap || document.getElementById('nbBellBtn')) return;
    const btn = document.createElement('button');
    btn.type = 'button'; btn.id = 'nbBellBtn';
    btn.className = 'nav-icon-btn';
    btn.setAttribute('aria-label', 'Notifikasi');
    btn.style.position = 'relative';
    btn.innerHTML = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg><span class="nb-cart-count" id="nbNotifCount" style="display:none;">0</span>';
    const drop = document.createElement('div');
    drop.id = 'nbNotifDrop';
    drop.style.cssText = 'display:none;position:absolute;top:calc(100% + 10px);right:0;width:min(340px,86vw);max-height:380px;overflow-y:auto;background:#fff;border-radius:18px;box-shadow:0 24px 60px rgba(11,23,84,.3);padding:10px;z-index:9600;';
    const holder = document.createElement('div');
    holder.style.position = 'relative';
    holder.appendChild(btn);
    holder.appendChild(drop);
    wrap.insertBefore(holder, wrap.firstChild);
    btn.addEventListener('click', async (e) => {
      e.stopPropagation();
      const open = drop.style.display === 'block';
      if (open) { drop.style.display = 'none'; return; }
      drop.style.display = 'block';
      drop.innerHTML = '<p class="service-desc">Memuat...</p>';
      try {
        const n = await api('/notifications');
        const badge = document.getElementById('nbNotifCount');
        if (badge) { badge.textContent = n.belum_dibaca || 0; badge.style.display = n.belum_dibaca > 0 ? 'flex' : 'none'; }
        if (!n.items.length) { drop.innerHTML = '<p class="service-desc">Belum ada notifikasi.</p>'; }
        else {
          drop.innerHTML = n.items.map((x) =>
            '<div style="padding:10px 12px;border-radius:12px;background:' + (x.dibaca_at ? '#fff' : 'var(--color-primary-light,#EEF2FF)') + ';margin-bottom:6px;">' +
            '<b style="font-size:.82rem;">' + esc(x.judul) + '</b><p style="font-size:.78rem;margin:2px 0;color:var(--color-text-muted);">' + esc(x.pesan) + '</p></div>'
          ).join('');
        }
        await api('/notifications/read', { method: 'PATCH', body: {} });
        setTimeout(() => { const b2 = document.getElementById('nbNotifCount'); if (b2) b2.style.display = 'none'; }, 1500);
      } catch (err) { drop.innerHTML = '<p class="service-desc">Gagal memuat.</p>'; }
    });
    document.addEventListener('click', (e) => { if (!holder.contains(e.target)) drop.style.display = 'none'; });
    api('/notifications').then((n) => {
      const badge = document.getElementById('nbNotifCount');
      if (badge && n.belum_dibaca > 0) { badge.textContent = n.belum_dibaca; badge.style.display = 'flex'; }
    }).catch(() => {});
  }

  /* ================= CHAT PENJUAL (native) ================= */
  let nbChatOrderId = null;
  function ensureSellerChat() {
    let m = document.getElementById('nbSellerChat');
    if (m) return m;
    m = document.createElement('div');
    m.id = 'nbSellerChat'; m.className = 'nb-modal';
    m.innerHTML = '<div class="nb-modal-card" style="display:flex;flex-direction:column;height:min(560px,88vh);">' +
      '<div style="display:flex;align-items:center;gap:10px;margin-bottom:10px;"><h3 style="margin:0;flex:1;color:var(--color-primary);font-size:1rem;" id="nbSellerTitle">Chat Penjual</h3>' +
      '<button class="nb-drawer-close" type="button">&times;</button></div>' +
      '<div id="nbSellerList" style="overflow-y:auto;margin-bottom:10px;"></div>' +
      '<div id="nbSellerThread" style="flex:1;overflow-y:auto;display:flex;flex-direction:column;gap:8px;border-top:1px solid var(--color-border-light);padding-top:10px;min-height:120px;"></div>' +
      '<div style="display:flex;gap:8px;margin-top:10px;"><input type="text" id="nbSellerInput" class="form-control" placeholder="Tulis pesan..." maxlength="1000" style="flex:1;"><button type="button" class="btn-pill btn-pill-blue" id="nbSellerSend" style="padding:10px 20px;"><span>Kirim</span></button></div></div>';
    document.body.appendChild(m);
    m.querySelector('.nb-drawer-close').addEventListener('click', () => m.classList.remove('open'));
    m.addEventListener('click', (e) => { if (e.target === m) m.classList.remove('open'); });
    return m;
  }
  async function openSellerChat(orderId) {
    const m = ensureSellerChat();
    m.classList.add('open');
    await loadSellerList(orderId);
    document.getElementById('nbSellerSend').onclick = () => sendSellerMessage();
    document.getElementById('nbSellerInput').onkeydown = (e) => { if (e.key === 'Enter') sendSellerMessage(); };
  }
  async function loadSellerList(activeOrderId) {
    const box = document.getElementById('nbSellerList');
    box.innerHTML = '<p class="service-desc">Memuat percakapan...</p>';
    try {
      const chats = await api('/chats');
      if (!chats.length) { box.innerHTML = '<p class="service-desc">Belum ada percakapan. Mulai dari tombol Chat Penjual di pesanan.</p>'; }
      else {
        box.innerHTML = chats.map((c) =>
          '<button type="button" data-chat="' + c.id + '" class="nb-mini-btn ' + (String(activeOrderId) === String(c.order_id) || (!activeOrderId && c.id === window.__nbChatId) ? 'primary' : '') + '" style="margin:0 6px 6px 0;border:1px solid var(--color-border);background:' + (c.id === window.__nbChatId ? 'var(--color-accent);color:#fff;' : '#fff;') + '">' +
          esc(c.produk || ('Chat #' + c.id)) + (c.belum_dibaca ? ' (' + c.belum_dibaca + ')' : '') + '</button>'
        ).join('');
        $$('[data-chat]', box).forEach((b) => b.addEventListener('click', () => loadSellerThread(Number(b.getAttribute('data-chat')))));
      }
      let target = null;
      if (activeOrderId) {
        const res = await api('/chats', { method: 'POST', body: { order_id: Number(activeOrderId) } });
        target = res.id;
      } else if (chats.length) { target = chats[0].id; }
      if (target) loadSellerThread(target);
    } catch (err) { box.innerHTML = '<p class="service-desc">' + esc(err.message) + '</p>'; }
  }
  async function loadSellerThread(chatId) {
    window.__nbChatId = chatId;
    const box = document.getElementById('nbSellerThread');
    box.innerHTML = '<p class="service-desc">Memuat...</p>';
    try {
      const msgs = await api('/chats/' + chatId + '/messages');
      const me = store.user || {};
      box.innerHTML = msgs.length ? msgs.map((x) => {
        const mine = String(x.pengirim_id) === String(me.id);
        return '<div class="binar-msg ' + (mine ? 'user' : 'bot') + '" style="' + (mine ? '' : 'background:var(--color-bg-alt);border:1px solid var(--color-border-light);') + '">' + esc(x.pesan) + '</div>';
      }).join('') : '<p class="service-desc">Sapa penjual dulu yuk!</p>';
      box.scrollTop = box.scrollHeight;
      loadSellerListRefresh();
    } catch (err) { box.innerHTML = '<p class="service-desc">' + esc(err.message) + '</p>'; }
  }
  async function loadSellerListRefresh() {
    try {
      const chats = await api('/chats');
      const box = document.getElementById('nbSellerList');
      if (!box) return;
      box.innerHTML = chats.map((c) =>
        '<button type="button" data-chat="' + c.id + '" class="nb-mini-btn primary" style="margin:0 6px 6px 0;' + (c.id !== window.__nbChatId ? 'background:#fff;color:var(--color-primary);border:1px solid var(--color-border);' : '') + '">' +
        esc(c.produk || ('Chat #' + c.id)) + (c.belum_dibaca ? ' (' + c.belum_dibaca + ')' : '') + '</button>'
      ).join('') || '<p class="service-desc">Belum ada percakapan.</p>';
      $$('[data-chat]', box).forEach((b) => b.addEventListener('click', () => loadSellerThread(Number(b.getAttribute('data-chat')))));
    } catch (e) {}
  }
  async function sendSellerMessage() {
    const input = document.getElementById('nbSellerInput');
    const msg = input.value.trim();
    if (!msg || !window.__nbChatId) return;
    input.value = '';
    try {
      await api('/chats/' + window.__nbChatId + '/messages', { method: 'POST', body: { pesan: msg } });
      loadSellerThread(window.__nbChatId);
    } catch (err) { toast(err.message, 'error'); }
  }

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
        if (!items.length) { page.innerHTML = '<p class="service-desc">Tidak ada barang untuk checkout. <a href="belanja.html">Belanja dulu</a></p>'; return; }
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
          '<div class="row"><span>Subtotal Pengiriman</span><span>' + rupiah(ongkir) + '</span></div>' +
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
        '<img src="' + esc(img) + '" alt="' + esc(title) + '" loading="lazy">' +
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
        if (count) count.textContent = n + ' koleksi';
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
  async function healTokenFromSession() {
    if (!SAME_ORIGIN) return;
    try {
      const r = await fetch(API_BASE + '/api/session-token', {
        headers: { Accept: 'application/json' }, credentials: 'include',
      });
      if (!r.ok) return;
      const d = await r.json();
      const cur = store.user;
      if (!store.token || !cur || String(cur.id) !== String(d.user.id)) {
        store.save(d.token, d.user);
      }
    } catch (e) {}
  }

  /* ---------- boot ---------- */
  document.addEventListener('DOMContentLoaded', async () => {
    loadNativeCss();
    await healTokenFromSession();
    initHeaderAuth();
    initNativeLogin();
    initNativeRegister();
    initGoogleButtons();
    initNativeShop();
    initNativeGaleri();
    initNativeDashboard();
    initCartDrawer();
    initWishlistUI();
    initProfileModal();
    initNativeChat();
    initNativePayment();
    initNativeOrders();
    initNativeProduct();
    initNativeCheckout();
    const seeAll = document.querySelector('.dash-see-all');
    if (seeAll && document.querySelector('.dash-orders-list')) {
      seeAll.setAttribute('href', 'pesanan.html');
    }
  });

  function loadNativeCss() {
    if (document.querySelector('link[href^="css/16-native.css"]')) return;
    const l = document.createElement('link');
    l.rel = 'stylesheet'; l.href = 'css/16-native.css?v=2';
    document.head.appendChild(l);
  }
})();
