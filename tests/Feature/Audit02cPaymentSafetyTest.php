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
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use Illuminate\Http\Request;
use Laravel\Sanctum\Sanctum;

class Audit02cPaymentSafetyTest extends TestCase
{
    use DatabaseMigrations;

    private DokuClient $dokuClient;
    private string $clientId = 'BRN-KAFFAH-TEST-01';
    private string $clientSecret = 'SEC-KAFFAH-TEST-SECRET-12345';

    protected function setUp(): void
    {
        parent::setUp();

        Event::fake([PaymentReceived::class]);

        Config::set('services.doku.client_id', $this->clientId);
        Config::set('services.doku.client_secret', $this->clientSecret);
        Config::set('services.doku.is_production', false);
        Config::set('services.doku.base_url', 'https://api-sandbox.doku.com');
        Config::set('services.doku.notification_url', 'http://localhost:8001/api/doku/notification');

        $this->dokuClient = new DokuClient(new DokuConfig());
        $this->app->instance(DokuClient::class, $this->dokuClient);
    }

    private function createStudentWithParent(string $nis = '2026999'): array
    {
        $parentPhone = '081299998888';

        $parentUser = User::create([
            'name'      => 'Wali Siswa ' . $nis,
            'username'  => 'parent_' . $nis,
            'email'     => 'parent_' . $nis . '@kaffah.sch.id',
            'password'  => bcrypt('password123'),
            'role'      => 'student',
            'is_active' => true,
            'phone'     => $parentPhone,
        ]);

        $studentUser = User::create([
            'name'      => 'Siswa ' . $nis,
            'username'  => 'student_' . $nis,
            'email'     => 'student_' . $nis . '@kaffah.sch.id',
            'password'  => bcrypt('password123'),
            'role'      => 'student',
            'is_active' => true,
        ]);

        $student = Student::create([
            'user_id'      => $studentUser->id,
            'nis'          => $nis,
            'name'         => 'Siswa ' . $nis,
            'class_name'   => '7A',
            'parent_phone' => $parentPhone,
            'status'       => 'active',
        ]);

        return ['parent' => $parentUser, 'student' => $student];
    }

    private function createTestBill(Student $student, string $name = 'Uang Seragam 2026', float $amount = 500000.00, string $type = 'OTHER', ?int $month = 11): StudentBill
    {
        return StudentBill::create([
            'student_id'  => $student->id,
            'type'        => $type,
            'name'        => $name,
            'amount'      => $amount,
            'status'      => 'UNPAID',
            'paid_amount' => 0,
            'bill_month'  => $month,
            'bill_year'   => 2026,
            'due_date'    => '2026-11-25',
        ]);
    }

    /**
     * 1. Verifikasi CreateAdminCommand menolak opsi CLI --password
     */
    public function test_admin_command_rejects_password_option()
    {
        $command = \Illuminate\Support\Facades\Artisan::all()['tu:create-admin'];
        $this->assertNotNull($command);
        $this->assertFalse($command->getDefinition()->hasOption('password'));
    }

