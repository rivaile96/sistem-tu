<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Student;
use App\Models\StudentBill;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Hash;

/**
 * Task 12 — Student Identity Contract Test
 *
 * Membuktikan:
 * - Student lookup menggunakan user_id, BUKAN nisn fallback
 * - username (NIS) tidak pernah diperlakukan sebagai NISN
 * - getHomeData menemukan student yang benar via user_id
 * - getBills menemukan student yang benar via user_id
 * - Student A tidak bisa mendapatkan data Student B
 * - Login response menyimpan user_nis dan user_nisn yang benar
 * - NISN yang dikembalikan berasal dari student_profiles, bukan username
 */
class StudentIdentityTest extends TestCase
{
    use DatabaseMigrations;

    // ---------------------------------------------------------------
    // HELPERS
    // ---------------------------------------------------------------

    /**
     * Buat student dengan user + profile, return token TU Sanctum.
     */
    private function makeStudentWithToken(array $overrides = []): array
    {
        $nis      = $overrides['nis']      ?? '26080001';
        $nisn     = $overrides['nisn']     ?? '0123456789';
        $password = $overrides['password'] ?? $nis;

        $user = User::create([
            'name'     => $overrides['name']  ?? 'Ahmad Fauzan',
            'username' => $nis,                          // NIS disimpan di username
            'email'    => ($overrides['email'] ?? $nis) . '@test.id',
            'password' => Hash::make($password),
            'role'     => 'student',
        ]);

        $profile = Student::create([
            'user_id'    => $user->id,
            'nis'        => $nis,
            'nisn'       => $nisn,                       // NISN berbeda dari NIS
            'class_name' => $overrides['class_name'] ?? '7A',
            'status'     => $overrides['status']     ?? 'active',
        ]);

        $token = $user->createToken('mobile-student')->plainTextToken;

        return compact('user', 'profile', 'nis', 'nisn', 'password', 'token');
    }

    private function authHeader(string $token): array
    {
        return ['Authorization' => "Bearer $token", 'Accept' => 'application/json'];
    }

    // ================================================================
    // TEST 1: getHomeData menemukan student via user_id, bukan username/NISN
    // ================================================================

    public function test_get_home_data_finds_student_via_user_id(): void
    {
        ['token' => $token, 'profile' => $profile] = $this->makeStudentWithToken();

        $response = $this->withHeaders($this->authHeader($token))
                         ->getJson('/api/finance/dashboard');

        $response->assertStatus(200)
                 ->assertJsonPath('status', 'success');

        // Nama student harus dari profile, bukan default
        $this->assertSame($profile->name ?? 'Ahmad Fauzan', $response->json('student_name'));
    }

    // ================================================================
    // TEST 2: getBills menemukan bills student yang benar via user_id
    // ================================================================

    public function test_get_bills_finds_correct_student_bills_via_user_id(): void
    {
        ['token' => $token, 'profile' => $profile] = $this->makeStudentWithToken();

        // Buat bill untuk student ini
        StudentBill::create([
            'student_id'  => $profile->id,
            'type'        => 'SPP',
            'name'        => 'SPP September Ahmad',
            'amount'      => 600000,
            'status'      => 'UNPAID',
            'paid_amount' => 0,
        ]);

        $response = $this->withHeaders($this->authHeader($token))
                         ->getJson('/api/finance/bills');

        $response->assertStatus(200);

        $bills = $response->json('data');
        $this->assertNotEmpty($bills, 'Bills harus ada untuk student ini');

        $billNames = array_column($bills, 'name');
        $this->assertContains('SPP September Ahmad', $billNames);
    }

    // ================================================================
    // TEST 3: username (NIS) tidak pernah dicocokkan ke nisn
    // ================================================================

    public function test_username_is_never_treated_as_nisn(): void
    {
        // Buat student di mana NIS != NISN (kasus normal semua siswa)
        $nis  = '26080055';
        $nisn = '9876543210'; // NISN yang benar-benar beda dari NIS

        ['token' => $token, 'profile' => $profile] =
            $this->makeStudentWithToken(['nis' => $nis, 'nisn' => $nisn]);

        // Buat student lain yang kebetulan nisn-nya sama dengan NIS student pertama
        // Ini membuktikan bahwa orWhere('nisn', username) bisa menyebabkan cross-lookup
        $otherUser = User::create([
            'name'     => 'Student Lain',
            'username' => '99999999',
            'email'    => 'lain@test.id',
            'password' => Hash::make('99999999'),
            'role'     => 'student',
        ]);
        $otherProfile = Student::create([
            'user_id'    => $otherUser->id,
            'nis'        => '99999999',
            'nisn'       => $nis, // nisn-nya kebetulan = NIS student pertama
            'class_name' => '8B',
            'status'     => 'active',
        ]);

        // Buat bill eksklusif untuk student lain
        StudentBill::create([
            'student_id'  => $otherProfile->id,
            'type'        => 'SPP',
            'name'        => 'SPP Rahasia Student Lain',
            'amount'      => 700000,
            'status'      => 'UNPAID',
            'paid_amount' => 0,
        ]);

        // Login sebagai student pertama — tidak boleh dapat bill student lain
        $response = $this->withHeaders($this->authHeader($token))
                         ->getJson('/api/finance/bills');

        $response->assertStatus(200);

        $bills     = $response->json('data') ?? [];
        $billNames = array_column($bills, 'name');

        $this->assertNotContains(
            'SPP Rahasia Student Lain',
            $billNames,
            'Student tidak boleh melihat bill student lain meskipun NIS-nya = NISN student lain'
        );
    }

