<?php

use App\Http\Controllers\HeadmasterAuthController;
use App\Http\Controllers\HeadmasterController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Headmaster Routes
|--------------------------------------------------------------------------
|
| Routes for school headmasters/owners with read-only access to 
| financial summaries, ledgers, and reports.
|
*/

// Headmaster Authentication
Route::prefix('headmaster')->name('headmaster.')->group(function () {
    Route::get('/login', [HeadmasterAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [HeadmasterAuthController::class, 'login'])->name('login.post');
    Route::post('/logout', [HeadmasterAuthController::class, 'logout'])->name('logout');
});

// Headmaster Protected Routes
Route::prefix('headmaster')->name('headmaster.')->middleware(['headmaster.auth'])->group(function () {
    Route::get('/dashboard', [HeadmasterController::class, 'dashboard'])->name('dashboard');
    Route::get('/ledgers', [HeadmasterController::class, 'ledgers'])->name('ledgers');
    Route::get('/particular-ledger', [HeadmasterController::class, 'particularLedger'])->name('particular-ledger');
    Route::get('/overdue', [HeadmasterController::class, 'overdue'])->name('overdue');
    Route::get('/invoices', [HeadmasterController::class, 'invoices'])->name('invoices');
});
