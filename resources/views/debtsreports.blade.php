@extends('layouts.app')

@section('content')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Base Card Styling */
        .card {
            margin-bottom: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            border: none;
            border-radius: 12px;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
        }

        .dashboard-card {
            border-left: 4px solid #0d6efd;
            height: 100%;
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
        }

        /* Header Styling */
        .card-header {
            background: linear-gradient(90deg, #0d6efd 0%, #4680ff 100%);
            color: white;
            border-radius: 12px 12px 0 0;
            padding: 1rem 1.5rem;
        }

        /* Dashboard Icons */
        .dashboard-icon {
            font-size: 2.2rem;
            color: #0d6efd;
            opacity: 0.9;
            transition: transform 0.3s ease;
        }

        .dashboard-card:hover .dashboard-icon {
            transform: scale(1.1);
        }

        /* Spinner Overlay */
        .spinner-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(255, 255, 255, 0.8);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 1000;
            border-radius: 12px;
        }

        /* Chart Containers */
        #debtTrendsChart, #paymentPerformanceChart {
            height: 300px;
        }

        #debtTrendsChartContainer {
            height: 400px;
            width: 100%;
            padding: 15px;
        }

        /* Status Colors */
        .debt-status-paid {
            color: #198754;
            font-weight: 600;
        }

        .debt-status-pending {
            color: #ffc107;
            font-weight: 600;
        }

        .debt-overdue {
            color: #dc3545;
            font-weight: 600;
        }

        /* Buttons */
        .btn {
            border-radius: 8px;
            padding: 0.5rem 1rem;
            transition: all 0.3s ease;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
        }

        /* Table Styling */
        .table {
            border-radius: 8px;
            overflow: hidden;
        }

        .table th {
            background: #f1f3f5;
            border-bottom: 2px solid #dee2e6;
            padding: 1rem;
        }

        .table td {
            padding: 1rem;
            vertical-align: middle;
        }

        .table-hover tbody tr:hover {
            background-color: rgba(13, 110, 253, 0.05);
            transition: background-color 0.2s ease;
        }

        /* Modal Styling */
        .modal-content {
            border-radius: 12px;
            border: none;
            box-shadow: 0 5px 25px rgba(0, 0, 0, 0.2);
        }

        .modal-header {
            background: linear-gradient(90deg, #0d6efd 0%, #4680ff 100%);
            color: white;
            border-radius: 12px 12px 0 0;
        }

        .nav-tabs .nav-link {
            border: none;
            padding: 0.75rem 1.5rem;
            color: #6c757d;
            font-weight: 500;
        }

        .nav-tabs .nav-link.active {
            color: #0d6efd;
            border-bottom: 3px solid #0d6efd;
            background: transparent;
        }

        /* Additional Modern Touches */
        h1 {
            font-weight: 700;
            background: linear-gradient(90deg, #0d6efd 0%, #4680ff 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        h5.card-title {
            font-weight: 600;
            color: #343a40;
        }

        .container-fluid {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 2rem;
        }

        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .dashboard-card {
                margin-bottom: 15px;
            }

            .card-header {
                padding: 0.75rem 1rem;
            }

            .dashboard-icon {
                font-size: 1.8rem;
            }
        }
    </style>
    <div class="container-fluid py-4">
        <div class="row mb-4">
            <div class="col-12">
                <h1 class="mb-3">Tableau de Bord de Gestion des Dettes</h1>
                <p class="text-muted">Vue d'ensemble de la situation des dettes et des paiements</p>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card dashboard-card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-8">
                                <h5 class="card-title">Total des Dettes</h5>
                                <h2 id="totalDebtAmount" class="fw-bold">--</h2>
                            </div>
                            <div class="col-4 text-end">
                                <i class="fas fa-money-bill-wave dashboard-icon"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card dashboard-card" style="border-left-color: #20c997;">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-8">
                                <h5 class="card-title">Dettes Remboursées</h5>
                                <h2 id="paidDebtsCount" class="fw-bold">--</h2>
                            </div>
                            <div class="col-4 text-end">
                                <i class="fas fa-check-circle dashboard-icon" style="color: #20c997;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card dashboard-card" style="border-left-color: #fd7e14;">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-8">
                                <h5 class="card-title">Dettes en Attente</h5>
                                <h2 id="pendingDebtsCount" class="fw-bold">--</h2>
                            </div>
                            <div class="col-4 text-end">
                                <i class="fas fa-hourglass-half dashboard-icon" style="color: #fd7e14;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card dashboard-card" style="border-left-color: #dc3545;">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-8">
                                <h5 class="card-title">Dettes en Souffrance</h5>
                                <h2 id="overdueDebtsCount" class="fw-bold">--</h2>
                            </div>
                            <div class="col-4 text-end">
                                <i class="fas fa-exclamation-triangle dashboard-icon" style="color: #dc3545;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts Row -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Tendances des Dettes (6 derniers mois)</h5>
                        <div class="position-relative">
                            <button class="btn btn-sm btn-outline-secondary" id="refreshTrendsBtn">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                            <div class="spinner-overlay d-none" id="trendSpinner">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Chargement...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="debtTrendsChartContainer">
                            <canvas id="debtTrendsChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Performance des Paiements</h5>
                        <div class="position-relative">
                            <button class="btn btn-sm btn-outline-secondary" id="refreshPerformanceBtn">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                            <div class="spinner-overlay d-none" id="performanceSpinner">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Chargement...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <h6 class="text-muted">Taux de Remboursement</h6>
                                        <h3 id="overallRepaymentRate" class="mb-0">--%</h3>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <h6 class="text-muted">Montant Total Reçu</h6>
                                        <h3 id="totalPaymentsReceived" class="mb-0">--</h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <canvas id="paymentPerformanceChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Clients et Payments Tables -->
        <div class="row mb-4">
            <div class="col-md-7">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Dettes par Client</h5>
                        <div class="position-relative">
                            <button class="btn btn-sm btn-outline-secondary" id="refreshCustomersBtn">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                            <div class="spinner-overlay d-none" id="customerSpinner">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Chargement...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Client</th>
                                        <th>Total Dettes</th>
                                        <th>Nb Dettes</th>
                                        <th>Payées</th>
                                        <th>En Attente</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="customerDebtsList">
                                    <tr>
                                        <td colspan="6" class="text-center">Chargement des données...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-5">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Paiements Récents</h5>
                        <div class="position-relative">
                            <select class="form-select form-select-sm d-inline-block w-auto" id="paymentDaysFilter">
                                <option value="7">7 jours</option>
                                <option value="30" selected>30 jours</option>
                                <option value="90">90 jours</option>
                            </select>
                            <button class="btn btn-sm btn-outline-secondary ms-2" id="refreshPaymentsBtn">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                            <div class="spinner-overlay d-none" id="paymentSpinner">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Chargement...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Client</th>
                                        <th>Montant</th>
                                        <th>Méthode</th>
                                    </tr>
                                </thead>
                                <tbody id="recentPaymentsList">
                                    <tr>
                                        <td colspan="4" class="text-center">Chargement des données...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Overdue Debts -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Dettes en Souffrance</h5>
                        <div class="position-relative">
                            <select class="form-select form-select-sm d-inline-block w-auto" id="overdueDaysFilter">
                                <option value="15">+15 jours</option>
                                <option value="30" selected>+30 jours</option>
                                <option value="60">+60 jours</option>
                                <option value="90">+90 jours</option>
                            </select>
                            <button class="btn btn-sm btn-outline-secondary ms-2" id="refreshOverdueBtn">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                            <div class="spinner-overlay d-none" id="overdueSpinner">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Chargement...</span>
                                </div>
                            </div>
                            <a href="/api/reports/export-debts-csv" class="btn btn-sm btn-success ms-2">
                                <i class="fas fa-file-csv"></i> Exporter CSV
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Client</th>
                                        <th>Montant</th>
                                        <th>Date de création</th>
                                        <th>Jours en retard</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="overdueDebtsList">
                                    <tr>
                                        <td colspan="6" class="text-center">Chargement des données...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Customer Detail Modal -->
    <div class="modal fade" id="customerDetailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Détails du Client</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="spinner-border text-primary d-flex mx-auto" id="customerDetailSpinner" role="status">
                        <span class="visually-hidden">Chargement...</span>
                    </div>
                    <div id="customerDetailContent" class="d-none">
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <h5 id="customerName" class="mb-3">--</h5>
                                        <p><strong>Contact:</strong> <span id="customerContact">--</span></p>
                                        <p><strong>Dette Actuelle:</strong> <span id="customerCurrentDebt">--</span></p>
                                        <p><strong>Total Payé:</strong> <span id="customerTotalPaid">--</span></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <ul class="nav nav-tabs" id="customerTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="debts-tab" data-bs-toggle="tab" data-bs-target="#debts-tab-pane" type="button" role="tab" aria-controls="debts-tab-pane" aria-selected="true">Historique des Dettes</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="payments-tab" data-bs-toggle="tab" data-bs-target="#payments-tab-pane" type="button" role="tab" aria-controls="payments-tab-pane" aria-selected="false">Historique des Paiements</button>
                            </li>
                        </ul>
                        <div class="tab-content p-3 border border-top-0 rounded-bottom" id="customerTabsContent">
                            <div class="tab-pane fade show active" id="debts-tab-pane" role="tabpanel" aria-labelledby="debts-tab" tabindex="0">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Montant</th>
                                                <th>Statut</th>
                                                <th>Date</th>
                                                <th>Détails Transaction</th>
                                            </tr>
                                        </thead>
                                        <tbody id="customerDebtHistory">
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="payments-tab-pane" role="tabpanel" aria-labelledby="payments-tab" tabindex="0">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Montant</th>
                                                <th>Date</th>
                                                <th>Méthode</th>
                                                <th>ID Dette</th>
                                            </tr>
                                        </thead>
                                        <tbody id="customerPaymentHistory">
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                </div>
            </div>
        </div>
    </div>

  <!-- Include jQuery and Bootstrap JS -->
<script src="{{ asset('assets\js\jquery-3.7.1.min.js') }}"></script>
<script src="{{ asset('assets\vendor\DataTables\datatables.min.css') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // API Base URL - Change this to match your API endpoint
        const API_BASE_URL = '/api/reports';

        // Format currency
        const formatCurrency = (amount) => {
            return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'XOF' }).format(amount);
        };

        // Format date
        const formatDate = (dateString) => {
            return new Date(dateString).toLocaleDateString('fr-FR', {
                year: 'numeric',
                month: 'short',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        };

        // Charts
        let debtTrendsChart;
        //let paymentPerformanceChart;

        // Load data functions
        const loadSummaryData = () => {
            fetch(`${API_BASE_URL}/debt-summary`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('totalDebtAmount').textContent = formatCurrency(data.total_debt_amount);
                    document.getElementById('paidDebtsCount').textContent = data.paid_debts_count;
                    document.getElementById('pendingDebtsCount').textContent = data.pending_debts_count;
                })
                .catch(error => console.error('Error loading summary data:', error));
        };

        const loadDebtTrends = () => {
    const spinner = document.getElementById('trendSpinner');
    spinner.classList.remove('d-none');

    // Set specific height to the canvas
    const chartCanvas = document.getElementById('debtTrendsChart');
    chartCanvas.style.height = '400px'; // Adjustable height
    chartCanvas.style.width = '100%';   // Full width

    fetch(`${API_BASE_URL}/debt-trends?months=6`)
        .then(response => response.json())
        .then(data => {
            const months = data.trends.map(item => item.month),
                  newDebts = data.trends.map(item => item.new_debts),
                  paymentsReceived = data.trends.map(item => item.payments_received),
                  netChange = data.trends.map(item => item.net_change);

            if (debtTrendsChart) debtTrendsChart.destroy();

            debtTrendsChart = new Chart(chartCanvas.getContext('2d'), {
                type: 'line',
                data: {
                    labels: months,
                    datasets: [{
                        label: 'Nouvelles Dettes',
                        data: newDebts,
                        borderColor: '#dc3545',
                        backgroundColor: 'rgba(220, 53, 69, 0.1)',
                        tension: 0.1,
                        fill: true
                    }, {
                        label: 'Paiements Reçus',
                        data: paymentsReceived,
                        borderColor: '#198754',
                        backgroundColor: 'rgba(25, 135, 84, 0.1)',
                        tension: 0.1,
                        fill: true
                    }, {
                        label: 'Changement Net',
                        data: netChange,
                        borderColor: '#0d6efd',
                        backgroundColor: 'rgba(13, 110, 253, 0.1)',
                        tension: 0.1,
                        borderDash: [5, 5],
                        fill: false
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false, // Allows custom height
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: context => context.dataset.label + ': ' + formatCurrency(context.raw)
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: value => formatCurrency(value)
                            }
                        }
                    }
                }
            });

            spinner.classList.add('d-none');
        })
        .catch(error => {
            console.error('Error loading debt trends:', error);
            spinner.classList.add('d-none');
        });
};
       // Chart instance reference (declared in outer scope)
