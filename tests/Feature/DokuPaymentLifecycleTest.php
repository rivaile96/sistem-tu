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
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;

class DokuPaymentLifecycleTest extends TestCase
{
    use DatabaseMigrations;

    private DokuClient $dokuClient;
    private string $clientId = 'MALL-KAFFAH-TEST';
    private string $clientSecret = 'SEC-KAFFAH-TEST-999';

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
        $this->dokuClient->enableMock();
        $this->app->instance(DokuClient::class, $this->dokuClient);
    }

    private function createStudentWithParent(string $nis = '2026101'): array
    {
        $parentPhone = '0813' . str_pad(substr($nis, -6), 8, '0', STR_PAD_LEFT);

        $parentUser = User::create([
            'name'      => 'Parent ' . $nis,
            'username'  => 'parent_' . $nis,
            'email'     => 'parent_' . $nis . '@kaffah.test',
            'password'  => bcrypt('password123'),
            'role'      => 'student',
            'is_active' => true,
            'phone'     => $parentPhone,
        ]);

        $studentUser = User::create([
            'name'      => 'Student ' . $nis,
            'username'  => 'student_' . $nis,
            'email'     => 'student_' . $nis . '@kaffah.test',
            'password'  => bcrypt('password123'),
            'role'      => 'student',
            'is_active' => true,
        ]);

        $student = Student::create([
            'user_id'      => $studentUser->id,
            'nis'          => $nis,
            'name'         => 'Student ' . $nis,
            'class_name'   => 'X-B',
            'parent_phone' => $parentPhone,
            'status'       => 'active',
        ]);

        return ['parent' => $parentUser, 'student' => $student];
    }

    private function createBill(Student $student, string $name = 'SPP September', float $amount = 350000.00): StudentBill
    {
        static $seq = 0;
        $seq++;
        $month = ($seq % 12) + 1;

        return StudentBill::create([
            'student_id'  => $student->id,
            'type'        => 'SPP',
            'name'        => $name,
            'amount'      => $amount,
            'status'      => 'UNPAID',
            'paid_amount' => 0,
            'bill_month'  => $month,
            'bill_year'   => 2026,
            'due_date'    => '2026-09-10',
        ]);
    }

    /**
     * Test 1: Sesi pembayaran baru mendapatkan masa berlaku 24 jam.
     */
    public function test_new_checkout_has_24_hour_expiry(): void
    {
        $actor = $this->createStudentWithParent('2026101');
        $bill1 = $this->createBill($actor['student'], 'SPP 1', 300000);
        $bill2 = $this->createBill($actor['student'], 'SPP 2', 400000);

        Sanctum::actingAs($actor['parent']);

        $before = now()->addHours(23)->addMinutes(59);

        $res = $this->postJson('/api/finance/payment/create', [
            'bill_ids'     => [$bill1->id, $bill2->id],
            'payment_type' => 'va',
            'channel'      => 'bca',
        ]);

        $res->assertStatus(200);
        $res->assertJson(['status' => 'success']);

        $order = PaymentOrder::where('order_id', $res->json('order_id'))->first();
        $this->assertNotNull($order);
        $this->assertEquals('PENDING', $order->status);
        $this->assertNotNull($order->expiry_time);
        $this->assertTrue($order->expiry_time->greaterThan($before));
    }

    /**
     * Test 2: Pemanggilan ulang createPayment dengan tagihan yang SAMA persis
     * akan me-REUSE checkout aktif yang masih berlaku (Idempoten, tidak buat order baru).
     */
    public function test_reuse_active_checkout_with_matching_bills(): void
    {
        $actor = $this->createStudentWithParent('2026102');
        $bill1 = $this->createBill($actor['student'], 'SPP 1', 250000);
        $bill2 = $this->createBill($actor['student'], 'SPP 2', 250000);

        Sanctum::actingAs($actor['parent']);

        // Checkout pertama
        $res1 = $this->postJson('/api/finance/payment/create', [
            'bill_ids'     => [$bill1->id, $bill2->id],
            'payment_type' => 'va',
            'channel'      => 'bca',
        ]);
        $res1->assertStatus(200);
        $orderId1 = $res1->json('order_id');

        $this->assertEquals(1, PaymentOrder::count());

        // Checkout kedua dengan tagihan yang sama
        $res2 = $this->postJson('/api/finance/payment/create', [
            'bill_ids'     => [$bill1->id, $bill2->id],
            'payment_type' => 'va',
            'channel'      => 'bca',
        ]);

        $res2->assertStatus(200);
        $res2->assertJson([
            'status'   => 'success',
            'reused'   => true,
            'order_id' => $orderId1,
        ]);

        // Pastikan tidak ada order ganda di database
        $this->assertEquals(1, PaymentOrder::count());
    }

    /**
     * Test 3: Tagihan yang overlap parsial dengan order aktif ditolak dengan 409 Conflict.
     */
    public function test_overlapping_bills_rejected_with_409(): void
    {
        $actor = $this->createStudentWithParent('2026103');
        $bill1 = $this->createBill($actor['student'], 'SPP 1', 200000);
        $bill2 = $this->createBill($actor['student'], 'SPP 2', 200000);
        $bill3 = $this->createBill($actor['student'], 'SPP 3', 200000);

        Sanctum::actingAs($actor['parent']);

        // Order 1 mengunci bill 1 dan bill 2
        $this->postJson('/api/finance/payment/create', [
            'bill_ids'     => [$bill1->id, $bill2->id],
            'payment_type' => 'va',
            'channel'      => 'bca',
        ])->assertStatus(200);

        // Order 2 mencoba membayar bill 2 dan bill 3 (bill 2 overlap!)
        $resConflict = $this->postJson('/api/finance/payment/create', [
            'bill_ids'     => [$bill2->id, $bill3->id],
            'payment_type' => 'va',
            'channel'      => 'bca',
        ]);

        $resConflict->assertStatus(409);
        $resConflict->assertJsonStructure(['status', 'message', 'active_order_id']);
    }

    /**
     * Test 4: Alur pembatalan (Cancel) membebaskan tagihan kembali ke status UNPAID.
     */
    public function test_safe_cancellation_unlocks_bills(): void
    {
        $actor = $this->createStudentWithParent('2026104');
        $bill1 = $this->createBill($actor['student'], 'SPP 1', 300000);

        Sanctum::actingAs($actor['parent']);

        $res = $this->postJson('/api/finance/payment/create', [
            'bill_ids'     => [$bill1->id],
            'payment_type' => 'va',
            'channel'      => 'bca',
        ]);
        $orderId = $res->json('order_id');

        // Batalkan sesi pembayaran
        $cancelRes = $this->postJson('/api/finance/payment/cancel', [
            'order_id' => $orderId,
        ]);

        $cancelRes->assertStatus(200);
        $cancelRes->assertJson(['status' => 'success']);

        $order = PaymentOrder::where('order_id', $orderId)->first();
        $this->assertEquals('CANCELLED', $order->status);

        $bill1->refresh();
        $this->assertEquals('UNPAID', $bill1->status);

        // Setelah dibatalkan, tagihan bill1 bisa dibuatkan sesi pembayaran baru (misal ganti QRIS)
        $newRes = $this->postJson('/api/finance/payment/create', [
            'bill_ids'     => [$bill1->id],
            'payment_type' => 'qris',
            'channel'      => 'qris',
        ]);

        $newRes->assertStatus(200);
        $this->assertNotEquals($orderId, $newRes->json('order_id'));
    }

    /**
     * Test 5: Anti-IDOR pada endpoint pembatalan (tidak dapat membatalkan order siswa lain).
     */
    public function test_cancel_anti_idor(): void
    {
        $actorA = $this->createStudentWithParent('2026105');
        $actorB = $this->createStudentWithParent('2026106');

        $billA = $this->createBill($actorA['student'], 'SPP A', 300000);

        Sanctum::actingAs($actorA['parent']);
        $resA = $this->postJson('/api/finance/payment/create', [
            'bill_ids'     => [$billA->id],
            'payment_type' => 'va',
            'channel'      => 'bca',
        ]);
        $orderIdA = $resA->json('order_id');

        // Actor B mencoba membatalkan order milik Actor A
        Sanctum::actingAs($actorB['parent']);
        $resB = $this->postJson('/api/finance/payment/cancel', [
            'order_id' => $orderIdA,
        ]);

        $resB->assertStatus(403);
    }

    /**
     * Test 6: Order yang sudah PAID tidak dapat dibatalkan.
     */
    public function test_cannot_cancel_already_paid_order(): void
    {
        $actor = $this->createStudentWithParent('2026107');
        $bill = $this->createBill($actor['student'], 'SPP Lunas', 200000);

        Sanctum::actingAs($actor['parent']);
        $res = $this->postJson('/api/finance/payment/create', [
            'bill_ids'     => [$bill->id],
            'payment_type' => 'va',
            'channel'      => 'bca',
        ]);
        $orderId = $res->json('order_id');

        $order = PaymentOrder::where('order_id', $orderId)->first();
        $order->update(['status' => 'PAID']);

        $cancelRes = $this->postJson('/api/finance/payment/cancel', [
            'order_id' => $orderId,
        ]);

        $cancelRes->assertStatus(400);
        $cancelRes->assertJson(['status' => 'error']);
    }

    /**
     * Test 7: Race condition: Webhook sukses tiba saat order dalam CANCEL_REQUESTED -> PAID MENANG!
     */
    public function test_race_condition_webhook_wins_over_cancel_requested(): void
    {
        $actor = $this->createStudentWithParent('2026108');
        $bill = $this->createBill($actor['student'], 'SPP Race', 500000);

        $order = PaymentOrder::create([
            'order_id'        => 'ORD-RACE-001',
            'student_id'      => $actor['student']->id,
            'amount'          => 500000,
            'currency'        => 'IDR',
            'provider'        => 'doku',
            'payment_method'  => 'DOKU',
            'payment_channel' => 'bca',
            'status'          => 'CANCEL_REQUESTED',
            'expiry_time'     => now()->addDay(),
        ]);

        PaymentOrderItem::create([
            'payment_order_id' => $order->id,
            'bill_id'          => $bill->id,
            'amount'           => 500000,
            'name'             => $bill->name,
        ]);

        // Simulasikan webhook settlement masuk dari DOKU
        $timestamp = gmdate('Y-m-d\TH:i:s\Z');
        $signature = 'mock-valid-sig';
        $this->dokuClient->enableMock(['verify_signature' => true]);

        $payload = [
            'trxId'             => 'ORD-RACE-001',
            'transactionStatus' => 'SUCCESS',
            'totalAmount'       => ['value' => '500000.00'],
            'referenceNo'       => 'DOKU-REF-RACE-001',
            'paidTime'          => now()->toIso8601String(),
        ];

        $webhookRes = $this->withHeaders([
            'X-SIGNATURE' => $signature,
            'X-TIMESTAMP' => $timestamp,
        ])->postJson('/api/doku/notification', $payload);

        $webhookRes->assertStatus(200);

        $order->refresh();
        $this->assertEquals('PAID', $order->status);

        $bill->refresh();
        $this->assertEquals('PAID', $bill->status);
    }

    /**
     * Test 8: Race condition: Webhook sukses tiba SETELAH order berstatus CANCELLED -> dicatat sebagai LATE_PAID.
     */
    public function test_race_condition_late_payment_on_cancelled_order(): void
    {
        $actor = $this->createStudentWithParent('2026109');
        $bill = $this->createBill($actor['student'], 'SPP Late', 450000);

        $order = PaymentOrder::create([
            'order_id'        => 'ORD-LATE-001',
            'student_id'      => $actor['student']->id,
            'amount'          => 450000,
            'currency'        => 'IDR',
            'provider'        => 'doku',
            'payment_method'  => 'DOKU',
            'payment_channel' => 'bca',
            'status'          => 'CANCELLED',
            'expiry_time'     => now()->addDay(),
        ]);

        PaymentOrderItem::create([
            'payment_order_id' => $order->id,
            'bill_id'          => $bill->id,
            'amount'           => 450000,
            'name'             => $bill->name,
        ]);

        $this->dokuClient->enableMock(['verify_signature' => true]);

        $payload = [
            'trxId'             => 'ORD-LATE-001',
            'transactionStatus' => 'SUCCESS',
            'totalAmount'       => ['value' => '450000.00'],
            'referenceNo'       => 'DOKU-REF-LATE-001',
            'paidTime'          => now()->toIso8601String(),
        ];

        $webhookRes = $this->withHeaders([
            'X-SIGNATURE' => 'valid-sig',
            'X-TIMESTAMP' => gmdate('Y-m-d\TH:i:s\Z'),
        ])->postJson('/api/doku/notification', $payload);

        $webhookRes->assertStatus(200);

        $order->refresh();
        $this->assertEquals('LATE_PAID', $order->status);

        // Tagihan tidak otomatis di-settle agar tidak merusak rekonsiliasi manual
        $bill->refresh();
        $this->assertEquals('UNPAID', $bill->status);
    }

    /**
     * Test 9: Expired order ditandai EXPIRED pada checkStatus dan membebaskan tagihan.
     */
    public function test_expired_order_handling(): void
    {
        $actor = $this->createStudentWithParent('2026110');
        $bill = $this->createBill($actor['student'], 'SPP Expired', 350000);

        $order = PaymentOrder::create([
            'order_id'        => 'ORD-EXP-001',
            'student_id'      => $actor['student']->id,
            'amount'          => 350000,
            'currency'        => 'IDR',
            'provider'        => 'doku',
            'payment_method'  => 'DOKU',
            'payment_channel' => 'bca',
            'status'          => 'PENDING',
            'expiry_time'     => now()->subMinute(), // Sudah lewat batas waktu
        ]);

        PaymentOrderItem::create([
            'payment_order_id' => $order->id,
            'bill_id'          => $bill->id,
            'amount'           => 350000,
            'name'             => $bill->name,
        ]);

        Sanctum::actingAs($actor['parent']);

        $res = $this->getJson('/api/finance/payment/status/ORD-EXP-001');
        $res->assertStatus(200);
        $res->assertJson([
            'payment_status' => 'EXPIRED',
        ]);

        $order->refresh();
        $this->assertEquals('EXPIRED', $order->status);

        $bill->refresh();
        $this->assertEquals('UNPAID', $bill->status);
    }

    /**
     * Test 10: Endpoint getActivePayment mengembalikan sesi aktif jika ada.
     */
    public function test_get_active_payment_endpoint(): void
    {
        $actor = $this->createStudentWithParent('2026111');
        $bill = $this->createBill($actor['student'], 'SPP Active', 600000);

        Sanctum::actingAs($actor['parent']);

        // Sebelum ada checkout
        $resBefore = $this->getJson('/api/finance/payment/active');
        $resBefore->assertStatus(200);
        $resBefore->assertJson([
            'status'     => 'success',
            'has_active' => false,
        ]);

        // Buat checkout
        $this->postJson('/api/finance/payment/create', [
            'bill_ids'     => [$bill->id],
            'payment_type' => 'va',
            'channel'      => 'bca',
        ]);

        // Setelah ada checkout
        $resAfter = $this->getJson('/api/finance/payment/active');
        $resAfter->assertStatus(200);
        $resAfter->assertJson([
            'status'     => 'success',
            'has_active' => true,
        ]);
        $this->assertNotNull($resAfter->json('data.order_id'));
        $this->assertGreaterThan(0, $resAfter->json('data.remaining_seconds'));
    }

    /**
     * Test 11: Format expiry time pada QRIS dan VA berdurasi 24 jam.
     */
    public function test_doku_client_expiry_duration(): void
    {
        $qrisRes = $this->dokuClient->generateQris([
            'order_id' => 'ORD-TEST-QRIS-24',
            'amount'   => 100000,
        ]);

        $this->assertTrue($qrisRes['success']);
        $expiryQris = \Carbon\Carbon::parse($qrisRes['expiry_time']);
        $this->assertGreaterThan(now()->addHours(23), $expiryQris);

        $vaRes = $this->dokuClient->createVirtualAccount([
            'order_id'      => 'ORD-TEST-VA-24',
            'amount'        => 200000,
            'customer_name' => 'Test User',
            'bank'          => 'bca',
        ]);

        $this->assertTrue($vaRes['success']);
        $expiryVa = \Carbon\Carbon::parse($vaRes['expiry_time']);
        $this->assertGreaterThan(now()->addHours(23), $expiryVa);
    }

    /**
     * Test 12: Private key validation dan fallback ke storage/keys/.
     */
    public function test_doku_config_private_key_fallback(): void
    {
        $config = new DokuConfig(privateKey: '');
        $this->assertTrue($config->isPrivateKeyValid());
        $this->assertStringContainsString('BEGIN', $config->privateKey);
        $this->assertStringContainsString('PRIVATE KEY', $config->privateKey);
    }
}
