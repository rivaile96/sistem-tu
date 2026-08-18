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
// SppController removed in Phase 6B-4 — spp_bills table dropped
use App\Http\Controllers\SchoolSettingController;  // Pengaturan Sekolah
use App\Http\Controllers\NaikKelasController;       // Naik Kelas Massal
use App\Http\Controllers\RombelController;           // Rombel & Tahun Ajaran
use App\Http\Controllers\KelasController;           // Master Kelas
use App\Http\Controllers\PPDBController;            // PPDB Flow
use App\Http\Controllers\Siswa\AuthSiswaController;
use App\Http\Controllers\Siswa\DashboardSiswaController;
use App\Http\Controllers\Siswa\PaymentSiswaController;

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

// =========================================================================
//  PORTAL SISWA (Guard: siswa — terpisah dari admin)
// =========================================================================
Route::prefix('siswa')->name('siswa.')->group(function () {

    // Auth (tidak butuh login)
    Route::get('/login',  [AuthSiswaController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthSiswaController::class, 'login'])->middleware('throttle:5,1')->name('login.post');
    Route::post('/logout',[AuthSiswaController::class, 'logout'])->name('logout');

    // Midtrans webhook — tidak butuh auth, Midtrans yang hit ini
    Route::post('/payment/callback', [PaymentSiswaController::class, 'callback'])->name('payment.callback');

    // Protected — wajib login siswa
    Route::middleware('auth.siswa')->group(function () {
        Route::get('/dashboard',            [DashboardSiswaController::class, 'index'])->name('dashboard');
        Route::get('/tagihan/{bill}',        [DashboardSiswaController::class, 'detail'])->name('bill.detail');
        Route::post('/tagihan/{bill}/pay',   [PaymentSiswaController::class, 'createToken'])->name('payment.token');
        Route::get('/tagihan/{bill}/struk',  [PaymentSiswaController::class, 'struk'])->name('tagihan.struk');
        Route::get('/payment/success',       [PaymentSiswaController::class, 'success'])->name('payment.success');
    });
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
    // Read: all staff + kepala_sekolah. Write: admin,tu,staf. Delete: admin,tu only.
    Route::prefix('students')->name('students.')->group(function () {
        Route::get('/', [StudentController::class, 'index'])->name('index');

        // Static routes MUST come before /{id} wildcard to avoid shadowing
        // Write operations — admin, tu, staf
        Route::middleware('role:admin,tu,staf')->group(function () {
            Route::get('/create', [StudentController::class, 'create'])->name('create');
            Route::post('/', [StudentController::class, 'store'])->name('store');
            Route::get('/import', [StudentController::class, 'importForm'])->name('import');
            Route::post('/import', [StudentController::class, 'importCsv'])->name('import.process');
            Route::get('/template', [StudentController::class, 'downloadTemplate'])->name('template');
            Route::get('/{id}/edit', [StudentController::class, 'edit'])->name('edit');
            Route::put('/{id}', [StudentController::class, 'update'])->name('update');
            Route::post('/{id}/status', [StudentController::class, 'ubahStatus'])->name('ubah-status.process');
        });

        // Delete — admin, tu only (staf cannot delete students)
        Route::middleware('role:admin,tu')->group(function () {
            Route::delete('/{id}', [StudentController::class, 'destroy'])->name('destroy');
        });

        // Wildcard routes last — after all static routes
        Route::get('/{id}', [StudentController::class, 'show'])->name('show');
        Route::get('/{id}/finance', [StudentController::class, 'show'])->name('finance');
        Route::get('/{id}/status', [StudentController::class, 'formUbahStatus'])->name('ubah-status');
    });

    // NAIK KELAS MASSAL — admin + tu only
    Route::prefix('naik-kelas')->name('naik-kelas.')->middleware('role:admin,tu')->group(function () {
        Route::get('/', [NaikKelasController::class, 'index'])->name('index');
        Route::post('/preview', [NaikKelasController::class, 'preview'])->name('preview');
        Route::post('/eksekusi', [NaikKelasController::class, 'eksekusi'])->name('eksekusi');
    });

    // MASTER KELAS — admin + tu only
    Route::middleware('role:admin,tu')->group(function () {
        Route::post('kelas/update-jenjang', [KelasController::class, 'updateJenjang'])->name('kelas.update-jenjang');
        Route::resource('kelas', KelasController::class)->except(['show']);
    });

    // ── Tahun Ajaran — admin + tu only ───────────────────────────────────────
    Route::prefix('tahun-ajaran')->name('tahun-ajaran.')->middleware('role:admin,tu')->group(function () {
        Route::get('/',                [RombelController::class, 'tahunAjaranIndex'])->name('index');
        Route::get('/create',          [RombelController::class, 'tahunAjaranCreate'])->name('create');
        Route::post('/',               [RombelController::class, 'tahunAjaranStore'])->name('store');
        Route::get('/{tahunAjaran}/edit',   [RombelController::class, 'tahunAjaranEdit'])->name('edit');
        Route::put('/{tahunAjaran}',        [RombelController::class, 'tahunAjaranUpdate'])->name('update');
        Route::delete('/{tahunAjaran}',     [RombelController::class, 'tahunAjaranDestroy'])->name('destroy');
    });

    // ── Rombel — admin + tu only ──────────────────────────────────────────────
    Route::middleware('role:admin,tu')->group(function () {
        Route::resource('rombel', RombelController::class);
        Route::post('rombel/{rombel}/assign-siswa',         [RombelController::class, 'assignSiswa'])->name('rombel.assign-siswa');
        Route::delete('rombel/{rombel}/remove-siswa/{student}', [RombelController::class, 'removeSiswa'])->name('rombel.remove-siswa');
    });

    // 3. BILLING / TAGIHAN
    // Read/print/export: admin,tu,staf,kepala_sekolah.
    // Create/pay/delete: admin,tu only.
    Route::controller(BillController::class)->prefix('bills')->name('bills.')->group(function () {
        // Monitoring — all authenticated staff including kepala_sekolah
        Route::middleware('role:admin,tu,staf,kepala_sekolah')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/export', 'export')->name('export');
            Route::get('/{id}/print', 'print')->name('print');
        });

        // Write operations — admin,tu only
        Route::middleware('role:admin,tu')->group(function () {
            Route::get('/create', 'create')->name('create');
            Route::post('/', 'store')->name('store');
            Route::post('/{id}/pay', 'pay')->name('pay');
            Route::delete('/{id}', 'destroy')->name('destroy');
        });
    });

    // 4. POS (KANTIN & KOPERASI)
    // Transaksi kasir: admin,tu,staf. Master data & bundling: admin,tu. History/laporan: admin,tu,kepala_sekolah.
    Route::prefix('pos')->name('pos.')->group(function () {
        // A. Master Barang — admin,tu only
        Route::middleware('role:admin,tu')->group(function () {
            Route::resource('items', PosItemController::class);
        });

        // B. Master Paket / Bundling — admin,tu only
        Route::middleware('role:admin,tu')->group(function () {
            Route::resource('bundles', PosBundleController::class);
            Route::get('bundles/{bundle}/generate-bills', [PosBundleController::class, 'generateBillsForm'])->name('bundles.generateBillsForm');
            Route::post('bundles/{bundle}/generate-bills', [PosBundleController::class, 'generateBills'])->name('bundles.generateBills');
        });

        // C. Kasir / Transaksi — admin,tu,staf
        Route::middleware('role:admin,tu,staf')->controller(PosTransactionController::class)->group(function () {
            Route::get('/transaction', 'index')->name('transaction');
            Route::post('/transaction', 'store')->name('transaction.store');
            Route::get('/transaction/{id}/print', 'printStruk')->name('transaction.print');
        });

        // D. Laporan & Riwayat — admin,tu,kepala_sekolah
        Route::middleware('role:admin,tu,kepala_sekolah')->controller(PosReportController::class)->prefix('history')->name('history.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::post('/{id}/repay', 'repay')->name('repay');
        });
    });

    // 5. PENGATURAN SYSTEM (INTEGRASI) — admin only
    Route::prefix('settings')->name('settings.')->middleware('role:admin')->group(function () {
        Route::get('/integration', [IntegrationController::class, 'index'])->name('integration');
        Route::post('/integration', [IntegrationController::class, 'update'])->name('integration.update');
        Route::post('/integration/sync', [IntegrationController::class, 'sync'])->name('integration.sync');
    });

    // 6. IDENTITAS SEKOLAH (KOP SURAT & LOGO) — admin only
    Route::controller(SchoolSettingController::class)->middleware('role:admin')->group(function() {
        Route::get('/school-settings', 'index')->name('school.settings');
        Route::post('/school-settings', 'update')->name('school.update');
    });

    // 9. PPDB FLOW
    // Read: admin,tu,staf,kepala_sekolah. Write/delete: admin,tu only.
    Route::prefix('ppdb')->name('ppdb.')->group(function () {
        Route::middleware('role:admin,tu,staf,kepala_sekolah')->group(function () {
            Route::get('/', [PPDBController::class, 'index'])->name('index');
            Route::get('/{id}', [PPDBController::class, 'show'])->name('show');
        });

        // admin,tu only
        Route::middleware('role:admin,tu')->group(function () {
            Route::get('/daftar', [PPDBController::class, 'create'])->name('create');
            Route::post('/daftar', [PPDBController::class, 'store'])->name('store');
            Route::get('/konversi', [PPDBController::class, 'konversiIndex'])->name('konversi');
            Route::post('/konversi', [PPDBController::class, 'konversiEksekusi'])->name('konversi.eksekusi');
            Route::get('/{id}/edit', [PPDBController::class, 'edit'])->name('edit');
            Route::put('/{id}', [PPDBController::class, 'update'])->name('update');
            Route::post('/{id}/seleksi', [PPDBController::class, 'seleksi'])->name('seleksi');
            Route::delete('/{id}', [PPDBController::class, 'destroy'])->name('destroy');
        });
    });

    // 7. LEGACY SPP (Sistem Lama - Opsional)
    // Phase 6B-4: SPP legacy routes removed.
    // SppController and spp_bills table have been dropped.
    // Historical SPP data accessible via /bills?type=SPP

    // 8. PROFILE USER
    Route::controller(ProfileController::class)->group(function () {
        Route::get('/profile', 'edit')->name('profile.edit');
        Route::patch('/profile', 'update')->name('profile.update');
        Route::delete('/profile', 'destroy')->name('profile.destroy');
    });
});


