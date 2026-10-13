/**
 * Graphique comparatif des menus (Chart.js) : nombre de commandes et chiffre d'affaires.
 * Les données sont fournies par le serveur (agrégation MongoDB) via l'attribut data-stats,
 * puis rechargées en JSON lorsque les filtres changent, sans rechargement de page.
 */
(function () {
    'use strict';

    const canvas = document.getElementById('orders-chart');
    if (!canvas || typeof Chart === 'undefined') {
        return;
    }

    const euro = new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR', maximumFractionDigits: 0 });
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    Chart.defaults.font.family = 'Inter, system-ui, sans-serif';
    Chart.defaults.color = '#2B2118';

    const chart = new Chart(canvas, {
        type: 'bar',
        data: { labels: [], datasets: [] },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: reducedMotion ? false : { duration: 400 },
            interaction: { mode: 'index', intersect: false },
            scales: {
                commandes: {
                    type: 'linear', position: 'left', beginAtZero: true,
                    ticks: { precision: 0 }, title: { display: true, text: 'Commandes' },
                },
                ca: {
                    type: 'linear', position: 'right', beginAtZero: true,
                    grid: { drawOnChartArea: false },
                    ticks: { callback: (v) => euro.format(v) }, title: { display: true, text: "Chiffre d'affaires" },
                },
            },
            plugins: {
                legend: { position: 'bottom' },
                tooltip: {
                    callbacks: {
                        label: (ctx) => ctx.dataset.yAxisID === 'ca'
                            ? ctx.dataset.label + ' : ' + euro.format(ctx.parsed.y)
                            : ctx.dataset.label + ' : ' + ctx.parsed.y,
                    },
                },
            },
        },
    });

    function render(stats) {
        chart.data.labels = stats.map((s) => s.menu_titre);
        chart.data.datasets = [
            { label: 'Nombre de commandes', data: stats.map((s) => s.nb_commandes), backgroundColor: '#7B1E3A', borderRadius: 6, yAxisID: 'commandes' },
            { label: "Chiffre d'affaires", data: stats.map((s) => s.chiffre_affaires), backgroundColor: '#C8963E', borderRadius: 6, yAxisID: 'ca' },
        ];
        chart.update();
    }

    render(JSON.parse(canvas.dataset.stats || '[]'));

    // Actualisation dynamique du graphique quand un filtre change
    const form = document.getElementById('stats-filters');
    form?.addEventListener('change', async () => {
        const params = new URLSearchParams(new FormData(form));
        try {
            const response = await fetch('/admin/api/stats?' + params.toString(), { headers: { Accept: 'application/json' } });
            if (response.ok) {
                const stats = await response.json();
                render(stats);
                document.getElementById('total-commandes').textContent = stats.reduce((n, s) => n + s.nb_commandes, 0);
                document.getElementById('total-ca').textContent = new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' })
                    .format(stats.reduce((n, s) => n + s.chiffre_affaires, 0));
            }
        } catch (e) {
            // En cas d'échec, le bouton « Filtrer » reste disponible (rechargement classique)
        }
    });
})();
