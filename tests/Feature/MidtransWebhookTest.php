<?php

/**
 * Phase 1.6-A — Midtrans Webhook Security Regression Tests
 *
 * Tests every security gate in PaymentSiswaController::callback():
 *   1.  Invalid signature          → HTTP 200, no bill mutation
 *   2.  Missing signature_key      → HTTP 200, no bill mutation
 *   3.  Missing server key config  → HTTP 200, no bill mutation
 *   4.  Unknown order_id format    → HTTP 200, no bill mutation
 *   5.  Amount mismatch            → HTTP 200, no bill mutation
 *   6.  Valid settlement           → bill PAID, paid_at set, token cleared
 *   7.  Expire notification        → bill UNPAID, token cleared, paid_at NULL
 *   8.  Duplicate settlement       → second call does not mutate bill
 *   9.  Different order_id on PAID → bill unchanged
 */

use App\Models\Student;
use App\Models\StudentBill;
use Illuminate\Support\Facades\Config;

// ── Helpers ────────────────────────────────────────────────────────────────

/**
 * Build a Midtrans-style payload with a valid SHA512 signature.
 *
 * @param  array  $overrides  Fields to override (including 'signature_key' to forge).
 */
function midtransPayload(string $orderId, string $grossAmount, string $serverKey, array $overrides = []): array
{
    $statusCode        = $overrides['status_code']        ?? '200';
    $transactionStatus = $overrides['transaction_status'] ?? 'settlement';
    $fraudStatus       = $overrides['fraud_status']       ?? 'accept';

    $signature = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

    return array_merge([
        'order_id'           => $orderId,
        'status_code'        => $statusCode,
        'gross_amount'       => $grossAmount,
        'transaction_status' => $transactionStatus,
        'fraud_status'       => $fraudStatus,
        'signature_key'      => $signature,
    ], $overrides);
}

// ── Test Suite ─────────────────────────────────────────────────────────────

// Shared setup: real server key in config for all tests that need it.
beforeEach(function () {
    Config::set('services.midtrans.server_key', 'test-server-key-xyz');
});

// ── 1. Invalid signature ────────────────────────────────────────────────────
test('webhook with invalid signature returns 200 and does not mutate bill', function () {
    $bill = StudentBill::factory()->withToken('snap-abc')->create([
        'amount' => 150000,
    ]);
    $orderId = 'BILL-' . $bill->id . '-ABCDEF-1000000000';

    $payload = midtransPayload($orderId, '150000.00', 'test-server-key-xyz', [
        'signature_key' => 'this-is-a-forged-signature',
    ]);

    $response = $this->postJson('/siswa/payment/callback', $payload);

    $response->assertStatus(200);

    $bill->refresh();
    expect($bill->status)->toBe('UNPAID');
    expect($bill->paid_at)->toBeNull();
    expect($bill->payment_token)->toBe('snap-abc');
});

// ── 2. Missing signature_key field ──────────────────────────────────────────
test('webhook with missing signature_key returns 200 and does not mutate bill', function () {
    $bill    = StudentBill::factory()->withToken('snap-abc')->create(['amount' => 150000]);
    $orderId = 'BILL-' . $bill->id . '-ABCDEF-1000000000';

    $payload = midtransPayload($orderId, '150000.00', 'test-server-key-xyz');
    unset($payload['signature_key']); // remove entirely

    $response = $this->postJson('/siswa/payment/callback', $payload);

    $response->assertStatus(200);

    $bill->refresh();
    expect($bill->status)->toBe('UNPAID');
    expect($bill->paid_at)->toBeNull();
});

// ── 3. Server key not configured ────────────────────────────────────────────
test('webhook with missing server key config returns 200 and does not mutate bill', function () {
    Config::set('services.midtrans.server_key', ''); // simulate misconfiguration

    $bill    = StudentBill::factory()->withToken('snap-abc')->create(['amount' => 150000]);
    $orderId = 'BILL-' . $bill->id . '-ABCDEF-1000000000';

    // Even a "valid" signature built with empty key must be rejected.
    $payload = midtransPayload($orderId, '150000.00', '', [
        'transaction_status' => 'settlement',
    ]);

    $response = $this->postJson('/siswa/payment/callback', $payload);

    $response->assertStatus(200);

    $bill->refresh();
    expect($bill->status)->toBe('UNPAID');
    expect($bill->paid_at)->toBeNull();
});

// ── 4. Unknown / unrecognised order_id format ────────────────────────────────
test('webhook with unknown order_id format returns 200 and does not mutate any bill', function () {
    $bill = StudentBill::factory()->create(['amount' => 150000]);

    // order_id that does not match BILL-{id}-* format
    $orderId = 'UNKNOWN-ORDER-12345';
    $payload = midtransPayload($orderId, '150000.00', 'test-server-key-xyz');

    $response = $this->postJson('/siswa/payment/callback', $payload);

    $response->assertStatus(200);

    $bill->refresh();
    expect($bill->status)->toBe('UNPAID');
});

// ── 5. Amount mismatch ──────────────────────────────────────────────────────
test('webhook with gross_amount mismatch returns 200 and does not mark bill PAID', function () {
    $bill    = StudentBill::factory()->withToken('snap-abc')->create(['amount' => 150000]);
    $orderId = 'BILL-' . $bill->id . '-ABCDEF-1000000000';

    // Correct signature but wrong amount (50000 instead of 150000)
    $payload = midtransPayload($orderId, '50000.00', 'test-server-key-xyz', [
        'transaction_status' => 'settlement',
    ]);

    $response = $this->postJson('/siswa/payment/callback', $payload);

    $response->assertStatus(200);

    $bill->refresh();
    expect($bill->status)->toBe('UNPAID');
    expect($bill->paid_at)->toBeNull();
    expect($bill->payment_token)->toBe('snap-abc'); // token untouched
});

