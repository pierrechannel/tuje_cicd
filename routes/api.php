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

// For web routes
Route::post('/login', [LoginController::class, 'login'])->name('login');

// If you're using API routes
Route::post('/api/login', [LoginController::class, 'login'])->name('login');
//use Illuminate\Routing\Controller as BaseController;

//Route::get('/sanctum/csrf-cookie', [CsrfCookieController::class, 'show']);

Route::get('/debts/summary', [DebtController::class, 'getDebtSummary']);

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




Route::post('/clear-cache', [CacheController::class, 'clearCache']);

// Dans routes/api.php
Route::get('/transactions/{id}', [TransactionController::class, 'show']);
Route::get('/transactions/{id}/pdf', [TransactionController::class, 'generatePdf']);
Route::get('/customers/{customerId}/total-debt-amount', [DebtController::class, 'getTotalDebtAmount']);
Route::get('/debts/customer-debts', [DebtController::class, 'getCustomerDebt']);



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
