'use strict';

const excerptField = document.getElementById('excerpt');
const excerptCounter = document.getElementById('excerpt-counter');

if (excerptField && excerptCounter && !excerptCounter.dataset.counterBound) {
    excerptCounter.dataset.counterBound = '1';

    const updateCounter = () => {
        const length = excerptField.value.length;
        excerptCounter.textContent = `${length}/300`;
        excerptCounter.style.color = length > 300 ? '#ef9a9a' : '#9b9b9b';
    };

    excerptField.addEventListener('input', updateCounter);
    updateCounter();
}
