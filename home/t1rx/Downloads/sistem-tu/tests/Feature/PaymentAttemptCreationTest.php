<?php

/**
 * Phase 3.7B — PaymentAttempt creation tests.
 *
 * Covers:
 *   1.  Web payment initiation creates a PaymentAttempt
 *   2.  API payment initiation creates a PaymentAttempt
 *   3.  PaymentAttempt contains correct student_bill_id
 *   4.  PaymentAttempt contains exact bill amount
 *   5.  PaymentAttempt stores snap_token
 *   6.  PaymentAttempt source = WEB for siswa flow
 *   7.  PaymentAttempt source = API for parent flow
 *   8.  student_bills.payment_token matches active attempt snap_token
 *   9.  Midtrans failure creates no PaymentAttempt
 *  10.  Rapid duplicate initiation reuses existing pending attempt
 *  11.  IDOR protection: siswa cannot initiate payment for another student's bill
 *  12.  PAID bill cannot create a new attempt (web)
 *  13.  PAID bill cannot create a new attempt (API)
 *  14.  PAYMENT_ATTEMPT_CREATED audit log written for web initiation
 *  15.  PAYMENT_ATTEMPT_CREATED audit log written for API initiation
 */

use App\Models\AuditLog;
use App\Models\PaymentAttempt;
use App\Models\Student;
use App\Models\StudentBill;
use App\Models\User;
use Illuminate\Support\Facades\Config;

// ── Helpers ───────────────────────────────────────────────────────────────────

/** Create a siswa + bill pair, authenticate via siswa guard. */
function siswaWithBill(array $billAttrs = []): array
{
    $student = Student::factory()->create(['status' => 'active']);
    $bill    = StudentBill::factory()->create(array_merge([
        'student_id' => $student->id,
        'status'     => 'UNPAID',
        'amount'     => 300000,
    ], $billAttrs));

    return [$student, $bill];
}

/** Authenticate a student via the siswa guard and return the test instance. */
function actingAsSiswa($test, $student)
{
    return $test->actingAs($student, 'siswa');
}

/** Create a parent API token for a student. */
function parentToken(Student $student): string
{
    return $student->createToken('ParentApp')->plainTextToken;
}

/** Mock Snap::getSnapToken to return a predictable token. */
function mockSnap(string $token = 'snap-test-token-abc123'): void
{
    \Midtrans\Snap::shouldReceive('getSnapToken')
                  ->once()
                  ->andReturn($token);
}

/** Mock Snap::getSnapToken to throw an exception. */
function mockSnapFailure(): void
{
    \Midtrans\Snap::shouldReceive('getSnapToken')
                  ->once()
                  ->andThrow(new \Exception('Midtrans connection timeout'));
}

beforeEach(function () {
    Config::set('services.midtrans.server_key', 'test-server-key-xyz');
    Config::set('services.midtrans.client_key', 'test-client-key-xyz');
});

// ── 1. Web initiation creates PaymentAttempt ──────────────────────────────────
test('web payment initiation creates a PaymentAttempt record', function () {
    [$student, $bill] = siswaWithBill();
    mockSnap('snap-web-token-001');

    actingAsSiswa($this, $student)
        ->postJson("/siswa/payment/token/{$bill->id}")
        ->assertStatus(200);

    expect(PaymentAttempt::where('student_bill_id', $bill->id)->count())->toBe(1);
});

// ── 2. API initiation creates PaymentAttempt ──────────────────────────────────
test('API payment initiation creates a PaymentAttempt record', function () {
    $student = Student::factory()->create();
    $bill    = StudentBill::factory()->create([
        'student_id' => $student->id,
        'status'     => 'UNPAID',
        'amount'     => 200000,
    ]);
    mockSnap('snap-api-token-001');

    $this->withToken(parentToken($student))
         ->postJson('/api/parent/payment/create', [
             'id'   => $bill->id,
             'type' => 'BILL',
         ])
         ->assertStatus(200);

    expect(PaymentAttempt::where('student_bill_id', $bill->id)->count())->toBe(1);
});

// ── 3. Correct student_bill_id ────────────────────────────────────────────────
test('PaymentAttempt has correct student_bill_id', function () {
    [$student, $bill] = siswaWithBill();
    mockSnap('snap-token-billid');

    actingAsSiswa($this, $student)
        ->postJson("/siswa/payment/token/{$bill->id}")
        ->assertStatus(200);

    $attempt = PaymentAttempt::where('student_bill_id', $bill->id)->first();
    expect((int) $attempt->student_bill_id)->toBe((int) $bill->id);
});

