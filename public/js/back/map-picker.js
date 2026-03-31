'use strict';

if (typeof window.L !== 'undefined') {
    const mapNodes = document.querySelectorAll('.admin-map');
    mapNodes.forEach((mapNode) => {
        const latInputId = mapNode.getAttribute('data-lat-input') || '';
        const lngInputId = mapNode.getAttribute('data-lng-input') || '';
        const latInput = latInputId ? document.getElementById(latInputId) : null;
        const lngInput = lngInputId ? document.getElementById(lngInputId) : null;
        if (!latInput || !lngInput) {
            return;
        }

        const rawLat = Number(mapNode.getAttribute('data-lat') || latInput.value || 32.0);
        const rawLng = Number(mapNode.getAttribute('data-lng') || lngInput.value || 53.0);
        const initialLat = Number.isFinite(rawLat) && rawLat >= -90 && rawLat <= 90 ? rawLat : 32.0;
        const initialLng = Number.isFinite(rawLng) && rawLng >= -180 && rawLng <= 180 ? rawLng : 53.0;

        const map = window.L.map(mapNode).setView([initialLat, initialLng], 5);
        window.L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 18,
            attribution: '&copy; OpenStreetMap contributors',
        }).addTo(map);

        const marker = window.L.marker([initialLat, initialLng], { draggable: true }).addTo(map);

        const applyMarkerPosition = (lat, lng) => {
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

            const fixedLat = Number(lat).toFixed(6);
            const fixedLng = Number(lng).toFixed(6);
            latInput.value = fixedLat;
            lngInput.value = fixedLng;
            marker.setLatLng([Number(fixedLat), Number(fixedLng)]);
        };

        map.on('click', (event) => {
            applyMarkerPosition(event.latlng.lat, event.latlng.lng);
        });

        marker.on('dragend', () => {
            const pos = marker.getLatLng();
            applyMarkerPosition(pos.lat, pos.lng);
        });
    });
}
