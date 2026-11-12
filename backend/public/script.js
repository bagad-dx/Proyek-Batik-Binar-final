/**
 * BATIK BINAR - Modern Indramayu Heritage
 * JavaScript Vanilla (Royal Blue Theme & Modern Reference Layout)
 */

document.addEventListener('DOMContentLoaded', () => {
  // 1. Splash Screen Controller
  initSplashScreen();

  // 2. Mobile Navigation & Backdrop
  initMobileNav();

  // 3. Header Scroll Glassmorphism & Active Spy
  initHeaderScroll();

  // 4. Hero Capsule Cards Interaction
  initHeroCapsules();

  // 5. Password Toggles (Login & Register)
  initPasswordToggles();

  // 6. Password Strength Meter (Register)
  initPasswordStrength();

  // 7. Form Validations (Login & Register)
  initFormValidation();

  // 8. Lightbox Modal for Gallery & Motifs
  initLightboxModal();

  // 9. Scroll Reveal (Intersection Observer)
  initScrollReveal();

  // 10. Shop Page (belanja.html)
  initShopPage();

  // 11. Gallery Page (galeri.html)
  initGalleryPage();
});

/* ==========================================================================
   1. SPLASH SCREEN
   ========================================================================== */
function initSplashScreen() {
  const splash = document.getElementById('splashScreen');
  const skipBtn = document.getElementById('splashSkipBtn');
  
  if (!splash) return;

  const dismissSplash = () => {
    splash.classList.add('hidden');
    setTimeout(() => {
      splash.style.display = 'none';
    }, 800);
  };

  // Auto dismiss after 2.4 seconds
  const timer = setTimeout(dismissSplash, 2400);

  if (skipBtn) {
    skipBtn.addEventListener('click', (e) => {
      e.preventDefault();
      clearTimeout(timer);
      dismissSplash();
    });
  }
}

/* ==========================================================================
   2. MOBILE NAVIGATION
   ========================================================================== */
function initMobileNav() {
  const menuBtn = document.getElementById('menuToggleBtn');
  const navMenu = document.getElementById('navMenu');
  const backdrop = document.getElementById('mobileBackdrop');

  if (!menuBtn || !navMenu) return;

  const toggleMenu = () => {
    const isOpen = navMenu.classList.contains('open');
    if (isOpen) {
      closeMenu();
    } else {
      openMenu();
    }
  };

  const openMenu = () => {
    menuBtn.classList.add('active');
    navMenu.classList.add('open');
    if (backdrop) backdrop.classList.add('active');
    document.body.style.overflow = 'hidden';
  };

  const closeMenu = () => {
    menuBtn.classList.remove('active');
    navMenu.classList.remove('open');
    if (backdrop) backdrop.classList.remove('active');
    document.body.style.overflow = '';
  };

  menuBtn.addEventListener('click', toggleMenu);

  if (backdrop) {
    backdrop.addEventListener('click', closeMenu);
  }

  const navLinks = navMenu.querySelectorAll('.nav-link');
  navLinks.forEach(link => {
    link.addEventListener('click', closeMenu);
  });

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && navMenu.classList.contains('open')) {
      closeMenu();
    }
  });
}


/* ==========================================================================
  3. HEADER SCROLL EFFECT & ACTIVE SPY
   ========================================================================== */
function initHeaderScroll() {
  const header = document.getElementById('siteHeader');
  if (!header) return;

  const handleScroll = () => {
    if (window.scrollY > 40) {
      header.classList.add('scrolled');
    } else {
      header.classList.remove('scrolled');
    }
  };

  window.addEventListener('scroll', handleScroll, { passive: true });
  handleScroll();

  // Active section spy: samakan berdasar id section. Mendukung href '#x',
  // 'index.html#x', URL penuh '.../#x', plus tautan halaman ('galeri.html'
  // ikut aktif saat section#galeri terlihat).
  function linkTargetsId(link, id) {
    const href = link.getAttribute('href') || '';
    const parts = href.split('#');
    if ((parts[1] || '').split('?')[0] === id) return true;
    const page = parts[0].split('?')[0];
    const base = page.substring(page.lastIndexOf('/') + 1).replace(/\.html?$/i, '');
    return base !== '' && base === id;
  }
  const sections = document.querySelectorAll('section[id]');
  const navLinks = document.querySelectorAll('.nav-menu .nav-link');

  if (sections.length > 0 && navLinks.length > 0) {
    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          const id = entry.target.getAttribute('id');
          navLinks.forEach(link => {
            if (linkTargetsId(link, id)) {
              link.classList.add('active');
            } else {
              link.classList.remove('active');
            }
          });
        }
      });
    }, { rootMargin: '-30% 0px -60% 0px' });

    sections.forEach(sec => observer.observe(sec));
  }
}

/* ==========================================================================
   4. HERO CAPSULE CARDS INTERACTION
   ========================================================================== */
function initHeroCapsules() {
  const prevBtn = document.getElementById('capsulePrevBtn');
  const nextBtn = document.getElementById('capsuleNextBtn');
  const capsules = document.querySelectorAll('.capsule-card');

  if (!prevBtn || !nextBtn || capsules.length === 0) return;

  let activeIndex = 1; // Center card (index 1) is initially tallest

  const updateCapsules = () => {
    capsules.forEach((card, idx) => {
      if (idx === activeIndex) {
        card.style.transform = 'translateY(-14px) scale(1.04)';
        card.style.borderColor = '#0B5FA5';
      } else {
        card.style.transform = 'translateY(0) scale(1)';
        card.style.borderColor = '#FFFFFF';
      }
    });
  };

  prevBtn.addEventListener('click', () => {
    activeIndex = (activeIndex - 1 + capsules.length) % capsules.length;
    updateCapsules();
  });

  nextBtn.addEventListener('click', () => {
    activeIndex = (activeIndex + 1) % capsules.length;
    updateCapsules();
  });

  capsules.forEach((card, idx) => {
    card.addEventListener('click', () => {
      activeIndex = idx;
      updateCapsules();
    });
  });
}

/* ==========================================================================
   5. PASSWORD SHOW / HIDE TOGGLE
   ========================================================================== */
