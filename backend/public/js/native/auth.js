/* BATIK BINAR native frontend: Auth. */
'use strict';

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
    /* Pengaman login Belanja: hormati ?redirect= (hanya path relatif
       di situs ini) agar user kembali ke belanja setelah login. */
    try {
      const red = new URLSearchParams(window.location.search).get('redirect') || '';
      if (/^[A-Za-z0-9._\-\/]+\.(?:html|php)(\?.*)?$/.test(red) && red.indexOf('..') === -1 && red.indexOf('//') === -1 && red.indexOf(':') === -1) return red;
    } catch (e) {}
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
     ditolak dengan pesan error.
     MODE DEMO (pengaturan_otp.demo = 1): backend juga mengembalikan
     `demo_code` (kode ASLI yang sama dengan yang diverifikasi) dan halaman
     menampilkannya di kotak "MODE DEMO" + mengisi otomatis kolom OTP. */
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
      '<div id="otpDemoBox" class="otp-demo-box" style="display:none;">' +
      '<span class="otp-demo-badge">MODE DEMO</span>' +
      '<p class="service-desc" style="margin:6px 0 2px;">Kode OTP demo Anda &mdash; tidak perlu cek email/WA:</p>' +
      '<div class="otp-demo-code" id="otpDemoCode">------</div>' +
      '<p class="service-desc" style="margin-top:4px;">Kode sudah diisikan otomatis ke kolom di bawah. Ini hanya untuk demo, matikan sebelum go-live.</p>' +
      '</div>' +
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
      const hintFor = (ok, live) => (!ok ? 'Belum diisi admin' : (cfg.demo ? 'Mode demo: kode OTP tampil di layar' : (local ? 'Mode tes lokal: kode di otp-log.log' : live)));
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
      const demoBox = document.getElementById('otpDemoBox');
      if (demoBox) demoBox.style.display = 'none';
      step1Nodes.forEach((el) => { el.style.display = ''; });
      if (channelBox) channelBox.style.display = '';
      if (submitBtn) submitBtn.style.display = '';
    }

    function showStep2(email, channel, expiresIn, infoMsg, resendIn, demoCode) {
      step1Nodes.forEach((el) => { el.style.display = 'none'; });
      if (channelBox) channelBox.style.display = 'none';
      if (submitBtn) submitBtn.style.display = 'none';
      otpPanel.style.display = 'block';
      const dest = channel === 'wa' ? 'WhatsApp' : 'email ' + email;
      document.getElementById('otpInfo').textContent =
        (infoMsg || 'Kode OTP dikirim ke ' + dest + '.') + ' Masukkan 6 digit kode.';
      const input = otpInput();
      input.value = '';
      const demoBox = document.getElementById('otpDemoBox');
      if (demoCode) {
        document.getElementById('otpInfo').textContent = 'Mode demo aktif: kode OTP tampil di kotak di bawah, tidak perlu cek email/WA.';
        document.getElementById('otpDemoCode').textContent = demoCode;
        if (demoBox) demoBox.style.display = '';
        input.value = demoCode;
      } else if (demoBox) {
        demoBox.style.display = 'none';
      }
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
        showStep2(data.email, data.channel, data.expires_in, data.message, data.resend_in, data.demo_code);
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
        showStep2(data.email, data.channel, data.expires_in, data.message, data.resend_in, data.demo_code);
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
      '.otp-demo-box{margin:12px 0;padding:14px 16px;border:2px dashed #B45309;border-radius:14px;background:#FFFBEB;}' +
      '.otp-demo-badge{display:inline-block;background:#B45309;color:#fff;font-size:.72rem;font-weight:800;letter-spacing:1px;padding:3px 10px;border-radius:999px;}' +
      '.otp-demo-code{text-align:center;font-size:2rem;font-weight:800;letter-spacing:10px;color:#92400E;background:#fff;border:1px solid #FDE68A;border-radius:10px;padding:8px 4px;margin:6px 0;}' +
      '.otp-actions{display:flex;gap:8px;margin-top:10px;}' +
      '.nb-mini-btn{border:1px solid var(--color-border-light,#e5ddcf);background:#fff;border-radius:999px;padding:8px 16px;font-size:.8rem;cursor:pointer;}' +
      '.nb-mini-btn.primary{background:var(--color-primary,#152A88);color:#fff;border-color:transparent;}' +
      '.nb-mini-btn:disabled{opacity:.6;cursor:not-allowed;}';
    document.head.appendChild(st);
  }

  /* Label & warna badge status pesanan (dipakai dashboard + semua pesanan). */
  const STATUS_CLASS = { pending: 'status-process', diproses: 'status-process', dikirim: 'status-shipped', selesai: 'status-done', batal: 'status-process' };
  const STATUS_LABEL = { pending: 'Pending', diproses: 'Diproses', dikirim: 'Dikirim', selesai: 'Selesai', batal: 'Batal' };

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
