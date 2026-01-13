<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        
        // 👇 INI KUNCI UTAMANYA! (Jangan dihapus)
        api: __DIR__.'/../routes/api.php', 
        
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        
        // Konfigurasi Pengecualian CSRF (Untuk Webhook Midtrans nanti)
        // Karena Webhook dikirim oleh server Midtrans, kita harus izinkan lewat.
        $middleware->validateCsrfTokens(except: [
            'midtrans-callback',      // Jika route ada di web.php
            'api/midtrans-callback',  // Jika route ada di api.php
            'midtrans/webhook',       // (Cadangan sesuai request lu)
        ]);
        
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();