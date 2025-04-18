<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Liste des Dettes - PDF</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #aaa;
            padding: 6px;
            text-align: left;
        }
        .badge {
            padding: 3px 6px;
            border-radius: 4px;
            color: #fff;
            font-size: 10px;
        }
        .badge-pending { background-color: #f39c12; }
        .badge-paid { background-color: #27ae60; }
        .badge-overdue { background-color: #e74c3c; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Liste des Dettes - {{ $customer->name }}</h2>
        <p>Client #{{ $customer->id }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Transaction</th>
                <th>Montant</th>
                <th>Statut</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($debts as $debt)
                <tr>
                    <td>{{ $debt->id }}</td>
                    <td>{{ $debt->transaction_id }}</td>
                    <td>{{ number_format($debt->amount, 2) }} €</td>
                    <td>
                        @if($debt->status == 'pending')
                            <span class="badge badge-pending">En attente</span>
                        @elseif($debt->status == 'paid')
                            <span class="badge badge-paid">Payé</span>
                        @elseif($debt->status == 'overdue')
                            <span class="badge badge-overdue">En retard</span>
                        @else
                            {{ ucfirst($debt->status) }}
                        @endif
                    </td>
                    <td>{{ \Carbon\Carbon::parse($debt->created_at)->format('d/m/Y') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p><strong>Total dû :</strong> {{ number_format($debts->where('status', '!=', 'paid')->sum('amount'), 2) }} €</p>
    <p><strong>Total payé :</strong> {{ number_format($debts->where('status', 'paid')->sum('amount'), 2) }} €</p>
    <p><strong>Total général :</strong> {{ number_format($debts->sum('amount'), 2) }} €</p>
</body>
</html>
