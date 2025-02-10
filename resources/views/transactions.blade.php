@extends('layouts.app')

@section('content')
    <div class="container py-4">
        <div id="loadingSpinner" style="display:none;">Loading...</div>
        <div class="card shadow-sm">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h4 class="mb-0 text-primary">Transactions</h4>
                <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#transactionModal" id="addTransactionButtonInCard">
                    <i class="bi bi-plus-lg me-2"></i>Nouvelle Transaction
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <div id="loadingIndicator" class="text-center py-4 d-none  justify-content-center">
                            <div class="spinner-border text-danger" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                    <table id="transactionsTable" class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Client</th>
                                <th>Montant Total</th>
                                <th>Montant Payé</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>

                            <!-- Rows populated via JS -->
                        </tbody>
                    </table>
                </div>
            </div>



            <!-- Toast Element -->
<div id="toastContainer" aria-live="polite" aria-atomic="true" style="position: absolute; top: 20px; right: 20px;">
    <div class="toast" id="successToast" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header">
            <strong class="me-auto">Notification</strong>
            <small class="text-muted" id="toastTimestamp"></small>
            <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
        <div class="toast-body" id="toastMessage"></div>
    </div>
</div>
        </div>
    </div>

    <!-- Transaction Modal -->
    <div class="modal fade" id="transactionModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header border-0 bg-light">
                    <h5 class="modal-title text-primary">Nouvelle Transaction</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="transactionForm" class="needs-validation" novalidate>
                        <input type="hidden" name="transaction_id" id="transactionId">

                        <div class="mb-4">
                            <label class="form-label fw-bold">Client</label>
                            <select class="form-select " name="customer_id" id="customerSelect" required>
                                <option value="">Sélectionnez un client</option>
                            </select>
                            <div class="invalid-feedback">Veuillez sélectionner un client</div>
                        </div>

                        <div id="itemsContainer" class="mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="fw-bold mb-0">Services</h6>
                                <button type="button" id="addItemButton" class="btn btn-outline-primary btn-sm">
                                    <i class="bi bi-plus-lg me-2"></i>Ajouter un service
                                </button>
                            </div>
                            <!-- Items added dynamically -->
                        </div>


                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Statut de paiement</label>
                                <select class="form-select" name="payment_status" id="paymentStatus" required>
                                    <option value="paid">Payé</option>
                                    <option value="partial">Partiel</option>
                                    <option value="unpaid">Non payé</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Montant payé</label>
                                <div class="input-group">
                                    <input type="number" step="0.01" class="form-control" name="amount_paid" id="amountPaid" required>
                                    <span class="input-group-text">Fbu</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Montant Total</label>
                                <div class="input-group">
                                    <input type="number" step="0.01" class="form-control bg-light" id="totalAmount">
                                    <span class="input-group-text">Fbu</span>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer border-0 bg-light">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-primary" id="saveTransaction">
                        <i class="bi bi-save me-2"></i>Enregistrer
                    </button>
                </div>
            </div>
        </div>
    </div>

    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <style>
        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 2rem;
            font-weight: 500;
            font-size: 0.875rem;
        }
        .status-paid {
            background-color: #d1fae5;
            color: #065f46;
        }
        .status-partial {
            background-color: #fef3c7;
            color: #92400e;
        }
        .status-unpaid {
            background-color: #fee2e2;
            color: #991b1b;
        }
        .item-row {
            background-color: #f8fafc;
            border-radius: 0.5rem;
            padding: 1rem;
            margin-bottom: 1rem;
        }
        .form-label {
            font-size: 0.875rem;
            margin-bottom: 0.5rem;
        }
        .table th {
            font-weight: 600;
            font-size: 0.875rem;
        }
        .invoice-content {
            padding: 2rem;
        }
        .company-name {
            color: #2563eb;
            font-weight: bold;
        }
        .company-details {
            font-size: 0.9rem;
        }
        .invoice-info h3 {
            color: #2563eb;
        }
        .table th {
            background-color: #f8f9fa;
        }
        .footer {
            border-top: 1px solid #dee2e6;
            padding-top: 1rem;
        }
        @media print {
            .modal-footer {
                display: none;
            }
        }

    </style>

