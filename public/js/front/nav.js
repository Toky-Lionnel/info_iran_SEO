'use strict';

const navToggle = document.querySelector('.nav-toggle');
const navMenu = document.querySelector('.nav-menu');
const dropdownToggles = document.querySelectorAll('.nav-dropdown-toggle');

if (navToggle && navMenu) {
    navToggle.addEventListener('click', () => {
        const isOpen = navMenu.classList.toggle('open');
        navToggle.setAttribute('aria-expanded', String(isOpen));
    });
}

const closeDropdowns = () => {
    document.querySelectorAll('.nav-dropdown.open').forEach((dropdown) => {
        dropdown.classList.remove('open');
        const toggle = dropdown.querySelector('.nav-dropdown-toggle');
        if (toggle) {
            toggle.setAttribute('aria-expanded', 'false');
        }
    });
};

dropdownToggles.forEach((toggle) => {
    toggle.addEventListener('click', (event) => {
        event.preventDefault();
        const dropdown = toggle.closest('.nav-dropdown');
        if (!dropdown) {
            return;
        }

        const isOpen = dropdown.classList.contains('open');
        closeDropdowns();
        if (!isOpen) {
            dropdown.classList.add('open');
            toggle.setAttribute('aria-expanded', 'true');
        }
    });
});

document.addEventListener('click', (event) => {
    const target = event.target;
    if (!(target instanceof Node)) {
        return;
    }

    if (navToggle && navMenu && !navToggle.contains(target) && !navMenu.contains(target)) {
        navMenu.classList.remove('open');
        navToggle.setAttribute('aria-expanded', 'false');
    }

    if (!target.closest('.nav-dropdown')) {
        closeDropdowns();
    }
});

document.querySelectorAll('[data-confirm]').forEach((button) => {
    button.addEventListener('click', (event) => {
        const message = button.getAttribute('data-confirm') || 'Confirmer cette action ?';
        if (!window.confirm(message)) {
            event.preventDefault();
        }
    });
});
