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
});

/* ==========================================================================
   1. SPLASH SCREEN
   ========================================================================== */
function initSplashScreen() {
  const splash = document.getElementById('splashScreen');
  const skipBtn = document.getElementById('splashSkipBtn');

  if (!splash) return;

  const KEY = 'binarSplashSeen';
  let timer = null;
  let pendingHref = null;

  const dismissSplash = (remember) => {
    if (remember) {
      try { sessionStorage.setItem(KEY, '1'); } catch (e) {}
    }
    clearTimeout(timer);
    splash.classList.add('hidden');
    setTimeout(() => {
      splash.style.display = 'none';
      if (pendingHref) {
        const href = pendingHref;
        pendingHref = null;
        window.location.href = href;
      }
    }, 500);
  };

  const playSplash = (afterHref) => {
    pendingHref = afterHref || null;
    splash.style.display = '';
    splash.classList.remove('hidden');
    // restart animasi progress bar
    void splash.offsetWidth;
    const fill = splash.querySelector('.splash-progress-fill');
    if (fill) {
      fill.style.animation = 'none';
      void fill.offsetWidth;
      fill.style.animation = '';
    }
    clearTimeout(timer);
    timer = setTimeout(() => dismissSplash(true), 2400);
  };

  if (skipBtn) {
    skipBtn.addEventListener('click', (e) => {
      e.preventDefault();
      dismissSplash(true);
    });
  }

  // Otomatis hanya sekali per sesi; kunjungan berikutnya langsung masuk
  let seen = false;
  try { seen = !!sessionStorage.getItem(KEY); } catch (e) {}
  if (seen) {
    splash.style.display = 'none';
  } else {
    playSplash(null);
  }

  // Klik logo Batik Binar = putar ulang animasi, lalu ke beranda
  document.querySelectorAll('a.brand-logo').forEach((logo) => {
    logo.addEventListener('click', (e) => {
      e.preventDefault();
      playSplash(logo.getAttribute('href'));
    });
  });
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

  // Active section spy (berbasis posisi scroll — konsisten untuk semua section)
  const spyLinks = Array.from(document.querySelectorAll('.nav-menu .nav-link'))
    .filter((a) => (a.hash || '') !== '');
  const spySections = spyLinks
    .map((a) => document.querySelector(a.hash))
    .filter((el) => !!el);

  const setActiveLink = (hash) => {
    spyLinks.forEach((link) => {
      link.classList.toggle('active', link.hash === hash);
    });
  };

  if (spySections.length > 0) {
    const OFFSET = 160; // kompensasi header fixed
    const updateSpy = () => {
      const y = window.scrollY + OFFSET;
      let current = spySections[0];
      spySections.forEach((sec) => {
        if (sec.offsetTop <= y) current = sec;
      });
      setActiveLink(current ? '#' + current.id : '');
    };
    let ticking = false;
    window.addEventListener('scroll', () => {
      if (!ticking) {
        window.requestAnimationFrame(() => { updateSpy(); ticking = false; });
        ticking = true;
      }
    }, { passive: true });
    // tandai langsung saat link diklik (tanpa menunggu scroll berhenti)
    spyLinks.forEach((link) => {
      link.addEventListener('click', () => setActiveLink(link.hash));
    });
    updateSpy();
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
        card.style.borderColor = '#1687D9';
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
    iconHtml = `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#1687D9" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>`;
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
