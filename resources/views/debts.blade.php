@extends('layouts.app')

@section('content')
<div class="container py-4">
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
                <div id="loading" class="text-center" style="display: none;">
                    <i class="bi bi-arrow-clockwise" style="font-size: 2rem;" role="status"></i>
                    <p>Chargement des dettes...</p>
                </div>
            </div>
        </div>
    </div>

    <div id="notification" class="mt-3"></div>

    <!-- Modal for Payment Input -->
    <div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header border-top">
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
<script src="{{ asset('assets\js\jquery-3.7.1.min.js') }}"></script>
<script src="{{ asset('assets\vendor\DataTables\datatables.min.css') }}"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css"/>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">

<script>
$(document).ready(function() {

    var accessToken = sessionStorage.getItem('accessToken'); // or use localStorage based on your Auth system.

    if (!accessToken) {
        // Not authenticated, redirect to login
        window.location.href = '/login'; // Adjust your login URL
    } else {

    loadDebts();

    function loadDebts() {
        $('#loading').show();
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
                $('#debtsTable').DataTable().clear().rows.add(debtsTableBody.children()).draw();
            } else {
                $('#debtsTable').DataTable(); // Initialize DataTable if not already done
            }

            $('#loading').hide();
        }).fail(function() {
            showNotification('Erreur de chargement des dettes.', 'danger');
            $('#loading').hide();
        });
    }

    $(document).on('click', '.pay-debt', function() {
        const debtId = $(this).data('id');
        $('#debtId').val(debtId);
        $('#paymentForm')[0].reset(); // Reset the form fields
    });

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

                loadDebts(); // Reload debts after payment
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

    setInterval(loadDebts, 10000);

    function formatCurrency(value) {
        return value.toLocaleString('fr-FR', { style: 'currency', currency: 'EUR' });
    }

    function formatDate(dateString) {
        return new Date(dateString).toLocaleDateString('fr-FR');
    }

    function showNotification(message, type) {
        const notification = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        $('#notification').html(notification);

        // Reset the styles after a moment
        setTimeout(function() {
            $('#notification').fadeOut(500, function () {
                $(this).html('').show(); // Clear the notification content
            });
        }, 3000);
    }
}
}
);

</script>
@endsection