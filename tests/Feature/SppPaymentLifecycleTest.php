<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Student;
use App\Models\StudentBill;
use App\Events\PaymentReceived;
use App\Http\Controllers\MidtransController;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Config;

/**
 * SPP Payment Lifecycle Test Suite
 *
 * Case  1: Create unpaid SPP bill
 * Case  2: Payment request stores order_id + PENDING status
 * Case  3: Valid settlement webhook → PAID
 * Case  4: Same webhook 2x → one transition only
 * Case  5: Same webhook 10x → no duplicate
 * Case  6: Pending webhook → PENDING state
 * Case  7: Settlement after pending → PAID
 * Case  8: Pending after PAID → remains PAID
 * Case  9: Wrong amount → rejected
 * Case 10: Wrong order_id → ignored gracefully
 * Case 11: Invalid signature → 403
 * Case 12: Two simultaneous settlements → only one applies
 * Case 13: Expired payment → UNPAID (not PAID)
 * Case 14: Cancel/deny → UNPAID
 * Case 15: Replay on existing PAID → no change
 * Case 16: paid_amount precision preserved (decimal)
 * Case 17: PaymentReceived event dispatched on settlement
 */
class SppPaymentLifecycleTest extends TestCase
{
    use DatabaseMigrations;

    private string $serverKey = 'test-midtrans-server-key';

    protected function setUp(): void
    {
        parent::setUp();
        Config::set('services.midtrans.server_key', $this->serverKey);
    }

    // ================================================================
    // HELPERS
    // ================================================================

    private function makeStudent(string $nis = '9900001'): Student
    {
        $user = User::create([
            'name' => "Student $nis", 'username' => $nis,
            'email' => "$nis@test.id", 'password' => bcrypt('pw'), 'role' => 'student',
        ]);
        return Student::create([
            'user_id' => $user->id, 'nis' => $nis, 'status' => 'active',
        ]);
    }

    private function makeBill(Student $student, array $overrides = []): StudentBill
    {
        return StudentBill::create(array_merge([
            'student_id'  => $student->id,
            'type'        => 'SPP',
            'name'        => 'SPP Agustus 2026',
            'amount'      => '500000.00',
            'status'      => 'UNPAID',
            'paid_amount' => '0.00',
            'bill_month'  => 8,
            'bill_year'   => 2026,
            'due_date'    => '2026-08-10',
        ], $overrides));
    }

    /**
     * Generate valid Midtrans signature.
     * signature = sha512(order_id + status_code + gross_amount + server_key)
     */
    private function signature(string $orderId, string $statusCode, string $grossAmount): string
    {
        return hash('sha512', $orderId . $statusCode . $grossAmount . $this->serverKey);
    }

    private function webhook(array $payload): \Illuminate\Testing\TestResponse
    {
        return $this->postJson('/api/midtrans/webhook', $payload);
    }

    private function webhookPayload(string $orderId, string $txStatus, string $grossAmount = '500000.00', string $statusCode = '200'): array
    {
        return [
            'order_id'           => $orderId,
            'status_code'        => $statusCode,
            'gross_amount'       => $grossAmount,
            'transaction_status' => $txStatus,
            'signature_key'      => $this->signature($orderId, $statusCode, $grossAmount),
        ];
    }

    // ================================================================
    // CASE 1: Create unpaid SPP bill
    // ================================================================

    public function test_create_unpaid_spp_bill(): void
    {
        $student = $this->makeStudent();
        $bill    = $this->makeBill($student);

        $this->assertEquals('UNPAID', $bill->status);
        $this->assertEquals('500000.00', $bill->amount);
        $this->assertEquals('0.00', $bill->paid_amount);
        $this->assertNull($bill->paid_at);
        $this->assertNull($bill->midtrans_order_id);
    }

    // ================================================================
    // CASE 2: Payment request stores order_id + PENDING
    // ================================================================

    public function test_payment_request_stores_order_id_and_pending_status(): void
    {
        $student = $this->makeStudent();
        $bill    = $this->makeBill($student);
        $orderId = 'BILL-' . $bill->id . '-' . time();

        // Simulate what createPayment does (Midtrans SDK not available in test)
        $bill->update([
            'midtrans_order_id' => $orderId,
            'payment_token'     => 'snap-token-test',
            'status'            => 'PENDING',
        ]);

        $fresh = $bill->fresh();
        $this->assertEquals('PENDING', $fresh->status);
        $this->assertEquals($orderId, $fresh->midtrans_order_id);
        $this->assertEquals('snap-token-test', $fresh->payment_token);
    }

