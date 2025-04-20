<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\api\PrintShopController;
use App\Http\Controllers\api\ServiceController;
use App\Http\Controllers\api\CustomerController;
use App\Http\Controllers\api\TransactionController;
use App\Http\Controllers\api\DebtController;
use App\Http\Controllers\api\ExpenseController;
use App\Http\Controllers\api\PaymentController;
use App\Http\Controllers\api\PriceHistoryController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\api\UserController;
use App\Http\Controllers\api\AuthController;
use App\Http\Controllers\StatsController;
use App\Http\Controllers\CsrfCookieController;
use App\Http\Controllers\CacheController;
use Barryvdh\DomPDF\Facade as PDF;
use App\Http\Controllers\api\LoginController;
use App\Http\Controllers\api\ReportController;
use App\Http\Controllers\api\TransactionReportController;
use App\Http\Controllers\api\PaymentReportController;



// For web routes
Route::post('/login', [LoginController::class, 'login'])->name('login');

// If you're using API routes
Route::post('/api/login', [LoginController::class, 'login'])->name('login');
//use Illuminate\Routing\Controller as BaseController;

//Route::get('/sanctum/csrf-cookie', [CsrfCookieController::class, 'show']);

Route::get('/debts/summary', [DebtController::class, 'getDebtSummary']);
Route::get('/customers/{customerId}/debts', [DebtController::class, 'getDebtsForCustomer']);
Route::get('/customers/{customerId}/debts/pdf', [DebtController::class, 'downloadDebtsPdf']);



//use App\Http\Controllers\CsrfCookieController;

// Public Routes
Route::post('/login', [AuthController::class, 'login']);
// In routes/api.php
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
});
//Route::get('/sanctum/csrf-cookie', [CsrfCookieController::class, 'show']);

// Authenticated Routes
//Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Profile Routes
    Route::get('/profile', [ProfileController::class, 'index']);
    Route::get('/profile/{id}', [ProfileController::class, 'show']);

    Route::put('/profile', [ProfileController::class, 'update']);
    //Route::put('/profile/{id}/password', [ProfileController::class, 'updatePassword'])->middleware('auth');
    Route::middleware('auth:sanctum')->group(function () {
        Route::put('/profile/{user}/password', [UserController::class, 'updatePassword']);
    });
    Route::post('/profile/upload-image', [ProfileController::class, 'uploadImage']);

    // Print Shop Routes
    Route::put('/debts/{id}/payment', [DebtController::class, 'updateDebtPayment']);

    // Resource Routes
    Route::apiResource('services', ServiceController::class);
    Route::apiResource('customers', CustomerController::class);
    Route::apiResource('transactions', TransactionController::class);
    Route::apiResource('expenses', ExpenseController::class);
    Route::apiResource('debts', DebtController::class);
    Route::apiResource('payments', PaymentController::class);

    Route::get('/debts/summary', [DebtController::class, 'getDebtSummary']);
    Route::get('/debts-per-customer', [DebtController::class, 'getDebtsPerCustomer']);


    // Additional Debt Routes
    Route::get('/customers/{customerId}/debts', [DebtController::class, 'getCustomerDebts']);
    Route::apiResource('users', UserController::class);

//});

// User API Routes

// Stats Route
Route::get('/stats', [StatsController::class, 'index']);

// Expense Categories Route
Route::get('/categories', [ExpenseController::class, 'getCategories']);
Route::post('/services/{service}/price', [PriceHistoryController::class, 'updatePrice']);
Route::get('transactions/{id}/excel', [TransactionController::class, 'generateExcel']);


// Routes pour les rapports
Route::prefix('reports')->group(function () {
    Route::get('/debt-summary', [App\Http\Controllers\api\ReportController::class, 'debtSummaryReport']);
    Route::get('/debts-by-customer', [App\Http\Controllers\api\ReportController::class, 'debtsByCustomerReport']);
    Route::get('/recent-payments', [App\Http\Controllers\api\ReportController::class, 'recentPaymentsReport']);
    Route::get('/debt-trends', [App\Http\Controllers\api\ReportController::class, 'debtTrendsReport']);
    Route::get('/overdue-debts', [App\Http\Controllers\api\ReportController::class, 'overdueDebtsReport']);
    Route::get('/payment-performance', [App\Http\Controllers\api\ReportController::class, 'paymentPerformanceReport']);
    Route::get('/customer/{customerId}', [App\Http\Controllers\api\ReportController::class, 'customerDebtReport']);
    Route::get('/export-csv', [App\Http\Controllers\api\ReportController::class, 'exportDebtsCSV']);
});

