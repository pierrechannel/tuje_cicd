<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Debt;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    /**
     * Générer un rapport de synthèse des dettes
     */
    public function debtSummaryReport()
    {
        $totalDebtAmount = Debt::where('amount', '>', 0)->sum('amount');
        $totalDebtsCount = Debt::count();
        $paidDebts = Debt::where('status', 'paid')->count();
        $pendingDebts = Debt::where('status', 'pending')->count();
        $averageDebtAmount = Debt::where('amount', '>', 0)->avg('amount');

        return response()->json([
            'total_debt_amount' => $totalDebtAmount,
            'total_debts_count' => $totalDebtsCount,
            'paid_debts_count' => $paidDebts,
            'pending_debts_count' => $pendingDebts,
            'average_debt_amount' => $averageDebtAmount,
            'generated_at' => now()->format('Y-m-d H:i:s')
        ]);
    }

    /**
     * Générer un rapport des dettes par client
     */
    public function debtsByCustomerReport()
    {
        $customerDebts = DB::table('debts')
            ->join('customers', 'debts.customer_id', '=', 'customers.id')
            ->select(
                'customers.id',
                'customers.name',
                DB::raw('SUM(debts.amount) as total_debt'),
                DB::raw('COUNT(debts.id) as debt_count'),
                DB::raw('COUNT(CASE WHEN debts.status = "paid" THEN 1 END) as paid_count'),
                DB::raw('COUNT(CASE WHEN debts.status = "pending" THEN 1 END) as pending_count')
            )
            ->where('debts.amount', '>', 0)
            ->groupBy('customers.id', 'customers.name')
            ->orderBy('total_debt', 'desc')
            ->get();

        return response()->json([
            'customers' => $customerDebts,
            'generated_at' => now()->format('Y-m-d H:i:s')
        ]);
    }

    /**
     * Générer un rapport des paiements récents
     */
    public function recentPaymentsReport(Request $request)
    {
        $days = $request->input('days', 30);

        $recentPayments = Payment::with(['debt.customer', 'debt.transaction'])
            ->where('payment_date', '>=', Carbon::now()->subDays($days))
            ->orderBy('payment_date', 'desc')
            ->get()
            ->map(function ($payment) {
                return [
                    'payment_id' => $payment->id,
                    'payment_date' => $payment->payment_date,
                    'amount' => $payment->amount,
                    'payment_method' => $payment->payment_method,
                    'customer_name' => $payment->debt->customer->name ?? 'N/A',
                    'debt_id' => $payment->debt_id,
                    'transaction_id' => $payment->debt->transaction_id ?? null
                ];
            });

        return response()->json([
            'payments' => $recentPayments,
            'period' => $days . ' days',
            'total_payments' => count($recentPayments),
            'total_amount' => $recentPayments->sum('amount'),
            'generated_at' => now()->format('Y-m-d H:i:s')
        ]);
    }

    /**
     * Générer un rapport sur les tendances des dettes
     */
    public function debtTrendsReport(Request $request)
    {
        $months = $request->input('months', 6);

        $trends = [];

        for ($i = 0; $i < $months; $i++) {
            $date = Carbon::now()->subMonths($i);
            $monthStart = $date->copy()->startOfMonth();
            $monthEnd = $date->copy()->endOfMonth();

            $newDebts = Debt::whereBetween('created_at', [$monthStart, $monthEnd])->sum('amount');
            $paymentsReceived = Payment::whereBetween('payment_date', [$monthStart, $monthEnd])->sum('amount');

            $trends[] = [
                'month' => $date->format('M Y'),
                'new_debts' => $newDebts,
                'payments_received' => $paymentsReceived,
                'net_change' => $paymentsReceived - $newDebts
            ];
        }

        // Inverser pour avoir les mois en ordre chronologique
        $trends = array_reverse($trends);

        return response()->json([
            'trends' => $trends,
            'generated_at' => now()->format('Y-m-d H:i:s')
        ]);
    }

    /**
     * Générer un rapport des dettes en souffrance
     */
    public function overdueDebtsReport(Request $request)
    {
        $days = $request->input('days', 30);
        $threshold = Carbon::now()->subDays($days);

        $overdueDebts = Debt::with(['customer', 'transaction'])
            ->where('status', 'pending')
            ->where('amount', '>', 0)
            ->where('created_at', '<', $threshold)
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($debt) {
                $daysOverdue = Carbon::parse($debt->created_at)->diffInDays(Carbon::now());

                return [
                    'debt_id' => $debt->id,
                    'customer_name' => $debt->customer->name ?? 'N/A',
                    'customer_id' => $debt->customer_id,
                    'amount' => $debt->amount,
                    'created_at' => $debt->created_at,
                    'days_overdue' => $daysOverdue,
                    'transaction_id' => $debt->transaction_id
                ];
            });

        return response()->json([
            'overdue_debts' => $overdueDebts,
            'total_overdue' => $overdueDebts->sum('amount'),
            'count' => count($overdueDebts),
            'threshold_days' => $days,
            'generated_at' => now()->format('Y-m-d H:i:s')
        ]);
    }

    /**
     * Générer un rapport de performance des remboursements
     */
    public function paymentPerformanceReport()
    {
        $totalDebt = Debt::sum('amount');
        $totalPaid = Payment::sum('amount');
        $repaymentRate = ($totalDebt > 0) ? ($totalPaid / $totalDebt) * 100 : 0;

        // Performance par mois (6 derniers mois)
        $monthlyPerformance = [];
        for ($i = 0; $i < 6; $i++) {
            $date = Carbon::now()->subMonths($i);
            $monthStart = $date->copy()->startOfMonth();
            $monthEnd = $date->copy()->endOfMonth();

            $monthlyDebt = Debt::whereBetween('created_at', [$monthStart, $monthEnd])->sum('amount');
            $monthlyPayment = Payment::whereBetween('payment_date', [$monthStart, $monthEnd])->sum('amount');
            $monthlyRate = ($monthlyDebt > 0) ? ($monthlyPayment / $monthlyDebt) * 100 : 0;

            $monthlyPerformance[] = [
                'month' => $date->format('M Y'),
                'debt_amount' => $monthlyDebt,
                'payment_amount' => $monthlyPayment,
                'repayment_rate' => round($monthlyRate, 2)
            ];
        }

        // Inverser pour avoir les mois en ordre chronologique
        $monthlyPerformance = array_reverse($monthlyPerformance);

        return response()->json([
            'overall_repayment_rate' => round($repaymentRate, 2),
            'total_debt_created' => $totalDebt,
            'total_payments_received' => $totalPaid,
            'monthly_performance' => $monthlyPerformance,
            'generated_at' => now()->format('Y-m-d H:i:s')
        ]);
    }

    /**
     * Générer un rapport pour un client spécifique
     */
    public function customerDebtReport($customerId)
    {
        $customer = Customer::findOrFail($customerId);

        $debts = Debt::where('customer_id', $customerId)
                     ->with('transaction')
                     ->get();

        $payments = Payment::whereIn('debt_id', $debts->pluck('id'))
                          ->orderBy('payment_date', 'desc')
                          ->get();

        $totalDebt = $debts->where('status', 'pending')->sum('amount');
        $totalPaid = $payments->sum('amount');
        $debtHistory = $debts->map(function ($debt) {
            return [
                'debt_id' => $debt->id,
                'amount' => $debt->amount,
                'status' => $debt->status,
                'created_at' => $debt->created_at,
                'transaction_id' => $debt->transaction_id,
                'transaction_date' => $debt->transaction->created_at ?? null,
                'transaction_details' => $debt->transaction->details ?? null
            ];
        });

        $paymentHistory = $payments->map(function ($payment) {
            return [
                'payment_id' => $payment->id,
                'amount' => $payment->amount,
                'payment_date' => $payment->payment_date,
                'payment_method' => $payment->payment_method,
                'debt_id' => $payment->debt_id
            ];
        });

        return response()->json([
            'customer' => [
                'id' => $customer->id,
                'name' => $customer->name,
                'contact' => $customer->phone ?? $customer->email ?? 'N/A'
            ],
            'total_current_debt' => $totalDebt,
            'total_paid' => $totalPaid,
            'debt_history' => $debtHistory,
            'payment_history' => $paymentHistory,
            'generated_at' => now()->format('Y-m-d H:i:s')
        ]);
    }

    /**
     * Générer un rapport CSV des dettes
     */
    public function exportDebtsCSV()
    {
        $debts = Debt::with(['customer', 'transaction'])->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="debts_report.csv"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        $callback = function() use ($debts) {
            $file = fopen('php://output', 'w');

            // En-têtes CSV
            fputcsv($file, [
                'ID',
                'Client',
                'Montant',
                'Statut',
                'Date de création',
                'ID Transaction',
                'Détails Transaction'
            ]);

            // Données
            foreach ($debts as $debt) {
                fputcsv($file, [
                    $debt->id,
                    $debt->customer->name ?? 'N/A',
                    $debt->amount,
                    $debt->status,
                    $debt->created_at,
                    $debt->transaction_id,
                    $debt->transaction->details ?? 'N/A'
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
