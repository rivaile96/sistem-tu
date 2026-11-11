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

    // ================================================================
    // TEST 11 (TU-01): getHomeData menggunakan student_profiles.id,
    // BUKAN users.id ketika users.id != student_profiles.id
    // ================================================================

    public function test_home_data_uses_student_profile_id_when_user_id_differs(): void
    {
        // Buat 2 akun staff/admin terlebih dahulu agar users.id bergeser dari student_profiles.id
        User::create([
            'name'     => 'Admin 1',
            'username' => 'admin1',
            'email'    => 'admin1@test.id',
            'password' => Hash::make('secret'),
            'role'     => 'admin',
        ]);
        User::create([
            'name'     => 'Admin 2',
            'username' => 'admin2',
            'email'    => 'admin2@test.id',
            'password' => Hash::make('secret'),
            'role'     => 'tu',
        ]);

        // Siswa Pertama: users.id = 3, tetapi student_profiles.id = 1
        [
            'user'    => $userA,
            'profile' => $profileA,
            'token'   => $tokenA,
        ] = $this->makeStudentWithToken([
            'nis'        => '26080301',
            'nisn'       => '3000000001',
            'name'       => 'Siswa Offset A',
            'class_name' => '9A',
        ]);

        // Buat Siswa Kedua & Ketiga sehingga ada student_profiles dengan id == $userA->id (id = 3)
        $this->makeStudentWithToken([
            'nis'   => '26080302',
            'nisn'  => '3000000002',
            'name'  => 'Siswa Offset B',
            'email' => '26080302',
        ]);
        ['profile' => $profileCollision] = $this->makeStudentWithToken([
            'nis'   => '26080303',
            'nisn'  => '3000000003',
            'name'  => 'Siswa Collision ID',
            'email' => '26080303',
        ]);

        $this->assertNotSame($userA->id, $profileA->id, 'Precondition: users.id harus berbeda dari student_profiles.id');
        $this->assertSame($userA->id, $profileCollision->id, 'Precondition: profileCollision->id sengaja sama dengan userA->id');

        // Tagihan milik Siswa A (student_id = profileA->id = 1)
        StudentBill::create([
            'student_id'  => $profileA->id,
            'type'        => 'SPP',
            'name'        => 'SPP Milik Siswa A',
            'amount'      => 450000,
            'status'      => 'UNPAID',
            'paid_amount' => 0,
        ]);

        // Tagihan milik Siswa Collision (student_id = profileCollision->id == userA->id = 3)
        StudentBill::create([
            'student_id'  => $profileCollision->id,
            'type'        => 'SPP',
            'name'        => 'SPP BOCOR Milik Siswa Collision',
            'amount'      => 999000,
            'status'      => 'UNPAID',
            'paid_amount' => 0,
        ]);

        $response = $this->withHeaders($this->authHeader($tokenA))
                         ->getJson('/api/finance/dashboard');

        $response->assertStatus(200)
                 ->assertJsonPath('status', 'success')
                 ->assertJsonPath('nis', '26080301')
                 ->assertJsonPath('class_name', '9A')
                 ->assertJsonPath('summary.total_tagihan', 450000)
                 ->assertJsonPath('summary.count', 1);

        $titles = array_column($response->json('list_tagihan'), 'title');
        $this->assertContains('SPP Milik Siswa A', $titles);
        $this->assertNotContains('SPP BOCOR Milik Siswa Collision', $titles);
    }

    // ================================================================
    // TEST 12 (TU-01): Isolasi kepemilikan pada createPayment,
    // checkStatus, getInvoiceDetail, dan getTransactionHistory
    // ================================================================

    public function test_finance_payment_and_history_endpoints_enforce_student_profile_ownership(): void
    {
        ['token' => $tokenA, 'profile' => $profileA] = $this->makeStudentWithToken([
            'nis'  => '26080401',
            'nisn' => '4000000001',
            'name' => 'Siswa A',
        ]);

        ['profile' => $profileB] = $this->makeStudentWithToken([
            'nis'   => '26080402',
            'nisn'  => '4000000002',
            'name'  => 'Siswa B',
            'email' => '26080402',
        ]);

        $billA = StudentBill::create([
            'student_id'        => $profileA->id,
            'type'              => 'SPP',
            'name'              => 'SPP Lunas Siswa A',
            'amount'            => 500000,
            'status'            => 'PAID',
            'paid_amount'       => 500000,
            'midtrans_order_id' => 'BILL-A-1001',
            'payment_method'    => 'MIDTRANS',
        ]);

        $billBUnpaid = StudentBill::create([
            'student_id'  => $profileB->id,
            'type'        => 'SPP',
            'name'        => 'SPP Belum Lunas Siswa B',
            'amount'      => 650000,
            'status'      => 'UNPAID',
            'paid_amount' => 0,
        ]);

        $billBPaid = StudentBill::create([
            'student_id'        => $profileB->id,
            'type'              => 'SPP',
            'name'              => 'SPP Lunas Siswa B',
            'amount'            => 650000,
            'status'            => 'PAID',
            'paid_amount'       => 650000,
            'midtrans_order_id' => 'BILL-B-2002',
            'payment_method'    => 'MIDTRANS',
        ]);

        // 1. Siswa A tidak boleh membuat pembayaran atas bill milik Siswa B (IDOR blocked 403 or rejected 400)
        $paymentResp = $this->withHeaders($this->authHeader($tokenA))
             ->postJson('/api/finance/payment/create', [
                 'id'   => $billBUnpaid->id,
                 'type' => 'SPP',
             ]);
        $this->assertTrue(
            in_array($paymentResp->getStatusCode(), [400, 403], true),
            'Expected 400 or 403 when creating payment for another student bill'
        );

        // 2. Siswa A hanya melihat transaksi miliknya sendiri di /api/finance/transactions
        $historyResp = $this->withHeaders($this->authHeader($tokenA))
                            ->getJson('/api/finance/transactions')
                            ->assertStatus(200);

        $historyTitles = array_column($historyResp->json('data'), 'title');
        $this->assertContains('SPP Lunas Siswa A', $historyTitles);
        $this->assertNotContains('SPP Lunas Siswa B', $historyTitles);

        // 3. Siswa A dapat cek status & invoice miliknya sendiri, tetapi 404 untuk order milik Siswa B
        $this->withHeaders($this->authHeader($tokenA))
             ->getJson('/api/finance/payment/status/BILL-A-1001')
             ->assertStatus(200)
             ->assertJsonPath('payment_status', 'PAID');

        $statusB = $this->withHeaders($this->authHeader($tokenA))
             ->getJson('/api/finance/payment/status/BILL-B-2002');
        $this->assertTrue(in_array($statusB->getStatusCode(), [403, 404], true));

        $this->withHeaders($this->authHeader($tokenA))
             ->getJson('/api/finance/payment/invoice/BILL-A-1001')
             ->assertStatus(200)
             ->assertJsonPath('data.total_amount', 500000);

        $invoiceB = $this->withHeaders($this->authHeader($tokenA))
             ->getJson('/api/finance/payment/invoice/BILL-B-2002');
        $this->assertTrue(in_array($invoiceB->getStatusCode(), [403, 404], true));
    }
}
