<?php

/**
 * Phase 2.2 — confirmed_by schema verification tests.
 *
 * Verifies:
 * 1. confirmed_by column exists and is nullable
 * 2. FK references users.id
 * 3. ON DELETE SET NULL — deleting a user nullifies confirmed_by on bills
 * 4. Existing bill records are unaffected (confirmed_by = NULL)
 * 5. confirmed_by can be set and read correctly
 */

use App\Models\StudentBill;
use App\Models\User;
use App\Models\Student;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

// ── 1. Column exists and is nullable ────────────────────────────────────────
test('student_bills has confirmed_by column and it is nullable', function () {
    expect(Schema::hasColumn('student_bills', 'confirmed_by'))->toBeTrue();

    // Create a bill without confirmed_by — must not throw.
    $bill = StudentBill::factory()->create();

    expect($bill->confirmed_by)->toBeNull();
});

// ── 2. confirmed_by can be set to a valid user id ────────────────────────────
test('confirmed_by can be set to an existing user id', function () {
    $user = User::factory()->create();
    $bill = StudentBill::factory()->create([
        'confirmed_by' => $user->id,
    ]);

    $bill->refresh();
    expect((int) $bill->confirmed_by)->toBe((int) $user->id);
});

// ── 3. ON DELETE SET NULL — deleting the confirming user nullifies the FK ────
test('deleting the confirming user sets confirmed_by to NULL on related bills', function () {
    $user = User::factory()->create();
    $bill = StudentBill::factory()->create([
        'confirmed_by' => $user->id,
    ]);

    // Confirm the FK is set before deletion.
    expect((int) $bill->confirmed_by)->toBe((int) $user->id);

    // Delete the user — ON DELETE SET NULL must fire.
    $user->delete();

    $bill->refresh();
    expect($bill->confirmed_by)->toBeNull();
});

// ── 4. Existing records are unaffected ───────────────────────────────────────
test('existing student_bills records have confirmed_by as NULL', function () {
    // Create several bills without confirmed_by.
    StudentBill::factory()->count(5)->create();

    $nullCount = StudentBill::whereNull('confirmed_by')->count();
    $totalCount = StudentBill::count();

    expect($nullCount)->toBe($totalCount);
});

// ── 5. Multiple bills confirmed by same user — all nullified on user delete ──
test('all bills confirmed by a deleted user are set to NULL', function () {
    $user = User::factory()->create();

    StudentBill::factory()->count(3)->create([
        'confirmed_by' => $user->id,
    ]);

    $user->delete();

    $remaining = StudentBill::where('confirmed_by', $user->id)->count();
    expect($remaining)->toBe(0);

    $nullified = StudentBill::whereNull('confirmed_by')->count();
    expect($nullified)->toBe(3);
});
