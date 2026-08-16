<?php

/**
 * Phase 3.2 — Role-based authorization tests.
 *
 * Covers:
 *   1.  admin can confirm payment (POST /bills/{id}/pay)
 *   2.  tu can confirm payment
 *   3.  kepala_sekolah gets 403 on payment confirmation
 *   4.  kepala_sekolah cannot delete a bill
 *   5.  kepala_sekolah cannot create a bill
 *   6.  kepala_sekolah can read bills index (allowed)
 *   7.  kepala_sekolah cannot modify school settings
 *   8.  admin can modify school settings
 *   9.  kepala_sekolah cannot delete a student
 *  10.  tu can delete a calon_siswa without bills
 *  11.  EnsureRole middleware returns JSON 403 for API-style requests
 *  12.  User model role helpers return correct values
 */

use App\Models\Student;
use App\Models\StudentBill;
use App\Models\User;

// ── Helpers ────────────────────────────────────────────────────────────────

function makeUser(string $role): User
{
    return User::factory()->create(['role' => $role]);
}

// ── 1. admin can confirm payment ─────────────────────────────────────────────
test('admin can confirm cash payment', function () {
    $admin   = makeUser('admin');
    $student = Student::factory()->create();
    $bill    = StudentBill::factory()->create([
        'student_id' => $student->id,
        'status'     => 'UNPAID',
        'amount'     => 100000,
    ]);

    $this->actingAs($admin)
         ->post("/bills/{$bill->id}/pay")
         ->assertRedirect();

    expect(StudentBill::find($bill->id)->status)->toBe('PAID');
});

// ── 2. tu can confirm payment ────────────────────────────────────────────────
test('tu can confirm cash payment', function () {
    $tu      = makeUser('tu');
    $student = Student::factory()->create();
    $bill    = StudentBill::factory()->create([
        'student_id' => $student->id,
        'status'     => 'UNPAID',
        'amount'     => 100000,
    ]);

    $this->actingAs($tu)
         ->post("/bills/{$bill->id}/pay")
         ->assertRedirect();

    expect(StudentBill::find($bill->id)->status)->toBe('PAID');
    expect((int) StudentBill::find($bill->id)->confirmed_by)->toBe((int) $tu->id);
});

// ── 3. kepala_sekolah gets 403 on payment confirmation ───────────────────────
test('kepala_sekolah cannot confirm payment and gets 403', function () {
    $ks      = makeUser('kepala_sekolah');
    $student = Student::factory()->create();
    $bill    = StudentBill::factory()->create([
        'student_id' => $student->id,
        'status'     => 'UNPAID',
        'amount'     => 100000,
    ]);

    $this->actingAs($ks)
         ->post("/bills/{$bill->id}/pay")
         ->assertStatus(403);

    // Bill must remain UNPAID.
    expect(StudentBill::find($bill->id)->status)->toBe('UNPAID');
});

// ── 4. kepala_sekolah cannot delete a bill ───────────────────────────────────
test('kepala_sekolah cannot delete a bill and gets 403', function () {
    $ks      = makeUser('kepala_sekolah');
    $student = Student::factory()->create();
    $bill    = StudentBill::factory()->create([
        'student_id' => $student->id,
        'status'     => 'UNPAID',
    ]);

    $this->actingAs($ks)
         ->delete("/bills/{$bill->id}")
         ->assertStatus(403);

    $this->assertDatabaseHas('student_bills', ['id' => $bill->id]);
});

// ── 5. kepala_sekolah cannot create a bill ───────────────────────────────────
test('kepala_sekolah cannot access bill creation and gets 403', function () {
    $ks = makeUser('kepala_sekolah');

    $this->actingAs($ks)
         ->get('/bills/create')
         ->assertStatus(403);
});

// ── 6. kepala_sekolah can read bills index ───────────────────────────────────
test('kepala_sekolah can read bills index', function () {
    $ks = makeUser('kepala_sekolah');

    $this->actingAs($ks)
         ->get('/bills')
         ->assertStatus(200);
});

// ── 7. kepala_sekolah cannot modify school settings ──────────────────────────
test('kepala_sekolah cannot modify school settings and gets 403', function () {
    $ks = makeUser('kepala_sekolah');

    $this->actingAs($ks)
         ->post('/school-settings', ['school_name' => 'Hack School'])
         ->assertStatus(403);
});

// ── 8. admin can access school settings ──────────────────────────────────────
test('admin can access school settings page', function () {
    $admin = makeUser('admin');

    $this->actingAs($admin)
         ->get('/school-settings')
         ->assertStatus(200);
});

// ── 9. kepala_sekolah cannot delete a student ────────────────────────────────
test('kepala_sekolah cannot delete a student and gets 403', function () {
    $ks      = makeUser('kepala_sekolah');
    $student = Student::factory()->calon()->create();

    $this->actingAs($ks)
         ->delete("/students/{$student->id}")
         ->assertStatus(403);

    $this->assertDatabaseHas('students', ['id' => $student->id, 'deleted_at' => null]);
});

// ── 10. tu can delete calon_siswa without bills ──────────────────────────────
test('tu can soft-delete calon_siswa without bills', function () {
    $tu      = makeUser('tu');
    $student = Student::factory()->calon()->create();

    $this->actingAs($tu)
         ->delete("/students/{$student->id}")
         ->assertRedirect(route('students.index'));

    $this->assertSoftDeleted('students', ['id' => $student->id]);
});

// ── 11. EnsureRole returns JSON 403 for JSON requests ────────────────────────
test('role middleware returns JSON 403 for api-style requests from wrong role', function () {
    $ks      = makeUser('kepala_sekolah');
    $student = Student::factory()->create();
    $bill    = StudentBill::factory()->create([
        'student_id' => $student->id,
        'status'     => 'UNPAID',
        'amount'     => 100000,
    ]);

    $this->actingAs($ks)
         ->postJson("/bills/{$bill->id}/pay")
         ->assertStatus(403)
         ->assertJson(['message' => 'Akses ditolak.']);
});

// ── 12. User model role helpers ───────────────────────────────────────────────
test('User model role helpers return correct values', function () {
    $admin = makeUser('admin');
    $tu    = makeUser('tu');
    $ks    = makeUser('kepala_sekolah');

    // isAdmin
    expect($admin->isAdmin())->toBeTrue();
    expect($tu->isAdmin())->toBeFalse();
    expect($ks->isAdmin())->toBeFalse();

    // isTu
    expect($tu->isTu())->toBeTrue();
    expect($admin->isTu())->toBeFalse();
    expect($ks->isTu())->toBeFalse();

    // isKepalaSekolah
    expect($ks->isKepalaSekolah())->toBeTrue();
    expect($admin->isKepalaSekolah())->toBeFalse();
    expect($tu->isKepalaSekolah())->toBeFalse();

    // canManageFinance
    expect($admin->canManageFinance())->toBeTrue();
    expect($tu->canManageFinance())->toBeTrue();
    expect($ks->canManageFinance())->toBeFalse();

    // hasRole
    expect($admin->hasRole('admin'))->toBeTrue();
    expect($admin->hasRole('tu'))->toBeFalse();
});
