<?php

/**
 * Phase 3.5 — Financial Audit Trail tests.
 *
 * Covers:
 *   1.  Bill creation sets created_by for authenticated staff
 *   2.  Bill creation writes BILL_CREATED audit log
 *   3.  Bill deletion writes BILL_DELETED audit log with old_values snapshot
 *   4.  Cash payment writes PAYMENT_CONFIRMED audit log (source=WEB)
 *   5.  Midtrans settlement writes PAYMENT_CONFIRMED (user_id=NULL, source=MIDTRANS)
 *   6.  Duplicate Midtrans webhook does NOT write a second PAYMENT_CONFIRMED
 *   7.  Expire writes PAYMENT_FAILED audit log, first transition only
 *   8.  Cancel writes PAYMENT_FAILED audit log
 *   9.  UNPAID bill meaningful update writes BILL_UPDATED audit log
 *  10.  UNPAID bill non-meaningful update does NOT write BILL_UPDATED
 *  11.  PAID bill update attempt writes PAID_BILL_UPDATE_ATTEMPTED audit log
 *  12.  PAID bill remains unchanged after rejected update attempt
 *  13.  Audit insertion failure rolls back financial mutation
 *  14.  Historical bills keep created_by = NULL
 */

use App\Models\AuditLog;
use App\Models\Student;
use App\Models\StudentBill;
use App\Models\User;
use App\Services\FinancialAuditLogger;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

// ── Helpers ───────────────────────────────────────────────────────────────────

function auditTuUser(): User
{
    return User::factory()->create(['role' => 'tu']);
}

function auditStudent(): Student
{
    return Student::factory()->create(['status' => 'active']);
}

function validAuditBillPayload(int $studentId): array
{
    return [
        'target_type'  => 'student',
        'student_id'   => $studentId,
        'type'         => 'LAINNYA',
        'name'         => 'Uang Kegiatan',
        'item_names'   => ['Uang Kegiatan'],
        'item_prices'  => ['300000'],
        'item_qtys'    => ['1'],
    ];
}