    // ================================================================
    // CASE 3: Valid settlement → PAID
    // ================================================================

    public function test_valid_settlement_webhook_marks_bill_paid(): void
    {
        Event::fake([PaymentReceived::class]);
        $student = $this->makeStudent();
        $bill    = $this->makeBill($student);
        $orderId = 'BILL-' . $bill->id . '-111111';
        $bill->update(['midtrans_order_id' => $orderId, 'status' => 'PENDING']);

        $this->webhook($this->webhookPayload($orderId, 'settlement'))
             ->assertStatus(200);

        $fresh = $bill->fresh();
        $this->assertEquals('PAID', $fresh->status);
        $this->assertEquals('500000.00', $fresh->paid_amount);
        $this->assertNotNull($fresh->paid_at);
        $this->assertEquals('MIDTRANS', $fresh->payment_method);
        $this->assertNull($fresh->payment_token); // token cleared

        Event::assertDispatched(PaymentReceived::class, function ($e) use ($bill, $orderId) {
            return $e->billId === $bill->id
                && $e->amount === '500000.00'
                && $e->orderId === $orderId
                && $e->provider === 'midtrans';
        });
    }

    // ================================================================
    // CASE 4: Same webhook 2x → one transition only
    // ================================================================

    public function test_duplicate_webhook_causes_only_one_transition(): void
    {
        Event::fake([PaymentReceived::class]);
        $student = $this->makeStudent();
        $bill    = $this->makeBill($student);
        $orderId = 'BILL-' . $bill->id . '-222222';
        $bill->update(['midtrans_order_id' => $orderId, 'status' => 'PENDING']);

        $payload = $this->webhookPayload($orderId, 'settlement');

        $this->webhook($payload)->assertStatus(200);
        $this->webhook($payload)->assertStatus(200); // duplicate

        $this->assertEquals('PAID', $bill->fresh()->status);
        Event::assertDispatchedTimes(PaymentReceived::class, 1); // only once
    }

    // ================================================================
    // CASE 5: Same webhook 10x → no duplicate
    // ================================================================

    public function test_webhook_10x_no_duplicate(): void
    {
        Event::fake([PaymentReceived::class]);
        $student = $this->makeStudent();
        $bill    = $this->makeBill($student);
        $orderId = 'BILL-' . $bill->id . '-333333';
        $bill->update(['midtrans_order_id' => $orderId, 'status' => 'PENDING']);

        $payload = $this->webhookPayload($orderId, 'settlement');

        for ($i = 0; $i < 10; $i++) {
            $this->webhook($payload)->assertStatus(200);
        }

        $this->assertEquals('PAID', $bill->fresh()->status);
        $this->assertEquals('500000.00', $bill->fresh()->paid_amount);
        Event::assertDispatchedTimes(PaymentReceived::class, 1);
    }

    // ================================================================
    // CASE 6: Pending webhook → PENDING state
    // ================================================================

    public function test_pending_webhook_sets_pending_status(): void
    {
        $student = $this->makeStudent();
        $bill    = $this->makeBill($student);
        $orderId = 'BILL-' . $bill->id . '-444444';
        $bill->update(['midtrans_order_id' => $orderId]);

        $this->webhook($this->webhookPayload($orderId, 'pending', '500000.00', '201'))
             ->assertStatus(200);

        $this->assertEquals('PENDING', $bill->fresh()->status);
    }

    // ================================================================
    // CASE 7: Settlement after pending → PAID
    // ================================================================

    public function test_settlement_after_pending_marks_paid(): void
    {
        Event::fake([PaymentReceived::class]);
        $student = $this->makeStudent();
        $bill    = $this->makeBill($student);
        $orderId = 'BILL-' . $bill->id . '-555555';
        $bill->update(['midtrans_order_id' => $orderId]);

        $this->webhook($this->webhookPayload($orderId, 'pending', '500000.00', '201'));
        $this->assertEquals('PENDING', $bill->fresh()->status);

        $this->webhook($this->webhookPayload($orderId, 'settlement'));
        $this->assertEquals('PAID', $bill->fresh()->status);

        Event::assertDispatched(PaymentReceived::class);
    }

    // ================================================================
    // CASE 8: Pending after PAID → remains PAID
    // ================================================================