function initPasswordToggles() {
  const toggleButtons = document.querySelectorAll('.password-toggle-btn');

  toggleButtons.forEach(btn => {
    btn.addEventListener('click', () => {
      const targetInputId = btn.getAttribute('data-target');
      const input = document.getElementById(targetInputId);
      if (!input) return;

      const isPassword = input.getAttribute('type') === 'password';
      input.setAttribute('type', isPassword ? 'text' : 'password');

      btn.innerHTML = isPassword ? getEyeOffIcon() : getEyeIcon();
      btn.setAttribute('aria-label', isPassword ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi');
    });
  });
}

function getEyeIcon() {
  return `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
    <circle cx="12" cy="12" r="3"></circle>
  </svg>`;
}

function getEyeOffIcon() {
  return `<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
    <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
    <line x1="1" y1="1" x2="23" y2="23"></line>
  </svg>`;
}

/* ==========================================================================
   6. PASSWORD STRENGTH METER (Register)
   ========================================================================== */
function initPasswordStrength() {
  const pwdInput = document.getElementById('regPassword');
  const meterContainer = document.getElementById('pwdStrengthMeter');
  const strengthText = document.getElementById('pwdStrengthText');
  const bars = document.querySelectorAll('.strength-bar, .glass-strength-bar');

  if (!pwdInput || !meterContainer) return;

  pwdInput.addEventListener('input', () => {
    const val = pwdInput.value;
    if (val.length === 0) {
      meterContainer.classList.remove('active');
      return;
    }
    meterContainer.classList.add('active');

    let score = 0;
    if (val.length >= 8) score++;
    if (/[A-Z]/.test(val)) score++;
    if (/[0-9]/.test(val)) score++;
    if (/[^A-Za-z0-9]/.test(val)) score++;

    bars.forEach(b => {
      b.className = b.classList.contains('glass-strength-bar') ? 'glass-strength-bar' : 'strength-bar';
    });

    if (score <= 1) {
      bars[0].classList.add('filled-weak');
      if (strengthText) strengthText.textContent = 'Kekuatan kata sandi: Lemah (min. 8 karakter & angka)';
    } else if (score === 2 || score === 3) {
      bars[0].classList.add('filled-medium');
      bars[1].classList.add('filled-medium');
      if (score === 3) bars[2].classList.add('filled-medium');
      if (strengthText) strengthText.textContent = 'Kekuatan kata sandi: Sedang';
    } else if (score >= 4) {
      bars.forEach(b => b.classList.add('filled-strong'));
      if (strengthText) strengthText.textContent = 'Kekuatan kata sandi: Sangat Kuat';
    }
  });
}

/* ==========================================================================
   7. FORM VALIDATION
   ========================================================================== */
function initFormValidation() {
  const loginForm = document.getElementById('loginForm');
  if (loginForm) {
    loginForm.addEventListener('submit', (e) => {
      e.preventDefault();
      let isValid = true;

      const email = document.getElementById('loginEmail');
      const password = document.getElementById('loginPassword');

      if (!validateEmail(email.value.trim())) {
        showFieldError(email, 'Masukkan alamat email yang valid.');
        isValid = false;
      } else {
        clearFieldError(email);
      }

      if (password.value.trim().length === 0) {
        showFieldError(password, 'Kata sandi tidak boleh kosong.');
        isValid = false;
      } else {
        clearFieldError(password);
      }

      if (isValid) {
        showToast('Berhasil masuk! Selamat datang di Batik Binar.', 'success');
        setTimeout(() => {
          window.location.href = 'index.html';
        }, 1200);
      }
    });
  }

  const regForm = document.getElementById('registerForm');
  if (regForm) {
    regForm.addEventListener('submit', (e) => {
      e.preventDefault();
      let isValid = true;

      const name = document.getElementById('regName');
      const email = document.getElementById('regEmail');
      const phone = document.getElementById('regPhone');
      const password = document.getElementById('regPassword');
      const confirmPassword = document.getElementById('regConfirmPassword');
      const terms = document.getElementById('regTerms');

      if (name.value.trim().length < 3) {
        showFieldError(name, 'Nama lengkap minimal 3 karakter.');
        isValid = false;
      } else {
        clearFieldError(name);
      }

      if (!validateEmail(email.value.trim())) {
        showFieldError(email, 'Masukkan alamat email yang valid.');
        isValid = false;
      } else {
        clearFieldError(email);
      }

      const cleanPhone = phone.value.replace(/[^0-9]/g, '');
      if (cleanPhone.length < 9) {
        showFieldError(phone, 'Nomor telepon minimal 9 digit angka.');
        isValid = false;
      } else {
        clearFieldError(phone);
      }

      if (password.value.length < 6) {
        showFieldError(password, 'Kata sandi minimal 6 karakter.');
        isValid = false;
      } else {
        clearFieldError(password);
      }

      if (confirmPassword.value !== password.value || confirmPassword.value.length === 0) {
        showFieldError(confirmPassword, 'Konfirmasi kata sandi tidak cocok.');
        isValid = false;
      } else {
        clearFieldError(confirmPassword);
      }

      if (terms && !terms.checked) {
        showToast('Harap setujui Syarat & Ketentuan terlebih dahulu.', 'error');
        isValid = false;
      }

      if (isValid) {
        showToast('Pendaftaran berhasil! Mengarahkan ke halaman login...', 'success');
        setTimeout(() => {
          window.location.href = 'login.html';
        }, 1500);
      }
    });
  }

  const inputs = document.querySelectorAll('.form-control, .glass-control');
  inputs.forEach(input => {
    input.addEventListener('input', () => clearFieldError(input));
  });
}

function validateEmail(email) {
  return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

function showFieldError(input, message) {
  input.classList.add('is-invalid');
  const group = input.closest('.form-group, .glass-form-group');
  if (group) {
    group.classList.add('has-error');
    const errEl = group.querySelector('.form-error, .glass-error-msg');
    if (errEl) errEl.textContent = message;
  }
}

function clearFieldError(input) {
  input.classList.remove('is-invalid');
  const group = input.closest('.form-group, .glass-form-group');
  if (group) group.classList.remove('has-error');
}

/* ==========================================================================
   TOAST NOTIFICATION
   ========================================================================== */
function showToast(message, type = 'info', duration = 3500) {
  let container = document.getElementById('toastContainer');
  if (!container) {
    container = document.createElement('div');
    container.id = 'toastContainer';
    container.className = 'toast-container';
    document.body.appendChild(container);
  }

  const toast = document.createElement('div');
  toast.className = `toast toast-${type}`;
  
  let iconHtml = '';
  if (type === 'success') {
    iconHtml = `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#10B981" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>`;
  } else if (type === 'error') {
    iconHtml = `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#EF4444" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>`;
  } else {
    iconHtml = `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#0B5FA5" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>`;
  }

  toast.innerHTML = `${iconHtml}<span>${message}</span>`;
  container.appendChild(toast);

  requestAnimationFrame(() => toast.classList.add('show'));

  setTimeout(() => {
    toast.classList.remove('show');
    setTimeout(() => toast.remove(), 400);
  }, duration);
}

/* ==========================================================================
   8. LIGHTBOX MODAL
   ========================================================================== */
function initLightboxModal() {
  const modal = document.getElementById('previewModal');
  const closeBtn = document.getElementById('modalCloseBtn');
  if (!modal) return;

  const closeModal = () => {
    modal.classList.remove('active');
    document.body.style.overflow = '';
  };

  if (closeBtn) closeBtn.addEventListener('click', closeModal);
  modal.addEventListener('click', (e) => {
    if (e.target === modal) closeModal();
  });

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && modal.classList.contains('active')) closeModal();
  });
}

// Global modal opener for motif cards & gallery
window.openMotifDetail = function(title, tag, imgSrc, desc) {
  const modal = document.getElementById('previewModal');
  if (!modal) return;

  const modalImg = document.getElementById('modalImage');
  const modalTag = document.getElementById('modalTag');
  const modalTitle = document.getElementById('modalTitle');
  const modalDesc = document.getElementById('modalDesc');

  if (modalImg) modalImg.src = imgSrc;
  if (modalTag) modalTag.textContent = tag;
  if (modalTitle) modalTitle.textContent = title;
  if (modalDesc) modalDesc.textContent = desc;

  modal.classList.add('active');
  document.body.style.overflow = 'hidden';
};

/* ==========================================================================
   9. SCROLL REVEAL
   ========================================================================== */
function initScrollReveal() {
  const elements = document.querySelectorAll('.reveal');
  if (elements.length === 0) return;

  const observer = new IntersectionObserver((entries, obs) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('active');
        obs.unobserve(entry.target);
      }
    });
  }, { threshold: 0.12 });

  elements.forEach(el => observer.observe(el));
}

/* ==========================================================================
   10. SHOP PAGE (belanja.html) - 50 Products
   ========================================================================== */
