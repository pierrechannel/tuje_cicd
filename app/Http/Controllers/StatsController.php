<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\Expense;
use App\Models\Debt;
use App\Models\Payment;
use App\Models\ExpenseCategory;
use App\Models\TransactionItem;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StatsController extends Controller
{
    const CACHE_DURATION = 10; // Cache duration in seconds
    const MONTHS_TO_ANALYZE = 11; // Number of months for analysis

    public function index()
    {
        try {
            $statistics = [
                'summary' => Cache::remember('summary_metrics', self::CACHE_DURATION, fn() => $this->getSummaryMetrics()),
                'category_analysis' => Cache::remember('category_analysis', self::CACHE_DURATION, fn() => $this->getCategoryAnalysis()),
                'trends' => Cache::remember('trend_analysis', self::CACHE_DURATION, fn() => $this->getTrendAnalysis()),
                'detailed_metrics' => Cache::remember('detailed_metrics', self::CACHE_DURATION, fn() => $this->getDetailedMetrics()),
                'revenue_analysis' => Cache::remember('revenue_analysis', self::CACHE_DURATION, fn() => $this->getRevenueAnalysis()),
                'profit_analysis' => Cache::remember('profit_analysis', self::CACHE_DURATION, fn() => $this->getProfitAnalysis()),
                'updated_at' => now()
            ];

            return response()->json($statistics);
        } catch (\Exception $e) {
            \Log::error('Statistics retrieval error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Unable to retrieve statistics',
                'message' => 'An error occurred while fetching statistics.'
            ], 500);
        }
    }

    private function getCategoryAnalysis()
    {
        return [
            'category_totals' => $this->getCategoryTotals(),
            'category_distribution' => $this->getCategoryDistribution(),
            'top_categories' => $this->getTopCategories(),
            'revenue_distribution' => $this->getRevenueDistribution(),
            'expense_distribution' => $this->getExpenseDistribution(),
            'category_trends' => $this->getCategoryTrends(),
        ];
    }

    private function getTrendAnalysis()
    {
        return [
            'monthly_category_trends' => $this->getMonthlyCategoryTrends(),
            'daily_expenses' => $this->getDailyExpenses(),
            'year_comparison' => $this->getYearComparison(),
        ];
    }

    private function getDetailedMetrics()
    {
        return [
            'category_metrics' => $this->getCategoryMetrics(),
            'monthly_comparison' => $this->getMonthlyComparison(),
            'category_growth' => $this->getCategoryGrowth(),
        ];
    }

    private function getMonthlyData($model, $amountColumn, $months, $dateColumn)
    {
        $cacheKey = "monthly_data_{$model}_{$amountColumn}_" . implode('_', $months->toArray());
        return Cache::remember($cacheKey, self::CACHE_DURATION, function () use ($model, $amountColumn, $months, $dateColumn) {
            return $model::select(
                DB::raw('DATE_FORMAT(' . $dateColumn . ', "%Y-%m") as month'),
                DB::raw('SUM(' . $amountColumn . ') as total')
            )
            ->whereIn(DB::raw('DATE_FORMAT(' . $dateColumn . ', "%Y-%m")'), $months)
            ->groupBy('month')
            ->get()
            ->pluck('total', 'month');
        });
    }

    private function getRevenueAnalysis()
    {
        $months = collect(range(0, self::MONTHS_TO_ANALYZE))->map(fn($month) => now()->subMonths($month)->format('Y-m'))->reverse()->values();

        $revenueData = $this->getMonthlyData(Transaction::class, 'amount_paid', $months, 'created_at');
        $expenseData = $this->getMonthlyData(Expense::class, 'amount', $months, 'created_at');
        $paymentData = $this->getMonthlyData(Payment::class, 'amount', $months, 'payment_date');

        return [
            'months' => $months,
            'revenue_trends' => $months->map(fn($month) => $revenueData[$month] ?? 0),
            'expense_trends' => $months->map(fn($month) => $expenseData[$month] ?? 0),
            'payment_trends' => $months->map(fn($month) => $paymentData[$month] ?? 0),
            'average_monthly_revenue' => $revenueData->avg() ?? 0,
            'total_payments' => $paymentData->sum(),
            'best_month' => $this->getBestMonths(),
            'yearly_growth' => $this->calculateYearlyGrowth()
        ];
    }

    private function getCategoryTotals()
    {
        return ExpenseCategory::select(
            'expense_categories.category_name',
            DB::raw('COUNT(expenses.id) as transaction_count'),
            DB::raw('SUM(expenses.amount) as total_amount'),
            DB::raw('AVG(expenses.amount) as average_amount'),
            DB::raw('MAX(expenses.amount) as highest_expense'),
            DB::raw('MIN(expenses.amount) as lowest_expense')
        )
        ->leftJoin('expenses', 'expense_categories.id', '=', 'expenses.category_id')
        ->groupBy('expense_categories.id', 'expense_categories.category_name')
        ->get();
    }

    private function getCategoryDistribution()
    {
        $totalExpenses = Expense::sum('amount') ?: 1; // Prevent division by zero

        return ExpenseCategory::select(
            'expense_categories.category_name',
            DB::raw('SUM(expenses.amount) as total_amount'),
            DB::raw('(SUM(expenses.amount) / ' . $totalExpenses . ' * 100) as percentage')
        )
        ->leftJoin('expenses', 'expense_categories.id', '=', 'expenses.category_id')
        ->groupBy('expense_categories.id', 'expense_categories.category_name')
        ->orderByDesc('total_amount')
        ->get();
    }

    private function getTopCategories()
    {
        return ExpenseCategory::select(
            'expense_categories.category_name',
            DB::raw('SUM(expenses.amount) as total_amount'),
            DB::raw('COUNT(expenses.id) as transaction_count')
        )
        ->leftJoin('expenses', 'expense_categories.id', '=', 'expenses.category_id')
        ->whereMonth('expenses.created_at', now()->month)
        ->groupBy('expense_categories.id', 'expense_categories.category_name')
        ->orderByDesc('total_amount')
        ->take(5)
        ->get();
    }

    private function getCategoryTrends()
    {
        return Expense::select(
            'expense_categories.category_name',
            DB::raw('DATE_FORMAT(expenses.created_at, "%Y-%m") as month'),
            DB::raw('SUM(amount) as total_amount'),
            DB::raw('COUNT(*) as transaction_count')
        )
        ->join('expense_categories', 'expenses.category_id', '=', 'expense_categories.id')
        ->whereBetween('expenses.created_at', [now()->subMonths(6), now()])
        ->groupBy('expense_categories.id', 'expense_categories.category_name', 'month')
        ->orderBy('month')
        ->get();
    }

    private function getMonthlyCategoryTrends()
    {
        return Expense::select(
            'expense_categories.category_name',
            DB::raw('YEAR(expenses.created_at) as year'),
            DB::raw('MONTH(expenses.created_at) as month'),
            DB::raw('SUM(amount) as total_amount')
        )
        ->join('expense_categories', 'expenses.category_id', '=', 'expense_categories.id')
        ->whereYear('expenses.created_at', now()->year)
        ->groupBy('expense_categories.id', 'expense_categories.category_name', 'year', 'month')
        ->orderBy('year')
        ->orderBy('month')
        ->get();
    }

    private function getDailyExpenses()
    {
        return Expense::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('SUM(amount) as total_amount'),
            DB::raw('COUNT(*) as transaction_count')
        )
        ->whereBetween('created_at', [now()->subDays(30), now()])
        ->groupBy('date')
        ->orderBy('date')
        ->get();
    }

    private function getYearComparison()
    {
        $currentYear = now()->year;
        $lastYear = $currentYear - 1;

        return ExpenseCategory::select(
            'expense_categories.category_name',
            DB::raw('SUM(CASE WHEN YEAR(expenses.created_at) = ' . $currentYear . ' THEN expenses.amount ELSE 0 END) as current_year_amount'),
            DB::raw('SUM(CASE WHEN YEAR(expenses.created_at) = ' . $lastYear . ' THEN expenses.amount ELSE 0 END) as previous_year_amount')
        )
        ->leftJoin('expenses', 'expense_categories.id', '=', 'expenses.category_id')
        ->whereYear('expenses.created_at', '>=', $lastYear)
        ->groupBy('expense_categories.id', 'expense_categories.category_name')
        ->get();
    }

    private function getCategoryMetrics()
    {
        $currentMonth = now()->month;

        return ExpenseCategory::select(
            'expense_categories.category_name',
            DB::raw('COUNT(DISTINCT CASE WHEN MONTH(expenses.created_at) = ' . $currentMonth . ' THEN expenses.id END) as transactions_this_month'),
            DB::raw('SUM(CASE WHEN MONTH(expenses.created_at) = ' . $currentMonth . ' THEN expenses.amount END) as amount_this_month'),
            DB::raw('AVG(CASE WHEN MONTH(expenses.created_at) = ' . $currentMonth . ' THEN expenses.amount END) as average_this_month')
        )
        ->leftJoin('expenses', 'expense_categories.id', '=', 'expenses.category_id')
        ->groupBy('expense_categories.id', 'expense_categories.category_name')
        ->get();
    }

    private function getMonthlyComparison()
    {
        $currentMonth = now()->month;
        $lastMonth = now()->subMonth()->month;

        return ExpenseCategory::select(
            'expense_categories.category_name',
            DB::raw('SUM(CASE WHEN MONTH(expenses.created_at) = ' . $currentMonth . ' THEN expenses.amount ELSE 0 END) as current_month'),
            DB::raw('SUM(CASE WHEN MONTH(expenses.created_at) = ' . $lastMonth . ' THEN expenses.amount ELSE 0 END) as previous_month'),
            DB::raw('(SUM(CASE WHEN MONTH(expenses.created_at) = ' . $currentMonth . ' THEN expenses.amount END) -
                     SUM(CASE WHEN MONTH(expenses.created_at) = ' . $lastMonth . ' THEN expenses.amount END)) as difference')
        )
        ->leftJoin('expenses', 'expense_categories.id', '=', 'expenses.category_id')
        ->whereIn(DB::raw('MONTH(expenses.created_at)'), [$currentMonth, $lastMonth])
        ->groupBy('expense_categories.id', 'expense_categories.category_name')
        ->get();
    }

    private function getCategoryGrowth()
    {
        return ExpenseCategory::select(
            'expense_categories.category_name',
            DB::raw('COUNT(DISTINCT expenses.id) as total_transactions'),
            DB::raw('SUM(expenses.amount) as total_amount'),
            DB::raw('AVG(expenses.amount) as average_amount'),
            DB::raw('(MAX(CASE WHEN expenses.created_at >= NOW() - INTERVAL 30 DAY THEN expenses.amount END) -
                     MIN(CASE WHEN expenses.created_at >= NOW() - INTERVAL 30 DAY THEN expenses.amount END)) as amount_range_30_days')
        )
        ->leftJoin('expenses', 'expense_categories.id', '=', 'expenses.category_id')
        ->groupBy('expense_categories.id', 'expense_categories.category_name')
        ->get();
    }

    private function getSummaryMetrics()
    {
        $currentMonth = now()->month;
        $lastMonth = now()->subMonth()->month;

        $currentTransactionRevenue = Transaction::whereMonth('created_at', $currentMonth)->sum('amount_paid');
        $lastMonthTransactionRevenue = Transaction::whereMonth('created_at', $lastMonth)->sum('amount_paid');

        $currentPaymentRevenue = Payment::whereMonth('payment_date', $currentMonth)->sum('amount');
        $lastMonthPaymentRevenue = Payment::whereMonth('payment_date', $lastMonth)->sum('amount');

        $currentDebtRevenue = Debt::whereMonth('created_at', $currentMonth)->sum('amount');
        $lastMonthDebtRevenue = Debt::whereMonth('created_at', $lastMonth)->sum('amount');
        $totalDebts = Debt::sum('amount');

        $currentRevenue = $currentTransactionRevenue + $currentPaymentRevenue;
        $lastMonthRevenue = $lastMonthTransactionRevenue + $lastMonthPaymentRevenue;

        $currentExpenses = Expense::whereMonth('created_at', $currentMonth)->sum('amount');
        $lastMonthExpenses = Expense::whereMonth('created_at', $lastMonth)->sum('amount');

        return [
            'total_revenue' => $currentRevenue,
            'total_expenses' => $currentExpenses,
            'total_depts' => $totalDebts,
            'net_profit' => $currentRevenue - $currentExpenses,
            'profit_margin' => $currentRevenue > 0 ? round(($currentRevenue - $currentExpenses) / $currentRevenue * 100, 2) : 0,
            'revenue_growth' => $lastMonthRevenue > 0 ? round(($currentRevenue - $lastMonthRevenue) / $lastMonthRevenue * 100, 2) : 0,
            'expense_growth' => $lastMonthExpenses > 0 ? round(($currentExpenses - $lastMonthExpenses) / $lastMonthExpenses * 100, 2) : 0,
            'monthly_transactions' => Transaction::whereMonth('created_at', $currentMonth)->count(),
            'avg_transaction_value' => Transaction::whereMonth('created_at', $currentMonth)->avg('amount_paid') ?? 0,
        ];
    }

    private function getBestMonths()
    {
        // Logic to find best month based on transactions and payments
    }

    private function calculateYearlyGrowth()
    {
        $currentYear = now()->year;
        $lastYear = $currentYear - 1;

        $currentYearRevenue = Transaction::whereYear('created_at', $currentYear)->sum('amount_paid');
        $lastYearRevenue = Transaction::whereYear('created_at', $lastYear)->sum('amount_paid');

        if ($lastYearRevenue == 0) {
            return $currentYearRevenue > 0 ? 100 : 0; // Growth calculation
        }

        return round(($currentYearRevenue - $lastYearRevenue) / $lastYearRevenue * 100, 2);
    }

    private function getProfitAnalysis()
    {
        $months = collect(range(0, self::MONTHS_TO_ANALYZE))->map(fn($month) => now()->subMonths($month)->format('Y-m'))->reverse()->values();

        $profitData = [];

        foreach ($months as $month) {
            $revenue = Transaction::whereYear('created_at', Carbon::parse($month)->year)
                ->whereMonth('created_at', Carbon::parse($month)->month)
                ->sum('amount_paid');

            $expenses = Expense::whereYear('created_at', Carbon::parse($month)->year)
                ->whereMonth('created_at', Carbon::parse($month)->month)
                ->sum('amount');

            $profitNet = $revenue - $expenses;
            $percentage = $expenses > 0 ? ($profitNet / $expenses) * 100 : 0;

            $profitData[] = [
                'category_name' => $month,
                'current_month' => $revenue,
                'previous_month' => $expenses, // Change this if you have a different logic for previous month
                'difference' => $profitNet,
                'percentage' => round($percentage, 2),
            ];
        }

        return $profitData;
    }

    private function getRevenueDistribution()
    {
        // Calculate total revenue from transaction items
        $totalRevenue = TransactionItem::join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
            ->whereNotNull('transactions.amount_paid')
            ->sum(DB::raw('transaction_items.price * transaction_items.quantity')) ?: 1;

        return TransactionItem::select(
            'services.type as category_name',
            DB::raw('SUM(transaction_items.price * transaction_items.quantity) as total_amount'),
            DB::raw('(SUM(transaction_items.price * transaction_items.quantity) / ' . $totalRevenue . ' * 100) as percentage')
        )
        ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
        ->join('services', 'transaction_items.service_id', '=', 'services.id')
        ->whereNotNull('transactions.amount_paid')
        ->groupBy('services.type')
        ->orderByDesc('total_amount')
        ->get();
    }

    private function getExpenseDistribution()
    {
        $totalExpenses = Expense::sum('amount') ?: 1;

        return ExpenseCategory::select(
            'expense_categories.category_name as category_name',
            DB::raw('SUM(expenses.amount) as total_amount'),
            DB::raw('(SUM(expenses.amount) / ' . $totalExpenses . ' * 100) as percentage')
        )
        ->leftJoin('expenses', 'expense_categories.id', '=', 'expenses.category_id')
        ->groupBy('expense_categories.id', 'expense_categories.category_name')
        ->orderByDesc('total_amount')
        ->get();
    }
}
