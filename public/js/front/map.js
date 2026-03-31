'use strict';

const mapElement = document.getElementById('fo-events-map');

if (mapElement && typeof window.L !== 'undefined') {
    const map = L.map(mapElement).setView([32.0, 53.0], 5);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 18,
        attribution: '&copy; OpenStreetMap contributors',
    }).addTo(map);

    const typeColors = {
        militaire: '#c62828',
        politique: '#1565c0',
        diplomatique: '#2e7d32',
        bombardement: '#ef6c00',
        manifestation: '#6a1b9a',
    };

    const clusterLayer = typeof L.markerClusterGroup === 'function'
        ? L.markerClusterGroup()
        : L.layerGroup();
    clusterLayer.addTo(map);

    let heatLayer = null;
    const heatPoints = [];

    const iranZoneGeoJson = {
        type: 'Feature',
        geometry: {
            type: 'Polygon',
            coordinates: [[
                [44.0, 39.8], [47.0, 39.0], [51.0, 38.5], [56.5, 36.8], [61.8, 34.5],
                [62.5, 30.0], [61.0, 26.0], [56.0, 25.0], [51.0, 25.5], [47.5, 27.0],
                [45.0, 30.0], [44.0, 35.0], [44.0, 39.8],
            ]],
        },
    };

    L.geoJSON(iranZoneGeoJson, {
        style: {
            color: '#90a4ae',
            fillColor: '#546e7a',
            fillOpacity: 0.08,
            weight: 1.5,
        },
    }).addTo(map);

    let lastValidLatLng = [];

    const renderEvents = (events) => {
        clusterLayer.clearLayers();
        heatPoints.length = 0;
        lastValidLatLng = [];

        events.forEach((event) => {
            const lat = Number(event.latitude || 0);
            const lng = Number(event.longitude || 0);
            if (
                !Number.isFinite(lat)
                || !Number.isFinite(lng)
                || lat < -90
                || lat > 90
                || lng < -180
                || lng > 180
            ) {
                return;
            }

            const color = typeColors[event.type] || '#c62828';
            const marker = L.circleMarker([lat, lng], {
                radius: 7,
                color,
                fillColor: color,
                fillOpacity: 0.8,
                weight: 1,
            });

            const popup = `
                <div class="map-popup">
                    <h3>${escapeHtml(String(event.title || 'Evenement'))}</h3>
                    <p><strong>Type:</strong> ${escapeHtml(String(event.type || ''))}</p>
                    <p><strong>Ville:</strong> ${escapeHtml(String(event.city || ''))}</p>
                    <p><strong>Date:</strong> ${escapeHtml(String(event.event_date || ''))}</p>
                    <p>${escapeHtml(String(event.description || ''))}</p>
                </div>
            `;
            marker.bindPopup(popup);
            clusterLayer.addLayer(marker);
            heatPoints.push([lat, lng, 0.6]);
            lastValidLatLng.push([lat, lng]);
        });

        if (heatLayer) {
            map.removeLayer(heatLayer);
            heatLayer = null;
        }
        if (heatPoints.length > 0 && typeof L.heatLayer === 'function') {
            heatLayer = L.heatLayer(heatPoints, { radius: 24, blur: 18 });
        }
    };

    const addHeatToggle = () => {
        if (typeof L.heatLayer !== 'function') {
            return;
        }

        const HeatControl = L.Control.extend({
            options: { position: 'topright' },
            onAdd() {
                const button = L.DomUtil.create('button', 'leaflet-bar heat-toggle');
                button.type = 'button';
                button.textContent = 'Heatmap';
                button.setAttribute('aria-label', 'Basculer heatmap');
                L.DomEvent.on(button, 'click', (event) => {
                    L.DomEvent.stopPropagation(event);
                    if (!heatLayer) {
                        return;
                    }
                    if (map.hasLayer(heatLayer)) {
                        map.removeLayer(heatLayer);
                    } else {
                        heatLayer.addTo(map);
                    }
                });
                return button;
            },
        });

        map.addControl(new HeatControl());
    };

    const apiUrl = mapElement.dataset.apiUrl || '';
    if (apiUrl !== '') {
        fetch(apiUrl, { headers: { Accept: 'application/json' } })
            .then((response) => response.json())
            .then((payload) => {
                const events = Array.isArray(payload.data) ? payload.data : [];
                renderEvents(events);
                addHeatToggle();

                if (lastValidLatLng.length > 0) {
                    const bounds = L.latLngBounds(lastValidLatLng);
                    if (bounds.isValid()) {
                        map.fitBounds(bounds.pad(0.15));
                    }
                }
            })
            .catch(() => {
                // Keep map usable even if API fails.
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