const SHOP_PRODUCTS = [
  // === KAIN PANJANG (10) ===
  { id:1, name:"Kain Iwak Etong Royal Blue", category:"kain", categoryLabel:"Kain Panjang", price:1850000, img:"assets/img/pin-1dc76be6.jpg", teknik:"Tulis Complongan", bahan:"Katun Primissima", ukuran:"250 × 110 cm", desc:"Kain panjang bermotif ikan etong khas laut Jawa Utara. Melambangkan kelimpahan rezeki bahari dan rasa syukur nelayan Dermayon. Dikerjakan dengan teknik complongan selama 40 hari." },
  { id:2, name:"Kain Kapal Kandas Klasik", category:"kain", categoryLabel:"Kain Panjang", price:2100000, img:"assets/img/pin-8062513c.jpg", teknik:"Tulis Halus", bahan:"Katun Primissima", ukuran:"250 × 110 cm", desc:"Menceritakan kapal dagang kuno yang bersandar di muara Cimanuk. Simbol keteguhan hati dan pantang menyerah mengarungi samudra kehidupan." },
  { id:3, name:"Kain Merak Ngibing Premium", category:"kain", categoryLabel:"Kain Panjang", price:2750000, img:"assets/img/pin-8f842281.jpg", teknik:"Tulis Sutra", bahan:"Sutra ATBM", ukuran:"250 × 110 cm", desc:"Visualisasi tarian merak dengan sayap mengembang penuh keanggunan. Dibuat di atas sutra halus ATBM dengan pewarnaan alami dari daun mangga dan kayu mahoni." },
  { id:4, name:"Kain Sawat Kembang Pesisir", category:"kain", categoryLabel:"Kain Panjang", price:1650000, img:"assets/img/pin-9fff79bc.jpg", teknik:"Tulis Complongan", bahan:"Katun Primissima", ukuran:"250 × 110 cm", desc:"Motif bunga sawat yang mekar di tepi pantai Pantura. Menggambarkan keindahan alam pesisir Indramayu dan kesuburan tanah Dermayon." },
  { id:5, name:"Kain Mega Mendung Dermayon", category:"kain", categoryLabel:"Kain Panjang", price:1950000, img:"assets/img/pin-ed1bc9ab.jpg", teknik:"Tulis Cap", bahan:"Katun Primissima", ukuran:"250 × 110 cm", desc:"Adaptasi motif mega mendung versi pesisir Dermayon dengan gradasi biru laut yang khas dan sentuhan rintik complongan." },
  { id:6, name:"Kain Udang Windu Bahari", category:"kain", categoryLabel:"Kain Panjang", price:1750000, img:"assets/img/pin-7b7da76e.jpg", teknik:"Tulis Halus", bahan:"Katun Prima", ukuran:"250 × 110 cm", desc:"Motif udang windu melambangkan kemakmuran nelayan tambak di pesisir Indramayu. Dikerjakan dengan ketelitian tinggi oleh empu pembatik Paoman." },
  { id:7, name:"Kain Singa Barong Paoman", category:"kain", categoryLabel:"Kain Panjang", price:3200000, img:"assets/img/pin-409fafab.jpg", teknik:"Tulis Halus", bahan:"Sutra ATBM", ukuran:"250 × 110 cm", desc:"Motif singa barong yang gagah melambangkan keberanian dan kekuatan. Masterpiece dari sentra Paoman dengan pewarnaan alami eksklusif." },
  { id:8, name:"Kain Buketan Flora Laut", category:"kain", categoryLabel:"Kain Panjang", price:1550000, img:"assets/img/pin-b4def898.jpg", teknik:"Tulis Cap", bahan:"Katun Prima", ukuran:"250 × 110 cm", desc:"Rangkaian bunga dan flora laut yang disusun indah. Perpaduan warna biru pesisir dan hijau daun mangrove yang menyejukkan mata." },
  { id:9, name:"Kain Lokcan Burung Phoenix", category:"kain", categoryLabel:"Kain Panjang", price:2850000, img:"assets/img/pin-953ddeb8.jpg", teknik:"Tulis Halus", bahan:"Sutra Halus", ukuran:"250 × 110 cm", desc:"Motif burung phoenix atau lokcan yang melambangkan kebangkitan dan kemakmuran. Dipengaruhi oleh budaya Tionghoa pesisir utara Jawa." },
  { id:10, name:"Kain Ganggeng Rumput Laut", category:"kain", categoryLabel:"Kain Panjang", price:1450000, img:"assets/img/pin-392b39f3.jpg", teknik:"Cap Complongan", bahan:"Katun Prima", ukuran:"250 × 110 cm", desc:"Motif rumput laut atau ganggeng yang bergoyang di dasar laut. Melambangkan kelenturan dan ketahanan hidup masyarakat pesisir." },

  // === SELENDANG (10) ===
  { id:11, name:"Selendang Iwak Etong Mini", category:"selendang", categoryLabel:"Selendang", price:850000, img:"assets/img/pin-1dc76be6.jpg", teknik:"Tulis Halus", bahan:"Katun Primissima", ukuran:"200 × 50 cm", desc:"Selendang elegan dengan motif iwak etong dalam ukuran compact. Cocok untuk aksen busana formal maupun casual." },
  { id:12, name:"Selendang Merak Biru Pesisir", category:"selendang", categoryLabel:"Selendang", price:950000, img:"assets/img/pin-8f842281.jpg", teknik:"Tulis Complongan", bahan:"Katun Prima", ukuran:"200 × 50 cm", desc:"Selendang bermotif merak ngibing dengan dominasi warna biru pesisir. Rintik complongan halus menambah dimensi tekstur yang unik." },
  { id:13, name:"Selendang Sawat Kembang", category:"selendang", categoryLabel:"Selendang", price:750000, img:"assets/img/pin-9fff79bc.jpg", teknik:"Cap Halus", bahan:"Katun Prima", ukuran:"200 × 50 cm", desc:"Selendang ringan dengan motif sawat kembang yang feminin. Pewarnaan natural menghasilkan gradasi biru yang lembut." },
  { id:14, name:"Selendang Sutra Kapal Kandas", category:"selendang", categoryLabel:"Selendang", price:1350000, img:"assets/img/pin-8062513c.jpg", teknik:"Tulis Halus", bahan:"Sutra ATBM", ukuran:"200 × 50 cm", desc:"Selendang sutra mewah dengan motif kapal kandas. Bahan sutra ATBM memberikan kilau alami yang anggun." },
  { id:15, name:"Selendang Mega Mendung Scarf", category:"selendang", categoryLabel:"Selendang", price:680000, img:"assets/img/pin-ed1bc9ab.jpg", teknik:"Cap", bahan:"Katun Voile", ukuran:"180 × 45 cm", desc:"Scarf batik ringan dengan motif mega mendung versi Dermayon. Material voile yang breathable untuk gaya sehari-hari." },
  { id:16, name:"Selendang Buketan Pastel", category:"selendang", categoryLabel:"Selendang", price:720000, img:"assets/img/pin-7b7da76e.jpg", teknik:"Cap Halus", bahan:"Katun Prima", ukuran:"200 × 50 cm", desc:"Selendang dengan motif buketan bunga dalam palet warna pastel biru. Cocok untuk hijab atau aksesori busana modern." },
  { id:17, name:"Selendang Udang Windu", category:"selendang", categoryLabel:"Selendang", price:800000, img:"assets/img/pin-409fafab.jpg", teknik:"Tulis Cap", bahan:"Katun Prima", ukuran:"200 × 50 cm", desc:"Selendang bermotif udang windu dengan detail halus. Teknik tulis cap menghasilkan garis yang tegas namun tetap artistik." },
  { id:18, name:"Selendang Lokcan Elegan", category:"selendang", categoryLabel:"Selendang", price:1200000, img:"assets/img/pin-953ddeb8.jpg", teknik:"Tulis Halus", bahan:"Sutra Halus", ukuran:"200 × 50 cm", desc:"Selendang sutra dengan motif lokcan burung phoenix. Keanggunan motif Tionghoa-Jawa yang menyatu harmonis." },
  { id:19, name:"Selendang Ganggeng Hijau Laut", category:"selendang", categoryLabel:"Selendang", price:650000, img:"assets/img/pin-392b39f3.jpg", teknik:"Cap", bahan:"Katun Voile", ukuran:"180 × 45 cm", desc:"Selendang ringan dengan motif ganggeng rumput laut. Perpaduan warna hijau laut dan biru yang menenangkan." },
  { id:20, name:"Selendang Singa Barong Mini", category:"selendang", categoryLabel:"Selendang", price:1100000, img:"assets/img/pin-b4def898.jpg", teknik:"Tulis Halus", bahan:"Katun Primissima", ukuran:"200 × 50 cm", desc:"Selendang premium dengan motif singa barong dalam ukuran mini. Simbol keberanian yang cocok untuk acara formal." },

  // === BUSANA PRIA (10) ===
  { id:21, name:"Kemeja Slim Iwak Etong", category:"pria", categoryLabel:"Busana Pria", price:1250000, img:"assets/img/pin-1dc76be6.jpg", teknik:"Tulis Cap", bahan:"Katun Primissima", ukuran:"S / M / L / XL", desc:"Kemeja slim fit dengan motif iwak etong untuk pria modern. Potongan ergonomis dengan jahitan presisi untuk kenyamanan sepanjang hari." },
  { id:22, name:"Kemeja Formal Kapal Kandas", category:"pria", categoryLabel:"Busana Pria", price:1450000, img:"assets/img/pin-8062513c.jpg", teknik:"Tulis Halus", bahan:"Katun Primissima", ukuran:"S / M / L / XL / XXL", desc:"Kemeja formal dengan motif kapal kandas klasik. Desain minimalis modern yang cocok untuk acara kantor dan pesta." },
  { id:23, name:"Kemeja Lengan Pendek Merak", category:"pria", categoryLabel:"Busana Pria", price:980000, img:"assets/img/pin-8f842281.jpg", teknik:"Cap", bahan:"Katun Prima", ukuran:"S / M / L / XL", desc:"Kemeja lengan pendek casual dengan motif merak ngibing. Cocok untuk acara santai dan semi-formal." },
  { id:24, name:"Kemeja Batik Mega Mendung", category:"pria", categoryLabel:"Busana Pria", price:1150000, img:"assets/img/pin-ed1bc9ab.jpg", teknik:"Tulis Cap", bahan:"Katun Primissima", ukuran:"S / M / L / XL", desc:"Kemeja batik elegan dengan motif mega mendung Dermayon. Gradasi biru yang memikat untuk tampilan profesional." },
  { id:25, name:"Kemeja Premium Singa Barong", category:"pria", categoryLabel:"Busana Pria", price:1850000, img:"assets/img/pin-409fafab.jpg", teknik:"Tulis Halus", bahan:"Sutra ATBM", ukuran:"S / M / L / XL", desc:"Kemeja sutra premium dengan motif singa barong Paoman. Masterpiece yang cocok untuk acara istimewa dan pesta mewah." },
  { id:26, name:"Kemeja Kasual Buketan", category:"pria", categoryLabel:"Busana Pria", price:880000, img:"assets/img/pin-b4def898.jpg", teknik:"Cap", bahan:"Katun Prima", ukuran:"S / M / L / XL", desc:"Kemeja kasual dengan motif buketan flora. Desain relaxed fit yang nyaman untuk kegiatan sehari-hari." },
  { id:27, name:"Kemeja Batik Lokcan Pria", category:"pria", categoryLabel:"Busana Pria", price:1550000, img:"assets/img/pin-953ddeb8.jpg", teknik:"Tulis Halus", bahan:"Katun Primissima", ukuran:"S / M / L / XL / XXL", desc:"Kemeja formal dengan motif lokcan burung phoenix. Keanggunan budaya Tionghoa-Jawa dalam balutan modern." },
  { id:28, name:"Kemeja Sawat Kembang Pria", category:"pria", categoryLabel:"Busana Pria", price:950000, img:"assets/img/pin-9fff79bc.jpg", teknik:"Cap Halus", bahan:"Katun Prima", ukuran:"S / M / L / XL", desc:"Kemeja batik dengan motif sawat kembang yang maskulin. Sentuhan bunga pesisir dalam gaya yang bold dan modern." },
  { id:29, name:"Kemeja Ganggeng Slim Fit", category:"pria", categoryLabel:"Busana Pria", price:920000, img:"assets/img/pin-392b39f3.jpg", teknik:"Cap", bahan:"Katun Prima", ukuran:"S / M / L / XL", desc:"Kemeja slim fit dengan motif ganggeng rumput laut. Nuansa laut yang kalem untuk gaya sehari-hari." },
  { id:30, name:"Kemeja Udang Windu Executive", category:"pria", categoryLabel:"Busana Pria", price:1350000, img:"assets/img/pin-7b7da76e.jpg", teknik:"Tulis Cap", bahan:"Katun Primissima", ukuran:"S / M / L / XL / XXL", desc:"Kemeja executive dengan motif udang windu. Potongan regular fit yang nyaman untuk eksekutif modern." },

  // === BUSANA WANITA (10) ===
  { id:31, name:"Dress Merak Ngibing Elegan", category:"wanita", categoryLabel:"Busana Wanita", price:2250000, img:"assets/img/pin-8f842281.jpg", teknik:"Tulis Halus", bahan:"Sutra ATBM", ukuran:"S / M / L / XL", desc:"Dress elegan dengan motif merak ngibing di atas sutra ATBM. Siluet A-line yang anggun untuk acara gala dan pesta." },
  { id:32, name:"Blouse Iwak Etong Modern", category:"wanita", categoryLabel:"Busana Wanita", price:1150000, img:"assets/img/pin-1dc76be6.jpg", teknik:"Tulis Cap", bahan:"Katun Primissima", ukuran:"S / M / L / XL", desc:"Blouse modern dengan motif iwak etong. Potongan peplum yang feminin dengan detail ruffles di pergelangan tangan." },
  { id:33, name:"Tunik Mega Mendung Dermayon", category:"wanita", categoryLabel:"Busana Wanita", price:1350000, img:"assets/img/pin-ed1bc9ab.jpg", teknik:"Tulis Cap", bahan:"Katun Primissima", ukuran:"S / M / L / XL / XXL", desc:"Tunik longgar bermotif mega mendung Dermayon. Cocok untuk dipadupadankan dengan celana atau rok untuk gaya modest." },
  { id:34, name:"Rok Batik Kapal Kandas", category:"wanita", categoryLabel:"Busana Wanita", price:980000, img:"assets/img/pin-8062513c.jpg", teknik:"Cap Halus", bahan:"Katun Prima", ukuran:"S / M / L / XL", desc:"Rok A-line dengan motif kapal kandas. Potongan midi yang versatile untuk tampilan kasual hingga semi-formal." },
  { id:35, name:"Dress Cocktail Sawat Kembang", category:"wanita", categoryLabel:"Busana Wanita", price:1850000, img:"assets/img/pin-9fff79bc.jpg", teknik:"Tulis Halus", bahan:"Katun Primissima", ukuran:"S / M / L", desc:"Dress cocktail dengan motif sawat kembang. Desain off-shoulder yang menawan untuk pesta malam." },
  { id:36, name:"Blouse Buketan Flora Wanita", category:"wanita", categoryLabel:"Busana Wanita", price:920000, img:"assets/img/pin-b4def898.jpg", teknik:"Cap", bahan:"Katun Prima", ukuran:"S / M / L / XL", desc:"Blouse casual dengan motif buketan flora pesisir. Potongan relaxed yang nyaman untuk kegiatan sehari-hari." },
  { id:37, name:"Outer Batik Lokcan Wanita", category:"wanita", categoryLabel:"Busana Wanita", price:1650000, img:"assets/img/pin-953ddeb8.jpg", teknik:"Tulis Halus", bahan:"Katun Primissima", ukuran:"S / M / L / XL", desc:"Outer atau cardigan batik dengan motif lokcan. Layering piece yang sempurna untuk gaya berlapis modern." },
  { id:38, name:"Kaftan Singa Barong Premium", category:"wanita", categoryLabel:"Busana Wanita", price:2450000, img:"assets/img/pin-409fafab.jpg", teknik:"Tulis Halus", bahan:"Sutra ATBM", ukuran:"Free Size", desc:"Kaftan sutra premium dengan motif singa barong. Busana mewah untuk acara istimewa dengan draping yang mengalir indah." },
  { id:39, name:"Cullote Ganggeng Kasual", category:"wanita", categoryLabel:"Busana Wanita", price:780000, img:"assets/img/pin-392b39f3.jpg", teknik:"Cap", bahan:"Katun Prima", ukuran:"S / M / L / XL", desc:"Cullote atau celana kulot bermotif ganggeng rumput laut. Nyaman dan stylish untuk tampilan sehari-hari." },
  { id:40, name:"Kebaya Modern Udang Windu", category:"wanita", categoryLabel:"Busana Wanita", price:1750000, img:"assets/img/pin-7b7da76e.jpg", teknik:"Tulis Cap", bahan:"Katun Primissima", ukuran:"S / M / L / XL", desc:"Kebaya modern dengan sentuhan motif udang windu. Perpaduan tradisi dan modernitas untuk acara adat dan resmi." },

  // === AKSESORIS (10) ===
  { id:41, name:"Tote Bag Batik Iwak Etong", category:"aksesoris", categoryLabel:"Aksesoris", price:450000, img:"assets/img/pin-1dc76be6.jpg", teknik:"Cap", bahan:"Katun Canvas + Batik", ukuran:"40 × 35 × 10 cm", desc:"Tote bag premium dengan panel batik motif iwak etong. Tas serbaguna dengan lapisan dalam kanvas tebal." },
  { id:42, name:"Clutch Batik Merak Ngibing", category:"aksesoris", categoryLabel:"Aksesoris", price:380000, img:"assets/img/pin-8f842281.jpg", teknik:"Tulis", bahan:"Batik + Kulit Sintetis", ukuran:"28 × 16 cm", desc:"Clutch elegan dengan panel batik motif merak ngibing. Dilengkapi strap rantai gold untuk tampilan mewah." },
  { id:43, name:"Masker Batik Premium Set", category:"aksesoris", categoryLabel:"Aksesoris", price:180000, img:"assets/img/pin-9fff79bc.jpg", teknik:"Cap", bahan:"Katun Prima 3-Ply", ukuran:"Free Size (set 3)", desc:"Set 3 masker batik premium dengan motif berbeda. Bahan katun 3-ply yang breathable dan bisa dicuci ulang." },
  { id:44, name:"Dompet Batik Kapal Kandas", category:"aksesoris", categoryLabel:"Aksesoris", price:320000, img:"assets/img/pin-8062513c.jpg", teknik:"Cap", bahan:"Batik + Kulit Sintetis", ukuran:"20 × 10 cm", desc:"Dompet panjang dengan panel batik motif kapal kandas. Banyak sekat untuk kartu dan uang kertas." },
  { id:45, name:"Kipas Batik Tulis Mewah", category:"aksesoris", categoryLabel:"Aksesoris", price:250000, img:"assets/img/pin-7b7da76e.jpg", teknik:"Tulis", bahan:"Kain Batik + Bambu", ukuran:"23 cm (buka)", desc:"Kipas lipat mewah dengan kain batik tulis motif pesisir. Rangka bambu yang ringan dan kokoh." },
  { id:46, name:"Ikat Kepala Batik Unisex", category:"aksesoris", categoryLabel:"Aksesoris", price:150000, img:"assets/img/pin-ed1bc9ab.jpg", teknik:"Cap", bahan:"Katun Prima", ukuran:"Free Size", desc:"Ikat kepala atau bandana batik unisex dengan motif mega mendung. Aksesori stylish untuk gaya streetwear." },
  { id:47, name:"Syal Batik Buketan Lebar", category:"aksesoris", categoryLabel:"Aksesoris", price:550000, img:"assets/img/pin-b4def898.jpg", teknik:"Cap Halus", bahan:"Katun Voile", ukuran:"180 × 70 cm", desc:"Syal lebar bermotif buketan flora pesisir. Material voile ringan yang cocok untuk traveling dan outdoor." },
  { id:48, name:"Pouch Batik Serbaguna", category:"aksesoris", categoryLabel:"Aksesoris", price:220000, img:"assets/img/pin-953ddeb8.jpg", teknik:"Cap", bahan:"Batik + Canvas", ukuran:"25 × 15 cm", desc:"Pouch serbaguna dengan panel batik motif lokcan. Cocok untuk menyimpan kosmetik, gadget, atau alat tulis." },
  { id:49, name:"Gantungan Kunci Batik Set", category:"aksesoris", categoryLabel:"Aksesoris", price:95000, img:"assets/img/pin-392b39f3.jpg", teknik:"Cap", bahan:"Kain Batik + Metal", ukuran:"8 × 4 cm (set 3)", desc:"Set 3 gantungan kunci dengan potongan kain batik berbeda motif. Souvenir khas Indramayu yang unik." },
  { id:50, name:"Bantal Sofa Batik Complongan", category:"aksesoris", categoryLabel:"Aksesoris", price:350000, img:"assets/img/pin-409fafab.jpg", teknik:"Cap Complongan", bahan:"Katun + Isi Dakron", ukuran:"45 × 45 cm", desc:"Bantal sofa dekoratif dengan sarung batik motif complongan. Menambah sentuhan budaya pada interior rumah Anda." }
];

