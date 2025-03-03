@extends('layouts.app')

@section('content')
<div class="container py-4">
    <!-- Tab navigation -->
    <ul class="nav nav-tabs mb-4" id="debtsTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="debts-list-tab" data-bs-toggle="tab" data-bs-target="#debts-list-content" type="button" role="tab" aria-controls="debts-list-content" aria-selected="true">Liste des Dettes</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="debts-by-customer-tab" data-bs-toggle="tab" data-bs-target="#debts-by-customer-content" type="button" role="tab" aria-controls="debts-by-customer-content" aria-selected="false">Dettes par Client</button>
        </li>
    </ul>

    <div class="tab-content" id="debtsTabContent">
        <!-- Debts List Tab Content -->
        <div class="tab-pane fade show active" id="debts-list-content" role="tabpanel" aria-labelledby="debts-list-tab">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">Liste des Dettes</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="debtsTable" class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Client</th>
                                    <th>Téléphone</th>
                                    <th>Montant</th>
                                    <th>Date</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Filled dynamically -->
                            </tbody>
                        </table>
                        <div id="loading-debts" class="text-center" style="display: none;">
                            <i class="bi bi-arrow-clockwise" style="font-size: 2rem;" role="status"></i>
                            <p>Chargement des dettes...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Debts by Customer Tab Content -->
        <div class="tab-pane fade" id="debts-by-customer-content" role="tabpanel" aria-labelledby="debts-by-customer-tab">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Dettes par Client</h5>
                    <span class="text-muted small" id="reportGeneratedAt"></span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="customerDebtsTable" class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>Client</th>
                                    <th>Dette Totale</th>
                                    <th>Nombre de Dettes</th>
                                    <th>Dettes Payées</th>
                                    <th>Dettes en Attente</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Filled dynamically -->
                            </tbody>
                        </table>
                        <div id="loading-customers" class="text-center" style="display: none;">
                            <i class="bi bi-arrow-clockwise" style="font-size: 2rem;" role="status"></i>
                            <p>Chargement des données...</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="notification" class="mt-3"></div>

    <!-- Modal for Payment Input -->
    <div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="paymentModalLabel">Enregistrer le Paiement</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="paymentForm" novalidate>
                        <div class="mb-3">
                            <label for="amount" class="form-label">Montant du Paiement</label>
                            <input type="number" class="form-control" id="amount" required min="0" step="0.01">
                            <div class="invalid-feedback">Veuillez entrer un montant valide.</div>
                        </div>
                        <input type="hidden" id="debtId">
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" form="paymentForm" class="btn btn-primary">Enregistrer</button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Required Scripts and Styles -->
<script src="{{ asset('assets\js\jquery-3.7.1.min.js') }}"></script>
<script src="{{ asset('assets\vendor\DataTables\datatables.min.css') }}"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css"/>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">

