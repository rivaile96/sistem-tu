<?php

namespace App\Http\Controllers;

use App\Events\PaymentReceived;
use App\Models\PosOrder;
use App\Models\StudentBill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * MidtransController — Hardened webhook handler.
 *
 * SECURITY:
 * - Signature validated before any DB read/write.
 * - Amount validated against stored bill amount (not trusted from webhook).
 * - Bill looked up by midtrans_order_id (not by parsing order_id string).
 * - lockForUpdate() prevents concurrent settlement race.
 * - PAID bill cannot regress to any earlier status.
 * - Duplicate webhook: idempotent — no duplicate financial mutation.
 *
 * DO NOT log server key, snap token, or payment credentials.
 */
class MidtransController extends Controller
{
    // =====================================================================
    // PUBLIC WEBHOOK ENTRY
    // =====================================================================

    /**
     * POST /api/midtrans/webhook
     * POST /api/midtrans-callback  (legacy alias)
     *
     * Always returns HTTP 200 — Midtrans will retry on non-200.
     * Internal errors are logged, not surfaced to Midtrans.
     */
    public function callback(Request $request)
    {
        $orderId    = $request->input('order_id', '');
        $statusCode = $request->input('status_code', '');
        $grossAmt   = $request->input('gross_amount', '');
        $sigKey     = $request->input('signature_key', '');
        $txStatus   = $request->input('transaction_status', '');

        // ── 1. SIGNATURE VALIDATION ──────────────────────────────────────
        $serverKey = config('services.midtrans.server_key');

        if (empty($serverKey)) {
            Log::error('MidtransController: MIDTRANS_SERVER_KEY tidak dikonfigurasi');
            return response()->json(['message' => 'Server configuration error'], 500);
        }

        $expected = hash('sha512', $orderId . $statusCode . $grossAmt . $serverKey);

        if (!hash_equals($expected, $sigKey)) {
            Log::warning('MidtransController: invalid signature', [
                'order_id' => $orderId,
                'ip'       => $request->ip(),
            ]);
            return response()->json(['message' => 'Invalid signature'], 403);
        }

        // ── 2. ROUTE BY ORDER PREFIX ─────────────────────────────────────
        if (str_starts_with($orderId, 'BILL-')) {
            $this->handleBillPayment($orderId, $txStatus, $grossAmt, $request);
        } elseif (str_starts_with($orderId, 'POS-') || str_starts_with($orderId, 'TRX-')) {
            $this->handlePosPayment($orderId, $txStatus, $request);
        } else {
            Log::warning('MidtransController: unknown order prefix', ['order_id' => $orderId]);
        }

        return response()->json(['message' => 'OK']);
    }

    // =====================================================================
    // BILL (student_bills) PAYMENT HANDLER
    // =====================================================================

