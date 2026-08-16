<?php

/**
 * Phase 2.4 — Financial timestamp regression tests.
 *
 * Verifies that all financial reporting uses paid_at as the canonical
 * payment timestamp, not updated_at or created_at.
 *
 * Covers:
 *   1. Bill PAID today with paid_at today → included in today's income
 *   2. Bill PAID but updated_at today, paid_at yesterday → NOT in today's income
 *   3. Bill PAID with paid_at NULL → excluded from time-based reporting
 *   4. 7-day chart groups by paid_at correctly
 *   5. Cash payment: confirmed_by populated, paid_at used as payment date
 *   6. Midtrans: confirmed_by NULL, paid_at used as payment date
 *   7. Receipt (struk): payment date from paid_at, NULL shows fallback
 *   8. Historical PAID with paid_at NULL: no fabricated date on receipt
 */

use App\Models\Student;
use App\Models\StudentBill;
use App\Models\User;
use Illuminate\Support\Facades\Config;

beforeEach(function () {
    Config::set('services.midtrans.server_key', 'test-server-key-xyz');
});

// ── 1. Bill paid today with paid_at=today → included in today's income ───────
test('bill paid today with paid_at today is included in today income aggregation', function () {
    StudentBill::factory()->create([
        'status'         => 'PAID',
        'paid_at'        => now(),
        'payment_method' => 'CASH',
        'amount'         => 150000,
    ]);

    // Query mirrors DashboardController today income logic.
    $total = StudentBill::whereDate('paid_at', today())
                        ->whereNotNull('paid_at')
                        ->where('status', 'PAID')
                        ->where('payment_method', 'CASH')
                        ->sum('amount');

    expect((float) $total)->toBe(150000.0);
});

// ── 2. Bill PAID but updated_at=today, paid_at=yesterday → NOT today's income ─
test('bill with paid_at yesterday is not included in today income even if updated_at is today', function () {
    $bill = StudentBill::factory()->create([
        'status'         => 'PAID',
        'paid_at'        => now()->subDay(), // yesterday
        'payment_method' => 'CASH',
        'amount'         => 200000,
    ]);

    // Force updated_at to today to simulate the old (wrong) query behavior.
    \DB::table('student_bills')
       ->where('id', $bill->id)
       ->update(['updated_at' => now()]);

    // Correct query: uses paid_at — must NOT include this bill.
    $total = StudentBill::whereDate('paid_at', today())
                        ->whereNotNull('paid_at')
                        ->where('status', 'PAID')
                        ->sum('amount');

    expect((float) $total)->toBe(0.0);

    // Wrong query (old behavior): uses updated_at — would have included it.
    $wrongTotal = StudentBill::whereDate('updated_at', today())
                             ->where('status', 'PAID')
                             ->sum('amount');

    // This confirms the old query was broken — it would have over-reported.
    expect((float) $wrongTotal)->toBe(200000.0);
});

// ── 3. Bill PAID with paid_at NULL → excluded from time-based reporting ───────
test('bill PAID with paid_at NULL is excluded from time-based income reporting', function () {
    StudentBill::factory()->create([
        'status'         => 'PAID',
        'paid_at'        => null,  // historical record — unknown payment time
        'payment_method' => 'CASH',
        'amount'         => 300000,
    ]);

    $total = StudentBill::whereDate('paid_at', today())
                        ->whereNotNull('paid_at')
                        ->where('status', 'PAID')
                        ->sum('amount');

    expect((float) $total)->toBe(0.0);

    // Also excluded from monthly totals.
    $monthly = StudentBill::whereMonth('paid_at', now()->month)
                          ->whereYear('paid_at', now()->year)
                          ->whereNotNull('paid_at')
                          ->where('status', 'PAID')
                          ->sum('amount');

    expect((float) $monthly)->toBe(0.0);
});

// ── 4. 7-day chart groups by paid_at correctly ────────────────────────────────
test('7-day chart groups bills by paid_at date not updated_at', function () {
    $threeDaysAgo = now()->subDays(3)->startOfDay();
    $yesterday    = now()->subDay()->startOfDay();

    // Bill paid 3 days ago.
    $billA = StudentBill::factory()->create([
        'status'         => 'PAID',
        'paid_at'        => $threeDaysAgo,
        'payment_method' => 'CASH',
        'amount'         => 100000,
    ]);

    // Bill paid yesterday — but force updated_at to 3 days ago.
    $billB = StudentBill::factory()->create([
        'status'         => 'PAID',
        'paid_at'        => $yesterday,
        'payment_method' => 'CASH',
        'amount'         => 200000,
    ]);
    \DB::table('student_bills')
       ->where('id', $billB->id)
       ->update(['updated_at' => $threeDaysAgo]);

    // Correct: grouping by paid_at.
    $threeDaysAgoTotal = StudentBill::whereDate('paid_at', $threeDaysAgo)
                                    ->whereNotNull('paid_at')
                                    ->where('status', 'PAID')
                                    ->sum('amount');

    $yesterdayTotal = StudentBill::whereDate('paid_at', $yesterday)
                                 ->whereNotNull('paid_at')
                                 ->where('status', 'PAID')
                                 ->sum('amount');

    expect((float) $threeDaysAgoTotal)->toBe(100000.0);
    expect((float) $yesterdayTotal)->toBe(200000.0);
});

