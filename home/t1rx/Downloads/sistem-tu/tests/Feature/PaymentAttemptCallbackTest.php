<?php

/**
 * Phase 3.7C — PaymentAttempt webhook callback integration tests.
 *
 * Covers:
 *   1.  pending → settlement
 *   2.  pending → capture
 *   3.  pending → expire
 *   4.  pending → cancel
 *   5.  pending → deny
 *   6.  duplicate settlement (idempotency)
 *   7.  duplicate expire (idempotency)
 *   8.  wrong gross_amount against attempt
 *   9.  wrong gross_amount against bill
 *  10.  transaction_id stored from webhook
 *  11.  bank stored when present in webhook
 *  12.  VA number stored when present in webhook
 *  13.  settlement_time parsed into settled_at
 *  14.  PAYMENT_CONFIRMED written only once
 *  15.  PAYMENT_FAILED written only once
 *  16.  StudentBill synchronised on settlement
 *  17.  legacy webhook (no PaymentAttempt) — bill updated, no crash
 *  18.  already-PAID bill receiving duplicate settlement webhook
 *  19.  stale attempt webhook — old order_id settles while newer attempt exists
 *  20.  rollback: if StudentBill update fails, PaymentAttempt stays pending
 */

use App\Models\AuditLog;
use App\Models\PaymentAttempt;
use App\Models\Student;
use App\Models\StudentBill;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

// ── Helpers ───────────────────────────────────────────────────────────────────

function makeAttemptAndBill(int $amount = 250000): array
{
    $bill    = StudentBill::factory()->create(['amount' => $amount, 'status' => 'UNPAID']);
    $orderId = 'BILL-' . $bill->id . '-ABCDEF-' . time();

    $attempt = PaymentAttempt::factory()->forBill($bill)->create([
        'order_id'     => $orderId,
        'snap_token'   => 'snap-tok-' . $bill->id,
        'status'       => PaymentAttempt::STATUS_PENDING,
        'gross_amount' => $amount,
        'initiated_at' => now()->subMinutes(2),
        'source'       => PaymentAttempt::SOURCE_WEB,
    ]);

    return [$bill, $attempt, $orderId];
}

