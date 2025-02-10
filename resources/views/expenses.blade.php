@extends('layouts.app')

@section('content')
<div class="container">
    <!-- Card Component -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Expense List</h5>
            <button class="btn btn-primary" id="addExpenseButton">Add Expense</button>
        </div>
        <div class="card-body">
            <div id="loading" class="text-center" style="display: none;">
                <i class="bi bi-arrow-clockwise" style="font-size: 2rem;" role="status"></i>
                <p>Loading expenses...</p>
            </div>
            <div class="table-responsive">
                <table id="expensesTable" class="table table-striped table-hover w-100">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Role</th>
                        <th>Description</th>
                        <th>Amount</th>
                        <th>Date</th>
                        <th>Category</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Filled dynamically -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal for Adding and Editing Expense -->
<div class="modal fade" id="expenseModal" tabindex="-1" aria-labelledby="expenseModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="expenseModalLabel">Add/Edit Expense</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="expenseForm">
                    <input type="hidden" id="expenseId" name="expenseId">
                    <div class="mb-3">
                        <label for="category_id" class="form-label">Category</label>
                        <select class="form-control" id="category_id" name="category_id" required>
                            <option value="">Select Category</option>
                            <!-- Categories will be populated here -->
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <input type="text" class="form-control" id="description" name="description" required>
                    </div>
                    <div class="mb-3">
                        <label for="amount" class="form-label">Amount (Fbu)</label>
                        <input type="number" step="0.01" class="form-control" id="amount" name="amount" required>
                    </div>
                    <button type="submit" class="btn btn-primary" id="submitExpenseButton"></button>
                </form>
                <div id="formFeedback" class="mt-3"></div>
            </div>
        </div>
    </div>
</div>

<!-- Include jQuery and Bootstrap JS -->
<script src="{{ asset('assets\js\jquery-3.7.1.min.js') }}"></script>
<script src="{{ asset('assets\vendor\DataTables\datatables.min.css') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
$(document).ready(function() {
    // Initialize DataTables
    var table = $('#expensesTable').DataTable();

    loadExpenses();

    function loadExpenses() {
        $('#loading').show();
        $.get('/api/expenses', function(data) {
            table.clear();
            data.forEach(function(expense) {
                table.row.add([
                    expense.user ? expense.user.name : 'N/A',
                    expense.user ? expense.user.role : 'N/A',
                    expense.description,
                    `${expense.amount} Fbu`,
                    new Date(expense.created_at).toLocaleDateString(),
                    expense.category ? expense.category.category_name : 'N/A',
                    `
                        <button class="btn btn-sm btn-warning edit-expense" data-id="${expense.id}" data-description="${expense.description}" data-amount="${expense.amount}" data-category="${expense.category ? expense.category.id : ''}">Edit</button>
                        <button class="btn btn-sm btn-danger delete-expense" data-id="${expense.id}">Delete</button>
                    `
                ]).draw(false);
            });
            $('#loading').hide();
        }).fail(function() {
            showNotification('Error loading expenses.', 'danger');
            $('#loading').hide();
        });
    }

    // Load categories into dropdown
    function loadCategories() {
        $.get('/api/categories', function(categories) {
            const categorySelect = $('#category_id');
            categorySelect.empty().append('<option value="">Select Category</option>'); // Clear existing options

            categories.forEach(function(category) {
                categorySelect.append(new Option(category.category_name, category.id)); // Adjust property names based on your Category model
            });
        });
    }

    // Open modal to add expense
    $('#addExpenseButton').on('click', function() {
        $('#expenseModalLabel').text('Add Expense');
        $('#expenseForm')[0].reset();
        $('#expenseId').val(''); // Reset hidden field for ID
        $('#submitExpenseButton').text('Add Expense');
        loadCategories(); // Load categories when opening modal
        $('#expenseModal').modal('show');
    });

    // Handle form submission (Add/Edit)
    $('#expenseForm').on('submit', function(e) {
        e.preventDefault();
        const expenseId = $('#expenseId').val();
        const method = expenseId ? 'PUT' : 'POST';
        const url = expenseId ? `/api/expenses/${expenseId}` : '/api/expenses';

        $.ajax({
            url: url,
            method: method,
            data: {
                user_id: sessionStorage.getItem('userId'),
                description: $('#description').val(),
                amount: Number($('#amount').val()),
                category_id: Number($('#category_id').val())
            },
            success: function(response) {
                showNotification('Expense saved successfully!', 'success');
                $('#expenseModal').modal('hide');
                loadExpenses(); // Reload data
            },
            error: function(xhr) {
                let errorMessage = 'Error saving expense.';
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    errorMessage = xhr.responseJSON.errors.join(', ');
                }
                showNotification(errorMessage, 'danger');
            }
        });
    });

    // Edit an expense
    $(document).on('click', '.edit-expense', function() {
        const id = $(this).data('id');
        const description = $(this).data('description');
        const amount = $(this).data('amount');
        const category = $(this).data('category');

        $('#expenseId').val(id);
        $('#description').val(description);
        $('#amount').val(amount);
        $('#category_id').val(category);
        $('#expenseModalLabel').text('Edit Expense');
        $('#submitExpenseButton').text('Update Expense');
        loadCategories(); // Load categories when opening modal
        $('#expenseModal').modal('show');
    });

    // Delete an expense
    $(document).on('click', '.delete-expense', function() {
        const expenseId = $(this).data('id');
        if (confirm('Are you sure you want to delete this expense?')) {
            $.ajax({
                url: `/api/expenses/${expenseId}`,
                method: 'DELETE',
                success: function(response) {
                    showNotification(response.message, 'success');
                    loadExpenses(); // Reload data
                },
                error: function(xhr) {
                    showNotification('Error deleting expense.', 'danger');
                }
            });
        }
    });

    function showNotification(message, type) {
        const notification = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        $('#formFeedback').html(notification);
    }
});
</script>
@endsection
