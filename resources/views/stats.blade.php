@extends('layouts.app')

@section('content')
<style>
    :root {
        --bs-primary: #0d6efd;
        --bs-secondary: #6c757d;
    }

    body {
        background-color: #f8f9fa;
    }

    .card {
        transition: transform 0.2s;
        border-radius: 1rem;
    }

    .card:hover {
        transform: translateY(-5px);
    }

    .stat-card {
        background: linear-gradient(145deg, #ffffff, #f8f9fa);
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }

    .chart-container {
        min-height: 350px;
    }

    .metrics-container {
        min-height: 250px;
    }

    .loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(255,255,255,0.8);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 1000;
    }

    .spinner-container {
        padding: 2rem;
        background: white;
        border-radius: 1rem;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
</style>
</head>
<body>
<div class="container-fluid py-4 px-4">
    <div class="row mb-4">
        <div class="col">
            <h2 class="display-6 fw-bold text-primary mb-4">Dashboard Financier</h2>
        </div>
    </div>

    <!-- Loading Indicator -->
    <div id="loadingIndicator" class="loading-overlay d-none">
        <div class="spinner-container">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2 mb-0">Chargement des données...</p>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row g-4 mb-4" id="statsContent"></div>

    <!-- Revenue Overview -->
    <div class="row mb-4">
        <div class="col">
            <h3 class="h4 fw-bold mb-4">Analyse des Revenus et Dépenses</h3>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <!-- Revenue vs Expenses Chart -->
        <div class="col-lg-8">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body">
                    <h5 class="card-title fw-bold mb-3">Revenus vs Dépenses</h5>
                    <p class="card-text text-muted small">Analyse comparative des revenus et des dépenses sur une période donnée.</p>
                    <div id="revenueExpensesChart" class="chart-container"></div>
                </div>
            </div>
        </div>

        <!-- Revenue Metrics -->
        <div class="col-lg-4">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-body">
                    <h5 class="card-title fw-bold mb-3">Métriques de Revenue</h5>
                    <p class="card-text text-muted small">Couverture des performances financières clés.</p>
                    <div id="revenueMetrics" class="metrics-container"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Distribution Analysis -->
    <div class="row mb-4">
        <div class="col">
            <h3 class="h4 fw-bold mb-4">Distribution des Revenus et Dépenses</h3>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h5 class="card-title fw-bold mb-3">Distribution des Revenus</h5>
                    <div id="revenueDistributionChart" class="chart-container"></div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h5 class="card-title fw-bold mb-3">Distribution des Dépenses</h5>
                    <div id="categoryDistributionChart" class="chart-container"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Profit Analysis -->
    <div class="row g-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <h5 class="card-title fw-bold mb-3">Analyse des Profits</h5>
                    <p class="card-text text-muted small">Détail des performances de profit mensuel.</p>
                    <div class="table-responsive" id="profitAnalysisTable"></div>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- Required CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/apexcharts/dist/apexcharts.css">
<script src="{{ asset('assets/apexcharts/dist/apexcharts.min.js') }}"></script>

<!-- Required JS -->
<script src="{{ asset('assets/js/jquery-3.7.1.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    $(document).ready(function() {
        function clearCache() {
            $.ajax({
                url: '/api/clear-cache', // The API endpoint
                method: 'POST',
                success: function(response) {
                    console.log(response.message); // Log success message
                    // Optionally, show an alert
                },
                error: function(xhr) {
                    console.error('Error: ' + xhr.responseJSON.message); // Log error message
                    // Optionally, show an alert
                }
            });
        }

        // Clear cache initially when the page loads
        clearCache();

        // Set interval to clear cache every 30 seconds
        setInterval(clearCache, 30000); // 30000 milliseconds = 30 seconds
    });
    </script>
<script src="{{ asset('assets/js/jquery-3.7.1.min.js') }}"></script>

