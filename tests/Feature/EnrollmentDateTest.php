<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Student;
use App\Models\StudentBill;
use Illuminate\Foundation\Testing\DatabaseMigrations;

class EnrollmentDateTest extends TestCase
{
    use DatabaseMigrations;

    // ================================================================
    // HELPERS
    // ================================================================

    private function makeAdmin(): User
    {
        return User::create([
            'name' => 'Admin', 'username' => 'admin001',
            'email' => 'admin@tu.test', 'password' => bcrypt('pw'), 'role' => 'admin',
        ]);
    }

    private function makeTuStaff(): User
    {
        return User::create([
            'name' => 'Staff', 'username' => 'staff001',
            'email' => 'staff@tu.test', 'password' => bcrypt('pw'), 'role' => 'tu',
        ]);
    }

    private function makeStudent(array $overrides = []): Student
    {
        $user = User::create([
            'name'     => $overrides['name'] ?? 'Test Student',
            'username' => $overrides['nis'] ?? '9900001',
            'email'    => ($overrides['nis'] ?? '9900001') . '@test.id',
            'password' => bcrypt('pw'),
            'role'     => 'student',
        ]);

        return Student::create(array_merge([
            'user_id'    => $user->id,
            'nis'        => '9900001',
            'status'     => 'active',
            'enrolled_at'=> null,
        ], array_diff_key($overrides, ['name' => ''])));
    }

    // ================================================================
    // CASE 1: Existing student without enrolled_at remains valid
    // ================================================================

    public function test_existing_student_without_enrolled_at_is_valid(): void
    {
        $student = $this->makeStudent(['enrolled_at' => null]);

        $this->assertNull($student->enrolled_at);
        $this->assertFalse($student->isEnrolled());
        $this->assertDatabaseHas('student_profiles', [
            'id'          => $student->id,
            'enrolled_at' => null,
        ]);
    }

    // ================================================================
    // CASE 2: Provisioned but not enrolled → enrolled_at = NULL
    // ================================================================

    public function test_provisioned_student_has_null_enrolled_at(): void
    {
        $student = $this->makeStudent();

        $this->assertNull($student->fresh()->enrolled_at);
        $this->assertFalse($student->fresh()->isEnrolled());
    }

    // ================================================================
    // CASE 3: Officially enrolled student has enrolled_at set
    // ================================================================

    public function test_enrolled_student_has_enrolled_at(): void
    {
        $admin   = $this->makeAdmin();
        $student = $this->makeStudent(['nis' => '9900002']);

        $this->actingAs($admin)
             ->post("/students/{$student->id}/enrolled-at", [
                 'enrolled_at' => '2026-07-01',
             ])
             ->assertRedirect();

        $fresh = $student->fresh();
        $this->assertNotNull($fresh->enrolled_at);
        $this->assertTrue($fresh->isEnrolled());
        $this->assertEquals('2026-07-01', $fresh->enrolled_at->toDateString());
    }

    // ================================================================
    // CASE 4: created_at != enrolled_at — they are different fields
    // ================================================================

    public function test_created_at_is_not_enrolled_at(): void
    {
        $admin   = $this->makeAdmin();
        $student = $this->makeStudent(['nis' => '9900003']);

        // Set enrolled_at to a different date than created_at
        $this->actingAs($admin)
             ->post("/students/{$student->id}/enrolled-at", [
                 'enrolled_at' => '2026-08-01',
             ]);

        $fresh = $student->fresh();
        // enrolled_at and created_at must be tracked separately
        $this->assertNotEquals(
            $fresh->created_at->toDateString(),
            $fresh->enrolled_at->toDateString()
        );
    }

    // ================================================================
    // CASE 5: NIS unchanged after setting enrolled_at
    // ================================================================

    public function test_nis_unchanged_after_enrolled_at_update(): void
    {
        $admin   = $this->makeAdmin();
        $student = $this->makeStudent(['nis' => '26089715']);

        $originalNis = $student->nis;

        $this->actingAs($admin)
             ->post("/students/{$student->id}/enrolled-at", [
                 'enrolled_at' => '2026-07-01',
             ]);

        $this->assertEquals($originalNis, $student->fresh()->nis);
    }

    // ================================================================
    // CASE 6: Existing bills unchanged after enrolled_at update
    // ================================================================

    public function test_existing_bills_unchanged_after_enrolled_at_update(): void
    {
        $admin   = $this->makeAdmin();
        $student = $this->makeStudent(['nis' => '9900004']);

        $bill = StudentBill::create([
            'student_id' => $student->id,
            'type'       => 'SPP',
            'name'       => 'SPP Juli',
            'amount'     => 500000,
            'status'     => 'UNPAID',
        ]);

        $this->actingAs($admin)
             ->post("/students/{$student->id}/enrolled-at", [
                 'enrolled_at' => '2026-07-01',
             ]);

        // Bill tetap ada dan tidak berubah
        $this->assertDatabaseHas('student_bills', [
            'id'     => $bill->id,
            'amount' => 500000,
            'status' => 'UNPAID',
        ]);
    }

    // ================================================================
    // CASE 7: Existing payments unchanged (via bill status check)
    // ================================================================

