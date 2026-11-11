<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Student;
use App\Models\StudentBill;
use App\Models\PaymentOrder;
use App\Models\PaymentOrderItem;
use App\Services\Doku\DokuClient;
use App\Services\Doku\DokuConfig;
use App\Events\PaymentReceived;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Config;
use Laravel\Sanctum\Sanctum;

class DokuPaymentIntegrationTest extends TestCase
{
    use DatabaseMigrations;

    private DokuClient $dokuClient;
    private string $clientId = 'CLIENT-TEST-123';
    private string $clientSecret = 'SECRET-TEST-456';

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('services.doku.client_id', $this->clientId);
        Config::set('services.doku.client_secret', $this->clientSecret);
        Config::set('services.doku.is_production', false);
        Config::set('services.doku.notification_url', 'http://localhost:8001/api/doku/notification');

        $this->dokuClient = new DokuClient(new DokuConfig());
        $this->dokuClient->enableMock();
        $this->app->instance(DokuClient::class, $this->dokuClient);
    }

    private function createStudentWithParent(string $nis = '2026001'): array
    {
        $parentPhone = '0812' . str_pad(substr($nis, -6), 8, '0', STR_PAD_LEFT);

        $parentUser = User::create([
            'name'      => 'Budi Guardian ' . $nis,
            'username'  => 'parent_' . $nis,
            'email'     => 'parent_' . $nis . '@test.id',
            'password'  => bcrypt('password123'),
            'role'      => 'student',
            'is_active' => true,
            'phone'     => $parentPhone,
        ]);

        $studentUser = User::create([
            'name'      => 'Ahmad Student ' . $nis,
            'username'  => 'student_' . $nis,
            'email'     => 'student_' . $nis . '@test.id',
            'password'  => bcrypt('password123'),
            'role'      => 'student',
            'is_active' => true,
        ]);

        $student = Student::create([
            'user_id'      => $studentUser->id,
            'nis'          => $nis,
            'name'         => 'Ahmad Student ' . $nis,
            'class_name'   => 'X-A',
            'parent_phone' => $parentPhone,
            'status'       => 'active',
        ]);

        return ['parent' => $parentUser, 'student' => $student];
    }

    private function createBill(Student $student, string $type = 'SPP', float $amount = 500000.00): StudentBill
    {
        static $seq = 0;
        $seq++;
        return StudentBill::create([
            'student_id'  => $student->id,
            'type'        => $type,
            'name'        => "Tagihan $type $seq",
            'amount'      => $amount,
            'status'      => 'UNPAID',
            'paid_amount' => 0,
            'bill_month'  => 8,
            'bill_year'   => 2026,
            'due_date'    => '2026-08-10',
        ]);
    }

    public function test_doku_config_and_mock_client(): void
    {
        $config = new DokuConfig();
        $this->assertEquals($this->clientId, $config->getClientId());
        $this->assertEquals($this->clientSecret, $config->getClientSecret());
        $this->assertFalse($config->isProduction());

        $token = $this->dokuClient->getB2bAccessToken();
        $this->assertNotEmpty($token);

        $vaRes = $this->dokuClient->createVirtualAccount([
            'order_id'       => 'TEST-ORD-01',
            'amount'         => 150000,
            'customer_name'  => 'Test Siswa',
            'bank'           => 'bca',
        ]);
        $this->assertEquals('TEST-ORD-01', $vaRes['order_id']);
        $this->assertNotEmpty($vaRes['va_number']);

        $qrisRes = $this->dokuClient->generateQris([
            'order_id'       => 'TEST-ORD-02',
            'amount'         => 200000,
            'customer_name'  => 'Test Siswa',
        ]);
        $this->assertEquals('TEST-ORD-02', $qrisRes['order_id']);
        $this->assertNotEmpty($qrisRes['qr_url']);
    }

    public function test_parent_create_payment_multi_bill(): void
    {
        $data = $this->createStudentWithParent('2026002');
        $student = $data['student'];
        $parent = $data['parent'];

        $b1 = $this->createBill($student, 'SPP', 500000.00);
        $b2 = $this->createBill($student, 'GEDUNG', 250000.00);

        Sanctum::actingAs($parent);

        $response = $this->postJson('/api/finance/payment/create', [
            'bill_ids'     => [$b1->id, $b2->id],
            'payment_type' => 'bank_transfer',
            'bank'         => 'bca',
            'student_id'   => $student->id,
        ]);

        $response->assertStatus(200);
        $json = $response->json();
        $this->assertEquals('success', $json['status']);
        $this->assertEquals(750000, $json['amount']);
        $this->assertNotEmpty($json['va_number']);
        $this->assertNotEmpty($json['order_id']);

        // Check PaymentOrder in DB
        $order = PaymentOrder::where('order_id', $json['order_id'])->first();
        $this->assertNotNull($order);
        $this->assertEquals(750000.00, (float) $order->amount);
        $this->assertEquals('PENDING', $order->status);
        $this->assertEquals('doku', $order->provider);
        $this->assertCount(2, $order->items);

        // Individual bills remain UNPAID with payment_method DOKU until settled
        $this->assertEquals('UNPAID', $b1->fresh()->status);
        $this->assertEquals('DOKU', $b1->fresh()->payment_method);
        $this->assertEquals('UNPAID', $b2->fresh()->status);
        $this->assertEquals('DOKU', $b2->fresh()->payment_method);
    }

    public function test_parent_create_payment_anti_idor_blocks_foreign_bill(): void
    {
        $data1 = $this->createStudentWithParent('2026003');
        $student1 = $data1['student'];
        $parent1 = $data1['parent'];

        $data2 = $this->createStudentWithParent('2026004');
        $student2 = $data2['student'];

        $b1 = $this->createBill($student1, 'SPP', 500000.00);
        $b2 = $this->createBill($student2, 'SPP', 500000.00); // Belongs to student2!

        Sanctum::actingAs($parent1);

        $response = $this->postJson('/api/finance/payment/create', [
            'bill_ids'     => [$b1->id, $b2->id],
            'payment_type' => 'bank_transfer',
            'bank'         => 'bca',
            'student_id'   => $student1->id,
        ]);

        $response->assertStatus(403);
        $this->assertEquals('error', $response->json('status'));
        $this->assertEquals('UNPAID', $b1->fresh()->status);
    }

    public function test_doku_webhook_signature_verification_failure(): void
    {
        $payload = json_encode(['order' => ['invoice_number' => 'ORD-TEST-999']]);

        $response = $this->call(
            'POST',
            '/api/doku/notification',
            [],
            [],
            [],
            [
                'CONTENT_TYPE'     => 'application/json',
                'HTTP_X_SIGNATURE' => 'invalid_signature',
                'HTTP_X_TIMESTAMP' => gmdate("Y-m-d\TH:i:s\Z"),
            ],
            $payload
        );

        $response->assertStatus(403);
        $this->assertEquals('error', $response->json('status'));
    }

    public function test_doku_webhook_settlement_multi_bill_dispatches_events(): void
    {
        Event::fake([PaymentReceived::class]);

        $data = $this->createStudentWithParent('2026005');
        $student = $data['student'];
        $parent = $data['parent'];

        $b1 = $this->createBill($student, 'SPP', 400000.00);
        $b2 = $this->createBill($student, 'SERAGAM', 200000.00);

        Sanctum::actingAs($parent);

        $createRes = $this->postJson('/api/finance/payment/create', [
            'bill_ids'     => [$b1->id, $b2->id],
            'payment_type' => 'bank_transfer',
            'bank'         => 'bca',
            'student_id'   => $student->id,
        ]);

        $orderId = $createRes->json('order_id');
        $this->assertNotEmpty($orderId);

        // Prepare valid DOKU webhook notification
        $body = [
            'order' => [
                'invoice_number' => $orderId,
                'amount'         => 600000,
            ],
            'transaction' => [
                'status'         => 'SUCCESS',
                'type'           => 'VIRTUAL_ACCOUNT',
                'channel'        => 'BCA',
                'payment_date'   => now()->format('YmdHis'),
            ],
        ];
        $rawBody = json_encode($body);
        $timestamp = gmdate("Y-m-d\TH:i:s\Z");
        $bodyHash = strtolower(hash('sha256', $rawBody));
        $stringToSign = 'POST:/api/doku/notification:' . $bodyHash . ':' . $timestamp;
        $signature = base64_encode(hash_hmac('sha512', $stringToSign, $this->clientSecret, true));

        $webhookRes = $this->call(
            'POST',
            '/api/doku/notification',
            [],
            [],
            [],
            [
                'CONTENT_TYPE'     => 'application/json',
                'HTTP_X_SIGNATURE' => $signature,
                'HTTP_X_TIMESTAMP' => $timestamp,
            ],
            $rawBody
        );

        $webhookRes->assertStatus(200);
        $this->assertEquals('success', $webhookRes->json('status'));

        // Verify order is PAID
        $order = PaymentOrder::where('order_id', $orderId)->first();
        $this->assertEquals('PAID', $order->status);
        $this->assertNotNull($order->paid_at);

        // Verify both bills are PAID
        $this->assertEquals('PAID', $b1->fresh()->status);
        $this->assertEquals('PAID', $b2->fresh()->status);

        // Verify two PaymentReceived events were dispatched with distinct order_ids
        Event::assertDispatched(PaymentReceived::class, 2);
        Event::assertDispatched(PaymentReceived::class, function (PaymentReceived $event) use ($orderId, $b1) {
            return $event->billId === $b1->id && $event->orderId === "{$orderId}-B{$b1->id}" && (float) $event->amount === 400000.00;
        });
        Event::assertDispatched(PaymentReceived::class, function (PaymentReceived $event) use ($orderId, $b2) {
            return $event->billId === $b2->id && $event->orderId === "{$orderId}-B{$b2->id}" && (float) $event->amount === 200000.00;
        });

        // Test Idempotency: Send same webhook again
        $repeatRes = $this->call(
            'POST',
            '/api/doku/notification',
            [],
            [],
            [],
            [
                'CONTENT_TYPE'     => 'application/json',
                'HTTP_X_SIGNATURE' => $signature,
                'HTTP_X_TIMESTAMP' => $timestamp,
            ],
            $rawBody
        );
        $repeatRes->assertStatus(200);
        $this->assertEquals('Order already settled', $repeatRes->json('message'));
    }

    public function test_doku_webhook_amount_mismatch_rejected(): void
    {
        $data = $this->createStudentWithParent('2026006');
        $student = $data['student'];
        $parent = $data['parent'];

        $b1 = $this->createBill($student, 'SPP', 500000.00);

        Sanctum::actingAs($parent);

        $createRes = $this->postJson('/api/finance/payment/create', [
            'bill_ids'     => [$b1->id],
            'payment_type' => 'bank_transfer',
            'bank'         => 'bca',
            'student_id'   => $student->id,
        ]);
        $orderId = $createRes->json('order_id');

        // Mismatched amount: 300,000 instead of 500,000
        $body = [
            'order' => [
                'invoice_number' => $orderId,
                'amount'         => 300000,
            ],
            'transaction' => [
                'status' => 'SUCCESS',
            ],
        ];
        $rawBody = json_encode($body);
        $timestamp = gmdate("Y-m-d\TH:i:s\Z");
        $bodyHash = strtolower(hash('sha256', $rawBody));
        $stringToSign = 'POST:/api/doku/notification:' . $bodyHash . ':' . $timestamp;
        $signature = base64_encode(hash_hmac('sha512', $stringToSign, $this->clientSecret, true));

        $webhookRes = $this->call(
            'POST',
            '/api/doku/notification',
            [],
            [],
            [],
            [
                'CONTENT_TYPE'     => 'application/json',
                'HTTP_X_SIGNATURE' => $signature,
                'HTTP_X_TIMESTAMP' => $timestamp,
            ],
            $rawBody
        );

        $webhookRes->assertStatus(400);
        $this->assertEquals('error', $webhookRes->json('status'));
        $this->assertEquals('UNPAID', $b1->fresh()->status);
    }

    public function test_check_status_and_invoice_detail_for_payment_order(): void
    {
        $data = $this->createStudentWithParent('2026007');
        $student = $data['student'];
        $parent = $data['parent'];

        $b1 = $this->createBill($student, 'SPP', 500000.00);
        $b2 = $this->createBill($student, 'KEGIATAN', 150000.00);

        Sanctum::actingAs($parent);

        $createRes = $this->postJson('/api/finance/payment/create', [
            'bill_ids'     => [$b1->id, $b2->id],
            'payment_type' => 'bank_transfer',
            'bank'         => 'bca',
            'student_id'   => $student->id,
        ]);
        $orderId = $createRes->json('order_id');

        // Check Status
        $statusRes = $this->getJson("/api/finance/payment/status/{$orderId}?student_id={$student->id}");
        $statusRes->assertStatus(200);
        $this->assertEquals('PENDING', $statusRes->json('payment_status'));

        // Check Invoice Detail
        $invoiceRes = $this->getJson("/api/finance/payment/invoice/{$orderId}?student_id={$student->id}");
        $invoiceRes->assertStatus(200);
        $invoiceData = $invoiceRes->json('data');
        $this->assertEquals($orderId, $invoiceData['order_id']);
        $this->assertEquals(650000, $invoiceData['total_amount']);
        $this->assertCount(2, $invoiceData['items']);

        // Check IDOR prevention on invoice detail
        $dataOther = $this->createStudentWithParent('2026008');
        Sanctum::actingAs($dataOther['parent']);
        $foreignRes = $this->getJson("/api/finance/payment/invoice/{$orderId}?student_id={$dataOther['student']->id}");
        $foreignRes->assertStatus(403);
    }
}
