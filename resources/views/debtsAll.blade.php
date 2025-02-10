@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-header">
            <h5 class="mb-0">Somme des Dettes par Client</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="customerDebtsTable" class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Client</th>
                            <th>Somme des Dettes</th>
                            <th>Dernier Paiement</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Filled dynamically -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Required Scripts and Styles -->
    <script src="{{ asset('assets\js\jquery-3.7.1.min.js') }}"></script>
    <script src="{{ asset('assets\vendor\DataTables\datatables.min.css') }}"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css"/>
    <script src="https://cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>

    <script>
    $(document).ready(function() {
        var accessToken = sessionStorage.getItem('accessToken');

        if (!accessToken) {
            // Not authenticated, redirect to login
            window.location.href = '/login'; // Adjust your login URL
        } else {
            loadCustomerDebts();
        }

        function loadCustomerDebts() {
            $.get('api/debts/customer-debts', function(data) {
                let rows = '';
                data.forEach(function(customerDebt) {
                    rows += `
                        <tr>
                            <td>${customerDebt.customer.name}</td>
                            <td>${formatCurrency(customerDebt.total_debt)}Fbu</td>
                            <td>${formatDate(customerDebt.last_payment_date)}</td>
                        </tr>
                    `;
                });

                const customerDebtsTableBody = $('#customerDebtsTable tbody');
                customerDebtsTableBody.html(rows);

                if ($.fn.DataTable.isDataTable('#customerDebtsTable')) {
                    $('#customerDebtsTable').DataTable().clear().rows.add(customerDebtsTableBody.children()).draw();
                } else {
                    $('#customerDebtsTable').DataTable(); // Initialize DataTable if not already done
                }
            }).fail(function() {
                console.error('Erreur de chargement des informations de dettes par client.');
            });
        }

        function formatCurrency(value) {
            return value.toLocaleString('fr-FR', { style: 'currency', currency: 'EUR' });
        }

        function formatDate(dateString) {
            return new Date(dateString).toLocaleDateString('fr-FR');
        }
    });
    </script>
@endsection