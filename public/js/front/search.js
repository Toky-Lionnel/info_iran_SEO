'use strict';

const searchInput = document.querySelector('.search-form input[type="search"]');
const cards = document.querySelectorAll('.article-card');

if (searchInput && cards.length > 0) {
    searchInput.addEventListener('input', () => {
        const query = searchInput.value.trim().toLowerCase();
        cards.forEach((card) => {
            const text = card.textContent ? card.textContent.toLowerCase() : '';
            card.style.display = text.includes(query) ? '' : 'none';
        });
    });
}
