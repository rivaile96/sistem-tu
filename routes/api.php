<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ParentApiController; // <--- Pastikan ini ada!

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// 1. PUBLIC ROUTES (Login & Webhook)
// Ini jalur yang lu tembak di Postman
Route::post('/login', [ParentApiController::class, 'login'])->middleware('throttle:5,1');
Route::post('/midtrans-callback', [ParentApiController::class, 'callback']);

// 2. PROTECTED ROUTES (Harus Punya Token)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/home', [ParentApiController::class, 'getHomeData']);
    Route::post('/payment/create', [ParentApiController::class, 'createPayment']);
});