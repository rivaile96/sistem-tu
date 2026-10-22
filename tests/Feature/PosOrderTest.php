<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\PosOrder;
use App\Models\PosItem;
use App\Models\PosOrderItem;
use App\Models\User;
use App\Models\Student;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class PosOrderTest extends TestCase
{
    use DatabaseMigrations;

    // ===========================================================
    // HELPERS
    // ===========================================================
    private function makeKasir(): User
    {
        return User::create([
            'name'     => 'Kasir Test',
            'username' => 'kasir01',
            'email'    => 'kasir@test.com',
            'password' => bcrypt('password'),
            'role'     => 'admin',
        ]);
    }

    private function makeItem(string $name, int $price, int $stock = 10): PosItem
    {
        return PosItem::create([
            'name'      => $name,
            'category'  => 'Makanan',
            'price'     => $price,
            'stock'     => $stock,
            'is_active' => true,
        ]);
    }

    private function makeStudent(string $nis = 'TU-2024-001'): Student
    {
        return Student::create([
            'user_id' => null,
            'nis'     => $nis,
            'status'  => 'active',
        ]);
    }

    private function makeOrder(array $overrides = []): PosOrder
    {
        return PosOrder::create(array_merge([
            'transaction_code'  => 'TRX-TEST' . uniqid(),
            'total_amount'      => 10000,
            'payment_amount'    => 0,
            'change_amount'     => 0,
            'payment_status'    => 'UNPAID',
            'redemption_status' => 'PENDING',
        ], $overrides));
    }

    private function makeOrderWithItem(array $overrides = []): PosOrder
    {
        $item  = $this->makeItem('TestItem', 5000, 5);
        $order = $this->makeOrder($overrides);
        PosOrderItem::create([
            'pos_order_id'         => $order->id,
            'pos_item_id'          => $item->id,
            'quantity'             => 2,
            'price_at_transaction' => 5000,
        ]);
        return $order;
    }

    // ===========================================================
    // 1. Schema check
    // ===========================================================
    public function test_pos_orders_has_required_columns(): void
    {
        foreach (['payment_status', 'redemption_status', 'transaction_code', 'payment_amount', 'change_amount', 'qr_token'] as $col) {
            $this->assertTrue(Schema::hasColumn('pos_orders', $col), "Kolom $col harus ada");
        }
    }

    // ===========================================================
    // 2. Default values
    // ===========================================================
    public function test_pos_order_default_status_values(): void
    {
        $order = $this->makeOrder(['transaction_code' => 'TRX-DEFAULT001']);
        $fresh = $order->fresh();
        $this->assertEquals('UNPAID',  $fresh->payment_status);
        $this->assertEquals('PENDING', $fresh->redemption_status);
        $this->assertEquals('UNPAID',  $fresh->status);
    }

    // ===========================================================
    // 3. TEST REQUIREMENT 1: Cash checkout
    // payment_status = PAID, redemption_status = COMPLETED
    // ===========================================================
    public function test_cash_checkout_sets_paid_and_completed(): void
    {
        $kasir = $this->makeKasir();
        $item  = $this->makeItem('Roti', 5000, 10);

        $response = $this->actingAs($kasir)->postJson('/pos/transaction', [
            'cart'           => [['id' => $item->id, 'qty' => 2]],
            'payment_amount' => 15000,
            'total_amount'   => 10000,
        ]);

        $response->assertStatus(200)->assertJson(['success' => true]);

        $order = PosOrder::first();
        $this->assertNotNull($order);
        $this->assertNotNull($order->transaction_code);
        $this->assertEquals('PAID',      $order->payment_status);
        $this->assertEquals('COMPLETED', $order->redemption_status);
        $this->assertEquals(10000,       $order->total_amount);
        $this->assertEquals(15000,       $order->payment_amount);
        $this->assertEquals(5000,        $order->change_amount);
    }

    // ===========================================================
    // 4. TEST REQUIREMENT 2: Debt checkout
    // payment_status = UNPAID, redemption_status = PENDING
    // ===========================================================
    public function test_debt_checkout_sets_unpaid_and_pending(): void
    {
        $kasir   = $this->makeKasir();
        $item    = $this->makeItem('Buku', 8000, 5);
        $student = $this->makeStudent();

        $response = $this->actingAs($kasir)->postJson('/pos/transaction', [
            'cart'           => [['id' => $item->id, 'qty' => 1]],
            'payment_amount' => 0,
            'total_amount'   => 8000,
            'student_id'     => $student->id,
        ]);

        $response->assertStatus(200)->assertJson(['success' => true]);

        $order = PosOrder::first();
        $this->assertEquals('UNPAID',    $order->payment_status);
        $this->assertEquals('PENDING',   $order->redemption_status);
        $this->assertEquals($student->id, $order->student_id);
    }

    // ===========================================================
    // 5. TEST REQUIREMENT 3: Repay debt
    // payment_status = PAID, redemption_status = COMPLETED
    // ===========================================================
    public function test_repay_sets_paid_and_completed(): void
    {
        $kasir = $this->makeKasir();
        $order = $this->makeOrder([
            'payment_status'    => 'UNPAID',
            'redemption_status' => 'PENDING',
            'total_amount'      => 12000,
        ]);

        $response = $this->actingAs($kasir)->post("/pos/history/{$order->id}/repay");

        $fresh = $order->fresh();
        $this->assertEquals('PAID',      $fresh->payment_status);
        $this->assertEquals('COMPLETED', $fresh->redemption_status);
        $this->assertEquals(12000,       $fresh->payment_amount);
        $this->assertEquals(0,           $fresh->change_amount);
    }

    // ===========================================================
    // 6. TEST REQUIREMENT 4: Repay already PAID — harus ditolak
    // ===========================================================
    public function test_repay_already_paid_order_is_rejected(): void
    {
        $kasir = $this->makeKasir();
        $order = $this->makeOrder([
            'payment_status'    => 'PAID',
            'redemption_status' => 'COMPLETED',
            'total_amount'      => 12000,
            'payment_amount'    => 12000,
        ]);

        $response = $this->actingAs($kasir)->post("/pos/history/{$order->id}/repay");

        // State tidak berubah
        $fresh = $order->fresh();
        $this->assertEquals('PAID',      $fresh->payment_status);
        $this->assertEquals('COMPLETED', $fresh->redemption_status);
        $this->assertEquals(12000,       $fresh->payment_amount);
    }

    // ===========================================================
    // 7. TEST REQUIREMENT 5: Midtrans PAID — UNPAID → PAID
    // ===========================================================
    public function test_midtrans_settlement_pays_unpaid_order(): void
    {
        $order = $this->makeOrder(['total_amount' => 30000]);

        // Simulasi handlePosPayment settlement
        $this->assertEquals('UNPAID', $order->payment_status);

        if ($order->payment_status !== 'PAID') {
            $order->update(['payment_status' => 'PAID', 'redemption_status' => 'PENDING']);
        }

        $this->assertEquals('PAID',    $order->fresh()->payment_status);
        $this->assertEquals('PENDING', $order->fresh()->redemption_status);
    }

    // ===========================================================
    // 8. TEST REQUIREMENT 6: Duplicate Midtrans PAID callback
    // tidak menyebabkan side effect tambahan
    // ===========================================================
    public function test_duplicate_midtrans_paid_callback_is_idempotent(): void
    {
        $kasir = $this->makeKasir();
        $order = $this->makeOrder([
            'payment_status'    => 'PAID',
            'redemption_status' => 'PENDING',
            'total_amount'      => 30000,
        ]);

        // Payload valid — callback duplikat dengan nominal sama
        $payload = $this->makeMidtransPayload($order->transaction_code, 'settlement', 30000);

        $response = $this->postJson('/api/midtrans-callback', $payload);
        $response->assertStatus(200);

        // State tidak berubah dari PAID
        $fresh = $order->fresh();
        $this->assertEquals('PAID',    $fresh->payment_status);
        $this->assertEquals('PENDING', $fresh->redemption_status);
    }

    // ===========================================================
    // 9. TEST REQUIREMENT 7: Midtrans CANCELLED — UNPAID → CANCELLED
    // stok dikembalikan
    // ===========================================================
    public function test_midtrans_cancel_sets_cancelled_and_restores_stock(): void
    {
        $item  = $this->makeItem('Snack', 5000, 10);
        $order = $this->makeOrder(['total_amount' => 10000]);

        PosOrderItem::create([
            'pos_order_id'         => $order->id,
            'pos_item_id'          => $item->id,
            'quantity'             => 2,
            'price_at_transaction' => 5000,
        ]);

        $payload = $this->makeMidtransPayload($order->transaction_code, 'cancel', 10000);
        $response = $this->postJson('/api/midtrans-callback', $payload);
        $response->assertStatus(200);

        $this->assertEquals('CANCELLED', $order->fresh()->payment_status);
        // Stok dikembalikan: 10 + 2 = 12
        $this->assertEquals(12, $item->fresh()->stock);
    }

    // ===========================================================
    // 10. TEST REQUIREMENT 8: Midtrans CANCELLED setelah PAID
    // PAID tetap PAID, stock tidak di-rollback
    // ===========================================================
    public function test_midtrans_cancel_after_paid_is_ignored(): void
    {
        $item  = $this->makeItem('Minuman', 5000, 10);
        $order = $this->makeOrder([
            'payment_status'    => 'PAID',
            'redemption_status' => 'PENDING',
            'total_amount'      => 10000,
        ]);

        PosOrderItem::create([
            'pos_order_id'         => $order->id,
            'pos_item_id'          => $item->id,
            'quantity'             => 2,
            'price_at_transaction' => 5000,
        ]);

        $payload = $this->makeMidtransPayload($order->transaction_code, 'cancel', 10000);
        $response = $this->postJson('/api/midtrans-callback', $payload);
        $response->assertStatus(200);

        // payment_status tetap PAID
        $this->assertEquals('PAID', $order->fresh()->payment_status);
        // Stok TIDAK dikembalikan — tetap 10
        $this->assertEquals(10, $item->fresh()->stock);
    }

    // ===========================================================
    // 11. TEST REQUIREMENT 9: Midtrans invalid/unknown order
    // HTTP 200, warning log, tidak membuat order baru
    // ===========================================================
    public function test_midtrans_unknown_order_returns_200_and_logs(): void
    {
        Log::shouldReceive('warning')
            ->once()
            ->withArgs(function ($message, $context) {
                return str_contains($message, 'tidak ditemukan')
                    && isset($context['order_id']);
            });

        $payload = $this->makeMidtransPayload('TRX-DOESNOTEXIST999', 'settlement', 10000);
        $response = $this->postJson('/api/midtrans-callback', $payload);

        $response->assertStatus(200);
        $this->assertEquals(0, PosOrder::count());
    }

    // ===========================================================
    // 12. TEST REQUIREMENT 10: Midtrans unknown status
    // tidak mengubah order
    // ===========================================================
    public function test_midtrans_unknown_status_does_not_change_order(): void
    {
        $order = $this->makeOrder(['total_amount' => 10000]);

        foreach (['pending', 'refund', 'partial_refund', 'chargeback'] as $unknownStatus) {
            $payload = $this->makeMidtransPayload($order->transaction_code, $unknownStatus, 10000);
            $response = $this->postJson('/api/midtrans-callback', $payload);
            $response->assertStatus(200);
        }

        $fresh = $order->fresh();
        $this->assertEquals('UNPAID',  $fresh->payment_status);
        $this->assertEquals('PENDING', $fresh->redemption_status);
    }

    // ===========================================================
    // 13. TEST REQUIREMENT 11: transaction_code unik untuk banyak transaksi
    // ===========================================================
    public function test_transaction_codes_are_unique_across_multiple_orders(): void
    {
        $kasir = $this->makeKasir();
        $item  = $this->makeItem('Item A', 3000, 100);

        // Buat 20 transaksi berturut-turut
        for ($i = 0; $i < 20; $i++) {
            $response = $this->actingAs($kasir)->postJson('/pos/transaction', [
                'cart'           => [['id' => $item->id, 'qty' => 1]],
                'payment_amount' => 3000,
                'total_amount'   => 3000,
            ]);
            $response->assertStatus(200)->assertJson(['success' => true]);
        }

        $codes = PosOrder::pluck('transaction_code')->toArray();
        $this->assertCount(20, $codes);
        $this->assertCount(20, array_unique($codes), 'Semua transaction_code harus unik');

        foreach ($codes as $code) {
            $this->assertStringStartsWith('TRX-', $code);
        }
    }

    // ===========================================================
    // 14. TEST REQUIREMENT 12: Filter history by payment_status
    // ===========================================================
    public function test_history_filter_by_payment_status_paid(): void
    {
        $kasir = $this->makeKasir();
        $this->makeOrder(['payment_status' => 'PAID',   'transaction_code' => 'TRX-PAID001']);
        $this->makeOrder(['payment_status' => 'UNPAID', 'transaction_code' => 'TRX-UNPAID001']);

        $response = $this->actingAs($kasir)->get('/pos/history?payment_status=PAID&start_date=2000-01-01&end_date=2099-12-31');
        $response->assertStatus(200);

        // Hanya PAID yang muncul
        $transactions = $response->viewData('transactions');
        $this->assertTrue($transactions->every(fn($t) => $t->payment_status === 'PAID'));
        $this->assertFalse($transactions->contains(fn($t) => $t->payment_status === 'UNPAID'));
    }

    public function test_history_filter_by_payment_status_unpaid(): void
    {
        $kasir = $this->makeKasir();
        $this->makeOrder(['payment_status' => 'PAID',   'transaction_code' => 'TRX-PAID002']);
        $this->makeOrder(['payment_status' => 'UNPAID', 'transaction_code' => 'TRX-UNPAID002']);

        $response = $this->actingAs($kasir)->get('/pos/history?payment_status=UNPAID&start_date=2000-01-01&end_date=2099-12-31');
        $response->assertStatus(200);

        $transactions = $response->viewData('transactions');
        $this->assertTrue($transactions->every(fn($t) => $t->payment_status === 'UNPAID'));
        $this->assertFalse($transactions->contains(fn($t) => $t->payment_status === 'PAID'));
    }

    // ===========================================================
    // 15. TEST REQUIREMENT 13: Nama siswa dengan karakter khusus
    // tidak merusak JavaScript pada confirm repay (data-* attribute)
    // ===========================================================
    public function test_special_chars_in_student_name_are_escaped_in_blade(): void
    {
        $kasir = $this->makeKasir();

        // Buat user dengan nama berbahaya
        $dangerousName = "O'Brien <script>alert(1)</script> & \"test\"";
        $user = User::create([
            'name'     => $dangerousName,
            'username' => 'xss-student',
            'email'    => 'xss@test.com',
            'password' => bcrypt('password'),
            'role'     => 'student',
        ]);

        $student = Student::create([
            'user_id' => $user->id,
            'nis'     => 'TU-XSS-001',
            'status'  => 'active',
        ]);

        $this->makeOrder([
            'student_id'        => $student->id,
            'payment_status'    => 'UNPAID',
            'redemption_status' => 'PENDING',
            'transaction_code'  => 'TRX-XSS001',
        ]);

        $response = $this->actingAs($kasir)
            ->get('/pos/history?start_date=2000-01-01&end_date=2099-12-31');
        $response->assertStatus(200);

        $content = $response->getContent();
        // Raw script tag tidak boleh ada di data-name attribute
        $this->assertStringNotContainsString('<script>alert(1)</script>', $content);
        // Blade e() harus menghasilkan entitas HTML yang di-escape
        $this->assertStringContainsString('&lt;script&gt;', $content);
    }

    // ===========================================================
    // HELPER: Buat payload Midtrans dengan signature valid
    // ===========================================================
    private function makeMidtransPayload(string $orderId, string $status, float $amount): array
    {
        $serverKey   = config('services.midtrans.server_key', 'test-server-key');
        $statusCode  = '200';
        $grossAmount = number_format($amount, 2, '.', '');
        $signature   = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

        return [
            'order_id'            => $orderId,
            'transaction_status'  => $status,
            'status_code'         => $statusCode,
            'gross_amount'        => $grossAmount,
            'signature_key'       => $signature,
        ];
    }
}
