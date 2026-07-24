import Chart from 'chart.js/auto';

const palette = {
    primary: '#16A34A',
    primaryLight: 'rgba(22, 163, 74, 0.12)',
    blue: '#2563EB',
    amber: '#d97706',
    rose: '#e11d48',
    slate: '#64748b',
};

function renderLineChart(canvas, data) {
    if (!canvas || data.values.every((v) => v === 0)) {
        return false;
    }

    new Chart(canvas, {
        type: 'line',
        data: {
            labels: data.labels,
            datasets: [{
                label: 'Permainan selesai',
                data: data.values,
                borderColor: palette.primary,
                backgroundColor: palette.primaryLight,
                pointBackgroundColor: palette.primary,
                pointBorderColor: '#ffffff',
                pointBorderWidth: 2,
                fill: true,
                tension: 0.35,
                pointRadius: 4,
                pointHoverRadius: 5,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#F1F5F9' } },
                x: { grid: { display: false } },
            },
        },
    });

    return true;
}

function renderDoughnutChart(canvas, data) {
    if (!canvas || data.values.every((v) => v === 0)) {
        return false;
    }

    new Chart(canvas, {
        type: 'doughnut',
        data: {
            labels: data.labels,
            datasets: [{
                data: data.values,
                backgroundColor: [palette.primary, palette.blue],
                borderWidth: 0,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '72%',
            plugins: { legend: { display: false } },
        },
    });

    return true;
}

function renderBarChart(canvas, data) {
    if (!canvas || data.values.length === 0) {
        return false;
    }

    new Chart(canvas, {
        type: 'bar',
        data: {
            labels: data.labels,
            datasets: [{
                label: 'Akurasi (%)',
                data: data.values,
                backgroundColor: palette.primary,
                borderRadius: 6,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, max: 100 } },
        },
    });

    return true;
}

function renderHorizontalBarChart(canvas, data) {
    if (!canvas || data.values.length === 0) {
        return false;
    }

    new Chart(canvas, {
        type: 'bar',
        data: {
            labels: data.labels,
            datasets: [{
                label: 'Tingkat kesalahan (%)',
                data: data.values,
                backgroundColor: palette.rose,
                borderRadius: 6,
            }],
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { x: { beginAtZero: true, max: 100 } },
        },
    });

    return true;
}

document.addEventListener('DOMContentLoaded', () => {
    const root = document.getElementById('admin-dashboard-data');
    if (!root) {
        return;
    }

    const chartData = JSON.parse(root.dataset.charts);

    const renderers = [
        ['chart-games-daily', 'empty-games-daily', renderLineChart, chartData.games_daily],
        ['chart-mode-distribution', 'empty-mode-distribution', renderDoughnutChart, chartData.mode_distribution],
        ['chart-akurasi-kategori', 'empty-akurasi-kategori', renderBarChart, chartData.akurasi_per_kategori],
        ['chart-soal-tersulit', 'empty-soal-tersulit', renderHorizontalBarChart, chartData.soal_tersulit],
    ];

    renderers.forEach(([canvasId, emptyId, renderFn, data]) => {
        const canvas = document.getElementById(canvasId);
        const skeleton = document.getElementById(`skeleton-${canvasId}`);
        const empty = document.getElementById(emptyId);

        const rendered = renderFn(canvas, data);

        if (skeleton) {
            skeleton.classList.add('hidden');
        }

        if (rendered) {
            canvas.classList.remove('hidden');
        } else if (empty) {
            empty.classList.remove('hidden');
        }
    });
});