let paymentPerformanceChart = null;

// Configuration constants
const CHART_COLORS = {
  success: 'rgba(25, 135, 84, 0.7)',
  good: 'rgba(13, 110, 253, 0.7)',
  warning: 'rgba(255, 193, 7, 0.7)',
  danger: 'rgba(220, 53, 69, 0.7)',
  borderSuccess: 'rgb(25, 135, 84)',
  borderGood: 'rgb(13, 110, 253)',
  borderWarning: 'rgb(255, 193, 7)',
  borderDanger: 'rgb(220, 53, 69)'
};

const API_ENDPOINTS = {
  PAYMENT_PERFORMANCE: 'payment-performance'
};

// Helper functions
const toggleSpinner = (show = true) => {
  const spinner = document.getElementById('performanceSpinner');
  if (spinner) spinner.classList.toggle('d-none', !show);
};

const showErrorMessage = (message) => {
  const errorEl = document.getElementById('performanceError');
  if (errorEl) {
    errorEl.textContent = message;
    errorEl.classList.remove('d-none');
  }
};

const clearErrorMessage = () => {
  const errorEl = document.getElementById('performanceError');
  if (errorEl) errorEl.classList.add('d-none');
};

const getBarColor = (rate) => {
  if (rate >= 90) return { bg: CHART_COLORS.success, border: CHART_COLORS.borderSuccess };
  if (rate >= 70) return { bg: CHART_COLORS.good, border: CHART_COLORS.borderGood };
  if (rate >= 50) return { bg: CHART_COLORS.warning, border: CHART_COLORS.borderWarning };
  return { bg: CHART_COLORS.danger, border: CHART_COLORS.borderDanger };
};