function initShopPage() {
  const shopGrid = document.getElementById('shopGrid');
  if (!shopGrid) return; // Not on shop page

  const searchInput = document.getElementById('shopSearchInput');
  const searchCount = document.getElementById('shopSearchCount');
  const filterPills = document.getElementById('shopFilterPills');
  const shopEmpty = document.getElementById('shopEmpty');

  let currentFilter = 'semua';
  let currentSearch = '';

  // Render products
  function renderProducts() {
    let filtered = SHOP_PRODUCTS.filter(p => {
      const matchFilter = currentFilter === 'semua' || p.category === currentFilter;
      const matchSearch = currentSearch === '' ||
        p.name.toLowerCase().includes(currentSearch) ||
        p.categoryLabel.toLowerCase().includes(currentSearch) ||
        p.teknik.toLowerCase().includes(currentSearch) ||
        p.bahan.toLowerCase().includes(currentSearch) ||
        p.desc.toLowerCase().includes(currentSearch);
      return matchFilter && matchSearch;
    });

    if (searchCount) searchCount.textContent = `${filtered.length} produk`;

    if (filtered.length === 0) {
      shopGrid.style.display = 'none';
      if (shopEmpty) shopEmpty.style.display = 'block';
      return;
    }

    shopGrid.style.display = 'grid';
    if (shopEmpty) shopEmpty.style.display = 'none';

    shopGrid.innerHTML = filtered.map(p => `
      <a href="#" class="shop-product-card" onclick="openShopDetail(${p.id}); return false;">
        <div class="shop-product-img">
          <img src="${p.img}" alt="${p.name}" width="800" height="600" loading="lazy" decoding="async">
          <span class="shop-product-badge">${p.categoryLabel}</span>
        </div>
        <div class="shop-product-body">
          <div class="shop-product-category">${p.categoryLabel}</div>
          <h3 class="shop-product-name">${p.name}</h3>
          <div class="shop-product-price">
            <span class="price-currency">Rp</span> ${p.price.toLocaleString('id-ID')}
          </div>
          <div class="shop-product-footer">
            <span class="shop-product-teknik">${p.teknik}</span>
            <span class="shop-product-detail-btn" aria-hidden="true">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                <line x1="5" y1="12" x2="19" y2="12"></line>
                <polyline points="12 5 19 12 12 19"></polyline>
              </svg>
            </span>
          </div>
        </div>
      </a>
    `).join('');
  }

  // Search
  if (searchInput) {
    searchInput.addEventListener('input', () => {
      currentSearch = searchInput.value.trim().toLowerCase();
      renderProducts();
    });
  }

  // Filter
  if (filterPills) {
    filterPills.addEventListener('click', (e) => {
      const btn = e.target.closest('.shop-filter-btn');
      if (!btn) return;
      filterPills.querySelectorAll('.shop-filter-btn').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      currentFilter = btn.getAttribute('data-filter');
      renderProducts();
    });
  }

  // Initial render
  renderProducts();

  // Modal
  const modal = document.getElementById('shopModal');
  const closeBtn = document.getElementById('shopModalClose');

  if (modal && closeBtn) {
    closeBtn.addEventListener('click', () => {
      modal.classList.remove('active');
      document.body.style.overflow = '';
    });
    modal.addEventListener('click', (e) => {
      if (e.target === modal) {
        modal.classList.remove('active');
        document.body.style.overflow = '';
      }
    });
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && modal.classList.contains('active')) {
        modal.classList.remove('active');
        document.body.style.overflow = '';
      }
    });
  }
}

