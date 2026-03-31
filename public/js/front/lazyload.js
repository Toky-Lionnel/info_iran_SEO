'use strict';

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
