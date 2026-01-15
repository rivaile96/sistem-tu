<?php

use Illuminate\Support\Facades\Route;

// =========================================================================
//  IMPORT CONTROLLER
// =========================================================================
use App\Http\Controllers\DashboardController;      // Dashboard Utama
use App\Http\Controllers\ProfileController;        // Profil User
use App\Http\Controllers\StudentController;        // Manajemen Siswa
use App\Http\Controllers\BillController;           // (NEW) Sistem Tagihan & SPP
use App\Http\Controllers\PosItemController;        // Master Barang POS
use App\Http\Controllers\PosBundleController;      // (NEW) Master Paket Bundling
use App\Http\Controllers\PosTransactionController; // Kasir
use App\Http\Controllers\PosReportController;      // Laporan
use App\Http\Controllers\IntegrationController;    // Integrasi PPDB
use App\Http\Controllers\SppController;            // (Legacy) SPP Lama

/*
|--------------------------------------------------------------------------
| Web Routes (Sistem Tata Usaha Sekolah)
|--------------------------------------------------------------------------
|
| Terorganisir berdasarkan modul:
| 1. Dashboard (Executive)
| 2. Manajemen Siswa (Data & Keuangan)
| 3. Tagihan (Billing, Kwitansi, Export, SPP)
| 4. POS (Kantin, Stok, History, Bundling)
| 5. Pengaturan (Integrasi PPDB)
|
*/

// --- HALAMAN DEPAN (Redirect ke Login) ---
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

    // 3. BILLING / TAGIHAN (UPDATED FULL LOGIC)
    // Menggunakan Route::controller agar lebih rapi
    Route::controller(BillController::class)->prefix('bills')->name('bills.')->group(function () {
        // Monitoring & Filter
        Route::get('/', 'index')->name('index'); 
        
        // Form Buat Tagihan (SPP / Manual / Paket POS)
        Route::get('/create', 'create')->name('create'); 
        
        // Simpan Tagihan ke Database
        Route::post('/', 'store')->name('store'); 
        
        // Export Laporan ke Excel/CSV
        Route::get('/export', 'export')->name('export'); 
        
        // Aksi Spesifik per Tagihan (ID)
        Route::patch('/{id}/pay', 'pay')->name('pay');        // Bayar & Potong Stok
        Route::get('/{id}/print', 'print')->name('print');    // Cetak Kwitansi
        Route::delete('/{id}', 'destroy')->name('destroy');   // Hapus Tagihan (Fix Error Tadi)
    });

    // 4. POS (Kantin & Koperasi)
    Route::prefix('pos')->name('pos.')->group(function () {

        // A. Master Barang (Resource Standard)
        Route::resource('items', PosItemController::class);

        // B. Master Paket / Bundling (Untuk Tagihan Daftar Ulang)
        Route::resource('bundles', PosBundleController::class);

        // C. Kasir / Transaksi Harian
        Route::controller(PosTransactionController::class)->group(function() {
            Route::get('/transaction', 'index')->name('transaction');       // Halaman Kasir
            Route::post('/transaction', 'store')->name('transaction.store'); // Proses Bayar
            Route::get('/transaction/{id}/print', 'printStruk')->name('transaction.print'); // Cetak Struk
        });

        // D. Laporan & Riwayat
        Route::controller(PosReportController::class)->prefix('history')->name('history.')->group(function() {
            Route::get('/', 'index')->name('index');
            Route::post('/{id}/repay', 'repay')->name('repay'); // Pelunasan Hutang Kantin
        });
    });

    // 5. PENGATURAN (INTEGRASI)
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/integration', [IntegrationController::class, 'index'])->name('integration');
        Route::post('/integration', [IntegrationController::class, 'update'])->name('integration.update');
        Route::post('/integration/sync', [IntegrationController::class, 'sync'])->name('integration.sync');
    });

    // 6. LEGACY SPP (Sistem Lama - Opsional)
    Route::prefix('spp')->name('spp.')->group(function () {
        Route::get('/', [SppController::class, 'index'])->name('index');
        Route::get('/generate', [SppController::class, 'createGenerate'])->name('create_generate');
        Route::post('/generate', [SppController::class, 'storeGenerate'])->name('store_generate');
    });

    // 7. PROFILE USER
    Route::controller(ProfileController::class)->group(function () {
        Route::get('/profile', 'edit')->name('profile.edit');
        Route::patch('/profile', 'update')->name('profile.update');
        Route::delete('/profile', 'destroy')->name('profile.destroy');
    });
});