    /**
     * 2. Verifikasi Direct API V2 Signature: Valid vs Invalid
     */
    public function test_direct_api_v2_signature_verification()
    {
        $relativeUrl = '/api/doku/notification';
        $requestId = 'req-uuid-123456';
        $timestamp = gmdate('Y-m-d\TH:i:s\Z');
        $rawBody = json_encode([
            'order' => ['invoice_number' => 'INV-001', 'amount' => 500000],
            'transaction' => ['status' => 'SUCCESS']
        ]);

        $digest = base64_encode(hash('sha256', $rawBody, true));
        $stringToSign = "Client-Id:" . $this->clientId . "\n"
            . "Request-Id:" . $requestId . "\n"
            . "Request-Timestamp:" . $timestamp . "\n"
            . "Request-Target:" . $relativeUrl . "\n"
            . "Digest:" . $digest;

        $validSignature = 'HMACSHA256=' . base64_encode(hash_hmac('sha256', $stringToSign, $this->clientSecret, true));

        // Valid Request
        $request = Request::create($relativeUrl, 'POST', [], [], [], [
            'HTTP_CLIENT_ID'         => $this->clientId,
            'HTTP_REQUEST_ID'        => $requestId,
            'HTTP_REQUEST_TIMESTAMP' => $timestamp,
            'HTTP_SIGNATURE'         => $validSignature,
        ], $rawBody);

        $this->assertTrue($this->dokuClient->verifyNotificationSignature($request, $relativeUrl));

        // Tampered Body -> Mismatch
        $tamperedRequest = Request::create($relativeUrl, 'POST', [], [], [], [
            'HTTP_CLIENT_ID'         => $this->clientId,
            'HTTP_REQUEST_ID'        => $requestId,
            'HTTP_REQUEST_TIMESTAMP' => $timestamp,
            'HTTP_SIGNATURE'         => $validSignature,
        ], $rawBody . 'tampered');

        $this->assertFalse($this->dokuClient->verifyNotificationSignature($tamperedRequest, $relativeUrl));

        // Wrong Secret -> Mismatch
        $wrongSignature = 'HMACSHA256=' . base64_encode(hash_hmac('sha256', $stringToSign, 'WRONG_SECRET', true));
        $wrongRequest = Request::create($relativeUrl, 'POST', [], [], [], [
            'HTTP_CLIENT_ID'         => $this->clientId,
            'HTTP_REQUEST_ID'        => $requestId,
            'HTTP_REQUEST_TIMESTAMP' => $timestamp,
            'HTTP_SIGNATURE'         => $wrongSignature,
        ], $rawBody);

        $this->assertFalse($this->dokuClient->verifyNotificationSignature($wrongRequest, $relativeUrl));
    }

    /**
     * 3. Verifikasi SNAP Signature: Valid vs Invalid
     */
    public function test_snap_signature_verification()
    {
        $relativeUrl = '/api/doku/notification';
        $timestamp = now()->toIso8601String();
        $rawBody = json_encode([
            'partnerReferenceNo' => 'INV-002',
            'amount' => ['value' => '350000.00', 'currency' => 'IDR'],
            'transactionStatus' => 'SUCCESS'
        ]);

        $bodyHash = strtolower(hash('sha256', $rawBody));
        $stringToSign = 'POST:' . $relativeUrl . ':' . $bodyHash . ':' . $timestamp;
        $validSignature = base64_encode(hash_hmac('sha512', $stringToSign, $this->clientSecret, true));

        $request = Request::create($relativeUrl, 'POST', [], [], [], [
            'HTTP_X_SIGNATURE'  => $validSignature,
            'HTTP_X_TIMESTAMP'  => $timestamp,
            'HTTP_X_PARTNER_ID' => $this->clientId,
        ], $rawBody);

        $this->assertTrue($this->dokuClient->verifyNotificationSignature($request, $relativeUrl));

        // Invalid signature
        $invalidRequest = Request::create($relativeUrl, 'POST', [], [], [], [
            'HTTP_X_SIGNATURE'  => 'INVALID_BASE64_SIG',
            'HTTP_X_TIMESTAMP'  => $timestamp,
            'HTTP_X_PARTNER_ID' => $this->clientId,
        ], $rawBody);

        $this->assertFalse($this->dokuClient->verifyNotificationSignature($invalidRequest, $relativeUrl));
    }

