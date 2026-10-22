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
    ->withProviders([
        App\Providers\EventServiceProvider::class,
    ])
    ->withMiddleware(function (Middleware $middleware) {

        // Alias middleware untuk dipakai di routes
        $middleware->alias([
            'role'    => \App\Http\Middleware\CheckRole::class,
            'ability' => \Laravel\Sanctum\Http\Middleware\CheckAbilities::class,
        ]);

        // Konfigurasi Pengecualian CSRF (Untuk Webhook Midtrans nanti)
        $middleware->validateCsrfTokens(except: [
            'midtrans-callback',
            'api/midtrans-callback',
            'midtrans/webhook',
        ]);

    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();