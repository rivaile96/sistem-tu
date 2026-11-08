<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

/**
 * StudentSyncController
 *
 * Menerima push notification dari Kaffah Backend setelah student berhasil
 * dibuat/diupdate di Kaffah (contoh: setelah SPMB payment settlement).
 *
 * Security:
 *   - Endpoint ini hanya boleh dipanggil oleh Kaffah Backend.
 *   - Autentikasi: X-TU-SYNC-KEY header dicocokkan dengan config('services.tu_sync.secret').
 *   - Tidak ada autentikasi user/session — ini adalah service-to-service call.
 *
 * Idempotency:
 *   - NIS adalah cross-system identity key.
 *   - Jika NIS sudah ada di TU: update name, class_name, synced_at saja.
 *   - Jika NIS belum ada: create users + student_profiles.
 *   - Tidak pernah mengasumsikan Kaffah users.id == TU users.id.
 *
 * SEC-C04 compliance:
 *   - Tidak mengakses database SPMB secara langsung.
 *   - Tidak menggunakan NISN sebagai NIS.
 *   - NIS selalu berasal dari Kaffah Backend — bukan digenerate di TU.
 */
class StudentSyncController extends Controller
{
    /**
     * Terima push sync dari Kaffah Backend.
     *
     * POST /api/internal/sync-student
     * Header: X-TU-SYNC-KEY: {secret}
     *
     * Payload:
     *   nis         string  required  NIS siswa (cross-system key)
     *   nama        string  required  Nama lengkap
     *   nisn        string  optional  NISN siswa
     *   email       string  optional  Email siswa
     *   class_name  string  optional  Nama kelas
     *   no_hp       string  optional  Nomor HP siswa
     */
    public function receiveFromKaffah(Request $request)
    {
        // ============================================================
        // 1. AUTENTIKASI SERVICE-TO-SERVICE
        //    Tolak request tanpa X-TU-SYNC-KEY yang benar.
        //    Fail-closed: jika config kosong, tolak semua request.
        //    SEC-05: Gunakan hash_equals() (constant-time comparison).
        // ============================================================
        $expectedKey = config('services.tu_sync.secret');
        $providedKey = $request->header('X-TU-SYNC-KEY');

        if (
            !is_string($expectedKey)
            || trim($expectedKey) === ''
            || !is_string($providedKey)
            || $providedKey === ''
            || !hash_equals($expectedKey, $providedKey)
        ) {
            Log::warning('StudentSyncController: request ditolak — X-TU-SYNC-KEY tidak valid', [
                'ip' => $request->ip(),
            ]);
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        // ============================================================
        // 2. VALIDASI PAYLOAD
        // ============================================================
        $validated = $request->validate([
            'nis'        => 'required|string|max:20',
            'nama'       => 'required|string|max:255',
            'nisn'       => 'nullable|string|max:20',
            'email'      => 'nullable|email|max:255',
            'class_name' => 'nullable|string|max:50',
            'no_hp'      => 'nullable|string|max:20',
        ]);

        $nis = trim($validated['nis']);

        // ============================================================
        // 3. IDEMPOTENT UPSERT
        //
        //    Gunakan DB transaction + lockForUpdate agar concurrent
        //    request tidak membuat duplikat.
        //
        //    Flow:
        //      a. Cek apakah student_profiles dengan NIS ini sudah ada.
        //      b. Jika sudah ada: update name, class_name, synced_at.
        //         Tidak overwrite data lokal TU (billing, dll).
        //      c. Jika belum ada: create users + student_profiles.
        //         Password sementara = NIS (siswa diminta ganti saat login pertama).
        //
        //    NIS adalah satu-satunya cross-system key yang reliable.
        //    Tidak pernah mengasumsikan Kaffah users.id == TU users.id.
        // ============================================================
        DB::beginTransaction();
        try {
            $existingProfile = Student::where('nis', $nis)->lockForUpdate()->first();

            if ($existingProfile) {
                // Siswa sudah ada di TU — update field yang authoritative dari Kaffah.
                // Jangan overwrite: status, billing data, atau data lokal TU lainnya.
                $updateData = ['synced_at' => now()];

                if (!empty($validated['class_name'])) {
                    $updateData['class_name'] = $validated['class_name'];
                }
                if (!empty($validated['nisn'])) {
                    $updateData['nisn'] = $validated['nisn'];
                }

                $existingProfile->update($updateData);

                // Update nama di users jika berbeda
                if ($existingProfile->user) {
                    $existingProfile->user->update(['name' => $validated['nama']]);
                }

                DB::commit();

                Log::info('StudentSyncController: student diupdate dari Kaffah', [
                    'nis'            => $nis,
                    'tu_profile_id'  => $existingProfile->id,
                    'tu_user_id'     => $existingProfile->user_id,
                ]);

                return response()->json([
                    'success'       => true,
                    'action'        => 'updated',
                    'tu_profile_id' => $existingProfile->id,
                    'tu_user_id'    => $existingProfile->user_id,
                    'nis'           => $nis,
                ]);
            }

            // Siswa belum ada di TU — buat baru.
            // Email: gunakan yang dikirim Kaffah, fallback ke nis@sekolah.id
            $email = $validated['email'] ?? ($nis . '@sekolah.id');

            // Jika email sudah dipakai user lain (edge case), buat email unik
            if (User::where('email', $email)->exists()) {
                $email = $nis . '.' . uniqid() . '@sekolah.id';
            }

            $user = User::create([
                'name'     => $validated['nama'],
                'username' => $nis,   // NIS = username login mobile (konsisten cross-system)
                'email'    => $email,
                'password' => Hash::make($nis), // Password sementara = NIS
                'role'     => 'student',
                'phone'    => $validated['no_hp'] ?? null,
            ]);

            $profile = Student::create([
                'user_id'    => $user->id,
                'nis'        => $nis,
                'nisn'       => $validated['nisn'] ?? null,
                'class_name' => $validated['class_name'] ?? null,
                'status'     => 'active',
                'synced_at'  => now(),
            ]);

            DB::commit();

            Log::info('StudentSyncController: student baru dibuat dari Kaffah', [
                'nis'            => $nis,
                'tu_profile_id'  => $profile->id,
                'tu_user_id'     => $user->id,
            ]);

            return response()->json([
                'success'       => true,
                'action'        => 'created',
                'tu_profile_id' => $profile->id,
                'tu_user_id'    => $user->id,
                'nis'           => $nis,
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('StudentSyncController: sync gagal', [
                'nis'     => $nis ?? null,
                'message' => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Sync gagal. Silakan retry.',
            ], 500);
        }
    }
}