// ── 6. Valid settlement ──────────────────────────────────────────────────────
test('valid settlement webhook marks bill PAID with correct fields', function () {
    $bill    = StudentBill::factory()->withToken('snap-abc')->create(['amount' => 150000]);
    $orderId = 'BILL-' . $bill->id . '-ABCDEF-1000000000';

    $payload = midtransPayload($orderId, '150000.00', 'test-server-key-xyz', [
        'transaction_status' => 'settlement',
    ]);

    $response = $this->postJson('/siswa/payment/callback', $payload);

    $response->assertStatus(200);

    $bill->refresh();
    expect($bill->status)->toBe('PAID');
    expect($bill->paid_at)->not->toBeNull();
    expect($bill->payment_method)->toBe('MIDTRANS');
    expect($bill->midtrans_order_id)->toBe($orderId);
    expect($bill->payment_token)->toBeNull(); // cleared after success
});

// ── 6b. Valid capture (credit card) ─────────────────────────────────────────
test('valid capture webhook also marks bill PAID', function () {
    $bill    = StudentBill::factory()->withToken('snap-abc')->create(['amount' => 150000]);
    $orderId = 'BILL-' . $bill->id . '-ABCDEF-1000000001';

    $payload = midtransPayload($orderId, '150000.00', 'test-server-key-xyz', [
        'transaction_status' => 'capture',
        'fraud_status'       => 'accept',
    ]);

    $this->postJson('/siswa/payment/callback', $payload)->assertStatus(200);

    $bill->refresh();
    expect($bill->status)->toBe('PAID');
    expect($bill->paid_at)->not->toBeNull();
});

// ── 7. Expire notification ───────────────────────────────────────────────────
test('expire webhook keeps bill UNPAID and clears payment_token', function () {
    $bill    = StudentBill::factory()->withToken('snap-abc')->create(['amount' => 150000]);
    $orderId = 'BILL-' . $bill->id . '-ABCDEF-1000000000';

    $payload = midtransPayload($orderId, '150000.00', 'test-server-key-xyz', [
        'transaction_status' => 'expire',
        'status_code'        => '407',
    ]);

    $this->postJson('/siswa/payment/callback', $payload)->assertStatus(200);

    $bill->refresh();
    expect($bill->status)->toBe('UNPAID');
    expect($bill->paid_at)->toBeNull();
    expect($bill->payment_token)->toBeNull(); // token cleared so student can retry
});

// ── 7b. Cancel notification ──────────────────────────────────────────────────
test('cancel webhook keeps bill UNPAID and clears payment_token', function () {
    $bill    = StudentBill::factory()->withToken('snap-abc')->create(['amount' => 150000]);
    $orderId = 'BILL-' . $bill->id . '-ABCDEF-1000000000';

    $payload = midtransPayload($orderId, '150000.00', 'test-server-key-xyz', [
        'transaction_status' => 'cancel',
        'status_code'        => '200',
    ]);

    $this->postJson('/siswa/payment/callback', $payload)->assertStatus(200);

    $bill->refresh();
    expect($bill->status)->toBe('UNPAID');
    expect($bill->payment_token)->toBeNull();
});

// ── 8. Duplicate settlement (idempotency) ────────────────────────────────────
test('duplicate settlement webhook does not overwrite paid_at or midtrans_order_id', function () {
    $orderId = 'BILL-999-ABCDEF-1000000000';

    // Bill already settled — simulates the state after first webhook processed.
    $originalPaidAt = now()->subMinutes(5);
    $bill = StudentBill::factory()->create([
        'amount'            => 150000,
        'status'            => 'PAID',
        'paid_at'           => $originalPaidAt,
        'payment_method'    => 'MIDTRANS',
        'midtrans_order_id' => $orderId,
        'payment_token'     => null,
    ]);

    // Second delivery of the same settlement notification.
    $payload = midtransPayload($orderId, '150000.00', 'test-server-key-xyz', [
        'transaction_status' => 'settlement',
    ]);

    $this->postJson('/siswa/payment/callback', $payload)->assertStatus(200);

    $bill->refresh();
    expect($bill->status)->toBe('PAID');
    expect($bill->midtrans_order_id)->toBe($orderId);
    // paid_at must not have been overwritten by the duplicate webhook.
    // Compare as formatted strings — SQLite stores datetime without sub-second
    // precision so timestamp int comparison can drift by 1s.
    expect($bill->paid_at->format('Y-m-d H:i:s'))
        ->toBe($originalPaidAt->format('Y-m-d H:i:s'));
});

// ── 9. Different order_id on already-PAID bill ───────────────────────────────
test('webhook with different order_id on already-PAID bill leaves bill unchanged', function () {
    $existingOrderId = 'BILL-999-ORIGINAL-1000000000';
    $bill = StudentBill::factory()->create([
        'amount'            => 150000,
        'status'            => 'PAID',
        'paid_at'           => now()->subMinutes(10),
        'payment_method'    => 'MIDTRANS',
        'midtrans_order_id' => $existingOrderId,
        'payment_token'     => null,
    ]);

    // A different order_id that still parses to the same bill_id.
    $differentOrderId = 'BILL-' . $bill->id . '-NEWATTEMPT-9999999999';
    $payload = midtransPayload($differentOrderId, '150000.00', 'test-server-key-xyz', [
        'transaction_status' => 'settlement',
    ]);

    $this->postJson('/siswa/payment/callback', $payload)->assertStatus(200);

    $bill->refresh();
    expect($bill->midtrans_order_id)->toBe($existingOrderId); // not replaced
    expect($bill->status)->toBe('PAID');
});
