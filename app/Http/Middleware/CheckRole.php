<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     * Usage di route: ->middleware('role:admin') atau ->middleware('role:admin,kasir')
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['status' => 'error', 'message' => 'Akses ditolak.'], 403);
            }
            abort(403, 'Akses ditolak. Anda tidak memiliki izin untuk halaman ini.');
        }

        if (isset($user->is_active) && $user->is_active === false) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['status' => 'error', 'message' => 'Akun tidak aktif.'], 403);
            }
            abort(403, 'Akun tidak aktif.');
        }

        $effectiveRole = $user instanceof \App\Models\Student ? 'student' : (string) $user->role;

        if (! in_array($effectiveRole, $roles, true)) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['status' => 'error', 'message' => 'Akses ditolak.'], 403);
            }
            abort(403, 'Akses ditolak. Anda tidak memiliki izin untuk halaman ini.');
        }

        return $next($request);
    }
}