<script src="{{ asset('assets\js\jquery-3.7.1.min.js') }}"></script>
<script src="{{ asset('assets\vendor\DataTables\datatables.min.css') }}"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>

   <!-- Ensure to include these in your HTML head -->
<meta name="csrf-token" content="{{ csrf_token() }}">
<link rel="stylesheet" href="path/to/bootstrap.css">
<script src="path/to/jquery.js"></script>
<script src="path/to/bootstrap.bundle.js"></script>
<script src="path/to/dataTables.min.js"></script>
<link rel="stylesheet" href="path/to/dataTables.min.css">

<script>
$(document).ready(function() {
    let dataTable;

    initializeDataTable();
    loadInitialData();
    setupEventListeners();

    // Initialize DataTable
    function initializeDataTable() {
        dataTable = $('#transactionsTable').DataTable({
            language: {
                emptyTable: "Aucune donnée disponible",
                info: "Affichage de _START_ à _END_ sur _TOTAL_ entrées",
                search: "Rechercher :",
                paginate: {
                    next: "Suivant",
                    previous: "Précédent"
                }
            },
            order: [[0, 'desc']],
            pageLength: 10,
            responsive: true
        });
    }

    // Load initial data for customers, services, and transactions
    function loadInitialData() {
        // Show spinner
        $('#loadingIndicator').removeClass('d-none');

        Promise.all([
            fetch('/api/customers').then(res => res.json()),
            fetch('/api/services').then(res => res.json()),
            fetch('/api/transactions').then(res => res.json())
        ])
        .then(([customers, services, transactions]) => {
            populateCustomerSelect(customers);
            populateServiceSelects(services);
            updateTransactionsTable(transactions);
        })
        .catch(handleError)
        .finally(() => {
            // Hide spinner
            $('#loadingIndicator').addClass('d-none')
        });
    }

    // Create a new item row for the form
    function createItemRow(index) {
        return `
            <div class="item-row">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Service</label>
                        <select class="form-select serviceSelect" name="items[${index}][service_id]" required>
                            <option value="">Sélectionnez un service</option>
                        </select>
                        <div class="invalid-feedback">Veuillez sélectionner un service</div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Quantité</label>
                        <input type="number" class="form-control" name="items[${index}][quantity]" required min="1" value="1">
                        <div class="invalid-feedback">Quantité invalide</div>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="button" class="btn btn-outline-danger remove-item-button w-100">
                            <i class="bi bi-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
        `;
    }

    function setupEventListeners() {
        $('#addItemButton').click(() => {
            const index = $('#itemsContainer .item-row').length; // Get the current number of items
            $('#itemsContainer').append(createItemRow(index)); // Append a new item row
            populateServiceSelects(); // Populate service select options
        });

        $('#itemsContainer').on('change', '.serviceSelect, input[type="number"]', updateTotalAmount);
        $(document).on('click', '.remove-item-button', function() {
            $(this).closest('.item-row').remove(); // Remove the item row
            updateTotalAmount(); // Update total amount after removal
        });

        $('#saveTransaction').click(handleTransactionSave);
        $(document).on('click', '.edit-transaction', handleTransactionEdit);
        $(document).on('click', '.delete-transaction', handleTransactionDelete);
    }

    // Populate customer select options
    function populateCustomerSelect(customers) {
        const options = customers.map(customer =>
            `<option value="${customer.id}">${customer.name}</option>`
        ).join('');
        $('#customerSelect').html(`<option value="">Sélectionnez un client</option>${options}`);
    }

    // Populate the service select dropdowns
    function populateServiceSelects(services) {
        if (!services) {
            fetch('/api/services')
                .then(res => res.json())
                .then(populateSelectOptions)
                .catch(handleError);
            return;
        }
        populateSelectOptions(services);
    }

    // Populate options in each of the service selects
    function populateSelectOptions(services) {
        const options = services.map(service =>
            `<option value="${service.id}" data-price="${service.price}">${service.name} - ${service.price}Fbu</option>`
        ).join('');
        $('.serviceSelect').each(function() {
            const currentValue = $(this).val();
            $(this).html(`<option value="">Sélectionnez un service</option>${options}`);
            $(this).val(currentValue);
        });
    }

    // Update the transactions table with fetched transactions
    function updateTransactionsTable(transactions) {
        dataTable.clear();
        transactions.forEach(transaction => {
            dataTable.row.add([
                new Date(transaction.created_at).toLocaleDateString(),
                transaction.customer.name,
                `${transaction.total_amount}Fbu`,
                `${transaction.amount_paid}Fbu`,
                createStatusBadge(transaction.payment_status),
                createActionButtons(transaction.id)
            ]);
        });
        dataTable.draw();
    }

    // Create a badge for payment status
    function createStatusBadge(status) {
        const statusMap = {
            paid: ['status-paid', 'Payé'],
            partial: ['status-partial', 'Partiel'],
            unpaid: ['status-unpaid', 'Non payé']
        };
        const [className, text] = statusMap[status];
        return `<span class="status-badge ${className}">${text}</span>`;
    }



    // Handle transaction saving
    async function handleTransactionSave() {
        if (!validateForm()) return;

        const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (!token) {
            console.error('CSRF token not found');
            return;
        }

        let totalAmount = 0;
        const items = [];

        // Iterate through item rows for service and quantity
        $('.item-row').each(function() {
            const serviceId = $(this).find('.serviceSelect').val();
            const quantity = parseInt($(this).find('input[type="number"]').val());
            const price = parseFloat($(this).find('.serviceSelect option:selected').data('price') || 0);

            if (serviceId && quantity) {
                totalAmount += price * quantity;
                items.push({ service_id: serviceId, quantity: quantity });
            }
        });

        // Request data object
        const requestData = {
            customer_id: $('#customerSelect').val(),
            payment_status: $('#paymentStatus').val(),
            amount_paid: parseFloat($('#amountPaid').val()),
            items: items
        };

        const transactionId = $('#transactionId').val();
        const method = transactionId ? 'PUT' : 'POST';
        const url = transactionId ? `/api/transactions/${transactionId}` : '/api/transactions';

        try {
            const response = await fetch(url, {
                method,
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(requestData)
            });

            const data = await response.json();

            if (!response.ok) {
                if (response.status === 422) {
                    const errorMessages = Object.values(data.errors).flat();
                    throw new Error(errorMessages.join('\n'));
                }
                throw new Error(data.message || 'Une erreur est survenue');
            }

            showToast('success', 'Transaction enregistrée avec succès');
            $('#transactionModal').modal('hide');
            resetForm();
            loadInitialData();
        } catch (error) {
            handleError(error);
        }
    }

    // Validate the transaction form
    function validateForm() {
        const form = $('#transactionForm')[0];
        if (!form.checkValidity()) {
            $(form).addClass('was-validated');
            return false;
        }
        return true;
    }

    // Reset the form for new transaction creation
    function resetForm() {
        const form = $('#transactionForm')[0];
        form.reset();
        $(form).removeClass('was-validated');
        $('#transactionId').val('');
        $('#itemsContainer').empty();
        $('#addItemButton').click();
        $('.modal-title').text('Nouvelle Transaction');
    }

    // Show toast notifications
    function showToast(type, message) {
        if (!message) {
            console.error("Toast message is undefined");
            return;
        }

        const toastMessageElement = document.getElementById('toastMessage');
        const toastTimestampElement = document.getElementById('toastTimestamp');
        const toastElement = document.getElementById('successToast');

        toastMessageElement.innerText = message;
        toastTimestampElement.innerText = new Date().toLocaleTimeString();

        if (type === 'success') {
            toastElement.classList.remove('bg-danger');
            toastElement.classList.add('bg-success');
        } else if (type === 'error') {
            toastElement.classList.remove('bg-success');
            toastElement.classList.add('bg-danger');
        }

        const toast = new bootstrap.Toast(toastElement, {
            autohide: true,
            delay: 3000
        });

        toast.show();
    }

    // Handle errors
    function handleError(error) {
        console.error('Error:', error);
        showToast('error', error.message || 'Une erreur est survenue');
    }

    // Handle transaction editing
    async function handleTransactionEdit(event) {
    const id = $(event.currentTarget).data('id');
    try {
        const response = await fetch(`/api/transactions/${id}`);
        const transaction = await response.json();

        resetForm();

        // Set transaction fields
        $('#transactionId').val(transaction.id);
        $('#customerSelect').val(transaction.customer_id).trigger('change');
        $('#paymentStatus').val(transaction.payment_status);
        $('#amountPaid').val(transaction.amount_paid);
        $('#totalAmount').val(transaction.total_amount);

        // Clear and populate items
        $('#itemsContainer').empty();

        // Add existing items
        transaction.items.forEach((item, index) => {
            $('#itemsContainer').append(createItemRow(index));
            const $row = $('#itemsContainer').children().last();
            const $select = $row.find('.serviceSelect');

            populateServiceSelect($select).then(() => {
                $select.val(item.service_id);
                $row.find('.quantityInput').val(item.quantity);
            });
        });

        // Add "Add Item" button after the last item
        const $addButtonRow = $(`
            <div class="mt-3">
                <button type="button" class="btn btn-primary" id="addNewItemButton">
                    <i class="bi bi-plus"></i> Ajouter un service
                </button>
            </div>
        `);
        $('#itemsContainer').append($addButtonRow);

        // Handler for adding new items
        $('#addNewItemButton').click(() => {
            const newIndex = $('#itemsContainer .item-row').length;
            $addButtonRow.before(createItemRow(newIndex));
            const $newRow = $addButtonRow.prev();
            populateServiceSelect($newRow.find('.serviceSelect'));
        });

        $('.modal-title').text('Modifier Transaction');
        $('#transactionModal').modal('show');
    } catch (error) {
        handleError(error);
    }
}

function createItemRow(index) {
    return `
        <div class="item-row">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Service</label>
                    <select class="form-select serviceSelect" name="items[${index}][service_id]" required>
                        <option value="">Sélectionnez un service</option>
                    </select>
                    <div class="invalid-feedback">Veuillez sélectionner un service</div>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Quantité</label>
                    <input type="number" class="form-control quantityInput" name="items[${index}][quantity]" required min="1" value="1">
                    <div class="invalid-feedback">Quantité invalide</div>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="button" class="btn btn-outline-danger remove-item-button w-100">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
        </div>
    `;
}



async function populateServiceSelect($select) {
    try {
        const response = await fetch('/api/services');
        const services = await response.json();

        $select.html('<option value="">Sélectionnez un service</option>');
        services.forEach(service => {
            $select.append(`<option value="${service.id}" data-price="${service.price}">
                ${service.name} - ${service.price}Fbu
            </option>`);
        });
    } catch (error) {
        handleError(error);
    }
}


// Usage in handleTransactionEdit



// Update setupItemManagement to handle dynamic item addition
function setupItemManagement() {
    $('#itemsContainer').on('click', '.remove-item-button', function() {
        $(this).closest('.item-row').remove();
        updateTotalAmount();
    });

    $('#itemsContainer').on('change', '.serviceSelect, .quantityInput', updateTotalAmount);
}

    // Load existing services into dropdowns for a transaction
    async function loadServices() {
        try {
            const servicesResponse = await fetch('/api/services');
            const services = await servicesResponse.json();

            $('#itemsContainer .serviceSelect').each(function() {
                const select = $(this);
                services.forEach(service => {
                    const option = `<option value="${service.id}">${service.name}</option>`;
                    select.append(option);
                });
            });

        } catch (error) {
            handleError(error);
        }
    }

    async function handleTransactionDelete(event) {
        const id = $(event.currentTarget).data('id');
        if (!confirm('Êtes-vous sûr de vouloir supprimer cette transaction ?')) return;

        try {
            const response = await fetch(`/api/transactions/${id}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            if (!response.ok) throw new Error('Network response was not ok');

            showToast('success', 'Transaction supprimée avec succès');
            loadInitialData();
        } catch (error) {
            handleError(error);
        }
    }

    // Update total amount based on selected services and quantities
    function updateTotalAmount() {
        let total = 0;
        $('.item-row').each(function() {
            const price = $(this).find('.serviceSelect option:selected').data('price') || 0;
            const quantity = parseInt($(this).find('input[type="number"]').val()) || 0;
            total += price * quantity;
        });
        $('#totalAmount').val(total.toFixed(2));
    }
    // Add invoice button to actions column
function createActionButtons(id) {
    return `
        <button class="btn btn-sm btn-outline-primary edit-transaction me-2" data-id="${id}">
            <i class="bi bi-pencil"></i>
        </button>
        <button class="btn btn-sm btn-outline-danger delete-transaction me-2" data-id="${id}">
            <i class="bi bi-trash"></i>
        </button>
        <button class="btn btn-sm btn-outline-success view-invoice" data-id="${id}">
            <i class="bi bi-file-text"></i>
        </button>
    `;
}

// Add invoice modal to the page
$(document).ready(function() {
    // Append invoice modal to body
    $('body').append(`
        <div class="modal fade" id="invoiceModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Facture</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body" id="invoiceContent">
                        <!-- Invoice content will be loaded here -->
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                        <button type="button" class="btn btn-primary" id="printInvoice">
                            <i class="bi bi-printer"></i> Imprimer
                        </button>
                        <button type="button" class="btn btn-success" id="downloadInvoice">
                            <i class="bi bi-download"></i> Télécharger PDF
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `);

    // Handle invoice view button click
    $(document).on('click', '.view-invoice', async function(e) {
        e.preventDefault();
        const transactionId = $(this).data('id');
        try {
            const response = await fetch(`/api/transactions/${transactionId}`);
            if (!response.ok) throw new Error('Erreur lors du chargement de la facture');

            const transaction = await response.json();
            loadInvoice(transaction);
            $('#invoiceModal').modal('show');
        } catch (error) {
            showToast('error', error.message);
        }
    });

    // Handle print button click
    $('#printInvoice').click(function() {
        const content = document.getElementById('invoiceContent');
        const printWindow = window.open('', '_blank');
        printWindow.document.write(

                       // ${getInvoiceStyles()}

                    //${content.innerHTML}

        );
        printWindow.document.close();
        printWindow.focus();
        setTimeout(() => {
            printWindow.print();
            printWindow.close();
        }, 250);
    });

    $('#downloadInvoice').click(async function() {
    try {
        // Get the transaction ID from the data attribute
        const transactionId = $('#invoiceContent .invoice-content').data('transaction-id');
        console.log('Transaction ID:', transactionId); // Log the transaction ID for debugging

        // Check if the transaction ID is valid
        if (!transactionId) {
            showToast('error', 'Transaction ID is missing.'); // Use your existing toast function to show an error
            return; // Exit the function if the ID is missing
        }

        // Fetch the PDF
        const response = await fetch(`/api/transactions/${transactionId}/pdf`, {
            headers: {
                'Accept': 'application/pdf',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Check if the response is okay
        if (!response.ok) throw new Error('Erreur lors du téléchargement du PDF');
        else console.log("Facture télechargé avec succes");

        // Convert response to blob
        const blob = await response.blob();
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `facture-${transactionId}.pdf`; // Set the filename for the download
        document.body.appendChild(a);
        a.click(); // Trigger the download
        window.URL.revokeObjectURL(url); // Clean up
        a.remove(); // Remove the anchor element
    } catch (error) {
        showToast('error', error.message); // Handle any errors
    }
});

});

// Function to load invoice content
function loadInvoice(transaction) {
    const invoiceContent = `
        <div class="invoice-content" data-transaction-id="${transaction.id}">
            <div class="invoice-header d-flex justify-content-between mb-4">
                <div class="company-info">
                    <h2 class="company-name">VOTRE ENTREPRISE</h2>
                    <p class="company-details">
                        Adresse de l'entreprise<br>
                        Code postal, Ville<br>
                        Téléphone: 01 23 45 67 89<br>
                        Email: contact@entreprise.com
                    </p>
                </div>
                <div class="invoice-info text-end">
                    <h3>FACTURE</h3>
                    <p>
                        N°: ${transaction.id}<br>
                        Date: ${new Date(transaction.created_at).toLocaleDateString('fr-FR')}<br>
                    </p>
                </div>
            </div>

            <div class="customer-info mb-4">
                <h4>Facturer à:</h4>
                <p>${transaction.customer.name}<br>
                   ${transaction.customer.address || ''}<br>
                   ${transaction.customer.email || ''}</p>
            </div>

            <table class="table table-bordered mb-4">
                <thead class="table-light">
                    <tr>
                        <th>Service</th>
                        <th class="text-end">Prix unitaire</th>
                        <th class="text-end">Quantité</th>
                        <th class="text-end">Total</th>
                    </tr>
                </thead>
                <tbody>
                    ${transaction.items.map(item => `
                        <tr>
                            <td>${item.service.name}</td>
                            <td class="text-end">${item.price.toLocaleString('fr-FR')} Fbu</td>
                            <td class="text-end">${item.quantity}</td>
                            <td class="text-end">${(item.price * item.quantity).toLocaleString('fr-FR')} Fbu</td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>

            <div class="row justify-content-end">
                <div class="col-md-5">
                    <table class="table table-bordered">
                        <tr>
                            <th>Total HT:</th>
                            <td class="text-end">${(transaction.total_amount / 1.2).toLocaleString('fr-FR')} Fbu</td>
                        </tr>
                        <tr>
                            <th>TVA (20%):</th>
                            <td class="text-end">${(transaction.total_amount - transaction.total_amount / 1.2).toLocaleString('fr-FR')} Fbu</td>
                        </tr>
                        <tr>
                            <th>Total TTC:</th>
                            <td class="text-end">${transaction.total_amount.toLocaleString('fr-FR')} Fbu</td>
                        </tr>
                        <tr>
                            <th>Montant payé:</th>
                            <td class="text-end">${transaction.amount_paid.toLocaleString('fr-FR')} Fbu</td>
                        </tr>
                        ${transaction.payment_status !== 'paid' ? `
                            <tr>
                                <th>Reste à payer:</th>
                                <td class="text-end">${(transaction.total_amount - transaction.amount_paid).toLocaleString('fr-FR')} Fbu</td>
                            </tr>
                        ` : ''}
                    </table>
                </div>
            </div>

            <div class="payment-status mt-4 p-3 rounded ${getStatusClass(transaction.payment_status)}">
                Statut: ${getStatusText(transaction.payment_status)}
            </div>

            <div class="footer mt-5 text-center">
                <p class="small">
                    VOTRE ENTREPRISE - SIRET: XX XXX XXX XXX XXX - TVA: FRXXXXXXXXX<br>
                    Merci de votre confiance !
                </p>
            </div>
        </div>
    `;

    $('#invoiceContent').html(invoiceContent);
    console.log('Loaded transaction ID:', transaction.id);

    // Print the invoice
}

function printInvoice() {
    const printContent = document.getElementById('innvoice-content').innerHTML;
    const printWindow = window.open('', '', 'height=600,width=800');

    printWindow.document.write('<html><head><title>Invoice</title>');
    printWindow.document.write('<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/5.1.3/css/bootstrap.min.css">'); // Add any necessary styles
    printWindow.document.write('</head><body>');
    printWindow.document.write(printContent);
    printWindow.document.write('</body></html>');

    printWindow.document.close(); // Close the document
    printWindow.print(); // Open the print dialog
}

// Helper functions
function getStatusClass(status) {
    const statusClasses = {
        paid: 'bg-success text-white',
        partial: 'bg-warning',
        unpaid: 'bg-danger text-white'
    };
    return statusClasses[status] || '';
}

$('#printInvoice').click(function() {
        printInvoice();
    });

function getStatusText(status) {
    const statusTexts = {
        paid: 'PAYÉ',
        partial: 'PARTIELLEMENT PAYÉ',
        unpaid: 'NON PAYÉ'
    };
    return statusTexts[status] || status;
}

function getInvoiceStyles() {
    return `
        .invoice-content {
            padding: 2rem;
        }
        .company-name {
            color: #2563eb;
            font-weight: bold;
        }
        .company-details {
            font-size: 0.9rem;
        }
        .invoice-info h3 {
            color: #2563eb;
        }
        .table th {
            background-color: #f8f9fa;
        }
        .footer {
            border-top: 1px solid #dee2e6;
            padding-top: 1rem;
        }
        @media print {
            .modal-footer {
                display: none;
            }
        }
    `;
}
});
</script>
@endsection
