<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Service;
use App\Models\Customer;
use App\Models\Transaction;
use App\Models\Debt;
use Illuminate\Support\Facades\Validator;



class PrintShopController extends Controller
{

        // Gestion des services
        public function createService(Request $request)
        {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'price' => 'required|numeric|min:0',
                'type' => 'required|in:photocopy,print,photo'
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $service = Service::create($request->all());
            return response()->json($service, 201);
        }

        // Gestion des transactions
        public function createTransaction(Request $request)
        {
            $validator = Validator::make($request->all(), [
                'customer_id' => 'required|exists:customers,id',
                'service_id' => 'required|exists:services,id',
                'quantity' => 'required|integer|min:1',
                'payment_status' => 'required|in:paid,unpaid,partial',
                'amount_paid' => 'required|numeric|min:0',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $transaction = Transaction::create($request->all());

            // Gestion des dettes
            if ($request->payment_status !== 'paid') {
                $service = Service::find($request->service_id);
                $total_amount = $service->price * $request->quantity;
                $debt_amount = $total_amount - $request->amount_paid;

                if ($debt_amount > 0) {
                    Debt::create([
                        'customer_id' => $request->customer_id,
                        'transaction_id' => $transaction->id,
                        'amount' => $debt_amount,
                        'status' => 'pending'
                    ]);
                }
            }

            return response()->json($transaction, 201);
        }

        // Récupération des dettes
        public function getCustomerDebts($customerId)
        {
            $debts = Debt::where('customer_id', $customerId)
                         ->where('status', 'pending')
                         ->with(['transaction', 'customer'])
                         ->get();

            return response()->json($debts);
        }

        // Mise à jour du paiement d'une dette
        public function updateDebtPayment(Request $request, $debtId)
        {
            $validator = Validator::make($request->all(), [
                'amount_paid' => 'required|numeric|min:0',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $debt = Debt::findOrFail($debtId);
            $remaining = $debt->amount - $request->amount_paid;

            if ($remaining <= 0) {
                $debt->status = 'paid';
                $debt->amount = 0;
            } else {
                $debt->amount = $remaining;
            }

            $debt->save();
            return response()->json($debt);
        }

        // Statistiques
        public function getStats()
        {
            $stats = [
                'total_sales' => Transaction::sum('amount_paid'),
                'pending_debts' => Debt::where('status', 'pending')->sum('amount'),
                'services_count' => Service::count(),
                'transactions_today' => Transaction::whereDate('created_at', today())->count()
            ];

            return response()->json($stats);
        }

}
