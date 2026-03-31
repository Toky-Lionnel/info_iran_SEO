'use strict';

document.querySelectorAll('[data-confirm]').forEach((button) => {
    if (button instanceof HTMLElement && button.dataset.confirmBound) {
        return;
    }
    if (button instanceof HTMLElement) {
        button.dataset.confirmBound = '1';
    }
    button.addEventListener('click', (event) => {
        if (!window.confirm(button.getAttribute('data-confirm') || 'Confirmer ?')) {
            event.preventDefault();
        }
    });
});
