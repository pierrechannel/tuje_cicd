<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class PaymentReportController extends Controller
{
    /**
     * Generate a summary report of payments.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function summaryReport()
    {
        $summary = Payment::join('debts', 'payments.debt_id', '=', 'debts.id')
            ->join('customers', 'debts.customer_id', '=', 'customers.id')
            ->select('customers.name as customer_name', DB::raw('SUM(payments.amount) as total_paid'))
            ->groupBy('customers.name')
            ->get();

        return response()->json($summary);
    }

    /**
     * Generate a detailed report of payments within a date range.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function detailedReport(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $detailedReport = Payment::with(['debt.customer'])
            ->whereBetween('payment_date', [$startDate, $endDate])
            ->get();

        return response()->json($detailedReport);
    }

    /**
     * Generate a report of payments by payment method.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function paymentMethodReport()
    {
        $report = Payment::select('payment_method', DB::raw('COUNT(*) as count'), DB::raw('SUM(amount) as total'))
            ->groupBy('payment_method')
            ->get();

        return response()->json($report);
    }

    /**
     * Generate a report of payments by customer.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function customerReport()
    {
        $report = Payment::join('debts', 'payments.debt_id', '=', 'debts.id')
            ->join('customers', 'debts.customer_id', '=', 'customers.id')
            ->select('customers.name as customer_name', DB::raw('COUNT(payments.id) as payment_count'), DB::raw('SUM(payments.amount) as total_paid'))
            ->groupBy('customers.name')
            ->get();

        return response()->json($report);
    }

    /**
     * Generate a report of monthly payments.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function monthlyReport()
    {
        $report = Payment::select(DB::raw('YEAR(payment_date) as year'), DB::raw('MONTH(payment_date) as month'), DB::raw('SUM(amount) as total'))
            ->groupBy(DB::raw('YEAR(payment_date)'), DB::raw('MONTH(payment_date)'))
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get();

        return response()->json($report);
    }

    /**
     * Generate a report of yearly payments.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function yearlyReport()
    {
        $report = Payment::select(DB::raw('YEAR(payment_date) as year'), DB::raw('SUM(amount) as total'))
            ->groupBy(DB::raw('YEAR(payment_date)'))
            ->orderBy('year', 'asc')
            ->get();

        return response()->json($report);
    }

    /**
     * Generate a report of overdue payments.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function overdueReport()
    {
        $report = Payment::join('debts', 'payments.debt_id', '=', 'debts.id')
            ->where('debts.due_date', '<', now())
            ->whereNull('payments.payment_date')
            ->select('debts.id as debt_id', 'debts.amount as debt_amount', 'debts.due_date')
            ->get();

        return response()->json($report);
    }
}
