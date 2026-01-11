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

    // 1. DASHBOARD UTAMA (Executive Dashboard)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // 2. MODUL MANAJEMEN SISWA (Pusat Data Tagihan & Riwayat)
    Route::prefix('students')->name('students.')->group(function () {
        // List semua siswa (Filter Kelas & Search)
        Route::get('/', [StudentController::class, 'index'])->name('index');

        // Detail keuangan 1 siswa (Kartu SPP + History Jajan)
        Route::get('/{id}/finance', [StudentController::class, 'show'])->name('show');
    });

    // 3. MODUL TAGIHAN / BILLING (SPP, Gedung, Seragam, dll)
    Route::prefix('bills')->name('bills.')->group(function () {
        // Monitoring & Laporan
        Route::get('/', [BillController::class, 'index'])->name('index'); 
        
        // [PENTING] Route Export ditaruh SEBELUM route {id} biar gak bentrok
        Route::get('/export', [BillController::class, 'export'])->name('export'); 

        // Generate Tagihan Massal
        Route::get('/generate', [BillController::class, 'create'])->name('create');
        Route::post('/generate', [BillController::class, 'store'])->name('store');
        
        // Aksi Tagihan (Bayar & Cetak)
        Route::post('/{id}/pay', [BillController::class, 'pay'])->name('pay');
        Route::get('/{id}/print', [BillController::class, 'print'])->name('print');
    });

    // 4. MODUL POS (Kantin & Koperasi)
    Route::prefix('pos')->name('pos.')->group(function () {
        // Master Barang (CRUD Stok)
        Route::resource('items', PosItemController::class);

        // Transaksi Kasir
        Route::get('/transaction', [PosTransactionController::class, 'index'])->name('transaction');
        Route::post('/transaction', [PosTransactionController::class, 'store'])->name('transaction.store');
        
        // Cetak Struk (Thermal)
        Route::get('/transaction/{id}/print', [PosTransactionController::class, 'printStruk'])->name('transaction.print');

        // Laporan / Riwayat Penjualan
        Route::get('/history', [PosReportController::class, 'index'])->name('history.index');
        // Route::get('/history/{id}', [PosReportController::class, 'show'])->name('history.show'); // Opsional jika butuh detail
        
        // Pelunasan Hutang Kantin
        Route::post('/history/{id}/repay', [PosReportController::class, 'repay'])->name('history.repay');
    });

    // 5. PENGATURAN & INTEGRASI
    Route::prefix('settings')->name('settings.')->group(function () {
        // Halaman Konfigurasi Database PPDB
        Route::get('/integration', [IntegrationController::class, 'index'])->name('integration');
        
        // Simpan Konfigurasi
        Route::post('/integration', [IntegrationController::class, 'update'])->name('integration.update');
        
        // Eksekusi Tarik Data
        Route::post('/integration/sync', [IntegrationController::class, 'sync'])->name('integration.sync');
    });

    // 6. LEGACY SPP (Opsional - Jika masih ada menu lama yang mengarah ke sini)
    Route::prefix('spp')->name('spp.')->group(function () {
        Route::get('/', [SppController::class, 'index'])->name('index');
        Route::get('/generate', [SppController::class, 'createGenerate'])->name('create_generate');
        Route::post('/generate', [SppController::class, 'storeGenerate'])->name('store_generate');
    });

    // 7. PROFILE USER (Bawaan Laravel)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});