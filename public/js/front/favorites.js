'use strict';

const favoriteButtons = document.querySelectorAll('.favorite-btn');
const csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');
const baseUrlMeta = document.querySelector('meta[name="base-url"]');

const favoriteCsrf = csrfTokenMeta ? csrfTokenMeta.content : '';
const favoriteBaseUrl = baseUrlMeta ? baseUrlMeta.content : '';

favoriteButtons.forEach((button) => {
    button.addEventListener('click', () => {
        const articleId = Number(button.getAttribute('data-article-id') || 0);
        if (!favoriteBaseUrl || articleId <= 0) {
            return;
        }

        button.disabled = true;
        fetch(`${favoriteBaseUrl}/api/favorite`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'Accept': 'application/json',
                'X-CSRF-Token': favoriteCsrf,
            },
            body: new URLSearchParams({
                csrf_token: favoriteCsrf,
                article_id: String(articleId),
            }),
        })
            .then((response) => response.json())
            .then((payload) => {
                if (!payload || payload.ok !== true) {
                    return;
                }
                const isFavorite = Boolean(payload.favorite);
                const count = Number(payload.count || 0);
                button.classList.toggle('active', isFavorite);
                button.textContent = isFavorite
                    ? `Retire de lire plus tard (${count})`
                    : `Sauvegarder article (${count})`;
            })
            .catch(() => {})
            .finally(() => {
                button.disabled = false;
            });
    });
});
