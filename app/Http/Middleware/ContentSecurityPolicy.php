<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ContentSecurityPolicy
{
    /**
     * Middleware CSP DIMATIKAN TOTAL
     * Biar dashboard, chart, JS, dan Midtrans aman
     */
    public function handle(Request $request, Closure $next): Response
    {
        return $next($request);
    }
}
