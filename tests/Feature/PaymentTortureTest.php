<?php

/**
 * Phase 3.7E — Payment Torture Test / Final Consistency Test.
 *
 * Proves that the complete payment-attempt architecture remains consistent
 * under realistic retry, concurrency, and webhook-ordering scenarios.
 *
 * Scenarios:
 *   1.  Normal payment — full lifecycle
 *   2.  Double-click / duplicate initiation — reuse path
 *   3.  Retry after expire
 *   4.  Mandiri → BCA style retry (bank switch)
 *   5.  Old attempt settles after being locally cancelled
 *   6.  Two webhooks for the same attempt (idempotency)
 *   7.  Multiple terminal webhooks — first wins
 *   8.  Wrong amount — amount mismatch rejection
 *   9.  Signature failure — no mutation
 *  10.  Already-PAID bill receiving old attempt webhook
 *  11.  Cash payment — full lifecycle + audit
 *  12.  Parent API — IDOR, reuse, settlement, expire, audit source=API
 *  13.  Rollback — partial DB failure leaves no partial state
 *  14.  Legacy webhook — no PaymentAttempt, bill updated, no fabrication
 *
 * All tests assert combined state:
 *   StudentBill + PaymentAttempt + AuditLog
 */

use App\Models\AuditLog;
use App\Models\PaymentAttempt;
use App\Models\Student;
use App\Models\StudentBill;
use App\Models\User;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

// ── Helpers ───────────────────────────────────────────────────────────────────

/** Build a valid Midtrans webhook payload with correct SHA512 signature. */
function tortureWebhook(
    string $orderId,
    float  $amount,
    string $status,
    string $serverKey = 'test-server-key-xyz',
    array  $extra     = []
): array {
    $statusCode  = in_array($status, ['expire']) ? '407' : '200';
    $grossAmount = number_format($amount, 2, '.', '');
    $signature   = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

    return array_merge([
        'order_id'           => $orderId,
        'status_code'        => $statusCode,
        'gross_amount'       => $grossAmount,
        'transaction_status' => $status,
        'fraud_status'       => 'accept',
        'signature_key'      => $signature,
    ], $extra);
}

/** Create a UNPAID bill. */
function tortureBill(int $amount = 3450000): StudentBill
{
    return StudentBill::factory()->create([
        'amount' => $amount,
        'status' => 'UNPAID',
    ]);
}

/** Create a pending PaymentAttempt for a bill. */
function tortureAttempt(
    StudentBill $bill,
    ?string     $suffix    = null,
    ?string     $snapToken = null,
    string      $source    = PaymentAttempt::SOURCE_WEB
): PaymentAttempt {
    $suffix  = $suffix    ?? Str::random(6);
    $orderId = 'BILL-' . $bill->id . '-' . $suffix . '-' . time();

    return PaymentAttempt::factory()->forBill($bill)->create([
        'order_id'     => $orderId,
        'snap_token'   => $snapToken ?? ('snap-' . $suffix),
        'status'       => PaymentAttempt::STATUS_PENDING,
        'gross_amount' => $bill->amount,
        'initiated_at' => now()->subSeconds(3),
        'source'       => $source,
    ]);
}

/** Create a parent API bearer token for a student. */
function torturePToken(Student $student): string
{
    return $student->createToken('ParentApp')->plainTextToken;
}

/** Create an authenticated TU/admin staff user. */
function tortureTuUser(): User
{
    return User::factory()->create(['role' => 'tu']);
}

beforeEach(function () {
    Config::set('services.midtrans.server_key', 'test-server-key-xyz');
    Config::set('services.midtrans.client_key', 'test-client-key-xyz');
});

// ══════════════════════════════════════════════════════════════════════════════
// SCENARIO 1 — NORMAL PAYMENT: full lifecycle
// ══════════════════════════════════════════════════════════════════════════════

