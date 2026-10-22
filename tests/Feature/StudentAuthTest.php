<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Student;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Hash;

/**
 * Task 11 — StudentAuthController test suite
 *
 * Membuktikan:
 * - Login dengan NIS + password benar → TU Sanctum token
 * - Password salah → reject (tidak bocorkan apakah NIS atau password yang salah)
 * - NIS tidak ditemukan → reject
 * - Student inactive → reject
 * - Token yang dihasilkan bisa akses /api/finance/dashboard
 * - Finance endpoint dengan token TU berhasil
 * - Finance endpoint tanpa token → 401
 * - Student A tidak bisa akses data student B
 * - Response login tidak mengandung password
 * - Token Kaffah tidak diperlukan
 */
class StudentAuthTest extends TestCase
{
    use DatabaseMigrations;

    // ---------------------------------------------------------------
    // HELPERS
    // ---------------------------------------------------------------

    private function makeStudent(array $overrides = []): array
    {
        $nis = $overrides['nis'] ?? '26080001';
        $password = $overrides['password'] ?? $nis; // default: password = NIS

        $user = User::create([
            'name'     => $overrides['name'] ?? 'Ahmad Fauzan',
            'username' => $nis,
            'email'    => ($overrides['email'] ?? $nis) . '@sekolah.id',
            'password' => Hash::make($password),
            'role'     => 'student',
        ]);

        $profile = Student::create([
            'user_id'    => $user->id,
            'nis'        => $nis,
            'nisn'       => $overrides['nisn'] ?? '0123456789',
            'class_name' => $overrides['class_name'] ?? '7A',
            'status'     => $overrides['status'] ?? 'active',
        ]);

        return compact('user', 'profile', 'nis', 'password');
    }

    private function postLogin(string $identifier, string $password): \Illuminate\Testing\TestResponse
    {
        return $this->postJson('/api/student/login', [
            'identifier' => $identifier,
            'password'   => $password,
        ]);
    }

    // ================================================================
    // TEST 1: Login dengan NIS + password benar → success + token
    // ================================================================

    public function test_login_with_correct_nis_and_password_returns_token(): void
    {
        ['nis' => $nis, 'password' => $password] = $this->makeStudent();

        $response = $this->postLogin($nis, $password);

        $response->assertStatus(200)
                 ->assertJsonPath('status', 'success')
                 ->assertJsonStructure([
                     'status',
                     'token',
                     'user' => ['id', 'name', 'nis', 'nisn', 'class_name', 'role'],
                 ]);

        // Token tidak boleh null/kosong
        $this->assertNotEmpty($response->json('token'));
        // NIS dikembalikan dengan benar
        $this->assertSame($nis, $response->json('user.nis'));
        // Role = student
        $this->assertSame('student', $response->json('user.role'));
    }

    // ================================================================
    // TEST 2: Response login TIDAK mengandung password
    // ================================================================

    public function test_login_response_does_not_contain_password(): void
    {
        // Pakai password berbeda dari NIS agar tidak ada false positive
        // (NIS memang dikembalikan di user.nis — itu benar)
        $nis      = '26080099';
        $password = 'SecurePass123!';

        $user = User::create([
            'name'     => 'Test Siswa',
            'username' => $nis,
            'email'    => $nis . '@sekolah.id',
            'password' => Hash::make($password),
            'role'     => 'student',
        ]);
        Student::create([
            'user_id'    => $user->id,
            'nis'        => $nis,
            'nisn'       => '9999999999',
            'class_name' => '8B',
            'status'     => 'active',
        ]);

        $response = $this->postLogin($nis, $password);
        $response->assertStatus(200);

        $body       = $response->json();
        $bodyString = json_encode($body);

        // Plaintext password tidak boleh muncul di response
        $this->assertStringNotContainsString($password, $bodyString);
        // Key 'password' tidak boleh ada di root maupun di user object
        $this->assertArrayNotHasKey('password', $body);
        $this->assertArrayNotHasKey('password', $body['user'] ?? []);
        // NIS boleh muncul — itu memang dikembalikan sebagai identifier
        $this->assertSame($nis, $response->json('user.nis'));
    }

    // ================================================================
    // TEST 3: Password salah → 401, generic error message
    // ================================================================

    public function test_wrong_password_returns_401(): void
    {
        ['nis' => $nis] = $this->makeStudent();

        $response = $this->postLogin($nis, 'wrong-password-xyz');

        $response->assertStatus(401)
                 ->assertJsonPath('status', 'error');

        // Pesan error tidak boleh membocorkan bahwa NIS ditemukan
        $body = $response->json('message');
        $this->assertStringNotContainsString('ditemukan', strtolower($body));
        $this->assertStringNotContainsString('exists', strtolower($body));
    }

