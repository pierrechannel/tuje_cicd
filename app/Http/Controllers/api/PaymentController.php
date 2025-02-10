<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
 use App\Models\Payment;
 use Illuminate\Support\Facades\Validator;


class PaymentController extends Controller
{

    public function index()
    {
        $payments = Payment::with(['debt.customer'])->get(); // Eager load debt and customer

        return response()->json($payments);
    }

        public function store(Request $request)
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

        public function show($id)
        {
            $payment = Payment::with('debt')->findOrFail($id);
            return response()->json($payment);
        }

        public function update(Request $request, $id)
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

        public function destroy($id)
        {
            $payment = Payment::findOrFail($id);
            $payment->delete();
            return response()->json(['message' => 'Payment deleted successfully.']);
        }
    }
