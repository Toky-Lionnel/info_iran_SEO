'use strict';

const baseMetaTag = document.querySelector('meta[name="base-url"]');
const analyticsBaseUrl = baseMetaTag ? baseMetaTag.content : '';
const startTs = Date.now();

if (analyticsBaseUrl) {
    window.addEventListener('pagehide', sendAnalytics, { once: true });
}

function sendAnalytics() {
    const durationSec = Math.max(1, Math.round((Date.now() - startTs) / 1000));
    const data = new URLSearchParams({
        page: window.location.pathname || '/',
        duration: String(durationSec),
    });
    const target = `${analyticsBaseUrl}/api/analytics`;

    if (navigator.sendBeacon) {
        const blob = new Blob([data.toString()], { type: 'application/x-www-form-urlencoded' });
        navigator.sendBeacon(target, blob);
        return;
    }

    fetch(target, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'Accept': 'application/json',
        },
        body: data.toString(),
        keepalive: true,
    }).catch(() => {});
}
