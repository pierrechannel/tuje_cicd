<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Customer Debt Report</title>
    <style>
        /* Base styles */
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            margin: 30px;
            font-size: 14px;
            line-height: 1.5;
            color: #333;
        }

        /* Typography */
        h1, h2, h3 {
            color: #2c3e50;
            margin-top: 0;
        }

        h1 {
            font-size: 24px;
            margin-bottom: 20px;
        }

        h2 {
            font-size: 18px;
            margin-bottom: 15px;
        }

        h3 {
            font-size: 16px;
            margin-bottom: 10px;
        }

        /* Layout components */
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }

        .content-box {
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 25px;
        }

        .summary-grid {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }

        .summary-card {
            flex: 1;
            background-color: #f8f9fa;
            border-radius: 5px;
            padding: 15px;
        }

        /* Tables */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
            font-size: 14px;
        }

        th, td {
            padding: 12px 15px;
            border-bottom: 1px solid #e9ecef;
            text-align: left;
        }

        th {
            background-color: #f8f9fa;
            font-weight: 600;
        }

        tr:last-child td {
            border-bottom: none;
        }

        .text-right {
            text-align: right;
        }

        .total-row {
            font-weight: 600;
            background-color: #f8f9fa;
        }

        /* Debt sections */
        .debt-section {
            margin-bottom: 30px;
            border-radius: 5px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .debt-heading {
            background-color: #f0f2f5;
            padding: 12px 15px;
            font-weight: 600;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .debt-content {
            padding: 15px;
            background: white;
        }

        /* Status badges */
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .badge-pending {
            background-color: #fff3cd;
            color: #856404;
        }

        .badge-paid {
            background-color: #d4edda;
            color: #155724;
        }

        .badge-overdue {
            background-color: #f8d7da;
            color: #721c24;
        }

        /* Highlight for important information */
        .highlight {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #6c757d;
        }

        /* Footer */
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 12px;
            color: #6c757d;
            padding-top: 15px;
            border-top: 1px solid #eee;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            body {
                margin: 15px;
            }

            .summary-grid {
                flex-direction: column;
            }

            table {
                font-size: 13px;
            }

            th, td {
                padding: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Customer Debt Report</h1>
        <p>Generated on: {{ $generatedDate }}</p>
    </div>

    <div class="content-box">
        <h2>Customer Information</h2>
        <table>
            <tr>
                <th width="25%">Name</th>
                <td>{{ $customer->name }}</td>
            </tr>
            <tr>
                <th>Email</th>
                <td>{{ $customer->email ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Phone</th>
                <td>{{ $customer->phone ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Address</th>
                <td>{{ $customer->address ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>Customer ID</th>
                <td>{{ $customer->id }}</td>
            </tr>
        </table>
    </div>

    <div class="content-box">
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

    <h2>Detailed Debt Information</h2>

    @foreach($debts as $index => $debt)
    <div class="debt-section">
        <div class="debt-heading">
            Debt #{{ $index + 1 }} (ID: {{ $debt['id'] }})
            @if($debt['status'] == 'pending')
                <span class="badge badge-pending">Pending</span>
            @elseif($debt['status'] == 'paid')
                <span class="badge badge-paid">Paid</span>
            @elseif($debt['status'] == 'overdue')
                <span class="badge badge-overdue">Overdue</span>
            @else
                <span class="badge">{{ ucfirst($debt['status']) }}</span>
            @endif
        </div>

        <div class="debt-content">
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
                    <td><strong>{{ number_format($debt['remaining_balance'], 2) }}</strong></td>
                </tr>
            </table>

            @if(isset($debt['transaction_details']) && $debt['transaction_details'] != 'No details available')
            <div style="margin: 15px 0;">
                <strong>Transaction Details:</strong><br>
                {{ $debt['transaction_details'] }}
            </div>
            @endif

            @if(isset($debt['transaction_items']) && count($debt['transaction_items']) > 0)
            <div style="margin-top: 15px;">
                <strong>Transaction Items:</strong>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Service/Product</th>
                        <th class="text-right">Quantity</th>
                        <th class="text-right">Unit Price</th>
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
                        <td class="text-right">
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
            <div style="margin-top: 15px; font-style: italic;">No transaction items available</div>
            @endif
        </div>
    </div>
    @endforeach

    <div class="highlight">
        <h3>Total Outstanding Debt: {{ number_format($totalDebtAmount, 2) }}</h3>
    </div>

    @if(count($payments) > 0)
    <h2>Payment History</h2>
    <div class="content-box">
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Method</th>
                    <th>Reference</th>
                    <th class="text-right">Amount</th>
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
    <h2>Payment History</h2>
    <div class="content-box">
        <p style="font-style: italic;">No payment records found for this customer.</p>
    </div>
    @endif

    <div class="footer">
        <p>This is an official record of customer debt. For any questions or discrepancies, please contact us.</p>
        <p>Document ID: {{ uniqid('DEBT-') }} | Page 1 of 1</p>
    </div>
</body>
</html>