const initializeChart = (monthlyData) => {
  const ctx = document.getElementById('paymentPerformanceChart');
  if (!ctx) return;

  if (paymentPerformanceChart) {
    paymentPerformanceChart.destroy();
  }

  const months = monthlyData.map(item => item.month);
  const rates = monthlyData.map(item => item.repayment_rate);

  paymentPerformanceChart = new Chart(ctx.getContext('2d'), {
    type: 'bar',
    data: {
      labels: months,
      datasets: [{
        label: 'Taux de Remboursement (%)',
        data: rates,
        backgroundColor: rates.map(rate => getBarColor(rate).bg),
        borderColor: rates.map(rate => getBarColor(rate).border),
        borderWidth: 1
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: true, // Changed to maintain aspect ratio
      aspectRatio: 2, // Higher ratio reduces height (width/height)
      plugins: {
        tooltip: {
          callbacks: {
            label: (context) => `${context.dataset.label}: ${context.raw}%`
          }
        }
      },
      scales: {
        y: {
          beginAtZero: true,
          max: 100,
          ticks: {
            callback: (value) => `${value}%`
          }
        }
      }
    }
  });
};
// Main function
const loadPaymentPerformance = async () => {
  try {
    toggleSpinner(true);
    clearErrorMessage();

    const response = await fetch(`${API_BASE_URL}/${API_ENDPOINTS.PAYMENT_PERFORMANCE}`);

    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }

    const data = await response.json();

    // Validate response structure
    if (!data?.monthly_performance || !Array.isArray(data.monthly_performance)) {
      throw new Error('Invalid data structure from API');
    }

    // Update DOM elements
    document.getElementById('overallRepaymentRate').textContent = `${data.overall_repayment_rate}%`;
    document.getElementById('totalPaymentsReceived').textContent = formatCurrency(data.total_payments_received);

    // Initialize chart
    initializeChart(data.monthly_performance);

  } catch (error) {
    console.error('Payment performance load error:', error);
    showErrorMessage('Failed to load payment performance data. Please try again later.');
  } finally {
    toggleSpinner(false);
  }
};



        const loadCustomerDebts = () => {
            const spinner = document.getElementById('customerSpinner');
            spinner.classList.remove('d-none');

            fetch(`${API_BASE_URL}/debts-by-customer`)
                .then(response => response.json())
                .then(data => {
                    const customersList = document.getElementById('customerDebtsList');
                    if (!data.customers || data.customers.length === 0) {
                        customersList.innerHTML = '<tr><td colspan="6" class="text-center">Aucune dette trouvée</td></tr>';
                        spinner.classList.add('d-none');
                        return;
                    }

                    let html = '';
                    data.customers.forEach(customer => {
                        html += `
                            <tr>
                                <td>${customer.name}</td>
                                <td>${formatCurrency(customer.total_debt)}</td>
                                <td>${customer.debt_count}</td>
                                <td><span class="badge bg-success">${customer.paid_count}</span></td>
                                <td><span class="badge bg-warning text-dark">${customer.pending_count}</span></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary view-customer" data-customer-id="${customer.id}">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </td>
                            </tr>
                        `;
                    });

                    customersList.innerHTML = html;

                    // Attach event listeners to view customer buttons
                    document.querySelectorAll('.view-customer').forEach(button => {
                        button.addEventListener('click', function() {
                            const customerId = this.getAttribute('data-customer-id');
                            openCustomerDetail(customerId);
                        });
                    });

                    spinner.classList.add('d-none');
                })
                .catch(error => {
                    console.error('Error loading customer debts:', error);
                    spinner.classList.add('d-none');
                });
        };

        const loadRecentPayments = () => {
            const spinner = document.getElementById('paymentSpinner');
            spinner.classList.remove('d-none');

            const days = document.getElementById('paymentDaysFilter').value;

            fetch(`${API_BASE_URL}/recent-payments?days=${days}`)
                .then(response => response.json())
                .then(data => {
                    const paymentsList = document.getElementById('recentPaymentsList');
                    if (!data.payments || data.payments.length === 0) {
                        paymentsList.innerHTML = '<tr><td colspan="4" class="text-center">Aucun paiement récent</td></tr>';
                        spinner.classList.add('d-none');
                        return;
                    }

                    let html = '';
                    data.payments.forEach(payment => {
                        html += `
                            <tr>
                                <td>${formatDate(payment.payment_date)}</td>
                                <td>${payment.customer_name}</td>
                                <td>${formatCurrency(payment.amount)}</td>
                                <td><span class="badge bg-info">${payment.payment_method}</span></td>
                            </tr>
                        `;
                    });

                    paymentsList.innerHTML = html;
                    spinner.classList.add('d-none');
                })
                .catch(error => {
                    console.error('Error loading recent payments:', error);
                    spinner.classList.add('d-none');
                });
        };

        const loadOverdueDebts = () => {
        const spinner = document.getElementById('overdueSpinner');
        spinner.classList.remove('d-none');

        const days = document.getElementById('overdueDaysFilter').value;

        fetch(`${API_BASE_URL}/overdue-debts?days=${days}`)
            .then(response => response.json())
            .then(data => {
                document.getElementById('overdueDebtsCount').textContent = data.count;

                const debtsList = document.getElementById('overdueDebtsList');
                if (data.count === 0) {
                    debtsList.innerHTML = '<tr><td colspan="6" class="text-center">Aucune dette en souffrance</td></tr>';
                    spinner.classList.add('d-none');
                    return;
                }

                let html = '';
                data.overdue_debts.forEach(debt => {
                    let severityClass = '';
                    if (debt.days_overdue > 90) {
                        severityClass = 'severity-90';
                    } else if (debt.days_overdue > 60) {
                        severityClass = 'severity-60';
                    } else if (debt.days_overdue > 30) {
                        severityClass = 'severity-30';
                    } else {
                        severityClass = 'severity-15';
                    }

                    html += `
                        <tr class="${severityClass}">
                            <td>${debt.id}</td>
                            <td>${debt.customer_name}</td>
                            <td>${formatCurrency(debt.amount)}</td>
                            <td>${formatDate(debt.created_at)}</td>
                            <td><span class="badge bg-danger">${debt.days_overdue} jours</span></td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary"
                                        data-bs-toggle="tooltip"
                                        title="Voir les détails">
                                    <i class="fas fa-search"></i>
                                </button>
                            </td>
                        </tr>
                    `;
                });

                debtsList.innerHTML = html;
                spinner.classList.add('d-none');

                // Initialize tooltips
                const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                tooltipTriggerList.map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
            })
            .catch(error => {
                console.error('Error loading overdue debts:', error);
                spinner.classList.add('d-none');
            });
    };

    // Add smooth transitions for modal
    $('#customerDetailModal').on('show.bs.modal', function(e) {
        const spinner = document.getElementById('customerDetailSpinner');
        const content = document.getElementById('customerDetailContent');
        spinner.classList.remove('d-none');
        content.classList.add('d-none');

        // Simulated loading delay
        setTimeout(() => {
            spinner.classList.add('d-none');
            content.classList.remove('d-none');
        }, 500);
    });

    // Initialize all loaders
    document.addEventListener('DOMContentLoaded', function() {
        loadSummaryData();
        loadDebtTrends();
        loadPaymentPerformance();
        loadCustomerDebts();
        loadRecentPayments();
        loadOverdueDebts();

        // Add refresh button listeners
        document.getElementById('refreshTrendsBtn').addEventListener('click', loadDebtTrends);
        document.getElementById('refreshPerformanceBtn').addEventListener('click', loadPaymentPerformance);
        document.getElementById('refreshCustomersBtn').addEventListener('click', loadCustomerDebts);
        document.getElementById('refreshPaymentsBtn').addEventListener('click', loadRecentPayments);
        document.getElementById('refreshOverdueBtn').addEventListener('click', loadOverdueDebts);
    });

    // Enhanced currency formatting with error handling

</script>
@endsection
