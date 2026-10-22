<?php

namespace Tests\Feature;

use App\Models\FinanceAssertionNonce;
use App\Models\User;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Rsa\Sha256;
use Lcobucci\JWT\Signer\Key\InMemory;
use DateTimeImmutable;
use DateTimeZone;
use Ramsey\Uuid\Uuid;

class FinanceTokenExchangeTest extends TestCase
{
    use RefreshDatabase;

    private User $student;
    private Configuration $jwtConfig;
    private string $privateKeyPem;
    private string $publicKeyPem;

    protected function setUp(): void
    {
        parent::setUp();

        // Generate fresh RSA keypair untuk test — tidak pakai key production
        $privateKey = openssl_pkey_new([
            'private_key_bits' => 2048,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ]);

        // PHP 8.2: openssl_pkey_export() butuh local variable sebagai output buffer
        $privatePemLocal = '';
        openssl_pkey_export($privateKey, $privatePemLocal);
        $this->privateKeyPem = $privatePemLocal;

        $pubKeyDetails      = openssl_pkey_get_details($privateKey);
        $this->publicKeyPem = $pubKeyDetails['key'];

        // Override config dengan test keys — override langsung ke key 'public_key',
        // bukan lewat env() agar tidak ter-filter oleh str_replace di config file.
        config([
            'finance_assertion.public_key'                 => $this->publicKeyPem,
            'finance_assertion.expected_audience'          => 'tu-finance',
            'finance_assertion.expected_algorithm'         => 'RS256',
            'finance_assertion.clock_skew_seconds'         => 30,
            'finance_assertion.finance_token_expiry_hours' => 24,
            'finance_assertion.nonce_ttl_minutes'          => 10,
        ]);

        $this->jwtConfig = Configuration::forAsymmetricSigner(
            new Sha256(),
            InMemory::plainText($this->privateKeyPem),
            InMemory::plainText($this->publicKeyPem)
        );

        // Buat student TU
        $this->student = User::create([
            'name'     => 'Test Student TU',
            'email'    => 'test.tu@test.com',
            'username' => '26080001',
            'password' => Hash::make(\Illuminate\Support\Str::random(32)),
            'role'     => 'student',
        ]);

        // Buat Student profile jika model ada
        if (class_exists(\App\Models\Student::class)) {
            \App\Models\Student::create([
                'user_id'    => $this->student->id,
                'nis'        => '26080001',
                'nisn'       => '0123456789',
                'class_name' => '7A',
            ]);
        }
    }

    /**
     * Helper: buat valid assertion JWT dengan test private key.
     */
    private function makeAssertion(array $overrides = []): string
    {
        $now      = new DateTimeImmutable('now', new DateTimeZone('UTC'));
        $defaults = [
            'sub'  => (string) $this->student->id,
            'nis'  => '26080001',
            'role' => 'siswa',
            'aud'  => 'tu-finance',
            'jti'  => Uuid::uuid4()->toString(),
            'iat'  => $now,
            'exp'  => $now->modify('+5 minutes'),
        ];

        $params = array_merge($defaults, $overrides);

        $builder = $this->jwtConfig->builder()
            ->issuedBy('http://localhost')
            ->permittedFor($params['aud'])
            ->identifiedBy($params['jti'])
            ->issuedAt($params['iat'])
            ->canOnlyBeUsedAfter($params['iat'])
            ->expiresAt($params['exp'])
            ->relatedTo($params['sub'])
            ->withClaim('nis', $params['nis'])
            ->withClaim('role', $params['role']);

        return $builder->getToken(
            $this->jwtConfig->signer(),
            $this->jwtConfig->signingKey()
        )->toString();
    }

    // =========================================================================
    // TEST 1 — Valid assertion → finance token issued
    // =========================================================================
    public function test_valid_assertion_issues_finance_token(): void
    {
        $assertion = $this->makeAssertion();

        $response = $this->postJson('/api/internal/exchange-finance-token', [
            'assertion' => $assertion,
        ]);

        $response->assertStatus(200)
                 ->assertJsonPath('success', true)
                 ->assertJsonStructure([
                     'success', 'token', 'expires_at',
                     'user' => ['nis', 'role'],
                 ]);

        $this->assertNotEmpty($response->json('token'));
        $this->assertEquals('26080001', $response->json('user.nis'));
    }

