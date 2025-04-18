<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Customer Debt Report</title>
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
        <h1>Customer Debt Report</h1>
        <p>Generated on: {{ $generatedDate }}</p>
    </div>

    <div class="customer-info">
        <h2>Customer Information</h2>
        <p><strong>Name:</strong> {{ $customer->name }}</p>
        <p><strong>Email:</strong> {{ $customer->email ?? 'N/A' }}</p>
        <p><strong>Phone:</strong> {{ $customer->phone ?? 'N/A' }}</p>
        <p><strong>Address:</strong> {{ $customer->address ?? 'N/A' }}</p>
        <p><strong>Customer ID:</strong> {{ $customer->id }}</p>
    </div>

    <div class="summary-box">
        <h2>Summary</h2>
        <div class="summary-grid">
            <div class="summary-card">
                <h3>Debt Information</h3>
                <p><strong>Total Outstanding Debt:</strong> {{ number_format($totalDebtAmount, 2) }}</p>
                <p><strong>Total Number of Debts:</strong> {{ count($debts) }}</p>
            </div>
            <div class="summary-card">
                <h3>Payment Information</h3>
                <p><strong>Total Payments Made:</strong> {{ number_format($paidAmount ?? $payments->sum('amount'), 2) }}</p>
                <p><strong>Last Payment Date:</strong>
                    @if(count($payments) > 0)
                        {{ date('Y-m-d', strtotime($payments->first()->payment_date)) }}
                    @else
                        No payments recorded
                    @endif
                </p>
            </div>
        </div>
    </div>

    <div class="section-title">Detailed Debt Information</div>

    @foreach($debts as $index => $debt)
    <div class="debt-section">
        <div class="debt-heading">
            Debt #{{ $index + 1 }} (ID: {{ $debt['id'] }})
            <span style="float: right;">
                @if($debt['status'] == 'pending')
                    <span class="badge badge-pending">Pending</span>
                @elseif($debt['status'] == 'paid')
                    <span class="badge badge-paid">Paid</span>
                @elseif($debt['status'] == 'overdue')
                    <span class="badge badge-overdue">Overdue</span>
                @else
                    <span class="badge">{{ ucfirst($debt['status']) }}</span>
                @endif
            </span>
        </div>

        <table>
            <tr>
                <th width="25%">Created Date</th>
                <td width="25%">{{ $debt['created_at'] }}</td>
                <th width="25%">Transaction Reference</th>
                <td width="25%">{{ $debt['transaction_reference'] }}</td>
            </tr>
            <tr>
                <th>Transaction Date</th>
                <td>{{ $debt['transaction_date'] }}</td>
                <th>Original Amount</th>
                <td>{{ number_format($debt['original_amount'], 2) }}</td>
            </tr>
            <tr>
                <th>Amount Paid</th>
                <td>{{ number_format($debt['amount_paid'], 2) }}</td>
                <th>Remaining Balance</th>
                <td class="{{ $debt['remaining_balance'] > 0 ? 'highlight' : '' }}">
                    <strong>{{ number_format($debt['remaining_balance'], 2) }}</strong>
                </td>
            </tr>
        </table>

        @if(isset($debt['transaction_details']) && $debt['transaction_details'] != 'No details available')
        <div style="margin: 10px 0;">
            <strong>Transaction Details:</strong><br>
            {{ $debt['transaction_details'] }}
        </div>
        @endif

        @if(isset($debt['transaction_items']) && count($debt['transaction_items']) > 0)
        <div style="margin-top: 10px; margin-bottom: 5px;">
            <strong>Transaction Items:</strong>
        </div>
        <table class="items-table">
            <thead>
                <tr class="items-row-header">
                    <th>Service/Product</th>
                    <th>Quantity</th>
                    <th>Unit Price</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($debt['transaction_items'] as $item)
                <tr>
                    <td>
                        @if(is_array($item))
                            {{ $item['service'] ?? 'Unnamed Item' }}
                        @else
                            {{ $item->service->name ?? $item->product_name ?? $item->name ?? 'Item #' . $item->id }}
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
                    <td colspan="3" class="text-right">Subtotal:</td>
                    <td class="text-right">{{ number_format($debt['original_amount'], 2) }}</td>
                </tr>
            </tbody>
        </table>
        @else
        <div style="margin-top: 10px; font-style: italic;">No transaction items available</div>
        @endif
    </div>
    @endforeach

    <div style="margin-top: 20px; margin-bottom: 10px;" class="highlight">
        <p class="section-title">Total Outstanding Debt: {{ number_format($totalDebtAmount, 2) }}</p>
    </div>

    @if(count($payments) > 0)
    <div class="payment-history">
        <div class="section-title">Payment History</div>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Method</th>
                    <th>Reference</th>
                    <th>Amount</th>
                    <th>Related Debt ID</th>
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
                    <td colspan="3" class="text-right">Total Payments:</td>
                    <td class="text-right">{{ number_format($payments->sum('amount'), 2) }}</td>
                    <td></td>
                </tr>
            </tbody>
        </table>
    </div>
    @else
    <div class="payment-history">
        <div class="section-title">Payment History</div>
        <p style="font-style: italic;">No payment records found for this customer.</p>
    </div>
    @endif

    <div class="footer">
        <p>This is an official record of customer debt. For any questions or discrepancies, please contact us.</p>
        <p>Document ID: {{ uniqid('DEBT-') }} | Page 1 of 1</p>
    </div>
</body>
</html>
