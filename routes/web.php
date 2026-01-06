<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SppController;
// Import Controller POS di sini agar rapi
use App\Http\Controllers\PosReportController;
use App\Http\Controllers\PosItemController;
use App\Http\Controllers\PosTransactionController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Halaman Utama
Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

// Grouping menu SPP
Route::prefix('spp')->name('spp.')->group(function () {
    Route::get('/{id}/midtrans', [SppController::class, 'getMidtransToken'])->name('midtrans_token');
    Route::get('/', [SppController::class, 'index'])->name('index');
    Route::post('/{id}/pay', [SppController::class, 'storePayment'])->name('pay');
    Route::get('/generate', [SppController::class, 'createGenerate'])->name('create_generate');
    Route::post('/generate', [SppController::class, 'storeGenerate'])->name('store_generate');
    Route::get('/{id}/print', [SppController::class, 'printInvoice'])->name('print');
});

// Grouping menu POS
Route::prefix('pos')->name('pos.')->group(function () {
    // History
    Route::get('/history', [PosReportController::class, 'index'])->name('history.index');
    Route::get('/history/{id}', [PosReportController::class, 'show'])->name('history.show');

    // Master Barang (Inventory)
    Route::get('/items', [PosItemController::class, 'index'])->name('items.index');
    Route::post('/items', [PosItemController::class, 'store'])->name('items.store');
    Route::put('/items/{id}', [PosItemController::class, 'update'])->name('items.update');
    Route::delete('/items/{id}', [PosItemController::class, 'destroy'])->name('items.destroy');

    // Halaman Kasir / Transaksi
    Route::get('/transaction', [PosTransactionController::class, 'index'])->name('transaction');
    Route::post('/transaction', [PosTransactionController::class, 'store'])->name('transaction.store');
});