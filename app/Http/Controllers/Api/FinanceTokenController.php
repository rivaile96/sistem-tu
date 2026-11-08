<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FinanceAssertionNonce;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Validation\Constraint\SignedWith;
use Lcobucci\JWT\Validation\Constraint\PermittedFor;
use Lcobucci\JWT\Validation\Constraint\StrictValidAt;
use Psr\Clock\ClockInterface;
use DateTimeImmutable;
use DateTimeZone;
use DateInterval;

/**
 * FinanceTokenController
 *
 * Menerima short-lived signed assertion dari Kaffah (via mobile)
 * dan menukarnya dengan TU finance-scoped Sanctum token.
 *
 * Security model:
 *   - Assertion ditandatangani Kaffah dengan RSA private key
 *   - TU memverifikasi dengan RSA public key (offline — tidak perlu call Kaffah)
 *   - jti single-use — mencegah replay attack
 *   - aud must == "tu-finance"
 *   - Finance token hanya punya ability ['finance']
 *   - Finance token tidak bisa dipakai di academic/identity endpoint
 */
class FinanceTokenController extends Controller
{
    /**
     * POST /api/internal/exchange-finance-token
     *
     * Request:
     *   { "assertion": "eyJ..." }
     *
     * Response success:
     *   {
     *     "success": true,
     *     "token": "...",
     *     "expires_at": "...",
     *     "user": { "nis": "...", "nisn": "...", "role": "student" }
     *   }
     */
    public function exchange(Request $request)
    {
        // ================================================================
        // 1. VALIDASI INPUT
        // ================================================================
        $request->validate([
            'assertion' => 'required|string',
        ]);

        $assertionString = $request->input('assertion');

        // ================================================================
        // 2. LOAD CONFIG
        // ================================================================
        $publicKeyPem     = config('finance_assertion.public_key');
        $expectedAud      = config('finance_assertion.expected_audience', 'tu-finance');
        $expectedAlg      = config('finance_assertion.expected_algorithm', 'RS256');
        $clockSkewSecs    = (int) config('finance_assertion.clock_skew_seconds', 30);
        $tokenExpiryHours = (int) config('finance_assertion.finance_token_expiry_hours', 24);
        $nonceTtlMinutes  = (int) config('finance_assertion.nonce_ttl_minutes', 10);

        if (empty($publicKeyPem)) {
            Log::error('FinanceTokenController: TU_KAFFAH_FINANCE_ASSERTION_PUBLIC_KEY tidak dikonfigurasi');
            return response()->json([
                'success' => false,
                'message' => 'Layanan pertukaran token tidak tersedia.',
            ], 503);
        }

        // ================================================================
        // 3. PARSE JWT — tolak token malformed sebelum verifikasi
        // ================================================================
        try {
            // TU tidak punya private key.
            // forAsymmetricSigner butuh 3 parameter: signer, signingKey, verificationKey.
            // Untuk verifikasi saja, berikan publicKey di kedua posisi key.
            // SignedWith constraint hanya menggunakan verificationKey() — aman.
            $jwtConfig = Configuration::forAsymmetricSigner(
                new Sha256(),
                InMemory::plainText($publicKeyPem), // signingKey — tidak dipakai TU
                InMemory::plainText($publicKeyPem)  // verificationKey — dipakai SignedWith
            );

            $token = $jwtConfig->parser()->parse($assertionString);

        } catch (\Throwable $e) {
            Log::warning('FinanceTokenController: assertion malformed', [
                'ip'    => $request->ip(),
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Assertion tidak valid.',
            ], 401);
        }

        // ================================================================
        // 4. VERIFIKASI ALGORITHM — cegah algorithm confusion attack
        // ================================================================
        $headerAlg = $token->headers()->get('alg');
        if ($headerAlg !== $expectedAlg) {
            Log::warning('FinanceTokenController: algorithm mismatch', [
                'expected' => $expectedAlg,
                'received' => $headerAlg,
                'ip'       => $request->ip(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Algorithm assertion tidak diizinkan.',
            ], 401);
        }

        // ================================================================
        // 5. VERIFIKASI SIGNATURE + AUDIENCE + EXPIRY (dengan clock skew)
        //
        // PSR-20 ClockInterface — inline implementation.
        // Tidak membutuhkan lcobucci/clock package terpisah.
        // ================================================================
        $tz    = new DateTimeZone('UTC');
        $clock = new class($tz) implements ClockInterface {
            public function __construct(private readonly DateTimeZone $tz) {}
            public function now(): DateTimeImmutable
            {
                return new DateTimeImmutable('now', $this->tz);
            }
        };

        // Gunakan explicit key instance — bukan $jwtConfig->verificationKey().
        // Debug menunjukkan SignedWith($cfg->verificationKey()) selalu NO,
        // sedangkan SignedWith(new Sha256(), InMemory::plainText($pub)) selalu YES.
        $verificationKey = InMemory::plainText($publicKeyPem);

        $constraints = [
            new SignedWith(new Sha256(), $verificationKey),
            new PermittedFor($expectedAud),
            new StrictValidAt($clock, new DateInterval("PT{$clockSkewSecs}S")),
        ];

        if (! $jwtConfig->validator()->validate($token, ...$constraints)) {
            Log::warning('FinanceTokenController: assertion gagal validasi', [
                'ip'  => $request->ip(),
                'jti' => $token->claims()->get('jti', 'MISSING'),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Assertion kedaluwarsa atau tidak valid.',
            ], 401);
        }

        // ================================================================
        // 6. EKSTRAK CLAIMS
        // ================================================================
        $claims = $token->claims();
        $jti    = $claims->get('jti');
        $sub    = $claims->get('sub');
        $nis    = $claims->get('nis');
        $role   = $claims->get('role');
        $exp    = $claims->get('exp'); // DateTimeImmutable

        // Validasi claims wajib ada
        if (empty($jti) || empty($sub) || empty($nis)) {
            Log::warning('FinanceTokenController: claims tidak lengkap', [
                'jti' => $jti ?? 'MISSING',
                'sub' => $sub ?? 'MISSING',
                'nis' => $nis ?? 'MISSING',
                'ip'  => $request->ip(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Assertion tidak lengkap.',
            ], 401);
        }

        // Verifikasi role — hanya siswa yang boleh exchange finance token
        if ($role !== 'siswa') {
            Log::warning('FinanceTokenController: role tidak diizinkan', [
                'role' => $role,
                'nis'  => $nis,
                'ip'   => $request->ip(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Role tidak diizinkan untuk finance exchange.',
            ], 403);
        }

        // ================================================================
        // 7. REPLAY PROTECTION — cek jti single-use (fast path)
        // ================================================================
        if (FinanceAssertionNonce::isUsed($jti)) {
            Log::warning('FinanceTokenController: duplicate jti — replay attempt', [
                'jti' => $jti,
                'nis' => $nis,
                'ip'  => $request->ip(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Assertion sudah pernah digunakan.',
            ], 409);
        }

        // ================================================================
        // 8. RESOLVE STUDENT DI TU
        // ================================================================
        $user = User::where('username', $nis)
                    ->where('role', 'student')
                    ->first();

        if (! $user) {
            Log::warning('FinanceTokenController: student tidak ditemukan di TU', [
                'nis' => $nis,
                'sub' => $sub,
                'ip'  => $request->ip(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Siswa tidak ditemukan. Hubungi administrator.',
            ], 404);
        }

        if (isset($user->is_active) && $user->is_active === false) {
            return response()->json([
                'success' => false,
                'message' => 'Akun siswa tidak aktif.',
            ], 403);
        }

        $studentProfile = \App\Models\Student::where('user_id', $user->id)->first();
        if ($studentProfile && ! in_array($studentProfile->status, ['active'], true)) {
            return response()->json([
                'success' => false,
                'message' => 'Akun siswa tidak aktif.',
            ], 403);
        }

        // ================================================================
        // 9. CONSUME NONCE + ISSUE FINANCE TOKEN (atomic via DB transaction)
        // ================================================================
        try {
            DB::beginTransaction();

            // Re-check race condition dengan lockForUpdate
            $alreadyUsed = FinanceAssertionNonce::where('jti', $jti)
                ->lockForUpdate()
                ->exists();

            if ($alreadyUsed) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => 'Assertion sudah pernah digunakan.',
                ], 409);
            }

            // Simpan nonce — TTL dari exp assertion atau fallback nonce_ttl
            $nonceExpiry = $exp instanceof DateTimeImmutable
                ? $exp
                : now()->addMinutes($nonceTtlMinutes)->toDateTimeImmutable();

            FinanceAssertionNonce::consume($jti, $nis, $nonceExpiry);

            // Revoke finance token lama — single session per student
            $user->tokens()->where('name', 'student_finance')->delete();

            // Issue finance token dengan ability 'finance' + expiry 24 jam
            $expiresAt    = now()->addHours($tokenExpiryHours);
            $financeToken = $user->createToken(
                'student_finance',
                ['finance'],
                $expiresAt
            )->plainTextToken;

            DB::commit();

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('FinanceTokenController: gagal issue finance token', [
                'nis'   => $nis,
                'jti'   => $jti,
                'error' => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses pertukaran token.',
            ], 500);
        }

        // Load student profile via Student model — TU User tidak punya relasi studentProfile
        $student = \App\Models\Student::where('nis', $nis)->first();

        Log::info('FinanceTokenController: finance token issued', [
            'user_id'    => $user->id,
            'nis'        => $nis,
            'jti'        => $jti,
            'expires_at' => $expiresAt->toIso8601String(),
        ]);

        return response()->json([
            'success'    => true,
            'token'      => $financeToken,
            'expires_at' => $expiresAt->toIso8601String(),
            'user'       => [
                'nis'  => $user->username,
                'nisn' => $student->nisn ?? null,
                'role' => $user->role,
            ],
        ]);
    }
}