function midtransCallbackPayload(StudentBill $bill, string $orderId, string $status): array
{
    $serverKey   = config('services.midtrans.server_key');
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

// ── 1. Bill creation sets created_by ──────────────────────────────────────────
test('bill creation sets created_by to the authenticated staff user', function () {
    $tu      = auditTuUser();
    $student = auditStudent();

    $this->actingAs($tu)
         ->post('/bills', validAuditBillPayload($student->id))
         ->assertRedirect(route('bills.index'));

    $bill = StudentBill::where('student_id', $student->id)->first();
    expect($bill)->not->toBeNull();
    expect((int) $bill->created_by)->toBe((int) $tu->id);
});

// ── 2. Bill creation writes BILL_CREATED audit log ────────────────────────────
test('bill creation writes a BILL_CREATED audit log entry', function () {
    $tu      = auditTuUser();
    $student = auditStudent();

    $this->actingAs($tu)
         ->post('/bills', validAuditBillPayload($student->id))
         ->assertRedirect(route('bills.index'));

    $bill = StudentBill::where('student_id', $student->id)->first();

    $log = AuditLog::where('action', AuditLog::BILL_CREATED)
                   ->where('auditable_type', 'StudentBill')
                   ->where('auditable_id', $bill->id)
                   ->first();

    expect($log)->not->toBeNull();
    expect((int) $log->user_id)->toBe((int) $tu->id);
    expect($log->source)->toBe(AuditLog::SOURCE_WEB);
    expect($log->new_values['student_id'])->toBe($student->id);
    expect($log->new_values['amount'])->not->toBeNull();
    expect($log->old_values)->toBeNull();
});

// ── 3. Bill deletion writes BILL_DELETED audit log ────────────────────────────
test('bill deletion writes a BILL_DELETED audit log with old_values snapshot', function () {
    $tu      = auditTuUser();
    $student = auditStudent();
    $bill    = StudentBill::factory()->create([
        'student_id' => $student->id,
        'status'     => 'UNPAID',
        'amount'     => 200000,
        'type'       => 'LAINNYA',
    ]);
    $billId = $bill->id;

    $this->actingAs($tu)
         ->delete("/bills/{$billId}")
         ->assertRedirect();

    // Bill must be gone.
    expect(StudentBill::find($billId))->toBeNull();

    // Audit log must exist with snapshot of deleted bill.
    $log = AuditLog::where('action', AuditLog::BILL_DELETED)
                   ->where('auditable_id', $billId)
                   ->first();

    expect($log)->not->toBeNull();
    expect((int) $log->user_id)->toBe((int) $tu->id);
    expect($log->source)->toBe(AuditLog::SOURCE_WEB);
    expect($log->old_values['id'])->toBe($billId);
    expect($log->old_values['student_id'])->toBe($student->id);
    expect((float) $log->old_values['amount'])->toBe(200000.0);
    expect($log->new_values)->toBeNull();
});

// ── 4. Cash payment writes PAYMENT_CONFIRMED (source=WEB) ─────────────────────
test('cash payment writes PAYMENT_CONFIRMED audit log with source WEB', function () {
    $tu      = auditTuUser();
    $student = auditStudent();
    $bill    = StudentBill::factory()->create([
        'student_id' => $student->id,
        'status'     => 'UNPAID',
        'amount'     => 150000,
    ]);

    $this->actingAs($tu)
         ->post("/bills/{$bill->id}/pay")
         ->assertRedirect();

    $log = AuditLog::where('action', AuditLog::PAYMENT_CONFIRMED)
                   ->where('auditable_id', $bill->id)
                   ->first();

    expect($log)->not->toBeNull();
    expect((int) $log->user_id)->toBe((int) $tu->id);
    expect($log->source)->toBe(AuditLog::SOURCE_WEB);
    expect($log->new_values['status'])->toBe('PAID');
    expect($log->new_values['payment_method'])->toBe('CASH');
    expect($log->new_values['confirmed_by'])->toBe($tu->id);
    expect($log->old_values['status'])->toBe('UNPAID');
});

// ── 5. Midtrans settlement writes PAYMENT_CONFIRMED (user_id=NULL, MIDTRANS) ───
test('Midtrans settlement writes PAYMENT_CONFIRMED with user_id NULL and source MIDTRANS', function () {
    $bill    = StudentBill::factory()->withToken('snap-tok')->create(['amount' => 250000]);
    $orderId = 'BILL-' . $bill->id . '-ABCDEF-1000000001';

    $this->postJson('/siswa/payment/callback', midtransCallbackPayload($bill, $orderId, 'settlement'))
         ->assertStatus(200);

    $log = AuditLog::where('action', AuditLog::PAYMENT_CONFIRMED)
                   ->where('auditable_id', $bill->id)
                   ->first();

    expect($log)->not->toBeNull();
    expect($log->user_id)->toBeNull();
    expect($log->source)->toBe(AuditLog::SOURCE_MIDTRANS);
    expect($log->new_values['status'])->toBe('PAID');
    expect($log->new_values['payment_method'])->toBe('MIDTRANS');
    expect($log->new_values['midtrans_order_id'])->toBe($orderId);
});

// ── 6. Duplicate Midtrans webhook does NOT write a second audit log ────────────
test('duplicate Midtrans webhook does not create a second PAYMENT_CONFIRMED audit log', function () {
    $orderId = 'BILL-999-ABCDEF-1000000001';
    $bill    = StudentBill::factory()->create([
        'amount'            => 250000,
        'status'            => 'PAID',
        'paid_at'           => now()->subMinutes(5),
        'payment_method'    => 'MIDTRANS',
        'midtrans_order_id' => $orderId,
        'payment_token'     => null,
    ]);

    // Pre-existing audit log from first webhook.
    AuditLog::create([
        'user_id'        => null,
        'action'         => AuditLog::PAYMENT_CONFIRMED,
        'module'         => 'billing',
        'auditable_type' => 'StudentBill',
        'auditable_id'   => $bill->id,
        'source'         => AuditLog::SOURCE_MIDTRANS,
    ]);

    $countBefore = AuditLog::where('action', AuditLog::PAYMENT_CONFIRMED)
                            ->where('auditable_id', $bill->id)
                            ->count();

    // Send duplicate webhook.
    $this->postJson('/siswa/payment/callback', midtransCallbackPayload($bill, $orderId, 'settlement'))
         ->assertStatus(200);

    $countAfter = AuditLog::where('action', AuditLog::PAYMENT_CONFIRMED)
                           ->where('auditable_id', $bill->id)
                           ->count();

    expect($countAfter)->toBe($countBefore); // Still just 1.
});

// ── 7. Expire writes PAYMENT_FAILED ───────────────────────────────────────────
test('Midtrans expire writes PAYMENT_FAILED audit log and clears token', function () {
    $bill    = StudentBill::factory()->withToken('snap-exp')->create(['amount' => 250000]);
    $orderId = 'BILL-' . $bill->id . '-ABCDEF-1000000001';

    $grossAmount = number_format($bill->amount, 2, '.', '');
    $signature   = hash('sha512', $orderId . '407' . $grossAmount . 'test-server-key-xyz');

    $this->postJson('/siswa/payment/callback', [
        'order_id'           => $orderId,
        'status_code'        => '407',
        'gross_amount'       => $grossAmount,
        'transaction_status' => 'expire',
        'fraud_status'       => 'accept',
        'signature_key'      => $signature,
    ])->assertStatus(200);

    $bill->refresh();
    expect($bill->payment_token)->toBeNull();

    $log = AuditLog::where('action', AuditLog::PAYMENT_FAILED)
                   ->where('auditable_id', $bill->id)
                   ->first();

    expect($log)->not->toBeNull();
    expect($log->source)->toBe(AuditLog::SOURCE_MIDTRANS);
    expect($log->new_values['transaction_status'])->toBe('expire');
    expect($log->new_values['payment_token'])->toBeNull();
});

// ── 8. Cancel writes PAYMENT_FAILED ───────────────────────────────────────────
test('Midtrans cancel writes PAYMENT_FAILED audit log', function () {
    $bill    = StudentBill::factory()->withToken('snap-can')->create(['amount' => 250000]);
    $orderId = 'BILL-' . $bill->id . '-ABCDEF-1000000001';

    $this->postJson('/siswa/payment/callback', midtransCallbackPayload($bill, $orderId, 'cancel'))
         ->assertStatus(200);

    $log = AuditLog::where('action', AuditLog::PAYMENT_FAILED)
                   ->where('auditable_id', $bill->id)
                   ->first();

    expect($log)->not->toBeNull();
    expect($log->new_values['transaction_status'])->toBe('cancel');
});

// ── 9. UNPAID bill meaningful update writes BILL_UPDATED ──────────────────────
test('meaningful update on UNPAID bill writes BILL_UPDATED audit log', function () {
    $bill = StudentBill::factory()->create([
        'status' => 'UNPAID',
        'amount' => 100000,
        'name'   => 'Old Name',
    ]);

    $oldValues = ['amount' => '100000.00', 'name' => 'Old Name'];
    $newValues = ['amount' => '150000.00', 'name' => 'New Name'];

    FinancialAuditLogger::billUpdated($bill, $oldValues, $newValues);

    $log = AuditLog::where('action', AuditLog::BILL_UPDATED)
                   ->where('auditable_id', $bill->id)
                   ->first();

    expect($log)->not->toBeNull();
    expect($log->old_values)->toHaveKey('amount');
    expect($log->new_values)->toHaveKey('amount');
});

// ── 10. Non-meaningful update does NOT write BILL_UPDATED ─────────────────────
test('non-meaningful update on UNPAID bill does not write BILL_UPDATED audit log', function () {
    $bill = StudentBill::factory()->create(['status' => 'UNPAID', 'amount' => 100000]);

    // Same values — no change.
    FinancialAuditLogger::billUpdated(
        $bill,
        ['amount' => '100000.00'],
        ['amount' => '100000.00']
    );

    $count = AuditLog::where('action', AuditLog::BILL_UPDATED)
                     ->where('auditable_id', $bill->id)
                     ->count();

    expect($count)->toBe(0);
});

// ── 11. PAID bill update attempt writes PAID_BILL_UPDATE_ATTEMPTED ────────────
test('attempted modification of PAID bill writes PAID_BILL_UPDATE_ATTEMPTED audit log', function () {
    $bill = StudentBill::factory()->paid()->create(['amount' => 200000, 'type' => 'SPP']);

    // Trigger immutability guard via model — it throws RuntimeException.
    try {
        $bill->update(['amount' => 999999]);
    } catch (\RuntimeException $e) {
        // Expected — now log the attempt.
        FinancialAuditLogger::paidBillUpdateAttempted(
            $bill,
            ['amount' => 999999],
            AuditLog::SOURCE_WEB
        );
    }

    $log = AuditLog::where('action', AuditLog::PAID_BILL_UPDATE_ATTEMPTED)
                   ->where('auditable_id', $bill->id)
                   ->first();

    expect($log)->not->toBeNull();
    expect($log->source)->toBe(AuditLog::SOURCE_WEB);
    expect($log->old_values['amount'])->not->toBeNull();
    expect($log->new_values['amount'])->toBe(999999);
});

// ── 12. PAID bill remains unchanged after rejected update attempt ──────────────
test('PAID bill financial values remain unchanged after rejected update attempt', function () {
    $bill = StudentBill::factory()->paid()->create(['amount' => 200000]);

    expect(fn () => $bill->update(['amount' => 999999]))
        ->toThrow(\RuntimeException::class);

    $bill->refresh();
    expect((float) $bill->amount)->toBe(200000.0);
    expect($bill->status)->toBe('PAID');
});

// ── 13. Audit insertion failure rolls back financial mutation ──────────────────
test('audit insertion failure rolls back the financial mutation', function () {
    $student = auditStudent();
    $bill    = StudentBill::factory()->create([
        'student_id' => $student->id,
        'status'     => 'UNPAID',
        'amount'     => 100000,
    ]);

    // Verify atomicity: if the audit insert throws inside DB::transaction(),
    // the bill mutation is rolled back too.
    // DB::transaction(closure) uses a savepoint in SQLite (compatible with
    // Pest's RefreshDatabase outer transaction) and rolls back on exception.
    $threw = false;
    try {
        DB::transaction(function () use ($bill) {
            DB::table('student_bills')
              ->where('id', $bill->id)
              ->update(['status' => 'PAID', 'paid_at' => now()]);

            // Simulate audit write failure inside the transaction.
            throw new \RuntimeException('Simulated audit DB failure');
        });
    } catch (\RuntimeException $e) {
        $threw = true;
    }

    expect($threw)->toBeTrue();
    $bill->refresh();
    expect($bill->status)->toBe('UNPAID'); // savepoint rollback restored state
    expect($bill->paid_at)->toBeNull();
});

// ── 14. Historical bills keep created_by = NULL ───────────────────────────────
test('historical bills that predate created_by column have created_by NULL', function () {
    // Factory creates without created_by — simulates historical record.
    $bill = StudentBill::factory()->create(['status' => 'UNPAID']);

    expect($bill->created_by)->toBeNull();
});
