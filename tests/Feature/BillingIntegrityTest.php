<?php

/**
 * Phase 3.3 — Billing integrity tests.
 *
 * Covers:
 *   1.  Cannot create bill with zero SPP amount
 *   2.  Cannot create bill with negative SPP amount
 *   3.  Cannot create non-SPP bill with zero item price
 *   4.  Cannot create non-SPP bill with negative item price
 *   5.  Cannot create non-SPP bill with zero item quantity
 *   6.  Cannot create bill with empty item array
 *   7.  Cannot create bill with invalid type
 *   8.  Cannot create duplicate SPP bill (same student/month/year)
 *   9.  PAID bill cannot have amount changed (model guard)
 *  10.  PAID bill cannot have student_id changed (model guard)
 *  11.  PAID bill cannot have type changed (model guard)
 *  12.  UNPAID bill can still be edited freely
 *  13.  Midtrans callback on PAID bill (duplicate webhook) does not throw
 *  14.  Valid bill creation succeeds
 */

use App\Models\Student;
use App\Models\StudentBill;
use App\Models\User;

// ── Helpers ───────────────────────────────────────────────────────────────────

function tuUser(): User
{
    return User::factory()->create(['role' => 'tu']);
}

function activeStudent(): Student
{
    return Student::factory()->create(['status' => 'active']);
}

/**
 * Build a minimal valid non-SPP bill POST payload for a single student.
 */
function validBillPayload(int $studentId, array $overrides = []): array
{
    return array_merge([
        'target_type'  => 'student',
        'student_id'   => $studentId,
        'type'         => 'LAINNYA',
        'name'         => 'Uang Gedung',
        'item_names'   => ['Uang Gedung'],
        'item_prices'  => ['500000'],
        'item_qtys'    => ['1'],
    ], $overrides);
}

/**
 * Build a minimal valid SPP bill POST payload for a single student.
 */
function validSppPayload(int $studentId, array $overrides = []): array
{
    return array_merge([
        'target_type' => 'student',
        'student_id'  => $studentId,
        'type'        => 'SPP',
        'spp_month'   => 3,
        'spp_year'    => 2026,
        'spp_amount'  => 200000,
    ], $overrides);
}

// ── 1. Zero SPP amount rejected ───────────────────────────────────────────────
test('cannot create SPP bill with zero amount', function () {
    $tu      = tuUser();
    $student = activeStudent();

    $this->actingAs($tu)
         ->post('/bills', validSppPayload($student->id, ['spp_amount' => 0]))
         ->assertSessionHasErrors('spp_amount');

    expect(StudentBill::where('student_id', $student->id)->count())->toBe(0);
});

// ── 2. Negative SPP amount rejected ───────────────────────────────────────────
test('cannot create SPP bill with negative amount', function () {
    $tu      = tuUser();
    $student = activeStudent();

    $this->actingAs($tu)
         ->post('/bills', validSppPayload($student->id, ['spp_amount' => -5000]))
         ->assertSessionHasErrors('spp_amount');

    expect(StudentBill::where('student_id', $student->id)->count())->toBe(0);
});

// ── 3. Zero item price rejected ───────────────────────────────────────────────
test('cannot create non-SPP bill with zero item price', function () {
    $tu      = tuUser();
    $student = activeStudent();

    $this->actingAs($tu)
         ->post('/bills', validBillPayload($student->id, ['item_prices' => ['0']]))
         ->assertSessionHasErrors('item_prices.0');

    expect(StudentBill::where('student_id', $student->id)->count())->toBe(0);
});

// ── 4. Negative item price rejected ───────────────────────────────────────────
test('cannot create non-SPP bill with negative item price', function () {
    $tu      = tuUser();
    $student = activeStudent();

    $this->actingAs($tu)
         ->post('/bills', validBillPayload($student->id, ['item_prices' => ['-100']]))
         ->assertSessionHasErrors('item_prices.0');

    expect(StudentBill::where('student_id', $student->id)->count())->toBe(0);
});

// ── 5. Zero item quantity rejected ────────────────────────────────────────────
test('cannot create non-SPP bill with zero item quantity', function () {
    $tu      = tuUser();
    $student = activeStudent();

    $this->actingAs($tu)
         ->post('/bills', validBillPayload($student->id, ['item_qtys' => ['0']]))
         ->assertSessionHasErrors('item_qtys.0');

    expect(StudentBill::where('student_id', $student->id)->count())->toBe(0);
});

// ── 6. Empty item array rejected ──────────────────────────────────────────────
test('cannot create non-SPP bill with empty item arrays', function () {
    $tu      = tuUser();
    $student = activeStudent();

    $this->actingAs($tu)
         ->post('/bills', validBillPayload($student->id, [
             'item_names'  => [],
             'item_prices' => [],
             'item_qtys'   => [],
         ]))
         ->assertSessionHasErrors(['item_names', 'item_prices', 'item_qtys']);

    expect(StudentBill::where('student_id', $student->id)->count())->toBe(0);
});

