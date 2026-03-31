'use strict';

const navToggle = document.querySelector('.nav-toggle');
const navMenu = document.querySelector('.nav-menu');

if (navToggle && navMenu && !navMenu.dataset.boundNav) {
    navMenu.dataset.boundNav = '1';

    navToggle.addEventListener('click', () => {
        const isOpen = navMenu.classList.toggle('open');
        navToggle.setAttribute('aria-expanded', String(isOpen));
    });

    document.addEventListener('click', (event) => {
        const target = event.target;
        if (!(target instanceof Node)) {
            return;
        }
        if (!navToggle.contains(target) && !navMenu.contains(target)) {
            navMenu.classList.remove('open');
            navToggle.setAttribute('aria-expanded', 'false');
        }
    });
}

if ('IntersectionObserver' in window && !('loading' in HTMLImageElement.prototype)) {
    const images = document.querySelectorAll('img[loading="lazy"]');
    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (!entry.isIntersecting) {
                return;
            }
            const img = entry.target;
            if (img instanceof HTMLImageElement && img.dataset.src) {
                img.src = img.dataset.src;
            }
            observer.unobserve(entry.target);
        });
    });

    images.forEach((img) => observer.observe(img));
}
