<?php

use Illuminate\Support\Facades\Route;

// =========================================================================
//  IMPORT CONTROLLER
// =========================================================================
use App\Http\Controllers\DashboardController;      // Dashboard Utama
use App\Http\Controllers\ProfileController;        // Profil User
use App\Http\Controllers\StudentController;        // Manajemen Siswa
use App\Http\Controllers\BillController;           // Sistem Tagihan & SPP
use App\Http\Controllers\PosItemController;        // Master Barang POS
use App\Http\Controllers\PosBundleController;      // Master Paket Bundling
use App\Http\Controllers\PosTransactionController; // Kasir
use App\Http\Controllers\PosReportController;      // Laporan POS
use App\Http\Controllers\IntegrationController;    // Integrasi PPDB
use App\Http\Controllers\SppController;            // (Legacy) SPP Lama
use App\Http\Controllers\SchoolSettingController;  // Pengaturan Sekolah

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
| 6. Identitas Sekolah (Kop Surat)
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
        Route::get('/create', [StudentController::class, 'create'])->name('create');
        Route::post('/', [StudentController::class, 'store'])->name('store');
        Route::get('/import', [StudentController::class, 'importForm'])->name('import');
        Route::post('/import', [StudentController::class, 'importCsv'])->name('import.process');
        Route::get('/template', [StudentController::class, 'downloadTemplate'])->name('template');
        Route::get('/{id}', [StudentController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [StudentController::class, 'edit'])->name('edit');
        Route::put('/{id}', [StudentController::class, 'update'])->name('update');
        Route::delete('/{id}', [StudentController::class, 'destroy'])->name('destroy');
        Route::get('/{id}/status', [StudentController::class, 'formUbahStatus'])->name('ubah-status');
        Route::post('/{id}/status', [StudentController::class, 'ubahStatus'])->name('ubah-status.process');
        // Finance detail (backward compat)
        Route::get('/{id}/finance', [StudentController::class, 'show'])->name('finance');
    });

    // 3. BILLING / TAGIHAN (SYSTEM BARU)
    Route::controller(BillController::class)->prefix('bills')->name('bills.')->group(function () {
        Route::get('/', 'index')->name('index'); 
        Route::get('/create', 'create')->name('create'); 
        Route::post('/', 'store')->name('store'); 
        Route::get('/export', 'export')->name('export'); 
        
        // Aksi Spesifik per Tagihan (ID)
        // Note: Pakai POST untuk bayar biar aman dari error method patch
        Route::post('/{id}/pay', 'pay')->name('pay');        
        Route::get('/{id}/print', 'print')->name('print');    
        Route::delete('/{id}', 'destroy')->name('destroy');   
    });

    // 4. POS (KANTIN & KOPERASI)
    Route::prefix('pos')->name('pos.')->group(function () {
        // A. Master Barang
        Route::resource('items', PosItemController::class);

        // B. Master Paket / Bundling
        Route::resource('bundles', PosBundleController::class);

        // C. Kasir / Transaksi Harian
        Route::controller(PosTransactionController::class)->group(function() {
            Route::get('/transaction', 'index')->name('transaction');       
            Route::post('/transaction', 'store')->name('transaction.store'); 
            Route::get('/transaction/{id}/print', 'printStruk')->name('transaction.print'); 
        });

        // D. Laporan & Riwayat
        Route::controller(PosReportController::class)->prefix('history')->name('history.')->group(function() {
            Route::get('/', 'index')->name('index');
            Route::post('/{id}/repay', 'repay')->name('repay'); 
        });
    });

    // 5. PENGATURAN SYSTEM (INTEGRASI)
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/integration', [IntegrationController::class, 'index'])->name('integration');
        Route::post('/integration', [IntegrationController::class, 'update'])->name('integration.update');
        Route::post('/integration/sync', [IntegrationController::class, 'sync'])->name('integration.sync');
    });

    // 6. 🔥 IDENTITAS SEKOLAH (KOP SURAT & LOGO) 🔥
    // Kita taruh diluar grup 'settings' biar URL-nya pendek: /school-settings
    Route::controller(SchoolSettingController::class)->group(function() {
        Route::get('/school-settings', 'index')->name('school.settings'); // Halaman Form
        Route::post('/school-settings', 'update')->name('school.update'); // Proses Simpan
    });

    // 7. LEGACY SPP (Sistem Lama - Opsional)
    Route::prefix('spp')->name('spp.')->group(function () {
        Route::get('/', [SppController::class, 'index'])->name('index');
        Route::get('/generate', [SppController::class, 'createGenerate'])->name('create_generate');
        Route::post('/generate', [SppController::class, 'storeGenerate'])->name('store_generate');
    });

    // 8. PROFILE USER
    Route::controller(ProfileController::class)->group(function () {
        Route::get('/profile', 'edit')->name('profile.edit');
        Route::patch('/profile', 'update')->name('profile.update');
        Route::delete('/profile', 'destroy')->name('profile.destroy');
    });
});