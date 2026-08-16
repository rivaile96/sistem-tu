<?php

/**
 * Phase 1.6-C — Student Receipt IDOR Regression Tests
 *
 * Verifies PaymentSiswaController::struk() ownership enforcement:
 *   1. Student A → own PAID bill          → 200
 *   2. Student A → Student B's PAID bill  → 404
 *   3. Student A → non-existent bill ID   → 404
 *   4. Unauthenticated request            → redirect to siswa login
 */

use App\Models\Student;
use App\Models\StudentBill;
use Illuminate\Support\Facades\Auth;

// ── 1. Own paid bill — success ───────────────────────────────────────────────
test('authenticated student can view their own paid receipt', function () {
    $student = Student::factory()->create();
    $bill    = StudentBill::factory()->paid()->create([
        'student_id' => $student->id,
    ]);

    $response = $this->actingAs($student, 'siswa')
                     ->get("/siswa/tagihan/{$bill->id}/struk");

    $response->assertStatus(200);
});

// ── 2. Another student's paid bill — 404 ────────────────────────────────────
test('authenticated student cannot view another student paid receipt — returns 404', function () {
    $studentA = Student::factory()->create();
    $studentB = Student::factory()->create();

    // Bill belongs to Student B.
    $billB = StudentBill::factory()->paid()->create([
        'student_id' => $studentB->id,
    ]);

    // Student A tries to access Student B's receipt.
    $response = $this->actingAs($studentA, 'siswa')
                     ->get("/siswa/tagihan/{$billB->id}/struk");

    // Must be 404 — not 403, not 200.
    // 404 does not confirm to the attacker whether the bill exists at all.
    $response->assertStatus(404);
});

// ── 3. Non-existent bill ID — 404 ────────────────────────────────────────────
test('authenticated student requesting non-existent bill ID gets 404', function () {
    $student = Student::factory()->create();

    $response = $this->actingAs($student, 'siswa')
                     ->get('/siswa/tagihan/999999/struk');

    $response->assertStatus(404);
});

// ── 4. Unauthenticated request — redirected to login ────────────────────────
test('unauthenticated request to receipt endpoint redirects to siswa login', function () {
    $student = Student::factory()->create();
    $bill    = StudentBill::factory()->paid()->create([
        'student_id' => $student->id,
    ]);

    $response = $this->get("/siswa/tagihan/{$bill->id}/struk");

    // AuthSiswa middleware redirects unauthenticated requests.
    $response->assertRedirect(route('siswa.login'));
});

// ── 5. Own bill but UNPAID — redirected to dashboard ────────────────────────
test('student accessing own UNPAID bill receipt is redirected to dashboard', function () {
    $student = Student::factory()->create();
    $bill    = StudentBill::factory()->create([
        'student_id' => $student->id,
        'status'     => 'UNPAID',
    ]);

    $response = $this->actingAs($student, 'siswa')
                     ->get("/siswa/tagihan/{$bill->id}/struk");

    $response->assertRedirect(route('siswa.dashboard'));
});

// ── 6. Bill ID enumeration: sequential IDs across students ───────────────────
test('student cannot enumerate bill IDs belonging to other students', function () {
    $studentA = Student::factory()->create();
    $studentB = Student::factory()->create();

    // Create 5 bills for Student B with sequential IDs.
    $bills = StudentBill::factory()->paid()->count(5)->create([
        'student_id' => $studentB->id,
    ]);

    // Student A tries each of Student B's bill IDs — all must return 404.
    foreach ($bills as $bill) {
        $response = $this->actingAs($studentA, 'siswa')
                         ->get("/siswa/tagihan/{$bill->id}/struk");

        expect($response->status())->toBe(404,
            "Bill ID {$bill->id} belonging to Student B should return 404 for Student A."
        );
    }
});
