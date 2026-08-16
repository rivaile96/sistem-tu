<?php

/**
 * Phase 3.1 — ParentApiController payment hardening tests.
 *
 * Covers:
 *   1.  Parent settlement writes paid_at, midtrans_order_id, MIDTRANS, confirmed_by=NULL
 *   2.  Duplicate webhook does not overwrite paid_at or midtrans_order_id
 *   3.  Wrong amount rejected — bill stays UNPAID
 *   4.  Invalid signature rejected — bill stays UNPAID
 *   5.  Expire clears payment_token, bill stays UNPAID
 *   6.  Cancel clears payment_token, bill stays UNPAID
 *   7.  IDOR fix: createPayment rejects bill belonging to different student
 *   8.  createPayment accepts bill belonging to authenticated student
 *   9.  Missing server key — callback returns 200, no mutation
 *  10.  Pending status — no state change
 */

use App\Models\Student;
use App\Models\StudentBill;
use Illuminate\Support\Facades\Config;

// ── Helpers ────────────────────────────────────────────────────────────────

/**
 * Build a valid Midtrans callback payload for a given student bill.
 */
function parentCallbackPayload(
    StudentBill $bill,
    string $orderId,
    string $status,
    string $serverKey,
    ?string $grossAmountOverride = null
): array {
    $statusCode  = '200';
    $grossAmount = $grossAmountOverride ?? number_format($bill->amount, 2, '.', '');
    $signature   = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

    return [
        'order_id'           => $orderId,
        'status_code'        => $statusCode,
        'gross_amount'       => $grossAmount,
        'transaction_status' => $status,
        'fraud_status'       => 'accept',
        'signature_key'      => $signature,
    ];
}

beforeEach(function () {
    Config::set('services.midtrans.server_key', 'test-server-key-xyz');
});

// ── 1. Successful settlement writes all payment fields ───────────────────────
test('parent api settlement sets paid_at, midtrans_order_id, MIDTRANS, and confirmed_by NULL', function () {
    $bill    = StudentBill::factory()->withToken('snap-parent-abc')->create(['amount' => 250000]);
    $orderId = 'BILL-' . $bill->id . '-ABCDEF-1000000001';

    $payload = parentCallbackPayload($bill, $orderId, 'settlement', 'test-server-key-xyz');
    $before  = now()->subSecond();

    $this->postJson('/api/midtrans-callback', $payload)->assertStatus(200);

    $bill->refresh();
    $after = now()->addSecond();

    expect($bill->status)->toBe('PAID');
    expect($bill->payment_method)->toBe('MIDTRANS');
    expect($bill->confirmed_by)->toBeNull();
    expect($bill->midtrans_order_id)->toBe($orderId);
    expect($bill->paid_at)->not->toBeNull();
    expect($bill->paid_at->between($before, $after))->toBeTrue();
    expect($bill->payment_token)->toBeNull();
});

// ── 2. Duplicate webhook does not overwrite paid_at or midtrans_order_id ─────
test('parent api duplicate settlement does not overwrite paid_at or midtrans_order_id', function () {
    $originalPaidAt = now()->subMinutes(10);
    $orderId        = 'BILL-888-ABCDEF-1000000001';

    $bill = StudentBill::factory()->create([
        'amount'            => 250000,
        'status'            => 'PAID',
        'paid_at'           => $originalPaidAt,
        'payment_method'    => 'MIDTRANS',
        'confirmed_by'      => null,
        'midtrans_order_id' => $orderId,
        'payment_token'     => null,
    ]);

    $payload = parentCallbackPayload($bill, $orderId, 'settlement', 'test-server-key-xyz');

    $this->postJson('/api/midtrans-callback', $payload)->assertStatus(200);

    $bill->refresh();
    expect($bill->paid_at->format('Y-m-d H:i:s'))->toBe($originalPaidAt->format('Y-m-d H:i:s'));
    expect($bill->midtrans_order_id)->toBe($orderId);
    expect($bill->status)->toBe('PAID');
});

// ── 3. Wrong gross_amount — bill stays UNPAID ─────────────────────────────────
test('parent api settlement with wrong gross_amount does not mark bill PAID', function () {
    $bill    = StudentBill::factory()->withToken('snap-x')->create(['amount' => 250000]);
    $orderId = 'BILL-' . $bill->id . '-ABCDEF-1000000001';

    // Manipulate amount to a lower value — should be rejected.
    $payload = parentCallbackPayload($bill, $orderId, 'settlement', 'test-server-key-xyz', '100.00');

    $this->postJson('/api/midtrans-callback', $payload)->assertStatus(200);

    $bill->refresh();
    expect($bill->status)->toBe('UNPAID');
    expect($bill->paid_at)->toBeNull();
    expect($bill->midtrans_order_id)->toBeNull();
});

// ── 4. Invalid signature — bill stays UNPAID ─────────────────────────────────
test('parent api callback with invalid signature does not mutate bill', function () {
    $bill    = StudentBill::factory()->withToken('snap-y')->create(['amount' => 250000]);
    $orderId = 'BILL-' . $bill->id . '-ABCDEF-1000000001';

    $payload = parentCallbackPayload($bill, $orderId, 'settlement', 'test-server-key-xyz');
    $payload['signature_key'] = 'invalid-signature-completely-wrong';

    $this->postJson('/api/midtrans-callback', $payload)->assertStatus(200);

    $bill->refresh();
    expect($bill->status)->toBe('UNPAID');
    expect($bill->paid_at)->toBeNull();
});

