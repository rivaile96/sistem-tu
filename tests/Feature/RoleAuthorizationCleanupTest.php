<?php

/**
 * Phase 11 — Role & User Management Cleanup Tests
 *
 * Covers:
 *  1.  admin can access all operational routes
 *  2.  tu can access operational routes
 *  3.  staf can access limited operational routes
 *  4.  staf cannot delete a student
 *  5.  staf cannot access school settings
 *  6.  staf cannot create a bill
 *  7.  staf cannot pay a bill
 *  8.  kepala_sekolah can read dashboard
 *  9.  kepala_sekolah can read bills index
 *  10. kepala_sekolah can read pos history
 *  11. kepala_sekolah cannot create a bill
 *  12. kepala_sekolah cannot pay a bill
 *  13. kepala_sekolah cannot delete a student
 *  14. kepala_sekolah cannot access school settings
 *  15. kepala_sekolah cannot access pos transaction (kasir)
 *  16. staf can create a student
 *  17. staf can access pos transaction (kasir)
 *  18. all seeder accounts can log in
 *  19. isStaf() helper returns correct value
 *  20. canManageFinance() includes staf
 */

use App\Models\Student;
use App\Models\StudentBill;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

// ── Helpers ───────────────────────────────────────────────────────────────────

function makeRole(string $role): User
{
    return User::factory()->create([
        'role'              => $role,
        'email_verified_at' => now(),
    ]);
}

function makeUnpaidBill(): StudentBill
{
    $student = Student::factory()->create();
    return StudentBill::factory()->create([
        'student_id' => $student->id,
        'status'     => 'UNPAID',
        'amount'     => 100000,
    ]);
}

// ── 1. admin full access ──────────────────────────────────────────────────────
test('admin can access students index', function () {
    $admin = makeRole('admin');
    $this->actingAs($admin)->get('/students')->assertOk();
});

test('admin can access bills index', function () {
    $admin = makeRole('admin');
    $this->actingAs($admin)->get('/bills')->assertOk();
});

test('admin can access school settings', function () {
    $admin = makeRole('admin');
    $this->actingAs($admin)->get('/school-settings')->assertOk();
});

// ── 2. tu operational access ──────────────────────────────────────────────────
test('tu can access students index', function () {
    $tu = makeRole('tu');
    $this->actingAs($tu)->get('/students')->assertOk();
});

test('tu can access bills index', function () {
    $tu = makeRole('tu');
    $this->actingAs($tu)->get('/bills')->assertOk();
});

test('tu can pay a bill', function () {
    $tu   = makeRole('tu');
    $bill = makeUnpaidBill();
    $this->actingAs($tu)->post("/bills/{$bill->id}/pay")->assertRedirect();
    expect(StudentBill::find($bill->id)->status)->toBe('PAID');
});

// ── 3 & 4. staf limited access ────────────────────────────────────────────────
test('staf can access students index', function () {
    $staf = makeRole('staf');
    $this->actingAs($staf)->get('/students')->assertOk();
});

test('staf can create a student', function () {
    $staf = makeRole('staf');
    $this->actingAs($staf)->get('/students/create')->assertOk();
});

test('staf can access pos transaction (kasir)', function () {
    $staf = makeRole('staf');
    $this->actingAs($staf)->get('/pos/transaction')->assertOk();
});

test('staf cannot delete a student and gets 403', function () {
    $staf    = makeRole('staf');
    $student = Student::factory()->create(['status' => 'calon_siswa']);
    $this->actingAs($staf)->delete("/students/{$student->id}")->assertStatus(403);
    expect(Student::find($student->id))->not->toBeNull();
});

test('staf cannot access school settings and gets 403', function () {
    $staf = makeRole('staf');
    $this->actingAs($staf)->get('/school-settings')->assertStatus(403);
});

test('staf cannot create a bill and gets 403', function () {
    $staf = makeRole('staf');
    $this->actingAs($staf)->get('/bills/create')->assertStatus(403);
});

test('staf cannot pay a bill and gets 403', function () {
    $staf = makeRole('staf');
    $bill = makeUnpaidBill();
    $this->actingAs($staf)->post("/bills/{$bill->id}/pay")->assertStatus(403);
    expect(StudentBill::find($bill->id)->status)->toBe('UNPAID');
});

test('staf cannot access master barang and gets 403', function () {
    $staf = makeRole('staf');
    $this->actingAs($staf)->get('/pos/items')->assertStatus(403);
});

