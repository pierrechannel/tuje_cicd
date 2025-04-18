<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Debt;
use App\Models\Customer;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\Service;
use Barryvdh\DomPDF\Facade\Pdf;

use App\Models\Payment; // Ensure you have imported the Payment model
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB; // Import the DB Facade
use Illuminate\Support\Facades\Log;


class DebtController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Fetch only the debts where the amount is greater than 0
        $debts = Debt::with(['customer', 'transaction'])
                       ->where('amount', '>', 0)
                       ->get();

        return response()->json($debts);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'customer_id' => 'required|exists:customers,id',
            'transaction_id' => 'required|exists:transactions,id',
            'amount' => 'required|numeric|min:0',
            'status' => 'required|in:pending,paid'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Start a database transaction
        DB::transaction(function () use ($request) {
            $debt = Debt::create($request->all());
        });

        return response()->json($debt, 201);
    }

    public function show($id)
    {
        $debt = Debt::with(['customer', 'transaction'])->findOrFail($id);
        return response()->json($debt);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'customer_id' => 'sometimes|required|exists:customers,id',
            'transaction_id' => 'sometimes|required|exists:transactions,id',
            'amount' => 'sometimes|required|numeric|min:0',
            'status' => 'sometimes|required|in:pending,paid'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Start a database transaction
        DB::transaction(function () use ($request, $id) {
            $debt = Debt::findOrFail($id);
            $debt->update($request->all());
        });

        return response()->json($debt);
    }

    public function updateDebtPayment(Request $request, $debtId)
    {

 // Validate the incoming payment data
 $validator = Validator::make($request->all(), [
    'amount_paid' => 'required|numeric|min:0',
]);

if ($validator->fails()) {
    return response()->json(['errors' => $validator->errors()], 422);
}

// Start a database transaction
DB::transaction(function () use ($request, $debtId) {
    // Find the debt
    $debt = Debt::findOrFail($debtId);
    $amountPaid = $request->amount_paid;
    $remainingDebt = $debt->amount - $amountPaid;

    // Update the debt's amount and status
    if ($remainingDebt <= 0) {
        $debt->status = 'paid';
        $debt->amount = 0; // Mark as fully paid
    } else {
        $debt->amount = $remainingDebt; // Update remaining amount
    }

    $debt->save(); // Save the updated debt

    // Find the related transaction using the transaction_id in the debt
    $transaction = Transaction::findOrFail($debt->transaction_id);

    // Update the amount paid for the transaction
    $transaction->amount_paid += $amountPaid; // Increment the amount paid
    $transaction->save(); // Save the updated transaction

    // Find the related payment associated with the debt
    $payment = Payment::where('debt_id', $debt->id)->first();

    if ($payment) {
        // Update the existing payment amount
        $payment->amount += $amountPaid; // Increment the payment amount
        $payment->payment_date = date('Y-m-d H:i:s'); // Update payment date if provided
        $payment->payment_method = 'cash'; // Update payment method if provided
        $payment->save(); // Save the updated payment
    } else {
        // Optionally handle the case where no payment record exists
        Payment::create([
            'debt_id' => $debt->id,
            'amount' => $amountPaid,
            'payment_date' => date('Y-m-d H:i:s'),
            'payment_method' => 'cash',
        ]);
    }
});

return response()->json([
    'message' => 'Payment updated, transaction amount modified, and payment recorded successfully.',
   // Return the updated or newly created payment information
], 200);
    }

    public function destroy($id)
    {
        $debt = Debt::findOrFail($id);
        $debt->delete();
        return response()->json(['message' => 'Debt deleted successfully.']);
    }

    public function getCustomerDebts($customerId)
    {
        $debts = Debt::where('customer_id', $customerId)
                     ->where('status', 'pending')
                     ->with('transaction')
                     ->get();

        return response()->json($debts);
    }

    public function getTotalDebtAmount($customerId)
{
    // Calculate the total sum of debts where the amount is greater than 0
    $totalDebtAmount = Debt::where('customer_id', $customerId)
                           ->where('amount', '>', 0)
                           ->sum('amount');

    return response()->json(['total_debt_amount' => $totalDebtAmount]);
}
 /**
     * Get a summary of debts.
     */
    public function getDebtSummary()
    {
        // Calculate the total amount of debts
        $totalDebtAmount = Debt::where('amount', '>', 0)->sum('amount');

        // Count the total number of debts
        $totalDebtsCount = Debt::count();

        // Count total paid debts
        $totalPaidDebtsCount = Debt::where('status', 'paid')->count();

        // Count total pending debts
        $totalPendingDebtsCount = Debt::where('status', 'pending')->count();

        return response()->json([
            'total_debt_amount' => $totalDebtAmount,
            'total_debts_count' => $totalDebtsCount,
            'total_paid_debts_count' => $totalPaidDebtsCount,
            'total_pending_debts_count' => $totalPendingDebtsCount,
        ]);
    }
    public function getCustomerDebt(){
        $debts = Debt::with('customer')->get();
        $customerDebts = $debts->groupBy('customer.name')->map(function($customers) {
            return [
                'customer' => $customers->first()->customer,
                'total_debt' => $customers->sum('amount'),
                'last_payment_date' => $customers->first()->created_at,
            ];
        })->values()->toArray();

        return response()->json($customerDebts);
    }

    public function getDebtsPerCustomer()
{
    // Get all customers with their debts
    $debtsGrouped = Debt::with(['customer', 'transaction'])
        ->get()
        ->groupBy('customer.id')
        ->map(function ($debts) {
            return [
                'customer' => $debts->first()->customer,
                'debts' => $debts->map(function ($debt) {
                    return [
                        'id' => $debt->id,
                        'transaction_id' => $debt->transaction_id,
                        'amount' => $debt->amount,
                        'status' => $debt->status,
                        'created_at' => $debt->created_at,
                        'transaction' => $debt->transaction,
                    ];
                })->toArray(),
            ];
        })
        ->values();

    return response()->json($debtsGrouped);
}

