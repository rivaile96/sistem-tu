<?php

/**
 * Phase 3.7A — PaymentAttempt schema and model tests.
 *
 * Covers:
 *   1.  payment_attempts table exists
 *   2.  order_id has a unique index
 *   3.  bill can have multiple attempts
 *   4.  payment attempt belongs to its bill
 *   5.  bill hasMany attempts relationship works
 *   6.  duplicate order_id is rejected by the database
 *   7.  deleting a bill cascades and removes its attempts
 *   8.  default status is pending
 *   9.  expired() factory state sets correct fields
 *  10.  settled() factory state sets correct fields
 *  11.  isSettled() returns true for settlement status
 *  12.  isFailed() returns true for expire/cancel/deny
 *  13.  isPending() returns true only for pending status
 *  14.  gross_amount is cast to decimal
 *  15.  initiated_at, settled_at, expired_at are cast to datetime
 */

use App\Models\PaymentAttempt;
use App\Models\Student;
use App\Models\StudentBill;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// ── 1. Table exists ───────────────────────────────────────────────────────────
test('payment_attempts table exists', function () {
    expect(Schema::hasTable('payment_attempts'))->toBeTrue();
});

// ── 2. order_id unique index exists ───────────────────────────────────────────
test('order_id column has a unique index', function () {
    $bill = StudentBill::factory()->create();

    PaymentAttempt::factory()->forBill($bill)->create(['order_id' => 'UNIQUE-ORDER-001']);

    expect(
        fn () => PaymentAttempt::factory()->forBill($bill)->create(['order_id' => 'UNIQUE-ORDER-001'])
    )->toThrow(\Illuminate\Database\QueryException::class);
});

// ── 3. Bill can have multiple attempts ────────────────────────────────────────
test('a bill can have multiple payment attempts with different order_ids', function () {
    $bill = StudentBill::factory()->create(['status' => 'UNPAID', 'amount' => 300000]);

    PaymentAttempt::factory()->forBill($bill)->expired()->create();
    PaymentAttempt::factory()->forBill($bill)->expired()->create();
    PaymentAttempt::factory()->forBill($bill)->settled()->create();

    expect(PaymentAttempt::where('student_bill_id', $bill->id)->count())->toBe(3);
});

// ── 4. PaymentAttempt belongsTo bill ─────────────────────────────────────────
test('payment attempt belongs to its student bill', function () {
    $bill    = StudentBill::factory()->create();
    $attempt = PaymentAttempt::factory()->forBill($bill)->create();

    expect($attempt->bill)->toBeInstanceOf(StudentBill::class);
    expect((int) $attempt->bill->id)->toBe((int) $bill->id);
});

// ── 5. StudentBill hasMany paymentAttempts ────────────────────────────────────
test('student bill hasMany payment attempts relationship works', function () {
    $bill = StudentBill::factory()->create();

    PaymentAttempt::factory()->forBill($bill)->create();
    PaymentAttempt::factory()->forBill($bill)->create();

    $loaded = StudentBill::with('paymentAttempts')->find($bill->id);

    expect($loaded->paymentAttempts)->toHaveCount(2);
    expect($loaded->paymentAttempts->first())->toBeInstanceOf(PaymentAttempt::class);
});

// ── 6. Duplicate order_id rejected ───────────────────────────────────────────
test('duplicate order_id is rejected at database level', function () {
    $bill = StudentBill::factory()->create();

    PaymentAttempt::create([
        'student_bill_id' => $bill->id,
        'order_id'        => 'BILL-999-AAABBB-1000000001',
        'gross_amount'    => 200000,
        'initiated_at'    => now(),
        'status'          => 'pending',
    ]);

    expect(fn () => PaymentAttempt::create([
        'student_bill_id' => $bill->id,
        'order_id'        => 'BILL-999-AAABBB-1000000001', // same order_id
        'gross_amount'    => 200000,
        'initiated_at'    => now(),
        'status'          => 'pending',
    ]))->toThrow(\Illuminate\Database\QueryException::class);
});

// ── 7. Deleting bill cascades to attempts ─────────────────────────────────────
test('deleting a student bill cascades and removes all its payment attempts', function () {
    $bill = StudentBill::factory()->create(['status' => 'UNPAID']);

    PaymentAttempt::factory()->forBill($bill)->expired()->create();
    PaymentAttempt::factory()->forBill($bill)->create();

    $billId = $bill->id;
    expect(PaymentAttempt::where('student_bill_id', $billId)->count())->toBe(2);

    $bill->delete();

    expect(PaymentAttempt::where('student_bill_id', $billId)->count())->toBe(0);
});

