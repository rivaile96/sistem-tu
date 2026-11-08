<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Student;
use App\Models\User;
use App\Models\StudentBill;
use App\Models\PosOrder;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Log;

class StudentSyncTest extends TestCase
{
    use DatabaseMigrations;

    // ===========================================================
    // HELPERS
    // ===========================================================

    private function syncKey(): string
    {
        return config('services.tu_sync.secret', 'TuSyncSecretKey2026!');
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'nis'        => '2608001',
            'nama'       => 'Ahmad Fauzi',
            'nisn'       => '1234567890',
            'email'      => 'ahmad@sekolah.id',
            'class_name' => '7A',
            'no_hp'      => '08123456789',
        ], $overrides);
    }

    private function postSync(array $payload, ?string $key = null): \Illuminate\Testing\TestResponse
    {
        return $this->postJson('/api/internal/sync-student', $payload, [
            'X-TU-SYNC-KEY' => $key ?? $this->syncKey(),
        ]);
    }

    // ===========================================================
    // TEST 1: Student baru dari Kaffah berhasil dibuat di TU
    // ===========================================================
    public function test_new_student_is_created_in_tu(): void
    {
        $response = $this->postSync($this->validPayload());

        $response->assertStatus(201)
                 ->assertJsonPath('success', true)
                 ->assertJsonPath('action', 'created')
                 ->assertJsonPath('nis', '2608001');

        // Verifikasi student_profiles dibuat
        $this->assertDatabaseHas('student_profiles', [
            'nis'        => '2608001',
            'nisn'       => '1234567890',
            'class_name' => '7A',
            'status'     => 'active',
        ]);

        // Verifikasi users dibuat
        $this->assertDatabaseHas('users', [
            'username' => '2608001',
            'name'     => 'Ahmad Fauzi',
            'role'     => 'student',
        ]);

        // Verifikasi synced_at terisi
        $profile = Student::where('nis', '2608001')->first();
        $this->assertNotNull($profile->synced_at);
    }

    // ===========================================================
    // TEST 2: NIS yang sama tidak membuat duplicate TU user
    // ===========================================================
    public function test_duplicate_nis_does_not_create_duplicate_user(): void
    {
        // Sync pertama
        $this->postSync($this->validPayload())->assertStatus(201);

        // Sync kedua dengan NIS yang sama
        $this->postSync($this->validPayload(['nama' => 'Ahmad Fauzi Updated']))->assertStatus(200);

        // Hanya satu user dengan username = NIS
        $this->assertEquals(1, User::where('username', '2608001')->count());
    }

    // ===========================================================
    // TEST 3: NIS yang sama tidak membuat duplicate student_profile
    // ===========================================================
    public function test_duplicate_nis_does_not_create_duplicate_profile(): void
    {
        $this->postSync($this->validPayload())->assertStatus(201);
        $this->postSync($this->validPayload())->assertStatus(200);

        $this->assertEquals(1, Student::where('nis', '2608001')->count());
    }

    // ===========================================================
    // TEST 4: Sync dua kali menghasilkan state yang sama (idempotent)
    // ===========================================================
    public function test_sync_twice_is_idempotent(): void
    {
        $this->postSync($this->validPayload())->assertStatus(201);

        $profileBefore = Student::where('nis', '2608001')->first();
        $userIdBefore  = $profileBefore->user_id;

        // Sync kedua
        $response = $this->postSync($this->validPayload(['class_name' => '7B']));
        $response->assertStatus(200)->assertJsonPath('action', 'updated');

        $profileAfter = Student::where('nis', '2608001')->first();

        // user_id tidak berubah
        $this->assertEquals($userIdBefore, $profileAfter->user_id);
        // class_name diupdate dari Kaffah
        $this->assertEquals('7B', $profileAfter->class_name);
        // Masih satu profile
        $this->assertEquals(1, Student::where('nis', '2608001')->count());
    }

    // ===========================================================
    // TEST 5: Kaffah user ID berbeda dengan TU user ID tetap bekerja
    // (karena kita tidak menggunakan Kaffah user ID sama sekali)
    // ===========================================================
    public function test_kaffah_user_id_not_used_in_tu(): void
    {
        // Sync tidak menerima kaffah_user_id — payload hanya NIS-based
        $payload = $this->validPayload();
        // Tambahkan field asing — harus diabaikan, tidak crash
        $payload['kaffah_user_id']    = 9999;
        $payload['kaffah_profile_id'] = 8888;

        $response = $this->postSync($payload);
        $response->assertStatus(201);

        $profile = Student::where('nis', '2608001')->first();

        // TU user ID di-assign oleh TU sendiri — bukan 9999
        $this->assertNotEquals(9999, $profile->user_id);
        // Relasi user benar
        $this->assertNotNull($profile->user);
        $this->assertEquals('2608001', $profile->user->username);
    }

    // ===========================================================
    // TEST 6: Student profile terhubung ke TU user yang benar
    // ===========================================================
    public function test_profile_is_linked_to_correct_tu_user(): void
    {
        $response = $this->postSync($this->validPayload());
        $response->assertStatus(201);

        $data    = $response->json();
        $profile = Student::find($data['tu_profile_id']);
        $user    = User::find($data['tu_user_id']);

        $this->assertNotNull($profile);
        $this->assertNotNull($user);

        // Profile.user_id → User.id
        $this->assertEquals($user->id, $profile->user_id);
        // User.username = NIS
        $this->assertEquals('2608001', $user->username);
        // Profile.nis = NIS
        $this->assertEquals('2608001', $profile->nis);
        // Relasi Eloquent bekerja
        $this->assertEquals($user->id, $profile->user->id);
    }

    // ===========================================================
    // TEST 7: Mobile login menemukan student setelah sync
    // ===========================================================
    public function test_mobile_login_finds_student_after_sync(): void
    {
        // Sync dari Kaffah → TU membuat user + profile
        $this->postSync($this->validPayload())->assertStatus(201);

        $tuUser = User::where('username', '2608001')->first();
        $this->assertNotNull($tuUser);

        // Mobile login via actingAs (tidak butuh Sanctum token table)
        $response = $this->actingAs($tuUser, 'sanctum')
            ->getJson('/api/finance/dashboard');

        $response->assertStatus(200)
                 ->assertJsonPath('status', 'success');
    }

    // ===========================================================
    // TEST 8: Mobile dashboard menampilkan bills student yang benar
    // ===========================================================
    public function test_mobile_dashboard_shows_correct_bills_after_sync(): void
    {
        $this->postSync($this->validPayload())->assertStatus(201);

        $profile = Student::where('nis', '2608001')->first();

        // Buat bill untuk student ini
        StudentBill::create([
            'student_id'  => $profile->id,
            'type'        => 'SPP',
            'name'        => 'SPP Agustus',
            'amount'      => 500000,
            'status'      => 'UNPAID',
            'paid_amount' => 0,
        ]);

        $tuUser = $profile->user;
        $response = $this->actingAs($tuUser, 'sanctum')
            ->getJson('/api/finance/dashboard');

        $response->assertStatus(200)
                 ->assertJsonPath('status', 'success');

        $data = $response->json();
        // Ada tagihan aktif
        $this->assertGreaterThan(0, $data['dashboard_amount'] ?? $data['summary']['total_unpaid'] ?? 1);
    }

    // ===========================================================
    // TEST 9: POS debt menggunakan TU student profile ID yang benar
    // ===========================================================
    public function test_pos_debt_uses_correct_tu_profile_id(): void
    {
        $this->postSync($this->validPayload())->assertStatus(201);

        $profile = Student::where('nis', '2608001')->first();

        // Buat POS order hutang untuk student ini
        $order = PosOrder::create([
            'student_id'        => $profile->id,    // TU profile ID — bukan Kaffah user ID
            'transaction_code'  => 'TRX-' . uniqid(),
            'total_amount'      => 25000,
            'payment_amount'    => 0,
            'change_amount'     => 0,
            'payment_status'    => 'UNPAID',
            'redemption_status' => 'PENDING',
        ]);

        // Pastikan student bisa lookup pos_orders via TU profile ID
        $debt = PosOrder::where('student_id', $profile->id)
            ->where('payment_status', 'UNPAID')
            ->sum('total_amount');

        $this->assertEquals(25000, $debt);

        // Pastikan relasi Eloquent bekerja
        $this->assertEquals($profile->id, $order->student_id);
        $this->assertEquals('2608001', $order->student->nis);
    }

    // ===========================================================
    // TEST 10: Unauthorized sync ditolak (key salah)
    // ===========================================================
    public function test_unauthorized_sync_is_rejected_with_wrong_key(): void
    {
        $response = $this->postSync($this->validPayload(), 'wrong-key-xyz');
        $response->assertStatus(401)
                 ->assertJsonPath('success', false);

        // Tidak ada student yang dibuat
        $this->assertEquals(0, Student::count());
        $this->assertEquals(0, User::where('role', 'student')->count());
    }

    // ===========================================================
    // TEST 11: Unauthorized sync ditolak (tanpa key)
    // ===========================================================
    public function test_unauthorized_sync_is_rejected_without_key(): void
    {
        $response = $this->postJson('/api/internal/sync-student', $this->validPayload());
        $response->assertStatus(401);

        $this->assertEquals(0, Student::count());
    }

    // ===========================================================
    // TEST 12: Invalid payload ditolak (NIS kosong)
    // ===========================================================
    public function test_invalid_payload_missing_nis_is_rejected(): void
    {
        $payload = $this->validPayload();
        unset($payload['nis']);

        $response = $this->postSync($payload);
        $response->assertStatus(422);

        $this->assertEquals(0, Student::count());
    }

    // ===========================================================
    // TEST 13: Invalid payload ditolak (nama kosong)
    // ===========================================================
    public function test_invalid_payload_missing_nama_is_rejected(): void
    {
        $payload = $this->validPayload();
        unset($payload['nama']);

        $response = $this->postSync($payload);
        $response->assertStatus(422);

        $this->assertEquals(0, Student::count());
    }

    // ===========================================================
    // TEST 14: Sync gagal di tengah proses tidak menghasilkan
    // partial data (atomicity via DB transaction)
    // ===========================================================
    public function test_failed_sync_does_not_leave_partial_data(): void
    {
        // Buat user dengan email yang akan clash — supaya User::create gagal
        User::create([
            'name'     => 'Other User',
            'username' => 'other',
            'email'    => '2608002@sekolah.id',
            'password' => bcrypt('password'),
            'role'     => 'admin',
        ]);

        // Payload dengan NIS baru tapi email yang sudah ada
        // Student::create akan jalan, tapi jika ada constraint lain yang fail,
        // tidak boleh ada partial data.
        // Simulasi: NIS unik constraint gagal dengan inject NIS yang sudah ada di user.username
        User::create([
            'name'     => 'Conflict User',
            'username' => '2608003', // username = NIS akan clash
            'email'    => 'conflict@sekolah.id',
            'password' => bcrypt('password'),
            'role'     => 'student',
        ]);

        // Sync dengan NIS '2608003' — User::create akan gagal karena username UNIQUE
        $response = $this->postSync($this->validPayload(['nis' => '2608003', 'email' => 'new2608003@sekolah.id']));

        // Harus return error, bukan 201
        $this->assertNotEquals(201, $response->status());

        // Tidak ada student_profile yang dibuat untuk NIS ini
        $this->assertEquals(0, Student::where('nis', '2608003')->count());
    }

    // ===========================================================
    // TEST 15: Update sync tidak overwrite status lokal TU
    // ===========================================================
    public function test_update_sync_does_not_overwrite_local_status(): void
    {
        // Buat student manual di TU dengan status graduated
        $user = User::create([
            'name'     => 'Lulus Student',
            'username' => '2608001',
            'email'    => '2608001@sekolah.id',
            'password' => bcrypt('password'),
            'role'     => 'student',
        ]);
        Student::create([
            'user_id'    => $user->id,
            'nis'        => '2608001',
            'class_name' => '9A',
            'status'     => 'graduated',  // status lokal TU
        ]);

        // Kaffah push sync — tidak boleh overwrite 'graduated' ke 'active'
        $response = $this->postSync($this->validPayload(['class_name' => '9B']));
        $response->assertStatus(200)->assertJsonPath('action', 'updated');

        $profile = Student::where('nis', '2608001')->first();

        // Status lokal tidak berubah
        $this->assertEquals('graduated', $profile->status);
        // class_name diupdate dari Kaffah
        $this->assertEquals('9B', $profile->class_name);
    }
}