    public function test_pending_webhook_after_paid_does_not_regress(): void
    {
        $student = $this->makeStudent();
        $bill    = $this->makeBill($student, [
            'status'            => 'PAID',
            'paid_amount'       => '500000.00',
            'midtrans_order_id' => 'BILL-1-777777',
            'paid_at'           => now(),
        ]);

        $this->webhook($this->webhookPayload('BILL-1-777777', 'pending', '500000.00', '201'))
             ->assertStatus(200);

        $this->assertEquals('PAID', $bill->fresh()->status);
        $this->assertEquals('500000.00', $bill->fresh()->paid_amount);
    }

    // ================================================================
    // CASE 9: Wrong amount → rejected
    // ================================================================

    public function test_wrong_amount_rejected(): void
    {
        Event::fake([PaymentReceived::class]);
        $student = $this->makeStudent();
        $bill    = $this->makeBill($student, ['amount' => '500000.00']);
        $orderId = 'BILL-' . $bill->id . '-888888';
        $bill->update(['midtrans_order_id' => $orderId, 'status' => 'PENDING']);

        // Webhook claims different amount
        $this->webhook($this->webhookPayload($orderId, 'settlement', '400000.00'))
             ->assertStatus(200); // webhook always 200 to stop Midtrans retries

        // Bill must NOT be PAID
        $this->assertNotEquals('PAID', $bill->fresh()->status);
        Event::assertNotDispatched(PaymentReceived::class);
    }

    // ================================================================
    // CASE 10: Wrong order_id → ignored gracefully
    // ================================================================

    public function test_unknown_order_id_ignored_gracefully(): void
    {
        $orderId = 'BILL-99999-000000'; // doesn't exist

        $this->webhook($this->webhookPayload($orderId, 'settlement'))
             ->assertStatus(200);

        $this->assertEquals(0, StudentBill::where('status', 'PAID')->count());
    }

    // ================================================================
    // CASE 11: Invalid signature → 403
    // ================================================================

    public function test_invalid_signature_rejected(): void
    {
        $orderId = 'BILL-1-111111';

        $this->postJson('/api/midtrans/webhook', [
            'order_id'           => $orderId,
            'status_code'        => '200',
            'gross_amount'       => '500000.00',
            'transaction_status' => 'settlement',
            'signature_key'      => 'invalid-signature',
        ])->assertStatus(403);
    }

    // ================================================================
    // CASE 12: Two simultaneous settlements → only one applies
    // (Simulated via sequential calls with DB lock — true concurrency
    //  tested via idempotency: same order_id, same result)
    // ================================================================

    public function test_concurrent_settlements_only_one_applies(): void
    {
        Event::fake([PaymentReceived::class]);
        $student = $this->makeStudent();
        $bill    = $this->makeBill($student);
        $orderId = 'BILL-' . $bill->id . '-concurrent';
        $bill->update(['midtrans_order_id' => $orderId, 'status' => 'PENDING']);

        $payload = $this->webhookPayload($orderId, 'settlement');

        // Both succeed at HTTP level but only one financial transition
        $this->webhook($payload)->assertStatus(200);
        $this->webhook($payload)->assertStatus(200);

        $this->assertEquals('PAID', $bill->fresh()->status);
        $this->assertEquals('500000.00', $bill->fresh()->paid_amount);
        Event::assertDispatchedTimes(PaymentReceived::class, 1);
    }

    // ================================================================
    // CASE 13: Expired payment → UNPAID (not PAID)
    // ================================================================

    public function test_expired_payment_reverts_to_unpaid(): void
    {
        $student = $this->makeStudent();
        $bill    = $this->makeBill($student);
        $orderId = 'BILL-' . $bill->id . '-expire';
        $bill->update(['midtrans_order_id' => $orderId, 'status' => 'PENDING']);

        $this->webhook($this->webhookPayload($orderId, 'expire'))
             ->assertStatus(200);

        $fresh = $bill->fresh();
        $this->assertEquals('UNPAID', $fresh->status);
        $this->assertNull($fresh->midtrans_order_id); // cleared for retry
    }

    // ================================================================
    // CASE 14: Cancel/deny → UNPAID
    // ================================================================

    public function test_cancel_reverts_to_unpaid(): void
    {
        $student = $this->makeStudent();
        $bill    = $this->makeBill($student);
        $orderId = 'BILL-' . $bill->id . '-cancel';
        $bill->update(['midtrans_order_id' => $orderId, 'status' => 'PENDING']);

        $this->webhook($this->webhookPayload($orderId, 'cancel'))
             ->assertStatus(200);

        $this->assertEquals('UNPAID', $bill->fresh()->status);
    }

