<!-- resources/views/pdf/summary.blade.php -->
<!DOCTYPE html>
<html>
<head>
    <title>Business Summary</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            line-height: 1.6;
        }
        h1 {
            text-align: center;
            color: #333;
        }
        .summary-section {
            margin-bottom: 20px;
        }
        .summary-section h2 {
            border-bottom: 2px solid #ccc;
            padding-bottom: 5px;
        }
        .summary-section p {
            margin: 5px 0;
        }
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .summary-table th, .summary-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .summary-table th {
            background-color: #f4f4f4;
        }
    </style>
</head>
<body>
    <h1>Business Summary Report</h1>

    <div class="summary-section">
        <h2>Date Range</h2>
        <p>{{ $startDate->format('Y-m-d') }} to {{ $endDate->format('Y-m-d') }}</p>
    </div>

    <div class="summary-section">
        <h2>Financial Overview</h2>
        <p>Total Revenue: ${{ number_format($totalRevenue, 2) }}</p>
        <p>Total Paid: ${{ number_format($totalPaid, 2) }}</p>
        <p>Outstanding Amount: ${{ number_format($outstandingAmount, 2) }}</p>
    </div>

    <div class="summary-section">
        <h2>Transaction Status</h2>
        <p>Paid Transactions: {{ $paidTransactions }}</p>
        <p>Partial Transactions: {{ $partialTransactions }}</p>
        <p>Unpaid Transactions: {{ $unpaidTransactions }}</p>
    </div>

    <div class="summary-section">
        <h2>Top Services</h2>
        <table class="summary-table">
            <thead>
                <tr>
                    <th>Service Name</th>
                    <th>Revenue</th>
                    <th>Count</th>
                </tr>
            </thead>
            <tbody>
                @foreach($topServices as $serviceName => $details)
                    <tr>
                        <td>{{ $serviceName }}</td>
                        <td>${{ number_format($details['revenue'], 2) }}</td>
                        <td>{{ $details['count'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="summary-section">
        <h2>Top Customers</h2>
        <table class="summary-table">
            <thead>
                <tr>
                    <th>Customer Name</th>
                    <th>Email</th>
                    <th>Total Spent</th>
                    <th>Transaction Count</th>
                </tr>
            </thead>
            <tbody>
                @foreach($topCustomers as $customer)
                    <tr>
                        <td>{{ $customer['name'] }}</td>
                        <td>{{ $customer['email'] }}</td>
                        <td>${{ number_format($customer['total_spent'], 2) }}</td>
                        <td>{{ $customer['transaction_count'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</body>
</html>
