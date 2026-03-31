'use strict';

const csrfMeta = document.querySelector('meta[name="csrf-token"]');
const csrfToken = csrfMeta ? csrfMeta.content : '';
const baseMeta = document.querySelector('meta[name="base-url"]');
const baseUrl = baseMeta ? baseMeta.content : '';

if (baseUrl) {
    pollNotifications();
    window.setInterval(pollNotifications, 60000);
}

const markAllButton = document.getElementById('mark-all-notifications');
if (markAllButton) {
    markAllButton.addEventListener('click', () => {
        fetch(`${baseUrl}/api/notifications/read`, {
            method: 'POST',
            headers: {
                'X-CSRF-Token': csrfToken,
                'Accept': 'application/json',
            },
            body: new URLSearchParams({ csrf_token: csrfToken }),
        })
            .then((response) => response.json())
            .then(() => pollNotifications())
            .catch(() => {});
    });
}

function pollNotifications() {
    fetch(`${baseUrl}/api/notifications`, {
        headers: { Accept: 'application/json' },
    })
        .then((response) => response.json())
        .then((payload) => {
            if (!payload || payload.ok !== true) {
                return;
            }

            updateBadges(Number(payload.unread_count || 0));
            updateNotificationList(Array.isArray(payload.data) ? payload.data : []);
            maybeTriggerWebNotification(Array.isArray(payload.data) ? payload.data : []);
        })
        .catch(() => {});
}

function updateBadges(unreadCount) {
    document.querySelectorAll('.notification-badge').forEach((badge) => {
        badge.textContent = String(unreadCount);
        badge.hidden = unreadCount <= 0;
    });
}

function updateNotificationList(items) {
    const list = document.getElementById('notification-list');
    if (!list) {
        return;
    }

    list.innerHTML = '';
    const sliced = items.slice(0, 8);
    if (sliced.length === 0) {
        const li = document.createElement('li');
        li.textContent = 'Aucune notification.';
        list.appendChild(li);
        return;
    }

    sliced.forEach((item) => {
        const li = document.createElement('li');
        li.className = Number(item.is_read || 0) === 0 ? 'notification-item unread' : 'notification-item';
        li.innerHTML = `
            <strong>${escapeHtml(String(item.type || 'info'))}</strong>
            <p>${escapeHtml(String(item.message || ''))}</p>
            <small>${escapeHtml(String(item.created_at || ''))}</small>
        `;
        list.appendChild(li);
    });
}

function maybeTriggerWebNotification(items) {
    if (!('Notification' in window)) {
        return;
    }

    const unread = items.find((item) => Number(item.is_read || 0) === 0);
    if (!unread) {
        return;
    }

    const lastSeenId = window.localStorage.getItem('last_notification_id');
    if (lastSeenId === String(unread.id)) {
        return;
    }

    const fireNotification = () => {
        try {
            new Notification('Iran - Nouvelle notification', {
                body: String(unread.message || 'Mise a jour disponible'),
                icon: `${baseUrl}/public/images/logo.webp`,
            });
            window.localStorage.setItem('last_notification_id', String(unread.id));
        } catch (error) {
            // Silently ignore if blocked.
        }
    };

    if (Notification.permission === 'granted') {
        fireNotification();
        return;
    }

    if (Notification.permission === 'default') {
        Notification.requestPermission().then((permission) => {
            if (permission === 'granted') {
                fireNotification();
            }
        });
    }
}

function escapeHtml(value) {
    return value
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}