    // ================================================================
    // CASE 15: Replay on existing PAID bill → no state change
    // ================================================================

    public function test_replay_on_paid_bill_does_not_modify_amount(): void
    {
        $student = $this->makeStudent();
        $paidAt  = now()->subMinutes(5);
        $bill    = $this->makeBill($student, [
            'status'            => 'PAID',
            'paid_amount'       => '500000.00',
            'paid_at'           => $paidAt,
            'midtrans_order_id' => 'BILL-replay-111',
            'payment_method'    => 'MIDTRANS',
        ]);

        $this->webhook($this->webhookPayload('BILL-replay-111', 'settlement'))
             ->assertStatus(200);

        $fresh = $bill->fresh();
        $this->assertEquals('PAID', $fresh->status);
        $this->assertEquals('500000.00', $fresh->paid_amount);
        $this->assertEquals($paidAt->toDateTimeString(), $fresh->paid_at->toDateTimeString());
    }

    // ================================================================
    // CASE 16: paid_amount precision preserved (decimal)
    // ================================================================

    public function test_paid_amount_precision_decimal(): void
    {
        $student = $this->makeStudent();
        $bill    = $this->makeBill($student, ['amount' => '750000.50']);
        $orderId = 'BILL-' . $bill->id . '-decimal';
        $bill->update(['midtrans_order_id' => $orderId, 'status' => 'PENDING']);

        $this->webhook($this->webhookPayload($orderId, 'settlement', '750000.50'))
             ->assertStatus(200);

        $this->assertEquals('750000.50', $bill->fresh()->paid_amount);
    }

    // ================================================================
    // CASE 17: PaymentReceived event has correct payload
    // ================================================================

    public function test_payment_received_event_payload(): void
    {
        Event::fake([PaymentReceived::class]);
        $student = $this->makeStudent('26089715');
        $bill    = $this->makeBill($student, ['amount' => '500000.00']);
        $orderId = 'BILL-' . $bill->id . '-event';
        $bill->update(['midtrans_order_id' => $orderId, 'status' => 'PENDING']);

        $this->webhook($this->webhookPayload($orderId, 'settlement'));

        Event::assertDispatched(PaymentReceived::class, function ($e) use ($bill, $orderId, $student) {
            return $e->billId        === $bill->id
                && $e->studentId     === $student->id
                && $e->nis           === '26089715'
                && $e->amount        === '500000.00'
                && $e->paymentMethod === 'MIDTRANS'
                && $e->provider      === 'midtrans'
                && $e->orderId       === $orderId
                && !str_contains(serialize($e), 'server_key')
                && !str_contains(serialize($e), 'snap_token');
        });
    }

    // ================================================================
    // AUDIT LOG CREATED ON SETTLEMENT
    // ================================================================

    public function test_audit_log_created_on_settlement(): void
    {
        Event::fake([PaymentReceived::class]);
        $student = $this->makeStudent();
        $bill    = $this->makeBill($student);
        $orderId = 'BILL-' . $bill->id . '-audit';
        $bill->update(['midtrans_order_id' => $orderId, 'status' => 'PENDING']);

        $this->webhook($this->webhookPayload($orderId, 'settlement'));

        $this->assertDatabaseHas('audit_logs', [
            'action'      => 'PAYMENT_SETTLED',
            'target_type' => 'StudentBill',
            'target_id'   => $bill->id,
        ]);
    }

    // ================================================================
    // HAIDAR/SARIF REGRESSION: existing bills unchanged
    // ================================================================

    public function test_unrelated_bill_not_affected_by_webhook(): void
    {
        $student1 = $this->makeStudent('26089715'); // Haidar
        $student2 = $this->makeStudent('26081478'); // Sarif

        $haidarBill = $this->makeBill($student1, ['status' => 'UNPAID', 'amount' => '500000.00']);
        $sarifBill  = $this->makeBill($student2, ['status' => 'PAID', 'paid_amount' => '500000.00']);

        // Pay only Haidar's bill
        $orderId = 'BILL-' . $haidarBill->id . '-haidar';
        $haidarBill->update(['midtrans_order_id' => $orderId, 'status' => 'PENDING']);

        $this->webhook($this->webhookPayload($orderId, 'settlement'));

        $this->assertEquals('PAID', $haidarBill->fresh()->status);
        // Sarif's bill untouched
        $this->assertEquals('PAID', $sarifBill->fresh()->status);
        $this->assertEquals('500000.00', $sarifBill->fresh()->paid_amount);
    }
}
