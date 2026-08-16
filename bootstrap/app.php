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

        // CSRF exceptions — Midtrans webhook + portal siswa callback
        $middleware->validateCsrfTokens(except: [
            'midtrans-callback',
            'api/midtrans-callback',
            'midtrans/webhook',
            'siswa/payment/callback',  // Midtrans server-to-server callback
        ]);

        // Middleware alias
        $middleware->alias([
            'auth.siswa' => \App\Http\Middleware\AuthSiswa::class,
            'role'       => \App\Http\Middleware\EnsureRole::class,
        ]);

    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();