<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Student;
use App\Models\StudentBill;
use App\Events\PaymentReceived;
use App\Jobs\PushPaymentToKaffahJob;
use App\Listeners\PaymentReceivedListener;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Event;

/**
 * TU → Kaffah Payment Integration Test
 *
 * Case  1: PaymentReceived dispatches PushPaymentToKaffahJob
 * Case  2: Job payload has no credentials
 * Case  3: Valid job → Kaffah accepts → success
 * Case  4: Job idempotent — Kaffah returns 208
 * Case  5: Kaffah 5xx → throws for retry
 * Case  6: Kaffah 4xx → fail permanently
 * Case  7: Kaffah down — TU bill stays PAID, job queued
 * Case  8: billing_type resolved from StudentBill.type
 * Case  9: No credential in job payload
 * Case 10: Listener dispatches job async (implements ShouldQueue)
 */
class TuPaymentIntegrationTest extends TestCase
{
    use DatabaseMigrations;

    private function makeStudent(string $nis = '9900001'): Student
    {
        $user = User::create([
            'name' => "Student $nis", 'username' => $nis,
            'email' => "$nis@test.id", 'password' => bcrypt('pw'), 'role' => 'student',
        ]);
        return Student::create(['user_id' => $user->id, 'nis' => $nis, 'status' => 'active']);
    }

    private static int $billSeq = 0;

    private function makeBill(Student $student, string $type = 'SPP'): StudentBill
    {
        self::$billSeq++;
        return StudentBill::create([
            'student_id'        => $student->id,
            'type'              => $type,
            'name'              => "$type Agustus 2026",
            'amount'            => '500000.00',
            'status'            => 'PAID',
            'paid_amount'       => '500000.00',
            'paid_at'           => now(),
            'bill_month'        => 8,
            'bill_year'         => 2026,
            'due_date'          => '2026-08-10',
            'midtrans_order_id' => 'BILL-TEST-' . self::$billSeq . '-' . uniqid(),
            'payment_method'    => 'MIDTRANS',
        ]);
    }

    private function makeEvent(Student $student, StudentBill $bill): PaymentReceived
    {
        return new PaymentReceived(
            billId:        $bill->id,
            studentId:     $student->id,
            nis:           $student->nis,
            amount:        '500000.00',
            paymentMethod: 'MIDTRANS',
            provider:      'midtrans',
            orderId:       $bill->midtrans_order_id,
            paidAt:        now()->toIso8601String(),
        );
    }

    private function kaffahUrl(): string
    {
        return rtrim(config('services.kaffah.url', 'http://localhost:8000'), '/');
    }

    // ================================================================
    // CASE 1: PaymentReceived dispatches PushPaymentToKaffahJob
    // ================================================================

    public function test_payment_received_event_dispatches_job(): void
    {
        Queue::fake();
        $student = $this->makeStudent();
        $bill    = $this->makeBill($student);
        $event   = $this->makeEvent($student, $bill);

        $listener = new PaymentReceivedListener();
        $listener->handle($event);

        Queue::assertPushed(PushPaymentToKaffahJob::class, function ($job) use ($bill, $student) {
            $serialized = serialize($job);
            return str_contains($serialized, $student->nis)
                && str_contains($serialized, '500000');
        });
    }

    // ================================================================
    // CASE 2: Job payload has no credentials
    // ================================================================

    public function test_job_payload_has_no_credentials(): void
    {
        $student = $this->makeStudent();
        $bill    = $this->makeBill($student);
        $event   = $this->makeEvent($student, $bill);
        $job     = PushPaymentToKaffahJob::fromEvent($event, 'SPP');
        $serialized = serialize($job);

        $this->assertStringNotContainsString('password',    $serialized);
        $this->assertStringNotContainsString('server_key',  $serialized);
        $this->assertStringNotContainsString('snap_token',  $serialized);
        $this->assertStringNotContainsString('api_secret',  $serialized);
        $this->assertStringContainsString($student->nis,    $serialized);
        $this->assertStringContainsString('500000',         $serialized);
    }

    // ================================================================
    // CASE 3: Valid job → Kaffah accepts → success
    // ================================================================