public function getDebtsForCustomer($customerId)
{
    $customer = Customer::find($customerId);

    if (!$customer) {
        return response()->json(['message' => 'Customer not found'], 404);
    }

    $debts = Debt::with('transaction')
        ->where('customer_id', $customerId)
        ->get();

    $response = [
        'customer' => $customer,
        'debts' => $debts->map(function ($debt) {
            return [
                'id' => $debt->id,
                'transaction_id' => $debt->transaction_id,
                'amount' => $debt->amount,
                'status' => $debt->status,
                'created_at' => $debt->created_at,
                'transaction' => $debt->transaction,
            ];
        }),
    ];

    return response()->json($response);
}

public function downloadDebtsPdf($customerId)
{
    try {
        // Find customer with eager loading to reduce queries
        $customer = Customer::findOrFail($customerId);
        Log::info('Customer found', ['customer' => $customer->toArray()]);

        // Get all debts for the customer with optimized eager loading and filter by status
        $debts = Debt::with([
            'transaction',
            'transaction.items.service' // Include service details for better reporting
        ])
        ->where('customer_id', $customerId)
        ->whereIn('status', ['pending', 'unpaid']) // Filter debts by status
        ->get();
        Log::info('Debts retrieved', ['debts' => $debts->toArray()]);

        // Early return if no debts found
        if ($debts->isEmpty()) {
            Log::warning('No debts found for customer', ['customer_id' => $customerId]);
            return response()->json(['message' => 'No debts found for this customer'], 404);
        }

        // Calculate total debt amount
        $totalDebtAmount = $debts->sum('amount');
        Log::info('Total debt amount calculated', ['totalDebtAmount' => $totalDebtAmount]);

        // Get payment history for this customer's debts with eager loading and optimized query
        $debtIds = $debts->pluck('id')->toArray();
        Log::info('Debt IDs for payment retrieval', ['debtIds' => $debtIds]);

        $payments = Payment::with('debt') // Eager load the debt relationship
            ->whereHas('debt', function ($query) use ($debtIds) {
                $query->whereIn('id', $debtIds);
            })
            ->orderBy('payment_date', 'desc')
            ->get();
        Log::info('Payments retrieved', ['payments' => $payments->toArray()]);

        // Prepare debt details for PDF with better handling of relationships
        $detailedDebts = $debts->map(function ($debt) {
            $transaction = $debt->transaction;

            // Handle potential null transaction
            if (!$transaction) {
                return [
                    'id' => $debt->id,
                    'amount' => $debt->amount,
                    'original_amount' => $debt->amount,
                    'status' => $debt->status,
                    'created_at' => $debt->created_at->format('Y-m-d'),
                    'transaction_date' => 'N/A',
                    'transaction_details' => 'No transaction available',
                    'transaction_reference' => 'N/A',
                    'amount_paid' => 0,
                    'remaining_balance' => $debt->amount,
                    'transaction_items' => []
                ];
            }

            // Calculate original amount correctly
            $originalAmount = isset($transaction->total_amount) ?
                $transaction->total_amount :
                ($transaction->amount_paid + $transaction->getRemainingAmountAttribute());

            return [
                'id' => $debt->id,
                'amount' => $debt->amount,
                'original_amount' => $originalAmount,
                'status' => $debt->status,
                'created_at' => $debt->created_at->format('Y-m-d'),
                'transaction_date' => $transaction->created_at ? $transaction->created_at->format('Y-m-d') : 'N/A',
                'transaction_details' => $transaction->details ?? 'No details available',
                'transaction_reference' => $transaction->reference ?? $transaction->id ?? 'N/A',
                'amount_paid' => $transaction->amount_paid ?? 0,
                'remaining_balance' => max($debt->amount, 0), // Ensure non-negative balance
                'transaction_items' => $transaction->items->map(function($item) {
                    return [
                        'service' => $item->service ? $item->service->name : 'Unknown service',
                        'quantity' => $item->quantity,
                        'price' => $item->price,
                        'total' => $item->quantity * $item->price
                    ];
                })
            ];
        });
        Log::info('Detailed debts prepared', ['detailedDebts' => $detailedDebts->toArray()]);

        // Add PDF metadata and set paper size
        $pdf = Pdf::loadView('customer_debts', [
            'customer' => $customer,
            'debts' => $detailedDebts,
            'payments' => $payments,
            'totalDebtAmount' => $totalDebtAmount,
            'paidAmount' => $payments->sum('amount'),
            'generatedDate' => now()->format('Y-m-d H:i')
        ])->setPaper('a4');

        // Generate a more descriptive filename
        $filename = 'dettes_' . str_replace(' ', '_', $customer->name) . '_' . $customer->id . '_' . now()->format('Ymd') . '.pdf';

        return $pdf->download($filename);
    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        Log::error('Customer not found', ['customer_id' => $customerId, 'error' => $e->getMessage()]);
        return response()->json(['message' => 'Customer not found'], 404);
    } catch (\Exception $e) {
        // Log the error for debugging
        Log::error('PDF generation error', ['error' => $e->getMessage()]);
        return response()->json(['message' => 'Failed to generate PDF', 'error' => $e->getMessage()], 500);
    }
}
}
