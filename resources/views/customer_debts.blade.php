<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rapport de Dettes du Client</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            font-size: 12px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .header h1 {
            font-size: 24px;
            margin-bottom: 5px;
        }
        .header p {
            margin: 5px 0;
        }
        .company-info {
            text-align: center;
            margin-bottom: 15px;
        }
        .customer-info {
            margin-bottom: 20px;
            padding: 10px;
            border: 1px solid #ccc;
            background-color: #f9f9f9;
        }
        .summary-box {
            margin-bottom: 20px;
            padding: 10px;
            border: 1px solid #ddd;
            background-color: #f5f5f5;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .total-row {
            font-weight: bold;
            background-color: #f0f0f0;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ccc;
            padding-top: 10px;
        }
        .payment-history {
            margin-top: 20px;
        }
        .section-title {
            font-size: 14px;
            font-weight: bold;
            margin: 15px 0 10px 0;
            padding-bottom: 5px;
            border-bottom: 1px solid #ccc;
        }
        .status-paid {
            color: green;
        }
        .status-pending {
            color: orange;
        }
        .status-overdue {
            color: red;
        }
        .items-table {
            margin-left: 20px;
            width: 95%;
            font-size: 11px;
        }
        .items-row-header {
            background-color: #f8f8f8;
        }
        .debt-section {
            margin-bottom: 25px;
            page-break-inside: avoid;
            border: 1px solid #eee;
            padding: 10px;
        }
        .debt-heading {
            background-color: #eaeaea;
            padding: 8px;
            margin-bottom: 10px;
            font-weight: bold;
            border-left: 4px solid #aaa;
        }
        .summary-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        .summary-card {
            border: 1px solid #ddd;
            padding: 10px;
            background-color: #fafafa;
        }
        .summary-card h3 {
            margin-top: 0;
            border-bottom: 1px solid #eee;
            padding-bottom: 5px;
            margin-bottom: 10px;
        }
        .highlight {
            background-color: #ffffdd;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .badge {
            display: inline-block;
            padding: 3px 7px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .badge-pending {
            background-color: #ffd580;
            color: #805700;
        }
        .badge-paid {
            background-color: #c8e6c9;
            color: #2e7d32;
        }
        .badge-overdue {
            background-color: #ffcdd2;
            color: #c62828;
        }
        .transaction-date {
            font-style: italic;
            color: #666;
            font-size: 11px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Rapport de Dettes du Client</h1>
        <p>Généré le : {{ $generatedDate }}</p>
    </div>

    <div class="customer-info">
        <h2>Informations sur le Client</h2>
        <p><strong>Nom :</strong> {{ $customer->name }}</p>
        <p><strong>Email :</strong> {{ $customer->email ?? 'N/A' }}</p>
        <p><strong>Téléphone :</strong> {{ $customer->phone ?? 'N/A' }}</p>
        <p><strong>Adresse :</strong> {{ $customer->address ?? 'N/A' }}</p>
        <p><strong>ID Client :</strong> {{ $customer->id }}</p>
    </div>

    <div class="summary-box">
        <h2>Résumé</h2>
        <div class="summary-grid">
            <div class="summary-card">
                <h3>Informations sur la Dette</h3>
                <p><strong>Dette Totale en Cours :</strong> {{ number_format($totalDebtAmount, 2) }}</p>
                <p><strong>Nombre Total de Dettes :</strong> {{ count($debts) }}</p>
            </div>
            <div class="summary-card">
                <h3>Informations sur les Paiements</h3>
                <p><strong>Total des Paiements Effectués :</strong> {{ number_format($paidAmount ?? $payments->sum('amount'), 2) }}</p>
                <p><strong>Date du Dernier Paiement :</strong>
                    @if(count($payments) > 0)
                        {{ date('Y-m-d', strtotime($payments->first()->payment_date)) }}
                    @else
                        Aucun paiement enregistré
                    @endif
                </p>
            </div>
        </div>
    </div>

    <div class="section-title">Détails des Dettes</div>

    @foreach($debts as $index => $debt)
    <div class="debt-section">
        <div class="debt-heading">
            Dette #{{ $index + 1 }} (ID: {{ $debt['id'] }})
            <span style="float: right;">
                @if($debt['status'] == 'pending')
                    <span class="badge badge-pending">En attente</span>
                @elseif($debt['status'] == 'paid')
                    <span class="badge badge-paid">Payée</span>
                @elseif($debt['status'] == 'overdue')
                    <span class="badge badge-overdue">En retard</span>
                @else
                    <span class="badge">{{ ucfirst($debt['status']) }}</span>
                @endif
            </span>
        </div>

        <table>
            <tr>
                <th width="25%">Date de Création</th>
                <td width="25%">{{ $debt['created_at'] }}</td>
                <th width="25%">Référence de Transaction</th>
                <td width="25%">{{ $debt['transaction_reference'] }}</td>
            </tr>
            <tr>
                <th>Date de Transaction</th>
                <td>{{ $debt['transaction_date'] }}</td>
                <th>Montant Initial</th>
                <td>{{ number_format($debt['original_amount'], 2) }}</td>
            </tr>
            <tr>
                <th>Montant Payé</th>
                <td>{{ number_format($debt['amount_paid'], 2) }}</td>
                <th>Solde Restant</th>
                <td class="{{ $debt['remaining_balance'] > 0 ? 'highlight' : '' }}">
                    <strong>{{ number_format($debt['remaining_balance'], 2) }}</strong>
                </td>
            </tr>
        </table>

        @if(isset($debt['transaction_details']) && $debt['transaction_details'] != 'No details available')
        <div style="margin: 10px 0;">
            <strong>Détails de la Transaction :</strong><br>
            {{ $debt['transaction_details'] }}
        </div>
        @endif

        @if(isset($debt['transaction_items']) && count($debt['transaction_items']) > 0)
        <div style="margin-top: 10px; margin-bottom: 5px;">
            <strong>Éléments de la Transaction :</strong>
        </div>
        <table class="items-table">
            <thead>
                <tr class="items-row-header">
                    <th>Service/Produit</th>
                    <th>Quantité</th>
                    <th>Prix Unitaire</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($debt['transaction_items'] as $item)
                <tr>
                    <td>
                        @if(is_array($item))
                            {{ $item['service'] ?? 'Élément sans nom' }}
                        @else
                            {{ $item->service->name ?? $item->product_name ?? $item->name ?? 'Élément #' . $item->id }}
                        @endif
                    </td>
                    <td class="text-center">
                        @if(is_array($item))
                            {{ $item['quantity'] ?? 1 }}
                        @else
                            {{ $item->quantity ?? 1 }}
                        @endif
                    </td>
                    <td class="text-right">
                        @if(is_array($item))
                            {{ number_format($item['price'] ?? 0, 2) }}
                        @else
                            {{ number_format($item->price ?? $item->unit_price ?? 0, 2) }}
                        @endif
                    </td>
                    <td class="text-right">
                        @if(is_array($item))
                            {{ number_format(($item['quantity'] ?? 1) * ($item['price'] ?? 0), 2) }}
                        @else
                            {{ number_format(($item->quantity ?? 1) * ($item->price ?? $item->unit_price ?? 0), 2) }}
                        @endif
                    </td>
                </tr>
                @endforeach
                <tr class="total-row">
                    <td colspan="3" class="text-right">Sous-total :</td>
                    <td class="text-right">{{ number_format($debt['original_amount'], 2) }}</td>
                </tr>
            </tbody>
        </table>
        @else
        <div style="margin-top: 10px; font-style: italic;">Aucun élément de transaction disponible</div>
        @endif
    </div>
    @endforeach

    <div style="margin-top: 20px; margin-bottom: 10px;" class="highlight">
        <p class="section-title">Dette Totale en Cours : {{ number_format($totalDebtAmount, 2) }}</p>
    </div>
{{--
    @if(count($payments) > 0)
    <div class="payment-history">
        <div class="section-title">Historique des Paiements</div>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Méthode</th>
                    <th>Référence</th>
                    <th>Montant</th>
                    <th>ID de la Dette Associée</th>
                </tr>
            </thead>
            <tbody>
                @foreach($payments as $payment)
                <tr>
                    <td>{{ date('Y-m-d', strtotime($payment->payment_date)) }}</td>
                    <td>{{ ucfirst($payment->payment_method) }}</td>
                    <td>{{ $payment->reference ?? 'N/A' }}</td>
                    <td class="text-right">{{ number_format($payment->amount, 2) }}</td>
                    <td>{{ $payment->debt_id }}</td>
                </tr>
                @endforeach
                <tr class="total-row">
                    <td colspan="3" class="text-right">Total des Paiements :</td>
                    <td class="text-right">{{ number_format($payments->sum('amount'), 2) }}</td>
                    <td></td>
                </tr>
            </tbody>
        </table>
    </div>
    @else
    <div class="payment-history">
        <div class="section-title">Historique des Paiements</div>
        <p style="font-style: italic;">Aucun enregistrement de paiement trouvé pour ce client.</p>
    </div>
    @endif --}}

    <div class="footer">
        <p>Ceci est un enregistrement officiel des dettes du client. Pour toute question ou divergence, veuillez nous contacter.</p>
        <p>ID du Document : {{ uniqid('DEBT-') }} | Page 1 sur 1</p>
    </div>
</body>
</html>