// Global function to open shop detail modal
window.openShopDetail = function(productId) {
  const product = SHOP_PRODUCTS.find(p => p.id === productId);
  if (!product) return;

  const modal = document.getElementById('shopModal');
  if (!modal) return;

  const img = document.getElementById('shopModalImg');
  const category = document.getElementById('shopModalCategory');
  const title = document.getElementById('shopModalTitle');
  const price = document.getElementById('shopModalPrice');
  const desc = document.getElementById('shopModalDesc');
  const bahan = document.getElementById('shopModalBahan');
  const teknik = document.getElementById('shopModalTeknik');
  const ukuran = document.getElementById('shopModalUkuran');
  const waBtn = document.getElementById('shopModalWA');

  if (img) img.src = product.img;
  if (category) category.textContent = product.categoryLabel;
  if (title) title.textContent = product.name;
  if (price) price.textContent = `Rp ${product.price.toLocaleString('id-ID')}`;
  if (desc) desc.textContent = product.desc;
  if (bahan) bahan.textContent = product.bahan;
  if (teknik) teknik.textContent = product.teknik;
  if (ukuran) ukuran.textContent = product.ukuran;

  if (waBtn) {
    const waText = encodeURIComponent(`Halo Batik Binar! Saya tertarik dengan produk:\n\n📦 ${product.name}\n💰 Rp ${product.price.toLocaleString('id-ID')}\n📏 Ukuran: ${product.ukuran}\n\nMohon informasi ketersediaan dan cara pemesanannya. Terima kasih!`);
    waBtn.href = `https://wa.me/6282123456789?text=${waText}`;
  }

  modal.classList.add('active');
  document.body.style.overflow = 'hidden';
};

