'use strict';

const boStatsCanvas = document.getElementById('bo-stats-chart');
const boSeries = window.__BO_STATS_SERIES__;

if (boStatsCanvas && typeof window.Chart !== 'undefined' && boSeries) {
    new window.Chart(boStatsCanvas, {
        type: 'line',
        data: {
            labels: Array.isArray(boSeries.labels) ? boSeries.labels : [],
            datasets: [
                {
                    label: 'Pertes',
                    data: Array.isArray(boSeries.pertes) ? boSeries.pertes : [],
                    borderColor: '#c62828',
                    backgroundColor: 'rgba(198,40,40,0.15)',
                    fill: true,
                    tension: 0.3,
                },
                {
                    label: 'Deplacements',
                    data: Array.isArray(boSeries.deplacements) ? boSeries.deplacements : [],
                    borderColor: '#1565c0',
                    backgroundColor: 'rgba(21,101,192,0.12)',
                    fill: true,
                    tension: 0.3,
                },
                {
                    label: 'Sanctions',
                    data: Array.isArray(boSeries.sanctions) ? boSeries.sanctions : [],
                    borderColor: '#2e7d32',
                    backgroundColor: 'rgba(46,125,50,0.12)',
                    fill: true,
                    tension: 0.3,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    labels: { color: '#d5d5d5' },
                },
            },
            scales: {
                x: {
                    ticks: { color: '#aaaaaa' },
                    grid: { color: 'rgba(255,255,255,0.08)' },
                },
                y: {
                    ticks: { color: '#aaaaaa' },
                    grid: { color: 'rgba(255,255,255,0.08)' },
                },
            },
        },
    });
}