<script>
$(document).ready(function() {
    var accessToken = sessionStorage.getItem('accessToken');
    var debtsTable, customerDebtsTable;

    if (!accessToken) {
        window.location.href = '/login';
    } else {
        // Initial load of debts list
        loadDebts();

        // Set up tab change event
        $('#debtsTabs button').on('click', function (e) {
            e.preventDefault();
            $(this).tab('show');

            // Load data for the tab that was just activated
            if ($(this).attr('id') === 'debts-list-tab') {
                loadDebts();
            } else if ($(this).attr('id') === 'debts-by-customer-tab') {
                loadCustomerDebts();
            }
        });
    }

    function loadDebts() {
        $('#loading-debts').show();
        $.get('/api/debts', function(data) {
            let rows = '';
            data.forEach(function(debt) {
                rows += `
                    <tr>
                        <td>${debt.customer.name}</td>
                        <td>${debt.customer.phone}</td>
                        <td>${formatCurrency(debt.amount)}Fbu</td>
                        <td>${formatDate(debt.created_at)}</td>
                        <td>
                            <button class="btn btn-sm btn-success pay-debt" data-id="${debt.id}" data-bs-toggle="modal" data-bs-target="#paymentModal">
                                <i class="bi bi-check-circle"></i> Payer
                            </button>
                        </td>
                    </tr>
                `;
            });

            const debtsTableBody = $('#debtsTable tbody');
            debtsTableBody.html(rows);

            if ($.fn.DataTable.isDataTable('#debtsTable')) {
                debtsTable.clear().rows.add(debtsTableBody.children()).draw();
            } else {
                debtsTable = $('#debtsTable').DataTable({
                    "language": {
                        "url": "//cdn.datatables.net/plug-ins/1.10.21/i18n/French.json"
                    }
                });
            }

            $('#loading-debts').hide();
        }).fail(function() {
            showNotification('Erreur de chargement des dettes.', 'danger');
            $('#loading-debts').hide();
        });
    }

    function loadCustomerDebts() {
        $('#loading-customers').show();
        $.get('/api/reports/debts-by-customer', function(response) {
            let rows = '';
            response.customers.forEach(function(customer) {
                rows += `
                    <tr>
                        <td>${customer.name}</td>
                        <td>${formatCurrency(customer.total_debt)}Fbu</td>
                        <td>${customer.debt_count}</td>
                        <td>${customer.paid_count}</td>
                        <td>${customer.pending_count}</td>
                        <td>
                            <button class="btn btn-sm btn-primary view-customer-debts" data-id="${customer.id}">
                                <i class="bi bi-eye"></i> Détails
                            </button>
                        </td>
                    </tr>
                `;
            });

            const tableBody = $('#customerDebtsTable tbody');
            tableBody.html(rows);

            if ($.fn.DataTable.isDataTable('#customerDebtsTable')) {
                customerDebtsTable.clear().rows.add(tableBody.children()).draw();
            } else {
                customerDebtsTable = $('#customerDebtsTable').DataTable({
                    "language": {
                        "url": "//cdn.datatables.net/plug-ins/1.10.21/i18n/French.json"
                    },
                    "order": [[1, "desc"]]  // Sort by total debt desc
                });
            }

            // Set the report generation time
            $('#reportGeneratedAt').text('Rapport généré le: ' + formatDateTime(response.generated_at));

            $('#loading-customers').hide();
        }).fail(function() {
            showNotification('Erreur de chargement des données.', 'danger');
            $('#loading-customers').hide();
        });
    }

    // Handle payment button click
    $(document).on('click', '.pay-debt', function() {
        const debtId = $(this).data('id');
        $('#debtId').val(debtId);
        $('#paymentForm')[0].reset(); // Reset the form fields
    });

    // Handle payment form submission
    $('#paymentForm').on('submit', function(e) {
        e.preventDefault();

        if (!this.checkValidity()) {
            e.stopPropagation();
            $(this).addClass('was-validated');
            return;
        }

        const debtId = $('#debtId').val();
        const amountPaid = $('#amount').val();

        $.ajax({
            url: `/api/debts/${debtId}/payment`,
            method: 'PUT',
            data: { amount_paid: Number(amountPaid) },
            success: function(response) {
                showNotification('Paiement enregistré avec succès.', 'success');
                $('#paymentModal').modal('hide');  // Close the modal

                // Manually hide the backdrop as well
                $('.modal-backdrop').hide();

                // Reload both tables to reflect changes
                loadDebts();

                // If customer debts tab is visible, reload that data too
                if ($('#debts-by-customer-tab').hasClass('active')) {
                    loadCustomerDebts();
                }
            },
            error: function(xhr) {
                let errorMessage = 'Erreur lors du paiement';
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    errorMessage = xhr.responseJSON.errors.amount_paid.join(', ');
                }
                showNotification(errorMessage, 'danger');
            }
        });
    });

    // Handle view customer debts button
    $(document).on('click', '.view-customer-debts', function() {
        const customerId = $(this).data('id');
        // For now, we'll just switch to the debts tab and filter by customer
        // You can implement a more specific view if needed
        $('#debts-list-tab').tab('show');

        // This assumes your DataTable has search functionality
        if (debtsTable) {
            // Get customer name from the row
            const customerName = $(this).closest('tr').find('td:first').text();
            debtsTable.search(customerName).draw();
        }
    });

    // Set up auto-refresh
    const debtsRefreshInterval = setInterval(function() {
        if ($('#debts-list-tab').hasClass('active')) {
            loadDebts();
        }
    }, 10000);

    const customerDebtsRefreshInterval = setInterval(function() {
        if ($('#debts-by-customer-tab').hasClass('active')) {
            loadCustomerDebts();
        }
    }, 30000);

    // Helper functions
    function formatCurrency(value) {
        return parseFloat(value).toLocaleString('fr-FR', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    function formatDate(dateString) {
        return new Date(dateString).toLocaleDateString('fr-FR');
    }

    function formatDateTime(dateString) {
        return new Date(dateString).toLocaleString('fr-FR');
    }

    function showNotification(message, type) {
        const notification = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        $('#notification').html(notification);

        setTimeout(function() {
            $('#notification').fadeOut(500, function () {
                $(this).html('').show(); // Clear the notification content
            });
        }, 3000);
    }
});
</script>
@endsection