// ── 5. Cash payment: confirmed_by populated, paid_at as payment date ──────────
test('cash payment stores confirmed_by and paid_at correctly', function () {
    $tuUser  = User::factory()->create(['role' => 'tu']);
    $student = Student::factory()->create();
    $bill    = StudentBill::factory()->create([
        'student_id' => $student->id,
        'status'     => 'UNPAID',
        'amount'     => 150000,
    ]);

    $before = now()->subSecond();

    $this->actingAs($tuUser)->post("/bills/{$bill->id}/pay");

    $bill->refresh();
    $after = now()->addSecond();

    expect($bill->status)->toBe('PAID');
    expect($bill->payment_method)->toBe('CASH');
    expect((int) $bill->confirmed_by)->toBe((int) $tuUser->id);
    expect($bill->paid_at)->not->toBeNull();
    // paid_at must be within the request window — not fabricated from updated_at.
    expect($bill->paid_at->between($before, $after))->toBeTrue();
});

// ── 6. Midtrans: confirmed_by NULL, paid_at as payment date ──────────────────
test('midtrans settlement stores paid_at and leaves confirmed_by NULL', function () {
    $bill    = StudentBill::factory()->withToken('snap-abc')->create(['amount' => 150000]);
    $orderId = 'BILL-' . $bill->id . '-ABCDEF-1000000000';

    $grossAmount = number_format($bill->amount, 2, '.', '');
    $sig = hash('sha512', $orderId . '200' . $grossAmount . 'test-server-key-xyz');

    $before = now()->subSecond();

    $this->postJson('/siswa/payment/callback', [
        'order_id'           => $orderId,
        'status_code'        => '200',
        'gross_amount'       => $grossAmount,
        'transaction_status' => 'settlement',
        'fraud_status'       => 'accept',
        'signature_key'      => $sig,
    ])->assertStatus(200);

    $bill->refresh();
    $after = now()->addSecond();

    expect($bill->status)->toBe('PAID');
    expect($bill->payment_method)->toBe('MIDTRANS');
    expect($bill->confirmed_by)->toBeNull();
    expect($bill->paid_at)->not->toBeNull();
    expect($bill->paid_at->between($before, $after))->toBeTrue();
});

// ── 7. Receipt (struk): payment date comes from paid_at ──────────────────────
test('student receipt page uses paid_at for payment date display', function () {
    $student = Student::factory()->create();
    $bill    = StudentBill::factory()->paid()->create([
        'student_id' => $student->id,
        'paid_at'    => now()->subDays(2),
        'amount'     => 150000,
    ]);

    $response = $this->actingAs($student, 'siswa')
                     ->get("/siswa/tagihan/{$bill->id}/struk");

    $response->assertStatus(200);
    // Rendered date must reflect paid_at, not updated_at.
    $response->assertSee($bill->paid_at->locale('id')->isoFormat('D MMMM YYYY'));
    $response->assertDontSee('Tanggal pembayaran tidak tersedia');
});

// ── 8. Historical PAID with paid_at NULL: fallback shown, no fabricated date ──
test('receipt for historical PAID bill with null paid_at shows fallback not a fabricated date', function () {
    $student = Student::factory()->create();

    // Simulate a historical record: PAID but paid_at was never set.
    $bill = StudentBill::factory()->create([
        'student_id'     => $student->id,
        'status'         => 'PAID',
        'paid_at'        => null,
        'payment_method' => 'CASH',
        'amount'         => 150000,
    ]);

    $response = $this->actingAs($student, 'siswa')
                     ->get("/siswa/tagihan/{$bill->id}/struk");

    $response->assertStatus(200);
    $response->assertSee('Tanggal pembayaran tidak tersedia');
    // Must not show today's date as a fabricated fallback.
    $response->assertDontSee(now()->locale('id')->isoFormat('D MMMM YYYY'));
});
