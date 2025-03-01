<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\Service;
use App\Models\TransactionItem;
use App\Models\Debt;
use Illuminate\Support\Facades\Validator;
use PDF;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;


class TransactionController extends Controller
{
    public function index()
    {
        return response()->json(Transaction::with(['customer', 'items.service'])->get());
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_id' => 'required|exists:customers,id',
            'payment_status' => 'required|in:paid,unpaid,partial',
            'amount_paid' => 'required|numeric|min:0',
            'items' => 'required|array',
            'items.*.service_id' => 'required|exists:services,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Calculate total amount first
        $total_amount = 0;
        foreach ($request->items as $item) {
            $service = Service::findOrFail($item['service_id']);
            $total_amount += $service->price * $item['quantity'];
        }

        // Create transaction with total_amount
        $transaction = Transaction::create([
            'customer_id' => $request->customer_id,
            'payment_status' => $request->payment_status,
            'amount_paid' => $request->amount_paid,
            'total_amount' => $total_amount,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create transaction items
        foreach ($request->items as $item) {
            $service = Service::findOrFail($item['service_id']);
            $currentPrice = $service->getCurrentPrice();

            TransactionItem::create([
                'transaction_id' => $transaction->id,
                'service_id' => $item['service_id'],
                'quantity' => $item['quantity'],
                'price' => $currentPrice,
            ]);
        }

        // Handle debt management for unpaid transactions
        $this->handleDebtManagement($request, $transaction, $total_amount);

        return response()->json($transaction->load('items.service'), 201);
    }

    public function show($id)
    {
        $transaction = Transaction::with(['customer', 'items.service'])->findOrFail($id);
        return response()->json($transaction);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'customer_id' => 'sometimes|required|exists:customers,id',
            'payment_status' => 'sometimes|required|in:paid,unpaid,partial',
            'amount_paid' => 'sometimes|required|numeric|min:0',
            'items' => 'sometimes|array',
            'items.*.service_id' => 'sometimes|required|exists:services,id',
            'items.*.quantity' => 'sometimes|required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $transaction = Transaction::findOrFail($id);
        $transaction->update($request->only(['customer_id', 'payment_status', 'amount_paid']));

        // Clear existing items
        TransactionItem::where('transaction_id', $transaction->id)->delete();

        // Create new transaction items
        foreach ($request->items as $item) {
            TransactionItem::create([
                'transaction_id' => $transaction->id,
                'service_id' => $item['service_id'],
                'quantity' => $item['quantity'],
                'price' => Service::findOrFail($item['service_id'])->price,
            ]);
        }

        // Optionally handle debt management again if payment status changed
        $this->handleDebtManagement($request, $transaction, $transaction->total_amount);

        return response()->json($transaction->load('items.service'));
    }

    public function destroy($id)
    {
        $transaction = Transaction::findOrFail($id);
        // Delete items before deleting the transaction
        TransactionItem::where('transaction_id', $transaction->id)->delete();
        $transaction->delete();
        return response()->json(['message' => 'Transaction deleted successfully.']);
    }

    private function handleDebtManagement(Request $request, Transaction $transaction, $total_amount)
    {
        if ($request->payment_status !== 'paid') {
            $debt_amount = $total_amount - $request->amount_paid;

            if ($debt_amount > 0) {
                Debt::updateOrCreate(
                    ['customer_id' => $request->customer_id, 'transaction_id' => $transaction->id],
                    ['amount' => $debt_amount, 'status' => 'pending']
                );
            }
        } else {
            // Clear existing debt if the payment status is 'paid'
            Debt::where('transaction_id', $transaction->id)->delete();
        }
    }
    public function generatePdf($id)
{
    try {
        // Fetch the transaction with related customer and items
        $transaction = Transaction::with(['customer', 'items.service'])->findOrFail($id);

        // Generate HTML content for the PDF
        $html = $this->generateHtmlContent($transaction);

        // Load the HTML into the PDF
        $pdf = PDF::loadHTML($html);

        // Get the current time formatted as desired (e.g., Y-m-d_H-i-s)
        $currentTime = now()->format('Y-m-d_H-i-s');

        // Download the PDF with the specified filename including current time
        return $pdf->download("facture-{$id}_{$currentTime}.pdf");
    } catch (\Exception $e) {
        // Handle the error, log it, or return a response
        return response()->json(['error' => 'Could not generate PDF: ' . $e->getMessage()], 500);
    }
}


private function generateHtmlContent($transaction)
{
    return '<!DOCTYPE html>
    <html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Invoice</title>
        <style>
            :root {
                --primary-color: #3a86ff;
                --secondary-color: #8338ec;
                --accent-color: #ff006e;
                --light-gray: #f8f9fa;
                --dark-gray: #343a40;
                --medium-gray: #6c757d;
                --border-radius: 8px;
                --box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            }

            body {
                font-family: "Helvetica Neue", Arial, sans-serif;
                margin: 0;
                padding: 40px;
                color: var(--dark-gray);
                background-color: var(--light-gray);
                line-height: 1.6;
            }

            .invoice-container {
                max-width: 800px;
                margin: 0 auto;
                background: white;
                border-radius: var(--border-radius);
                box-shadow: var(--box-shadow);
                overflow: hidden;
            }

            .invoice-header {
                background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
                color: white;
                padding: 30px;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }

            .logo-area h1 {
                margin: 0;
                font-size: 28px;
                font-weight: 800;
            }

            .logo-area p {
                margin: 5px 0 0;
                opacity: 0.8;
            }

            .invoice-info {
                text-align: right;
            }

            .invoice-info h2 {
                margin: 0;
                font-size: 24px;
                text-transform: uppercase;
            }

            .invoice-info p {
                margin: 5px 0 0;
            }

            .invoice-body {
                padding: 30px;
            }

            .client-grid {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 30px;
                margin-bottom: 40px;
            }

            .company-info, .client-info {
                padding: 20px;
                border-radius: var(--border-radius);
                background-color: var(--light-gray);
            }

            .info-title {
                font-size: 18px;
                font-weight: 600;
                margin: 0 0 15px;
                color: var(--primary-color);
                border-bottom: 2px solid var(--primary-color);
                padding-bottom: 8px;
            }

            .client-details p {
                margin: 8px 0;
            }

            table {
                width: 100%;
                border-collapse: collapse;
                margin: 30px 0;
            }

            thead {
                background-color: var(--primary-color);
                color: white;
            }

            th {
                text-align: left;
                padding: 12px 15px;
                font-weight: 600;
            }

            td {
                padding: 12px 15px;
                border-bottom: 1px solid #ddd;
            }

            tr:last-child td {
                border-bottom: none;
            }

            tr:nth-child(even) {
                background-color: rgba(0, 0, 0, 0.02);
            }

            .text-right {
                text-align: right;
            }

            .summary-section {
                display: flex;
                justify-content: flex-end;
                margin-top: 30px;
            }

            .summary-table {
                width: 50%;
            }

            .summary-table th {
                text-align: left;
                background-color: transparent;
                color: var(--dark-gray);
                font-weight: 600;
                padding: 8px 15px;
                border-bottom: 1px solid #ddd;
            }

            .summary-table td {
                text-align: right;
                padding: 8px 15px;
            }

            .summary-table tr.total-row {
                font-weight: 700;
                font-size: 18px;
            }

            .summary-table tr.total-row td {
                color: var(--accent-color);
            }

            .payment-status {
                margin-top: 30px;
                padding: 15px;
                border-radius: var(--border-radius);
                text-align: center;
                font-weight: 600;
            }

            .status-paid {
                background-color: rgba(40, 167, 69, 0.1);
                color: #28a745;
                border: 1px solid rgba(40, 167, 69, 0.2);
            }

            .status-unpaid {
                background-color: rgba(255, 0, 110, 0.1);
                color: var(--accent-color);
                border: 1px solid rgba(255, 0, 110, 0.2);
            }

            .status-partial {
                background-color: rgba(255, 193, 7, 0.1);
                color: #ffc107;
                border: 1px solid rgba(255, 193, 7, 0.2);
            }

            .invoice-footer {
                background-color: var(--light-gray);
                padding: 20px 30px;
                text-align: center;
                font-size: 14px;
                color: var(--medium-gray);
                border-top: 1px solid #ddd;
            }

            .footer-info {
                margin-top: 10px;
                display: flex;
                justify-content: center;
                gap: 30px;
            }

            @media print {
                body {
                    padding: 0;
                    background-color: white;
                }

                .invoice-container {
                    box-shadow: none;
                }
            }
        </style>
    </head>
    <body>
        <div class="invoice-container">
            <div class="invoice-header">
                <div class="logo-area">
                    <h1>VOTRE ENTREPRISE</h1>
                    <p>Solutions professionnelles</p>
                </div>
                <div class="invoice-info">
                    <h2>Facture</h2>
                    <p>N°: ' . $transaction->id . '</p>
                    <p>Date: ' . $transaction->created_at->format('d/m/Y') . '</p>
                </div>
            </div>

            <div class="invoice-body">
                <div class="client-grid">
                    <div class="company-info">
                        <h3 class="info-title">Nos coordonnées</h3>
                        <div class="client-details">
                            <p><strong>VOTRE ENTREPRISE</strong></p>
                            <p>Adresse de l\'entreprise</p>
                            <p>Code postal, Ville</p>
                            <p>Tél: 01 23 45 67 89</p>
                            <p>Email: contact@entreprise.com</p>
                        </div>
                    </div>

                    <div class="client-info">
                        <h3 class="info-title">Facturer à</h3>
                        <div class="client-details">
                            <p><strong>' . $transaction->customer->name . '</strong></p>
                            <p>' . ($transaction->customer->address ?? 'Adresse non spécifiée') . '</p>
                            <p>Email: ' . ($transaction->customer->email ?? 'Non spécifié') . '</p>
                        </div>
                    </div>
                </div>

                ' . $this->generateItemsTable($transaction->items) . '

                <div class="summary-section">
                    ' . $this->generateSummaryTable($transaction) . '
                </div>

                ' . $this->generatePaymentStatus($transaction->payment_status) . '
            </div>

            <div class="invoice-footer">
                <p>Merci pour votre confiance !</p>
                <div class="footer-info">
                    <span>SIRET: XX XXX XXX XXX XXX</span>
                    <span>TVA: FRXXXXXXXXX</span>
                </div>
            </div>
        </div>
    </body>
    </html>';
}


private function generateItemsTable($items)
{
    $rows = '';

    foreach ($items as $item) {
        $rows .= '<tr>
            <td>' . $item->service->name . '</td>
            <td class="text-right">' . number_format($item->service->price, 2) . ' Fbu</td>
            <td class="text-right">' . $item->quantity . '</td>
            <td class="text-right">' . number_format($item->service->price * $item->quantity, 2) . ' Fbu</td>
        </tr>';
    }

    return '
    <table>
        <thead>
            <tr>
                <th>Service</th>
                <th class="text-right">Prix unitaire</th>
                <th class="text-right">Quantité</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>' . $rows . '</tbody>
    </table>';
}

private function generateSummaryTable($transaction)
{
    $rows = '';

    // Total HT
    $rows .= '<tr>
        <th>Total HT:</th>
        <td>' . number_format($transaction->total_amount / 1.2, 2) . ' Fbu</td>
    </tr>';

    // TVA
    $rows .= '<tr>
        <th>TVA (20%):</th>
        <td>' . number_format($transaction->total_amount - ($transaction->total_amount / 1.2), 2) . ' Fbu</td>
    </tr>';

    // Total TTC (with special styling)
    $rows .= '<tr class="total-row">
        <th>Total TTC:</th>
        <td>' . number_format($transaction->total_amount, 2) . ' Fbu</td>
    </tr>';

    // Amount paid
    $rows .= '<tr>
        <th>Montant payé:</th>
        <td>' . number_format($transaction->amount_paid, 2) . ' Fbu</td>
    </tr>';

    // Remaining balance if not fully paid
    if ($transaction->payment_status !== 'paid') {
        $rows .= '<tr>
            <th>Reste à payer:</th>
            <td>' . number_format($transaction->total_amount - $transaction->amount_paid, 2) . ' Fbu</td>
        </tr>';
    }

    return '<table class="summary-table">' . $rows . '</table>';
}

private function generatePaymentStatus($status)
{
    $statusClass = '';
    $statusText = '';

    switch ($status) {
        case 'paid':
            $statusClass = 'status-paid';
            $statusText = 'Facture Payée';
            break;
        case 'partial':
            $statusClass = 'status-partial';
            $statusText = 'Paiement Partiel';
            break;
        default:
            $statusClass = 'status-unpaid';
            $statusText = 'Facture Non Payée';
            break;
    }

    return '<div class="payment-status ' . $statusClass . '">' . $statusText . '</div>';
}

    public function generateExcel($id)
    {
        try {
            // Fetch the transaction with related customer and items
            $transaction = Transaction::with(['customer', 'items.service'])->findOrFail($id);

            // Create a new Spreadsheet object
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Set some basic properties
            $spreadsheet->getProperties()
                ->setCreator('Your Company')
                ->setLastModifiedBy('Your Company')
                ->setTitle('Invoice #' . $transaction->id)
                ->setSubject('Invoice for ' . $transaction->customer->name)
                ->setDescription('Invoice generated for transaction #' . $transaction->id);

            // Format the header
            $sheet->mergeCells('A1:F1');
            $sheet->setCellValue('A1', 'VOTRE ENTREPRISE');
            $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
            $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $sheet->mergeCells('A2:F2');
            $sheet->setCellValue('A2', 'Solutions professionnelles');
            $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Invoice details
            $sheet->setCellValue('A4', 'FACTURE');
            $sheet->getStyle('A4')->getFont()->setBold(true);

            $sheet->setCellValue('A5', 'N°:');
            $sheet->setCellValue('B5', $transaction->id);

            $sheet->setCellValue('A6', 'Date:');
            $sheet->setCellValue('B6', $transaction->created_at->format('d/m/Y'));

            // Company information
            $sheet->setCellValue('A8', 'NOS COORDONNÉES:');
            $sheet->getStyle('A8')->getFont()->setBold(true);
            $sheet->setCellValue('A9', 'VOTRE ENTREPRISE');
            $sheet->setCellValue('A10', 'Adresse de l\'entreprise');
            $sheet->setCellValue('A11', 'Code postal, Ville');
            $sheet->setCellValue('A12', 'Tél: 01 23 45 67 89');
            $sheet->setCellValue('A13', 'Email: contact@entreprise.com');

            // Client information
            $sheet->setCellValue('D8', 'FACTURER À:');
            $sheet->getStyle('D8')->getFont()->setBold(true);
            $sheet->setCellValue('D9', $transaction->customer->name);
            $sheet->setCellValue('D10', $transaction->customer->address ?? 'Adresse non spécifiée');
            $sheet->setCellValue('D11', 'Email: ' . ($transaction->customer->email ?? 'Non spécifié'));

            // Table headers - starting at row 15
            $sheet->setCellValue('A15', 'Service');
            $sheet->setCellValue('B15', 'Prix unitaire');
            $sheet->setCellValue('C15', 'Quantité');
            $sheet->setCellValue('D15', 'Total');

            $sheet->getStyle('A15:D15')->getFont()->setBold(true);
            $sheet->getStyle('A15:D15')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('3a86ff');
            $sheet->getStyle('A15:D15')->getFont()->getColor()->setRGB('FFFFFF');

            // Table data
            $row = 16;
            foreach ($transaction->items as $item) {
                $sheet->setCellValue('A' . $row, $item->service->name);
                $sheet->setCellValue('B' . $row, number_format($item->service->price, 2) . ' Fbu');
                $sheet->setCellValue('C' . $row, $item->quantity);
                $sheet->setCellValue('D' . $row, number_format($item->service->price * $item->quantity, 2) . ' Fbu');

                // Align right for numbers
                $sheet->getStyle('B' . $row . ':D' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                // Add light background for even rows
                if ($row % 2 == 0) {
                    $sheet->getStyle('A' . $row . ':D' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F8F9FA');
                }

                $row++;
            }

            // Add borders to the table
            $tableStyle = [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'DDDDDD'],
                    ],
                ],
            ];
            $sheet->getStyle('A15:D' . ($row - 1))->applyFromArray($tableStyle);

            // Summary - 2 rows after the table
            $summaryStartRow = $row + 2;

            // Total HT
            $totalHT = $transaction->total_amount / 1.2;
            $sheet->setCellValue('C' . $summaryStartRow, 'Total HT:');
            $sheet->setCellValue('D' . $summaryStartRow, number_format($totalHT, 2) . ' Fbu');
            $sheet->getStyle('C' . $summaryStartRow)->getFont()->setBold(true);
            $sheet->getStyle('D' . $summaryStartRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

            // TVA
            $tva = $transaction->total_amount - $totalHT;
            $sheet->setCellValue('C' . ($summaryStartRow + 1), 'TVA (20%):');
            $sheet->setCellValue('D' . ($summaryStartRow + 1), number_format($tva, 2) . ' Fbu');
            $sheet->getStyle('C' . ($summaryStartRow + 1))->getFont()->setBold(true);
            $sheet->getStyle('D' . ($summaryStartRow + 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

            // Total TTC
            $sheet->setCellValue('C' . ($summaryStartRow + 2), 'Total TTC:');
            $sheet->setCellValue('D' . ($summaryStartRow + 2), number_format($transaction->total_amount, 2) . ' Fbu');
            $sheet->getStyle('C' . ($summaryStartRow + 2))->getFont()->setBold(true);
            $sheet->getStyle('D' . ($summaryStartRow + 2))->getFont()->setBold(true);
            $sheet->getStyle('D' . ($summaryStartRow + 2))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle('D' . ($summaryStartRow + 2))->getFont()->getColor()->setARGB(Color::COLOR_RED);

            // Amount paid
            $sheet->setCellValue('C' . ($summaryStartRow + 3), 'Montant payé:');
            $sheet->setCellValue('D' . ($summaryStartRow + 3), number_format($transaction->amount_paid, 2) . ' Fbu');
            $sheet->getStyle('C' . ($summaryStartRow + 3))->getFont()->setBold(true);
            $sheet->getStyle('D' . ($summaryStartRow + 3))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

            // Remaining balance if not fully paid
            if ($transaction->payment_status !== 'paid') {
                $sheet->setCellValue('C' . ($summaryStartRow + 4), 'Reste à payer:');
                $sheet->setCellValue('D' . ($summaryStartRow + 4), number_format($transaction->total_amount - $transaction->amount_paid, 2) . ' Fbu');
                $sheet->getStyle('C' . ($summaryStartRow + 4))->getFont()->setBold(true);
                $sheet->getStyle('D' . ($summaryStartRow + 4))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            }

            // Payment status
            $statusRow = $transaction->payment_status !== 'paid' ? $summaryStartRow + 6 : $summaryStartRow + 5;
            $sheet->mergeCells('A' . $statusRow . ':D' . $statusRow);

            switch ($transaction->payment_status) {
                case 'paid':
                    $statusText = 'Facture Payée';
                    $statusColor = '28a745'; // Green
                    break;
                case 'partial':
                    $statusText = 'Paiement Partiel';
                    $statusColor = 'ffc107'; // Yellow
                    break;
                default:
                    $statusText = 'Facture Non Payée';
                    $statusColor = 'ff006e'; // Red
                    break;
            }

            $sheet->setCellValue('A' . $statusRow, $statusText);
            $sheet->getStyle('A' . $statusRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('A' . $statusRow)->getFont()->setBold(true);
            $sheet->getStyle('A' . $statusRow)->getFont()->getColor()->setRGB($statusColor);
            $sheet->getStyle('A' . $statusRow)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB(substr($statusColor, 0, 6) . '10');

            // Footer
            $footerRow = $statusRow + 3;
            $sheet->mergeCells('A' . $footerRow . ':D' . $footerRow);
            $sheet->setCellValue('A' . $footerRow, 'Merci pour votre confiance !');
            $sheet->getStyle('A' . $footerRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $sheet->mergeCells('A' . ($footerRow + 1) . ':D' . ($footerRow + 1));
            $sheet->setCellValue('A' . ($footerRow + 1), 'SIRET: XX XXX XXX XXX XXX | TVA: FRXXXXXXXXX');
            $sheet->getStyle('A' . ($footerRow + 1))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Auto-size columns
            foreach (range('A', 'D') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            // Create the Excel file
            $writer = new Xlsx($spreadsheet);

            // Set the filename
            $filename = 'facture-' . $id . '_' . now()->format('Y-m-d_H-i-s') . '.xlsx';

            // Create a temporary file
            $tempFile = tempnam(sys_get_temp_dir(), 'invoice');
            $writer->save($tempFile);

            // Return the file as a download
            return response()->download($tempFile, $filename, [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ])->deleteFileAfterSend(true);

        } catch (\Exception $e) {
            // Handle the error, log it, or return a response
            return response()->json(['error' => 'Could not generate Excel file: ' . $e->getMessage()], 500);
        }
    }

}

