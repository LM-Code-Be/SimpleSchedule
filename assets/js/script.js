document.addEventListener('DOMContentLoaded', () => {
    initCharts();
});

function initCharts() {
    if (!window.appCharts || typeof Chart === 'undefined') {
        return;
    }

    const cfg = window.appCharts;

    const weeklyEl = document.getElementById('weeklyChart');
    if (weeklyEl) {
        new Chart(weeklyEl, {
            type: 'bar',
            data: {
                labels: cfg.weekly.labels,
                datasets: [{
                    label: 'Événements',
                    data: cfg.weekly.values,
                    borderRadius: 8,
                    backgroundColor: '#0f63f4'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
            }
        });
    }

    const monthlyEl = document.getElementById('monthlyChart');
    if (monthlyEl) {
        new Chart(monthlyEl, {
            type: 'line',
            data: {
                labels: cfg.monthly.labels,
                datasets: [{
                    label: 'Événements / mois',
                    data: cfg.monthly.values,
                    borderColor: '#0284c7',
                    backgroundColor: 'rgba(14, 165, 233, 0.16)',
                    tension: 0.35,
                    fill: true,
                    pointRadius: 3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { precision: 0 } } }
            }
        });
    }

    const priorityEl = document.getElementById('priorityChart');
    if (priorityEl) {
        new Chart(priorityEl, {
            type: 'doughnut',
            data: {
                labels: cfg.priority.labels,
                datasets: [{
                    data: cfg.priority.values,
                    backgroundColor: ['#14b8a6', '#f59e0b', '#ef4444']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });
    }

    const tagsEl = document.getElementById('tagsChart');
    if (tagsEl) {
        new Chart(tagsEl, {
            type: 'bar',
            data: {
                labels: cfg.tags.labels,
                datasets: [{
                    data: cfg.tags.values,
                    backgroundColor: cfg.tags.colors,
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y',
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    x: { beginAtZero: true, ticks: { precision: 0 } },
                    y: { ticks: { autoSkip: false } }
                }
            }
        });
    }
}