    // ================================================================
    // TEST 4: Login response mengandung NIS dari users.username
    // ================================================================

    public function test_login_response_returns_nis_from_username_field(): void
    {
        $nis = '26080077';
        $this->makeStudentWithToken(['nis' => $nis, 'nisn' => '1122334455']);

        $response = $this->postJson('/api/student/login', [
            'identifier' => $nis,
            'password'   => $nis,
        ]);

        $response->assertStatus(200)
                 ->assertJsonPath('user.nis', $nis);

        // NIS di response harus dari users.username, bukan dari student_profiles.nisn
        $this->assertNotSame('1122334455', $response->json('user.nis'));
    }

    // ================================================================
    // TEST 5: Login response mengandung NISN dari student_profiles.nisn
    // ================================================================

    public function test_login_response_returns_nisn_from_student_profile(): void
    {
        $nis  = '26080088';
        $nisn = '5544332211';
        $this->makeStudentWithToken(['nis' => $nis, 'nisn' => $nisn]);

        $response = $this->postJson('/api/student/login', [
            'identifier' => $nis,
            'password'   => $nis,
        ]);

        $response->assertStatus(200)
                 ->assertJsonPath('user.nisn', $nisn);

        // NISN harus dari student_profiles, bukan dari users.username (NIS)
        $this->assertNotSame($nis, $response->json('user.nisn'));
    }

    // ================================================================
    // TEST 6: NIS dan NISN harus berbeda di response
    // ================================================================

    public function test_nis_and_nisn_are_different_values_in_response(): void
    {
        $nis  = '26080091';
        $nisn = '0011223344';

        $this->makeStudentWithToken(['nis' => $nis, 'nisn' => $nisn]);

        $response = $this->postJson('/api/student/login', [
            'identifier' => $nis,
            'password'   => $nis,
        ]);

        $response->assertStatus(200);

        $returnedNis  = $response->json('user.nis');
        $returnedNisn = $response->json('user.nisn');

        $this->assertSame($nis, $returnedNis);
        $this->assertSame($nisn, $returnedNisn);
        // NIS dan NISN adalah dua identifier berbeda — tidak boleh sama
        $this->assertNotSame($returnedNis, $returnedNisn);
    }

    // ================================================================
    // TEST 7: Student tanpa profile → 403 di semua finance endpoint
    // ================================================================

    public function test_user_without_student_profile_gets_403_on_finance(): void
    {
        // User dengan role student tapi tidak punya student_profiles
        $user = User::create([
            'name'     => 'Ghost Student',
            'username' => '00000000',
            'email'    => 'ghost@test.id',
            'password' => Hash::make('00000000'),
            'role'     => 'student',
        ]);
        $token = $user->createToken('mobile-student')->plainTextToken;

        // dashboard — untuk getHomeData, return 200 dengan data kosong (by design)
        // bills → 403
        $this->withHeaders($this->authHeader($token))
             ->getJson('/api/finance/bills')
             ->assertStatus(403);
    }

    // ================================================================
    // TEST 8: Student A tidak bisa lihat data Student B di dashboard
    // ================================================================

    public function test_home_data_only_shows_own_student_data(): void
    {
        ['token' => $tokenA, 'profile' => $profileA] =
            $this->makeStudentWithToken([
                'nis'  => '26080100',
                'nisn' => '1000000001',
                'name' => 'Siswa A',
            ]);

        ['profile' => $profileB] =
            $this->makeStudentWithToken([
                'nis'   => '26080101',
                'nisn'  => '1000000002',
                'name'  => 'Siswa B',
                'email' => '26080101',
            ]);

        // Buat bills untuk B
        StudentBill::create([
            'student_id'  => $profileB->id,
            'type'        => 'SPP',
            'name'        => 'SPP B Exclusive',
            'amount'      => 500000,
            'status'      => 'UNPAID',
            'paid_amount' => 0,
        ]);

        // Login sebagai A — dashboard harus menampilkan nama A, bukan B
        $response = $this->withHeaders($this->authHeader($tokenA))
                         ->getJson('/api/finance/dashboard');

        $response->assertStatus(200);

        // student_name harus A, bukan B
        $this->assertNotSame('Siswa B', $response->json('student_name'));
        $this->assertSame('Siswa A', $response->json('student_name'));
    }

    // ================================================================
    // TEST 9: getHomeData return 200 dengan tagihan 0 jika tidak ada bill
    // ================================================================

    public function test_home_data_returns_zero_amount_when_no_bills(): void
    {
        ['token' => $token] = $this->makeStudentWithToken([
            'nis'  => '26080200',
            'nisn' => '2000000001',
        ]);

        $response = $this->withHeaders($this->authHeader($token))
                         ->getJson('/api/finance/dashboard');

        $response->assertStatus(200)
                 ->assertJsonPath('status', 'success')
                 ->assertJsonPath('summary.dashboard_amount', 0);
    }

    // ================================================================
    // TEST 10: getBills return array kosong jika tidak ada bill
    // ================================================================

    public function test_get_bills_returns_empty_array_when_no_bills(): void
    {
        ['token' => $token] = $this->makeStudentWithToken([
            'nis'  => '26080201',
            'nisn' => '2000000002',
        ]);

        $response = $this->withHeaders($this->authHeader($token))
                         ->getJson('/api/finance/bills');

        $response->assertStatus(200);

        $bills = $response->json('data');
        $this->assertIsArray($bills);
        $this->assertEmpty($bills);
    }
}
