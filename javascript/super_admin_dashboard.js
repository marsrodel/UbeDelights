(function() {
    'use strict';

    var accent = '#764ba2';
    var gridColor = '#e8e8f0';
    var fontFamily = "'DM Sans', sans-serif";

    var registrationsData = typeof chartRegistrationsData !== 'undefined' ? chartRegistrationsData : { labels: [], counts: [] };
    var statusData = typeof chartStatusData !== 'undefined' ? chartStatusData : { labels: [], counts: [] };
    var activityData = typeof chartActivityData !== 'undefined' ? chartActivityData : { labels: [], counts: [] };
    var roleData = typeof chartRoleData !== 'undefined' ? chartRoleData : { labels: [], counts: [] };

    function initCharts() {
        if (typeof Chart === 'undefined') return;

        new Chart(document.getElementById('registrationsChart'), {
            type: 'line',
            data: {
                labels: registrationsData.labels,
                datasets: [{
                    label: 'Registrations',
                    data: registrationsData.counts,
                    borderColor: accent,
                    backgroundColor: accent + '20',
                    fill: true,
                    tension: 0.4,
                    pointRadius: 4,
                    pointBackgroundColor: accent
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { display: false }, ticks: { font: { family: fontFamily, size: 11 } } },
                    y: { beginAtZero: true, grid: { color: gridColor }, ticks: { font: { family: fontFamily, size: 11 }, stepSize: 1 } }
                }
            }
        });

        new Chart(document.getElementById('statusChart'), {
            type: 'doughnut',
            data: {
                labels: statusData.labels,
                datasets: [{
                    data: statusData.counts,
                    backgroundColor: ['#22c55e', '#f59e0b', '#ef4444', '#6366f1', '#8b8ba3'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { font: { family: fontFamily, size: 12 }, padding: 16 } }
                },
                cutout: '65%'
            }
        });

        new Chart(document.getElementById('activityChart'), {
            type: 'bar',
            data: {
                labels: activityData.labels,
                datasets: [{
                    label: 'Actions',
                    data: activityData.counts,
                    backgroundColor: accent + 'cc',
                    borderRadius: 6,
                    barThickness: 32
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { display: false }, ticks: { font: { family: fontFamily, size: 11 } } },
                    y: { beginAtZero: true, grid: { color: gridColor }, ticks: { font: { family: fontFamily, size: 11 }, stepSize: 1 } }
                }
            }
        });

        new Chart(document.getElementById('roleChart'), {
            type: 'doughnut',
            data: {
                labels: roleData.labels,
                datasets: [{
                    data: roleData.counts,
                    backgroundColor: ['#7c3aed', '#6366f1', '#a78bfa'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { font: { family: fontFamily, size: 12 }, padding: 16 } }
                },
                cutout: '65%'
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initCharts);
    } else {
        initCharts();
    }
})();