    public function test_paid_bills_unchanged_after_enrolled_at_update(): void
    {
        $admin   = $this->makeAdmin();
        $student = $this->makeStudent(['nis' => '9900005']);

        $bill = StudentBill::create([
            'student_id'  => $student->id,
            'type'        => 'SPP',
            'name'        => 'SPP Juni',
            'amount'      => 500000,
            'status'      => 'PAID',
            'paid_amount' => 500000,
        ]);

        $this->actingAs($admin)
             ->post("/students/{$student->id}/enrolled-at", ['enrolled_at' => '2026-07-01']);

        $this->assertDatabaseHas('student_bills', [
            'id'     => $bill->id,
            'status' => 'PAID',
            'amount' => 500000,
        ]);
    }

    // ================================================================
    // Authorization: non-admin cannot update enrolled_at
    // ================================================================

    public function test_non_admin_cannot_update_enrolled_at(): void
    {
        $staff   = $this->makeTuStaff();
        $student = $this->makeStudent(['nis' => '9900006']);

        $this->actingAs($staff)
             ->post("/students/{$student->id}/enrolled-at", [
                 'enrolled_at' => '2026-07-01',
             ])
             ->assertStatus(403);

        $this->assertNull($student->fresh()->enrolled_at);
    }

    // ================================================================
    // Validation: future date rejected
    // ================================================================

    public function test_future_enrolled_at_is_rejected(): void
    {
        $admin   = $this->makeAdmin();
        $student = $this->makeStudent(['nis' => '9900007']);

        $this->actingAs($admin)
             ->post("/students/{$student->id}/enrolled-at", [
                 'enrolled_at' => now()->addYears(2)->toDateString(),
             ])
             ->assertSessionHasErrors('enrolled_at');

        $this->assertNull($student->fresh()->enrolled_at);
    }

    // ================================================================
    // Admin can clear enrolled_at (reset to NULL)
    // ================================================================

    public function test_admin_can_clear_enrolled_at(): void
    {
        $admin   = $this->makeAdmin();
        $student = $this->makeStudent(['nis' => '9900008', 'enrolled_at' => '2026-07-01']);

        $this->actingAs($admin)
             ->post("/students/{$student->id}/enrolled-at", ['enrolled_at' => ''])
             ->assertRedirect();

        $this->assertNull($student->fresh()->enrolled_at);
    }

    // ================================================================
    // Audit log created on enrolled_at update
    // ================================================================

    public function test_audit_log_created_on_enrolled_at_update(): void
    {
        $admin   = $this->makeAdmin();
        $student = $this->makeStudent(['nis' => '9900009']);

        $this->actingAs($admin)
             ->post("/students/{$student->id}/enrolled-at", [
                 'enrolled_at' => '2026-07-01',
             ]);

        $this->assertDatabaseHas('audit_logs', [
            'user_id'     => $admin->id,
            'action'      => 'UPDATE',
            'module'      => 'FINANCE_CONFIG',
            'target_type' => 'Student',
            'target_id'   => $student->id,
        ]);
    }

    // ================================================================
    // isNewStudentForPeriod logic
    // ================================================================

    public function test_is_new_student_for_period_returns_true_for_matching_month(): void
    {
        $student = $this->makeStudent(['nis' => '9900010', 'enrolled_at' => '2026-07-15']);

        $this->assertTrue($student->isNewStudentForPeriod(7, 2026));
        $this->assertFalse($student->isNewStudentForPeriod(8, 2026));
        $this->assertFalse($student->isNewStudentForPeriod(7, 2025));
    }

    public function test_is_new_student_for_period_returns_false_when_not_enrolled(): void
    {
        $student = $this->makeStudent(['enrolled_at' => null]);

        $this->assertFalse($student->isNewStudentForPeriod(7, 2026));
    }

    // ================================================================
    // Haidar and Sarif regression: NIS unchanged, enrolled_at NULL
    // ================================================================

    public function test_haidar_profile_regression(): void
    {
        // Simulate Haidar via sync
        $user = User::create([
            'name' => 'Haidar', 'username' => '26089715',
            'email' => '26089715@sekolah.id', 'password' => bcrypt('pw'), 'role' => 'student',
        ]);
        $student = Student::create([
            'user_id' => $user->id, 'nis' => '26089715',
            'nisn' => '2132154214', 'status' => 'active',
        ]);

        $this->assertEquals('26089715', $student->nis);
        $this->assertNull($student->enrolled_at);
        $this->assertEquals('2132154214', $student->nisn);
    }

    public function test_sarif_profile_regression(): void
    {
        $user = User::create([
            'name' => 'Sarif', 'username' => '26081478',
            'email' => '26081478@sekolah.id', 'password' => bcrypt('pw'), 'role' => 'student',
        ]);
        $student = Student::create([
            'user_id' => $user->id, 'nis' => '26081478',
            'nisn' => '-', 'status' => 'active',
        ]);

        $this->assertEquals('26081478', $student->nis);
        $this->assertNull($student->enrolled_at);
        $this->assertEquals('-', $student->nisn);
    }
}