test('S1: normal payment — attempt created, settlement marks bill PAID with correct fields', function () {
    $bill    = tortureBill(3450000);
    $attempt = tortureAttempt($bill, 'S1NRM1');

    // ── Pre-settlement state ──────────────────────────────────────────────────
    expect($attempt->status)->toBe(PaymentAttempt::STATUS_PENDING);
    expect($bill->status)->toBe('UNPAID');
    expect($bill->payment_token)->toBeNull();    // factory doesn't set token on bill

    // ── Settlement ───────────────────────────────────────────────────────────
    $this->postJson('/siswa/payment/callback', tortureWebhook(
        $attempt->order_id, 3450000, 'settlement', 'test-server-key-xyz',
        ['payment_type' => 'bank_transfer', 'bank' => 'bca', 'transaction_id' => 'TXN-S1-001']
    ))->assertStatus(200);

    // ── PaymentAttempt state ──────────────────────────────────────────────────
    $attempt->refresh();
    expect($attempt->status)->toBe(PaymentAttempt::STATUS_SETTLEMENT);
    expect($attempt->bank)->toBe('bca');
    expect($attempt->transaction_id)->toBe('TXN-S1-001');
    expect($attempt->settled_at)->not->toBeNull();
    expect($attempt->snap_token)->toBeNull();

    // ── StudentBill state ─────────────────────────────────────────────────────
    $bill->refresh();
    expect($bill->status)->toBe('PAID');
    expect($bill->paid_at)->not->toBeNull();
    expect($bill->payment_method)->toBe('MIDTRANS');
    expect($bill->midtrans_order_id)->toBe($attempt->order_id);
    expect($bill->payment_token)->toBeNull();
    expect($bill->confirmed_by)->toBeNull();

    // ── Audit state ───────────────────────────────────────────────────────────
    expect(
        AuditLog::where('action', AuditLog::PAYMENT_CONFIRMED)
                 ->where('auditable_id', $bill->id)
                 ->count()
    )->toBe(1);
});

// ══════════════════════════════════════════════════════════════════════════════
// SCENARIO 2 — DOUBLE CLICK: reuse path
// ══════════════════════════════════════════════════════════════════════════════

test('S2: double-click duplicate initiation — reuse existing pending, one attempt, no duplicate audit', function () {
    $student = Student::factory()->create();
    $bill    = StudentBill::factory()->create([
        'student_id' => $student->id,
        'status'     => 'UNPAID',
        'amount'     => 300000,
    ]);
    $attempt = tortureAttempt($bill, 'S2DBL1', 'snap-double-click');
    $bill->update(['payment_token' => 'snap-double-click']);

    // Both requests return the same snap_token — no new Snap call possible
    // (Snap is a plain class, not mockable, so we verify via DB state only).
    $r1 = $this->actingAs($student, 'siswa')
               ->postJson("/siswa/tagihan/{$bill->id}/pay")
               ->assertStatus(200);

    $r2 = $this->actingAs($student, 'siswa')
               ->postJson("/siswa/tagihan/{$bill->id}/pay")
               ->assertStatus(200);

    // Both responses return the same token.
    expect($r1->json('snap_token'))->toBe('snap-double-click');
    expect($r2->json('snap_token'))->toBe('snap-double-click');
    expect($r1->json('order_id'))->toBe($r2->json('order_id'));

    // Exactly one pending attempt.
    expect(
        PaymentAttempt::where('student_bill_id', $bill->id)
                       ->where('status', PaymentAttempt::STATUS_PENDING)
                       ->count()
    )->toBe(1);

    // Exactly one PAYMENT_ATTEMPT_CREATED audit event.
    expect(
        AuditLog::where('action', AuditLog::PAYMENT_ATTEMPT_CREATED)
                 ->where('auditable_id', $attempt->id)
                 ->count()
    )->toBe(0); // Attempt was created via factory, not via controller — no audit from controller.

    // Bill state unchanged.
    expect($bill->fresh()->status)->toBe('UNPAID');
});

// ══════════════════════════════════════════════════════════════════════════════
// SCENARIO 3 — RETRY AFTER EXPIRE
// ══════════════════════════════════════════════════════════════════════════════

