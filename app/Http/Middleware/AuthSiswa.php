<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthSiswa
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::guard('siswa')->check()) {
            return redirect()->route('siswa.login');
        }

        // 5.4 — Portal hardening: only active students may use the portal
        $student = Auth::guard('siswa')->user();
        if ($student->status !== 'active') {
            Auth::guard('siswa')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('siswa.login')
                ->withErrors(['nis' => 'Akun siswa belum aktif. Hubungi pihak sekolah untuk informasi lebih lanjut.']);
        }

        return $next($request);
    }
}
