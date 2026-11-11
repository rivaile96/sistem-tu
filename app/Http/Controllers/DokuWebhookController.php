<?php

namespace App\Http\Controllers;

use App\Events\PaymentReceived;
use App\Models\PaymentOrder;
use App\Models\PosOrder;
use App\Models\StudentBill;
use App\Services\Doku\DokuClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * DokuWebhookController — Penanganan notifikasi pembayaran masuk dari DOKU.
 *
 * Menerapkan:
 * - Verifikasi HMAC-SHA512 / SNAP signature
 * - Idempotensi penuh untuk mencegah double settlement
 * - Pelunasan multi-bill atomik dalam 1 transaksi DB
 * - Dispatch event PaymentReceived idempoten per-bill ke Kaffah Central
 */
class DokuWebhookController extends Controller
{
    public function __construct(
        private readonly DokuClient $dokuClient
    ) {}

    public function handle(Request $request): JsonResponse
    {
        $relativeUrl = '/' . ltrim($request->path(), '/');

        // 1. Signature Verification
        if (!$this->dokuClient->verifyNotificationSignature($request, $relativeUrl)) {
            Log::warning('DokuWebhook: verifikasi signature gagal', [
                'ip'        => $request->ip(),
                'path'      => $relativeUrl,
                'signature' => $request->header('X-SIGNATURE'),
            ]);

            return response()->json([
                'status'          => 'error',
                'responseCode'    => '4017300',
                'responseMessage' => 'Unauthorized. Invalid Signature',
            ], 403);
        }

        $payload = $request->all();

        // 2. Parse Order ID, Status, Amount, Paid At
        // Mendukung format SNAP dan Direct API
        $orderId = $payload['trxId']
            ?? $payload['partnerReferenceNo']
            ?? $payload['order']['invoice_number']
            ?? $payload['invoiceNumber']
            ?? null;

        if (!$orderId) {
            Log::warning('DokuWebhook: order_id tidak ditemukan dalam payload', ['payload' => $payload]);
            return response()->json([
                'status'          => 'error',
                'responseCode'    => '4007300',
                'responseMessage' => 'Bad Request. Missing order ID',
            ], 400);
        }

        $rawStatus = strtoupper(
            $payload['transactionStatus']
            ?? $payload['paymentStatus']
            ?? $payload['transaction']['status']
            ?? $payload['status']
            ?? ''
        );

        $currency = $payload['amount']['currency']
            ?? $payload['totalAmount']['currency']
            ?? $payload['order']['currency']
            ?? null;

        if ($currency !== null && strtoupper((string) $currency) !== 'IDR') {
            Log::warning('DokuWebhook: mata uang tidak didukung', ['order_id' => $orderId, 'currency' => $currency]);
            return response()->json([
                'status'          => 'error',
                'responseCode'    => '4007302',
                'responseMessage' => 'Bad Request. Unsupported currency (only IDR is supported)',
            ], 400);
        }

        $grossAmount = $payload['totalAmount']['value']
            ?? $payload['amount']['value']
            ?? $payload['order']['amount']
            ?? $payload['amount']
            ?? '0';

        $providerRef = $payload['referenceNo']
            ?? $payload['transaction']['id']
            ?? $payload['approvalCode']
            ?? ('DOKU-' . $orderId);

        $paidAtStr = $payload['paidTime']
            ?? $payload['transaction']['date']
            ?? now()->toIso8601String();

        $paidAt = rescue(fn() => \Carbon\Carbon::parse($paidAtStr), now());

        $eventsToDispatch = [];

        try {
            DB::beginTransaction();

            $order = PaymentOrder::where('order_id', $orderId)
                ->with(['items.bill.student', 'items.posOrder'])
                ->lockForUpdate()
                ->first();

            if (!$order) {
                DB::rollBack();
                Log::warning('DokuWebhook: PaymentOrder tidak ditemukan', ['order_id' => $orderId]);
                return response()->json([
                    'status'          => 'error',
                    'responseCode'    => '4047300',
                    'responseMessage' => 'Order not found',
                ], 404);
            }

            // 3. Idempotency Check: Already PAID
            if ($order->status === 'PAID') {
                DB::rollBack();
                Log::info('DokuWebhook: duplikat notifikasi untuk order yang sudah PAID', [
                    'order_id' => $orderId,
                ]);
                return response()->json([
                    'status'          => 'success',
                    'responseCode'    => '2000700',
                    'responseMessage' => 'Successful (Already Settled)',
                    'message'         => 'Order already settled',
                ]);
            }

            // 4. Handle Settlement / Success
            $isSuccess = in_array($rawStatus, ['SUCCESS', 'SETTLEMENT', 'PAID', 'CAPTURE'], true)
                || ($payload['result']['status'] ?? '') === 'SUCCESS';

            if ($isSuccess) {
                // Validate Gross Amount matches Order Amount
                $expectedAmount = number_format((float) $order->amount, 2, '.', '');
                $receivedAmount = number_format((float) $grossAmount, 2, '.', '');

                if (bccomp($expectedAmount, $receivedAmount, 2) !== 0) {
                    DB::rollBack();
                    Log::error('DokuWebhook: nominal tidak cocok — transaksi ditolak', [
                        'order_id'        => $orderId,
                        'expected_amount' => $expectedAmount,
                        'received_amount' => $receivedAmount,
                    ]);

                    return response()->json([
                        'status'          => 'error',
                        'responseCode'    => '4007301',
                        'responseMessage' => 'Amount Mismatch',
                    ], 400);
                }

                // RACE CONDITION: Jika order sudah berstatus CANCELLED sebelum pembayaran tiba (Late Payment)
                if ($order->status === 'CANCELLED') {
                    $order->update([
                        'status'             => 'LATE_PAID',
                        'paid_at'            => $paidAt,
                        'provider_reference' => $providerRef,
                        'raw_response'       => $payload,
                    ]);
                    DB::commit();

                    Log::warning('DokuWebhook: LATE PAYMENT pada order yang sudah CANCELLED — dicatat sebagai LATE_PAID untuk rekonsiliasi manual', [
                        'order_id' => $orderId,
                        'amount'   => $grossAmount,
                        'paid_at'  => $paidAt->toIso8601String(),
                    ]);

                    return response()->json([
                        'status'          => 'success',
                        'responseCode'    => '2000700',
                        'responseMessage' => 'Successful (Late Payment Recorded for Reconciliation)',
                    ]);
                }

                // RACE CONDITION: Jika order dalam status CANCEL_REQUESTED saat webhook tiba: PAID MENANG!
                if ($order->status === 'CANCEL_REQUESTED') {
                    Log::info('DokuWebhook: Pembayaran diterima saat order dalam status CANCEL_REQUESTED — settlement diprioritaskan', [
                        'order_id' => $orderId,
                    ]);
                }

                $order->update([
                    'status'             => 'PAID',
                    'paid_at'            => $paidAt,
                    'provider_reference' => $providerRef,
                    'raw_response'       => $payload,
                ]);

                // Atomically settle all bill items & pos order items
                $isMultiBill = $order->items->count() > 1;

                foreach ($order->items as $item) {
                    if ($item->bill) {
                        $bill = $item->bill;
                        $billOrderId = $isMultiBill
                            ? "{$order->order_id}-B{$bill->id}"
                            : $order->order_id;

                        $bill->update([
                            'status'            => 'PAID',
                            'paid_amount'       => $bill->amount,
                            'paid_at'           => $paidAt,
                            'payment_method'    => 'DOKU',
                            'midtrans_order_id' => $billOrderId,
                        ]);

                        // Collect event for dispatching outside transaction
                        $eventsToDispatch[] = new PaymentReceived(
                            billId:        $bill->id,
                            studentId:     $bill->student_id,
                            nis:           $bill->student->nis ?? '',
                            amount:        (string) $bill->amount,
                            paymentMethod: 'DOKU',
                            provider:      'doku',
                            orderId:       "{$order->order_id}-B{$bill->id}",
                            paidAt:        $paidAt->toIso8601String(),
                        );
                    }

                    if ($item->posOrder) {
                        $pos = $item->posOrder;
                        $pos->update([
                            'payment_status'    => 'PAID',
                            'redemption_status' => 'PENDING',
                            'payment_method'    => 'DOKU',
                            'paid_amount'       => $pos->total_amount,
                        ]);

                        // Event for POS / Kantin payment
                        $eventsToDispatch[] = new PaymentReceived(
                            billId:        $pos->id,
                            studentId:     $pos->student_id,
                            nis:           $pos->student->nis ?? '',
                            amount:        (string) $pos->total_amount,
                            paymentMethod: 'DOKU',
                            provider:      'doku',
                            orderId:       "{$order->order_id}-POS{$pos->id}",
                            paidAt:        $paidAt->toIso8601String(),
                        );
                    }
                }

                DB::commit();

                // Dispatch events AFTER transaction commit
                foreach ($eventsToDispatch as $event) {
                    event($event);
                }

                Log::info('DokuWebhook: order berhasil disettle', [
                    'order_id'    => $orderId,
                    'total_items' => $order->items->count(),
                ]);

                return response()->json([
                    'status'          => 'success',
                    'responseCode'    => '2000700',
                    'responseMessage' => 'Successful',
                ]);
            }

            // 5. Handle Expiry / Cancellation
            if (in_array($rawStatus, ['EXPIRED', 'CANCELLED', 'FAILED', 'DENY'], true)) {
                // Jangan batalkan order yang sudah berstatus PAID atau LATE_PAID
                if (in_array($order->status, ['PAID', 'LATE_PAID'], true)) {
                    DB::rollBack();
                    return response()->json([
                        'status'          => 'success',
                        'responseCode'    => '2000700',
                        'responseMessage' => 'Ignored (Order already finalized as paid)',
                    ]);
                }

                $order->update([
                    'status'       => $rawStatus === 'EXPIRED' ? 'EXPIRED' : 'CANCELLED',
                    'raw_response' => $payload,
                ]);

                // Revert bills to UNPAID
                foreach ($order->items as $item) {
                    if ($item->bill && $item->bill->status !== 'PAID') {
                        $item->bill->update([
                            'status'            => 'UNPAID',
                            'midtrans_order_id' => null,
                        ]);
                    }
                }

                DB::commit();

                Log::info('DokuWebhook: order dibatalkan / expired', [
                    'order_id' => $orderId,
                    'status'   => $rawStatus,
                ]);

                return response()->json([
                    'status'          => 'success',
                    'responseCode'    => '2000700',
                    'responseMessage' => 'Order marked as ' . $rawStatus,
                ]);
            }

            DB::commit();

            return response()->json([
                'status'          => 'success',
                'responseCode'    => '2000700',
                'responseMessage' => 'Received',
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('DokuWebhook: exception handling webhook', [
                'order_id' => $orderId,
                'error'    => $e->getMessage(),
            ]);

            return response()->json([
                'responseCode'    => '5007300',
                'responseMessage' => 'Internal Server Error',
            ], 500);
        }
    }
}
