'use strict';

const shareContainers = document.querySelectorAll('.share-buttons[data-article-id]');
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
const baseUrlMeta = document.querySelector('meta[name="base-url"]')?.getAttribute('content') || '';
const baseUrl = baseUrlMeta.replace(/\/+$/, '');
const shareEndpoint = baseUrl ? `${baseUrl}/share-log` : 'share-log';

shareContainers.forEach((container) => {
    const articleId = Number(container.getAttribute('data-article-id') || '0');
    if (!Number.isInteger(articleId) || articleId <= 0) {
        return;
    }

    container.querySelectorAll('.share-btn').forEach((button) => {
        button.addEventListener('click', async () => {
            const channel = String(button.getAttribute('data-channel') || '').trim();
            if (!channel) {
                return;
            }

            if (channel === 'copy') {
                try {
                    await navigator.clipboard.writeText(window.location.href);
                    button.textContent = 'Lien copie';
                    setTimeout(() => {
                        button.textContent = 'Copier le lien';
                    }, 1600);
                } catch (error) {
                    // Ignore clipboard errors silently.
                }
            }

            const form = new FormData();
            form.set('article_id', String(articleId));
            form.set('channel', channel);
            if (csrfToken) {
                form.set('csrf_token', csrfToken);
            }

            fetch(shareEndpoint, {
                method: 'POST',
                body: form,
                credentials: 'same-origin',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                },
            }).catch(() => {
                // Logging is best-effort only.
            });
        });
    });
});
