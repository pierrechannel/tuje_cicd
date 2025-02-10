<?php


namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\Customer;
use App\Models\Transaction;
use App\Models\Debt;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PrintShopController extends Controller
{
    // Service CRUD
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

    public function getServices()
    {
        return response()->json(Service::all());
    }

    public function getService($id)
    {
        $service = Service::findOrFail($id);
        return response()->json($service);
    }

    public function updateService(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'price' => 'sometimes|required|numeric|min:0',
            'type' => 'sometimes|required|in:photocopy,print,photo'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $service = Service::findOrFail($id);
        $service->update($request->all());
        return response()->json($service);
    }

    public function deleteService($id)
    {
        $service = Service::findOrFail($id);
        $service->delete();
        return response()->json(['message' => 'Service deleted successfully.']);
    }

    // Customer CRUD
    public function createCustomer(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:15',
            'email' => 'required|email|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $customer = Customer::create($request->all());
        return response()->json($customer, 201);
    }

    public function getCustomers()
    {
        return response()->json(Customer::all());
    }

    public function getCustomer($id)
    {
        $customer = Customer::findOrFail($id);
        return response()->json($customer);
    }

    public function updateCustomer(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'phone' => 'sometimes|required|string|max:15',
            'email' => 'sometimes|required|email|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $customer = Customer::findOrFail($id);
        $customer->update($request->all());
        return response()->json($customer);
    }

    public function deleteCustomer($id)
    {
        $customer = Customer::findOrFail($id);
        $customer->delete();
        return response()->json(['message' => 'Customer deleted successfully.']);
    }

    // Transaction CRUD
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

    public function getTransactions()
    {
        return response()->json(Transaction::with(['customer', 'service'])->get());
    }

    public function getTransaction($id)
    {
        $transaction = Transaction::with(['customer', 'service'])->findOrFail($id);
        return response()->json($transaction);
    }

    public function updateTransaction(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'customer_id' => 'sometimes|required|exists:customers,id',
            'service_id' => 'sometimes|required|exists:services,id',
            'quantity' => 'sometimes|required|integer|min:1',
            'payment_status' => 'sometimes|required|in:paid,unpaid,partial',
            'amount_paid' => 'sometimes|required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $transaction = Transaction::findOrFail($id);
        $transaction->update($request->all());
        return response()->json($transaction);
    }

    public function deleteTransaction($id)
    {
        $transaction = Transaction::findOrFail($id);
        $transaction->delete();
        return response()->json(['message' => 'Transaction deleted successfully.']);
    }

    // Debt CRUD
    public function createDebt(Request $request)
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

        $debt = Debt::create($request->all());
        return response()->json($debt, 201);
    }

    public function getDebts()
    {
        return response()->json(Debt::with(['customer', 'transaction'])->get());
    }

    public function getDebt($id)
    {
        $debt = Debt::with(['customer', 'transaction'])->findOrFail($id);
        return response()->json($debt);
    }

    public function updateDebt(Request $request, $id)
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

        $debt = Debt::findOrFail($id);
        $debt->update($request->all());
        return response()->json($debt);
    }

    public function deleteDebt($id)
    {
        $debt = Debt::findOrFail($id);
        $debt->delete();
        return response()->json(['message' => 'Debt deleted successfully.']);
    }

    // Payment CRUD
    public function createPayment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'debt_id' => 'required|exists:debts,id',
            'amount' => 'required|numeric|min:0',
            'payment_date' => 'required|date',
            'payment_method' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $payment = Payment::create($request->all());
        return response()->json($payment, 201);
    }

    public function getPayments()
    {
        return response()->json(Payment::with('debt')->get());
    }

    public function getPayment($id)
    {
        $payment = Payment::with('debt')->findOrFail($id);
        return response()->json($payment);
    }

    public function updatePayment(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'debt_id' => 'sometimes|required|exists:debts,id',
            'amount' => 'sometimes|required|numeric|min:0',
            'payment_date' => 'sometimes|required|date',
            'payment_method' => 'sometimes|required|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $payment = Payment::findOrFail($id);
        $payment->update($request->all());
        return response()->json($payment);
    }

    public function deletePayment($id)
    {
        $payment = Payment::findOrFail($id);
        $payment->delete();
        return response()->json(['message' => 'Payment deleted successfully.']);
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
