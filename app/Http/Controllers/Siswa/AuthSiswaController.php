<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthSiswaController extends Controller
{
    public function showLogin()
    {
        if (Auth::guard('siswa')->check()) {
            return redirect()->route('siswa.dashboard');
        }
        return view('siswa.auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'nis'      => 'required|string',
            'password' => 'required|string',
        ]);

        // Cari siswa berdasarkan NIS, hanya yang aktif
        $student = Student::where('nis', $request->nis)
                          ->where('status', 'active')
                          ->first();

        if (!$student) {
            return back()
                ->withErrors(['nis' => 'NIS tidak ditemukan atau akun tidak aktif.'])
                ->withInput();
        }

        // Verifikasi password: tanggal lahir format ddmmyy (6 digit)
        $expected = Carbon::parse($student->birth_date)->format('dmy');
        if ($expected !== $request->password) {
            return back()
                ->withErrors(['password' => 'Password salah. Gunakan tanggal lahir format ddmmyy (contoh: lahir 12 Juli 2006 → 120706).'])
                ->withInput();
        }

        // Login manual via guard siswa (tanpa attempt() karena password tidak di-hash)
        Auth::guard('siswa')->login($student, $request->boolean('remember'));
        $request->session()->regenerate();

        return redirect()->route('siswa.dashboard');
    }

    public function logout(Request $request)
    {
        Auth::guard('siswa')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('siswa.login');
    }
}
