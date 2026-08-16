<?php

/**
 * Phase 2.3 — Payment write path integration tests.
 *
 * Covers:
 *   1. Cash payment success: status PAID, paid_at set, confirmed_by = TU user ID
 *   2. Cash payment already PAID: no overwrite, no duplicate stock deduction
 *   3. Cash payment stock failure: rollback, bill UNPAID, stock unchanged
 *   4. Midtrans settlement: confirmed_by NULL, paid_at set, payment_method MIDTRANS
 *   5. Midtrans duplicate webhook: no overwrite
 *   6. Midtrans expire/cancel/deny: UNPAID, no paid_at, no confirmed_by
 */

use App\Models\Student;
use App\Models\StudentBill;
use App\Models\BillItem;
use App\Models\PosItem;
use App\Models\PosBundle;
use App\Models\User;
use Illuminate\Support\Facades\Config;

// ── Helpers ────────────────────────────────────────────────────────────────

function midtransPayloadFor(StudentBill $bill, string $orderId, string $status, string $serverKey): array
{
    $statusCode  = '200';
    $grossAmount = number_format($bill->amount, 2, '.', '');
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

// ══════════════════════════════════════════════════════════════════════════
// A. CASH / MANUAL PAYMENT TESTS
// ══════════════════════════════════════════════════════════════════════════

// ── 1. Cash payment success ──────────────────────────────────────────────
test('cash payment sets status PAID, paid_at, payment_method CASH, and confirmed_by', function () {
    $tuUser  = User::factory()->create(['role' => 'tu']);
    $student = Student::factory()->create();
    $bill    = StudentBill::factory()->create([
        'student_id' => $student->id,
        'status'     => 'UNPAID',
        'amount'     => 150000,
    ]);

    $response = $this->actingAs($tuUser)
                     ->post("/bills/{$bill->id}/pay");

    $response->assertRedirect();

    $bill->refresh();
    expect($bill->status)->toBe('PAID');
    expect($bill->paid_at)->not->toBeNull();
    expect($bill->payment_method)->toBe('CASH');
    expect((int) $bill->confirmed_by)->toBe((int) $tuUser->id);
});

// ── 2. Cash payment already PAID — no overwrite ──────────────────────────
test('cash payment on already-PAID bill does not overwrite payment fields', function () {
    $tuUser  = User::factory()->create(['role' => 'tu']);
    $student = Student::factory()->create();

    $originalConfirmer = User::factory()->create();
    $originalPaidAt    = now()->subHour();

    $bill = StudentBill::factory()->create([
        'student_id'   => $student->id,
        'status'       => 'PAID',
        'paid_at'      => $originalPaidAt,
        'payment_method' => 'CASH',
        'confirmed_by' => $originalConfirmer->id,
        'amount'       => 150000,
    ]);

    $this->actingAs($tuUser)->post("/bills/{$bill->id}/pay");

    $bill->refresh();
    // Must not be overwritten.
    expect($bill->paid_at->format('Y-m-d H:i:s'))->toBe($originalPaidAt->format('Y-m-d H:i:s'));
    expect((int) $bill->confirmed_by)->toBe((int) $originalConfirmer->id);
    expect($bill->payment_method)->toBe('CASH');
});

// ── 3. Cash payment with insufficient stock — full rollback ──────────────
test('cash payment with insufficient stock rolls back: bill stays UNPAID, stock unchanged', function () {
    $tuUser  = User::factory()->create(['role' => 'tu']);
    $student = Student::factory()->create();

    // Create a POS item with only 1 unit in stock.
    $posItem = PosItem::create([
        'name'     => 'Buku Paket',
        'price'    => 50000,
        'stock'    => 1,
        'category' => 'Buku',
    ]);

    // Create a bundle requiring 2 units of that item.
    $bundle = PosBundle::create([
        'name'      => 'Paket Buku',
        'price'     => 100000,
        'is_active' => true,
    ]);

    // PosBundle hasMany items via pos_bundle_items table.
    \DB::table('pos_bundle_items')->insert([
        'pos_bundle_id' => $bundle->id,
        'pos_item_id'   => $posItem->id,
        'quantity'      => 2,  // requires 2 × bill_item.quantity
        'created_at'    => now(),
        'updated_at'    => now(),
    ]);

    $bill = StudentBill::factory()->create([
        'student_id' => $student->id,
        'status'     => 'UNPAID',
        'amount'     => 100000,
    ]);

    // Bill item references the bundle, quantity 1 → needs 2 × 1 = 2 units.
    BillItem::create([
        'student_bill_id' => $bill->id,
        'pos_bundle_id'   => $bundle->id,
        'item_name'       => 'Paket Buku',
        'quantity'        => 1,
        'price'           => 100000,
        'subtotal'        => 100000,
    ]);

    $this->actingAs($tuUser)->post("/bills/{$bill->id}/pay");

    $bill->refresh();
    $posItem->refresh();

    expect($bill->status)->toBe('UNPAID');
    expect($bill->paid_at)->toBeNull();
    expect($bill->confirmed_by)->toBeNull();
    expect($posItem->stock)->toBe(1); // unchanged
});

// ── 4. Cash payment with sufficient stock — stock decremented ────────────
test('cash payment with sufficient stock decrements stock correctly', function () {
    $tuUser  = User::factory()->create(['role' => 'tu']);
    $student = Student::factory()->create();

    $posItem = PosItem::create([
        'name'     => 'Seragam',
        'price'    => 150000,
        'stock'    => 5,
        'category' => 'Seragam',
    ]);

    $bundle = PosBundle::create([
        'name'      => 'Paket Seragam',
        'price'     => 150000,
        'is_active' => true,
    ]);

    \DB::table('pos_bundle_items')->insert([
        'pos_bundle_id' => $bundle->id,
        'pos_item_id'   => $posItem->id,
        'quantity'      => 2,
        'created_at'    => now(),
        'updated_at'    => now(),
    ]);

    $bill = StudentBill::factory()->create([
        'student_id' => $student->id,
        'status'     => 'UNPAID',
        'amount'     => 150000,
    ]);

    BillItem::create([
        'student_bill_id' => $bill->id,
        'pos_bundle_id'   => $bundle->id,
        'item_name'       => 'Paket Seragam',
        'quantity'        => 1,   // 1 × bundle(2) = 2 units
        'price'           => 150000,
        'subtotal'        => 150000,
    ]);

    $this->actingAs($tuUser)->post("/bills/{$bill->id}/pay");

    $bill->refresh();
    $posItem->refresh();

    expect($bill->status)->toBe('PAID');
    expect($posItem->stock)->toBe(3); // 5 - 2 = 3
});

// ══════════════════════════════════════════════════════════════════════════
// B. MIDTRANS SETTLEMENT TESTS
// ══════════════════════════════════════════════════════════════════════════

// ── 5. Midtrans settlement sets confirmed_by = NULL ──────────────────────
test('midtrans settlement sets confirmed_by to NULL', function () {
    $bill    = StudentBill::factory()->withToken('snap-abc')->create(['amount' => 150000]);
    $orderId = 'BILL-' . $bill->id . '-ABCDEF-1000000000';

    $payload = midtransPayloadFor($bill, $orderId, 'settlement', 'test-server-key-xyz');

    $this->postJson('/siswa/payment/callback', $payload)->assertStatus(200);

    $bill->refresh();
    expect($bill->status)->toBe('PAID');
    expect($bill->paid_at)->not->toBeNull();
    expect($bill->payment_method)->toBe('MIDTRANS');
    expect($bill->confirmed_by)->toBeNull();   // gateway, not operator
    expect($bill->midtrans_order_id)->toBe($orderId);
    expect($bill->payment_token)->toBeNull();
});

// ── 6. Midtrans duplicate webhook — no overwrite ─────────────────────────
test('midtrans duplicate settlement does not overwrite confirmed_by or paid_at', function () {
    $originalPaidAt = now()->subMinutes(5);
    $orderId        = 'BILL-999-ABCDEF-1000000000';

    $bill = StudentBill::factory()->create([
        'amount'            => 150000,
        'status'            => 'PAID',
        'paid_at'           => $originalPaidAt,
        'payment_method'    => 'MIDTRANS',
        'confirmed_by'      => null,
        'midtrans_order_id' => $orderId,
        'payment_token'     => null,
    ]);

    $payload = midtransPayloadFor($bill, $orderId, 'settlement', 'test-server-key-xyz');

    $this->postJson('/siswa/payment/callback', $payload)->assertStatus(200);

    $bill->refresh();
    expect($bill->paid_at->format('Y-m-d H:i:s'))->toBe($originalPaidAt->format('Y-m-d H:i:s'));
    expect($bill->confirmed_by)->toBeNull();
    expect($bill->status)->toBe('PAID');
});

// ── 7. Midtrans expire — no paid_at, no confirmed_by ────────────────────
test('midtrans expire leaves bill UNPAID with no paid_at and no confirmed_by', function () {
    $bill    = StudentBill::factory()->withToken('snap-abc')->create(['amount' => 150000]);
    $orderId = 'BILL-' . $bill->id . '-ABCDEF-1000000000';

    $payload = midtransPayloadFor($bill, $orderId, 'expire', 'test-server-key-xyz');
    // Override status_code for expire
    $payload['status_code'] = '407';
    $payload['signature_key'] = hash('sha512',
        $payload['order_id'] . '407' . $payload['gross_amount'] . 'test-server-key-xyz'
    );

    $this->postJson('/siswa/payment/callback', $payload)->assertStatus(200);

    $bill->refresh();
    expect($bill->status)->toBe('UNPAID');
    expect($bill->paid_at)->toBeNull();
    expect($bill->confirmed_by)->toBeNull();
    expect($bill->payment_token)->toBeNull(); // token cleared for retry
});

// ── 8. Midtrans deny — no paid_at, no confirmed_by ──────────────────────
test('midtrans deny leaves bill UNPAID with no paid_at and no confirmed_by', function () {
    $bill    = StudentBill::factory()->withToken('snap-abc')->create(['amount' => 150000]);
    $orderId = 'BILL-' . $bill->id . '-ABCDEF-1000000000';

    $payload = midtransPayloadFor($bill, $orderId, 'deny', 'test-server-key-xyz');

    $this->postJson('/siswa/payment/callback', $payload)->assertStatus(200);

    $bill->refresh();
    expect($bill->status)->toBe('UNPAID');
    expect($bill->paid_at)->toBeNull();
    expect($bill->confirmed_by)->toBeNull();
});
