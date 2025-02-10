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
        $companyInfo = '
            <div class="company-info">
                <h2 style="color: #007bff;">VOTRE ENTREPRISE</h2>
                <p>
                    Adresse de l\'entreprise<br>
                    Code postal, Ville<br>
                    Téléphone: 01 23 45 67 89<br>
                    Email: contact@entreprise.com
                </p>
            </div>';

        $customerInfo = '
            <div class="customer-info">
                <h4 style="color: #6c757d;">Facturer à:</h4>
                <p>' . $transaction->customer->name . '<br>' .
                ($transaction->customer->address ?? '') . '<br>' .
                ($transaction->customer->email ?? '') . '</p>
            </div>';

        $invoiceInfo = '
            <h3 style="border-bottom: 2px solid #007bff; padding-bottom: 5px;">FACTURE</h3>
            <p>N°: ' . $transaction->id . '<br>Date: ' . $transaction->created_at->format('Y-m-d') . '</p>';

        $itemsTable = $this->generateItemsTable($transaction->items);
        $summaryTable = $this->generateSummaryTable($transaction);

        $paymentStatus = $this->generatePaymentStatus($transaction->payment_status);

        $footer = '
            <div class="footer">
                <p>VOTRE ENTREPRISE - SIRET: XX XXX XXX XXX XXX - TVA: FRXXXXXXXXX<br>Merci de votre confiance !</p>
            </div>';

        return '
            <style>
                body {
                    font-family: "Helvetica Neue", Arial, sans-serif;
                    margin: 20px;
                    color: #343a40;
                    background-color: #f8f9fa;
                }
                h1 {
                    font-size: 26px;
                    margin-bottom: 20px;
                    color: #343a40;
                    text-align: center;
                }
                h2 {
                    margin: 10px 0;
                }
                h3 {
                    margin: 20px 0;
                    font-weight: bold;
                }
                .company-info, .customer-info {
                    margin-bottom: 20px;
                    border: 1px solid #dee2e6;
                    padding: 15px;
                    border-radius: 5px;
                    background-color: #ffffff;
                }
                table {
                    width: 100%;
                    border-collapse: collapse;
                    margin-bottom: 20px;
                }
                th, td {
                    border: 1px solid #dee2e6;
                    padding: 12px;
                    text-align: left;
                }
                th {
                    background-color: #007bff;
                    color: white;
                    font-weight: bold;
                }
                .text-end {
                    text-align: right;
                }
                .total-amount {
                    font-size: 20px;
                    font-weight: bold;
                    color: #28a745;
                }
                .payment-status {
                    padding: 10px;
                    margin-top: 20px;
                    border-radius: 5px;
                    text-align: center;
                }
                .paid {
                    background-color: #d4edda;
                    color: #155724;
                }
                .unpaid {
                    background-color: #f8d7da;
                    color: #721c24;
                }
                .footer {
                    margin-top: 50px;
                    text-align: center;
                    font-size: 12px;
                    color: #6c757d;
                }
            </style>
            <h1>Invoice #' . $transaction->id . '</h1>
            ' . $companyInfo . '
            ' . $customerInfo . '
            ' . $invoiceInfo . '
            ' . $itemsTable . '
            ' . $summaryTable . '
            ' . $paymentStatus . '
            ' . $footer;
    }


    private function generateItemsTable($items)
    {
        $rows = '';

        foreach ($items as $item) {
            $rows .= '<tr>
                <td>' . $item->service->name . '</td>
                <td class="text-end">' . number_format($item->service->price, 2) . ' Fbu</td>
                <td class="text-end">' . $item->quantity . '</td>
                <td class="text-end">' . number_format($item->service->price * $item->quantity, 2) . ' Fbu</td>
            </tr>';
        }

        return '
        <table>
            <thead>
                <tr>
                    <th>Service</th>
                    <th class="text-end">Prix unitaire</th>
                    <th class="text-end">Quantité</th>
                    <th class="text-end">Total</th>
                </tr>
            </thead>
            <tbody>' . $rows . '</tbody>
        </table>';
    }

    private function generateSummaryTable($transaction)
    {
        $rows = [
            'Total HT:' => number_format($transaction->total_amount / 1.2, 2) . ' Fbu',
            'TVA (20%):' => number_format($transaction->total_amount - ($transaction->total_amount / 1.2), 2) . ' Fbu',
            'Total TTC:' => number_format($transaction->total_amount, 2) . ' Fbu',
            'Montant payé:' => number_format($transaction->amount_paid, 2) . ' Fbu',
        ];

        if ($transaction->payment_status !== 'paid') {
            $rows['Reste à payer:'] = number_format($transaction->total_amount - $transaction->amount_paid, 2) . ' Fbu';
        }

        $summaryRows = '';
        foreach ($rows as $label => $value) {
            $summaryRows .= '<tr><th>' . $label . '</th><td class="text-end">' . $value . '</td></tr>';
        }

        return '
        <div class="row justify-content-end">
            <div class="col-md-5">
                <table class="table table-bordered">' . $summaryRows . '</table>
            </div>
        </div>';
    }

    private function generatePaymentStatus($status)
    {
        $statusClass = $status === 'paid' ? 'paid' : 'unpaid';
        $statusText = $status === 'paid' ? 'Statut: Payé' : 'Statut: Non payé';

        return '<div class="payment-status ' . $statusClass . '">' . $statusText . '</div>';
    }


}