<script>
$(document).ready(function() {
    let charts = {};
    loadStats();
    setInterval(loadStats, 300000); // Refresh every 5 minutes
    fetchStatistics();
    function loadStats() {
    // Show loading spinner with improved styling
    $('#loadingIndicator').removeClass('d-none');

    // Fetch stats from the API
    $.get('/api/stats')
        .done(handleApiResponse)
        .fail(handleApiError);
        $('#loadingIndicator').addClass('d-none');
}




    function handleApiResponse(data) {
        try {
            updateSummaryCards(data.summary);
            updateRevenueCharts(data.revenue_analysis);
            updateCategoryCharts(data.category_analysis);
            updateProfitAnalysis(data.detailed_metrics.monthly_comparison);
            updateRevenueMetrics(data.revenue_analysis);
        } catch (error) {
            console.error('Error handling API response:', error);
            handleApiError();
        }
    }

    function handleApiError() {
        console.error('Error loading stats');
        $('#statsContent').html('<div class="col-12 text-center alert alert-danger">Erreur de chargement. Veuillez réessayer plus tard.</div>');
    }

    function updateSummaryCards(summary) {
        const cardsHtml = `
            ${['total_revenue', 'total_expenses', 'net_profit', 'total_depts'].map(stat => `
                <div class="col-md-3">
                    <div class="card h-100 shadow border-light">
                        <div class="card-body text-center">
                            <i class="bi bi-${getStatIcon(stat)} mb-3" style="font-size: 2rem;"></i>
                            <h5 class="card-title">${getStatTitle(stat)}</h5>
                            <h3 class="card-text">${formatCurrency(summary[stat])}</h3>
                            <p class="text-muted mb-0">vs Mois Dernier: ${stat.includes('expenses') ? summary.expense_growth : summary.revenue_growth} %</p>
                        </div>
                    </div>
                </div>
            `).join('')}
        `;
        $('#statsContent').html(cardsHtml);
    }

    function getStatIcon(stat) {
        switch(stat) {
            case 'total_revenue': return 'graph-up-arrow';
            case 'total_expenses': return 'cash-stack';
            case 'net_profit': return 'piggy-bank';
            case 'total_depts': return 'exclamation-circle';
            default: return 'circle';
        }
    }

    function getStatTitle(stat) {
        switch(stat) {
            case 'total_revenue': return 'Revenu Total';
            case 'total_expenses': return 'Dépenses Totales';
            case 'net_profit': return 'Profit Net';
            case 'total_depts': return 'Dettes Totales';
            default: return 'Statistique';
        }
    }

    function formatCurrency(value) {
        return value ? parseFloat(value).toLocaleString('fr-FR') + ' Fbu' : 'N/A';
    }

    function updateRevenueMetrics(revenueAnalysis) {
        const metricsHtml = `
            <ul class="list-group">
                <li class="list-group-item">
                    <strong>Moyenne Mensuelle:</strong>
                    ${revenueAnalysis.average_monthly_revenue !== null ? formatCurrency(revenueAnalysis.average_monthly_revenue) : 'N/A'}
                </li>
                <li class="list-group-item">
                    <strong>Meilleur Mois:</strong>
                    ${revenueAnalysis.best_month?.month || 'N/A'} -
                    ${revenueAnalysis.best_month?.amount ? formatCurrency(revenueAnalysis.best_month.amount) : 'N/A'}
                </li>
                <li class="list-group-item">
                    <strong>Croissance Annuelle:</strong>
                    ${revenueAnalysis.yearly_growth !== null ? revenueAnalysis.yearly_growth + '%' : 'N/A'}
                </li>
            </ul>
        `;
        $('#revenueMetrics').html(metricsHtml);
    }

    function updateRevenueCharts(revenueData) {
        const chartOptions = {
            chart: { type: 'line', height: 350 },
            title: { text: 'Revenus vs Dépenses', align: 'left' },
            xaxis: { categories: revenueData.months || [] },
            series: [{
                name: 'Revenus',
                data: revenueData.revenue_trends.map(val => parseFloat(val) || 0)
            }, {
                name: 'Dépenses',
                data: revenueData.expense_trends.map(val => parseFloat(val) || 0)
            }],
            tooltip: {
                shared: true,
                intersect: false
            }
        };

        if (charts.revenueExpensesChart) charts.revenueExpensesChart.destroy();
        charts.revenueExpensesChart = new ApexCharts(document.querySelector("#revenueExpensesChart"), chartOptions);
        charts.revenueExpensesChart.render();
    }

    function updateCategoryCharts(categoryData) {
        if (!categoryData) return;

        const revenueDistribution = categoryData.category_distribution || [];
        const categoryDistribution = categoryData.category_totals || [];

        updatePieChart('revenueDistributionChart', revenueDistribution);
        updatePieChart('categoryDistributionChart', categoryDistribution);
    }

    function updateCategoryCharts(categoryData) {
    if (!categoryData) return;

    // Revenue Distribution Chart
    const revenueData = categoryData.revenue_distribution || [];
    console.log("Revenue data:",revenueData);
    updatePieChart('revenueDistributionChart', revenueData, 'Distribution des Revenus');

    // Expense Distribution Chart
    const expenseData = categoryData.expense_distribution || [];
    updatePieChart('categoryDistributionChart', expenseData, 'Distribution des Dépenses');
}

function updatePieChart(chartId, data, title) {
    const categories = data.map(item => item.category_name).filter(name => name);
    const amounts = data.map(item => parseFloat(item.total_amount) || 0);

    const options = {
        chart: {
            type: 'pie',
            height: 350
        },
        title: {
            text: title,
            align: 'center'
        },
        labels: categories,
        series: amounts,
        colors: generateColors(categories.length),
        tooltip: {
            y: {
                formatter: (val) => `${val} Fbu`
            }
        },
        legend: {
            position: 'bottom'
        },
        responsive: [{
            breakpoint: 480,
            options: {
                chart: {
                    height: 300
                },
                legend: {
                    position: 'bottom'
                }
            }
        }]
    };

    if (charts[chartId]) charts[chartId].destroy();
    charts[chartId] = new ApexCharts(document.querySelector(`#${chartId}`), options);
    charts[chartId].render();
}

    function fetchStatistics() {
        $.ajax({
            url: '/api/stats', // Adjust the URL to your actual API endpoint
            method: 'GET',
            success: function(data) {
                if (data.profit_analysis) {
                    updateProfitAnalysis(data.profit_analysis);
                }
                // Handle other statistics as needed
            },
            error: function(error) {
                console.error('Error fetching statistics:', error);
                $('#profitAnalysisTable').html('<div class="alert alert-danger">Erreur lors de la récupération des données.</div>');
            }
        });
    }

    function updateProfitAnalysis(profitData) {
        if (!profitData || !Array.isArray(profitData) || profitData.length === 0) {
            $('#profitAnalysisTable').html('<div class="alert alert-warning">Aucune donnée disponible.</div>');
            return;
        }

        const tableHtml = `
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Période</th>
                        <th>Revenus</th>
                        <th>Dépenses</th>
                        <th>Profit Net</th>
                    </tr>
                </thead>
                <tbody>
                    ${profitData.map(month => `
                        <tr>
                            <td>${month.category_name || 'N/A'}</td>
                            <td>${month.current_month !== undefined ? formatCurrency(month.current_month) : 'N/A'}</td>
                            <td>${month.previous_month !== undefined ? formatCurrency(month.previous_month) : 'N/A'}</td>
                            <td>${month.difference !== undefined ? formatCurrency(month.difference) : 'N/A'}</td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        `;
        $('#profitAnalysisTable').html(tableHtml);
    }



    function generateColors(count) {
        const colors = [
            '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF',
            '#FF9F40', '#C9CBCF', '#FF6384', '#4BC0C0', '#FF9F40'
        ];
        return colors.slice(0, count);
    }
});

</script>
@endsection
