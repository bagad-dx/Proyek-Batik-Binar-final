/* Admin Panel Batik Binar — jam live + sidebar mobile.
   Diadaptasi dari FrontEnd/admin/admin.html (referensi).
   Tidak ada logika bisnis di sini; hanya chrome panel. */
(function () {
  /* ===== LIVE CLOCK (Bahasa Indonesia) ===== */
  function updateClock() {
    var now = new Date();
    var days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    var months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Ags', 'Sep', 'Okt', 'Nov', 'Des'];
    function pad(n) { return String(n).padStart(2, '0'); }
    var timeEl = document.getElementById('clockTime');
    var dateEl = document.getElementById('clockDate');
    if (timeEl) timeEl.textContent = pad(now.getHours()) + ':' + pad(now.getMinutes()) + ':' + pad(now.getSeconds());
    if (dateEl) dateEl.textContent = days[now.getDay()] + ', ' + now.getDate() + ' ' + months[now.getMonth()] + ' ' + now.getFullYear();
  }
  updateClock();
  setInterval(updateClock, 1000);

  /* ===== MOBILE SIDEBAR TOGGLE ===== */
  function toggleSidebar() {
    var sb = document.getElementById('sidebar');
    var ov = document.getElementById('sidebarOverlay');
    if (sb) sb.classList.toggle('mobile-open');
    if (ov) ov.classList.toggle('mobile-open');
  }

  function closeSidebar() {
    var sb = document.getElementById('sidebar');
    var ov = document.getElementById('sidebarOverlay');
    if (sb) sb.classList.remove('mobile-open');
    if (ov) ov.classList.remove('mobile-open');
  }

  window.toggleSidebar = toggleSidebar;
  window.closeSidebar = closeSidebar;

  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') closeSidebar();
  });

  /* ===== DROPDOWN NOTIFIKASI (data live dari /api/notifications) =====
     Struktur mengikuti referensi admin.html; isinya diambil dari API,
     bukan data contoh. Gagal/tanpa token -> tampil status kosong, lonceng tetap jalan. */
  (function initNotifDropdown() {
    var bellBtn = document.getElementById('notifBellBtn');
    var dropdown = document.getElementById('notifDropdown');
    if (!bellBtn || !dropdown) return;
    var backdrop = document.getElementById('notifBackdrop');
    var markAll = document.getElementById('notifMarkAll');
    var badge = document.getElementById('notifBadge');
    var list = document.getElementById('notifList');

    function openNotif() { dropdown.classList.add('open'); if (backdrop) backdrop.classList.add('active'); }
    function closeNotif() { dropdown.classList.remove('open'); if (backdrop) backdrop.classList.remove('active'); }
    /* Total belum dibaca dari API (null = belum diketahui -> fallback hitung DOM).
       Dipakai karena dropdown hanya merender 5 item, jadi hitungan DOM mentok di 5. */
    var unreadTotal = null;
    function updateBadge() {
      if (!badge) return;
      var n = (typeof unreadTotal === 'number' && unreadTotal >= 0)
        ? unreadTotal
        : dropdown.querySelectorAll('.notif-item.unread').length;
      badge.textContent = n;
      badge.style.display = n > 0 ? 'flex' : 'none';
    }
    function timeAgo(iso) {
      var t = new Date(iso).getTime();
      if (isNaN(t)) return '';
      var s = Math.floor((Date.now() - t) / 1000);
      if (s < 60) return 'Baru saja';
      var m = Math.floor(s / 60);
      if (m < 60) return m + ' menit yang lalu';
      var h = Math.floor(m / 60);
      if (h < 24) return h + ' jam yang lalu';
      var d = Math.floor(h / 24);
      if (d < 30) return d + ' hari yang lalu';
      return new Date(iso).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
    }
    var ICONS = {
      pembayaran: ['icon-success', '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>'],
      chat: ['icon-info', '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>'],
      pesanan: ['icon-warning', '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>']
    };
    function markRead(id) {
      var token = null;
      try { token = localStorage.getItem('binar_token'); } catch (e) { token = null; }
      if (!token) return;
      fetch('/api/notifications/read', {
        method: 'PATCH',
        headers: { 'Authorization': 'Bearer ' + token, 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify(id ? { id: id } : {})
      }).catch(function () { /* abaikan: status baca bukan kritis */ });
    }
    function render(items) {
      if (!list) return;
      list.innerHTML = '';
      if (!items || !items.length) {
        var empty = document.createElement('div');
        empty.className = 'notif-empty';
        empty.textContent = 'Belum ada notifikasi.';
        list.appendChild(empty);
        updateBadge();
        return;
      }
      items.sort(function (a, b) { return (a.dibaca_at ? 1 : 0) - (b.dibaca_at ? 1 : 0); });
      items.slice(0, 5).forEach(function (it) {
        var icon = ICONS[it.tipe] || ICONS.pesanan;
        var item = document.createElement('div');
        item.className = 'notif-item' + (it.dibaca_at ? '' : ' unread');
        item.setAttribute('data-notif-id', it.id);
        var ic = document.createElement('div');
        ic.className = 'notif-icon ' + icon[0];
        ic.innerHTML = icon[1];
        var ct = document.createElement('div');
        ct.className = 'notif-content';
        var ti = document.createElement('div');
        ti.className = 'notif-title';
        ti.textContent = it.judul || 'Notifikasi';
        var de = document.createElement('div');
        de.className = 'notif-desc';
        de.textContent = it.pesan || '';
        var tm = document.createElement('div');
        tm.className = 'notif-time';
        tm.textContent = timeAgo(it.created_at);
        ct.appendChild(ti); ct.appendChild(de); ct.appendChild(tm);
        item.appendChild(ic); item.appendChild(ct);
        item.addEventListener('click', function () {
          item.classList.remove('unread');
          if (typeof unreadTotal === 'number' && unreadTotal > 0) unreadTotal -= 1;
          updateBadge();
          markRead(it.id);
          var url = it.url || 'pesanan.html';
          if (url.indexOf('http') !== 0 && url.charAt(0) !== '/') url = '/' + url;
          window.location.href = url;
        });
        list.appendChild(item);
      });
      updateBadge();
    }
    function load() {
      var token = null;
      try { token = localStorage.getItem('binar_token'); } catch (e) { token = null; }
      if (!token) { render([]); return; }
      fetch('/api/notifications', { headers: { 'Authorization': 'Bearer ' + token, 'Accept': 'application/json' } })
        .then(function (r) { if (!r.ok) throw new Error('http ' + r.status); return r.json(); })
        .then(function (j) {
          if (j && typeof j.belum_dibaca === 'number') unreadTotal = j.belum_dibaca;
          render(j.items || []);
        })
        .catch(function () { render([]); });
    }
    bellBtn.addEventListener('click', function (e) {
      e.stopPropagation();
      if (dropdown.classList.contains('open')) closeNotif(); else { openNotif(); load(); }
    });
    if (backdrop) backdrop.addEventListener('click', closeNotif);
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && dropdown.classList.contains('open')) closeNotif();
    });
    document.addEventListener('click', function (e) {
      if (!dropdown.contains(e.target) && !bellBtn.contains(e.target)) closeNotif();
    });
    if (markAll) {
      markAll.addEventListener('click', function () {
        dropdown.querySelectorAll('.notif-item.unread').forEach(function (el) { el.classList.remove('unread'); });
        unreadTotal = 0;
        updateBadge();
        markRead(null);
      });
    }
    load();
  })();

  /* ===== PRODUK TERLARIS (data live dari /api/products, terjual = order lunas) ===== */
  (function initTopProducts() {
    var box = document.getElementById('topProducts');
    if (!box) return;
    function esc(s) {
      return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
        return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
      });
    }
    function compactPrice(n) {
      n = parseFloat(n) || 0;
      if (n >= 1000000) {
        var v = n / 1000000;
        return 'Rp ' + (Number.isInteger(v) ? String(v) : v.toFixed(2).replace('.', ',')) + 'Jt';
      }
      if (n >= 1000) return 'Rp ' + Math.round(n / 1000) + 'Rb';
      return 'Rp ' + n.toLocaleString('id-ID');
    }
    var queue = ['/api/products'];
    var all = [];
    var loadFailed = false; // true bila ada halaman API yang gagal dimuat
    function render() {
      var top = all
        .map(function (p) { p._sold = parseInt(p.terjual, 10) || 0; return p; })
        .filter(function (p) { return p._sold > 0; })
        .sort(function (a, b) { return b._sold - a._sold; })
        .slice(0, 3);
      if (!top.length) {
        if (loadFailed) {
          box.innerHTML = '<div class="notif-empty">Gagal memuat produk terlaris, coba refresh halaman.</div>';
        } else {
          box.innerHTML = '<div class="notif-empty">Belum ada produk yang terjual (order lunas).</div>';
        }
        return;
      }
      var ranks = ['gold', 'silver', 'bronze'];
      box.innerHTML = top.map(function (p, i) {
        var cat = (p.category && p.category.nama) || (p.jenis && p.jenis.label_display) || '';
        return '<div class="top-prod-item"><div class="top-prod-rank ' + ranks[i] + '">' + (i + 1) + '</div>' +
          '<img class="top-prod-img" src="' + esc(p.gambar || '') + '" alt="' + esc(p.nama) + '" onerror="this.src=\'/assets/logo-batik-binar.png\'"/>' +
          '<div class="top-prod-info"><div class="top-prod-name">' + esc(p.nama) + '</div>' +
          '<div class="top-prod-cat">' + esc(cat) + ' &middot; ' + p._sold + ' terjual</div></div>' +
          '<div class="top-prod-price">' + compactPrice(p.harga) + '</div></div>';
      }).join('');
    }
    function loadNext() {
      if (!queue.length) { render(); return; }
      var u = queue.shift();
      fetch(u, { headers: { 'Accept': 'application/json' } })
        .then(function (r) { if (!r.ok) throw new Error('http ' + r.status); return r.json(); })
        .then(function (j) {
          if (j && j.data) all = all.concat(j.data);
          if (j && j.next_page_url) queue.push(j.next_page_url);
          loadNext();
        })
        .catch(function () { loadFailed = true; render(); });
    }
    loadNext();
  })();
})();
