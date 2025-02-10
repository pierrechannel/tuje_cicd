@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0 text-primary">Customer List</h5>
            <button class="btn btn-primary" id="addCustomerButton">
                <i class="bi bi-plus-lg me-2"></i> Add Customer
            </button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="customersTable" class="table table-striped table-hover" style="width:100%">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Phone</th>
                            <th>Email</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>

    <div id="notification" class="mt-3"></div>
</div>

<!-- Customer Modal -->
<div class="modal fade" id="customerModal" tabindex="-1" aria-labelledby="customerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="customerModalLabel">Add Customer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="customerForm" novalidate>
                    @csrf
                    <input type="hidden" id="customerId" name="customerId">
                    <div class="mb-3">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" class="form-control" id="name" name="name" required
                               placeholder="Enter customer name" minlength="2">
                        <div class="invalid-feedback">Please enter a valid name (minimum 2 characters)</div>
                    </div>
                    <div class="mb-3">
                        <label for="phone" class="form-label">Phone</label>
                        <input type="tel" class="form-control" id="phone" name="phone" required
                               placeholder="Enter customer phone" pattern="[0-9+\s-]{10,}">
                        <div class="invalid-feedback">Please enter a valid phone number</div>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required
                               placeholder="Enter customer email">
                        <div class="invalid-feedback">Please enter a valid email address</div>
                    </div>
                    <button type="submit" class="btn btn-primary w-100" id="submitCustomerButton">Add Customer</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Confirm Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this customer? This action cannot be undone.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Delete</button>
            </div>
        </div>
    </div>
</div>

<!-- Required Scripts -->
<script src="{{ asset('assets/js/jquery-3.7.1.min.js') }}"></script>
<script src="{{ asset('assets/vendor/DataTables/datatables.min.js') }}"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css"/>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">

<script>
$(document).ready(function() {
    // Check authentication
    const accessToken = sessionStorage.getItem('accessToken');
    if (!accessToken) {
        window.location.href = '/login';
        return;
    }

    // Setup AJAX defaults
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            'Authorization': `Bearer ${accessToken}`
        }
    });

    let dataTable;

    function initializeDataTable() {
        if ($.fn.DataTable.isDataTable('#customersTable')) {
            dataTable.destroy();
        }

        dataTable = $('#customersTable').DataTable({
            ajax: {
                url: '/api/customers',
                dataSrc: ''
            },
            columns: [
                { data: 'name' },
                { data: 'phone' },
                { data: 'email' },
                {
                    data: null,
                    render: function(data) {
                        return `
                            <div class="btn-group" role="group">
                                <button class="btn btn-sm btn-warning edit-customer" data-id="${data.id}">
                                    <i class="bi bi-pencil"></i> Edit
                                </button>
                                <button class="btn btn-sm btn-danger delete-customer" data-id="${data.id}">
                                    <i class="bi bi-trash"></i> Delete
                                </button>
                            </div>
                        `;
                    }
                }
            ],
            language: {
                emptyTable: "No customers found",
                search: "Search:",
                lengthMenu: "Show _MENU_ entries per page",
                info: "Showing _START_ to _END_ of _TOTAL_ customers",
                paginate: {
                    first: "First",
                    last: "Last",
                    next: "Next",
                    previous: "Previous"
                }
            },
            order: [[0, 'asc']],
            responsive: true
        });
    }

    // Initialize DataTable
    initializeDataTable();

    // Add Customer Button Click
    $('#addCustomerButton').on('click', function() {
        resetForm();
        $('#customerModalLabel').text('Add Customer');
        $('#submitCustomerButton').text('Add Customer');
        $('#customerModal').modal('show');
    });

    // Edit Customer Button Click
    $(document).on('click', '.edit-customer', function() {
        const customerId = $(this).data('id');

        $.ajax({
            url: `/api/customers/${customerId}`,
            method: 'GET',
            success: function(customer) {
                $('#customerId').val(customer.id);
                $('#name').val(customer.name);
                $('#phone').val(customer.phone);
                $('#email').val(customer.email);
                $('#customerModalLabel').text('Edit Customer');
                $('#submitCustomerButton').text('Update Customer');
                $('#customerModal').modal('show');
            },
            error: function(xhr) {
                handleError(xhr, 'Error loading customer details');
            }
        });
    });

    // Delete Customer Button Click
    let deleteCustomerId = null;
    $(document).on('click', '.delete-customer', function() {
        deleteCustomerId = $(this).data('id');
        $('#deleteModal').modal('show');
    });

    // Confirm Delete
    $('#confirmDelete').on('click', function() {
        if (!deleteCustomerId) return;

        $.ajax({
            url: `/api/customers/${deleteCustomerId}`,
            method: 'DELETE',
            success: function() {
                showNotification('Customer deleted successfully', 'success');
                closeModal('deleteModal');
                dataTable.ajax.reload();
            },
            error: function(xhr) {
                handleError(xhr, 'Error deleting customer');
            }
        });
    });

    // Form Submit Handler
    $('#customerForm').on('submit', function(e) {
        e.preventDefault();

        if (!this.checkValidity()) {
            e.stopPropagation();
            $(this).addClass('was-validated');
            return;
        }

        const customerId = $('#customerId').val();
        const isEdit = Boolean(customerId);
        const method = isEdit ? 'PUT' : 'POST';
        const url = isEdit ? `/api/customers/${customerId}` : '/api/customers';

        $.ajax({
            url: url,
            method: method,
            data: {
                name: $('#name').val().trim(),
                phone: $('#phone').val().trim(),
                email: $('#email').val().trim(),
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                showNotification(
                    `Customer ${isEdit ? 'updated' : 'added'} successfully`,
                    'success'
                );
                closeModal('customerModal');
                dataTable.ajax.reload();
            },
            error: function(xhr) {
                handleError(xhr, `Error ${isEdit ? 'updating' : 'adding'} customer`);
            }
        });
    });

    // Utility Functions
    function resetForm() {
        const form = $('#customerForm')[0];
        form.reset();
        form.classList.remove('was-validated');
        $('#customerId').val('');
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
            $('#notification').fadeOut(500, function() {
                $(this).html('').show();
            });
        }, 3000);
    }

    function handleError(xhr, defaultMessage) {
        let errorMessage = defaultMessage;
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

    function closeModal(modalId) {
        $(`#${modalId}`).modal('hide');
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open');
    }
});
</script>
@endsection