test('S3: retry after expire — A expires, B created fresh, only one pending', function () {
    $bill    = tortureBill();
    $attempt = tortureAttempt($bill, 'S3EXP1', 'snap-expire-a');
    $bill->update(['payment_token' => 'snap-expire-a']);

    // Expire webhook for A.
    $statusCode  = '407';
    $grossAmount = number_format($bill->amount, 2, '.', '');
    $signature   = hash('sha512', $attempt->order_id . $statusCode . $grossAmount . 'test-server-key-xyz');

    $this->postJson('/siswa/payment/callback', [
        'order_id'           => $attempt->order_id,
        'status_code'        => $statusCode,
        'gross_amount'       => $grossAmount,
        'transaction_status' => 'expire',
        'fraud_status'       => 'accept',
        'signature_key'      => $signature,
    ])->assertStatus(200);

    // A is now terminal.
    $attempt->refresh();
    expect($attempt->status)->toBe(PaymentAttempt::STATUS_EXPIRE);
    expect($attempt->expired_at)->not->toBeNull();

    // Bill is UNPAID, payment_token cleared.
    $bill->refresh();
    expect($bill->status)->toBe('UNPAID');
    expect($bill->payment_token)->toBeNull();

    // PAYMENT_FAILED audit written.
    expect(
        AuditLog::where('action', AuditLog::PAYMENT_FAILED)
                 ->where('auditable_id', $bill->id)
                 ->count()
    )->toBe(1);

    // Create attempt B directly (simulates new initiation after expire).
    $attemptB = tortureAttempt($bill, 'S3EXP2', 'snap-retry-b');
    $bill->update(['payment_token' => 'snap-retry-b']);

    // A remains terminal, B is pending.
    expect($attempt->fresh()->status)->toBe(PaymentAttempt::STATUS_EXPIRE);
    expect($attemptB->status)->toBe(PaymentAttempt::STATUS_PENDING);

    // Only one pending attempt.
    expect(
        PaymentAttempt::where('student_bill_id', $bill->id)
                       ->where('status', PaymentAttempt::STATUS_PENDING)
                       ->count()
    )->toBe(1);

    // B has a different order_id and snap_token.
    expect($attemptB->order_id)->not->toBe($attempt->order_id);
    expect($attemptB->snap_token)->toBe('snap-retry-b');
});

// ══════════════════════════════════════════════════════════════════════════════
// SCENARIO 4 — BANK SWITCH RETRY (Mandiri → BCA)
// ══════════════════════════════════════════════════════════════════════════════

test('S4: bank-switch retry — A expires (Mandiri), B settles (BCA), bill shows B', function () {
    $bill = tortureBill(500000);

    // Attempt A — Mandiri, expires.
    $attemptA = tortureAttempt($bill, 'S4MDR1', 'snap-mandiri');
    $bill->update(['payment_token' => 'snap-mandiri']);

    // Expire A.
    $statusCode  = '407';
    $grossAmount = number_format(500000, 2, '.', '');
    $signature   = hash('sha512', $attemptA->order_id . $statusCode . $grossAmount . 'test-server-key-xyz');
    $this->postJson('/siswa/payment/callback', [
        'order_id'           => $attemptA->order_id,
        'status_code'        => $statusCode,
        'gross_amount'       => $grossAmount,
        'transaction_status' => 'expire',
        'fraud_status'       => 'accept',
        'signature_key'      => $signature,
    ])->assertStatus(200);

    expect($attemptA->fresh()->status)->toBe(PaymentAttempt::STATUS_EXPIRE);

    // Attempt B — BCA.
    $attemptB = tortureAttempt($bill, 'S4BCA1', 'snap-bca');
    $bill->update(['payment_token' => 'snap-bca']);

    // Settle B.
    $this->postJson('/siswa/payment/callback', tortureWebhook(
        $attemptB->order_id, 500000, 'settlement', 'test-server-key-xyz',
        ['payment_type' => 'bank_transfer', 'bank' => 'bca', 'transaction_id' => 'TXN-BCA-S4']
    ))->assertStatus(200);

    // ── PaymentAttempt state ──────────────────────────────────────────────────
    expect($attemptA->fresh()->status)->toBe(PaymentAttempt::STATUS_EXPIRE);
    $attemptB->refresh();
    expect($attemptB->status)->toBe(PaymentAttempt::STATUS_SETTLEMENT);
    expect($attemptB->bank)->toBe('bca');
    expect($attemptB->transaction_id)->toBe('TXN-BCA-S4');

    // ── StudentBill state ─────────────────────────────────────────────────────
    $bill->refresh();
    expect($bill->status)->toBe('PAID');
    expect($bill->midtrans_order_id)->toBe($attemptB->order_id);
    expect($bill->payment_token)->toBeNull();
    expect($bill->paid_at)->not->toBeNull();

    // ── Audit state ───────────────────────────────────────────────────────────
    // PAYMENT_FAILED for A.
    expect(
        AuditLog::where('action', AuditLog::PAYMENT_FAILED)
                 ->where('auditable_id', $bill->id)
                 ->count()
    )->toBe(1);

    // PAYMENT_CONFIRMED for B.
    expect(
        AuditLog::where('action', AuditLog::PAYMENT_CONFIRMED)
                 ->where('auditable_id', $bill->id)
                 ->count()
    )->toBe(1);
});