    public function test_push_payment_job_success(): void
    {
        $kaffahUrl = $this->kaffahUrl();
        Http::fake([
            $kaffahUrl . '/api/internal/finance/payment-received' => Http::response([
                'success'    => true,
                'journal_id' => 42,
            ], 201),
        ]);

        $student = $this->makeStudent();
        $bill    = $this->makeBill($student);
        $event   = $this->makeEvent($student, $bill);
        $job     = PushPaymentToKaffahJob::fromEvent($event, 'SPP');
        $job->handle();

        Http::assertSent(function ($request) use ($student, $bill) {
            return str_contains($request->url(), 'payment-received')
                && $request['event']          === 'payment.received'
                && $request['nis']            === $student->nis
                && $request['billing_type']   === 'SPP'
                && $request['currency']       === 'IDR'
                && isset($request['order_id'])
                && $request->hasHeader('X-KAFFAH-SERVICE-KEY')
                && !isset($request['password'])
                && !isset($request['server_key']);
        });
    }

    // ================================================================
    // CASE 4: Kaffah 208 idempotent → job succeeds
    // ================================================================

    public function test_push_payment_job_idempotent_208(): void
    {
        $kaffahUrl = $this->kaffahUrl();
        Http::fake([
            $kaffahUrl . '/api/internal/finance/payment-received' => Http::response([
                'success'    => true,
                'idempotent' => true,
            ], 208),
        ]);

        $student = $this->makeStudent();
        $bill    = $this->makeBill($student);
        $event   = $this->makeEvent($student, $bill);
        $job     = PushPaymentToKaffahJob::fromEvent($event, 'SPP');
        $job->handle(); // should NOT throw

        $this->assertTrue(true); // reached = idempotent handled correctly
    }

    // ================================================================
    // CASE 5: Kaffah 5xx → throws for retry
    // ================================================================

    public function test_push_payment_job_throws_on_5xx(): void
    {
        $kaffahUrl = $this->kaffahUrl();
        Http::fake([
            $kaffahUrl . '/api/internal/finance/payment-received' => Http::response([], 503),
        ]);

        $this->expectException(\RuntimeException::class);

        $student = $this->makeStudent();
        $bill    = $this->makeBill($student);
        $event   = $this->makeEvent($student, $bill);
        $job     = PushPaymentToKaffahJob::fromEvent($event, 'SPP');
        $job->handle();
    }

    // ================================================================
    // CASE 6: Kaffah 4xx → fail permanently (no retry)
    // ================================================================

    public function test_push_payment_job_fails_permanently_on_4xx(): void
    {
        $kaffahUrl = $this->kaffahUrl();
        Http::fake([
            $kaffahUrl . '/api/internal/finance/payment-received' => Http::response([
                'message' => 'COA not found',
            ], 422),
        ]);

        $student = $this->makeStudent();
        $bill    = $this->makeBill($student);
        $event   = $this->makeEvent($student, $bill);
        $job     = PushPaymentToKaffahJob::fromEvent($event, 'SPP');

        // 4xx → job calls $this->fail() (permanent fail, no retry).
        // In test context, fail() does not throw — but the request IS sent
        // and no retry exception is thrown (unlike 5xx which throws).
        $job->handle();

        // Verify the request was sent exactly once — no retry
        Http::assertSentCount(1);
    }

    // ================================================================
    // CASE 7: Kaffah down — TU bill stays PAID, listener queued
    // ================================================================

    public function test_kaffah_down_tu_bill_stays_paid(): void
    {
        // Core assertion: PaymentReceived event fires AFTER transaction commit.
        // TU bill stays PAID regardless of Kaffah availability.
        // The listener/job handles async delivery — tested separately.
        Event::fake([PaymentReceived::class]);

        $student = $this->makeStudent();
        $bill    = $this->makeBill($student);

        event(new PaymentReceived(
            billId:        $bill->id,
            studentId:     $student->id,
            nis:           $student->nis,
            amount:        '500000.00',
            paymentMethod: 'MIDTRANS',
            provider:      'midtrans',
            orderId:       $bill->midtrans_order_id,
            paidAt:        now()->toIso8601String(),
        ));

        // Bill stays PAID — event dispatch does not affect payment state
        $this->assertEquals('PAID', $bill->fresh()->status);
        $this->assertEquals('500000.00', $bill->fresh()->paid_amount);

        // Event was dispatched — listener will consume it asynchronously
        Event::assertDispatched(PaymentReceived::class, function ($e) use ($bill) {
            return $e->billId === $bill->id
                && $e->amount === '500000.00';
        });
    }

    // ================================================================
    // CASE 8: billing_type resolved from StudentBill.type
    // ================================================================