    // ================================================================
    // TEST 4: NIS tidak ditemukan → 401, TIDAK membocorkan "user not found"
    // ================================================================

    public function test_nonexistent_nis_returns_401_without_revealing_reason(): void
    {
        $response = $this->postLogin('99999999', 'anypassword');

        $response->assertStatus(401)
                 ->assertJsonPath('status', 'error');

        // Response yang sama dengan password salah — tidak bocorkan enumeration
        $wrongPassResponse = $this->makeStudent();
        $wrongPass = $this->postLogin($wrongPassResponse['nis'], 'wrongpass');

        // Status code sama
        $this->assertSame(401, $wrongPass->status());
        // JSON key yang sama
        $this->assertSame(
            array_keys($response->json()),
            array_keys($wrongPass->json())
        );
    }

    // ================================================================
    // TEST 5: Student inactive → 403
    // ================================================================

    public function test_inactive_student_cannot_login(): void
    {
        // Valid enum: ['active', 'graduated', 'dropped_out']
        // 'inactive' tidak ada — pakai 'dropped_out' sebagai representasi non-aktif
        ['nis' => $nis, 'password' => $password] = $this->makeStudent([
            'status' => 'dropped_out',
        ]);

        $response = $this->postLogin($nis, $password);

        $response->assertStatus(403)
                 ->assertJsonPath('status', 'error');
    }

    // ================================================================
    // TEST 6: Student graduated → 403
    // ================================================================

    public function test_graduated_student_cannot_login(): void
    {
        ['nis' => $nis, 'password' => $password] = $this->makeStudent([
            'nis'    => '26080002',
            'status' => 'graduated',
        ]);

        $response = $this->postLogin($nis, $password);

        $response->assertStatus(403);
    }

    // ================================================================
    // TEST 7: Validation — identifier kosong → 422
    // ================================================================

    public function test_missing_identifier_returns_422(): void
    {
        $this->postJson('/api/student/login', ['password' => 'test'])
             ->assertStatus(422);
    }

    // ================================================================
    // TEST 8: Validation — password kosong → 422
    // ================================================================

    public function test_missing_password_returns_422(): void
    {
        $this->postJson('/api/student/login', ['identifier' => '26080001'])
             ->assertStatus(422);
    }

    // ================================================================
    // TEST 9: Token yang dihasilkan bisa akses /api/finance/dashboard
    // ================================================================

    public function test_tu_token_can_access_finance_dashboard(): void
    {
        ['nis' => $nis, 'password' => $password] = $this->makeStudent();

        $loginResponse = $this->postLogin($nis, $password);
        $loginResponse->assertStatus(200);

        $token = $loginResponse->json('token');
        $this->assertNotEmpty($token);

        // Gunakan TU Sanctum token untuk akses finance dashboard
        $dashResponse = $this->withHeaders([
            'Authorization' => "Bearer $token",
            'Accept'        => 'application/json',
        ])->getJson('/api/finance/dashboard');

        $dashResponse->assertStatus(200)
                     ->assertJsonPath('status', 'success');
    }

    // ================================================================
    // TEST 10: Finance endpoint tanpa token → 401
    // ================================================================

    public function test_finance_dashboard_without_token_returns_401(): void
    {
        $this->getJson('/api/finance/dashboard')
             ->assertStatus(401);
    }

    // ================================================================
    // TEST 11: Finance bills tanpa token → 401
    // ================================================================

    public function test_finance_bills_without_token_returns_401(): void
    {
        $this->getJson('/api/finance/bills')
             ->assertStatus(401);
    }

    // ================================================================
    // TEST 12: Finance endpoint dengan TU token berhasil
    // ================================================================

    public function test_finance_bills_with_tu_token_returns_200(): void
    {
        ['nis' => $nis, 'password' => $password] = $this->makeStudent();

        $token = $this->postLogin($nis, $password)->json('token');

        $this->withHeaders([
            'Authorization' => "Bearer $token",
            'Accept'        => 'application/json',
        ])->getJson('/api/finance/bills')
          ->assertStatus(200);
    }

    // ================================================================
    // TEST 13: Student A tidak bisa akses data student B
    // ================================================================