/* ==========================================================================
   11. GALLERY PAGE (galeri.html) - Filters & Detail Modal
   ========================================================================== */
const GALLERY_DATA = [
  { title:"Motif Iwak Etong", category:"motif", tag:"Motif Batik", img:"assets/img/pin-1dc76be6.jpg", desc:"Motif ikan etong khas laut Jawa Utara, melambangkan kelimpahan rezeki bahari dan rasa syukur nelayan Dermayon." },
  { title:"Motif Kapal Kandas", category:"motif", tag:"Motif Batik", img:"assets/img/pin-8062513c.jpg", desc:"Kapal dagang kuno bersandar di muara Cimanuk, simbol keteguhan hati mengarungi samudra." },
  { title:"Motif Merak Ngibing", category:"motif", tag:"Motif Batik", img:"assets/img/pin-8f842281.jpg", desc:"Tarian merak dengan sayap mengembang, memancarkan keanggunan dan sukacita." },
  { title:"Rintik Complongan", category:"detail", tag:"Detail Tekstur", img:"assets/img/pin-9fff79bc.jpg", desc:"Kerapian lubang jarum mikro complongan yang menembus serat kain dan memunculkan rintik magis." },
  { title:"Motif Canting Tulis", category:"motif", tag:"Motif Batik", img:"assets/img/pin-ed1bc9ab.jpg", desc:"Detail canting tulis khas Paoman yang menghasilkan garis-garis halus penuh presisi." },
  { title:"Kain Sutra Pesisir", category:"motif", tag:"Motif Batik", img:"assets/img/pin-7b7da76e.jpg", desc:"Keindahan kain sutra dengan motif pesisir yang berkilau alami di bawah cahaya." },
  { title:"Moda Modern Batik", category:"busana", tag:"Busana Modern", img:"assets/img/pin-1da9a95d.jpg", desc:"Busana modern yang memadukan motif batik tradisional dengan siluet kontemporer." },
  { title:"Detail Canting & Kain", category:"proses", tag:"Proses Pembuatan", img:"assets/img/pin-409fafab.jpg", desc:"Detail canting tembaga dan kain mori dalam proses pembatikan tulis tangan." },
  { title:"Maestro Canting Paoman", category:"proses", tag:"Proses Pembuatan", img:"assets/img/pin-b4def898.jpg", desc:"Jemari lentik maestro pembatik melukis lilin malam dengan konsentrasi penuh." },
  { title:"Teknik Complongan Jarum", category:"proses", tag:"Proses Pembuatan", img:"assets/img/pin-392b39f3.jpg", desc:"Teknik khas Indramayu menusuk ribuan jarum mikro untuk efek rintik cecek magis." },
  { title:"Molani Pola Sketsa", category:"proses", tag:"Proses Pembuatan", img:"assets/img/pin-953ddeb8.jpg", desc:"Langkah awal menggambar pola motif batik di atas kain mori dengan pensil halus." },
  { title:"Nyanting Lilin Malam", category:"proses", tag:"Proses Pembuatan", img:"assets/img/pin-813a9043.jpg", desc:"Proses menorehkan lilin malam panas menggunakan canting tembaga khas Paoman." },
  { title:"Pewarnaan Alami Pesisir", category:"proses", tag:"Proses Pembuatan", img:"assets/img/pin-64cc4ca8.jpg", desc:"Pencelupan kain ke dalam ekstrak alami kayu mahoni dan daun mangga untuk warna bahari." },
  { title:"Nglorod Lilin Malam", category:"proses", tag:"Proses Pembuatan", img:"assets/img/pin-eb416f44.jpg", desc:"Merebus kain untuk meluruhkan lilin malam hingga motif complongan muncul utuh." },
  { title:"Ibu Pembatik Paoman", category:"sentra", tag:"Sentra Paoman", img:"assets/img/pin-813a9043.jpg", desc:"Para ibu pembatik di sentra Paoman yang melestarikan warisan budaya secara turun temurun." },
  { title:"Kain Batik Siap Pakai", category:"motif", tag:"Motif Batik", img:"assets/img/gst-tytask.jpg", desc:"Kain batik tulis yang telah melewati seluruh proses dan siap dijahit menjadi busana." },
  { title:"Elegansi Busana Sutra", category:"busana", tag:"Busana Modern", img:"assets/img/gst-sio8uh.jpg", desc:"Gaun modern berbahan sutra ATBM dengan corak motif Iwak Etong bernuansa Royal Blue." },
  { title:"Siluet Busana Wanita", category:"busana", tag:"Busana Modern", img:"assets/img/gst-tzpwdi.jpg", desc:"Kemeja batik tulis wanita bergaya minimalis formal dengan potongan modern." },
  { title:"Gradasi Warna Indigo", category:"detail", tag:"Detail Tekstur", img:"assets/img/pin-1dc76be6.jpg", desc:"Detail gradasi warna indigo alami yang dihasilkan dari proses pencelupan bertahap." },
  { title:"Tekstur Lilin Crackling", category:"detail", tag:"Detail Tekstur", img:"assets/img/pin-8062513c.jpg", desc:"Efek crackling atau retakan lilin malam yang memberikan karakter unik pada kain batik." },
  { title:"Close-up Canting Tembaga", category:"detail", tag:"Detail Tekstur", img:"assets/img/pin-409fafab.jpg", desc:"Detail ujung canting tembaga yang digunakan untuk menorehkan lilin malam pada kain." },
  { title:"Serat Katun Primissima", category:"detail", tag:"Detail Tekstur", img:"assets/img/pin-b4def898.jpg", desc:"Close-up serat katun primissima yang halus, bahan dasar terbaik untuk batik tulis." },
  { title:"Ornamen Pinggiran Kain", category:"detail", tag:"Detail Tekstur", img:"assets/img/pin-392b39f3.jpg", desc:"Detail ornamen pinggiran kain batik yang dikerjakan dengan presisi tinggi." },
  { title:"Motif Flora Mangrove", category:"motif", tag:"Motif Batik", img:"assets/img/pin-953ddeb8.jpg", desc:"Motif flora mangrove yang terinspirasi dari hutan bakau di pesisir Indramayu." },
  { title:"Kemeja Batik Formal", category:"busana", tag:"Busana Modern", img:"assets/img/pin-ed1bc9ab.jpg", desc:"Kemeja batik formal pria dengan motif mega mendung untuk tampilan profesional." },
  { title:"Dress Batik Cocktail", category:"busana", tag:"Busana Modern", img:"assets/img/pin-8f842281.jpg", desc:"Dress cocktail berbahan batik sutra untuk acara malam yang elegan." },
  { title:"Blazer Batik Modern", category:"busana", tag:"Busana Modern", img:"assets/img/pin-7b7da76e.jpg", desc:"Blazer modern dengan panel batik motif pesisir, cocok untuk gaya smart casual." },
  { title:"Workshop Pembatik Paoman", category:"sentra", tag:"Sentra Paoman", img:"assets/img/pin-813a9043.jpg", desc:"Suasana workshop pembatik di sentra Paoman, Indramayu, tempat lahirnya karya seni batik." },
  { title:"Showroom Batik Binar", category:"sentra", tag:"Sentra Paoman", img:"assets/img/pin-64cc4ca8.jpg", desc:"Showroom Batik Binar yang menampilkan koleksi terbaik dari para empu pembatik Paoman." },
  { title:"Pameran Batik Indramayu", category:"sentra", tag:"Sentra Paoman", img:"assets/img/pin-eb416f44.jpg", desc:"Pameran batik Indramayu yang memperkenalkan kekayaan motif pesisir ke dunia." },
  { title:"Motif Udang Windu", category:"motif", tag:"Motif Batik", img:"assets/img/pin-7b7da76e.jpg", desc:"Motif udang windu melambangkan kemakmuran nelayan tambak di pesisir Indramayu." },
  { title:"Proses Penjemuran Kain", category:"proses", tag:"Proses Pembuatan", img:"assets/img/pin-953ddeb8.jpg", desc:"Proses penjemuran kain batik di bawah sinar matahari untuk mengunci warna alami." },
  { title:"Gaun Batik Runway", category:"busana", tag:"Busana Modern", img:"assets/img/pin-1da9a95d.jpg", desc:"Gaun batik yang tampil di runway fashion show, membuktikan batik mampu go international." },
  { title:"Kebaya Batik Modern", category:"busana", tag:"Busana Modern", img:"assets/img/gst-sio8uh.jpg", desc:"Kebaya modern yang dipadukan dengan kain batik tulis untuk acara resmi dan pernikahan." },
  { title:"Detail Cecek Sawut", category:"detail", tag:"Detail Tekstur", img:"assets/img/pin-9fff79bc.jpg", desc:"Detail cecek sawut yang halus, salah satu isen-isen khas batik Indramayu." },
  { title:"Motif Singa Barong", category:"motif", tag:"Motif Batik", img:"assets/img/pin-409fafab.jpg", desc:"Motif singa barong yang gagah, simbol keberanian dan kekuatan dalam budaya Dermayon." },
  { title:"Outer Batik Kasual", category:"busana", tag:"Busana Modern", img:"assets/img/pin-b4def898.jpg", desc:"Outer batik kasual yang bisa dipadukan dengan jeans untuk gaya sehari-hari." },
  { title:"Komunitas Pembatik Muda", category:"sentra", tag:"Sentra Paoman", img:"assets/img/pin-813a9043.jpg", desc:"Komunitas pembatik muda Indramayu yang meneruskan tradisi dengan inovasi modern." },
  { title:"Pelatihan Canting Pemula", category:"sentra", tag:"Sentra Paoman", img:"assets/img/pin-813a9043.jpg", desc:"Pelatihan penggunaan canting bagi pemula di sentra batik Paoman." },
  { title:"Pewarnaan Alam Tradisional", category:"proses", tag:"Proses Pembuatan", img:"assets/img/pin-64cc4ca8.jpg", desc:"Proses pewarnaan alam tradisional menggunakan bahan-bahan dari alam sekitar pesisir." },
  { title:"Motif Ganggeng Laut", category:"motif", tag:"Motif Batik", img:"assets/img/pin-392b39f3.jpg", desc:"Motif ganggeng atau rumput laut yang bergoyang di dasar laut, simbol kelenturan hidup." },
  { title:"Tekstur Sutra ATBM", category:"detail", tag:"Detail Tekstur", img:"assets/img/pin-8f842281.jpg", desc:"Tekstur sutra ATBM yang berkilau alami, bahan premium untuk batik tulis eksklusif." },
  { title:"Rok Batik A-Line", category:"busana", tag:"Busana Modern", img:"assets/img/gst-tzpwdi.jpg", desc:"Rok batik A-line yang versatile untuk tampilan kasual hingga semi-formal." },
  { title:"Galeri Seni Batik", category:"sentra", tag:"Sentra Paoman", img:"assets/img/pin-64cc4ca8.jpg", desc:"Galeri seni batik yang menyimpan koleksi karya maestro pembatik Indramayu lintas generasi." },
  { title:"Motif Buketan Pesisir", category:"motif", tag:"Motif Batik", img:"assets/img/pin-b4def898.jpg", desc:"Rangkaian bunga buketan pesisir yang melambangkan keindahan dan kesuburan alam Dermayon." },
  { title:"Heritage Center Paoman", category:"sentra", tag:"Sentra Paoman", img:"assets/img/pin-eb416f44.jpg", desc:"Pusat warisan budaya batik Paoman yang menjadi destinasi wisata edukatif Indramayu." },
  { title:"Detail Motif Lokcan", category:"detail", tag:"Detail Tekstur", img:"assets/img/pin-953ddeb8.jpg", desc:"Detail halus motif lokcan burung phoenix, perpaduan budaya Tionghoa dan Jawa." },
  { title:"Set Busana Couple Batik", category:"busana", tag:"Busana Modern", img:"assets/img/pin-7b7da76e.jpg", desc:"Set busana couple batik untuk pasangan yang ingin tampil serasi dan penuh gaya." },
  { title:"Mural Batik Indramayu", category:"sentra", tag:"Sentra Paoman", img:"assets/img/pin-1dc76be6.jpg", desc:"Mural besar bermotif batik yang menghiasi dinding di kawasan sentra Paoman, Indramayu." },
  { title:"Syal Batik Hijab Modern", category:"busana", tag:"Busana Modern", img:"assets/img/pin-ed1bc9ab.jpg", desc:"Syal batik yang cocok digunakan sebagai hijab modern dengan motif mega mendung pesisir." }
];

