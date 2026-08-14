(() => {
    const canvas = document.getElementById('salesChart');
    const dataNode = document.getElementById('sales-chart-data');
    if (!canvas || !dataNode || typeof Chart === 'undefined') return;

    const chartData = JSON.parse(dataNode.textContent);
    const readTheme = () => {
        const styles = getComputedStyle(document.documentElement);
        return {
            primary: styles.getPropertyValue('--erp-primary').trim(),
            muted: styles.getPropertyValue('--erp-muted').trim(),
            border: styles.getPropertyValue('--erp-border').trim(),
        };
    };

    const theme = readTheme();
    const chart = new Chart(canvas, {
        type: 'line',
        data: {
            labels: chartData.labels,
            datasets: [{
                label: chartData.label,
                data: chartData.values,
                borderColor: theme.primary,
                backgroundColor: `${theme.primary}14`,
                tension: .42,
                fill: true,
                borderWidth: 2,
                pointRadius: 0,
                pointHoverRadius: 4,
                pointBackgroundColor: theme.primary,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { intersect: false, mode: 'index' },
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: (context) => `${new Intl.NumberFormat('vi-VN').format(context.parsed.y)} ₫`,
                    },
                },
            },
            scales: {
                x: { grid: { display: false }, border: { display: false }, ticks: { color: theme.muted, padding: 8 } },
                y: {
                    beginAtZero: true,
                    border: { display: false },
                    grid: { color: `${theme.border}aa`, drawTicks: false },
                    ticks: {
                        color: theme.muted,
                        callback: (value) => `${new Intl.NumberFormat('vi-VN', { notation: 'compact' }).format(value)} ₫`,
                    },
                },
            },
        },
    });

    window.addEventListener('erp:theme-changed', () => {
        const nextTheme = readTheme();
        const dataset = chart.data.datasets[0];

        dataset.borderColor = nextTheme.primary;
        dataset.backgroundColor = `${nextTheme.primary}14`;
        dataset.pointBackgroundColor = nextTheme.primary;
        chart.options.scales.x.ticks.color = nextTheme.muted;
        chart.options.scales.y.ticks.color = nextTheme.muted;
        chart.options.scales.y.grid.color = nextTheme.border;
        chart.update();
    });
})();