    public function test_student_a_cannot_see_student_b_data(): void
    {
        // Buat dua student
        ['nis' => $nisA, 'password' => $passwordA, 'profile' => $profileA] =
            $this->makeStudent(['nis' => '26080010', 'nisn' => '1111111111']);

        ['nis' => $nisB, 'password' => $passwordB, 'profile' => $profileB] =
            $this->makeStudent(['nis' => '26080011', 'nisn' => '2222222222',
                                'name' => 'Budi Santoso', 'email' => '26080011']);

        // Buat bill untuk student B
        \App\Models\StudentBill::create([
            'student_id'  => $profileB->id,
            'type'        => 'SPP',
            'name'        => 'SPP Agustus Budi',
            'amount'      => 500000,
            'status'      => 'UNPAID',
            'paid_amount' => 0,
        ]);

        // Login sebagai student A
        $tokenA = $this->postLogin($nisA, $passwordA)->json('token');

        // Akses bills dengan token A
        $response = $this->withHeaders([
            'Authorization' => "Bearer $tokenA",
            'Accept'        => 'application/json',
        ])->getJson('/api/finance/bills');

        $response->assertStatus(200);

        $bills = $response->json('data');

        // Bills A tidak boleh mengandung bill milik student B
        $billNames = array_column($bills ?? [], 'name');
        $this->assertNotContains('SPP Agustus Budi', $billNames,
            'Student A tidak boleh melihat bill student B');
    }

    // ================================================================
    // TEST 14: Token TU tidak butuh token Kaffah
    // ================================================================

    public function test_finance_works_with_tu_token_without_kaffah_token(): void
    {
        ['nis' => $nis, 'password' => $password] = $this->makeStudent();

        // Login langsung ke TU — tidak ada interaksi dengan Kaffah
        $token = $this->postLogin($nis, $password)->json('token');

        // Finance endpoint bisa diakses dengan token TU saja
        $this->withHeaders([
            'Authorization' => "Bearer $token",
        ])->getJson('/api/finance/dashboard')
          ->assertStatus(200);
    }

    // ================================================================
    // TEST 15: Token lama di-revoke saat login ulang (single session)
    // ================================================================

    public function test_old_token_revoked_on_new_login(): void
    {
        ['nis' => $nis, 'password' => $password] = $this->makeStudent();

        // Login pertama
        $tokenOld = $this->postLogin($nis, $password)->json('token');

        // Login kedua — token lama harus di-revoke
        $tokenNew = $this->postLogin($nis, $password)->json('token');

        $this->assertNotSame($tokenOld, $tokenNew);

        // Token lama tidak bisa akses finance
        $this->withHeaders(['Authorization' => "Bearer $tokenOld"])
             ->getJson('/api/finance/dashboard')
             ->assertStatus(401);

        // Token baru bisa akses
        $this->withHeaders(['Authorization' => "Bearer $tokenNew"])
             ->getJson('/api/finance/dashboard')
             ->assertStatus(200);
    }

    // ================================================================
    // TEST 16: Logout — token di-revoke
    // ================================================================

    public function test_logout_revokes_token(): void
    {
        ['nis' => $nis, 'password' => $password] = $this->makeStudent();

        $token = $this->postLogin($nis, $password)->json('token');
        $this->assertNotEmpty($token);

        // Ambil token ID dari string "id|plaintext"
        $tokenId = (int) explode('|', $token)[0];

        // Pastikan token ada di DB sebelum logout
        $this->assertDatabaseHas('personal_access_tokens', ['id' => $tokenId]);

        // Logout
        $this->withToken($token)
             ->postJson('/api/student/logout')
             ->assertStatus(200)
             ->assertJsonPath('status', 'success');

        // Token harus sudah dihapus dari DB — ini bukti revoke yang sesungguhnya.
        // Sanctum men-validate token via DB lookup, jadi jika token tidak ada
        // di personal_access_tokens maka request berikutnya akan return 401.
        $this->assertDatabaseMissing('personal_access_tokens', ['id' => $tokenId]);
    }

    // ================================================================
    // TEST 17: Response user mengandung nisn dari student_profiles
    // ================================================================

    public function test_login_response_contains_nisn_from_student_profile(): void
    {
        ['nis' => $nis, 'password' => $password] = $this->makeStudent([
            'nisn' => '9876543210',
        ]);

        $response = $this->postLogin($nis, $password);

        $response->assertStatus(200)
                 ->assertJsonPath('user.nisn', '9876543210');
    }

    // ================================================================
    // TEST 18: Non-student (admin) tidak bisa login via student endpoint
    // ================================================================

    public function test_admin_user_cannot_login_via_student_endpoint(): void
    {
        User::create([
            'name'     => 'Admin User',
            'username' => 'admin001',
            'email'    => 'admin@sekolah.id',
            'password' => Hash::make('adminpass'),
            'role'     => 'admin',
        ]);

        $this->postLogin('admin001', 'adminpass')
             ->assertStatus(401);
    }
}
