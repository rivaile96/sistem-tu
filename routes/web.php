<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController; // <--- INI YANG TADI KURANG
use App\Http\Controllers\SppController;
use App\Http\Controllers\PosTransactionController;
use App\Http\Controllers\PosItemController;
use App\Http\Controllers\PosReportController;

/*
|--------------------------------------------------------------------------
| Web Routes (Fixed & Complete)
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('login');
});

// Load Route Auth bawaan Breeze (Login, Register, dll)
require __DIR__.'/auth.php';

// GROUP DASHBOARD (Wajib Login)
Route::middleware(['auth', 'verified'])->group(function () {

    // 1. Dashboard Utama
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // 2. Profile User (FIX ERROR profile.edit)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // 3. Module SPP
    Route::prefix('spp')->name('spp.')->group(function () {
        Route::get('/', [SppController::class, 'index'])->name('index');
        Route::get('/generate', [SppController::class, 'createGenerate'])->name('create_generate');
        Route::post('/generate', [SppController::class, 'storeGenerate'])->name('store_generate');
        Route::post('/{id}/pay-manual', [SppController::class, 'storePayment'])->name('pay_manual');
        Route::get('/{id}/midtrans-token', [SppController::class, 'getMidtransToken'])->name('midtrans_token');
        Route::get('/{id}/print', [SppController::class, 'printInvoice'])->name('print');
    });

    // 4. Module POS
    Route::prefix('pos')->name('pos.')->group(function () {
        Route::get('/transaction', [PosTransactionController::class, 'index'])->name('transaction');
        Route::post('/transaction', [PosTransactionController::class, 'store'])->name('transaction.store');
        Route::resource('items', PosItemController::class);
        Route::get('/history', [PosReportController::class, 'index'])->name('history.index');
        Route::get('/history/{id}', [PosReportController::class, 'show'])->name('history.show');
    });
});