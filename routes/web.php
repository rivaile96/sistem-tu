<?php

use Illuminate\Support\Facades\Route;

// Import Semua Controller
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SppController;
use App\Http\Controllers\PosTransactionController;
use App\Http\Controllers\PosItemController;
use App\Http\Controllers\PosReportController;
use App\Http\Controllers\IntegrationController; // <--- Controller Baru untuk Sync Siswa
use App\Http\Controllers\StudentController;     // <--- TAMBAHAN untuk Modul Siswa

/*
|--------------------------------------------------------------------------
| Web Routes (Sistem Tata Usaha Sekolah)
|--------------------------------------------------------------------------
|
| Terorganisir berdasarkan modul:
| 1. Dashboard
| 2. SPP (Keuangan Sekolah)
| 3. POS (Kantin & Koperasi)
| 4. Pengaturan & Integrasi
|
*/

// --- HALAMAN DEPAN ---
// Redirect otomatis ke login jika belum masuk
Route::get('/', function () {
    return redirect()->route('login');
});

// Load Route Auth bawaan Breeze (Login, Register, Reset Password)
require __DIR__.'/auth.php';


// =========================================================================
//  PROTECTED ROUTES (Wajib Login & Email Verified)
// =========================================================================
Route::middleware(['auth', 'verified'])->group(function () {

    // 1. DASHBOARD UTAMA
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');


    // 2. MODUL SPP (Uang Sekolah)
    Route::prefix('spp')->name('spp.')->group(function () {
        // Halaman Daftar Tagihan
        Route::get('/', [SppController::class, 'index'])->name('index');
        
        // Generate Tagihan Massal
        Route::get('/generate', [SppController::class, 'createGenerate'])->name('create_generate');
        Route::post('/generate', [SppController::class, 'storeGenerate'])->name('store_generate');
        
        // Pembayaran & Invoice
        Route::post('/{id}/pay-manual', [SppController::class, 'storePayment'])->name('pay_manual');
        Route::get('/{id}/midtrans-token', [SppController::class, 'getMidtransToken'])->name('midtrans_token');
        Route::get('/{id}/print', [SppController::class, 'printInvoice'])->name('print');
    });


    // =========================================================
    //  🔥 MODUL MANAJEMEN SISWA (Pusat Data Tagihan & Riwayat)
    // =========================================================
    Route::prefix('students')->name('students.')->group(function () {
        // List semua siswa
        Route::get('/', [StudentController::class, 'index'])->name('index');

        // Detail keuangan 1 siswa (SPP + POS)
        Route::get('/{id}/finance', [StudentController::class, 'show'])->name('show');
    });


    // 3. MODUL POS (Kasir & Stok)
    Route::prefix('pos')->name('pos.')->group(function () {
        // Master Barang (CRUD)
        Route::resource('items', PosItemController::class);

        // Transaksi Kasir
        Route::get('/transaction', [PosTransactionController::class, 'index'])->name('transaction');
        Route::post('/transaction', [PosTransactionController::class, 'store'])->name('transaction.store');
        
        // Cetak Struk (Thermal)
        Route::get('/transaction/{id}/print', [PosTransactionController::class, 'printStruk'])->name('transaction.print');

        // Laporan / Riwayat Penjualan
        Route::get('/history', [PosReportController::class, 'index'])->name('history.index');
        Route::get('/history/{id}', [PosReportController::class, 'show'])->name('history.show');
        Route::post('/history/{id}/repay', [PosReportController::class, 'repay'])->name('history.repay');
    });


    // 4. PENGATURAN & INTEGRASI (Baru!)
    Route::prefix('settings')->name('settings.')->group(function () {
        // Menu Integrasi Kesiswaan (Sync Database)
        Route::get('/integration', [IntegrationController::class, 'index'])->name('integration');
        Route::post('/integration', [IntegrationController::class, 'update'])->name('integration.update');
        Route::post('/integration/sync', [IntegrationController::class, 'sync'])->name('integration.sync');
    });


    // 5. PROFILE USER (Bawaan Laravel)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
