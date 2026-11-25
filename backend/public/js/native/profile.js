/* BATIK BINAR native frontend: Profile. */
'use strict';

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
      '<div class="form-group"><label class="form-label">Foto Profil</label><div style="display:flex;gap:12px;align-items:center;"><span id="nbPfAvatarPrev" style="width:64px;height:64px;border-radius:50%;background:linear-gradient(135deg,#152A88,#0B5FA5);color:#fff;font-weight:800;font-size:1.4rem;display:inline-flex;align-items:center;justify-content:center;overflow:hidden;flex:none;">?</span><div class="input-wrapper" style="flex:1;"><input type="file" id="nbPfAvatar" class="form-control" accept="image/jpeg,image/png,image/webp"></div></div><p class="service-meta-text">JPG/PNG/WebP, maks 2MB.</p></div>' +
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