function initGalleryPage() {
  const galeriGrid = document.getElementById('galeriGrid');
  if (!galeriGrid) return; // Not on gallery page

  const filterBar = document.querySelector('.gallery-filter-bar');
  const searchInput = document.getElementById('galeriSearchInput');
  const searchCount = document.getElementById('galeriSearchCount');
  const emptyState = document.getElementById('galeriEmpty');
  let currentFilter = 'semua';
  let currentSearch = '';

  const staticCards = galeriGrid.querySelectorAll('.gallery-page-card');

  // If cards are directly in HTML, use them!
  if (staticCards.length > 0) {
    function filterStaticGallery() {
      let visibleCount = 0;
      staticCards.forEach(card => {
        const cat = card.getAttribute('data-category') || '';
        const title = (card.getAttribute('data-title') || card.querySelector('.gallery-page-title')?.textContent || '').toLowerCase();
        const tag = (card.getAttribute('data-tag') || card.querySelector('.gallery-page-tag')?.textContent || '').toLowerCase();
        const desc = (card.getAttribute('data-desc') || '').toLowerCase();

        const matchCategory = (currentFilter === 'semua') || (cat === currentFilter);
        const matchSearch = !currentSearch || title.includes(currentSearch) || tag.includes(currentSearch) || desc.includes(currentSearch);

        if (matchCategory && matchSearch) {
          card.style.display = '';
          visibleCount++;
        } else {
          card.style.display = 'none';
        }
      });

      if (searchCount) {
        const totalCards = staticCards.length;
        const filtered = currentFilter !== 'semua' || currentSearch !== '';
        searchCount.textContent = filtered
          ? `${visibleCount} dari ${totalCards} koleksi`
          : `${totalCards} koleksi`;
      }

      if (emptyState) {
        emptyState.style.display = visibleCount === 0 ? 'block' : 'none';
      }
    }

    // Attach click for modal preview to each static card
    staticCards.forEach(card => {
      card.style.cursor = 'pointer';
      card.addEventListener('click', () => {
        const imgSrc = card.querySelector('img')?.getAttribute('src') || '';
        const title = card.getAttribute('data-title') || card.querySelector('.gallery-page-title')?.textContent || '';
        const tag = card.getAttribute('data-tag') || card.querySelector('.gallery-page-tag')?.textContent || '';
        const desc = card.getAttribute('data-desc') || 'Koleksi mahakarya Batik Binar pesisir Dermayon, Indramayu.';

        const modal = document.getElementById('galeriModal');
        if (!modal) return;

        const modalImgWrap = modal.querySelector('.gallery-detail-img-wrap');
        const modalImg = document.getElementById('galeriModalImg');
        const modalTag = document.getElementById('galeriModalTag');
        const modalTitle = document.getElementById('galeriModalTitle');
        const modalDesc = document.getElementById('galeriModalDesc');

        if (modalImg) {
          modalImg.src = imgSrc;
        }
        if (modalImgWrap) {
          modalImgWrap.style.display = imgSrc ? 'block' : 'none';
        }
        if (modalTag) modalTag.textContent = tag;
        if (modalTitle) modalTitle.textContent = title;
        if (modalDesc) modalDesc.textContent = desc;

        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
      });
    });

    // Filter button clicks
    if (filterBar) {
      filterBar.addEventListener('click', (e) => {
        const btn = e.target.closest('.gallery-filter-btn');
        if (!btn) return;
        filterBar.querySelectorAll('.gallery-filter-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        currentFilter = btn.getAttribute('data-filter') || 'semua';
        filterStaticGallery();
      });
    }

    // Search input
    if (searchInput) {
      searchInput.addEventListener('input', () => {
        currentSearch = searchInput.value.trim().toLowerCase();
        filterStaticGallery();
      });
    }

    // Initial filter run
    filterStaticGallery();
  } else {
    // Fallback dynamic rendering from GALLERY_DATA array
    function renderGallery() {
      const filtered = currentFilter === 'semua'
        ? GALLERY_DATA
        : GALLERY_DATA.filter(g => g.category === currentFilter);

      galeriGrid.innerHTML = filtered.map((g, idx) => {
        const realIdx = GALLERY_DATA.indexOf(g);
        return `
        <a href="#" class="gallery-page-card" data-category="${g.category}" onclick="openGaleriDetail(${realIdx}); return false;">
          <img src="${g.img}" alt="${g.title}" width="800" height="600" loading="lazy" decoding="async">
          <div class="gallery-page-overlay">
            <span class="gallery-page-tag">${g.tag}</span>
            <h4 class="gallery-page-title">${g.title}</h4>
          </div>
        </a>
      `}).join('');
    }

    if (filterBar) {
      filterBar.addEventListener('click', (e) => {
        const btn = e.target.closest('.gallery-filter-btn');
        if (!btn) return;
        filterBar.querySelectorAll('.gallery-filter-btn').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        currentFilter = btn.getAttribute('data-filter');
        renderGallery();
      });
    }

    renderGallery();
  }

  // Modal
  const modal = document.getElementById('galeriModal');
  const closeBtn = document.getElementById('galeriModalClose');

  if (modal && closeBtn) {
    closeBtn.addEventListener('click', () => {
      modal.classList.remove('active');
      document.body.style.overflow = '';
    });
    modal.addEventListener('click', (e) => {
      if (e.target === modal) {
        modal.classList.remove('active');
        document.body.style.overflow = '';
      }
    });
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && modal.classList.contains('active')) {
        modal.classList.remove('active');
        document.body.style.overflow = '';
      }
    });
  }
}

// Global function to open gallery detail
window.openGaleriDetail = function(index) {
  const item = GALLERY_DATA[index];
  if (!item) return;

  const modal = document.getElementById('galeriModal');
  if (!modal) return;

  const img = document.getElementById('galeriModalImg');
  const tag = document.getElementById('galeriModalTag');
  const title = document.getElementById('galeriModalTitle');
  const desc = document.getElementById('galeriModalDesc');

  if (img) img.src = item.img;
  if (tag) tag.textContent = item.tag;
  if (title) title.textContent = item.title;
  if (desc) desc.textContent = item.desc;

  modal.classList.add('active');
  document.body.style.overflow = 'hidden';
};
