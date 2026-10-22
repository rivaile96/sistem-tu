<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ParentApiController;
use App\Http\Controllers\Api\FinanceTokenController;
use App\Http\Controllers\MidtransController;

// ============================================================
// 1. PUBLIC — Midtrans Webhook
// ============================================================
Route::post('/midtrans/webhook',   [MidtransController::class, 'callback']);
Route::post('/midtrans-callback',  [MidtransController::class, 'callback']); // legacy alias

// ============================================================
// 2. FINANCE ROUTES
//    auth:sanctum  — harus punya TU Sanctum token
//    role:student  — hanya siswa
// ============================================================
Route::middleware(['auth:sanctum', 'role:student'])
    ->prefix('finance')
    ->group(function () {
        Route::get('/dashboard',                 [ParentApiController::class, 'getHomeData']);
        Route::post('/payment/create',           [ParentApiController::class, 'createPayment']);
        Route::get('/transactions',              [ParentApiController::class, 'getTransactionHistory']);
        Route::get('/payment/status/{order_id}', [ParentApiController::class, 'checkStatus']);
        Route::get('/payment/invoice/{order_id}',[ParentApiController::class, 'getInvoiceDetail']);
        Route::post('/pin/set',                  [ParentApiController::class, 'setPin']);
        Route::post('/pin/verify',               [ParentApiController::class, 'verifyPin'])
             ->middleware('throttle:5,1');
        Route::get('/banners',                   [ParentApiController::class, 'getBanners']);
        Route::get('/payment-channels',          [ParentApiController::class, 'getPaymentChannels']);
        Route::get('/bills',                     [ParentApiController::class, 'getBills']);
    });

// ============================================================
// 3. STUDENT AUTH (LEGACY — backward compatibility)
//    Endpoint ini dipertahankan agar mobile versi lama tetap berfungsi.
//    JANGAN buat flow baru yang bergantung pada endpoint ini.
// ============================================================
Route::post('/student/login', [\App\Http\Controllers\Api\StudentAuthController::class, 'login'])
    ->middleware('throttle:10,1');
Route::post('/student/logout', [\App\Http\Controllers\Api\StudentAuthController::class, 'logout'])
    ->middleware('auth:sanctum');

// ============================================================
// 4. FINANCE TOKEN EXCHANGE (NEW ARCHITECTURE)
//    Mobile yang sudah login ke Kaffah menukar signed assertion
//    menjadi TU finance-scoped token.
// ============================================================
Route::post('/internal/exchange-finance-token', [FinanceTokenController::class, 'exchange'])
    ->middleware('throttle:10,1');

// ============================================================
// 5. INTERNAL — Student Sync dari Kaffah Backend
//    Autentikasi: X-TU-SYNC-KEY header (service-to-service)
// ============================================================
Route::post('/internal/sync-student', [\App\Http\Controllers\Api\StudentSyncController::class, 'receiveFromKaffah'])
    ->middleware('throttle:30,1');

// ============================================================
// 6. INTERNAL — Official Enrollment Event dari Kaffah Backend
//    Autentikasi: X-TU-SYNC-KEY header (service-to-service)
//    Set student_profiles.enrolled_at — TIDAK membuat siswa baru.
// ============================================================
Route::post('/internal/student-enrollment', [\App\Http\Controllers\Api\StudentEnrollmentController::class, 'receiveFromKaffah'])
    ->middleware('throttle:30,1');