    /**
     * 4. Webhook DOKU Direct API V2 memproses pelunasan multi-bill
     */
    public function test_direct_api_v2_notification_settles_order_and_bills()
    {
        $data = $this->createStudentWithParent('2026111');
        $student = $data['student'];
        $bill1 = $this->createTestBill($student, 'Tagihan Buku', 200000.00, 'BUKU', 10);
        $bill2 = $this->createTestBill($student, 'Tagihan Ekskul', 300000.00, 'EKSKUL', 11);

        $orderId = 'DOKU-V2-' . time();
        $order = PaymentOrder::create([
            'order_id'       => $orderId,
            'student_id'     => $student->id,
            'amount'         => 500000.00,
            'currency'       => 'IDR',
            'provider'       => 'doku',
            'payment_method' => 'bank_transfer',
            'payment_channel'=> 'bca',
            'va_number'      => '1900800000375130',
            'status'         => 'PENDING',
            'expiry_time'    => now()->addHours(24),
        ]);

        PaymentOrderItem::create(['payment_order_id' => $order->id, 'bill_id' => $bill1->id, 'amount' => 200000.00, 'name' => 'Tagihan Buku']);
        PaymentOrderItem::create(['payment_order_id' => $order->id, 'bill_id' => $bill2->id, 'amount' => 300000.00, 'name' => 'Tagihan Ekskul']);

        $relativeUrl = '/api/doku/notification';
        $requestId = 'req-notify-999';
        $timestamp = gmdate('Y-m-d\TH:i:s\Z');
        $payload = [
            'service' => ['id' => 'ONLINE_PAYMENT'],
            'order'   => [
                'invoice_number' => $orderId,
                'amount'         => 500000.00,
                'currency'       => 'IDR',
            ],
            'virtual_account_info' => [
                'virtual_account_number' => '1900800000375130',
            ],
            'transaction' => [
                'status'              => 'SUCCESS',
                'date'                => now()->toIso8601String(),
                'original_request_id' => 'orig-req-123',
            ],
        ];

        $rawBody = json_encode($payload, JSON_UNESCAPED_SLASHES);
        $digest = base64_encode(hash('sha256', $rawBody, true));
        $stringToSign = "Client-Id:" . $this->clientId . "\n"
            . "Request-Id:" . $requestId . "\n"
            . "Request-Timestamp:" . $timestamp . "\n"
            . "Request-Target:" . $relativeUrl . "\n"
            . "Digest:" . $digest;
        $signature = 'HMACSHA256=' . base64_encode(hash_hmac('sha256', $stringToSign, $this->clientSecret, true));

        $response = $this->call('POST', $relativeUrl, [], [], [], [
            'HTTP_CLIENT_ID'         => $this->clientId,
            'HTTP_REQUEST_ID'        => $requestId,
            'HTTP_REQUEST_TIMESTAMP' => $timestamp,
            'HTTP_SIGNATURE'         => $signature,
            'CONTENT_TYPE'           => 'application/json',
        ], $rawBody);

        $response->assertStatus(200);
        $response->assertJson(['status' => 'success', 'responseCode' => '2000700']);

        $order->refresh();
        $this->assertEquals('PAID', $order->status);

        $bill1->refresh();
        $bill2->refresh();
        $this->assertEquals('PAID', $bill1->status);
        $this->assertEquals('PAID', $bill2->status);
        $this->assertEquals('DOKU', $bill1->payment_method);

        // 5. Duplicate Webhook: Idempotency Check
        $dupResponse = $this->call('POST', $relativeUrl, [], [], [], [
            'HTTP_CLIENT_ID'         => $this->clientId,
            'HTTP_REQUEST_ID'        => $requestId,
            'HTTP_REQUEST_TIMESTAMP' => $timestamp,
            'HTTP_SIGNATURE'         => $signature,
            'CONTENT_TYPE'           => 'application/json',
        ], $rawBody);

        $dupResponse->assertStatus(200);
        $dupResponse->assertJson(['status' => 'success', 'responseCode' => '2000700', 'message' => 'Order already settled']);
    }