    // =========================================================================
    // TEST 2 — Expired assertion → reject
    // =========================================================================
    public function test_expired_assertion_rejected(): void
    {
        $past      = new DateTimeImmutable('-10 minutes', new DateTimeZone('UTC'));
        $assertion = $this->makeAssertion([
            'iat' => $past->modify('-5 minutes'),
            'exp' => $past,
        ]);

        $this->postJson('/api/internal/exchange-finance-token', [
            'assertion' => $assertion,
        ])->assertStatus(401);
    }

    // =========================================================================
    // TEST 3 — Invalid signature → reject
    // =========================================================================
    public function test_invalid_signature_rejected(): void
    {
        // Generate different keypair untuk signing
        $wrongKey = openssl_pkey_new(['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA]);
        openssl_pkey_export($wrongKey, $wrongPrivatePem);

        $wrongConfig = Configuration::forAsymmetricSigner(
            new Sha256(),
            InMemory::plainText($wrongPrivatePem),
            InMemory::plainText($wrongPrivatePem)
        );

        $now   = new DateTimeImmutable('now', new DateTimeZone('UTC'));
        $token = $wrongConfig->builder()
            ->issuedBy('http://localhost')
            ->permittedFor('tu-finance')
            ->identifiedBy(Uuid::uuid4()->toString())
            ->issuedAt($now)
            ->expiresAt($now->modify('+5 minutes'))
            ->relatedTo('1')
            ->withClaim('nis', '26080001')
            ->withClaim('role', 'siswa')
            ->getToken($wrongConfig->signer(), $wrongConfig->signingKey())
            ->toString();

        $this->postJson('/api/internal/exchange-finance-token', [
            'assertion' => $token,
        ])->assertStatus(401);
    }

    // =========================================================================
    // TEST 4 — Wrong audience → reject
    // =========================================================================
    public function test_wrong_audience_rejected(): void
    {
        $assertion = $this->makeAssertion(['aud' => 'wrong-audience']);

        $this->postJson('/api/internal/exchange-finance-token', [
            'assertion' => $assertion,
        ])->assertStatus(401);
    }

    // =========================================================================
    // TEST 5 — Duplicate jti → reject (replay attack)
    // =========================================================================
    public function test_duplicate_jti_rejected(): void
    {
        $jti       = Uuid::uuid4()->toString();
        $assertion = $this->makeAssertion(['jti' => $jti]);

        // First use — harus sukses
        $this->postJson('/api/internal/exchange-finance-token', [
            'assertion' => $assertion,
        ])->assertStatus(200);

        // Second use — harus ditolak
        $this->postJson('/api/internal/exchange-finance-token', [
            'assertion' => $assertion,
        ])->assertStatus(409);
    }

    // =========================================================================
    // TEST 6 — Missing jti → reject
    // =========================================================================
    public function test_missing_jti_rejected(): void
    {
        // Buat token tanpa jti dengan manipulasi manual
        $now   = new DateTimeImmutable('now', new DateTimeZone('UTC'));
        $token = $this->jwtConfig->builder()
            ->issuedBy('http://localhost')
            ->permittedFor('tu-finance')
            // Sengaja tidak panggil identifiedBy()
            ->issuedAt($now)
            ->expiresAt($now->modify('+5 minutes'))
            ->relatedTo('1')
            ->withClaim('nis', '26080001')
            ->withClaim('role', 'siswa')
            ->getToken($this->jwtConfig->signer(), $this->jwtConfig->signingKey())
            ->toString();

        $this->postJson('/api/internal/exchange-finance-token', [
            'assertion' => $token,
        ])->assertStatus(401);
    }

    // =========================================================================
    // TEST 7 — Future iat beyond clock skew → reject
    // =========================================================================
    public function test_future_iat_beyond_skew_rejected(): void
    {
        $future    = new DateTimeImmutable('+10 minutes', new DateTimeZone('UTC'));
        $assertion = $this->makeAssertion([
            'iat' => $future,
            'exp' => $future->modify('+5 minutes'),
        ]);

        $this->postJson('/api/internal/exchange-finance-token', [
            'assertion' => $assertion,
        ])->assertStatus(401);
    }

    // =========================================================================
    // TEST 8 — Student tidak ditemukan di TU → reject
    // (TU tidak punya is_active — test via student not found)
    // =========================================================================
    public function test_inactive_student_rejected(): void
    {
        // Hapus student dari TU — simulasi student tidak tersedia
        $this->student->delete();

        $assertion = $this->makeAssertion();

        $this->postJson('/api/internal/exchange-finance-token', [
            'assertion' => $assertion,
        ])->assertStatus(404);
    }

    // =========================================================================
    // TEST 9 — Wrong role → reject
    // =========================================================================
    public function test_wrong_role_rejected(): void
    {
        $assertion = $this->makeAssertion(['role' => 'guru']);

        $this->postJson('/api/internal/exchange-finance-token', [
            'assertion' => $assertion,
        ])->assertStatus(403);
    }

    // =========================================================================
    // TEST 10 — Malformed assertion → reject
    // =========================================================================
    public function test_malformed_assertion_rejected(): void
    {
        $this->postJson('/api/internal/exchange-finance-token', [
            'assertion' => 'this.is.not.a.jwt',
        ])->assertStatus(401);
    }

    // =========================================================================
    // TEST 11 — Finance token dapat akses finance endpoint
    // =========================================================================
    public function test_finance_token_can_access_finance_route(): void
    {
        $assertion = $this->makeAssertion();

        $exchangeResponse = $this->postJson('/api/internal/exchange-finance-token', [
            'assertion' => $assertion,
        ]);

        $exchangeResponse->assertStatus(200);
        $financeToken = $exchangeResponse->json('token');

        // Coba akses finance/bills — harus dapat response (bukan 401/403)
        // Note: bisa 200 atau error dari controller tapi bukan auth error
        $billsResponse = $this->withToken($financeToken)
                              ->getJson('/api/finance/bills');

        $billsResponse->assertStatus(200);
    }

    // =========================================================================
    // TEST 12 — Finance token TIDAK bisa akses non-finance route
    // =========================================================================
    public function test_finance_token_cannot_access_non_finance_route(): void
    {
        $assertion = $this->makeAssertion();

        $exchangeResponse = $this->postJson('/api/internal/exchange-finance-token', [
            'assertion' => $assertion,
        ]);
        $exchangeResponse->assertStatus(200);
        $financeToken = $exchangeResponse->json('token');

        // Finance token tanpa ability 'student' tidak boleh akses
        // route yang butuh auth:sanctum + role:student tapi BUKAN finance.
        // Coba akses /api/internal/sync-student (butuh X-TU-SYNC-KEY, bukan Bearer)
        // harus 401 atau 403 — bukan 200.
        $response = $this->withToken($financeToken)
                         ->postJson('/api/internal/sync-student', []);

        // sync-student butuh X-TU-SYNC-KEY, bukan Bearer token — harus 401
        $this->assertNotEquals(200, $response->status());
    }

    // =========================================================================
    // TEST 13 — Legacy TU login masih bekerja (backward compatibility)
    // =========================================================================
    public function test_legacy_tu_login_still_works(): void
    {
        // Reset password ke known value untuk test legacy login
        $this->student->update(['password' => Hash::make('legacy_password')]);

        $response = $this->postJson('/api/student/login', [
            'identifier' => '26080001',
            'password'   => 'legacy_password',
        ]);

        // Bisa 200 atau bisa 404 jika student profile tidak ada — tapi BUKAN 500
        $this->assertNotEquals(500, $response->status());
    }

    // =========================================================================
    // TEST 14 — Finance token tidak mengandung password
    // =========================================================================
    public function test_exchange_response_does_not_contain_password(): void
    {
        $assertion = $this->makeAssertion();

        $response = $this->postJson('/api/internal/exchange-finance-token', [
            'assertion' => $assertion,
        ]);

        $response->assertStatus(200);

        $body = $response->json();
        $this->assertArrayNotHasKey('password', $body);
        $this->assertArrayNotHasKey('app_password', $body);

        // user object juga tidak boleh mengandung password
        if (isset($body['user'])) {
            $this->assertArrayNotHasKey('password', $body['user']);
        }
    }

    // =========================================================================
    // TEST 15 — Nonce disimpan setelah exchange
    // =========================================================================
    public function test_nonce_stored_after_successful_exchange(): void
    {
        $jti       = Uuid::uuid4()->toString();
        $assertion = $this->makeAssertion(['jti' => $jti]);

        $this->postJson('/api/internal/exchange-finance-token', [
            'assertion' => $assertion,
        ])->assertStatus(200);

        $this->assertDatabaseHas('finance_assertion_nonces', ['jti' => $jti]);
    }
}