// Transaction Summary Report
Route::get('/reports/transaction-summary', [TransactionReportController::class, 'transactionSummary'])
    ->name('reports.transaction-summary');

// Customer Activity Report
Route::get('/reports/customer-activity', [TransactionReportController::class, 'customerActivity'])
    ->name('reports.customer-activity');

// Debt Report
Route::get('/reports/debt', [TransactionReportController::class, 'debtReport'])
    ->name('reports.debt');

// Service Performance Report
Route::get('/reports/service-performance', [TransactionReportController::class, 'servicePerformance'])
    ->name('reports.service-performance');

// Excel Dashboard Report
Route::get('/reports/dashboard-excel', [TransactionReportController::class, 'dashboardExcel'])
    ->name('reports.dashboard-excel');

// PDF Summary Report
Route::get('/reports/summary-pdf', [TransactionReportController::class, 'summaryPdf'])
    ->name('reports.summary-pdf');

Route::post('/clear-cache', [CacheController::class, 'clearCache']);

// Dans routes/api.php
Route::get('/transactions/{id}', [TransactionController::class, 'show']);
Route::get('/transactions/{id}/pdf', [TransactionController::class, 'generatePdf']);
Route::get('/customers/{customerId}/total-debt-amount', [DebtController::class, 'getTotalDebtAmount']);
//Route::get('/debts/customer-debts', [DebtController::class, 'getCustomerDebt']);


// Define routes for the ReportController
Route::get('/reports/summary', [PaymentReportController::class, 'summaryReport'])->name('reports.summary');
Route::get('/reports/detailed', [PaymentReportController::class, 'detailedReport'])->name('reports.detailed');
Route::get('/reports/payment-methods', [PaymentReportController::class, 'paymentMethodReport'])->name('reports.paymentMethods');
Route::get('/reports/customers', [PaymentReportController::class, 'customerReport'])->name('reports.customers');
Route::get('/reports/monthly', [PaymentReportController::class, 'monthlyReport'])->name('reports.monthly');
Route::get('/reports/yearly', [PaymentReportController::class, 'yearlyReport'])->name('reports.yearly');
Route::get('/reports/overdue', [PaymentReportController::class, 'overdueReport'])->name('reports.overdue');


/*
Route::middleware(['auth'])->group(function () {
    Route::get('/services/{service}/price', [ServicePriceController::class, 'show'])
        ->name('services.price.show');
    Route::post('/services/{service}/price', [ServicePriceController::class, 'updatePrice'])
        ->name('services.price.update');
    Route::get('/services/{service}/price/history', [ServicePriceController::class, 'getPriceHistory'])
        ->name('services.price.history');
    Route::get('/services/{service}/price/export', [ServicePriceController::class, 'exportPriceHistory'])
        ->name('services.price.export');
});
*/

// ****************************************************************************

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\StockController;
use Illuminate\Support\Facades\Route;

// Category routes
Route::apiResource('categories', CategoryController::class);

// Supplier routes
Route::apiResource('suppliers', SupplierController::class);

// Product routes
Route::apiResource('products', ProductController::class);
Route::get('products/stock/low', [ProductController::class, 'lowStock']);
Route::get('products/stock/out', [ProductController::class, 'outOfStock']);

// Stock management routes
Route::post('stock/receive', [StockController::class, 'receiveStock']);
Route::post('stock/ship', [StockController::class, 'shipStock']);
Route::get('stock/history', [StockController::class, 'stockHistory']);
Route::get('stock/inventory', [StockController::class, 'inventory']);
Route::get('stock/alerts', [StockController::class, 'stockAlert
