<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // HAPUS kodingan $middleware->append(...) yang kamu tulis disini.
        // Biarkan kosong dulu, atau gunakan format yang benar.
        
        // Nanti untuk Webhook Midtrans (Langkah berikutnya), kita akan isi disini:
        $middleware->validateCsrfTokens(except: [
            'midtrans/webhook', // Ini persiapan buat nanti
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();