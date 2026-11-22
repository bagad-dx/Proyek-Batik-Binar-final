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
})();