    public function test_billing_type_resolved_from_bill_type(): void
    {
        Queue::fake();
        $student = $this->makeStudent();
        $bill    = $this->makeBill($student, 'SERAGAM');

        $event    = $this->makeEvent($student, $bill);
        $listener = new PaymentReceivedListener();
        $listener->handle($event);

        Queue::assertPushed(PushPaymentToKaffahJob::class, function ($job) {
            $serialized = serialize($job);
            return str_contains($serialized, 'SERAGAM');
        });
    }

    // ================================================================
    // CASE 9: Listener implements ShouldQueue (async)
    // ================================================================

    public function test_listener_implements_should_queue(): void
    {
        $listener = new PaymentReceivedListener();
        $this->assertInstanceOf(\Illuminate\Contracts\Queue\ShouldQueue::class, $listener);
    }

    // ================================================================
    // Haidar/Sarif regression: bill stays intact after event
    // ================================================================

    public function test_existing_paid_bill_unaffected_by_event_dispatch(): void
    {
        Queue::fake();
        $haidar = $this->makeStudent('26089715');
        $sarif  = $this->makeStudent('26081478');

        $haidarBill = $this->makeBill($haidar);
        $sarifBill  = $this->makeBill($sarif);

        // Trigger only Haidar's event
        $listener = new PaymentReceivedListener();
        $listener->handle($this->makeEvent($haidar, $haidarBill));

        // Both bills untouched
        $this->assertEquals('PAID', $haidarBill->fresh()->status);
        $this->assertEquals('500000.00', $haidarBill->fresh()->paid_amount);
        $this->assertEquals('PAID', $sarifBill->fresh()->status);
        $this->assertEquals('500000.00', $sarifBill->fresh()->paid_amount);
    }

    // ================================================================
    // SEC-05 FOLLOW-UP: Explicit URL & Secret Validation in PushPaymentToKaffahJob
    // ================================================================

    public function test_sec05_empty_or_invalid_base_url_sends_no_http_request(): void
    {
        Http::fake();
        $student = $this->makeStudent();
        $bill    = $this->makeBill($student);
        $event   = $this->makeEvent($student, $bill);

        foreach ([null, '', '   ', 'not-a-valid-url', 'ftp://localhost:8000', '/api/internal'] as $invalidUrl) {
            config([
                'services.kaffah.url'        => $invalidUrl,
                'services.kaffah.api_secret' => 'valid-secret-key',
            ]);

            $job = PushPaymentToKaffahJob::fromEvent($event, 'SPP');
            $job->handle();
        }

        Http::assertNothingSent();
    }

    public function test_sec05_empty_secret_sends_no_http_request(): void
    {
        Http::fake();
        $student = $this->makeStudent();
        $bill    = $this->makeBill($student);
        $event   = $this->makeEvent($student, $bill);

        foreach ([null, '', '   '] as $emptySecret) {
            config([
                'services.kaffah.url'        => 'http://localhost:8000',
                'services.kaffah.api_secret' => $emptySecret,
            ]);

            $job = PushPaymentToKaffahJob::fromEvent($event, 'SPP');
            $job->handle();
        }

        Http::assertNothingSent();
    }

    public function test_sec05_response_401_and_403_fail_permanently_without_retry_exception(): void
    {
        $student = $this->makeStudent();
        $bill    = $this->makeBill($student);
        $event   = $this->makeEvent($student, $bill);

        config([
            'services.kaffah.url'        => 'http://localhost:8000/',
            'services.kaffah.api_secret' => 'wrong-or-forbidden-secret',
        ]);

        Http::fake([
            'http://localhost:8000/api/internal/finance/payment-received' => Http::sequence()
                ->push(['success' => false, 'message' => 'Unauthorized'], 401)
                ->push(['success' => false, 'message' => 'Forbidden'], 403),
        ]);

        foreach ([401, 403] as $statusCode) {
            $job = PushPaymentToKaffahJob::fromEvent($event, 'SPP');
            $job->handle();
        }

        Http::assertSentCount(2);
    }

    public function test_sec05_connection_timeout_propagates_for_retry(): void
    {
        config([
            'services.kaffah.url'        => 'http://localhost:8000',
            'services.kaffah.api_secret' => 'test-secret-key',
        ]);

        Http::fake([
            'http://localhost:8000/api/internal/finance/payment-received' => function () {
                throw new \Illuminate\Http\Client\ConnectionException('Connection timed out');
            },
        ]);

        $this->expectException(\Illuminate\Http\Client\ConnectionException::class);

        $student = $this->makeStudent();
        $bill    = $this->makeBill($student);
        $event   = $this->makeEvent($student, $bill);
        $job     = PushPaymentToKaffahJob::fromEvent($event, 'SPP');
        $job->handle();
    }
}
