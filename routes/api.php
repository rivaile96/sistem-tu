<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MidtransController;

/*
|--------------------------------------------------------------------------
| API Routes (Jalur Eksternal)
|--------------------------------------------------------------------------
|
| Jalur ini tidak memiliki session/login (Stateless).
| Jalur ini WAJIB ada agar status pembayaran bisa update otomatis (LUNAS).
|
*/

// 1. Webhook Midtrans
// URL ini nanti dimasukkan ke Dashboard Midtrans: domain.com/api/midtrans/callback
Route::post('/midtrans/callback', [MidtransController::class, 'callback']);

// 2. User Info (Bawaan Laravel, biarkan saja)
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');