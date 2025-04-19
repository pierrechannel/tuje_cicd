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


    public function destroy($id)
    {
        $debt = Debt::findOrFail($id);
        $debt->delete();
        return response()->json(['message' => 'Debt deleted successfully.']);
    }

    /**
 * Update payment for a transaction and handle multiple debts if excess payment
 *
 * @param Request $request
 * @param int $id
 * @return JsonResponse
 */
public function updateDebtPayment(Request $request, $id)
{
    $validator = Validator::make($request->all(), [
        'amount_paid' => 'required|numeric|min:0',
    ]);

    if ($validator->fails()) {
        return response()->json(['errors' => $validator->errors()], 422);
    }

    // Find the transaction
    $transaction = Transaction::findOrFail($id);

    // Calculate the new amount paid
    $newAmountPaid = $request->amount_paid;
    $oldAmountPaid = $transaction->amount_paid;
    $additionalPayment = $newAmountPaid - $oldAmountPaid;

    // If there's no additional payment, just return the transaction
    if ($additionalPayment <= 0) {
        return response()->json($transaction->load('customer'));
    }

    // Update the transaction amount paid
    $transaction->amount_paid = $newAmountPaid;

    // Determine the new payment status
    $transaction->payment_status = $this->determinePaymentStatus($newAmountPaid, $transaction->total_amount);
    $transaction->save();

    // Calculate if there's excess payment after covering this transaction's debt
    $currentDebt = $transaction->total_amount - $oldAmountPaid;
    $excessPayment = $additionalPayment - $currentDebt;

    // Update or delete the debt record for this transaction
    $currentDebtRecord = Debt::where('transaction_id', $transaction->id)->first();

    if ($currentDebtRecord) {
        if ($excessPayment >= 0) {
            // If excess payment covers the entire debt, delete the debt record
            //$currentDebtRecord->delete();
        } else {
            // Otherwise update the debt amount
            $currentDebtRecord->amount = $transaction->total_amount - $newAmountPaid;
            $currentDebtRecord->save();
        }
    }

    // If there's excess payment, apply it to other debts from the same customer
    if ($excessPayment > 0) {
        $this->applyExcessPaymentToDebts($transaction->customer_id, $excessPayment);
    }

    return response()->json([
        'transaction' => $transaction->load('customer'),
        'message' => $excessPayment > 0 ?
            'Payment updated and excess amount applied to other debts.' :
            'Payment updated successfully.'
    ]);
}
/**
 * Apply excess payment to other debts from the same customer
 *
 * @param int $customerId
 * @param float $excessPayment
 * @return void
 */
private function applyExcessPaymentToDebts($customerId, $excessPayment)
{
    // Get all pending debts for this customer, ordered by creation date (oldest first)
    $otherDebts = Debt::where('customer_id', $customerId)
                      ->where('status', 'pending')
                      ->orderBy('created_at', 'asc')
                      ->with('transaction')
                      ->get();

    foreach ($otherDebts as $debt) {
        // If we've used all the excess payment, break out of the loop
        if ($excessPayment <= 0) {
            break;
        }

        $transaction = $debt->transaction;

        // Calculate how much of this debt can be paid with the excess
        $debtAmount = $debt->amount;
        $paymentToApply = min($excessPayment, $debtAmount);

        // Update the transaction's amount paid
        $transaction->amount_paid += $paymentToApply;
        $transaction->payment_status = $this->determinePaymentStatus(
            $transaction->amount_paid,
            $transaction->total_amount
        );
        $transaction->save();

        // Update or delete the debt record
        if ($paymentToApply >= $debtAmount) {
            // If payment covers the entire debt, delete the debt record
            //$debt->delete();
        } else {
            // Otherwise update the debt amount
            $debt->amount = $debtAmount - $paymentToApply;
            $debt->save();
        }

        // Reduce the excess payment by the amount applied
        $excessPayment -= $paymentToApply;
    }

    // If there's still excess payment after clearing all debts, you could:
    // 1. Add a credit record for the customer
    // 2. Refund the excess
    // 3. Leave it as an overpayment on the current transaction
    // For now, we'll just leave it as an overpayment on the current transaction
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


/**
 * Determine the payment status based on amount paid vs total amount
 *
 * @param float $amountPaid
 * @param float $totalAmount
 * @return string
 */
private function determinePaymentStatus($amountPaid, $totalAmount)
{
    if ($amountPaid >= $totalAmount) {
        return 'paid';
    } elseif ($amountPaid > 0) {
        return 'partial';
    } else {
        return 'unpaid';
    }
}
}