// ── 4. Exact bill amount stored ───────────────────────────────────────────────
test('PaymentAttempt gross_amount matches the exact bill amount', function () {
    [$student, $bill] = siswaWithBill(['amount' => 3450000]);
    mockSnap('snap-token-amount');

    actingAsSiswa($this, $student)
        ->postJson("/siswa/payment/token/{$bill->id}")
        ->assertStatus(200);

    $attempt = PaymentAttempt::where('student_bill_id', $bill->id)->first();
    expect((float) $attempt->gross_amount)->toBe(3450000.0);
});

// ── 5. snap_token stored on attempt ───────────────────────────────────────────
test('PaymentAttempt stores the snap_token returned by Midtrans', function () {
    [$student, $bill] = siswaWithBill();
    mockSnap('snap-unique-token-xyz');

    actingAsSiswa($this, $student)
        ->postJson("/siswa/payment/token/{$bill->id}")
        ->assertStatus(200);

    $attempt = PaymentAttempt::where('student_bill_id', $bill->id)->first();
    expect($attempt->snap_token)->toBe('snap-unique-token-xyz');
});

// ── 6. Source = WEB for siswa flow ────────────────────────────────────────────
test('PaymentAttempt source is WEB for the siswa payment flow', function () {
    [$student, $bill] = siswaWithBill();
    mockSnap('snap-web-source');

    actingAsSiswa($this, $student)
        ->postJson("/siswa/payment/token/{$bill->id}")
        ->assertStatus(200);

    $attempt = PaymentAttempt::where('student_bill_id', $bill->id)->first();
    expect($attempt->source)->toBe(PaymentAttempt::SOURCE_WEB);
});

// ── 7. Source = API for parent flow ───────────────────────────────────────────
test('PaymentAttempt source is API for the parent API payment flow', function () {
    $student = Student::factory()->create();
    $bill    = StudentBill::factory()->create([
        'student_id' => $student->id,
        'status'     => 'UNPAID',
        'amount'     => 200000,
    ]);
    mockSnap('snap-api-source');

    $this->withToken(parentToken($student))
         ->postJson('/api/parent/payment/create', [
             'id'   => $bill->id,
             'type' => 'BILL',
         ])
         ->assertStatus(200);

    $attempt = PaymentAttempt::where('student_bill_id', $bill->id)->first();
    expect($attempt->source)->toBe(PaymentAttempt::SOURCE_API);
});

// ── 8. student_bills.payment_token matches attempt snap_token ─────────────────
test('student_bills payment_token matches the active PaymentAttempt snap_token', function () {
    [$student, $bill] = siswaWithBill();
    mockSnap('snap-consistency-check');

    actingAsSiswa($this, $student)
        ->postJson("/siswa/payment/token/{$bill->id}")
        ->assertStatus(200);

    $attempt = PaymentAttempt::where('student_bill_id', $bill->id)->first();
    $bill->refresh();

    expect($bill->payment_token)->toBe($attempt->snap_token);
    expect($bill->payment_token)->toBe('snap-consistency-check');
});

// ── 9. Midtrans failure creates no PaymentAttempt ─────────────────────────────
test('Midtrans failure does not create a PaymentAttempt record', function () {
    [$student, $bill] = siswaWithBill();
    mockSnapFailure();

    actingAsSiswa($this, $student)
        ->postJson("/siswa/payment/token/{$bill->id}")
        ->assertStatus(500);

    expect(PaymentAttempt::where('student_bill_id', $bill->id)->count())->toBe(0);

    // Bill state must be unchanged.
    $bill->refresh();
    expect($bill->payment_token)->toBeNull();
});

// ── 10. Rapid duplicate reuses existing pending attempt ───────────────────────
test('rapid duplicate initiation reuses the existing pending attempt without calling Midtrans again', function () {
    [$student, $bill] = siswaWithBill();

    // Pre-create a pending attempt — simulates first call already succeeded.
    $existing = PaymentAttempt::factory()->forBill($bill)->create([
        'snap_token'   => 'snap-existing-pending',
        'order_id'     => 'BILL-' . $bill->id . '-EXIST1-9999999999',
        'status'       => PaymentAttempt::STATUS_PENDING,
        'gross_amount' => $bill->amount,
        'initiated_at' => now()->subSeconds(5),
        'source'       => PaymentAttempt::SOURCE_WEB,
    ]);

    // Snap must NOT be called — reuse path should short-circuit.
    \Midtrans\Snap::shouldReceive('getSnapToken')->never();

    $response = actingAsSiswa($this, $student)
        ->postJson("/siswa/payment/token/{$bill->id}")
        ->assertStatus(200);

    // Only one attempt should exist.
    expect(PaymentAttempt::where('student_bill_id', $bill->id)->count())->toBe(1);

    // Response snap_token must be the existing one.
    expect($response->json('snap_token'))->toBe('snap-existing-pending');
    expect($response->json('order_id'))->toBe($existing->order_id);
});

