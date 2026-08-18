<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * EnsureRole — gate-keeps routes to one or more allowed roles.
 *
 * Usage in routes/web.php:
 *   ->middleware('role:admin,tu')
 *   ->middleware('role:admin')
 *
 * Returns HTTP 403 for authenticated users who lack the required role.
 * Unauthenticated users are handled upstream by the 'auth' middleware.
 *
 * Role enum (users.role): admin | tu | staf | kepala_sekolah
 */
class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = Auth::user();

        // auth middleware runs first — $user should never be null here,
        // but guard defensively to avoid a type error.
        if (! $user || ! in_array($user->role, $roles, true)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Akses ditolak.'], 403);
            }
            abort(403, 'Akses ditolak. Anda tidak memiliki izin untuk melakukan tindakan ini.');
        }

        return $next($request);
    }
}