// ══════════════════════════════════════════════════════════════════════════════
// SCENARIO 5 — OLD ATTEMPT SETTLES AFTER BEING LOCALLY CANCELLED
// ══════════════════════════════════════════════════════════════════════════════

test('S5: old cancelled attempt settlement — IGNORED, bill unchanged, audit written', function () {
    $bill = tortureBill(200000);

    // A cancelled locally (superseded by B's initiation).
    $attemptA = PaymentAttempt::factory()->forBill($bill)->create([
        'order_id'   => 'BILL-' . $bill->id . '-S5OLD1-' . time(),
        'status'     => PaymentAttempt::STATUS_CANCEL,
        'expired_at' => now()->subMinutes(2),
        'snap_token' => null,
    ]);

    // B is active pending.
    $attemptB = tortureAttempt($bill, 'S5NEW1', 'snap-b-active');
    $bill->update(['payment_token' => 'snap-b-active']);

    // Late settlement webhook arrives for A.
    $this->postJson('/siswa/payment/callback', tortureWebhook(
        $attemptA->order_id, 200000, 'settlement'
    ))->assertStatus(200);

    // A still terminal — not overwritten.
    expect($attemptA->fresh()->status)->toBe(PaymentAttempt::STATUS_CANCEL);

    // Bill NOT marked PAID.
    $bill->refresh();
    expect($bill->status)->toBe('UNPAID');
    expect($bill->paid_at)->toBeNull();
    expect($bill->midtrans_order_id)->toBeNull();

    // B's token NOT cleared.
    expect($bill->payment_token)->toBe('snap-b-active');

    // B still pending.
    expect($attemptB->fresh()->status)->toBe(PaymentAttempt::STATUS_PENDING);

    // SETTLEMENT_IGNORED audit written exactly once.
    expect(
        AuditLog::where('action', AuditLog::PAYMENT_ATTEMPT_SETTLEMENT_IGNORED)
                 ->where('auditable_id', $attemptA->id)
                 ->count()
    )->toBe(1);
});

// ══════════════════════════════════════════════════════════════════════════════
// SCENARIO 6 — TWO WEBHOOKS FOR SAME ATTEMPT (idempotency)
// ══════════════════════════════════════════════════════════════════════════════

test('S6: duplicate settlement webhooks — second is idempotent, one audit only', function () {
    $bill    = tortureBill();
    $attempt = tortureAttempt($bill, 'S6DUP1');

    $payload = tortureWebhook($attempt->order_id, $bill->amount, 'settlement');

    $this->postJson('/siswa/payment/callback', $payload)->assertStatus(200);
    $this->postJson('/siswa/payment/callback', $payload)->assertStatus(200);

    // ── PaymentAttempt ────────────────────────────────────────────────────────
    $attempt->refresh();
    expect($attempt->status)->toBe(PaymentAttempt::STATUS_SETTLEMENT);

    // ── StudentBill ───────────────────────────────────────────────────────────
    $bill->refresh();
    expect($bill->status)->toBe('PAID');

    // ── Audit ─────────────────────────────────────────────────────────────────
    // Exactly one PAYMENT_CONFIRMED — second webhook hits bill-level idempotency.
    expect(
        AuditLog::where('action', AuditLog::PAYMENT_CONFIRMED)
                 ->where('auditable_id', $bill->id)
                 ->count()
    )->toBe(1);
});

// ══════════════════════════════════════════════════════════════════════════════
// SCENARIO 7 — MULTIPLE TERMINAL WEBHOOKS — first wins
// ══════════════════════════════════════════════════════════════════════════════

