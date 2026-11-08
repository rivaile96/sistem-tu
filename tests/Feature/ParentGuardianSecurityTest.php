<?php

namespace Tests\Feature;

use App\Models\PosOrder;
use App\Models\Student;
use App\Models\StudentBill;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ParentGuardianSecurityTest extends TestCase
{
    use RefreshDatabase;

    private function createStudentWithBill(array $overrides = []): array
    {
        $nis      = $overrides['nis'] ?? '26081001';
        $password = $overrides['password'] ?? 'Password123!';
        $phone    = $overrides['parent_phone'] ?? '081234567801';

        $user = User::create([
            'name'      => $overrides['name'] ?? 'Siswa ' . $nis,
            'username'  => $nis,
            'email'     => $nis . '@kaffah.sch.id',
            'password'  => Hash::make($password),
            'role'      => $overrides['role'] ?? 'student',
            'is_active' => $overrides['is_active'] ?? true,
            'phone'     => $phone,
        ]);

        $student = Student::create([
            'user_id'      => $user->id,
            'nis'          => $nis,
            'nisn'         => $overrides['nisn'] ?? ('00' . $nis),
            'class_name'   => $overrides['class_name'] ?? '7A',
            'parent_phone' => $phone,
            'status'       => $overrides['status'] ?? 'active',
        ]);

        $bill = StudentBill::create([
            'student_id'        => $student->id,
            'type'              => 'SPP',
            'name'              => $overrides['bill_name'] ?? ('SPP ' . $nis),
            'amount'            => $overrides['bill_amount'] ?? 450000,
            'status'            => $overrides['bill_status'] ?? 'UNPAID',
            'paid_amount'       => 0,
            'midtrans_order_id' => $overrides['order_id'] ?? ('BILL-' . $nis . '-100'),
        ]);

        return compact('user', 'student', 'bill', 'nis', 'password');
    }

    public function test_sec07a_valid_and_invalid_login_and_inactive_and_expired_token(): void
    {
        $data = $this->createStudentWithBill(['nis' => '26081001']);

        // 1. Login valid
        $loginRes = $this->postJson('/api/student/login', [
            'identifier' => $data['nis'],
            'password'   => $data['password'],
        ]);
        $loginRes->assertStatus(200)
            ->assertJsonPath('status', 'success');

        $token = $loginRes->json('token');
        $this->assertNotEmpty($token);

        // 2. Login salah password -> 401 generic
        $this->postJson('/api/student/login', [
            'identifier' => $data['nis'],
            'password'   => 'WrongPassword999!',
        ])->assertStatus(401)
          ->assertJsonPath('status', 'error');

        // 3. Akun non-aktif ditolak (403)
        $inactive = $this->createStudentWithBill([
            'nis'       => '26081002',
            'is_active' => false,
        ]);
        $this->postJson('/api/student/login', [
            'identifier' => $inactive['nis'],
            'password'   => $inactive['password'],
        ])->assertStatus(403);

        // Jika akun dinonaktifkan setelah punya token -> 403 saat akses endpoint
        $data['user']->update(['is_active' => false]);
        $this->withToken($token)
            ->getJson('/api/finance/dashboard')
            ->assertStatus(403);

        // Aktifkan kembali lalu kadaluarsakan token -> 401
        $data['user']->update(['is_active' => true]);
        $data['user']->tokens()->update(['expires_at' => now()->subMinute()]);
        $this->app['auth']->forgetGuards();

        $this->withToken($token)
            ->getJson('/api/finance/dashboard')
            ->assertStatus(401);
    }

    public function test_sec07a_guardian_a_can_access_child_a_but_blocked_from_child_b_idor(): void
    {
        $childA = $this->createStudentWithBill([
            'nis'          => '26082001',
            'parent_phone' => '081111111111',
            'bill_name'    => 'SPP Anak A',
            'order_id'     => 'ORDER-CHILD-A',
        ]);
        $childB = $this->createStudentWithBill([
            'nis'          => '26082002',
            'parent_phone' => '082222222222',
            'bill_name'    => 'SPP Anak B Rahasia',
            'order_id'     => 'ORDER-CHILD-B',
        ]);

        $tokenA = $childA['user']->createToken('student_finance', ['finance'], now()->addDay())->plainTextToken;

        // 5. Wali A akses Anak A berhasil
        $dashA = $this->withToken($tokenA)->getJson('/api/finance/dashboard');
        $dashA->assertStatus(200)
            ->assertJsonPath('nis', '26082001');
        $this->assertStringNotContainsString('SPP Anak B Rahasia', $dashA->getContent());

        // 6 & 8. Wali A ditolak saat memanipulasi student_id / nis / bill_ids / order_id milik Anak B
        $this->withToken($tokenA)
            ->getJson('/api/finance/dashboard?student_id=' . $childB['student']->id)
            ->assertStatus(403);

        $this->withToken($tokenA)
            ->getJson('/api/finance/bills?nis=' . $childB['student']->nis)
            ->assertStatus(403);

        $this->withToken($tokenA)
            ->getJson('/api/finance/transactions?student_id=' . $childB['student']->id)
            ->assertStatus(403);

        $this->withToken($tokenA)
            ->postJson('/api/finance/payment/create', [
                'id'   => $childB['bill']->id,
                'type' => 'SPP',
            ])
            ->assertStatus(403);

        $this->withToken($tokenA)
            ->postJson('/api/finance/payment/create', [
                'bill_ids'     => [$childB['bill']->id],
                'payment_type' => 'bank_transfer',
            ])
            ->assertStatus(403);

        $this->withToken($tokenA)
            ->getJson('/api/finance/payment/status/ORDER-CHILD-B')
            ->assertStatus(403);

        $this->withToken($tokenA)
            ->getJson('/api/finance/payment/invoice/ORDER-CHILD-B')
            ->assertStatus(403);

        $this->withToken($tokenA)
            ->getJson('/api/finance/payment/invoice/' . $childB['bill']->id)
            ->assertStatus(403);
    }

    public function test_sec07a_guardian_with_multiple_children_can_switch_between_own_children_only(): void
    {
        $sharedGuardianPhone = '081999888777';

        $childA1 = $this->createStudentWithBill([
            'nis'          => '26083001',
            'name'         => 'Anak Pertama',
            'parent_phone' => $sharedGuardianPhone,
            'bill_name'    => 'SPP Anak 1',
        ]);
        $childA2 = $this->createStudentWithBill([
            'nis'          => '26083002',
            'name'         => 'Anak Kedua',
            'parent_phone' => $sharedGuardianPhone,
            'bill_name'    => 'SPP Anak 2',
        ]);
        $otherChild = $this->createStudentWithBill([
            'nis'          => '26083099',
            'name'         => 'Anak Orang Lain',
            'parent_phone' => '081000000000',
            'bill_name'    => 'SPP Anak Lain',
        ]);

        $tokenGuardian = $childA1['user']->createToken('student_finance', ['finance'], now()->addDay())->plainTextToken;

        // Akses Anak 1
        $res1 = $this->withToken($tokenGuardian)
            ->getJson('/api/finance/dashboard?student_id=' . $childA1['student']->id);
        $res1->assertStatus(200)
            ->assertJsonPath('nis', '26083001');
        $this->assertCount(2, $res1->json('children'));

        // Akses Anak 2 (sah karena parent_phone sama)
        $res2 = $this->withToken($tokenGuardian)
            ->getJson('/api/finance/dashboard?student_id=' . $childA2['student']->id);
        $res2->assertStatus(200)
            ->assertJsonPath('nis', '26083002');

        // Akses Anak Orang Lain -> 403 Forbidden
        $this->withToken($tokenGuardian)
            ->getJson('/api/finance/dashboard?student_id=' . $otherChild['student']->id)
            ->assertStatus(403);
    }

    public function test_sec07a_pin_is_hashed_never_plaintext_and_rate_limited(): void
    {
        $child = $this->createStudentWithBill(['nis' => '26084001']);
        $token = $child['user']->createToken('student_finance', ['finance'], now()->addDay())->plainTextToken;
        $rawPin = '654321';

        // Set PIN
        $setRes = $this->withToken($token)
            ->postJson('/api/finance/pin/set', ['pin' => $rawPin]);

        $setRes->assertStatus(200)
            ->assertJsonPath('status', 'success');

        // PIN tidak pernah muncul di response
        $this->assertStringNotContainsString($rawPin, $setRes->getContent());

        // PIN disimpan ter-hash di database (bukan plaintext)
        $freshUser    = $child['user']->fresh();
        $freshStudent = $child['student']->fresh();

        $this->assertNotNull($freshUser->pin);
        $this->assertNotSame($rawPin, $freshUser->pin);
        $this->assertTrue(Hash::check($rawPin, $freshUser->pin));
        $this->assertTrue(Hash::check($rawPin, $freshStudent->pin));
        $this->assertArrayNotHasKey('pin', $freshUser->toArray());
        $this->assertArrayNotHasKey('pin', $freshStudent->toArray());

        // Verifikasi PIN benar -> 200 (request ke-2 pada throttle:5,1 setelah pin/set)
        $this->withToken($token)
            ->postJson('/api/finance/pin/verify', ['pin' => $rawPin])
            ->assertStatus(200)
            ->assertJsonPath('status', 'success');

        // Verifikasi PIN salah 3x berikutnya -> 401 (total 5 request dalam 1 menit)
        for ($i = 0; $i < 3; $i++) {
            $this->withToken($token)
                ->postJson('/api/finance/pin/verify', ['pin' => '000000'])
                ->assertStatus(401);
        }

        // Request ke-6 terkena rate limit throttle:5,1 -> 429 Too Many Requests
        $this->withToken($token)
            ->postJson('/api/finance/pin/verify', ['pin' => '000000'])
            ->assertStatus(429);
    }
}