// ── 11. IDOR: siswa cannot pay another student's bill ─────────────────────────
test('siswa cannot initiate payment for a bill belonging to another student', function () {
    $studentA = Student::factory()->create();
    $studentB = Student::factory()->create();
    $billB    = StudentBill::factory()->create([
        'student_id' => $studentB->id,
        'status'     => 'UNPAID',
    ]);

    \Midtrans\Snap::shouldReceive('getSnapToken')->never();

    actingAsSiswa($this, $studentA)
        ->postJson("/siswa/payment/token/{$billB->id}")
        ->assertStatus(403);

    expect(PaymentAttempt::where('student_bill_id', $billB->id)->count())->toBe(0);
});

// ── 12. PAID bill rejected — web ──────────────────────────────────────────────
test('web flow rejects payment initiation for an already paid bill', function () {
    $student = Student::factory()->create();
    $bill    = StudentBill::factory()->paid()->create(['student_id' => $student->id]);

    \Midtrans\Snap::shouldReceive('getSnapToken')->never();

    actingAsSiswa($this, $student)
        ->postJson("/siswa/payment/token/{$bill->id}")
        ->assertStatus(400);

    expect(PaymentAttempt::where('student_bill_id', $bill->id)->count())->toBe(0);
});

// ── 13. PAID bill rejected — API ──────────────────────────────────────────────
test('API flow rejects payment initiation for an already paid bill', function () {
    $student = Student::factory()->create();
    $bill    = StudentBill::factory()->paid()->create(['student_id' => $student->id]);

    \Midtrans\Snap::shouldReceive('getSnapToken')->never();

    $this->withToken(parentToken($student))
         ->postJson('/api/parent/payment/create', [
             'id'   => $bill->id,
             'type' => 'BILL',
         ])
         ->assertStatus(400);

    expect(PaymentAttempt::where('student_bill_id', $bill->id)->count())->toBe(0);
});

// ── 14. Audit log written for web initiation ──────────────────────────────────
test('web payment initiation writes a PAYMENT_ATTEMPT_CREATED audit log entry', function () {
    [$student, $bill] = siswaWithBill();
    mockSnap('snap-audit-web');

    actingAsSiswa($this, $student)
        ->postJson("/siswa/payment/token/{$bill->id}")
        ->assertStatus(200);

    $attempt = PaymentAttempt::where('student_bill_id', $bill->id)->first();

    $log = AuditLog::where('action', AuditLog::PAYMENT_ATTEMPT_CREATED)
                   ->where('auditable_type', 'PaymentAttempt')
                   ->where('auditable_id', $attempt->id)
                   ->first();

    expect($log)->not->toBeNull();
    expect($log->source)->toBe(AuditLog::SOURCE_WEB);
    expect($log->new_values['order_id'])->toBe($attempt->order_id);
    expect($log->new_values['gross_amount'])->toBe((string) $attempt->gross_amount);
    // snap_token must NOT be stored in audit log.
    expect(isset($log->new_values['snap_token']))->toBeFalse();
});

// ── 15. Audit log written for API initiation ──────────────────────────────────
test('API payment initiation writes a PAYMENT_ATTEMPT_CREATED audit log entry', function () {
    $student = Student::factory()->create();
    $bill    = StudentBill::factory()->create([
        'student_id' => $student->id,
        'status'     => 'UNPAID',
        'amount'     => 200000,
    ]);
    mockSnap('snap-audit-api');

    $this->withToken(parentToken($student))
         ->postJson('/api/parent/payment/create', [
             'id'   => $bill->id,
             'type' => 'BILL',
         ])
         ->assertStatus(200);

    $attempt = PaymentAttempt::where('student_bill_id', $bill->id)->first();

    $log = AuditLog::where('action', AuditLog::PAYMENT_ATTEMPT_CREATED)
                   ->where('auditable_type', 'PaymentAttempt')
                   ->where('auditable_id', $attempt->id)
                   ->first();

    expect($log)->not->toBeNull();
    expect($log->source)->toBe(AuditLog::SOURCE_API);
    expect($log->new_values['source'])->toBe('API');
    expect(isset($log->new_values['snap_token']))->toBeFalse();
});
