<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ContentSecurityPolicy
{
    /**
     * Content Security Policy — aktif dengan whitelist CDN yang dipakai sistem-tu
     *
     * Whitelist yang diizinkan:
     * - cdn.jsdelivr.net      → SweetAlert2
     * - cdnjs.cloudflare.com  → Alpine.js, Chart.js, dll
     * - fonts.googleapis.com  → Google Fonts CSS
     * - fonts.gstatic.com     → Google Fonts files
     * - app.midtrans.com      → Midtrans payment gateway
     * - 'self'                → asset lokal
     * - 'unsafe-inline'       → inline style/script (Blade, Alpine, Tailwind JIT)
     * - 'unsafe-eval'         → Alpine.js reactivity membutuhkan eval
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Hanya terapkan CSP pada response HTML
        $contentType = $response->headers->get('Content-Type', '');
        if (!str_contains($contentType, 'text/html')) {
            return $response;
        }

        $self      = "'self'";
        $unsafeInline = "'unsafe-inline'";
        $unsafeEval   = "'unsafe-eval'";

        $cdnSources = implode(' ', [
            'cdn.jsdelivr.net',
            'cdnjs.cloudflare.com',
            'unpkg.com',
        ]);

        $fontSources = implode(' ', [
            'fonts.googleapis.com',
            'fonts.gstatic.com',
        ]);

        $midtransSources = implode(' ', [
            'app.midtrans.com',
            'api.midtrans.com',
            'app.sandbox.midtrans.com',
            'api.sandbox.midtrans.com',
            'snap-assets.sandbox.midtrans.com',
            'snap-assets.midtrans.com',
        ]);

        $frameSources = $midtransSources;

        $imgSources = implode(' ', [
            $self,
            'data:',
            'blob:',
            'cdn.jsdelivr.net',
            'cdnjs.cloudflare.com',
        ]);

        $csp = implode('; ', [
            "default-src {$self}",
            "script-src {$self} {$unsafeInline} {$unsafeEval} {$cdnSources} app.midtrans.com app.sandbox.midtrans.com snap-assets.sandbox.midtrans.com snap-assets.midtrans.com",
            "style-src {$self} {$unsafeInline} {$cdnSources} {$fontSources}",
            "font-src {$self} data: {$fontSources} {$cdnSources}",
            "img-src {$imgSources}",
            "connect-src {$self} app.midtrans.com api.midtrans.com app.sandbox.midtrans.com api.sandbox.midtrans.com snap-assets.sandbox.midtrans.com",
            "frame-src {$frameSources}",
            "object-src 'none'",
            "base-uri {$self}",
            "form-action {$self}",
            "upgrade-insecure-requests",
        ]);

        $response->headers->set('Content-Security-Policy', $csp);
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        return $response;
    }
}