// ── 8–15. kepala_sekolah read-only ────────────────────────────────────────────
test('kepala_sekolah can read dashboard', function () {
    $ks = makeRole('kepala_sekolah');
    $this->actingAs($ks)->get('/dashboard')->assertOk();
});

test('kepala_sekolah can read bills index', function () {
    $ks = makeRole('kepala_sekolah');
    $this->actingAs($ks)->get('/bills')->assertOk();
});

test('kepala_sekolah can read pos history', function () {
    $ks = makeRole('kepala_sekolah');
    $this->actingAs($ks)->get('/pos/history')->assertOk();
});

test('kepala_sekolah cannot create a bill and gets 403', function () {
    $ks = makeRole('kepala_sekolah');
    $this->actingAs($ks)->get('/bills/create')->assertStatus(403);
});

test('kepala_sekolah cannot pay a bill and gets 403', function () {
    $ks   = makeRole('kepala_sekolah');
    $bill = makeUnpaidBill();
    $this->actingAs($ks)->post("/bills/{$bill->id}/pay")->assertStatus(403);
    expect(StudentBill::find($bill->id)->status)->toBe('UNPAID');
});

test('kepala_sekolah cannot delete a student and gets 403', function () {
    $ks      = makeRole('kepala_sekolah');
    $student = Student::factory()->create(['status' => 'calon_siswa']);
    $this->actingAs($ks)->delete("/students/{$student->id}")->assertStatus(403);
    expect(Student::find($student->id))->not->toBeNull();
});

test('kepala_sekolah cannot access school settings and gets 403', function () {
    $ks = makeRole('kepala_sekolah');
    $this->actingAs($ks)->get('/school-settings')->assertStatus(403);
});

test('kepala_sekolah cannot access kasir/transaksi and gets 403', function () {
    $ks = makeRole('kepala_sekolah');
    $this->actingAs($ks)->get('/pos/transaction')->assertStatus(403);
});

// ── 18. seeder accounts login ─────────────────────────────────────────────────
test('seeder admin account can log in', function () {
    User::firstOrCreate(
        ['email' => 'admin@sekolah.com'],
        ['name' => 'Admin TU', 'password' => Hash::make('password'), 'role' => 'admin', 'email_verified_at' => now()]
    );
    $this->post('/login', ['email' => 'admin@sekolah.com', 'password' => 'password'])
         ->assertRedirect();
    $this->assertAuthenticated();
});

test('seeder tu account can log in', function () {
    User::firstOrCreate(
        ['email' => 'tu@sekolah.com'],
        ['name' => 'Staff TU', 'password' => Hash::make('password'), 'role' => 'tu', 'email_verified_at' => now()]
    );
    $this->post('/login', ['email' => 'tu@sekolah.com', 'password' => 'password'])
         ->assertRedirect();
    $this->assertAuthenticated();
});

test('seeder staf account can log in', function () {
    User::firstOrCreate(
        ['email' => 'staf@sekolah.com'],
        ['name' => 'Staff Sekolah', 'password' => Hash::make('password'), 'role' => 'staf', 'email_verified_at' => now()]
    );
    $this->post('/login', ['email' => 'staf@sekolah.com', 'password' => 'password'])
         ->assertRedirect();
    $this->assertAuthenticated();
});

test('seeder kepsek account can log in', function () {
    User::firstOrCreate(
        ['email' => 'kepsek@sekolah.com'],
        ['name' => 'Kepala Sekolah', 'password' => Hash::make('password'), 'role' => 'kepala_sekolah', 'email_verified_at' => now()]
    );
    $this->post('/login', ['email' => 'kepsek@sekolah.com', 'password' => 'password'])
         ->assertRedirect();
    $this->assertAuthenticated();
});

// ── 19 & 20. User model helpers ───────────────────────────────────────────────
test('isStaf() returns correct value', function () {
    $staf  = makeRole('staf');
    $admin = makeRole('admin');
    $tu    = makeRole('tu');
    $ks    = makeRole('kepala_sekolah');

    expect($staf->isStaf())->toBeTrue();
    expect($admin->isStaf())->toBeFalse();
    expect($tu->isStaf())->toBeFalse();
    expect($ks->isStaf())->toBeFalse();
});

test('canManageFinance() includes staf', function () {
    $staf  = makeRole('staf');
    $admin = makeRole('admin');
    $tu    = makeRole('tu');
    $ks    = makeRole('kepala_sekolah');

    expect($staf->canManageFinance())->toBeTrue();
    expect($admin->canManageFinance())->toBeTrue();
    expect($tu->canManageFinance())->toBeTrue();
    expect($ks->canManageFinance())->toBeFalse();
});
