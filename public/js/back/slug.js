'use strict';

const titleInput = document.getElementById('title');
const slugInput = document.getElementById('slug');

function generateSlug(text) {
    return text
        .toLowerCase()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .replace(/[^a-z0-9\s-]/g, '')
        .trim()
        .replace(/\s+/g, '-')
        .replace(/-+/g, '-')
        .substring(0, 200);
}

if (titleInput && slugInput && !slugInput.dataset.slugBound) {
    slugInput.dataset.slugBound = '1';

    titleInput.addEventListener('input', () => {
        if (!slugInput.dataset.userModified) {
            slugInput.value = generateSlug(titleInput.value);
        }
    });

    slugInput.addEventListener('input', () => {
        slugInput.dataset.userModified = 'true';
    });
}
