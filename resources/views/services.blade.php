@extends('layouts.app')

@section('content')
    <style>
        .price-history-chart {
            height: 300px;
            margin-top: 20px;
        }
    </style>
<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0 text-primary">Liste des services</h5>
            <button class="btn btn-primary" id="addServiceButton">
                <i class="bi bi-plus-lg me-2"></i> Ajouter un service
            </button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="servicesTable" class="table table-striped table-hover" style="width:100%">
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Type</th>
                            <th>Prix</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    <div id="notification" class="mt-3"></div>
</div>

<!-- Modal de service -->
<div class="modal fade" id="serviceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Ajouter un service</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body">
                <form id="serviceForm" novalidate>
                    @csrf
                    <input type="hidden" id="serviceId" name="serviceId">
                    <div class="mb-3">
                        <label for="name" class="form-label">Nom</label>
                        <input type="text" class="form-control" id="name" name="name" required
                               placeholder="Entrez le nom du service" minlength="2">
                        <div class="invalid-feedback">Veuillez entrer un nom valide (minimum 2 caractèr</div>
                    </div>
      <div class="mb-3">
    <label for="type" class="form-label">Type de service</label>
    <select class="form-select" id="type" name="type" required>
        <option value="" disabled selected>Choisissez un type de service</option>
        
        <option value="print">Impression</option>
        <option value="photocopy">Photocopie</option>
        <option value="camera">Camera</option>
        <option value="photo">Photos</option>
        <option value="art">Art</option>
        <option value="article">Articles</option>
                    

    </select>
    <div class="invalid-feedback">Veuillez choisir un type de service</div>
</div>
                    <div class="mb-3">
                        <label for="price" class="form-label">Prix</label>
                        <input type="number" class="form-control" id="price" name="price" required
                               placeholder="Entrez le prix du service" step="0.01" min="0">
                        <div class="invalid-feedback">Veuillez entrer un prix valide</div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100" id="submitServiceButton">Ajouter un service</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal de mise à jour des prix -->
<div class="modal fade" id="priceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Mettre à jour le prix</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body">
                <form id="priceForm" novalidate>
                    @csrf
                    <input type="hidden" id="priceServiceId" name="priceServiceId">
                    <div class="mb-3">
                        <label for="newPrice" class="form-label">Nouveau prix</label>
                        <input type="number" class="form-control" id="newPrice" name="new_price" required
                               step="0.01" min="0">
                        <div class="invalid-feedback">Veuillez entrer un prix valide</div>
                    </div>
                    <div class="mb-3">
                        <label for="reason" class="form-label">Raison du changement</label>
                        <textarea class="form-control" id="reason" name="reason" rows="3"
                                  placeholder="Entrez la raison du changement de prix"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Mettre à jour le prix</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal de l'historique des prix -->
<div class="modal fade" id="historyModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Historique des prix</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="startDate" class="form-label">Date de début</label>
                        <input type="date" class="form-control" id="startDate">
                    </div>
                    <div class="col-md-6">
                        <label for="endDate" class="form-label">Date de fin</label>
                        <input type="date" class="form-control" id="endDate">
                    </div>
                </div>
                <div id="priceHistoryChart" class="price-history-chart"></div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de confirmation de suppression -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmer la suppression</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body">
                Êtes-vous sûr de vouloir supprimer ce service ? Cette action est irréversible.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Supprimer</button>
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('assets/js/jquery-3.7.1.min.js') }}"></script>
<script src="{{ asset('assets/vendor/DataTables/datatables.min.js') }}"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">