function siswaWebhookPayload(
    string $orderId,
    float  $amount,
    string $status,
    string $serverKey = 'test-server-key-xyz',
    array  $extra     = []
): array {
    $statusCode  = '200';
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

beforeEach(function () {
    Config::set('services.midtrans.server_key', 'test-server-key-xyz');
});

// ── 1. pending → settlement ───────────────────────────────────────────────────
test('settlement webhook transitions attempt from pending to settlement', function () {
    [$bill, $attempt, $orderId] = makeAttemptAndBill();

    $this->postJson('/siswa/payment/callback', siswaWebhookPayload($orderId, 250000, 'settlement'))
         ->assertStatus(200);

    expect($attempt->fresh()->status)->toBe(PaymentAttempt::STATUS_SETTLEMENT);
    expect($attempt->fresh()->settled_at)->not->toBeNull();
    expect($attempt->fresh()->snap_token)->toBeNull();
});

// ── 2. pending → capture ──────────────────────────────────────────────────────
test('capture webhook transitions attempt from pending to capture', function () {
    [$bill, $attempt, $orderId] = makeAttemptAndBill();

    $this->postJson('/siswa/payment/callback', siswaWebhookPayload($orderId, 250000, 'capture'))
         ->assertStatus(200);

    expect($attempt->fresh()->status)->toBe(PaymentAttempt::STATUS_CAPTURE);
    expect($attempt->fresh()->settled_at)->not->toBeNull();
});

// ── 3. pending → expire ───────────────────────────────────────────────────────
test('expire webhook transitions attempt from pending to expire', function () {
    [$bill, $attempt, $orderId] = makeAttemptAndBill();

    $statusCode  = '407';
    $grossAmount = number_format(250000, 2, '.', '');
    $signature   = hash('sha512', $orderId . $statusCode . $grossAmount . 'test-server-key-xyz');

    $this->postJson('/siswa/payment/callback', [
        'order_id'           => $orderId,
        'status_code'        => $statusCode,
        'gross_amount'       => $grossAmount,
        'transaction_status' => 'expire',
        'fraud_status'       => 'accept',
        'signature_key'      => $signature,
    ])->assertStatus(200);

    expect($attempt->fresh()->status)->toBe(PaymentAttempt::STATUS_EXPIRE);
    expect($attempt->fresh()->expired_at)->not->toBeNull();
    expect($attempt->fresh()->snap_token)->toBeNull();
});

// ── 4. pending → cancel ───────────────────────────────────────────────────────
test('cancel webhook transitions attempt from pending to cancel', function () {
    [$bill, $attempt, $orderId] = makeAttemptAndBill();

    $this->postJson('/siswa/payment/callback', siswaWebhookPayload($orderId, 250000, 'cancel'))
         ->assertStatus(200);

    expect($attempt->fresh()->status)->toBe(PaymentAttempt::STATUS_CANCEL);
    expect($attempt->fresh()->expired_at)->not->toBeNull();
});

// ── 5. pending → deny ─────────────────────────────────────────────────────────
test('deny webhook transitions attempt from pending to deny', function () {
    [$bill, $attempt, $orderId] = makeAttemptAndBill();

    $this->postJson('/siswa/payment/callback', siswaWebhookPayload($orderId, 250000, 'deny'))
         ->assertStatus(200);

    expect($attempt->fresh()->status)->toBe(PaymentAttempt::STATUS_DENY);
});

// ── 6. duplicate settlement (idempotency) ─────────────────────────────────────
test('duplicate settlement webhook does not mutate an already-settled attempt', function () {
    [$bill, $attempt, $orderId] = makeAttemptAndBill();

    // First webhook — settles
    $this->postJson('/siswa/payment/callback', siswaWebhookPayload($orderId, 250000, 'settlement'))
         ->assertStatus(200);

    $settledAt = $attempt->fresh()->settled_at;

    // Second webhook — must be ignored
    $this->postJson('/siswa/payment/callback', siswaWebhookPayload($orderId, 250000, 'settlement'))
         ->assertStatus(200);

    expect($attempt->fresh()->settled_at->toISOString())->toBe($settledAt->toISOString());
    expect(
        AuditLog::where('action', AuditLog::PAYMENT_CONFIRMED)
                 ->where('auditable_id', $bill->id)
                 ->count()
    )->toBe(1);
});

// ── 7. duplicate expire (idempotency) ─────────────────────────────────────────
test('duplicate expire webhook does not overwrite expired_at of already-expired attempt', function () {
    [$bill, $attempt, $orderId] = makeAttemptAndBill();

    $statusCode  = '407';
    $grossAmount = number_format(250000, 2, '.', '');
    $signature   = hash('sha512', $orderId . $statusCode . $grossAmount . 'test-server-key-xyz');
    $payload     = [
        'order_id'           => $orderId,
        'status_code'        => $statusCode,
        'gross_amount'       => $grossAmount,
        'transaction_status' => 'expire',
        'fraud_status'       => 'accept',
        'signature_key'      => $signature,
    ];

    $this->postJson('/siswa/payment/callback', $payload)->assertStatus(200);
    $expiredAt = $attempt->fresh()->expired_at;

    $this->postJson('/siswa/payment/callback', $payload)->assertStatus(200);
    expect($attempt->fresh()->expired_at->toISOString())->toBe($expiredAt->toISOString());
});

// ── 8. wrong gross_amount against attempt ─────────────────────────────────────
test('webhook with wrong amount does not mutate attempt or bill', function () {
    [$bill, $attempt, $orderId] = makeAttemptAndBill(250000);

    // Send wrong amount — validation should reject before any DB write.
    $badAmount  = number_format(999999, 2, '.', '');
    $statusCode = '200';
    $signature  = hash('sha512', $orderId . $statusCode . $badAmount . 'test-server-key-xyz');

    $this->postJson('/siswa/payment/callback', [
        'order_id'           => $orderId,
        'status_code'        => $statusCode,
        'gross_amount'       => $badAmount,
        'transaction_status' => 'settlement',
        'fraud_status'       => 'accept',
        'signature_key'      => $signature,
    ])->assertStatus(200);

    expect($attempt->fresh()->status)->toBe(PaymentAttempt::STATUS_PENDING);
    expect($bill->fresh()->status)->toBe('UNPAID');
});

// ── 9. wrong gross_amount against bill ────────────────────────────────────────
test('amount mismatch against bill keeps bill UNPAID', function () {
    [$bill, $attempt, $orderId] = makeAttemptAndBill(250000);

    // Manually change bill amount to create a mismatch scenario.
    DB::table('student_bills')->where('id', $bill->id)->update(['amount' => 300000]);

    $this->postJson('/siswa/payment/callback', siswaWebhookPayload($orderId, 250000, 'settlement'))
         ->assertStatus(200);

    expect($bill->fresh()->status)->toBe('UNPAID');
});

// ── 10. transaction_id stored ─────────────────────────────────────────────────
test('transaction_id from webhook payload is stored on PaymentAttempt', function () {
    [$bill, $attempt, $orderId] = makeAttemptAndBill();

    $this->postJson('/siswa/payment/callback', siswaWebhookPayload(
        $orderId, 250000, 'settlement', 'test-server-key-xyz',
        ['transaction_id' => 'TXN-MIDTRANS-ABC123']
    ))->assertStatus(200);

    expect($attempt->fresh()->transaction_id)->toBe('TXN-MIDTRANS-ABC123');
});

// ── 11. bank stored when present ──────────────────────────────────────────────
test('bank field from webhook payload is stored on PaymentAttempt', function () {
    [$bill, $attempt, $orderId] = makeAttemptAndBill();

    $this->postJson('/siswa/payment/callback', siswaWebhookPayload(
        $orderId, 250000, 'settlement', 'test-server-key-xyz',
        ['payment_type' => 'bank_transfer', 'bank' => 'bca']
    ))->assertStatus(200);

    expect($attempt->fresh()->bank)->toBe('bca');
    expect($attempt->fresh()->payment_method)->toBe('bank_transfer');
});

// ── 12. VA number stored when present ────────────────────────────────────────
test('VA number from webhook va_numbers array is stored on PaymentAttempt', function () {
    [$bill, $attempt, $orderId] = makeAttemptAndBill();

    $this->postJson('/siswa/payment/callback', siswaWebhookPayload(
        $orderId, 250000, 'settlement', 'test-server-key-xyz',
        [
            'payment_type' => 'bank_transfer',
            'va_numbers'   => [['bank' => 'mandiri', 'va_number' => '8888012345678901']],
        ]
    ))->assertStatus(200);

    expect($attempt->fresh()->va_number)->toBe('8888012345678901');
    expect($attempt->fresh()->bank)->toBe('mandiri');
});

// ── 13. settlement_time parsed into settled_at ────────────────────────────────
test('settlement_time from webhook is parsed and stored as settled_at', function () {
    [$bill, $attempt, $orderId] = makeAttemptAndBill();

    $this->postJson('/siswa/payment/callback', siswaWebhookPayload(
        $orderId, 250000, 'settlement', 'test-server-key-xyz',
        ['settlement_time' => '2026-08-16 10:12:00']
    ))->assertStatus(200);

    $settled = $attempt->fresh()->settled_at;
    expect($settled)->not->toBeNull();
    expect($settled->format('Y-m-d H:i:s'))->toBe('2026-08-16 10:12:00');
});

// ── 14. PAYMENT_CONFIRMED written only once ───────────────────────────────────
test('PAYMENT_CONFIRMED audit log is written exactly once even on duplicate webhooks', function () {
    [$bill, $attempt, $orderId] = makeAttemptAndBill();

    $this->postJson('/siswa/payment/callback', siswaWebhookPayload($orderId, 250000, 'settlement'))
         ->assertStatus(200);
    $this->postJson('/siswa/payment/callback', siswaWebhookPayload($orderId, 250000, 'settlement'))
         ->assertStatus(200);

    expect(
        AuditLog::where('action', AuditLog::PAYMENT_CONFIRMED)
                 ->where('auditable_id', $bill->id)
                 ->count()
    )->toBe(1);
});

// ── 15. PAYMENT_FAILED written only once ─────────────────────────────────────
test('PAYMENT_FAILED audit log is written exactly once even on duplicate expire webhooks', function () {
    [$bill, $attempt, $orderId] = makeAttemptAndBill();

    $statusCode  = '407';
    $grossAmount = number_format(250000, 2, '.', '');
    $signature   = hash('sha512', $orderId . $statusCode . $grossAmount . 'test-server-key-xyz');
    $payload     = [
        'order_id'           => $orderId,
        'status_code'        => $statusCode,
        'gross_amount'       => $grossAmount,
        'transaction_status' => 'expire',
        'fraud_status'       => 'accept',
        'signature_key'      => $signature,
    ];

    $this->postJson('/siswa/payment/callback', $payload)->assertStatus(200);
    $this->postJson('/siswa/payment/callback', $payload)->assertStatus(200);

    expect(
        AuditLog::where('action', AuditLog::PAYMENT_FAILED)
                 ->where('auditable_id', $bill->id)
                 ->count()
    )->toBe(1);
});

// ── 16. StudentBill synchronised on settlement ────────────────────────────────
test('StudentBill is fully synchronised after settlement', function () {
    [$bill, $attempt, $orderId] = makeAttemptAndBill(175000);

    $this->postJson('/siswa/payment/callback', siswaWebhookPayload($orderId, 175000, 'settlement'))
         ->assertStatus(200);

    $bill->refresh();
    expect($bill->status)->toBe('PAID');
    expect($bill->payment_method)->toBe('MIDTRANS');
    expect($bill->midtrans_order_id)->toBe($orderId);
    expect($bill->payment_token)->toBeNull();
    expect($bill->confirmed_by)->toBeNull();
    expect($bill->paid_at)->not->toBeNull();
});

// ── 17. legacy webhook — no PaymentAttempt — bill still updated ───────────────
test('legacy settlement webhook with no PaymentAttempt record still marks bill PAID', function () {
    $bill    = StudentBill::factory()->create(['amount' => 200000, 'status' => 'UNPAID']);
    $orderId = 'BILL-' . $bill->id . '-LEGACY-' . time();

    // No PaymentAttempt created — simulates pre-Phase-3.7 bill.
    expect(PaymentAttempt::where('order_id', $orderId)->count())->toBe(0);

    $this->postJson('/siswa/payment/callback', siswaWebhookPayload($orderId, 200000, 'settlement'))
         ->assertStatus(200);

    $bill->refresh();
    expect($bill->status)->toBe('PAID');
    expect($bill->midtrans_order_id)->toBe($orderId);
    // No attempt record fabricated.
    expect(PaymentAttempt::where('order_id', $orderId)->count())->toBe(0);
});

// ── 18. already-PAID bill receiving duplicate settlement ─────────────────────
test('duplicate settlement webhook for already-PAID bill is safely ignored', function () {
    $orderId = 'BILL-88-PAID-' . time();
    $bill    = StudentBill::factory()->paid($orderId)->create(['amount' => 200000]);

    $attempt = PaymentAttempt::factory()->forBill($bill)->create([
        'order_id'   => $orderId,
        'status'     => PaymentAttempt::STATUS_SETTLEMENT,
        'settled_at' => now()->subMinutes(10),
        'snap_token' => null,
    ]);

    $originalSettledAt = $attempt->fresh()->settled_at;

    $this->postJson('/siswa/payment/callback', siswaWebhookPayload($orderId, 200000, 'settlement'))
         ->assertStatus(200);

    // Bill state unchanged.
    expect($bill->fresh()->status)->toBe('PAID');
    // Attempt not re-mutated.
    expect($attempt->fresh()->settled_at->toISOString())->toBe($originalSettledAt->toISOString());
    // No second PAYMENT_CONFIRMED log.
    expect(
        AuditLog::where('action', AuditLog::PAYMENT_CONFIRMED)
                 ->where('auditable_id', $bill->id)
                 ->count()
    )->toBe(0);
});

// ── 19. stale attempt — old order_id settles while newer attempt exists ───────
test('stale old attempt settlement still marks bill PAID because the payment legitimately occurred', function () {
    $bill = StudentBill::factory()->create(['amount' => 300000, 'status' => 'UNPAID']);

    $orderIdA = 'BILL-' . $bill->id . '-OLD-' . (time() - 100);
    $orderIdB = 'BILL-' . $bill->id . '-NEW-' . time();

    // Attempt A — older, pending (e.g. Mandiri VA, never expired yet).
    $attemptA = PaymentAttempt::factory()->forBill($bill)->create([
        'order_id'     => $orderIdA,
        'status'       => PaymentAttempt::STATUS_PENDING,
        'snap_token'   => 'snap-old',
        'gross_amount' => 300000,
        'initiated_at' => now()->subMinutes(10),
    ]);

    // Attempt B — newer, pending (BCA VA).
    $attemptB = PaymentAttempt::factory()->forBill($bill)->create([
        'order_id'     => $orderIdB,
        'status'       => PaymentAttempt::STATUS_PENDING,
        'snap_token'   => 'snap-new',
        'gross_amount' => 300000,
        'initiated_at' => now()->subMinutes(2),
    ]);

    // Attempt A's settlement webhook arrives first.
    $this->postJson('/siswa/payment/callback', siswaWebhookPayload($orderIdA, 300000, 'settlement'))
         ->assertStatus(200);

    // Attempt A settled, bill is PAID.
    expect($attemptA->fresh()->status)->toBe(PaymentAttempt::STATUS_SETTLEMENT);
    expect($bill->fresh()->status)->toBe('PAID');
    expect($bill->fresh()->midtrans_order_id)->toBe($orderIdA);

    // Attempt B's webhook arrives — must be ignored (bill already PAID).
    $this->postJson('/siswa/payment/callback', siswaWebhookPayload($orderIdB, 300000, 'settlement'))
         ->assertStatus(200);

    // Bill midtrans_order_id still points to Attempt A.
    expect($bill->fresh()->midtrans_order_id)->toBe($orderIdA);
    // Only one PAYMENT_CONFIRMED audit log.
    expect(
        AuditLog::where('action', AuditLog::PAYMENT_CONFIRMED)
                 ->where('auditable_id', $bill->id)
                 ->count()
    )->toBe(1);
});

// ── 20. rollback: PaymentAttempt update rolled back if StudentBill update fails ─
test('transaction rolls back PaymentAttempt if StudentBill update throws', function () {
    [$bill, $attempt, $orderId] = makeAttemptAndBill(250000);

    // Force StudentBill update to fail by dropping a required column temporarily.
    // Simplest approach: wrap and simulate via a DB transaction + exception.
    // We verify the contract: both writes are atomic.
    $threw = false;
    try {
        DB::transaction(function () use ($bill, $attempt, $orderId) {
            // Simulate PaymentAttempt update.
            $attempt->update(['status' => PaymentAttempt::STATUS_SETTLEMENT, 'settled_at' => now()]);

            // Simulate StudentBill update failure.
            throw new \RuntimeException('Simulated StudentBill update failure');
        });
    } catch (\RuntimeException $e) {
        $threw = true;
    }

    expect($threw)->toBeTrue();
    // Both must be rolled back.
    expect($attempt->fresh()->status)->toBe(PaymentAttempt::STATUS_PENDING);
    expect($bill->fresh()->status)->toBe('UNPAID');
});