    private function handleBillPayment(
        string  $orderId,
        string  $txStatus,
        string  $grossAmt,
        Request $request
    ): void {
        // $eventPayload is set inside the transaction and dispatched OUTSIDE.
        // Dispatching inside a transaction causes Event::fake() to miss the event
        // in tests, and is also semantically wrong (event should fire after commit).
        $eventPayload = null;

        try {
            DB::transaction(function () use ($orderId, $txStatus, $grossAmt, $request, &$eventPayload) {

                $bill = StudentBill::where('midtrans_order_id', $orderId)
                    ->lockForUpdate()
                    ->first();

                if (!$bill) {
                    Log::warning('MidtransController: bill not found for order', ['order_id' => $orderId]);
                    return;
                }

                // ── IDEMPOTENCY: PAID bill never regresses ────────────────
                if ($bill->status === 'PAID') {
                    $this->auditLog('PAYMENT_DUPLICATE', $bill->id, $orderId, $txStatus, $request);
                    Log::info('MidtransController: duplicate webhook for already-PAID bill', [
                        'order_id' => $orderId, 'bill_id' => $bill->id,
                    ]);
                    return;
                }

                // ── SETTLEMENT / CAPTURE ──────────────────────────────────
                if (in_array($txStatus, ['settlement', 'capture'])) {

                    // Amount validation — compare bill.amount vs webhook gross_amount
                    $billAmt    = number_format((float) $bill->amount, 2, '.', '');
                    $webhookAmt = number_format((float) $grossAmt,     2, '.', '');

                    if (bccomp($billAmt, $webhookAmt, 2) !== 0) {
                        Log::error('MidtransController: amount mismatch — REJECTING settlement', [
                            'order_id'        => $orderId,
                            'bill_id'         => $bill->id,
                            'expected_amount' => $billAmt,
                            'received_amount' => $grossAmt,
                        ]);
                        $this->auditLog('PAYMENT_FAILED', $bill->id, $orderId, 'amount_mismatch', $request);
                        return;
                    }

                    $paidAt = now();

                    $bill->update([
                        'status'         => 'PAID',
                        'paid_amount'    => $bill->amount,
                        'paid_at'        => $paidAt,
                        'payment_method' => 'MIDTRANS',
                        'payment_token'  => null,
                    ]);

                    $this->auditLog('PAYMENT_SETTLED', $bill->id, $orderId, $txStatus, $request);

                    // Collect payload — dispatched AFTER transaction commit
                    $eventPayload = new PaymentReceived(
                        billId:        $bill->id,
                        studentId:     $bill->student_id,
                        nis:           $bill->student->nis ?? '',
                        amount:        (string) $bill->amount,
                        paymentMethod: 'MIDTRANS',
                        provider:      'midtrans',
                        orderId:       $orderId,
                        paidAt:        $paidAt->toIso8601String(),
                    );

                    Log::info('MidtransController: bill settled', [
                        'bill_id' => $bill->id, 'order_id' => $orderId,
                    ]);

                // ── PENDING ───────────────────────────────────────────────
                } elseif ($txStatus === 'pending') {
                    if ($bill->status === 'UNPAID') {
                        $bill->update(['status' => 'PENDING']);
                        $this->auditLog('PAYMENT_PENDING', $bill->id, $orderId, $txStatus, $request);
                    }

                // ── EXPIRE / CANCEL / DENY ────────────────────────────────
                } elseif (in_array($txStatus, ['expire', 'cancel', 'deny'])) {
                    if ($bill->status !== 'PAID') {
                        $bill->update([
                            'status'            => 'UNPAID',
                            'midtrans_order_id' => null,
                        ]);
                        $this->auditLog('PAYMENT_FAILED', $bill->id, $orderId, $txStatus, $request);
                    }

                // ── REFUND ────────────────────────────────────────────────
                } elseif ($txStatus === 'refund') {
                    $this->auditLog('PAYMENT_REFUNDED', $bill->id, $orderId, $txStatus, $request);
                    Log::info('MidtransController: refund received — no automatic reversal (future accounting task)', [
                        'bill_id' => $bill->id, 'order_id' => $orderId,
                    ]);
                }
            });

            // ── DISPATCH EVENT AFTER COMMIT ───────────────────────────────
            if ($eventPayload !== null) {
                event($eventPayload);
            }

        } catch (\Throwable $e) {
            Log::error('MidtransController: exception in handleBillPayment', [
                'order_id' => $orderId, 'error' => $e->getMessage(),
            ]);
        }
    }

    // =====================================================================
    // POS (pos_orders) PAYMENT HANDLER — preserved from original
    // =====================================================================

    private function handlePosPayment(string $orderId, string $txStatus, Request $request): void
    {
        try {
            $order = PosOrder::where('transaction_code', $orderId)->first();

            // Fallback: try by ID from order_id format POS-{id}-{ts} or TRX-{id}-{ts}
            if (!$order) {
                $parts = explode('-', $orderId);
                $id    = $parts[1] ?? null;
                if ($id && is_numeric($id)) {
                    $order = PosOrder::find($id);
                }
            }

            if (!$order) {
                Log::warning('MidtransController: POS order tidak ditemukan', [
                    'order_id' => $orderId,
                ]);
                return;
            }

            // Idempotency: already PAID
            if ($order->payment_status === 'PAID') {
                return;
            }

            if (in_array($txStatus, ['settlement', 'capture'])) {
                $order->update([
                    'payment_status'    => 'PAID',
                    'redemption_status' => 'PENDING',
                    'payment_method'    => 'MIDTRANS',
                ]);
            } elseif (in_array($txStatus, ['expire', 'cancel'])) {
                $order->update(['payment_status' => 'CANCELLED']);
                // Restore stock for each cancelled order item
                foreach ($order->items as $orderItem) {
                    \App\Models\PosItem::where('id', $orderItem->pos_item_id)
                        ->increment('stock', $orderItem->quantity);
                }
            }
        } catch (\Throwable $e) {
            Log::error('MidtransController: exception in handlePosPayment', [
                'order_id' => $orderId,
                'error'    => $e->getMessage(),
            ]);
        }
    }

    // =====================================================================
    // AUDIT LOG
    // =====================================================================

    private function auditLog(
        string  $action,
        int     $billId,
        string  $orderId,
        string  $txStatus,
        Request $request
    ): void {
        try {
            DB::table('audit_logs')->insert([
                'user_id'     => null, // webhook — no user
                'action'      => $action,
                'module'      => 'FINANCE_CONFIG',
                'target_type' => 'StudentBill',
                'target_id'   => $billId,
                'description' => json_encode([
                    'order_id'         => $orderId,
                    'transaction_status'=> $txStatus,
                    'provider'         => 'midtrans',
                ]),
                'old_value'   => null,
                'new_value'   => null,
                'ip_address'  => $request->ip(),
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('MidtransController: audit log gagal', ['error' => $e->getMessage()]);
        }
    }
}