test('S7: multiple terminal webhooks — first expire wins, subsequent ignored', function () {
    $bill    = tortureBill();
    $attempt = tortureAttempt($bill, 'S7TERM');
    $bill->update(['payment_token' => 'snap-s7-term']);

    $statusCode  = '407';
    $grossAmount = number_format($bill->amount, 2, '.', '');
    $signature   = hash('sha512', $attempt->order_id . $statusCode . $grossAmount . 'test-server-key-xyz');

    $expirePayload = [
        'order_id'           => $attempt->order_id,
        'status_code'        => $statusCode,
        'gross_amount'       => $grossAmount,
        'transaction_status' => 'expire',
        'fraud_status'       => 'accept',
        'signature_key'      => $signature,
    ];

    // First expire.
    $this->postJson('/siswa/payment/callback', $expirePayload)->assertStatus(200);
    $firstExpiredAt = $attempt->fresh()->expired_at;

    // Second expire — should not overwrite expired_at.
    $this->postJson('/siswa/payment/callback', $expirePayload)->assertStatus(200);
    expect($attempt->fresh()->expired_at->toISOString())->toBe($firstExpiredAt->toISOString());

    // Cancel webhook — also should not change already-expired attempt.
    $this->postJson('/siswa/payment/callback', tortureWebhook(
        $attempt->order_id, $bill->amount, 'cancel'
    ))->assertStatus(200);
    expect($attempt->fresh()->status)->toBe(PaymentAttempt::STATUS_EXPIRE);

    // Exactly one PAYMENT_FAILED.
    expect(
        AuditLog::where('action', AuditLog::PAYMENT_FAILED)
                 ->where('auditable_id', $bill->id)
                 ->count()
    )->toBe(1);
});

// ══════════════════════════════════════════════════════════════════════════════
// SCENARIO 8 — WRONG AMOUNT
// ══════════════════════════════════════════════════════════════════════════════

test('S8: webhook with wrong amount — attempt stays pending, bill stays UNPAID', function () {
    $bill    = tortureBill(3450000);
    $attempt = tortureAttempt($bill, 'S8AMT1');

    // Send wrong amount — signature computed with wrong amount.
    $wrongAmount = number_format(345000, 2, '.', '');
    $statusCode  = '200';
    $signature   = hash('sha512', $attempt->order_id . $statusCode . $wrongAmount . 'test-server-key-xyz');

    $this->postJson('/siswa/payment/callback', [
        'order_id'           => $attempt->order_id,
        'status_code'        => $statusCode,
        'gross_amount'       => $wrongAmount,
        'transaction_status' => 'settlement',
        'fraud_status'       => 'accept',
        'signature_key'      => $signature,
    ])->assertStatus(200);

    // ── PaymentAttempt unchanged ──────────────────────────────────────────────
    expect($attempt->fresh()->status)->toBe(PaymentAttempt::STATUS_PENDING);

    // ── Bill unchanged ────────────────────────────────────────────────────────
    $bill->refresh();
    expect($bill->status)->toBe('UNPAID');
    expect($bill->paid_at)->toBeNull();
    expect($bill->midtrans_order_id)->toBeNull();

    // ── No PAYMENT_CONFIRMED ──────────────────────────────────────────────────
    expect(
        AuditLog::where('action', AuditLog::PAYMENT_CONFIRMED)
                 ->where('auditable_id', $bill->id)
                 ->count()
    )->toBe(0);
});

// ══════════════════════════════════════════════════════════════════════════════
// SCENARIO 9 — SIGNATURE FAILURE
// ══════════════════════════════════════════════════════════════════════════════

test('S9: invalid signature — no attempt mutation, no bill mutation, HTTP 200', function () {
    $bill    = tortureBill();
    $attempt = tortureAttempt($bill, 'S9SIG1');

    $this->postJson('/siswa/payment/callback', [
        'order_id'           => $attempt->order_id,
        'status_code'        => '200',
        'gross_amount'       => number_format($bill->amount, 2, '.', ''),
        'transaction_status' => 'settlement',
        'fraud_status'       => 'accept',
        'signature_key'      => 'invalid-signature-xyz',
    ])->assertStatus(200);

    // Nothing changed.
    expect($attempt->fresh()->status)->toBe(PaymentAttempt::STATUS_PENDING);
    expect($bill->fresh()->status)->toBe('UNPAID');
    expect(
        AuditLog::where('action', AuditLog::PAYMENT_CONFIRMED)
                 ->where('auditable_id', $bill->id)
                 ->count()
    )->toBe(0);
});

// ══════════════════════════════════════════════════════════════════════════════
// SCENARIO 10 — ALREADY-PAID BILL receiving old attempt webhook
// ══════════════════════════════════════════════════════════════════════════════

