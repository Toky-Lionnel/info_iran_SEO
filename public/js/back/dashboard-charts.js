'use strict';

const trafficCanvas = document.getElementById('dashboard-traffic-chart');
const trafficRows = Array.isArray(window.__DASHBOARD_TRAFFIC__) ? window.__DASHBOARD_TRAFFIC__ : [];

if (trafficCanvas && typeof window.Chart !== 'undefined') {
    const labels = trafficRows.map((row) => String(row.day_label || ''));
    const values = trafficRows.map((row) => Number(row.visits || 0));

    new window.Chart(trafficCanvas, {
        type: 'bar',
        data: {
            labels,
            datasets: [
                {
                    label: 'Visites',
                    data: values,
                    borderColor: '#c62828',
                    backgroundColor: 'rgba(198,40,40,0.24)',
                    borderWidth: 1,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    labels: {
                        color: '#dadada',
                    },
                },
            },
            scales: {
                x: {
                    ticks: { color: '#bdbdbd' },
                    grid: { color: 'rgba(255,255,255,0.08)' },
                },
                y: {
                    ticks: { color: '#bdbdbd' },
                    grid: { color: 'rgba(255,255,255,0.08)' },
                },
            },
        },
    });
}
