/* ============================================
   Noor Guide — Animations & Interactions
   ============================================ */

(function() {
    'use strict';

    /* --- Scroll Reveal --- */
    function initScrollReveal() {
        const revealElements = document.querySelectorAll('.reveal, .reveal-left, .reveal-right, .reveal-scale');
        
        if (!revealElements.length) return;

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('revealed');
                    observer.unobserve(entry.target);
                }
            });
        }, {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        });

        revealElements.forEach(el => observer.observe(el));
    }

    /* --- Counter Animation --- */
    function initCounters() {
        const counters = document.querySelectorAll('[data-count]');
        
        if (!counters.length) return;

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    animateCounter(entry.target);
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.5 });

        counters.forEach(el => observer.observe(el));
    }

    function animateCounter(el) {
        const target = el.getAttribute('data-count');
        const suffix = el.getAttribute('data-suffix') || '';
        const prefix = el.getAttribute('data-prefix') || '';
        const duration = 2000;
        const start = 0;
        const end = parseInt(target.replace(/\D/g, ''));
        const increment = end / (duration / 16);
        let current = start;

        const timer = setInterval(() => {
            current += increment;
            if (current >= end) {
                current = end;
                clearInterval(timer);
            }
            el.textContent = prefix + Math.floor(current).toLocaleString() + suffix;
        }, 16);
    }

    /* --- Navbar Scroll Effect --- */
    function initNavbarScroll() {
        const header = document.querySelector('.site-header');
        if (!header) return;

        let lastScroll = 0;

        window.addEventListener('scroll', () => {
            const currentScroll = window.pageYOffset;
            
            if (currentScroll > 50) {
                header.style.boxShadow = '0 4px 20px rgba(0, 0, 0, 0.1)';
            } else {
                header.style.boxShadow = '0 2px 12px rgba(0, 0, 0, 0.06)';
            }

            lastScroll = currentScroll;
        });
    }

    /* --- Mobile Menu --- */
    function initMobileMenu() {
        const toggle = document.querySelector('.menu-toggle');
        const nav = document.querySelector('.nav-main');
        
        if (!toggle || !nav) return;

        toggle.addEventListener('click', function() {
            const expanded = this.getAttribute('aria-expanded') === 'true';
            this.setAttribute('aria-expanded', !expanded);
            nav.classList.toggle('is-open');
        });

        // Close on link click
        nav.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', () => {
                nav.classList.remove('is-open');
                toggle.setAttribute('aria-expanded', 'false');
            });
        });
    }

    /* --- Accessibility Toolbar --- */
    function initAccessibility() {
        const root = document.documentElement;
        const STORAGE_KEY = 'noor-guide-a11y';

        function loadSettings() {
            try {
                return JSON.parse(localStorage.getItem(STORAGE_KEY)) || {};
            } catch (e) {
                return {};
            }
        }

        function saveSettings(settings) {
            try {
                localStorage.setItem(STORAGE_KEY, JSON.stringify(settings));
            } catch (e) {}
        }

        const settings = loadSettings();

        // High contrast
        const btnContrast = document.getElementById('btn-high-contrast');
        if (btnContrast) {
            if (settings.highContrast) {
                document.body.classList.add('high-contrast');
                btnContrast.setAttribute('aria-pressed', 'true');
            }
            btnContrast.addEventListener('click', function() {
                const pressed = this.getAttribute('aria-pressed') === 'true';
                this.setAttribute('aria-pressed', !pressed);
                document.body.classList.toggle('high-contrast');
                settings.highContrast = !pressed;
                saveSettings(settings);
            });
        }

        // Font size
        if (settings.fontSize) root.style.fontSize = settings.fontSize + 'px';

        const btnIncrease = document.getElementById('btn-increase-text');
        if (btnIncrease) {
            btnIncrease.addEventListener('click', function() {
                const current = parseFloat(getComputedStyle(root).fontSize);
                if (current < 28) {
                    const newSize = current + 2;
                    root.style.fontSize = newSize + 'px';
                    settings.fontSize = newSize;
                    saveSettings(settings);
                }
            });
        }

        const btnDecrease = document.getElementById('btn-decrease-text');
        if (btnDecrease) {
            btnDecrease.addEventListener('click', function() {
                const current = parseFloat(getComputedStyle(root).fontSize);
                if (current > 14) {
                    const newSize = current - 2;
                    root.style.fontSize = newSize + 'px';
                    settings.fontSize = newSize;
                    saveSettings(settings);
                }
            });
        }
    }

    /* --- Smooth Scroll --- */
    function initSmoothScroll() {
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    e.preventDefault();
                    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        });
    }

    /* --- Parallax Effect --- */
    function initParallax() {
        const parallaxElements = document.querySelectorAll('.parallax');
        
        if (!parallaxElements.length) return;

        window.addEventListener('scroll', () => {
            const scrollY = window.pageYOffset;
            
            parallaxElements.forEach(el => {
                const speed = el.getAttribute('data-speed') || 0.3;
                const yPos = -(scrollY * speed);
                el.style.transform = `translateY(${yPos}px)`;
            });
        });
    }

    /* --- Initialize All --- */
    function init() {
        initScrollReveal();
        initCounters();
        initNavbarScroll();
        initMobileMenu();
        initAccessibility();
        initSmoothScroll();
        initParallax();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();