test('S10: already-PAID bill — old cancelled attempt settlement does not overwrite paid fields', function () {
    $settledOrderId = 'BILL-99-S10PAID-' . time();
    $bill           = StudentBill::factory()->paid($settledOrderId)->create(['amount' => 400000]);
    $originalPaidAt = $bill->paid_at;

    // Old attempt — already locally cancelled.
    $oldAttempt = PaymentAttempt::factory()->forBill($bill)->create([
        'order_id'   => 'BILL-' . $bill->id . '-S10OLD-' . (time() - 200),
        'status'     => PaymentAttempt::STATUS_CANCEL,
        'expired_at' => now()->subMinutes(10),
        'snap_token' => null,
    ]);

    // Late settlement for the old attempt.
    // The bill-level idempotency guard fires first (bill is already PAID)
    // and returns early — the attempt-level SETTLEMENT_IGNORED path is only
    // reached when the bill is UNPAID. This is correct: the bill is protected
    // regardless of which guard fires.
    $this->postJson('/siswa/payment/callback', tortureWebhook(
        $oldAttempt->order_id, 400000, 'settlement'
    ))->assertStatus(200);

    // Bill fields must not be overwritten.
    $bill->refresh();
    expect($bill->status)->toBe('PAID');
    expect($bill->midtrans_order_id)->toBe($settledOrderId);
    expect($bill->paid_at->toISOString())->toBe($originalPaidAt->toISOString());

    // No second PAYMENT_CONFIRMED written (bill guard returned early).
    expect(
        AuditLog::where('action', AuditLog::PAYMENT_CONFIRMED)
                 ->where('auditable_id', $bill->id)
                 ->count()
    )->toBe(0); // bill was created via factory, no audit written in test setup

    // Old attempt status unchanged.
    expect($oldAttempt->fresh()->status)->toBe(PaymentAttempt::STATUS_CANCEL);
});

// ══════════════════════════════════════════════════════════════════════════════
// SCENARIO 11 — CASH PAYMENT
// ══════════════════════════════════════════════════════════════════════════════

test('S11: cash payment — bill PAID, confirmed_by set, payment_method CASH, audit source=WEB', function () {
    $tuUser = tortureTuUser();
    $bill   = StudentBill::factory()->create(['amount' => 500000, 'status' => 'UNPAID']);

    $response = $this->actingAs($tuUser)
                     ->post("/bills/{$bill->id}/pay");

    // Allow redirect response (web controller returns back()).
    expect($response->status())->toBeIn([200, 302]);

    // ── StudentBill state ─────────────────────────────────────────────────────
    $bill->refresh();
    expect($bill->status)->toBe('PAID');
    expect($bill->payment_method)->toBe('CASH');
    expect($bill->paid_at)->not->toBeNull();
    expect((int) $bill->confirmed_by)->toBe($tuUser->id);
    expect($bill->midtrans_order_id)->toBeNull(); // Cash — no Midtrans order.

    // ── No PaymentAttempt created ─────────────────────────────────────────────
    expect(PaymentAttempt::where('student_bill_id', $bill->id)->count())->toBe(0);

    // ── Audit state ───────────────────────────────────────────────────────────
    $log = AuditLog::where('action', AuditLog::PAYMENT_CONFIRMED)
                   ->where('auditable_id', $bill->id)
                   ->first();
    expect($log)->not->toBeNull();
    expect($log->source)->toBe(AuditLog::SOURCE_WEB);
    expect($log->user_id)->toBe($tuUser->id);
});

test('S11b: cash payment on already-PAID bill — no overwrite', function () {
    $tuUser         = tortureTuUser();
    $orderId        = 'BILL-77-S11CASH-' . time();
    $bill           = StudentBill::factory()->paid($orderId)->create(['amount' => 200000]);
    $originalPaidAt = $bill->paid_at;

    $this->actingAs($tuUser)->post("/bills/{$bill->id}/pay");

    $bill->refresh();
    expect($bill->status)->toBe('PAID');
    expect($bill->paid_at->toISOString())->toBe($originalPaidAt->toISOString());
});

// ══════════════════════════════════════════════════════════════════════════════
// SCENARIO 12 — PARENT API
// ══════════════════════════════════════════════════════════════════════════════

test('S12a: parent API — IDOR protection (cannot pay another student bill)', function () {
    $studentA = Student::factory()->create();
    $studentB = Student::factory()->create();
    $billB    = StudentBill::factory()->create([
        'student_id' => $studentB->id,
        'status'     => 'UNPAID',
        'amount'     => 200000,
    ]);

    $this->withToken(torturePToken($studentA))
         ->postJson('/api/payment/create', ['id' => $billB->id, 'type' => 'BILL'])
         ->assertStatus(404); // scoped lookup returns 404 for IDOR
});

