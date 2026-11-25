/* BATIK BINAR native frontend: Chat. */
'use strict';

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
        window.location.href = m ? 'produk.html?id=' + m[1] : 'belanja.php';
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
            '<div class="nb-notif-item" data-notif-id="' + x.id + '" data-notif-url="' + esc(x.url || 'pesanan.html') + '" role="button" tabindex="0" style="padding:10px 12px;border-radius:12px;background:' + (x.dibaca_at ? '#fff' : 'var(--color-primary-light,#EEF2FF)') + ';margin-bottom:6px;cursor:pointer;">' +
            '<b style="font-size:.82rem;">' + esc(x.judul) + '</b><p style="font-size:.78rem;margin:2px 0;color:var(--color-text-muted);">' + esc(x.pesan) + '</p></div>'
          ).join('');
          $$('.nb-notif-item', drop).forEach((el) => {
            const go = async () => {
              const id = el.getAttribute('data-notif-id');
              const url = el.getAttribute('data-notif-url') || 'pesanan.html';
              try { await api('/notifications/read', { method: 'PATCH', body: { id: Number(id) } }); } catch (e) {}
              window.location.href = url;
            };
            el.addEventListener('click', go);
            el.addEventListener('keydown', (ev) => { if (ev.key === 'Enter') go(); });
          });
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
