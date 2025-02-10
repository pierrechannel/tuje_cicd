<div class="modal fade" id="transactionModal" tabindex="-1" aria-labelledby="transactionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="transactionModalLabel">Nouvelle Transaction</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="transactionForm" novalidate>
                    <input type="hidden" name="transaction_id" id="transactionId" value="">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="customerSelect" class="form-label">Client</label>
                            <select id="customerSelect" class="form-select" name="customer_id" required>
                                <option value="">Sélectionnez un client</option>
                                <!-- Options will be filled dynamically -->
                            </select>
                            <div class="invalid-feedback">Veuillez sélectionner un client.</div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="serviceSelect" class="form-label">Service</label>
                            <select id="serviceSelect" class="form-select" name="service_id" required>
                                <option value="">Sélectionnez un service</option>
                                <!-- Options will be filled dynamically -->
                            </select>
                            <div class="invalid-feedback">Veuillez sélectionner un service.</div>
                        </div>
                    </div>
                    
                    <div id="itemsContainer">
                        <h5 class="mt-4">Articles</h5>
                        <div class="row item-row mb-3">
                            <div class="col-md-8">
                                <select class="form-select" name="item_service_id[]" required>
                                    <option value="">Sélectionnez un service</option>
                                    <!-- Options will be filled dynamically -->
                                </select>
                                <div class="invalid-feedback">Veuillez sélectionner un service.</div>
                            </div>
                            <div class="col-md-3">
                                <input type="number" class="form-control" name="item_quantity[]" placeholder="Quantité" min="1" required>
                                <div class="invalid-feedback">Veuillez entrer une quantité valide.</div>
                            </div>
                            <div class="col-md-1">
                                <button type="button" class="btn btn-danger remove-item">×</button>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-secondary" id="addItemButton">Ajouter un article</button>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <button type="button" class="btn btn-primary" id="saveTransaction">Enregistrer</button>
            </div>
        </div>
    </div>
</div>