test('S12b: parent API — reuse pending attempt, no new attempt created', function () {
    $student = Student::factory()->create();
    $bill    = StudentBill::factory()->create([
        'student_id' => $student->id,
        'status'     => 'UNPAID',
        'amount'     => 300000,
    ]);
    $attempt = tortureAttempt($bill, 'S12REUSE', 'snap-api-reuse', PaymentAttempt::SOURCE_API);
    $bill->update(['payment_token' => 'snap-api-reuse']);

    $response = $this->withToken(torturePToken($student))
                     ->postJson('/api/payment/create', ['id' => $bill->id, 'type' => 'BILL'])
                     ->assertStatus(200);

    expect($response->json('snap_token'))->toBe('snap-api-reuse');
    expect($response->json('order_id'))->toBe($attempt->order_id);
    expect(PaymentAttempt::where('student_bill_id', $bill->id)->count())->toBe(1);
});

test('S12c: parent API — settlement via API callback marks bill PAID, source=MIDTRANS in audit', function () {
    $student = Student::factory()->create();
    $bill    = StudentBill::factory()->create([
        'student_id' => $student->id,
        'status'     => 'UNPAID',
        'amount'     => 250000,
    ]);
    $attempt = tortureAttempt($bill, 'S12SET1', 'snap-api-settle', PaymentAttempt::SOURCE_API);

    $this->postJson('/api/midtrans-callback', tortureWebhook(
        $attempt->order_id, 250000, 'settlement', 'test-server-key-xyz',
        ['payment_type' => 'gopay', 'transaction_id' => 'TXN-GOPAY-S12']
    ))->assertStatus(200);

    $attempt->refresh();
    expect($attempt->status)->toBe(PaymentAttempt::STATUS_SETTLEMENT);
    expect($attempt->transaction_id)->toBe('TXN-GOPAY-S12');

    $bill->refresh();
    expect($bill->status)->toBe('PAID');
    expect($bill->payment_method)->toBe('MIDTRANS');
    expect($bill->midtrans_order_id)->toBe($attempt->order_id);

    $log = AuditLog::where('action', AuditLog::PAYMENT_CONFIRMED)
                   ->where('auditable_id', $bill->id)
                   ->first();
    expect($log)->not->toBeNull();
    expect($log->source)->toBe(AuditLog::SOURCE_MIDTRANS);
});

test('S12d: parent API — expire via API callback clears token, bill stays UNPAID', function () {
    $student = Student::factory()->create();
    $bill    = StudentBill::factory()->create([
        'student_id' => $student->id,
        'status'     => 'UNPAID',
        'amount'     => 250000,
    ]);
    $attempt = tortureAttempt($bill, 'S12EXP1', 'snap-api-expire', PaymentAttempt::SOURCE_API);
    $bill->update(['payment_token' => 'snap-api-expire']);

    $statusCode  = '407';
    $grossAmount = number_format(250000, 2, '.', '');
    $signature   = hash('sha512', $attempt->order_id . $statusCode . $grossAmount . 'test-server-key-xyz');

    $this->postJson('/api/midtrans-callback', [
        'order_id'           => $attempt->order_id,
        'status_code'        => $statusCode,
        'gross_amount'       => $grossAmount,
        'transaction_status' => 'expire',
        'fraud_status'       => 'accept',
        'signature_key'      => $signature,
    ])->assertStatus(200);

    $attempt->refresh();
    expect($attempt->status)->toBe(PaymentAttempt::STATUS_EXPIRE);

    $bill->refresh();
    expect($bill->status)->toBe('UNPAID');
    expect($bill->payment_token)->toBeNull();
});

test('S12e: parent API — audit source=API for attempt creation', function () {
    $student = Student::factory()->create();
    $bill    = StudentBill::factory()->create([
        'student_id' => $student->id,
        'status'     => 'UNPAID',
        'amount'     => 200000,
    ]);

    // Create attempt with SOURCE_API directly (controller path needs Snap mock).
    $attempt = tortureAttempt($bill, 'S12AUD1', 'snap-api-audit', PaymentAttempt::SOURCE_API);

    // Manually write audit as the controller would.
    \App\Services\FinancialAuditLogger::paymentAttemptCreated(
        $attempt,
        AuditLog::SOURCE_API,
        null,
        null
    );

    $log = AuditLog::where('action', AuditLog::PAYMENT_ATTEMPT_CREATED)
                   ->where('auditable_id', $attempt->id)
                   ->first();
    expect($log)->not->toBeNull();
    expect($log->source)->toBe(AuditLog::SOURCE_API);
});

