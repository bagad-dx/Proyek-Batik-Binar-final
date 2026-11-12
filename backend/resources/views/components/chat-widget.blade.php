{{-- Widget Chatbot Binar: asisten batik untuk pembeli --}}
<style>
  .binar-chat-fab { position:fixed; right:22px; bottom:22px; z-index:9000; width:60px; height:60px; border-radius:50%; background:linear-gradient(135deg,var(--color-primary),var(--color-accent)); color:#fff; font-size:26px; display:flex; align-items:center; justify-content:center; cursor:pointer; box-shadow:0 10px 28px rgba(22,135,217,.45); border:3px solid #fff; transition:transform .25s; }
  .binar-chat-fab:hover { transform:scale(1.08); }
  .binar-chat-panel { position:fixed; right:22px; bottom:94px; z-index:9000; width:min(380px,calc(100vw - 32px)); height:min(540px,calc(100vh - 140px)); background:#fff; border-radius:24px; box-shadow:0 24px 60px rgba(11,23,84,.3); display:none; flex-direction:column; overflow:hidden; border:1px solid var(--color-border-light); }
  .binar-chat-panel.open { display:flex; }
  .binar-chat-head { background:linear-gradient(120deg,var(--color-primary-dark),var(--color-primary) 55%,var(--color-accent)); color:#fff; padding:14px 16px; display:flex; align-items:center; gap:10px; }
  .binar-chat-head img { width:38px; height:38px; border-radius:50%; background:#fff; object-fit:cover; border:2px solid rgba(255,255,255,.7); }
  .binar-chat-head .t b { display:block; font-size:.95rem; }
  .binar-chat-head .t span { font-size:.72rem; opacity:.85; display:flex; align-items:center; gap:5px; }
  .binar-chat-head .t span::before { content:""; width:8px; height:8px; border-radius:50%; background:#4ADE80; display:inline-block; }
  .binar-chat-head button { margin-left:auto; color:#fff; font-size:22px; line-height:1; opacity:.8; }
  .binar-chat-body { flex:1; overflow-y:auto; padding:14px; display:flex; flex-direction:column; gap:10px; background:var(--color-bg); }
  .binar-msg { max-width:85%; padding:10px 14px; border-radius:16px; font-size:.87rem; line-height:1.55; }
  .binar-msg.bot { background:#fff; border:1px solid var(--color-border-light); border-bottom-left-radius:4px; align-self:flex-start; box-shadow:var(--shadow-sm); }
  .binar-msg.user { background:var(--color-accent); color:#fff; border-bottom-right-radius:4px; align-self:flex-end; }
  .binar-chips { display:flex; flex-wrap:wrap; gap:6px; }
  .binar-chip { font-size:.75rem; font-weight:700; color:var(--color-primary); background:var(--color-primary-light); border:1px solid var(--color-border); padding:6px 12px; border-radius:9999px; cursor:pointer; }
  .binar-chip:hover { background:var(--color-accent); color:#fff; }
  .binar-prod { display:flex; gap:10px; background:#fff; border:1px solid var(--color-border-light); border-radius:14px; padding:8px; align-items:center; }
  .binar-prod img { width:52px; height:52px; border-radius:10px; object-fit:cover; background:var(--color-bg-alt); }
  .binar-prod .p { flex:1; min-width:0; }
  .binar-prod .p b { display:block; font-size:.8rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
  .binar-prod .p span { font-size:.78rem; color:var(--color-accent); font-weight:800; }
  .binar-prod a { font-size:.75rem; font-weight:800; color:#fff; background:var(--color-primary); padding:6px 12px; border-radius:9999px; }
  .binar-chat-foot { display:flex; gap:8px; padding:10px; border-top:1px solid var(--color-border-light); background:#fff; }
  .binar-chat-foot input { flex:1; border:1px solid var(--color-border); border-radius:9999px; padding:10px 16px; font-size:.87rem; outline:none; }
  .binar-chat-foot input:focus { border-color:var(--color-accent); }
  .binar-chat-foot button { width:42px; height:42px; border-radius:50%; background:var(--color-accent); color:#fff; font-size:18px; flex-shrink:0; box-shadow:0 4px 12px var(--color-accent-glow); }
  .binar-typing { font-size:.8rem; color:var(--color-text-muted); font-style:italic; }
</style>
<div class="binar-chat-fab" id="binarFab" title="Tanya Binar">✦</div>
<div class="binar-chat-panel" id="binarPanel" role="dialog" aria-label="Chat Binar">
  <div class="binar-chat-head">
    <img src="{{ asset('assets/logo-batik-binar.png') }}" alt="Binar">
    <div class="t"><b id="binarName">Binar Assistant</b><span id="binarStatus">Online — spesialis batik</span></div>
    <button id="binarClose" aria-label="Tutup chat">&times;</button>
  </div>
  <div class="binar-chat-body" id="binarBody"></div>
  <div class="binar-chat-foot">
    <input type="text" id="binarInput" placeholder="Tanya soal batik..." autocomplete="off" maxlength="500">
    <button id="binarSend" aria-label="Kirim">➤</button>
  </div>
</div>
<script>
(function () {
  var fab = document.getElementById('binarFab');
  var panel = document.getElementById('binarPanel');
  var body = document.getElementById('binarBody');
  var input = document.getElementById('binarInput');
  var greeted = false;
  var botActive = true;
  var chatHistory = [];
  function esc(s) { var d = document.createElement('div'); d.textContent = s; return d.innerHTML; }
  function addMsg(html, who) { var m = document.createElement('div'); m.className = 'binar-msg ' + who; m.innerHTML = html; body.appendChild(m); body.scrollTop = body.scrollHeight; return m; }
  function addChips(list) {
    var w = document.createElement('div'); w.className = 'binar-chips';
    list.forEach(function (s) { var b = document.createElement('button'); b.className = 'binar-chip'; b.textContent = s; b.onclick = function () { send(s); }; w.appendChild(b); });
    body.appendChild(w); body.scrollTop = body.scrollHeight;
  }
  function addProducts(list) {
    list.forEach(function (p) {
      var d = document.createElement('div'); d.className = 'binar-prod';
      var img = p.gambar ? '<img src="' + esc(p.gambar) + '" alt="">' : '';
      d.innerHTML = img + '<div class="p"><b>' + esc(p.nama) + '</b><span>' + esc(p.harga) + ' • stok ' + p.stok + '</span></div><a href="' + esc(p.url) + '">Lihat</a>';
      body.appendChild(d);
    });
    body.scrollTop = body.scrollHeight;
  }
  function toggle(force) {
    var open = force !== undefined ? force : !panel.classList.contains('open');
    panel.classList.toggle('open', open);
    if (open && !greeted) {
      greeted = true;
      fetch("{{ url('/api/chat/config') }}", { headers: { 'Accept': 'application/json' } })
        .then(function (r) { return r.json(); })
        .then(function (c) {
          document.getElementById('binarName').textContent = (c.bot_name || 'Binar') + ' Assistant';
          if (!c.is_active) { botActive = false; document.getElementById('binarStatus').textContent = 'Offline'; addMsg('Maaf, layanan chat sedang nonaktif. Hubungi halo@batikbinar.id ya!', 'bot'); return; }
          addMsg(c.greeting || 'Halo! Ada yang bisa saya bantu?', 'bot');
          addChips(['Best seller apa?', 'Lihat motif batik', 'Cara beli', 'Biaya ongkir']);
        })
        .catch(function () { addMsg('Halo! Saya <b>Binar</b>, asisten Batik Binar. Ada yang bisa saya bantu?', 'bot'); });
    }
    if (open) setTimeout(function () { input.focus(); }, 100);
  }
  function send(text) {
    var msg = (text !== undefined ? text : input.value).trim();
    if (!msg || !botActive) return;
    addMsg(esc(msg), 'user'); input.value = '';
    chatHistory.push({ role: 'user', text: msg });
    var tp = addMsg('<span class="binar-typing">Binar mengetik...</span>', 'bot');
    fetch("{{ url('/api/chat') }}", { method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' }, body: JSON.stringify({ message: msg, history: chatHistory.slice(-10) }) })
      .then(function (r) { return r.json(); })
      .then(function (d) {
        tp.innerHTML = d.reply || 'Maaf, ada gangguan. Coba lagi ya!';
        chatHistory.push({ role: 'bot', text: tp.textContent });
        if (d.products && d.products.length) addProducts(d.products);
        if (d.suggestions && d.suggestions.length) addChips(d.suggestions.slice(0, 4));
        body.scrollTop = body.scrollHeight;
      })
      .catch(function () { tp.textContent = 'Koneksi bermasalah. Coba lagi ya!'; });
  }
  fab.addEventListener('click', function () { toggle(); });
  document.getElementById('binarClose').addEventListener('click', function () { toggle(false); });
  document.getElementById('binarSend').addEventListener('click', function () { send(); });
  input.addEventListener('keydown', function (e) { if (e.key === 'Enter') send(); });
})();
</script>