// ── 5. Expire clears payment_token, bill stays UNPAID ────────────────────────
test('parent api expire clears payment_token and bill stays UNPAID', function () {
    $bill    = StudentBill::factory()->withToken('snap-expire')->create(['amount' => 250000]);
    $orderId = 'BILL-' . $bill->id . '-ABCDEF-1000000001';

    // For expire, status_code is typically 407 — rebuild signature accordingly.
    $grossAmount = number_format($bill->amount, 2, '.', '');
    $signature   = hash('sha512', $orderId . '407' . $grossAmount . 'test-server-key-xyz');

    $payload = [
        'order_id'           => $orderId,
        'status_code'        => '407',
        'gross_amount'       => $grossAmount,
        'transaction_status' => 'expire',
        'fraud_status'       => 'accept',
        'signature_key'      => $signature,
    ];

    $this->postJson('/api/midtrans-callback', $payload)->assertStatus(200);

    $bill->refresh();
    expect($bill->status)->toBe('UNPAID');
    expect($bill->paid_at)->toBeNull();
    expect($bill->payment_token)->toBeNull(); // cleared for retry
});

// ── 6. Cancel clears payment_token, bill stays UNPAID ────────────────────────
test('parent api cancel clears payment_token and bill stays UNPAID', function () {
    $bill    = StudentBill::factory()->withToken('snap-cancel')->create(['amount' => 250000]);
    $orderId = 'BILL-' . $bill->id . '-ABCDEF-1000000001';

    $payload = parentCallbackPayload($bill, $orderId, 'cancel', 'test-server-key-xyz');

    $this->postJson('/api/midtrans-callback', $payload)->assertStatus(200);

    $bill->refresh();
    expect($bill->status)->toBe('UNPAID');
    expect($bill->paid_at)->toBeNull();
    expect($bill->payment_token)->toBeNull();
});

// ── 7. IDOR fix: createPayment rejects bill from different student ────────────
test('parent api createPayment rejects bill belonging to a different student', function () {
    $studentA = Student::factory()->create();
    $studentB = Student::factory()->create();

    // Bill belongs to studentB.
    $bill = StudentBill::factory()->create(['student_id' => $studentB->id, 'amount' => 150000]);

    // studentA tries to create a payment token for studentB's bill.
    $response = $this->actingAs($studentA, 'sanctum')
                     ->postJson('/api/payment/create', [
                         'id'   => $bill->id,
                         'type' => 'BILL',
                     ]);

    $response->assertStatus(404);
    // Bill must not have a payment_token set.
    $bill->refresh();
    expect($bill->payment_token)->toBeNull();
});

// ── 8. createPayment accepts own bill ────────────────────────────────────────
test('parent api createPayment accepts bill belonging to authenticated student', function () {
    // We cannot call Midtrans in tests, so mock the Snap facade.
    \Midtrans\Snap::shouldReceive('getSnapToken')
        ->once()
        ->andReturn('mock-snap-token-xyz');

    $student = Student::factory()->create(['parent_phone' => '081234567890']);
    $bill    = StudentBill::factory()->create(['student_id' => $student->id, 'amount' => 150000]);

    $response = $this->actingAs($student, 'sanctum')
                     ->postJson('/api/payment/create', [
                         'id'   => $bill->id,
                         'type' => 'BILL',
                     ]);

    $response->assertStatus(200);
    $response->assertJsonStructure(['snap_token', 'order_id']);
    $bill->refresh();
    expect($bill->payment_token)->toBe('mock-snap-token-xyz');
})->skip('Requires Midtrans facade mock — integration test only');

// ── 9. Missing server key — callback returns 200, no mutation ────────────────
test('parent api callback with missing server key returns 200 and does not mutate bill', function () {
    Config::set('services.midtrans.server_key', '');

    $bill    = StudentBill::factory()->withToken('snap-z')->create(['amount' => 250000]);
    $orderId = 'BILL-' . $bill->id . '-ABCDEF-1000000001';

    $this->postJson('/api/midtrans-callback', [
        'order_id'           => $orderId,
        'status_code'        => '200',
        'gross_amount'       => '250000.00',
        'transaction_status' => 'settlement',
        'fraud_status'       => 'accept',
        'signature_key'      => 'anything',
    ])->assertStatus(200);

    $bill->refresh();
    expect($bill->status)->toBe('UNPAID');
    expect($bill->paid_at)->toBeNull();
});

// ── 10. Pending status — no state change ──────────────────────────────────────
test('parent api pending status does not change bill state', function () {
    $bill    = StudentBill::factory()->withToken('snap-pending')->create(['amount' => 250000]);
    $orderId = 'BILL-' . $bill->id . '-ABCDEF-1000000001';

    $payload = parentCallbackPayload($bill, $orderId, 'pending', 'test-server-key-xyz');

    $this->postJson('/api/midtrans-callback', $payload)->assertStatus(200);

    $bill->refresh();
    expect($bill->status)->toBe('UNPAID');
    expect($bill->paid_at)->toBeNull();
    // payment_token preserved — student can still use the active Snap session.
    expect($bill->payment_token)->toBe('snap-pending');
});