// ── 7. Invalid bill type rejected ─────────────────────────────────────────────
test('cannot create bill with invalid type', function () {
    $tu      = tuUser();
    $student = activeStudent();

    $this->actingAs($tu)
         ->post('/bills', validBillPayload($student->id, ['type' => 'HACKED_TYPE']))
         ->assertSessionHasErrors('type');

    expect(StudentBill::where('student_id', $student->id)->count())->toBe(0);
});

// ── 8. Duplicate SPP bill rejected (application layer) ────────────────────────
test('cannot create duplicate SPP bill for same student month and year', function () {
    $tu      = tuUser();
    $student = activeStudent();

    // First SPP bill — should succeed.
    $this->actingAs($tu)
         ->post('/bills', validSppPayload($student->id, [
             'spp_month' => 5,
             'spp_year'  => 2026,
         ]))
         ->assertRedirect(route('bills.index'));

    expect(StudentBill::where('student_id', $student->id)->count())->toBe(1);

    // Second identical SPP bill — application exists() guard must reject.
    $this->actingAs($tu)
         ->post('/bills', validSppPayload($student->id, [
             'spp_month' => 5,
             'spp_year'  => 2026,
         ]));

    // Only 1 bill should exist — duplicate was skipped (count stays at 1).
    expect(StudentBill::where('student_id', $student->id)->count())->toBe(1);
});

// ── 9. PAID bill: amount is immutable ─────────────────────────────────────────
test('PAID bill cannot have amount changed', function () {
    $bill = StudentBill::factory()->paid()->create(['amount' => 200000]);

    expect(fn () => $bill->update(['amount' => 999999]))
        ->toThrow(\RuntimeException::class);

    $bill->refresh();
    expect((float) $bill->amount)->toBe(200000.0);
});

// ── 10. PAID bill: student_id is immutable ────────────────────────────────────
test('PAID bill cannot have student_id changed', function () {
    $otherStudent = Student::factory()->create();
    $bill         = StudentBill::factory()->paid()->create();
    $originalStudentId = $bill->student_id;

    expect(fn () => $bill->update(['student_id' => $otherStudent->id]))
        ->toThrow(\RuntimeException::class);

    $bill->refresh();
    expect((int) $bill->student_id)->toBe((int) $originalStudentId);
});

// ── 11. PAID bill: type is immutable ──────────────────────────────────────────
test('PAID bill cannot have type changed', function () {
    $bill = StudentBill::factory()->paid()->create(['type' => 'SPP']);

    expect(fn () => $bill->update(['type' => 'LAINNYA']))
        ->toThrow(\RuntimeException::class);

    $bill->refresh();
    expect($bill->type)->toBe('SPP');
});

// ── 12. UNPAID bill can be freely edited ──────────────────────────────────────
test('UNPAID bill can have amount and name changed', function () {
    $bill = StudentBill::factory()->create([
        'status' => 'UNPAID',
        'amount' => 100000,
        'name'   => 'Old Name',
    ]);

    $bill->update(['amount' => 150000, 'name' => 'New Name']);
    $bill->refresh();

    expect((float) $bill->amount)->toBe(150000.0);
    expect($bill->name)->toBe('New Name');
});

// ── 13. Midtrans callback on PAID bill does not trigger immutability guard ────
test('Midtrans callback writing payment fields on PAID bill does not throw', function () {
    // Simulate duplicate webhook: bill already PAID, callback tries to write
    // payment fields again. The idempotency guard in the callback returns early
    // before any update — but even if it did update, payment_token and
    // midtrans_order_id are not in IMMUTABLE_WHEN_PAID.
    $bill = StudentBill::factory()->paid()->create([
        'payment_method'    => 'MIDTRANS',
        'midtrans_order_id' => 'BILL-1-ABCDEF-1000000001',
        'payment_token'     => null,
    ]);

    // These fields are NOT immutable — payment flow must be able to write them.
    expect(fn () => $bill->update(['payment_token' => null]))
        ->not->toThrow(\RuntimeException::class);
});

// ── 14. Valid bill creation succeeds ──────────────────────────────────────────
test('valid non-SPP bill creation succeeds', function () {
    $tu      = tuUser();
    $student = activeStudent();

    $this->actingAs($tu)
         ->post('/bills', validBillPayload($student->id))
         ->assertRedirect(route('bills.index'));

    $bill = StudentBill::where('student_id', $student->id)->first();
    expect($bill)->not->toBeNull();
    expect((float) $bill->amount)->toBeGreaterThan(0);
    expect($bill->status)->toBe('UNPAID');
});
