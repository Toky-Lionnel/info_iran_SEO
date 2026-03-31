'use strict';

const sidebarToggle = document.getElementById('sidebar-toggle');
const sidebar = document.querySelector('.sidebar');

if (sidebarToggle && sidebar) {
    sidebarToggle.addEventListener('click', () => {
        sidebar.classList.toggle('open');
    });
}

document.querySelectorAll('.sidebar-group-toggle').forEach((toggle) => {
    toggle.addEventListener('click', () => {
        const group = toggle.closest('.sidebar-group');
        if (!group) {
            return;
        }

        const submenu = group.querySelector('.sidebar-submenu');
        if (!submenu) {
            return;
        }

        const isOpen = submenu.classList.toggle('open');
        toggle.setAttribute('aria-expanded', String(isOpen));
    });
});
