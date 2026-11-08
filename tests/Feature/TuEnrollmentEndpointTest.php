<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Student;
use Illuminate\Foundation\Testing\DatabaseMigrations;

/**
 * Tests for TU enrollment endpoint.
 * POST /api/internal/student-enrollment
 * Auth: X-TU-SYNC-KEY
 */
class TuEnrollmentEndpointTest extends TestCase
{
    use DatabaseMigrations;

    private string $syncKey = 'TuSyncSecretKey2026!';

    private function makeStudent(array $overrides = []): Student
    {
        $nis  = $overrides['nis'] ?? '9900001';
        $user = User::create([
            'name'     => 'Test Student',
            'username' => $nis,
            'email'    => $nis . '@tu.test',
            'password' => bcrypt('pw'),
            'role'     => 'student',
        ]);
        return Student::create(array_merge([
            'user_id'     => $user->id,
            'nis'         => $nis,
            'status'      => 'active',
            'enrolled_at' => null,
        ], array_diff_key($overrides, ['name' => ''])));
    }

    private function postEnrollment(array $payload, ?string $key = null): \Illuminate\Testing\TestResponse
    {
        return $this->withHeaders([
            'X-TU-SYNC-KEY' => $key ?? $this->syncKey,
        ])->postJson('/api/internal/student-enrollment', $payload);
    }

    public function test_invalid_key_returns_401(): void
    {
        $this->postEnrollment([
            'nis'         => '9900001',
            'status'      => 'enrolled',
            'enrolled_at' => now()->subDay()->toIso8601String(),
        ], 'wrong-key')->assertStatus(401);
    }

    public function test_valid_enrollment_sets_enrolled_at(): void
    {
        $student    = $this->makeStudent(['nis' => '9900001']);
        $enrolledAt = now()->subDay()->toIso8601String();

        $this->postEnrollment([
            'nis'         => '9900001',
            'status'      => 'enrolled',
            'enrolled_at' => $enrolledAt,
        ])->assertStatus(200)->assertJsonPath('success', true);

        $this->assertNotNull($student->fresh()->enrolled_at);
        $this->assertTrue($student->fresh()->isEnrolled());
    }

    public function test_duplicate_same_date_is_idempotent(): void
    {
        $date    = now()->subDay()->toDateString();
        $student = $this->makeStudent(['nis' => '9900002', 'enrolled_at' => $date]);

        $this->postEnrollment([
            'nis'         => '9900002',
            'status'      => 'enrolled',
            'enrolled_at' => $date,
        ])->assertStatus(208)->assertJsonPath('idempotent', true);

        $this->assertEquals($date, $student->fresh()->enrolled_at->toDateString());
    }

    public function test_conflict_date_returns_409(): void
    {
        $student = $this->makeStudent([
            'nis'         => '9900003',
            'enrolled_at' => now()->subDays(10)->toDateString(),
        ]);

        $this->postEnrollment([
            'nis'         => '9900003',
            'status'      => 'enrolled',
            'enrolled_at' => now()->subDays(5)->toIso8601String(),
        ])->assertStatus(409);

        $this->assertEquals(
            now()->subDays(10)->toDateString(),
            $student->fresh()->enrolled_at->toDateString()
        );
    }

    public function test_unknown_nis_returns_404_no_student_created(): void
    {
        $before = Student::count();

        $this->postEnrollment([
            'nis'         => 'UNKNOWN_NIS_999',
            'status'      => 'enrolled',
            'enrolled_at' => now()->subDay()->toIso8601String(),
        ])->assertStatus(404);

        $this->assertEquals($before, Student::count());
    }

    public function test_invalid_status_rejected(): void
    {
        $this->makeStudent(['nis' => '9900004']);

        $this->postEnrollment([
            'nis'         => '9900004',
            'status'      => 'pending',
            'enrolled_at' => now()->subDay()->toIso8601String(),
        ])->assertStatus(422);
    }

    public function test_future_enrolled_at_rejected(): void
    {
        $this->makeStudent(['nis' => '9900005']);

        $this->postEnrollment([
            'nis'         => '9900005',
            'status'      => 'enrolled',
            'enrolled_at' => now()->addYear()->toIso8601String(),
        ])->assertStatus(422);
    }

    public function test_response_contains_no_credentials(): void
    {
        $this->makeStudent(['nis' => '9900006']);

        $response = $this->postEnrollment([
            'nis'         => '9900006',
            'status'      => 'enrolled',
            'enrolled_at' => now()->subDay()->toIso8601String(),
        ]);

        $body = $response->getContent();
        $this->assertStringNotContainsString('password',     $body);
        $this->assertStringNotContainsString('secret',       $body);
        $this->assertStringNotContainsString('app_password', $body);
    }

    public function test_audit_log_created_on_enrollment(): void
    {
        $student = $this->makeStudent(['nis' => '9900007']);

        $this->postEnrollment([
            'nis'         => '9900007',
            'status'      => 'enrolled',
            'enrolled_at' => now()->subDay()->toIso8601String(),
        ])->assertStatus(200);

        $this->assertDatabaseHas('audit_logs', [
            'action'      => 'UPDATE',
            'module'      => 'FINANCE_CONFIG',
            'target_type' => 'Student',
            'target_id'   => $student->id,
        ]);
    }

    public function test_no_bills_generated_by_enrollment(): void
    {
        $this->makeStudent(['nis' => '9900008']);

        $this->postEnrollment([
            'nis'         => '9900008',
            'status'      => 'enrolled',
            'enrolled_at' => now()->subDay()->toIso8601String(),
        ])->assertStatus(200);

        $this->assertEquals(0, \App\Models\StudentBill::count());
    }

    public function test_haidar_regression_nis_and_bills_unchanged(): void
    {
        $student = $this->makeStudent(['nis' => '26089715', 'nisn' => '2132154214']);

        \App\Models\StudentBill::create([
            'student_id' => $student->id,
            'type'       => 'SPP',
            'name'       => 'SPP Juli',
            'amount'     => 500000,
            'status'     => 'UNPAID',
        ]);

        $this->postEnrollment([
            'nis'         => '26089715',
            'status'      => 'enrolled',
            'enrolled_at' => now()->subDay()->toIso8601String(),
        ])->assertStatus(200);

        $fresh = $student->fresh();
        $this->assertEquals('26089715',   $fresh->nis);
        $this->assertEquals('2132154214', $fresh->nisn);
        $this->assertDatabaseHas('student_bills', [
            'student_id' => $student->id,
            'status'     => 'UNPAID',
            'amount'     => 500000,
        ]);
    }
}
