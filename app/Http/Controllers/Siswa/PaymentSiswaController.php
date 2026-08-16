<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\PaymentAttempt;
use App\Models\StudentBill;
use App\Services\FinancialAuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Midtrans\Config as MidtransConfig;
use Midtrans\Snap;

class PaymentSiswaController extends Controller
{
    public function __construct()
    {
        MidtransConfig::$serverKey    = config('services.midtrans.server_key');
        MidtransConfig::$isProduction = config('services.midtrans.is_production', false);
        MidtransConfig::$isSanitized  = config('services.midtrans.is_sanitized', true);
        MidtransConfig::$is3ds        = config('services.midtrans.is_3ds', true);
    }

    /**
     * Buat Midtrans Snap token untuk 1 tagihan.
     * Dipanggil via AJAX dari dashboard siswa.
     */
    public function createToken(Request $request, StudentBill $bill)
    {
        $student = Auth::guard('siswa')->user();

        if ((int) $bill->student_id !== (int) $student->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($bill->status === 'PAID') {
            return response()->json(['error' => 'Tagihan ini sudah dibayar.'], 400);
        }

        // Phase 3.7D: enforce ONE active pending attempt per bill.
        // Lock the bill row to prevent concurrent initiation race conditions.
        // If an existing pending attempt exists, return it immediately —
        // no new Snap call, no supersession needed.
        $existingAttempt = null;
        DB::transaction(function () use ($bill, &$existingAttempt) {
            // lockForUpdate() prevents a concurrent request from reading
            // the same pending attempt and also creating a new one.
            StudentBill::lockForUpdate()->find($bill->id);

            $existingAttempt = PaymentAttempt::where('student_bill_id', $bill->id)
                                             ->where('status', PaymentAttempt::STATUS_PENDING)
                                             ->latest('initiated_at')
                                             ->first();
        });

        if ($existingAttempt) {
            return response()->json([
                'snap_token'    => $existingAttempt->snap_token,
                'client_key'    => config('services.midtrans.client_key'),
                'order_id'      => $existingAttempt->order_id,
                'is_production' => config('services.midtrans.is_production', false),
            ]);
        }

        // Order ID unik: BILL-{id}-{random}-{timestamp}
        $orderId = 'BILL-' . $bill->id . '-' . Str::random(6) . '-' . time();

        $billName = $bill->name ?: ('Tagihan ' . ($bill->bill_month ?? '') . '/' . ($bill->bill_year ?? ''));

        $params = [
            'transaction_details' => [
                'order_id'     => $orderId,
                'gross_amount' => (int) $bill->amount,
            ],
            'customer_details' => [
                'first_name' => $student->name,
                'phone'      => $student->parent_phone ?? '',
            ],
            'item_details' => [
                [
                    'id'       => 'bill-' . $bill->id,
                    'price'    => (int) $bill->amount,
                    'quantity' => 1,
                    'name'     => mb_substr($billName, 0, 50), // Midtrans max 50 char
                ],
            ],
            'callbacks' => [
                'finish' => route('siswa.payment.success'),
            ],
        ];

        try {
            $snapToken = Snap::getSnapToken($params);

            // Phase 3.7D: supersede all pending attempts atomically inside
            // the same transaction as the new attempt creation.
            // lockForUpdate() on the bill prevents concurrent initiations from
            // both passing the "no pending attempt" check simultaneously.
            DB::transaction(function () use ($bill, $orderId, $snapToken, $request) {
                StudentBill::lockForUpdate()->find($bill->id);

                // Supersede any pending attempts that survived the pre-check
                // (guards against the narrow race between the pre-check and here).
                $superseded = PaymentAttempt::where('student_bill_id', $bill->id)
                                            ->where('status', PaymentAttempt::STATUS_PENDING)
                                            ->get();

                foreach ($superseded as $old) {
                    $old->update([
                        'status'     => PaymentAttempt::STATUS_CANCEL,
                        'expired_at' => now(),
                    ]);
                    FinancialAuditLogger::paymentAttemptCancelled(
                        $old,
                        AuditLog::SOURCE_WEB,
                        null,
                        $request
                    );
                }

                $attempt = PaymentAttempt::create([
                    'student_bill_id' => $bill->id,
                    'order_id'        => $orderId,
                    'snap_token'      => $snapToken,
                    'status'          => PaymentAttempt::STATUS_PENDING,
                    'gross_amount'    => $bill->amount,
                    'initiated_at'    => now(),
                    'source'          => PaymentAttempt::SOURCE_WEB,
                ]);

                // Maintain backward-compatible payment_token on the bill.
                // payment_token always mirrors the active attempt's snap_token.
                // PaymentAttempt.settled_at = Midtrans settlement_time (provider clock).
                // StudentBill.paid_at       = application processing time (set in callback).
                $bill->update(['payment_token' => $snapToken]);

                FinancialAuditLogger::paymentAttemptCreated(
                    $attempt,
                    AuditLog::SOURCE_WEB,
                    null,
                    $request
                );
            });

            return response()->json([
                'snap_token'    => $snapToken,
                'client_key'    => config('services.midtrans.client_key'),
                'order_id'      => $orderId,
                'is_production' => config('services.midtrans.is_production', false),
            ]);
        } catch (\Exception $e) {
            Log::error('PaymentSiswaController::createToken failed', [
                'bill_id' => $bill->id,
                'error'   => $e->getMessage(),
            ]);
            return response()->json([
                'error' => 'Gagal membuat token pembayaran: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Midtrans server-to-server webhook callback.
     * Route ini di-exclude dari CSRF di bootstrap/app.php.
     *
     * Validation chain (fail-fast, always HTTP 200 on rejection):
     *   1. Server key configured   (fail-closed)
     *   2. Signature valid         (Phase 1.1 — hash_equals SHA512)
     *   3. order_id present        (Phase 1.2-B)
     *   4. Bill exists             (Phase 1.2-B)
     *   5. Amount matches          (Phase 1.2-C)
     *   6. Idempotency             (Phase 1.2-D)
     *   7. Transaction status      (Phase 1.2-E)
     */
    public function callback(Request $request)
    {
        $payload = $request->all();

        // ── PHASE 1.1: Signature Verification ────────────────────────────────
        // Spec: SHA512(order_id + status_code + gross_amount + server_key)
        // Ref:  https://docs.midtrans.com/docs/verifying-data-integrity
        $orderId     = $payload['order_id']     ?? '';
        $statusCode  = $payload['status_code']  ?? '';
        $grossAmount = $payload['gross_amount'] ?? '';
        $serverKey   = config('services.midtrans.server_key');

        // ── PHASE 1.2-A: Fail-Closed Server Key Check ────────────────────────
        // If server key is empty, SHA512(payload + '') could match a crafted
        // request that also used an empty key. Reject before comparing.
        if (empty($serverKey)) {
            Log::error('Midtrans callback: MIDTRANS_SERVER_KEY is not configured', [
                'order_id' => $orderId,
                'ip'       => $request->ip(),
            ]);
            return response()->json(['message' => 'OK'], 200);
        }
        // ── End Fail-Closed Check ─────────────────────────────────────────────

        $expectedSig = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);
        $receivedSig = $payload['signature_key'] ?? '';

        // hash_equals() prevents timing-based side-channel attacks.
        // Returns HTTP 200 on failure so Midtrans does not retry indefinitely.
        if (! hash_equals($expectedSig, $receivedSig)) {
            Log::warning('Midtrans callback: invalid signature', [
                'order_id'   => $orderId,
                'ip'         => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
            return response()->json(['message' => 'OK'], 200);
        }
        // ── End Signature Verification ────────────────────────────────────────

        $transactionStatus = $payload['transaction_status'] ?? '';
        $fraudStatus       = $payload['fraud_status']       ?? 'accept';

        // ── PHASE 1.2-B: Order Validation ────────────────────────────────────
        // order_id format: BILL-{bill_id}-{rand6}-{timestamp}
        // Extract bill_id from the order_id string we generated in createToken().
        //
        // NOTE: payment_token stores the Snap token (a different identifier).
        // The canonical link between webhook and bill is the bill_id embedded
        // in the order_id — we do NOT look up by payment_token here.
        if (empty($orderId)) {
            Log::warning('Midtrans callback: missing order_id in payload', [
                'ip' => $request->ip(),
            ]);
            return response()->json(['message' => 'OK'], 200);
        }

        preg_match('/^BILL-(\d+)-/', $orderId, $matches);
        $billId = $matches[1] ?? null;

        if (! $billId) {
            Log::warning('Midtrans callback: unrecognised order_id format', [
                'order_id' => $orderId,
                'ip'       => $request->ip(),
            ]);
            return response()->json(['message' => 'OK'], 200);
        }

        $bill = StudentBill::find($billId);

        if (! $bill) {
            Log::warning('Midtrans callback: bill not found for order', [
                'order_id' => $orderId,
                'bill_id'  => $billId,
                'ip'       => $request->ip(),
            ]);
            return response()->json(['message' => 'OK'], 200);
        }
        // ── End Order Validation ──────────────────────────────────────────────

        // ── PHASE 1.2-C: Amount Validation ───────────────────────────────────
        // Cast both sides via round(float) → int to eliminate string/decimal
        // representation drift. e.g. "150000.00" == 150000 == 150000.00
        $receivedAmount = (int) round((float) $grossAmount);
        $expectedAmount = (int) round((float) $bill->amount);

        if ($receivedAmount !== $expectedAmount) {
            Log::error('Midtrans callback: amount mismatch — bill NOT marked PAID', [
                'order_id'        => $orderId,
                'bill_id'         => $bill->id,
                'expected_amount' => $expectedAmount,
                'received_amount' => $receivedAmount,
                'ip'              => $request->ip(),
            ]);
            return response()->json(['message' => 'OK'], 200);
        }
        // ── End Amount Validation ─────────────────────────────────────────────

        // ── PHASE 1.2-D: Idempotency Check ───────────────────────────────────
        // Basis: bill.status + bill.midtrans_order_id
        //
        // Case 1 — already PAID, same order_id:
        //   Duplicate webhook delivery for the same settlement → skip silently.
        //
        // Case 2 — already PAID, different order_id:
        //   Should not happen (one bill = one successful payment), but log as
        //   anomaly and skip — do NOT overwrite existing payment data.
        //
        // Case 3 — bill is UNPAID → fall through to status handling.
        if ($bill->status === 'PAID') {
            if ($bill->midtrans_order_id === $orderId) {
                Log::info('Midtrans callback: duplicate webhook ignored (already PAID)', [
                    'order_id' => $orderId,
                    'bill_id'  => $bill->id,
                ]);
            } else {
                Log::warning('Midtrans callback: bill already PAID with a different order_id', [
                    'order_id'          => $orderId,
                    'bill_id'           => $bill->id,
                    'existing_order_id' => $bill->midtrans_order_id,
                ]);
            }
            return response()->json(['message' => 'OK'], 200);
        }
        // ── End Idempotency Check ─────────────────────────────────────────────

        // ── PHASE 1.2-E: Transaction Status Handling ──────────────────────────
        if (in_array($transactionStatus, ['settlement', 'capture']) && $fraudStatus === 'accept') {

            // Successful payment — persist all fields atomically.
            // confirmed_by is explicitly NULL: Midtrans settlement is confirmed
            // by the payment gateway, not by a TU operator.
            DB::transaction(function () use ($bill, $orderId, $transactionStatus, $request, $payload) {

                // ── Phase 3.7D: attempt-level idempotency ─────────────────────
                // Look up the attempt for this specific order_id.
                // If already terminal: write SETTLEMENT_IGNORED and return early.
                // If pending: update it, then update the bill.
                $attempt = PaymentAttempt::where('order_id', $orderId)->first();

                if ($attempt) {
                    if (in_array($attempt->status, PaymentAttempt::TERMINAL_STATUSES)) {
                        // Attempt already settled/expired/cancelled — do NOT touch bill.
                        // Log as an operational exception (user may have been charged).
                        FinancialAuditLogger::paymentAttemptSettlementIgnored(
                            $attempt,
                            $payload,
                            $request
                        );
                        Log::warning('Midtrans callback: settlement ignored — attempt already terminal', [
                            'order_id'       => $orderId,
                            'attempt_status' => $attempt->status,
                            'bill_id'        => $bill->id,
                        ]);
                        return; // Early return — no bill mutation.
                    }

                    // Attempt is pending — settle it.
                    // PaymentAttempt.settled_at = Midtrans settlement_time (provider clock).
                    // StudentBill.paid_at       = application processing time (set below).
                    $attempt->update([
                        'status'         => $transactionStatus,
                        'payment_method' => $payload['payment_type']              ?? null,
                        'bank'           => $payload['bank']
                                            ?? ($payload['va_numbers'][0]['bank'] ?? null),
                        'va_number'      => $payload['va_numbers'][0]['va_number'] ?? null,
                        'transaction_id' => $payload['transaction_id']            ?? null,
                        'settled_at'     => $this->parseMidtransTimestamp(
                                               $payload['settlement_time'] ?? ($payload['transaction_time'] ?? null)
                                           ) ?? now(),
                        'expired_at'     => null,
                        'snap_token'     => null,
                    ]);
                } else {
                    // Legacy webhook — no PaymentAttempt record exists.
                    // Proceed with bill update only; do not fabricate an attempt.
                    Log::info('Midtrans callback: legacy webhook — no PaymentAttempt found, bill updated directly', [
                        'order_id' => $orderId,
                        'bill_id'  => $bill->id,
                    ]);
                }

                $bill->update([
                    'status'            => 'PAID',
                    'paid_at'           => now(),   // application clock — intentionally differs from settled_at
                    'payment_method'    => 'MIDTRANS',
                    'confirmed_by'      => null,    // Phase 2.3: gateway-confirmed, no operator
                    'midtrans_order_id' => $orderId,
                    'payment_token'     => null,
                ]);

                $bill->refresh();
                FinancialAuditLogger::paymentConfirmed(
                    $bill,
                    \App\Models\AuditLog::SOURCE_MIDTRANS,
                    null,
                    $request
                );
            });

            Log::info('Midtrans callback: payment settled', [
                'order_id'           => $orderId,
                'bill_id'            => $bill->id,
                'transaction_status' => $transactionStatus,
                'status_code'        => $statusCode,
                'gross_amount'       => $grossAmount,
            ]);

        } elseif (in_array($transactionStatus, ['expire', 'cancel', 'deny'])) {

            // Failed / expired — clear the active Snap token so the student
            // can initiate a fresh payment attempt. Bill stays UNPAID.
            // Phase 3.5: log PAYMENT_FAILED before clearing token (captures original token).
            DB::transaction(function () use ($bill, $orderId, $transactionStatus, $request, $payload) {

                // ── Phase 3.7D: attempt-level state machine ──────────────────
                $attempt = PaymentAttempt::where('order_id', $orderId)->first();

                if ($attempt && ! in_array($attempt->status, PaymentAttempt::TERMINAL_STATUSES)) {
                    $attempt->update([
                        'status'         => $transactionStatus,
                        'payment_method' => $payload['payment_type']              ?? null,
                        'bank'           => $payload['bank']
                                            ?? ($payload['va_numbers'][0]['bank'] ?? null),
                        'va_number'      => $payload['va_numbers'][0]['va_number'] ?? null,
                        'transaction_id' => $payload['transaction_id']            ?? null,
                        'expired_at'     => $this->parseMidtransTimestamp(
                                               $payload['expiry_time'] ?? ($payload['transaction_time'] ?? null)
                                           ) ?? now(),
                        'snap_token'     => null,
                    ]);
                    $attemptWasMutated = true;
                } elseif ($attempt && in_array($attempt->status, PaymentAttempt::TERMINAL_STATUSES)) {
                    // Already terminal — idempotent, no re-write.
                    Log::info('Midtrans callback: expire/cancel ignored — attempt already terminal', [
                        'order_id'       => $orderId,
                        'attempt_status' => $attempt->status,
                    ]);
                    $attemptWasMutated = false;
                } else {
                    Log::info('Midtrans callback: legacy webhook expire/cancel — no PaymentAttempt found', [
                        'order_id' => $orderId,
                        'bill_id'  => $bill->id,
                        'status'   => $transactionStatus,
                    ]);
                    $attemptWasMutated = true; // legacy path — write PAYMENT_FAILED
                }

                // Only clear payment_token if no other pending attempt is active
                // for this bill — prevents wiping a newer session's token.
                $hasOtherPending = PaymentAttempt::where('student_bill_id', $bill->id)
                    ->where('status', PaymentAttempt::STATUS_PENDING)
                    ->where('order_id', '!=', $orderId)
                    ->exists();

                // PAYMENT_FAILED only written if:
                //   a) the attempt was actually mutated (not already terminal), AND
                //   b) the bill is not already PAID.
                if ($attemptWasMutated && $bill->status !== 'PAID') {
                    FinancialAuditLogger::paymentFailed(
                        $bill,
                        $transactionStatus,
                        \App\Models\AuditLog::SOURCE_MIDTRANS,
                        $request
                    );

                    if (! $hasOtherPending) {
                        $bill->update(['payment_token' => null]);
                    }
                }
            });

            Log::info('Midtrans callback: payment not completed', [
                'order_id'           => $orderId,
                'bill_id'            => $bill->id,
                'transaction_status' => $transactionStatus,
            ]);

        } elseif ($transactionStatus === 'pending') {

            // Midtrans is waiting for bank confirmation — no state change.
            // A follow-up webhook will arrive when the status resolves.
            Log::info('Midtrans callback: payment pending, no state change', [
                'order_id' => $orderId,
                'bill_id'  => $bill->id,
            ]);

        } else {

            // Unhandled / unknown status — log and leave bill state unchanged.
            Log::warning('Midtrans callback: unhandled transaction_status', [
                'order_id'           => $orderId,
                'bill_id'            => $bill->id,
                'transaction_status' => $transactionStatus,
                'status_code'        => $statusCode,
            ]);
        }
        // ── End Transaction Status Handling ───────────────────────────────────

        return response()->json(['message' => 'OK'], 200);
    }

    /**
     * Parse a Midtrans timestamp string into a Carbon instance.
     * Midtrans uses "YYYY-MM-DD HH:MM:SS" in Asia/Jakarta timezone.
     * Returns null if the value is empty or unparseable.
     */
    private function parseMidtransTimestamp(?string $value): ?\Carbon\Carbon
    {
        if (empty($value)) {
            return null;
        }
        try {
            return \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $value, 'Asia/Jakarta')
                                  ->setTimezone(config('app.timezone', 'Asia/Jakarta'));
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Halaman struk/bukti pembayaran.
     *
     * PHASE 1.4 — IDOR fix: ownership enforced at the query level.
     * Bill is resolved only if it belongs to the authenticated student.
     * A bill ID belonging to another student returns 404, which does not
     * confirm to the requester whether that bill ID exists at all.
     *
     * Approach: query-scoped lookup (StudentBill::where('student_id')->findOrFail)
     * replaces implicit model binding + post-resolution ownership check.
     */
    public function struk(int $id)
    {
        $student = Auth::guard('siswa')->user();

        // Scoped lookup — only resolves if student_id matches the authenticated
        // student. findOrFail() throws ModelNotFoundException (→ 404) on any
        // mismatch or non-existent ID. No bill data is ever loaded for a
        // request that does not belong to this student.
        $bill = StudentBill::where('student_id', $student->id)
                           ->findOrFail($id);

        if ($bill->status !== 'PAID') {
            return redirect()->route('siswa.dashboard');
        }

        $schoolName    = \App\Models\Setting::where('key', 'school_name')->value('value') ?? 'Sekolah';
        $schoolAddress = \App\Models\Setting::where('key', 'school_address')->value('value') ?? '';

        return view('siswa.struk', compact('bill', 'student', 'schoolName', 'schoolAddress'));
    }

    /**
     * Halaman redirect setelah Midtrans Snap selesai.
     */
    public function success(Request $request)
    {
        return redirect()->route('siswa.dashboard')
                         ->with('success', 'Pembayaran berhasil diproses! Tagihan akan diperbarui setelah konfirmasi dari bank.');
    }
}