    /**
     * 6. Webhook Amount Mismatch Ditolak
     */
    public function test_amount_mismatch_is_rejected()
    {
        $data = $this->createStudentWithParent('2026222');
        $student = $data['student'];
        $bill = $this->createTestBill($student, 'Tagihan Lab', 150000.00);

        $orderId = 'DOKU-MISMATCH-' . time();
        $order = PaymentOrder::create([
            'order_id'       => $orderId,
            'student_id'     => $student->id,
            'amount'         => 150000.00,
            'currency'       => 'IDR',
            'provider'       => 'doku',
            'payment_method' => 'bank_transfer',
            'status'         => 'PENDING',
            'expiry_time'    => now()->addHours(24),
        ]);
        PaymentOrderItem::create(['payment_order_id' => $order->id, 'bill_id' => $bill->id, 'amount' => 150000.00, 'name' => 'Tagihan Lab']);

        $relativeUrl = '/api/doku/notification';
        $requestId = 'req-mismatch-1';
        $timestamp = gmdate('Y-m-d\TH:i:s\Z');
        $payload = [
            'order' => ['invoice_number' => $orderId, 'amount' => 100000.00], // Nominal salah!
            'transaction' => ['status' => 'SUCCESS']
        ];
        $rawBody = json_encode($payload);
        $digest = base64_encode(hash('sha256', $rawBody, true));
        $stringToSign = "Client-Id:" . $this->clientId . "\nRequest-Id:" . $requestId . "\nRequest-Timestamp:" . $timestamp . "\nRequest-Target:" . $relativeUrl . "\nDigest:" . $digest;
        $signature = 'HMACSHA256=' . base64_encode(hash_hmac('sha256', $stringToSign, $this->clientSecret, true));

        $response = $this->call('POST', $relativeUrl, [], [], [], [
            'HTTP_CLIENT_ID'         => $this->clientId,
            'HTTP_REQUEST_ID'        => $requestId,
            'HTTP_REQUEST_TIMESTAMP' => $timestamp,
            'HTTP_SIGNATURE'         => $signature,
            'CONTENT_TYPE'           => 'application/json',
        ], $rawBody);

        $response->assertStatus(400);
        $response->assertJson(['responseCode' => '4007301']);

        $order->refresh();
        $this->assertEquals('PENDING', $order->status); // Order tetap PENDING, tidak berubah
    }

    /**
     * 7. Provider Cancellation: Berhasil HANYA jika provider mengonfirmasi
     */
    public function test_cancellation_succeeds_when_provider_confirms()
    {
        $data = $this->createStudentWithParent('2026333');
        $student = $data['student'];
        $bill = $this->createTestBill($student, 'Tagihan Pramuka', 75000.00);

        $orderId = 'DOKU-CANCEL-OK-' . time();
        $order = PaymentOrder::create([
            'order_id'       => $orderId,
            'student_id'     => $student->id,
            'amount'         => 75000.00,
            'currency'       => 'IDR',
            'provider'       => 'doku',
            'payment_method' => 'bank_transfer',
            'status'         => 'PENDING',
            'expiry_time'    => now()->addHours(24),
        ]);
        PaymentOrderItem::create(['payment_order_id' => $order->id, 'bill_id' => $bill->id, 'amount' => 75000.00, 'name' => 'Tagihan Pramuka']);
        $bill->update(['midtrans_order_id' => $orderId]);

        // Mock provider confirms cancellation
        DokuClient::enableMock(['cancel_order' => ['success' => true, 'status' => 'CANCELLED']]);

        Sanctum::actingAs($data['parent'], ['finance']);

        $response = $this->postJson('/api/finance/payment/cancel', ['order_id' => $orderId]);
        $response->assertStatus(200);
        $response->assertJson(['status' => 'success']);

        $order->refresh();
        $bill->refresh();
        $this->assertEquals('CANCELLED', $order->status);
        $this->assertEquals('UNPAID', $bill->status);
        $this->assertNull($bill->midtrans_order_id); // Tagihan dibebaskan
    }

    /**
     * 8. Provider Cancellation: Gagal jika provider menolak (Tagihan TIDAK boleh dilepas!)
     */
    public function test_cancellation_fails_when_provider_rejects_and_retains_bills()
    {
        $data = $this->createStudentWithParent('2026444');
        $student = $data['student'];
        $bill = $this->createTestBill($student, 'Tagihan Komputer', 120000.00);

        $orderId = 'DOKU-CANCEL-FAIL-' . time();
        $order = PaymentOrder::create([
            'order_id'       => $orderId,
            'student_id'     => $student->id,
            'amount'         => 120000.00,
            'currency'       => 'IDR',
            'provider'       => 'doku',
            'payment_method' => 'bank_transfer',
            'status'         => 'PENDING',
            'expiry_time'    => now()->addHours(24),
        ]);
        PaymentOrderItem::create(['payment_order_id' => $order->id, 'bill_id' => $bill->id, 'amount' => 120000.00, 'name' => 'Tagihan Komputer']);
        $bill->update(['midtrans_order_id' => $orderId]);

        // Mock provider rejects cancellation (e.g. HTTP 400 from DOKU)
        DokuClient::enableMock([
            'cancel_order' => [
                'success' => false,
                'status'  => 'REJECTED',
                'raw'     => ['message' => 'Merchant does not support cancellation']
            ]
        ]);

        Sanctum::actingAs($data['parent'], ['finance']);

        $response = $this->postJson('/api/finance/payment/cancel', ['order_id' => $orderId]);
        $response->assertStatus(400);
        $response->assertJson(['status' => 'error', 'cancellation_status' => 'REJECTED']);

        $order->refresh();
        $bill->refresh();
        $this->assertEquals('PENDING', $order->status); // Order tetap PENDING!
        $this->assertEquals('UNPAID', $bill->status);
        $this->assertEquals($orderId, $bill->midtrans_order_id); // Tagihan TETAP TERKUNCI pada sesi ini!
    }

