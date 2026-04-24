(function () {
    const initDashboard = () => {
        const config = window.VizuraConfig?.dashboard;
        if (!config) return;

        var ctx = document.getElementById('sales-chart');
        if (!ctx) return;
        
        // Destroy existing chart if it exists to prevent overlap on Turbo load
        const existingChart = Chart.getChart(ctx);
        if (existingChart) existingChart.destroy();

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: config.labels,
                datasets: [{
                    label: 'Sales',
                    backgroundColor: 'rgba(16, 185, 129, 0.05)',
                    borderColor: '#10b981',
                    borderWidth: 4,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#10b981',
                    pointBorderWidth: 2,
                    pointRadius: 6,
                    pointHoverRadius: 8,
                    data: config.series,
                    fill: true,
                    tension: 0.4,
                }]
            },
            options: {
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#1e293b',
                        padding: 12,
                        titleFont: { size: 14, weight: 'bold' },
                        bodyFont: { size: 14 },
                        displayColors: false,
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { display: true, color: '#f1f5f9', drawBorder: false },
                        ticks: { color: '#94a3b8', font: { size: 11, weight: 'bold' } }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { color: '#94a3b8', font: { size: 11, weight: 'bold' } }
                    }
                },
                responsive: true,
                maintainAspectRatio: false
            }
        });
    };

    initDashboard();
    document.addEventListener('turbo:load', initDashboard);
})();