// ── 8. Default status is pending ─────────────────────────────────────────────
test('new payment attempt has pending status by default', function () {
    $bill    = StudentBill::factory()->create();
    $attempt = PaymentAttempt::factory()->forBill($bill)->create();

    expect($attempt->status)->toBe(PaymentAttempt::STATUS_PENDING);
});

// ── 9. Expired factory state ──────────────────────────────────────────────────
test('expired factory state sets status expire and clears snap_token', function () {
    $bill    = StudentBill::factory()->create();
    $attempt = PaymentAttempt::factory()->forBill($bill)->expired()->create();

    expect($attempt->status)->toBe(PaymentAttempt::STATUS_EXPIRE);
    expect($attempt->snap_token)->toBeNull();
    expect($attempt->expired_at)->not->toBeNull();
});

// ── 10. Settled factory state ─────────────────────────────────────────────────
test('settled factory state sets status settlement and populates settled_at', function () {
    $bill    = StudentBill::factory()->create();
    $attempt = PaymentAttempt::factory()->forBill($bill)->settled()->create();

    expect($attempt->status)->toBe(PaymentAttempt::STATUS_SETTLEMENT);
    expect($attempt->snap_token)->toBeNull();
    expect($attempt->settled_at)->not->toBeNull();
    expect($attempt->bank)->toBe('bca');
});

// ── 11. isSettled() ───────────────────────────────────────────────────────────
test('isSettled returns true for settlement and capture statuses', function () {
    $bill = StudentBill::factory()->create();

    $settled = PaymentAttempt::factory()->forBill($bill)->settled()->create();
    expect($settled->isSettled())->toBeTrue();

    $capture = PaymentAttempt::factory()->forBill($bill)->create([
        'status' => PaymentAttempt::STATUS_CAPTURE,
    ]);
    expect($capture->isSettled())->toBeTrue();

    $pending = PaymentAttempt::factory()->forBill($bill)->create();
    expect($pending->isSettled())->toBeFalse();
});

// ── 12. isFailed() ────────────────────────────────────────────────────────────
test('isFailed returns true for expire, cancel, and deny statuses', function () {
    $bill = StudentBill::factory()->create();

    foreach ([PaymentAttempt::STATUS_EXPIRE, PaymentAttempt::STATUS_CANCEL, PaymentAttempt::STATUS_DENY] as $failStatus) {
        $attempt = PaymentAttempt::factory()->forBill($bill)->create(['status' => $failStatus]);
        expect($attempt->isFailed())->toBeTrue();
    }

    $settled = PaymentAttempt::factory()->forBill($bill)->settled()->create();
    expect($settled->isFailed())->toBeFalse();
});

// ── 13. isPending() ───────────────────────────────────────────────────────────
test('isPending returns true only for pending status', function () {
    $bill    = StudentBill::factory()->create();
    $pending = PaymentAttempt::factory()->forBill($bill)->create();

    expect($pending->isPending())->toBeTrue();

    $expired = PaymentAttempt::factory()->forBill($bill)->expired()->create();
    expect($expired->isPending())->toBeFalse();
});

// ── 14. gross_amount cast ─────────────────────────────────────────────────────
test('gross_amount is stored and retrieved as a decimal', function () {
    $bill    = StudentBill::factory()->create(['amount' => 3450000]);
    $attempt = PaymentAttempt::factory()->forBill($bill)->create(['gross_amount' => 3450000]);

    expect((float) $attempt->fresh()->gross_amount)->toBe(3450000.0);
});

// ── 15. Timestamp casts ───────────────────────────────────────────────────────
test('initiated_at settled_at and expired_at are cast to Carbon datetime instances', function () {
    $bill    = StudentBill::factory()->create();
    $attempt = PaymentAttempt::factory()->forBill($bill)->settled()->create([
        'initiated_at' => now()->subMinutes(10),
        'expired_at'   => null,
    ]);

    expect($attempt->initiated_at)->toBeInstanceOf(\Carbon\Carbon::class);
    expect($attempt->settled_at)->toBeInstanceOf(\Carbon\Carbon::class);
    expect($attempt->expired_at)->toBeNull();
});
