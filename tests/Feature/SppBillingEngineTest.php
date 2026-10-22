<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Student;
use App\Models\StudentBill;
use App\Models\FinanceBillingType;
use App\Models\FinanceSppRule;
use App\Services\SppBillingEngine;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Queue;

/**
 * SPP Billing Engine Test Suite
 *
 * Case  1: Enrolled + offset 0 → bill enrollment month
 * Case  2: Enrolled + offset 1 → skip enrollment month → bill next month
 * Case  3: Existing student → normal SPP billing
 * Case  4: enrolled_at NULL → excluded from new-student offset logic, still billed
 * Case  5: Already billed → skip
 * Case  6: Two matching rules → conflict
 * Case  7: No matching rule → conflict
 * Case  8: Amount from rule
 * Case  9: Due date from rule
 * Case 10: Generate same month twice → no duplicate
 * Case 11: 1 valid + 1 conflict + 1 valid → all valid still generated
 * Case 12: Preview → zero DB writes
 * Case 13: Unauthorized → 403
 * Case 14: 1000+ students → chunked (no timeout assertion, just functional correctness)
 */
class SppBillingEngineTest extends TestCase
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

    private function makeStudent(string $nis, ?string $enrolledAt = null, string $className = 'X-A'): Student
    {
        $user = User::create([
            'name' => "Student $nis", 'username' => $nis,
            'email' => "$nis@test.id", 'password' => bcrypt('pw'), 'role' => 'student',
        ]);
        return Student::create([
            'user_id'     => $user->id,
            'nis'         => $nis,
            'status'      => 'active',
            'class_name'  => $className,
            'enrolled_at' => $enrolledAt,
        ]);
    }

    private function makeSppRule(array $overrides = []): FinanceSppRule
    {
        if (!FinanceBillingType::where('code', 'SPP')->exists()) {
            FinanceBillingType::create([
                'code' => 'SPP', 'name' => 'SPP Bulanan',
                'is_recurring' => true, 'sort_order' => 1, 'is_active' => true,
            ]);
        }
        return FinanceSppRule::create(array_merge([
            'name'                   => 'SPP TA 2026/2027',
            'billing_type_code'      => 'SPP',
            'academic_year'          => '2026/2027',
            'start_month'            => 7,
            'start_year'             => 2026,
            'amount'                 => 500000,
            'due_day'                => 10,
            'class_scope'            => null,
            'new_student_start_offset' => null,
            'is_active'              => true,
        ], $overrides));
    }

    private function engine(): SppBillingEngine
    {
        return app(SppBillingEngine::class);
    }

    // ================================================================
    // CASE 1: offset 0 → bill enrollment month
    // ================================================================

    public function test_enrolled_student_offset_0_billed_in_enrollment_month(): void
    {
        $this->makeSppRule(['new_student_start_offset' => 0]);
        $student = $this->makeStudent('9900001', '2026-08-15'); // enrolled August

        $result = $this->engine()->generate(8, 2026, 1);

        $this->assertEquals(1, $result['generated']);
        $this->assertDatabaseHas('student_bills', [
            'student_id' => $student->id,
            'type'       => 'SPP',
            'bill_month' => 8,
            'bill_year'  => 2026,
            'status'     => 'UNPAID',
        ]);
    }

    // ================================================================
    // CASE 2: offset 1 → skip enrollment month → bill next month
    // ================================================================

    public function test_enrolled_student_offset_1_skips_enrollment_month(): void
    {
        $this->makeSppRule(['new_student_start_offset' => 1]);
        $student = $this->makeStudent('9900002', '2026-08-15'); // enrolled August

        // August → should be EXCLUDED (offset 1 means first bill = September)
        $resultAug = $this->engine()->generate(8, 2026, 1);
        $this->assertEquals(0, $resultAug['generated']);
        $this->assertEquals(1, $resultAug['excluded']);
        $this->assertDatabaseMissing('student_bills', ['student_id' => $student->id, 'bill_month' => 8]);

        // September → should be GENERATED
        $resultSep = $this->engine()->generate(9, 2026, 1);
        $this->assertEquals(1, $resultSep['generated']);
        $this->assertDatabaseHas('student_bills', [
            'student_id' => $student->id,
            'bill_month' => 9,
            'bill_year'  => 2026,
        ]);
    }

    // ================================================================
    // CASE 3: Existing student → normal billing (no offset restriction)
    // ================================================================

    public function test_existing_student_without_offset_billed_normally(): void
    {
        $this->makeSppRule(); // no offset
        $student = $this->makeStudent('9900003', '2025-07-01'); // enrolled last year

        $result = $this->engine()->generate(8, 2026, 1);

        $this->assertEquals(1, $result['generated']);
        $this->assertDatabaseHas('student_bills', [
            'student_id' => $student->id,
            'bill_month' => 8,
            'bill_year'  => 2026,
        ]);
    }

    // ================================================================
    // CASE 4: enrolled_at NULL → no offset logic, still billed normally
    // ================================================================

    public function test_student_without_enrolled_at_is_billed_normally(): void
    {
        $this->makeSppRule(['new_student_start_offset' => 1]);
        $student = $this->makeStudent('9900004', null); // no enrolled_at

        // Without enrolled_at, offset logic is skipped — student billed normally
        $result = $this->engine()->generate(8, 2026, 1);

        $this->assertEquals(1, $result['generated']);
        $this->assertDatabaseHas('student_bills', [
            'student_id' => $student->id,
            'bill_month' => 8,
        ]);
    }

    // ================================================================
    // CASE 5: Already billed → skip
    // ================================================================

    public function test_already_billed_student_is_skipped(): void
    {
        $this->makeSppRule();
        $student = $this->makeStudent('9900005', '2026-07-01');

        // Create existing bill
        StudentBill::create([
            'student_id' => $student->id,
            'type'       => 'SPP',
            'name'       => 'SPP Agustus 2026',
            'amount'     => 500000,
            'status'     => 'UNPAID',
            'paid_amount'=> 0,
            'bill_month' => 8,
            'bill_year'  => 2026,
            'due_date'   => '2026-08-10',
        ]);

        $result = $this->engine()->generate(8, 2026, 1);

        $this->assertEquals(0, $result['generated']);
        $this->assertEquals(1, $result['already_billed']);
        $this->assertEquals(1, StudentBill::where('student_id', $student->id)->count());
    }

    // ================================================================
    // CASE 6: Two matching rules → conflict
    // ================================================================

    public function test_two_matching_rules_causes_conflict(): void
    {
        $this->makeSppRule(['name' => 'Rule A']);
        $this->makeSppRule(['name' => 'Rule B']); // both global scope
        $this->makeStudent('9900006', '2026-07-01');

        $result = $this->engine()->generate(8, 2026, 1);

        $this->assertEquals(1, $result['conflicts']);
        $this->assertEquals(0, $result['generated']);
        $this->assertEquals(0, StudentBill::count());
    }

    // ================================================================
    // CASE 7: No matching rule → conflict
    // ================================================================

    public function test_no_matching_rule_causes_conflict(): void
    {
        // No active SPP rule created
        $this->makeStudent('9900007', '2026-07-01');

        $result = $this->engine()->generate(8, 2026, 1);

        $this->assertEquals(0, $result['generated']);
        $this->assertEquals(0, StudentBill::count());
    }

    // ================================================================
    // CASE 8: Amount comes from rule
    // ================================================================

    public function test_amount_comes_from_rule(): void
    {
        $this->makeSppRule(['amount' => 750000]);
        $student = $this->makeStudent('9900008', '2026-07-01');

        $this->engine()->generate(8, 2026, 1);

        $this->assertDatabaseHas('student_bills', [
            'student_id' => $student->id,
            'amount'     => '750000.00',
        ]);
    }

    // ================================================================
    // CASE 9: Due date comes from rule.due_day
    // ================================================================

    public function test_due_date_comes_from_rule_due_day(): void
    {
        $this->makeSppRule(['due_day' => 15]);
        $student = $this->makeStudent('9900009', '2026-07-01');

        $this->engine()->generate(8, 2026, 1);

        $this->assertDatabaseHas('student_bills', [
            'student_id' => $student->id,
            'due_date'   => '2026-08-15 00:00:00',
        ]);
    }

    // ================================================================
    // CASE 10: Generate same month twice → no duplicate
    // ================================================================

    public function test_generate_same_month_twice_no_duplicate(): void
    {
        $this->makeSppRule();
        $student = $this->makeStudent('9900010', '2026-07-01');

        $result1 = $this->engine()->generate(8, 2026, 1);
        $result2 = $this->engine()->generate(8, 2026, 1);

        $this->assertEquals(1, $result1['generated']);
        $this->assertEquals(0, $result2['generated']);
        $this->assertEquals(1, $result2['already_billed']);
        $this->assertEquals(1, StudentBill::where('student_id', $student->id)->count());
    }

    // ================================================================
    // CASE 11: 1 valid + 1 conflict + 1 valid → valids still generated
    // ================================================================

    public function test_failure_isolation_one_conflict_does_not_block_others(): void
    {
        // Rule A + Rule B → conflict for student with null class_scope match
        $this->makeSppRule(['name' => 'Rule A']); // global
        $this->makeSppRule(['name' => 'Rule B']); // global → conflict for all

        // Student with specific class scope rule
        $this->makeSppRule([
            'name'        => 'Rule Specific',
            'class_scope' => 'X-A',
        ]);

        $studentA = $this->makeStudent('9900011a', '2026-07-01', 'X-A'); // specific rule → generate
        $studentB = $this->makeStudent('9900011b', '2026-07-01', 'X-B'); // no specific, two global → conflict
        $studentC = $this->makeStudent('9900011c', '2026-07-01', 'X-A'); // specific rule → generate

        $result = $this->engine()->generate(8, 2026, 1);

        $this->assertEquals(2, $result['generated']);
        $this->assertEquals(1, $result['conflicts']);
        $this->assertDatabaseHas('student_bills', ['student_id' => $studentA->id]);
        $this->assertDatabaseHas('student_bills', ['student_id' => $studentC->id]);
        $this->assertDatabaseMissing('student_bills', ['student_id' => $studentB->id]);
    }

    // ================================================================
    // CASE 12: Preview → zero DB writes
    // ================================================================

    public function test_preview_writes_nothing_to_database(): void
    {
        $this->makeSppRule();
        $this->makeStudent('9900012', '2026-07-01');

        $initialCount = StudentBill::count();

        $result = $this->engine()->preview(8, 2026);

        $this->assertEquals($initialCount, StudentBill::count());
        $this->assertArrayHasKey('summary', $result);
        $this->assertArrayHasKey('rows', $result);
        $this->assertEquals(1, $result['summary']['new_bills']);
    }

    // ================================================================
    // CASE 13: Unauthorized (non-admin) → 403
    // ================================================================

    public function test_non_admin_cannot_access_generate(): void
    {
        $staff = $this->makeTuStaff();

        $this->actingAs($staff)
             ->get('/finance/spp/generate')
             ->assertStatus(403);

        $this->actingAs($staff)
             ->post('/finance/spp/generate/preview', [
                 'billing_month' => 8,
                 'billing_year'  => 2026,
             ])
             ->assertStatus(403);

        $this->actingAs($staff)
             ->post('/finance/spp/generate/confirm', [
                 'billing_month' => 8,
                 'billing_year'  => 2026,
                 'confirmation'  => 'KONFIRMASI',
             ])
             ->assertStatus(403);
    }

    // ================================================================
    // CASE 14: 1000+ students → chunked, no timeout
    // ================================================================

    public function test_large_dataset_processed_in_chunks(): void
    {
        $this->makeSppRule();

        // Create 50 students (representative for chunking test — full 1000 too slow for unit test)
        for ($i = 1; $i <= 50; $i++) {
            $this->makeStudent('990' . str_pad($i, 4, '0', STR_PAD_LEFT), '2026-07-01');
        }

        $result = $this->engine()->generate(8, 2026, 1);

        $this->assertEquals(50, $result['generated']);
        $this->assertEquals(50, StudentBill::where('type', 'SPP')->where('bill_month', 8)->count());
    }

    // ================================================================
    // ADMIN CAN ACCESS GENERATE PAGE
    // ================================================================

    public function test_admin_can_access_generate_page(): void
    {
        $admin = $this->makeAdmin();

        $this->actingAs($admin)
             ->get('/finance/spp/generate')
             ->assertStatus(200);
    }

    // ================================================================
    // ADMIN PREVIEW RETURNS CORRECT SUMMARY
    // ================================================================

    public function test_admin_preview_returns_summary(): void
    {
        $admin = $this->makeAdmin();
        $this->makeSppRule();
        $this->makeStudent('9900099', '2026-07-01');

        $this->actingAs($admin)
             ->post('/finance/spp/generate/preview', [
                 'billing_month' => 8,
                 'billing_year'  => 2026,
             ])
             ->assertStatus(200)
             ->assertSee('Preview');
    }

    // ================================================================
    // EXISTING BILLS NOT MODIFIED
    // ================================================================

    public function test_existing_paid_bills_not_modified(): void
    {
        $this->makeSppRule();
        $student = $this->makeStudent('9900098', '2026-07-01');

        // Existing PAID bill for different month
        $bill = StudentBill::create([
            'student_id'  => $student->id,
            'type'        => 'SPP',
            'name'        => 'SPP Juli 2026',
            'amount'      => 500000,
            'status'      => 'PAID',
            'paid_amount' => 500000,
            'bill_month'  => 7,
            'bill_year'   => 2026,
            'due_date'    => '2026-07-10',
        ]);

        $this->engine()->generate(8, 2026, 1);

        // July bill untouched
        $this->assertDatabaseHas('student_bills', [
            'id'     => $bill->id,
            'status' => 'PAID',
            'amount' => '500000.00',
        ]);
    }

    // ================================================================
    // CONFIRM ROUTE REQUIRES CONFIRMATION WORD
    // ================================================================

    public function test_confirm_rejects_wrong_confirmation_word(): void
    {
        $admin = $this->makeAdmin();
        $this->makeSppRule();

        $this->actingAs($admin)
             ->post('/finance/spp/generate/confirm', [
                 'billing_month' => 8,
                 'billing_year'  => 2026,
                 'confirmation'  => 'YES', // wrong
             ])
             ->assertSessionHasErrors('confirmation');
    }
}
