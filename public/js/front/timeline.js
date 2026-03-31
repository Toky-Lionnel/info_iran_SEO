'use strict';

const timelineWrapper = document.getElementById('timeline-track-wrapper');
const timelineTrack = document.getElementById('timeline-track');

if (timelineWrapper && timelineTrack) {
    timelineWrapper.addEventListener(
        'wheel',
        (event) => {
            if (Math.abs(event.deltaY) <= Math.abs(event.deltaX)) {
                return;
            }
            event.preventDefault();
            timelineWrapper.scrollLeft += event.deltaY;
        },
        { passive: false }
    );

    timelineWrapper.addEventListener('keydown', (event) => {
        if (event.key === 'ArrowRight') {
            timelineWrapper.scrollLeft += 280;
        } else if (event.key === 'ArrowLeft') {
            timelineWrapper.scrollLeft -= 280;
        }
    });

    const apiUrl = timelineWrapper.dataset.apiUrl || '';
    if (apiUrl !== '') {
        fetch(apiUrl, { headers: { Accept: 'application/json' } })
            .then((response) => response.json())
            .then((payload) => {
                const events = Array.isArray(payload.data) ? payload.data : [];
                if (events.length === 0) {
                    return;
                }

                timelineTrack.innerHTML = '';
                events.forEach((event) => {
                    const card = document.createElement('article');
                    card.className = 'timeline-event-card';
                    card.tabIndex = 0;
                    card.innerHTML = `
                        <span class="timeline-date">${escapeHtml(String(event.event_date || ''))}</span>
                        <span class="timeline-category">${escapeHtml(String(event.category || ''))}</span>
                        <h3>${escapeHtml(String(event.title || ''))}</h3>
                        <p>${escapeHtml(String(event.description || ''))}</p>
                    `;
                    timelineTrack.appendChild(card);
                });

                revealTimelineCards();
            })
            .catch(() => {
                revealTimelineCards();
            });
    } else {
        revealTimelineCards();
    }
}

function revealTimelineCards() {
    const cards = document.querySelectorAll('.timeline-event-card');
    if (cards.length === 0) {
        return;
    }

    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                }
            });
        },
        {
            root: document.getElementById('timeline-track-wrapper'),
            threshold: 0.35,
        }
    );

    cards.forEach((card) => observer.observe(card));
}

function escapeHtml(value) {
    return value
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}
