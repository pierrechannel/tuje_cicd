@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0 text-primary">User Management</h5>
            <div class="d-flex gap-2">
                <button class="btn btn-outline-secondary" id="refreshTable">
                    <i class="bi bi-arrow-clockwise me-1"></i>Refresh
                </button>
                <button class="btn btn-primary" id="addUserButton">
                    <i class="bi bi-plus me-1"></i>Add User
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <div id="loadingIndicator" class="text-center py-4 d-none">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
                <table id="usersTable" class="table table-striped table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Username</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Data will be populated by DataTables -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal for Add/Edit User -->
<div class="modal fade" id="userModal" tabindex="-1" aria-labelledby="userModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="userModalLabel">Add/Edit User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="userForm" class="needs-validation" novalidate>
                    <input type="hidden" id="userId" name="userId">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username"
                                   required minlength="3" maxlength="16"
                                   pattern="[a-zA-Z0-9_\-]+">
                            <div class="invalid-feedback">
                                Username must be 3-16 characters and contain only letters, numbers, underscores, and hyphens.
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Full Name</label>
                            <input type="text" class="form-control" id="name" name="name"
                                   required minlength="2" maxlength="50"
                                   pattern="[a-zA-Z\s]+">
                            <div class="invalid-feedback">
                                Please enter a valid name (2-50 characters, letters only).
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                            <div class="invalid-feedback">
                                Please enter a valid email address.
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="role" class="form-label">Role</label>
                            <select class="form-select" id="role" name="role" required>
                                <option value="">Select Role</option>
                                <option value="admin">Admin</option>
                                <option value="user">User</option>
                                <option value="manager">Manager</option>
                            </select>
                            <div class="invalid-feedback">
                                Please select a role.
                            </div>
                        </div>
                    </div>
                    <div class="alert alert-danger mt-3 d-none" id="formErrors"></div>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary" id="submitUserButton">
                            <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                            <span class="button-text">Add User</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-labelledby="deleteConfirmModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this user? This action cannot be undone.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">Delete</button>
            </div>
        </div>
    </div>
</div>

<script src="{{ asset('assets/js/jquery-3.7.1.min.js') }}"></script>
<script src="{{ asset('assets/vendor/DataTables/datatables.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    $(document).ready(function() {
        const API_ENDPOINTS = {
            USERS: '/api/users',
            USER: id => `/api/users/${id}`
        };

        const STATUS_MESSAGES = {
            CREATE_SUCCESS: 'User created successfully!',
            UPDATE_SUCCESS: 'User updated successfully!',
            DELETE_SUCCESS: 'User deleted successfully!',
            GENERIC_ERROR: 'An error occurred. Please try again.'
        };

        let dataTable = $('#usersTable').DataTable({
            processing: true,
            ajax: {
                url: API_ENDPOINTS.USERS,
                dataSrc: '',
                beforeSend: () => $('#loadingIndicator').removeClass('d-none'),
                complete: () => $('#loadingIndicator').addClass('d-none'),
                error: handleAjaxError
            },
            columns: [
                { data: 'name' },
                { data: 'username' },
                { data: 'email' },
                { data: 'role', render: data => `<span class="badge text-bg-primary">${data}</span>` },
                {
                    data: null,
                    orderable: false,
                    render: data => generateActionButtons(data)
                }
            ],
            responsive: true,
            language: {
                emptyTable: "No users available",
                processing: "Loading users..."
            },
            order: [[0, 'asc']]
        });

        function generateActionButtons(data) {
            return `
                <div class="btn-group btn-group-sm">
                    <button class="edit-user btn btn-warning" data-id="${data.id}">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <button class="delete-user btn btn-danger" data-id="${data.id}">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            `;
        }

        function handleAjaxError(xhr) {
            console.error('Ajax error:', xhr);
            alert(STATUS_MESSAGES.GENERIC_ERROR);
        }

        function populateForm(user) {
            $('#userId').val(user.id);
            $('#username').val(user.username);
            $('#name').val(user.name);
            $('#email').val(user.email);
            $('#role').val(user.role);
        }

        async function handleFormSubmit(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const data = Object.fromEntries(formData.entries());
    const userId = data.userId;

    const method = userId ? 'PUT' : 'POST';
    const url = userId ? API_ENDPOINTS.USER(userId) : API_ENDPOINTS.USERS;

    try {
        const response = await fetch(url, {
            method: method,
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                username: data.username,
                name: data.name,
                email: data.email,
                role: data.role
            })
        });

        if (!response.ok) {
            const errorText = await response.text(); // Get raw response text
            console.error('Error response:', errorText);
            throw new Error('Failed to save user data.'); // Generic error message
        }

        const result = await response.json(); // Only parse if ok
        const successMessage = userId ? STATUS_MESSAGES.UPDATE_SUCCESS : STATUS_MESSAGES.CREATE_SUCCESS;
        alert(successMessage);
        $('#userModal').modal('hide');
        dataTable.ajax.reload();
        resetForm();
    } catch (error) {
        console.error('Error:', error);
        alert(error.message);
    }
}

        function handleError(errorResponse) {
            const errorDiv = $('#formErrors');
            errorDiv.removeClass('d-none').html('');

            if (errorResponse.errors) {
                $.each(errorResponse.errors, function (key, messages) {
                    messages.forEach(function (message) {
                        errorDiv.append(`<p>${message}</p>`);
                        $(`#${key}`).addClass('is-invalid').on('input', function() {
                            $(this).removeClass('is-invalid');
                        });
                    });
                });
            } else {
                errorDiv.html(`<p>${errorResponse.message || STATUS_MESSAGES.GENERIC_ERROR}</p>`);
            }
        }

        $(document).on('click', '#addUserButton', function() {
            resetForm();
            $('#userModalLabel').text('Add User');
            $('#submitUserButton .button-text').text('Add User');
            $('#userModal').modal('show');
        });

        $(document).on('click', '.edit-user', function() {
            const userId = $(this).data('id');
            fetch(API_ENDPOINTS.USER(userId))
                .then(response => response.json())
                .then(user => {
                    populateForm(user);
                    $('#userModalLabel').text('Edit User');
                    $('#submitUserButton .button-text').text('Update User');
                    $('#userModal').modal('show');
                })
                .catch(handleAjaxError);
        });

        let deleteUserId = null;

        $(document).on('click', '.delete-user', function() {
            deleteUserId = $(this).data('id');
            $('#deleteConfirmModal').modal('show');
        });

        $('#confirmDelete').on('click', function() {
            if (deleteUserId) {
                fetch(API_ENDPOINTS.USER(deleteUserId), {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                })
                .then(response => {
                    if (!response.ok) throw new Error("Failed to delete user.");
                    alert(STATUS_MESSAGES.DELETE_SUCCESS);
                    dataTable.ajax.reload();
                    deleteUserId = null;
                    $('#deleteConfirmModal').modal('hide');
                })
                .catch(handleAjaxError);
            }
        });

        $('#userForm').on('submit', handleFormSubmit);
        $('#refreshTable').on('click', () => dataTable.ajax.reload());

        function resetForm() {
            $('#userForm')[0].reset();
            $('#formErrors').addClass('d-none').empty();
            $('#userId').val('');
            $('input, select').removeClass('is-invalid');
        }
    });
</script>
@endsection
