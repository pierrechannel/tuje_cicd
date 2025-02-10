<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion Imprimerie</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css">
    <style>
        :root {
            --sidebar-width: 250px;
            --header-height: 60px;
            --primary-color: #2c3e50;
            --secondary-color: #34495e;
        }

        body {
            min-height: 100vh;
            background-color: #f8f9fa;
        }

        /* Header Styles */
        .main-header {
            height: var(--header-height);
            background: white;
            position: fixed;
            top: 0;
            right: 0;
            left: var(--sidebar-width);
            z-index: 1020;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 0 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        /* Sidebar Styles */
        #sidebar {
            width: var(--sidebar-width);
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            background: var(--primary-color);
            color: white;
            transition: all 0.3s;
            z-index: 1030;
            padding-top: 20px;
        }

        .sidebar-header {
            padding: 20px;
            background: var(--secondary-color);
            margin-bottom: 20px;
        }

        /* Content Styles */
        #content {
            margin-left: var(--sidebar-width);
            margin-top: var(--header-height);
            padding: 20px;
            transition: all 0.3s;
        }

        .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 12px 20px;
            transition: all 0.3s;
            border-radius: 0;
            margin: 2px 0;
        }

        .nav-link:hover {
            color: white;
            background: rgba(255,255,255,0.1);
            padding-left: 25px;
        }

        .nav-link.active {
            background: rgba(255,255,255,0.2);
            color: white;
            border-left: 4px solid #3498db;
        }

        .nav-link i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }

        /* Card Styles */
        .stats-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: all 0.3s;
            border: none;
        }

        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .table-container {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        /* Status Badge Styles */
        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.85em;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .status-paid {
            background-color: #d4edda;
            color: #155724;
        }

        .status-partial {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-unpaid {
            background-color: #f8d7da;
            color: #721c24;
        }

        /* Table Styles */
        .table thead th {
            background-color: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
            font-weight: 600;
        }

        .table td {
            vertical-align: middle;
        }

        /* Filter Styles */
        .filter-container {
            background: white;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        /* Modal Styles */
        .modal-content {
            border-radius: 10px;
            border: none;
        }

        .modal-header {
            background-color: #f8f9fa;
            border-radius: 10px 10px 0 0;
        }

        /* Custom Form Styles */
        .form-control, .form-select {
            border-radius: 8px;
            border: 1px solid #dee2e6;
            padding: 0.6rem 1rem;
        }

        .form-control:focus, .form-select:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
        }

        /* Button Styles */
        .btn {
            border-radius: 8px;
            padding: 0.6rem 1.2rem;
            font-weight: 500;
        }

        .btn-icon {
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        /* Animation */
        .fade-enter {
            opacity: 0;
            transform: translateY(20px);
        }

        .fade-enter-active {
            opacity: 1;
            transform: translateY(0);
            transition: all 0.3s;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div id="sidebar">
        <div class="sidebar-header">
            <h4 class="m-0">Gestion Imprimerie</h4>
            <small class="text-muted">Système de gestion</small>
        </div>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link active" href="#dashboard" data-bs-toggle="tab">
                    <i class="bi bi-speedometer2"></i> Tableau de bord
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#transactions" data-bs-toggle="tab">
                    <i class="bi bi-cart"></i> Transactions
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#debts" data-bs-toggle="tab">
                    <i class="bi bi-currency-euro"></i> Dettes
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#stats" data-bs-toggle="tab">
                    <i class="bi bi-graph-up"></i> Statistiques
                </a>
            </li>
        </ul>
    </div>

    <!-- Header -->
    <header class="main-header">
        <div class="d-flex align-items-center">
            <div class="search-box">
                <div class="input-group">
                    <span class="input-group-text bg-transparent border-end-0">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" class="form-control border-start-0" placeholder="Rechercher...">
                </div>
            </div>
        </div>
        <div class="d-flex align-items-center gap-3">
            <button class="btn btn-outline-secondary btn-icon">
                <i class="bi bi-bell"></i>
            </button>
            <button class="btn btn-outline-secondary btn-icon">
                <i class="bi bi-gear"></i>
            </button>
            <div class="dropdown">
                <button class="btn btn-outline-secondary dropdown-toggle btn-icon" data-bs-toggle="dropdown">
                    <i class="bi bi-person-circle"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="#"><i class="bi bi-person me-2"></i>Profil</a></li>
                    <li><a class="dropdown-item" href="#"><i class="bi bi-box-arrow-right me-2"></i>Déconnexion</a></li>
                </ul>
            </div>
        </div>
    </header>

    <!-- Content -->
    <div id="content">
        <div class="tab-content">
            <!-- Dashboard Tab -->
            <div class="tab-pane fade show active" id="dashboard">
                <div class="row g-4 mb-4">
                    <div class="col-md-3">
                        <div class="stats-card h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="card-subtitle text-muted">Ventes totales</h6>
                                    <i class="bi bi-graph-up text-primary fs-4"></i>
                                </div>
                                <h3 class="card-title mb-0" id="totalSales">0€</h3>
                                <small class="text-success">
                                    <i class="bi bi-arrow-up"></i> +5.3% cette semaine
                                </small>
                            </div>
                        </div>
                    </div>
                    <!-- Add more dashboard cards... -->
                </div>

                <div class="row">
                    <div class="col-md-8">
                        <div class="table-container">
                            <h5 class="mb-4">Transactions récentes</h5>
                            <div class="table-responsive">
                                <table class="table" id="recentTransactionsTable">
                                    <!-- Table content -->
                                </table>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="table-container">
                            <h5 class="mb-4">Dettes récentes</h5>
                            <div id="recentDebts">
                                <!-- Recent debts content -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Other tabs remain similar but with enhanced styling -->
            <!-- ... -->
        </div>
    </div>

    <!-- Enhanced Transaction Modal -->
    <div class="modal fade" id="transactionModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-plus-circle me-2"></i>
                        Nouvelle Transaction
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="transactionForm" class="needs-validation" novalidate>
                        <!-- Enhanced form with better validation and UI -->
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-2"></i>Annuler
                    </button>
                    <button type="button" class="btn btn-primary" id="saveTransaction">
                        <i class="bi bi-save me-2"></i>Enregistrer
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/moment/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>


    <script>
        // State management
        const state = {
            customers: [],
            services: [],
            transactions: [],
            debts: [],
            stats: {},
            filters: {
                dateRange: null,
                searchTerm: '',
                status: 'all'
            }
        };

        // API Service
        const api = {
    baseUrl: '/api',

    async request(endpoint, options = {}) {
        console.log(`Requesting ${endpoint} with options:`, options);
        try {
            const response = await fetch(`${this.baseUrl}${endpoint}`, {
                headers: {
                    'Content-Type': 'application/json',
                },
                ...options
            });

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const data = await response.json();
            console.log(`Response from ${endpoint}:`, data);
            return data;
        } catch (error) {
            console.error(`API Error: ${error.message}`);
            throw error;
        }
    },

    // API endpoints
    customers: {
        getAll: () => api.request('/customers'),
        create: (data) => api.request('/customers', {
            method: 'POST',
            body: JSON.stringify(data)
        })
    },

    services: {
        getAll: () => api.request('/services'), // Définir la méthode pour obtenir tous les services
    },

    transactions: {
        getAll: () => api.request('/transactions'),
        create: (data) => api.request('/transactions', {
            method: 'POST',
            body: JSON.stringify(data)
        }),
        getById: (id) => api.request(`/transactions/${id}`),
        update: (id, data) => api.request(`/transactions/${id}`, {
            method: 'PUT',
            body: JSON.stringify(data)
        })
    },

    debts: {
        getAll: () => api.request('/debts'),
        payDebt: (id, amount) => api.request(`/debts/${id}/payment`, {
            method: 'PUT',
            body: JSON.stringify({ amount_paid: amount })
        })
    },

    stats: {
        get: () => api.request('/stats')
    }
};

        // UI Components
        const UI = {
            elements: {
                transactionTable: document.querySelector('#transactionsTable'),
                debtsTable: document.querySelector('#debtsTable'),
                statsContent: document.querySelector('#statsContent'),
                searchInput: document.querySelector('#searchInput'),
                dateRangePicker: document.querySelector('#dateRangePicker'),
                transactionForm: document.querySelector('#transactionForm'),
                customerSelect: document.querySelector('select[name="customer_id"]'),
                serviceSelect: document.querySelector('select[name="service_id"]')
            },

            templates: {
                transactionRow: (transaction) => `
                    <tr data-id="${transaction.id}">
                        <td>${new Date(transaction.created_at).toLocaleDateString()}</td>
                        <td>${transaction.customer.name}</td>
                        <td>${transaction.service.name}</td>
                        <td>${transaction.quantity}</td>
                        <td>${transaction.total_amount.toLocaleString()}€</td>
                        <td>${transaction.amount_paid.toLocaleString()}€</td>
                        <td>
                            <span class="status-badge ${UI.helpers.getStatusClass(transaction.payment_status)}">
                                <i class="${UI.helpers.getStatusIcon(transaction.payment_status)}"></i>
                                ${UI.helpers.getStatusText(transaction.payment_status)}
                            </span>
                        </td>
                        <td>
                            <div class="btn-group">
                                <button class="btn btn-sm btn-outline-primary view-transaction">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-success edit-transaction">
                                    <i class="bi bi-pencil"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                `,

                statsCard: (title, value, icon, trend = null) => `
                    <div class="col-md-3">
                        <div class="stats-card h-100">
                            <div class="card-body">
                                <div class="icon-container mb-3">
                                    <i class="bi ${icon} fs-4"></i>
                                </div>
                                <h6 class="text-muted mb-2">${title}</h6>
                                <h3 class="mb-0">${value}</h3>
                                ${trend ? `
                                    <small class="${trend.type === 'up' ? 'text-success' : 'text-danger'}">
                                        <i class="bi bi-arrow-${trend.type}"></i>
                                        ${trend.value}% cette semaine
                                    </small>
                                ` : ''}
                            </div>
                        </div>
                    </div>
                `
            },

            helpers: {
                getStatusClass: (status) => ({
                    'paid': 'status-paid',
                    'partial': 'status-partial',
                    'unpaid': 'status-unpaid'
                })[status],

                getStatusIcon: (status) => ({
                    'paid': 'bi-check-circle',
                    'partial': 'bi-clock',
                    'unpaid': 'bi-exclamation-circle'
                })[status],

                getStatusText: (status) => ({
                    'paid': 'Payé',
                    'partial': 'Partiel',
                    'unpaid': 'Non payé'
                })[status],

                showLoading: (element) => {
                    element.classList.add('loading-skeleton');
                },

                hideLoading: (element) => {
                    element.classList.remove('loading-skeleton');
                },

                showNotification: (message, type = 'success') => {
                    const toast = document.createElement('div');
                    toast.className = `toast show position-fixed top-0 end-0 m-3 bg-${type}`;
                    toast.innerHTML = `
                        <div class="toast-header">
                            <strong class="me-auto">Notification</strong>
                            <button type="button" class="btn-close" data-bs-dismiss="toast"></button>
                        </div>
                        <div class="toast-body">${message}</div>
                    `;
                    document.body.appendChild(toast);
                    setTimeout(() => toast.remove(), 3000);
                }
            }
        };

        // Event Handlers
        const EventHandlers = {
            async handleTransactionSubmit(event) {
                event.preventDefault();
                const form = event.target;
                const formData = new FormData(form);

                try {
                    UI.helpers.showLoading(form);
                    const response = await api.transactions.create(Object.fromEntries(formData));
                    UI.helpers.showNotification('Transaction enregistrée avec succès');
                    await DataManager.refreshAll();
                    bootstrap.Modal.getInstance(document.querySelector('#transactionModal')).hide();
                    form.reset();
                } catch (error) {
                    UI.helpers.showNotification('Erreur lors de l\'enregistrement', 'danger');
                } finally {
                    UI.helpers.hideLoading(form);
                }
            },

            async handleDebtPayment(event) {
                const button = event.target.closest('.pay-debt');
                if (!button) return;

                const debtId = button.dataset.id;
                const amount = prompt('Entrez le montant du paiement:');

                if (!amount) return;

                try {
                    UI.helpers.showLoading(button);
                    await api.debts.payDebt(debtId, parseFloat(amount));
                    UI.helpers.showNotification('Paiement enregistré avec succès');
                    await DataManager.refreshAll();
                } catch (error) {
                    UI.helpers.showNotification('Erreur lors du paiement', 'danger');
                } finally {
                    UI.helpers.hideLoading(button);
                }
            },

            handleSearch: debounce((event) => {
                state.filters.searchTerm = event.target.value.toLowerCase();
                DataManager.filterTransactions();
            }, 300)
        };

        // Data Management
        const DataManager = {
            async refreshAll() {
                try {
                    console.log('Fetching data...');
                    const [customers, services, transactions, debts, stats] = await Promise.all([
                        api.customers.getAll(),
                        api.services.getAll(),
                        api.transactions.getAll(),
                        api.debts.getAll(),
                        api.stats.get()
                    ]);

                    console.log('Data fetched successfully:', { customers, services, transactions, debts, stats });

                    Object.assign(state, {
                        customers,
                        services,
                        transactions,
                        debts,
                        stats
                    });

                    this.updateUI();
                } catch (error) {
                    console.error('Error fetching data:', error);
                    UI.helpers.showNotification('Erreur lors du chargement des données', 'danger');
                }
            },



            updateUI() {
    console.log('Updating UI with state:', state);

    // Update transactions table
    const filteredTransactions = this.filterTransactions();
    if (UI.elements.transactionTable) {
        UI.elements.transactionTable.innerHTML = filteredTransactions
            .map(UI.templates.transactionRow)
            .join('');
    } else {
        console.error("transactionTable element not found");
    }

    // Update stats
    if (UI.elements.statsContent) {
        UI.elements.statsContent.innerHTML = [
            { title: 'Ventes totales', value: `${state.stats.total_sales}€`, icon: 'bi-graph-up', trend: { type: 'up', value: 5.3 } },
            { title: 'Dettes en attente', value: `${state.stats.pending_debts}€`, icon: 'bi-credit-card' },
            { title: 'Services', value: state.stats.services_count, icon: 'bi-printer' },
            { title: 'Transactions aujourd\'hui', value: state.stats.transactions_today, icon: 'bi-calendar-check' }
        ].map(stat => UI.templates.statsCard(stat.title, stat.value, stat.icon, stat.trend)).join('');
    } else {
        console.error("statsContent element not found");
    }

    console.log('Updated Stats:', state.stats);
},

            filterTransactions() {
                return state.transactions.filter(transaction => {
                    const matchesSearch = !state.filters.searchTerm ||
                        transaction.customer.name.toLowerCase().includes(state.filters.searchTerm) ||
                        transaction.service.name.toLowerCase().includes(state.filters.searchTerm);

                    const matchesDate = !state.filters.dateRange ||
                        (new Date(transaction.created_at) >= state.filters.dateRange[0] &&
                         new Date(transaction.created_at) <= state.filters.dateRange[1]);

                    const matchesStatus = state.filters.status === 'all' ||
                        transaction.payment_status === state.filters.status;

                    return matchesSearch && matchesDate && matchesStatus;
                });
            }
        };

        // Utility Functions
        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }
        UI.templates.transactionRow = (transaction) => `
    <tr data-id="${transaction.id}">
        <td>${new Date(transaction.created_at).toLocaleDateString()}</td>
        <td>${transaction.customer.name || 'N/A'}</td>
        <td>${transaction.service.name || 'N/A'}</td>
        <td>${transaction.quantity || 0}</td>
        <td>${(transaction.total_amount || 0).toLocaleString()}€</td>
        <td>${(transaction.amount_paid || 0).toLocaleString()}€</td>
        <td>
            <span class="status-badge ${UI.helpers.getStatusClass(transaction.payment_status)}">
                <i class="${UI.helpers.getStatusIcon(transaction.payment_status)}"></i>
                ${UI.helpers.getStatusText(transaction.payment_status)}
            </span>
        </td>
        <td>
            <div class="btn-group">
                <button class="btn btn-sm btn-outline-primary view-transaction">
                    <i class="bi bi-eye"></i>
                </button>
                <button class="btn btn-sm btn-outline-success edit-transaction">
                    <i class="bi bi-pencil"></i>
                </button>
            </div>
        </td>
    </tr>
`;

        // Initialize Application
        document.addEventListener('DOMContentLoaded', () => {
            // Initialize date range picker
            if (UI.elements.dateRangePicker) {
                new DateRangePicker(UI.elements.dateRangePicker, {
                    opens: 'left',
                    locale: {
                        format: 'DD/MM/YYYY'
                    }
                });
            }

            // Set up event listeners
            UI.elements.transactionForm?.addEventListener('submit', EventHandlers.handleTransactionSubmit);
            UI.elements.debtsTable?.addEventListener('click', EventHandlers.handleDebtPayment);
            UI.elements.searchInput?.addEventListener('input', EventHandlers.handleSearch);

            // Initial data load
            DataManager.refreshAll();

            // Set up automatic refresh
            setInterval(() => DataManager.refreshAll(), 60000);
        });

        // Theme management
        const ThemeManager = {
            toggleTheme() {
                const isDark = document.body.classList.toggle('theme-dark');
                localStorage.setItem('theme', isDark ? 'dark' : 'light');
            },

            initTheme() {
                const savedTheme = localStorage.getItem('theme');
                if (savedTheme === 'dark') {
                    document.body.classList.add('theme-dark');
                }
            }
        };

        ThemeManager.initTheme();
    </script>
</body>
</html>
