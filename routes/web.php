<?php

use Illuminate\Support\Facades\Route;

// =========================================================================
//  IMPORT CONTROLLER
// =========================================================================
use App\Http\Controllers\DashboardController;   // Dashboard Utama
use App\Http\Controllers\ProfileController;     // Profil User
use App\Http\Controllers\SppController;         // (Legacy) SPP Lama
use App\Http\Controllers\BillController;        // (New) Sistem Tagihan Terpusat
use App\Http\Controllers\StudentController;     // Manajemen Siswa
use App\Http\Controllers\PosItemController;     // Master Barang POS
use App\Http\Controllers\PosTransactionController; // Transaksi Kasir
use App\Http\Controllers\PosReportController;   // Laporan POS & Pelunasan
use App\Http\Controllers\IntegrationController; // Integrasi Database PPDB
use App\Http\Controllers\PosBundleController;   // 🔥 Paket / Bundle POS (BARU)

/*
|--------------------------------------------------------------------------
| Web Routes (Sistem Tata Usaha Sekolah)
|--------------------------------------------------------------------------
|
| Terorganisir berdasarkan modul:
| 1. Dashboard (Executive)
| 2. Manajemen Siswa (Data & Keuangan)
| 3. Tagihan (Billing, Kwitansi, Export)
| 4. POS (Kantin, Stok, History)
| 5. Pengaturan (Integrasi PPDB)
|
*/

// --- HALAMAN DEPAN ---
Route::get('/', function () {
    return redirect()->route('login');
});

// Auth Routes (Laravel Breeze)
require __DIR__.'/auth.php';

// =========================================================================
//  PROTECTED ROUTES (Wajib Login & Email Verified)
// =========================================================================
Route::middleware(['auth', 'verified'])->group(function () {

    // 1. DASHBOARD
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // 2. MANAJEMEN SISWA
    Route::prefix('students')->name('students.')->group(function () {
        Route::get('/', [StudentController::class, 'index'])->name('index');
        Route::get('/{id}/finance', [StudentController::class, 'show'])->name('show');
    });

    // 3. BILLING / TAGIHAN
    Route::prefix('bills')->name('bills.')->group(function () {
        Route::get('/', [BillController::class, 'index'])->name('index'); 
        Route::get('/export', [BillController::class, 'export'])->name('export'); 
        Route::get('/generate', [BillController::class, 'create'])->name('create');
        Route::post('/generate', [BillController::class, 'store'])->name('store');
        Route::post('/{id}/pay', [BillController::class, 'pay'])->name('pay');
        Route::get('/{id}/print', [BillController::class, 'print'])->name('print');
    });

    // 4. POS (Kantin & Koperasi)
    Route::prefix('pos')->name('pos.')->group(function () {

        // Master Barang
        Route::resource('items', PosItemController::class);

        // 🔥 MASTER PAKET / BUNDLE (INJECTED)
        Route::resource('bundles', PosBundleController::class)
            ->names('bundles');

        // Kasir
        Route::get('/transaction', [PosTransactionController::class, 'index'])->name('transaction');
        Route::post('/transaction', [PosTransactionController::class, 'store'])->name('transaction.store');
        
        // Cetak Struk
        Route::get('/transaction/{id}/print', [PosTransactionController::class, 'printStruk'])->name('transaction.print');

        // Laporan
        Route::get('/history', [PosReportController::class, 'index'])->name('history.index');
        Route::post('/history/{id}/repay', [PosReportController::class, 'repay'])->name('history.repay');
    });

    // 5. SETTINGS
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/integration', [IntegrationController::class, 'index'])->name('integration');
        Route::post('/integration', [IntegrationController::class, 'update'])->name('integration.update');
        Route::post('/integration/sync', [IntegrationController::class, 'sync'])->name('integration.sync');
    });

    // 6. LEGACY SPP
    Route::prefix('spp')->name('spp.')->group(function () {
        Route::get('/', [SppController::class, 'index'])->name('index');
        Route::get('/generate', [SppController::class, 'createGenerate'])->name('create_generate');
        Route::post('/generate', [SppController::class, 'storeGenerate'])->name('store_generate');
    });

    // 7. PROFILE
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