<script>
$(document).ready(function() {
    // Setup AJAX defaults
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    let dataTable = $('#servicesTable').DataTable({
        ajax: {
            url: '/api/services',
            dataSrc: ''
        },
        columns: [
            { data: 'name' },
            { data: 'type' },
            {
                data: 'price',
                render: function(data) {
                    return new Intl.NumberFormat('en-US', {
                        style: 'currency',
                        currency: 'Fbu'
                    }).format(data);
                }
            },
            {
                data: null,
                render: function(data) {
                    return `
                        <div class="btn-group" role="group">
                            <button class="btn btn-sm btn-warning edit-service" data-id="${data.id}">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-sm btn-info update-price" data-id="${data.id}">
                                <i class="bi bi-currency-dollar"></i>
                            </button>
                            <button class="btn btn-sm btn-secondary view-history" data-id="${data.id}">
                                <i class="bi bi-clock-history"></i>
                            </button>
                            <button class="btn btn-sm btn-danger delete-service" data-id="${data.id}">
                                <i class="bi bi-trash"></i>
                            </button>
                        </div>
                    `;
                }
            }
        ],
        order: [[0, 'asc']]
    });

    // Add Service Button Click
    $('#addServiceButton').on('click', function() {
        resetForm('#serviceForm');
        $('.modal-title').text('Add Service');
        $('#submitServiceButton').text('Add Service');
        $('#serviceModal').modal('show');
    });

    // Service Form Submit
    $('#serviceForm').on('submit', function(e) {
        e.preventDefault();
        if (!this.checkValidity()) {
            e.stopPropagation();
            $(this).addClass('was-validated');
            return;
        }

        const serviceId = $('#serviceId').val();
        const isEdit = Boolean(serviceId);
        const url = isEdit ? `/api/services/${serviceId}` : '/api/services';
        const method = isEdit ? 'PUT' : 'POST';

        $.ajax({
            url: url,
            method: method,
            data: {
                name: $('#name').val(),
                type: $('#type').val(),
                price: $('#price').val()
            },
            success: function(response) {
                showNotification(`Service ${isEdit ? 'updated' : 'added'} successfully`, 'success');
                $('#serviceModal').modal('hide');
                dataTable.ajax.reload();
            },
            error: handleError
        });
    });

    // Edit Service Click
    $(document).on('click', '.edit-service', function() {
        const serviceId = $(this).data('id');
        $.ajax({
            url: `/api/services/${serviceId}`,
            method: 'GET',
            success: function(service) {
                $('#serviceId').val(service.id);
                $('#name').val(service.name);
                $('#type').val(service.type);
                $('#price').val(service.price);
                $('.modal-title').text('Edit Service');
                $('#submitServiceButton').text('Update Service');
                $('#serviceModal').modal('show');
            },
            error: handleError
        });
    });

    // Update Price Click
    $(document).on('click', '.update-price', function() {
        $('#priceServiceId').val($(this).data('id'));
        $('#priceModal').modal('show');
    });

    // Price Form Submit
    $('#priceForm').on('submit', function(e) {
        e.preventDefault();
        if (!this.checkValidity()) {
            e.stopPropagation();
            $(this).addClass('was-validated');
            return;
        }

        const serviceId = $('#priceServiceId').val();
        $.ajax({
            url: `/api/services/${serviceId}/price`,
            method: 'POST',
            data: {
                new_price: $('#newPrice').val(),
                reason: $('#reason').val()
            },
            success: function(response) {
                showNotification('Price updated successfully', 'success');
                $('#priceModal').modal('hide');
                dataTable.ajax.reload();
            },
            error: handleError
        });
    });

    // View History Click
    let priceHistoryChart = null;
    $(document).on('click', '.view-history', function() {
        const serviceId = $(this).data('id');
        loadPriceHistory(serviceId);
        $('#historyModal').modal('show');
    });

    // Date Filter Change
    $('#startDate, #endDate').on('change', function() {
        const serviceId = $('#priceServiceId').val();
        loadPriceHistory(serviceId);
    });

    function loadPriceHistory(serviceId) {
        const startDate = $('#startDate').val();
        const endDate = $('#endDate').val();

        $.ajax({
            url: `/api/services/${serviceId}/price-history`,
            data: { start_date: startDate, end_date: endDate },
            success: function(history) {
                updatePriceHistoryChart(history);
            },
            error: handleError
        });
    }

    function updatePriceHistoryChart(history) {
        if (priceHistoryChart) {
            priceHistoryChart.destroy();
        }

        const ctx = document.getElementById('priceHistoryChart').getContext('2d');
        priceHistoryChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: history.map(h => h.date),
                datasets: [{
                    label: 'Price History',
                    data: history.map(h => h.price),
                    borderColor: 'rgb(75, 192, 192)',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '$' + value;
                            }
                        }
                    }
                }
            }
        });
    }

    // Delete Service
    let deleteServiceId = null;
    $(document).on('click', '.delete-service', function() {
        deleteServiceId = $(this).data('id');
        $('#deleteModal').modal('show');
    });

    $('#confirmDelete').on('click', function() {
        if (!deleteServiceId) return;

        $.ajax({
            url: `/api/services/${deleteServiceId}`,
            method: 'DELETE',
            success: function() {
                showNotification('Service deleted successfully', 'success');
                $('#deleteModal').modal('hide');
                dataTable.ajax.reload();
            },
            error: handleError
        });
    });

    // Utility Functions
    function resetForm(formId) {
        const form = $(formId)[0];
        form.reset();
        form.classList.remove('was-validated');
    }

    // Continuing the script section...

    function showNotification(message, type) {
        const notification = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        $('#notification').html(notification);

        setTimeout(function() {
            $('#notification').fadeOut(500, function() {
                $(this).html('').show();
            });
        }, 3000);
    }

    function handleError(xhr) {
        let errorMessage = 'An error occurred';
        if (xhr.responseJSON) {
            if (xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            } else if (xhr.responseJSON.errors) {
                errorMessage = Object.values(xhr.responseJSON.errors)
                    .flat()
                    .join(', ');
            }
        }
        showNotification(errorMessage, 'danger');
    }

    // Modal cleanup handlers
    ['serviceModal', 'priceModal', 'historyModal', 'deleteModal'].forEach(modalId => {
        $(`#${modalId}`).on('hidden.bs.modal', function() {
            const form = $(this).find('form');
            if (form.length) {
                resetForm(`#${form.attr('id')}`);
            }
            if (modalId === 'historyModal' && priceHistoryChart) {
                priceHistoryChart.destroy();
                priceHistoryChart = null;
            }
        });
    });

    // Initialize date inputs with current month range
    function initializeDateInputs() {
        const today = new Date();
        const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
        const lastDay = new Date(today.getFullYear(), today.getMonth() + 1, 0);

        $('#startDate').val(firstDay.toISOString().split('T')[0]);
        $('#endDate').val(lastDay.toISOString().split('T')[0]);
    }

    initializeDateInputs();
});
</script>
@endsection
