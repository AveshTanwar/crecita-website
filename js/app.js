/**
 * CRECITA WEBSITE — app.js
 * Clean, performance-optimised interactions
 */

class CrecitaWebsite {
    constructor() {
        this.init();
    }

    init() {
        this.setupThemeToggle();
        this.setupNavigation();
        this.setupScrollEffects();
        this.setupAnimations();
    }

    /* ─── Theme Toggle ─── */
    setupThemeToggle() {
        const themeToggle = document.getElementById('themeToggle');
        const saved = localStorage.getItem('theme') || 'light';

        if (saved === 'dark') {
            document.body.setAttribute('data-theme', 'dark');
            this.updateThemeIcon(true);
        }

        themeToggle?.addEventListener('click', () => {
            const isDark = document.body.getAttribute('data-theme') === 'dark';
            if (isDark) {
                document.body.removeAttribute('data-theme');
                localStorage.setItem('theme', 'light');
                this.updateThemeIcon(false);
            } else {
                document.body.setAttribute('data-theme', 'dark');
                localStorage.setItem('theme', 'dark');
                this.updateThemeIcon(true);
            }
        });
    }

    updateThemeIcon(isDark) {
        const btn = document.getElementById('themeToggle');
        if (btn) btn.innerHTML = isDark ? '<i class="fas fa-sun"></i>' : '<i class="fas fa-moon"></i>';
    }

    /* ─── Navigation ─── */
    setupNavigation() {
        const navbar     = document.getElementById('navbar');
        const mobileBtn  = document.getElementById('mobileToggle');
        const navMenu    = document.getElementById('navMenu');

        // Scroll shadow
        window.addEventListener('scroll', () => {
            navbar?.classList.toggle('scrolled', window.scrollY > 40);
        }, { passive: true });

        // Mobile toggle
        mobileBtn?.addEventListener('click', () => {
            const open = navMenu.classList.toggle('active');
            const spans = mobileBtn.querySelectorAll('span');
            if (open) {
                spans[0].style.transform = 'rotate(45deg) translate(4px, 4px)';
                spans[1].style.opacity   = '0';
                spans[2].style.transform = 'rotate(-45deg) translate(4px, -4px)';
            } else {
                spans.forEach(s => { s.style.transform = ''; s.style.opacity = ''; });
            }
        });

        // Mobile dropdown: tap to toggle submenu
        navMenu?.querySelectorAll('.nav-dropdown > .nav-link').forEach(link => {
            link.addEventListener('click', (e) => {
                if (window.innerWidth <= 768) {
                    e.preventDefault();
                    e.stopPropagation();
                    link.closest('.nav-dropdown').classList.toggle('open');
                }
            });
        });

        // Close menu on regular nav-link click (but not dropdown toggles)
        navMenu?.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', (e) => {
                if (link.closest('.nav-dropdown') && window.innerWidth <= 768) return;
                navMenu.classList.remove('active');
                navMenu.querySelectorAll('.nav-dropdown').forEach(d => d.classList.remove('open'));
                mobileBtn?.querySelectorAll('span').forEach(s => {
                    s.style.transform = ''; s.style.opacity = '';
                });
            });
        });

        // Close menu on submenu link click
        navMenu?.querySelectorAll('.dropdown-menu a').forEach(link => {
            link.addEventListener('click', () => {
                navMenu.classList.remove('active');
                navMenu.querySelectorAll('.nav-dropdown').forEach(d => d.classList.remove('open'));
                mobileBtn?.querySelectorAll('span').forEach(s => {
                    s.style.transform = ''; s.style.opacity = '';
                });
            });
        });

        this.setActiveLink();
    }

    setActiveLink() {
        const page = window.location.pathname.split('/').pop() || 'index.html';
        document.querySelectorAll('.nav-link').forEach(link => {
            link.classList.toggle('active', link.getAttribute('href') === page);
        });
    }

    /* ─── Scroll Effects ─── */
    setupScrollEffects() {
        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(a => {
            a.addEventListener('click', e => {
                const target = document.querySelector(a.getAttribute('href'));
                if (target) {
                    e.preventDefault();
                    const offset = 80; // navbar height
                    const top = target.getBoundingClientRect().top + window.scrollY - offset;
                    window.scrollTo({ top, behavior: 'smooth' });
                }
            });
        });

        // Back to top button
        this.createBackToTop();
    }

    createBackToTop() {
        const btn = document.createElement('button');
        btn.innerHTML = '<i class="fas fa-chevron-up"></i>';
        btn.setAttribute('aria-label', 'Back to top');
        Object.assign(btn.style, {
            position:     'fixed',
            bottom:       '1.5rem',
            right:        '1.5rem',
            width:        '44px',
            height:       '44px',
            borderRadius: '50%',
            background:   'var(--primary)',
            color:        'white',
            border:       'none',
            cursor:       'pointer',
            zIndex:       '999',
            opacity:      '0',
            transform:    'translateY(12px)',
            transition:   'opacity 0.3s ease, transform 0.3s ease',
            display:      'flex',
            alignItems:   'center',
            justifyContent: 'center',
            fontSize:     '0.875rem',
            boxShadow:    '0 4px 12px rgba(59,130,246,0.35)',
        });

        document.body.appendChild(btn);

        window.addEventListener('scroll', () => {
            const visible = window.scrollY > 400;
            btn.style.opacity   = visible ? '1' : '0';
            btn.style.transform = visible ? 'translateY(0)' : 'translateY(12px)';
        }, { passive: true });

        btn.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
    }

    /* ─── Scroll Animations ─── */
    setupAnimations() {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach((entry, i) => {
                if (entry.isIntersecting) {
                    // Stagger siblings slightly
                    const siblings = entry.target.parentElement?.querySelectorAll('.animate-on-scroll');
                    let delay = 0;
                    if (siblings && siblings.length > 1) {
                        siblings.forEach((el, idx) => { if (el === entry.target) delay = idx * 60; });
                    }
                    setTimeout(() => entry.target.classList.add('visible'), delay);
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.08, rootMargin: '0px 0px -40px 0px' });

        document.querySelectorAll('.animate-on-scroll').forEach(el => observer.observe(el));
    }

    /* ─── Notification ─── */
    showNotification(message, type = 'info') {
        const n = document.createElement('div');
        n.textContent = message;
        Object.assign(n.style, {
            position:     'fixed',
            top:          '5rem',
            right:        '1.5rem',
            padding:      '0.875rem 1.25rem',
            borderRadius: '0.625rem',
            color:        'white',
            zIndex:       '1001',
            fontSize:     '0.9rem',
            fontWeight:   '500',
            transform:    'translateX(120%)',
            transition:   'transform 0.3s ease',
            background:   type === 'success' ? '#10b981' : type === 'error' ? '#ef4444' : '#3b82f6',
            boxShadow:    '0 4px 16px rgba(0,0,0,0.15)',
        });
        document.body.appendChild(n);
        requestAnimationFrame(() => { n.style.transform = 'translateX(0)'; });
        setTimeout(() => {
            n.style.transform = 'translateX(120%)';
            setTimeout(() => n.remove(), 300);
        }, 3500);
    }
}

document.addEventListener('DOMContentLoaded', () => new CrecitaWebsite());