    /**
     * 9. Race Condition: Webhook settlement tiba saat status CANCEL_REQUESTED (PAID MENANG!)
     */
    public function test_settlement_wins_over_cancel_requested()
    {
        $data = $this->createStudentWithParent('2026555');
        $student = $data['student'];
        $bill = $this->createTestBill($student, 'Tagihan Wisata', 250000.00);

        $orderId = 'DOKU-RACE-' . time();
        $order = PaymentOrder::create([
            'order_id'       => $orderId,
            'student_id'     => $student->id,
            'amount'         => 250000.00,
            'currency'       => 'IDR',
            'provider'       => 'doku',
            'payment_method' => 'bank_transfer',
            'status'         => 'CANCEL_REQUESTED', // Customer sedang meminta cancel
            'expiry_time'    => now()->addHours(24),
        ]);
        PaymentOrderItem::create(['payment_order_id' => $order->id, 'bill_id' => $bill->id, 'amount' => 250000.00, 'name' => 'Tagihan Wisata']);

        // DOKU settlement notification tiba saat CANCEL_REQUESTED
        DokuClient::enableMock(['verify_signature' => true]);

        $response = $this->postJson('/api/doku/notification', [
            'order' => ['invoice_number' => $orderId, 'amount' => 250000.00],
            'transaction' => ['status' => 'SUCCESS']
        ]);

        $response->assertStatus(200);

        $order->refresh();
        $bill->refresh();
        $this->assertEquals('PAID', $order->status); // PAID MENANG!
        $this->assertEquals('PAID', $bill->status);
    }

    /**
     * 10. Late Payment: Pembayaran terlambat pada order yang sudah CANCELLED dicatat sebagai LATE_PAID
     */
    public function test_late_payment_on_cancelled_order_recorded_as_late_paid()
    {
        $data = $this->createStudentWithParent('2026666');
        $student = $data['student'];
        $bill = $this->createTestBill($student, 'Tagihan Kursus', 180000.00);

        $orderId = 'DOKU-LATE-' . time();
        $order = PaymentOrder::create([
            'order_id'       => $orderId,
            'student_id'     => $student->id,
            'amount'         => 180000.00,
            'currency'       => 'IDR',
            'provider'       => 'doku',
            'payment_method' => 'bank_transfer',
            'status'         => 'CANCELLED',
            'expiry_time'    => now()->subHour(), // Sudah lewat
        ]);
        PaymentOrderItem::create(['payment_order_id' => $order->id, 'bill_id' => $bill->id, 'amount' => 180000.00, 'name' => 'Tagihan Kursus']);

        DokuClient::enableMock(['verify_signature' => true]);

        $response = $this->postJson('/api/doku/notification', [
            'order' => ['invoice_number' => $orderId, 'amount' => 180000.00],
            'transaction' => ['status' => 'SUCCESS']
        ]);

        $response->assertStatus(200);
        $order->refresh();
        $bill->refresh();
        $this->assertEquals('LATE_PAID', $order->status);
        $this->assertEquals('UNPAID', $bill->status); // Tagihan tidak otomatis lunas, perlu rekonsiliasi manual
    }
}
