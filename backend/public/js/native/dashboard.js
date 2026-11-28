/* BATIK BINAR native frontend: Dashboard. */
'use strict';

  /* ---------- dashboard.html: data live ---------- */
  /* Avatar dashboard user: foto profil bila ada, inisial bila belum. */
  function paintDashAvatars(me) {
    const src = me && me.avatar ? imgUrl(me.avatar, '') : '';
    const initial = (((me && (me.name || me.username)) || '?') + '').trim().charAt(0).toUpperCase();
    const html = src
      ? '<img src="' + esc(src) + '" alt="Foto profil" style="width:100%;height:100%;border-radius:50%;object-fit:cover;">'
      : '<span style="display:flex;width:100%;height:100%;align-items:center;justify-content:center;font-size:1.5rem;font-weight:700;">' + esc(initial) + '</span>';
    const heroAv = document.querySelector('.dash-avatar');
    if (heroAv) heroAv.innerHTML = html;
    const cardAv = document.querySelector('.dash-profile-avatar');
    if (cardAv) cardAv.innerHTML = html;
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
      const displayName = String(me.name || me.username || 'Pengguna').trim();
      hero.textContent = 'Selamat Datang, ' + displayName + '!';
      paintDashAvatars(me);
      const setText = (sel, txt) => { const el = $(sel); if (el) el.textContent = txt; };
      setText('.dash-profile-name', displayName);
      setText('.dash-profile-email', me.email || '');
      const rows = $$('.dash-profile-row');
      rows.forEach((row) => {
        const label = (row.querySelector('.dash-profile-label') || {}).textContent || '';
        const val = row.querySelector('.dash-profile-value');
        if (!val) return;
        if (/telepon/i.test(label)) val.textContent = me.phone || '-';
        if (/bergabung/i.test(label)) {
          const t = new Date(me.created_at);
          val.textContent = me.created_at && !Number.isNaN(t.getTime())
            ? t.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }) +
              ', ' + t.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }).replace(':', '.')
            : '-';
        }
        if (/status/i.test(label)) val.textContent = me.banned_at ? 'Banned' : (me.is_active ? 'Aktif' : 'Nonaktif');
      });
      const orders = s.pesanan_terbaru || [];
      const nums = $$('.dash-stat-number');
      if (nums[0]) nums[0].textContent = s.pesanan_aktif || 0;
      if (nums[1]) nums[1].textContent = s.wishlist || 0;
      if (nums[2]) nums[2].textContent = s.riwayat_pesanan || 0;
      const list = document.querySelector('.dash-orders-list');
      if (list) {
        if (!orders.length) {
          list.innerHTML = '<p class="service-desc">Belum ada pesanan. <a href="belanja.php">Belanja dulu</a></p>';
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
      const list = document.querySelector('.dash-orders-list');
      if (list) list.innerHTML = '<p class="service-desc">Data akun tidak dapat dimuat.</p>';
      toast(e.message, 'error');
    }
  }
