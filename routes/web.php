<?php

use Illuminate\Support\Facades\Route;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('', function () {
    return view('login');
});

Route::get('/login', function () {
    return view('login');
});
Route::get('/justin/public/tester', function () {
    return 'Hello, Laravel is working!';
});

Route::get('/index', function () {
    return view('index');
});
Route::get('/transactions', function () {
    return view('transactions');
});
Route::get('/debts', function () {
    return view('debts');
});
Route::get('/all_debts', function () {
    return view('debtsAll');
});
Route::get('/stats', function () {
    return view('stats');
});
Route::get('/payments', function () {
    return view('payments');
});

Route::get('/expenses', function () {
    return view('expenses');
});

Route::get('/customers', function () {
    return view('customers');
});

Route::get('/users', function () {
    return view('users');
});

Route::get('/profile', function () {
    return view('profile');
});
Route::get('/test', function () {
    return view('test');
});
Route::get('/services', function () {
    return view('services');
});


use App\Http\Controllers\api\AuthController;

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');




