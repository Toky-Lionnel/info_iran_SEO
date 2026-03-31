'use strict';

const chartCanvas = document.getElementById('fo-stats-chart');
const series = window.__FO_STATS_SERIES__;

if (chartCanvas && typeof window.Chart !== 'undefined' && series) {
    new window.Chart(chartCanvas, {
        type: 'line',
        data: {
            labels: Array.isArray(series.labels) ? series.labels : [],
            datasets: [
                {
                    label: 'Pertes humaines',
                    data: Array.isArray(series.pertes) ? series.pertes : [],
                    borderColor: '#c62828',
                    backgroundColor: 'rgba(198,40,40,0.18)',
                    tension: 0.28,
                    fill: true,
                },
                {
                    label: 'Deplacements population',
                    data: Array.isArray(series.deplacements) ? series.deplacements : [],
                    borderColor: '#1565c0',
                    backgroundColor: 'rgba(21,101,192,0.16)',
                    tension: 0.28,
                    fill: true,
                },
                {
                    label: 'Sanctions economiques',
                    data: Array.isArray(series.sanctions) ? series.sanctions : [],
                    borderColor: '#2e7d32',
                    backgroundColor: 'rgba(46,125,50,0.16)',
                    tension: 0.28,
                    fill: true,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                legend: {
                    labels: {
                        color: '#dedede',
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
