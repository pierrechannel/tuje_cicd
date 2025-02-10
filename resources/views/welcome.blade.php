<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion Imprimerie</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">

    <style>
        :root {
            --sidebar-width: 250px;
        }

        body {
            min-height: 100vh;
            background-color: #f8f9fa;
        }

        #sidebar {
            width: var(--sidebar-width);
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            background: #2c3e50;
            color: white;
            transition: all 0.3s;
            z-index: 1000;
            padding-top: 20px;
        }

        #content {
            margin-left: var(--sidebar-width);
            padding: 20px;
            transition: all 0.3s;
        }

        .nav-link {
            color: rgba(255,255,255,0.8);
            padding: 10px 20px;
            transition: all 0.3s;
        }

        .nav-link:hover {
            color: white;
            background: rgba(255,255,255,0.1);
        }

        .nav-link.active {
            background: rgba(255,255,255,0.2);
            color: white;
        }

        .nav-link i {
            margin-right: 10px;
        }

        .stats-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }

        .stats-card:hover {
            transform: translateY(-5px);
        }

        .table-container {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .status-badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.85em;
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
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div id="sidebar">
        <div class="px-3 mb-4">
            <h4 class="text-white">NpSoft</h4>
        </div>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link active" href="#transactions" data-bs-toggle="tab">
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

    <!-- Content -->
    <div id="content">
        <div class="tab-content">
            <!-- Transactions Tab -->
            <div class="tab-pane fade show active" id="transactions">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3>Transactions</h3>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#transactionModal" id="addTransactionButton">
                        <i class="bi bi-plus-lg"></i> Nouvelle Transaction
                    </button>
                </div>
                <div class="table-container">
                    <table id="transactionsTable" class="table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Client</th>
                                <th>Service</th>
                                <th>Quantité</th>
                                <th>Montant Total</th>
                                <th>Montant Payé</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Filled dynamically -->
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Debts Tab -->
            <div class="tab-pane fade" id="debts">
                <h3 class="mb-4">Gestion des Dettes</h3>
                <div class="table-container">
                    <table id="debtsTable" class="table">
                        <thead>
                            <tr>
                                <th>Client</th>
                                <th>Montant</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Filled dynamically -->
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Stats Tab -->
            <div class="tab-pane fade" id="stats">
                <h3 class="mb-4">Statistiques</h3>
                <div class="row g-4" id="statsContent">
                    <!-- Filled dynamically -->
                </div>
            </div>
        </div>
    </div>

    <!-- Transaction Modal -->
    <div class="modal fade" id="transactionModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nouvelle Transaction</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="transactionForm">
                        <input type="hidden" name="transaction_id" id="transactionId" value="">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Client</label>
                                <select class="form-select" name="customer_id" required>
                                    <!-- Filled dynamically -->
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Service</label>
                                <select class="form-select" name="service_id" required>
                                    <!-- Filled dynamically -->
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Quantité</label>
                                <input type="number" class="form-control" name="quantity" id="quantity" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Statut de paiement</label>
                                <select class="form-select" name="payment_status" id="paymentStatus" required>
                                    <option value="paid">Payé</option>
                                    <option value="partial">Partiel</option>
                                    <option value="unpaid">Non payé</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Montant payé</label>
                                <input type="number" step="0.01" class="form-control" name="amount_paid" id="amountPaid" required>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-primary" id="saveTransaction">
                        <i class="bi bi-save"></i> Enregistrer
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>

    <script>
        $(document).ready(function() {
            // Load initial data
            loadCustomers();
            loadServices();
            loadTransactions();
            loadDebts();
            loadStats();

            // Add new transaction
            $('#addTransactionButton').click(function() {
                $('#transactionForm')[0].reset();
                $('#transactionId').val('');
                $('.modal-title').text('Nouvelle Transaction');
            });

            // Save transaction (add or edit)
            $('#saveTransaction').click(function() {
                const form = $('#transactionForm');
                const transactionId = $('#transactionId').val();
                const method = transactionId ? 'PUT' : 'POST'; // Determine the method based on transactionId
                const url = transactionId ? `/api/transactions/${transactionId}` : '/api/transactions';

                $.ajax({
                    url: url,
                    method: method,
                    data: form.serialize(),
                    success: function(response) {
                        $('#transactionModal').modal('hide');
                        form[0].reset();
                        loadTransactions();
                        alert('Transaction enregistrée avec succès');
                    },
                    error: function(xhr) {
                        alert('Erreur lors de l\'enregistrement');
                    }
                });
            });

            // Load transactions
            function loadTransactions() {
                $.get('/api/transactions', function(data) {
                    let rows = '';
                    data.forEach(function(transaction) {
                        const statusClass = {
                            'paid': 'status-paid',
                            'partial': 'status-partial',
                            'unpaid': 'status-unpaid'
                        }[transaction.payment_status];

                        const statusText = {
                            'paid': 'Payé',
                            'partial': 'Partiel',
                            'unpaid': 'Non payé'
                        }[transaction.payment_status];

                        rows += `
                            <tr>
                                <td>${new Date(transaction.created_at).toLocaleDateString()}</td>
                                <td>${transaction.customer.name}</td>
                                <td>${transaction.service.name}</td>
                                <td>${transaction.quantity}</td>
                                <td>${transaction.total_amount}€</td>
                                <td>${transaction.amount_paid}€</td>
                                <td><span class="status-badge ${statusClass}">${statusText}</span></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary edit-transaction" data-id="${transaction.id}">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger delete-transaction" data-id="${transaction.id}">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        `;
                    });
                    $('#transactionsTable tbody').html(rows);

                    // Initialize DataTables for transactions
                    $('#transactionsTable').DataTable();
                });
            }

            // Edit transaction
            $(document).on('click', '.edit-transaction', function() {
                const transactionId = $(this).data('id');
                $.get(`/api/transactions/${transactionId}`, function(data) {
                    $('#transactionId').val(data.id);
                    $('select[name="customer_id"]').val(data.customer_id);
                    $('select[name="service_id"]').val(data.service_id);
                    $('#quantity').val(data.quantity);
                    $('#paymentStatus').val(data.payment_status);
                    $('#amountPaid').val(data.amount_paid);
                    $('.modal-title').text('Modifier Transaction');
                    $('#transactionModal').modal('show');
                });
            });

            // Delete transaction
            $(document).on('click', '.delete-transaction', function() {
                const transactionId = $(this).data('id');
                if (confirm('Êtes-vous sûr de vouloir supprimer cette transaction ?')) {
                    $.ajax({
                        url: `/api/transactions/${transactionId}`,
                        method: 'DELETE',
                        success: function(response) {
                            loadTransactions();
                            alert('Transaction supprimée avec succès');
                        },
                        error: function(xhr) {
                            alert('Erreur lors de la suppression');
                        }
                    });
                }
            });

            // Load customers
            function loadCustomers() {
                $.get('/api/customers', function(data) {
                    let options = '';
                    data.forEach(function(customer) {
                        options += `<option value="${customer.id}">${customer.name}</option>`;
                    });
                    $('select[name="customer_id"]').html(options);
                });
            }

            // Load services
            function loadServices() {
                $.get('/api/services', function(data) {
                    let options = '';
                    data.forEach(function(service) {
                        options += `<option value="${service.id}">${service.name} - ${service.price}€</option>`;
                    });
                    $('select[name="service_id"]').html(options);
                });
            }

            // Load debts
            function loadDebts() {
                $.get('/api/debts', function(data) {
                    let rows = '';
                    data.forEach(function(debt) {
                        rows += `
                            <tr>
                                <td>${debt.customer.name}</td>
                                <td>${debt.amount}€</td>
                                <td>${new Date(debt.created_at).toLocaleDateString()}</td>
                                <td>
                                    <button class="btn btn-sm btn-success pay-debt" data-id="${debt.id}">
                                        Payer
                                    </button>
                                </td>
                            </tr>
                        `;
                    });
                    $('#debtsTable tbody').html(rows);

                    // Initialize DataTables for debts
                    $('#debtsTable').DataTable();
                });
            }

            // Load stats
            function loadStats() {
                $.get('/api/stats', function(data) {
                    $('#statsContent').html(`
                        <div class="col-md-3">
                            <div class="stats-card h-100">
                                <div class="card-body">
                                    <h5 class="card-title text-muted mb-3">
                                        <i class="bi bi-graph-up"></i> Ventes totales
                                    </h5>
                                    <h3 class="card-text">${data.total_sales}€</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stats-card h-100">
                                <div class="card-body">
                                    <h5 class="card-title text-muted mb-3">
                                        <i class="bi bi-credit-card"></i> Dettes en attente
                                    </h5>
                                    <h3 class="card-text">${data.pending_debts}€</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stats-card h-100">
                                <div class="card-body">
                                    <h5 class="card-title text-muted mb-3">
                                        <i class="bi bi-printer"></i> Services
                                    </h5>
                                    <h3 class="card-text">${data.services_count}</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stats-card h-100">
                                <div class="card-body">
                                    <h5 class="card-title text-muted mb-3">
                                        <i class="bi bi-calendar-check"></i> Transactions aujourd'hui
                                    </h5>
                                    <h3 class="card-text">${data.transactions_today}</h3>
                                </div>
                            </div>
                        </div>
                    `);
                });
            }

            // Manage debt payments
            $(document).on('click', '.pay-debt', function() {
                const debtId = $(this).data('id');
                const amount = prompt('Entrez le montant du paiement:');
                if (amount) {
                    $.ajax({
                        url: `/api/debts/${debtId}/payment`,
                        method: 'PUT',
                        data: { amount_paid: amount },
                        success: function(response) {
                            alert('Paiement enregistré avec succès');
                            loadDebts();
                            loadStats();
                        },
                        error: function(xhr) {
                            alert('Erreur lors du paiement');
                        }
                    });
                }
            });

            // Automatic data refresh
            setInterval(function() {
                loadDebts();
                loadStats();
            }, 60000); // Update every minute
        });
    </script>
</body>
</html>