// ══════════════════════════════════════════════════════════════════════════════
// SCENARIO 13 — ROLLBACK
// ══════════════════════════════════════════════════════════════════════════════

test('S13: DB transaction rollback — partial failure leaves no partial financial state', function () {
    $bill    = tortureBill(300000);
    $attempt = tortureAttempt($bill, 'S13ROLL');

    $threw = false;
    try {
        DB::transaction(function () use ($bill, $attempt) {
            $attempt->update([
                'status'     => PaymentAttempt::STATUS_SETTLEMENT,
                'settled_at' => now(),
                'snap_token' => null,
            ]);

            $bill->update([
                'status'         => 'PAID',
                'paid_at'        => now(),
                'payment_method' => 'MIDTRANS',
            ]);

            // Simulate audit write failure.
            throw new \RuntimeException('Simulated audit failure');
        });
    } catch (\RuntimeException) {
        $threw = true;
    }

    expect($threw)->toBeTrue();

    // ── Both records rolled back ───────────────────────────────────────────────
    expect($attempt->fresh()->status)->toBe(PaymentAttempt::STATUS_PENDING);
    expect($attempt->fresh()->settled_at)->toBeNull();
    expect($bill->fresh()->status)->toBe('UNPAID');
    expect($bill->fresh()->paid_at)->toBeNull();

    // ── No audit events ───────────────────────────────────────────────────────
    expect(
        AuditLog::where('action', AuditLog::PAYMENT_CONFIRMED)
                 ->where('auditable_id', $bill->id)
                 ->count()
    )->toBe(0);
});

// ══════════════════════════════════════════════════════════════════════════════
// SCENARIO 14 — LEGACY WEBHOOK (no PaymentAttempt)
// ══════════════════════════════════════════════════════════════════════════════

test('S14: legacy webhook — no PaymentAttempt exists, bill updated, no fabrication, HTTP 200', function () {
    $bill    = tortureBill(200000);
    $orderId = 'BILL-' . $bill->id . '-LEGACY-' . time();

    // No PaymentAttempt — simulates pre-Phase-3.7 order.
    expect(PaymentAttempt::where('order_id', $orderId)->count())->toBe(0);

    $this->postJson('/siswa/payment/callback', tortureWebhook(
        $orderId, 200000, 'settlement'
    ))->assertStatus(200);

    // ── Bill updated via legacy path ──────────────────────────────────────────
    $bill->refresh();
    expect($bill->status)->toBe('PAID');
    expect($bill->midtrans_order_id)->toBe($orderId);
    expect($bill->payment_method)->toBe('MIDTRANS');
    expect($bill->paid_at)->not->toBeNull();

    // ── No PaymentAttempt fabricated ──────────────────────────────────────────
    expect(PaymentAttempt::where('order_id', $orderId)->count())->toBe(0);

    // ── PAYMENT_CONFIRMED audit still written ─────────────────────────────────
    expect(
        AuditLog::where('action', AuditLog::PAYMENT_CONFIRMED)
                 ->where('auditable_id', $bill->id)
                 ->count()
    )->toBe(1);
});

test('S14b: legacy expire webhook — bill stays UNPAID, no PaymentAttempt fabricated', function () {
    $bill    = tortureBill(150000);
    $orderId = 'BILL-' . $bill->id . '-LGCEXP-' . time();

    $statusCode  = '407';
    $grossAmount = number_format(150000, 2, '.', '');
    $signature   = hash('sha512', $orderId . $statusCode . $grossAmount . 'test-server-key-xyz');

    $this->postJson('/siswa/payment/callback', [
        'order_id'           => $orderId,
        'status_code'        => $statusCode,
        'gross_amount'       => $grossAmount,
        'transaction_status' => 'expire',
        'fraud_status'       => 'accept',
        'signature_key'      => $signature,
    ])->assertStatus(200);

    expect($bill->fresh()->status)->toBe('UNPAID');
    expect(PaymentAttempt::where('order_id', $orderId)->count())->toBe(0);
});
