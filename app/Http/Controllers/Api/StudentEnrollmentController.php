<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Services\FinanceAuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * StudentEnrollmentController — TU
 *
 * Menerima official enrollment event dari Kaffah Backend.
 * Set student_profiles.enrolled_at.
 *
 * AUTH: X-TU-SYNC-KEY header (sama dengan sync-student).
 * IDENTITY: NIS.
 *
 * TIDAK membuat student baru — provisioning via /api/internal/sync-student
 * harus sudah terjadi lebih dulu.
 *
 * TIDAK generate tagihan — hanya set enrolled_at.
 */
class StudentEnrollmentController extends Controller
{
    /**
     * POST /api/internal/student-enrollment
     *
     * Payload:
     *   nis         string   required  NIS siswa
     *   status      string   required  harus "enrolled"
     *   enrolled_at string   required  ISO 8601 datetime, tidak boleh masa depan
     *
     * Response:
     *   200 — enrolled_at di-set
     *   208 — idempotent (enrolled_at sama)
     *   400/422 — validation error
     *   401 — invalid key
     *   404 — NIS tidak ditemukan di TU
     *   409 — conflict (enrolled_at berbeda dari yang sudah ada)
     */
    public function receiveFromKaffah(Request $request)
    {
        // ============================================================
        // 1. AUTH SERVICE-TO-SERVICE
        // ============================================================
        $expectedKey = config('services.tu_sync.secret');

        if (empty($expectedKey) || $request->header('X-TU-SYNC-KEY') !== $expectedKey) {
            Log::warning('StudentEnrollmentController: request ditolak — X-TU-SYNC-KEY tidak valid', [
                'ip' => $request->ip(),
            ]);
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        // ============================================================
        // 2. VALIDASI PAYLOAD
        // ============================================================
        $validated = $request->validate([
            'nis'         => 'required|string|max:30',
            'status'      => 'required|string|in:enrolled',
            'enrolled_at' => 'required|date|before_or_equal:now',
        ], [
            'status.in'                   => 'status harus "enrolled".',
            'enrolled_at.before_or_equal' => 'enrolled_at tidak boleh di masa depan.',
        ]);

        $nis        = trim($validated['nis']);
        $enrolledAt = $validated['enrolled_at'];

        // ============================================================
        // 3. CARI STUDENT BY NIS
        //    Identity: NIS. Bukan NISN, email, atau nama.
        //    TIDAK membuat student baru dari enrollment event.
        // ============================================================
        $student = Student::where('nis', $nis)->first();

        if (!$student) {
            Log::warning('StudentEnrollmentController: NIS tidak ditemukan di TU', [
                'nis' => $nis,
            ]);
            return response()->json([
                'success' => false,
                'message' => "Student dengan NIS {$nis} tidak ditemukan di TU. Pastikan provisioning sudah selesai.",
            ], 404);
        }

        // ============================================================
        // 4. IDEMPOTENCY
        // ============================================================
        if ($student->enrolled_at !== null) {
            $existingDate = $student->enrolled_at->toDateString();
            $requestDate  = \Carbon\Carbon::parse($enrolledAt)->toDateString();

            if ($existingDate === $requestDate) {
                // Same date — idempotent
                Log::info('StudentEnrollmentController: idempotent — enrolled_at sama', [
                    'nis'         => $nis,
                    'enrolled_at' => $existingDate,
                ]);
                return response()->json([
                    'success'     => true,
                    'message'     => 'enrolled_at sudah sama — no-op.',
                    'idempotent'  => true,
                    'enrolled_at' => $existingDate,
                ], 208);
            }

            // Different date — conflict
            Log::warning('StudentEnrollmentController: conflict enrolled_at', [
                'nis'      => $nis,
                'existing' => $existingDate,
                'received' => $requestDate,
            ]);
            return response()->json([
                'success' => false,
                'message' => "Conflict: student NIS {$nis} sudah memiliki enrolled_at ({$existingDate}). Gunakan admin manual correction jika diperlukan.",
            ], 409);
        }

        // ============================================================
        // 5. SET enrolled_at
        // ============================================================
        $oldData = ['enrolled_at' => null];

        $student->update(['enrolled_at' => $enrolledAt]);

        // Audit log
        FinanceAuditService::logUpdate($student, $oldData, $request);

        Log::info('StudentEnrollmentController: enrolled_at di-set dari Kaffah', [
            'nis'         => $nis,
            'student_id'  => $student->id,
            'enrolled_at' => $enrolledAt,
        ]);

        return response()->json([
            'success'     => true,
            'message'     => 'enrolled_at berhasil di-set.',
            'nis'         => $nis,
            'enrolled_at' => $enrolledAt,
        ]);
    }
}
