/* BATIK BINAR native frontend: Global. */
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
    if (gambar.indexOf('assets/storage/') === 0) return API_BASE + '/' + gambar;
    return API_BASE + '/storage/' + gambar;
  }
  const FALLBACK_IMG = 'assets/img/pin-7b7da76e.jpg';

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
        : '<span style="width:38px;height:38px;border-radius:50%;background:linear-gradient(135deg,#152A88,#0B5FA5);color:#fff;font-weight:800;display:inline-flex;align-items:center;justify-content:center;font-size:1rem;border:2px solid #fff;box-shadow:0 4px 12px rgba(21,42,136,.25);">' + esc(initial) + '</span>';
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

  async function healTokenFromSession() {
    if (!SAME_ORIGIN) return;
    try {
      const r = await fetch(API_BASE + '/api/session-token', {
        headers: { Accept: 'application/json' }, credentials: 'include',
      });
      if (!r.ok) return;
      const d = await r.json();
      if (!d || !d.user || !d.token) return;
      const cur = store.user;
      if (!store.token || !cur || String(cur.id) !== String(d.user.id)) {
        store.save(d.token, d.user);
      }
    } catch (e) {}
  }

  /* ---------- pengaman login halaman Belanja ----------
     Belanja hanya untuk user login. Token di localStorage saja belum
     cukup bukti — validasi lewat /me (auth:sanctum). Dipanggil di boot
     sebelum initNativeShop; kembalikan true bila mengalihkan (boot berhenti). */
  async function guardBelanjaPage() {
    if (!/belanja\.php$/i.test(window.location.pathname)) return false;
    if (!store.token) { window.location.replace('login.html?redirect=belanja.php'); return true; }
    try { await api('/me'); } catch (e) { store.clear(); window.location.replace('login.html?redirect=belanja.php'); return true; }
    return false;
  }

  /* ---------- boot ---------- */
  document.addEventListener('DOMContentLoaded', async () => {
    loadNativeCss();
    await healTokenFromSession();
    if (await guardBelanjaPage()) return;
    initHeaderAuth();
    initNativeLogin();
    initNativeRegister();
    initGoogleButtons();
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
