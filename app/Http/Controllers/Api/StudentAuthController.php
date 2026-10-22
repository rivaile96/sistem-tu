<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

/**
 * StudentAuthController
 *
 * Login endpoint untuk siswa mobile di TU Backend.
 *
 * Arsitektur (Task 11):
 *   Mobile → POST /api/student/login (TU :8001)
 *   → TU Sanctum token
 *   → /api/finance/* (TU :8001)
 *
 * Tidak bergantung pada Kaffah token. TU adalah auth authority
 * untuk semua finance API mobile.
 *
 * Security:
 *   - Identifier = NIS (username di TU users table)
 *   - Password verified via Hash::check
 *   - Tidak membocorkan apakah NIS atau password yang salah (generic error)
 *   - Rate limit: 10 percobaan per menit (throttle:10,1 di route)
 *   - Token sebelumnya dihapus saat login ulang (single session per student)
 *   - Password tidak pernah dikembalikan di response
 */
class StudentAuthController extends Controller
{
    /**
     * Login student mobile — return TU Sanctum token.
     *
     * POST /api/student/login
     *
     * Request:
     *   identifier  string  required  NIS siswa
     *   password    string  required  Password siswa
     *
     * Response success:
     *   {
     *     "status": "success",
     *     "token": "...",
     *     "user": {
     *       "id": 1,
     *       "name": "Ahmad Fauzan",
     *       "nis": "26080001",
     *       "nisn": "0123456789",
     *       "class_name": "7A",
     *       "role": "student"
     *     }
     *   }
     */
    public function login(Request $request)
    {
        // ============================================================
        // 1. VALIDASI INPUT
        // ============================================================
        $request->validate([
            'identifier' => 'required|string|max:50',
            'password'   => 'required|string|max:255',
        ]);

        $identifier = trim($request->identifier);

        // ============================================================
        // 2. CARI USER BERDASARKAN NIS (username)
        //
        // NIS disimpan di users.username sejak Task 7 (StudentSyncController).
        // Kita cari by username saja — tidak fallback ke email
        // agar tidak ada ambiguity lintas role.
        // ============================================================
        $user = User::where('username', $identifier)
                    ->where('role', 'student')
                    ->first();

        // ============================================================
        // 3. VERIFIKASI PASSWORD
        //
        // Generic error message — tidak membocorkan apakah NIS
        // atau password yang salah (SEC-H01: enumeration prevention).
        // ============================================================
        if (!$user || !Hash::check($request->password, $user->password)) {
            Log::warning('StudentAuthController: login gagal', [
                'identifier' => $identifier,
                'ip'         => $request->ip(),
                'reason'     => $user ? 'wrong_password' : 'user_not_found',
            ]);

            return response()->json([
                'status'  => 'error',
                'message' => 'NIS atau password salah.',
            ], 401);
        }

        // ============================================================
        // 4. CEK STATUS AKTIF
        //
        // Cek via student_profiles.status — student yang sudah
        // graduated/inactive tidak boleh login.
        // ============================================================
        $profile = Student::where('user_id', $user->id)->first();

        if (!$profile || !in_array($profile->status, ['active'])) {
            Log::warning('StudentAuthController: login ditolak — student tidak aktif', [
                'user_id' => $user->id,
                'status'  => $profile?->status ?? 'no_profile',
                'ip'      => $request->ip(),
            ]);

            return response()->json([
                'status'  => 'error',
                'message' => 'Akun tidak aktif. Hubungi TU.',
            ], 403);
        }

        // ============================================================
        // 5. HAPUS TOKEN LAMA + BUAT TOKEN BARU
        //
        // Single session: token lama dihapus agar tidak ada
        // multiple active session yang bisa disalahgunakan.
        // ============================================================
        $user->tokens()->delete();
        $token = $user->createToken('mobile-student')->plainTextToken;

        Log::info('StudentAuthController: login berhasil', [
            'user_id'    => $user->id,
            'nis'        => $user->username,
            'profile_id' => $profile->id,
            'ip'         => $request->ip(),
        ]);

        return response()->json([
            'status' => 'success',
            'token'  => $token,
            'user'   => [
                'id'         => $user->id,
                'name'       => $user->name,
                'nis'        => $user->username,   // NIS — cross-system identity key
                'nisn'       => $profile->nisn,
                'class_name' => $profile->class_name,
                'role'       => $user->role,
            ],
        ]);
    }

    /**
     * Logout — revoke token saat ini.
     *
     * POST /api/student/logout
     * Header: Authorization: Bearer {token}
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'Logout berhasil.',
        ]);
    }
}
