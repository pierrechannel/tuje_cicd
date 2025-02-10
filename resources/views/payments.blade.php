@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Liste des Paiements</h5>
        </div>
        <div class="card-body">
            <div class="table-container">
                <table id="paymentsTable" class="table table-striped table-responsive">
                    <thead>
                        <tr>
                            <th>Nom du Client</th>
                            <th>Téléphone</th>
                            <th>Email</th>
                            <th>Montant</th>
                            <th>Date de paiement</th>
                            <th>Méthode de paiement</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Filled dynamically -->
                    </tbody>
                </table>
                <div id="loading" class="text-center" style="display: none;">
                    <i class="bi bi-arrow-clockwise" style="font-size: 2rem;" role="status"></i>
                    <p>Chargement des paiements...</p>
                </div>
            </div>
        </div>
    </div>

    <div id="notification" class="mt-3"></div>
</div>

<!-- Required Scripts and Styles -->
<script src="{{ asset('assets\js\jquery-3.7.1.min.js') }}"></script>
<script src="{{ asset('assets\vendor\DataTables\datatables.min.css') }}"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">

<script>
$(document).ready(function() {
    loadPayments(); // Initial load of payments

    function loadPayments() {
        $('#loading').show();
        $.get('/api/payments', function(data) {
            let rows = '';
            data.forEach(function(payment) {
                rows += `
                    <tr>
                        <td>${payment.debt.customer.name}</td>
                        <td>${payment.debt.customer.phone}</td>
                        <td>${payment.debt.customer.email}</td>
                        <td>${formatCurrency(payment.amount)}Fbu</td>
                        <td>${formatDate(payment.payment_date)}</td>
                        <td>${payment.payment_method}</td>
                        <td>
                            <button class="btn btn-sm btn-danger delete-payment" data-id="${payment.id}">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;
            });
            $('#paymentsTable tbody').html(rows);
            $('#paymentsTable').DataTable(); // Initialize DataTable for the rows loaded
            $('#loading').hide();
        }).fail(function() {
            showNotification('Erreur de chargement des paiements.', 'danger');
            $('#loading').hide();
        });
    }

    // Handle adding a new payment
    $('#addPaymentButton').on('click', function() {
        const debtId = prompt('Entrez l\'ID de la dette:');
        const amount = prompt('Entrez le montant du paiement:');
        const paymentDate = prompt('Entrez la date de paiement (YYYY-MM-DD):');
        const paymentMethod = prompt('Entrez la méthode de paiement:');

        if (debtId && amount && paymentDate && paymentMethod) {
            $.ajax({
                url: '/api/payments',
                method: 'POST',
                data: {
                    debt_id: debtId,
                    amount: Number(amount),
                    payment_date: paymentDate,
                    payment_method: paymentMethod
                },
                success: function() {
                    showNotification('Paiement enregistré avec succès.', 'success');
                    loadPayments(); // Reload payments after adding
                },
                error: function(xhr) {
                    let errorMessage = 'Erreur lors de l\'ajout du paiement';
                    if (xhr.responseJSON && xhr.responseJSON.errors) {
                        errorMessage = xhr.responseJSON.errors.join(', ');
                    }
                    showNotification(errorMessage, 'danger');
                }
            });
        } else {
            showNotification('Veuillez entrer des valeurs valides.', 'warning');
        }
    });

    // Deleting a payment
    $(document).on('click', '.delete-payment', function() {
        const paymentId = $(this).data('id');
        if (confirm('Êtes-vous sûr de vouloir supprimer ce paiement ?')) {
            $.ajax({
                url: `/api/payments/${paymentId}`,
                method: 'DELETE',
                success: function(response) {
                    showNotification(response.message, 'success');
                    loadPayments(); // Reload payments after deletion
                },
                error: function() {
                    showNotification('Erreur lors de la suppression du paiement.', 'danger');
                }
            });
        }
    });

    // Automatic data refresh
    setInterval(loadPayments, 60000); // Update every minute

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
    }
});
</script>
@endsection
