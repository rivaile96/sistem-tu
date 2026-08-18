<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AuditLog;
use App\Models\PaymentAttempt;
use App\Models\PosOrder;
use App\Models\Student;
use App\Models\StudentBill;
use App\Services\FinancialAuditLogger;
use Midtrans\Config;
use Midtrans\Snap;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ParentApiController extends Controller
{
    // =================================================================
    // 1. LOGIN (Ortu login pakai NIS & No HP)
    // =================================================================
    public function login(Request $request)
    {
        $request->validate([
            'nis'   => 'required',
            'phone' => 'required',
        ]);

        $student = Student::where('nis', $request->nis)
                          ->where('parent_phone', $request->parent_phone ?? $request->phone)
                          ->first();

        if (! $student) {
            return response()->json(['message' => 'NIS atau No HP salah!'], 401);
        }

        // Phase 8.6 — Security: only active students can use the parent portal.
        // calon_siswa, keluar, graduated, alumni must not receive API tokens.
        if ($student->status !== 'active') {
            return response()->json(['message' => 'Akun siswa belum aktif atau sudah tidak terdaftar.'], 403);
        }

        $token = $student->createToken('ParentApp')->plainTextToken;

        return response()->json([
            'message' => 'Login Berhasil',
            'token'   => $token,
            'student' => [
                'id'         => $student->id,
                'name'       => $student->name,
                'nis'        => $student->nis,
                // kelas via relation (canonical since Phase 9.3)
                'class_name' => optional($student->kelas)->nama_kelas ?? '-',
                'kelas'      => $student->kelas_id ? [
                    'id'         => $student->kelas_id,
                    'nama_kelas' => optional($student->kelas)->nama_kelas ?? '-',
                ] : null,
            ],
        ]);
    }

    // =================================================================
    // 2. DASHBOARD (List Semua Tagihan)
    // =================================================================
    public function getHomeData(Request $request)
    {
        $student = $request->user();

        $sppBills = StudentBill::where('student_id', $student->id)
            ->where('type', 'SPP')
            ->where('status', 'UNPAID')
            ->orderBy('id', 'desc')
            ->get()
            ->map(fn ($bill) => [
                'type'            => 'SPP',
                'id'              => $bill->id,
                'title'           => $bill->name,
                'desc'            => 'Wajib Bulan Ini',
                'amount'          => $bill->amount,
                'original_amount' => $bill->original_amount ?? $bill->amount,
                'discount_amount' => $bill->discount_amount ?? 0,
                'status'          => $bill->status,
                'paid_at'         => $bill->paid_at?->toIso8601String(),
                'date'            => $bill->created_at->format('d M Y'),
            ]);

        $otherBills = StudentBill::where('student_id', $student->id)
            ->where('type', '!=', 'SPP')
            ->where('status', 'UNPAID')
            ->orderBy('id', 'desc')
            ->get()
            ->map(fn ($bill) => [
                'type'            => 'BILL',
                'id'              => $bill->id,
                'title'           => $bill->name,
                'desc'            => 'Tagihan Sekolah',
                'amount'          => $bill->amount,
                'original_amount' => $bill->original_amount ?? $bill->amount,
                'discount_amount' => $bill->discount_amount ?? 0,
                'status'          => $bill->status,
                'paid_at'         => $bill->paid_at?->toIso8601String(),
                'date'            => $bill->created_at->format('d M Y'),
            ]);

        $canteenDebts = PosOrder::where('student_id', $student->id)
            ->where('payment_status', 'UNPAID')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($trx) {
                $itemsCount = $trx->items ? $trx->items->count() : 0;
                return [
                    'type'   => 'POS',
                    'id'     => $trx->id,
                    'title'  => 'Jajan Kantin',
                    'desc'   => $trx->created_at->format('H:i') . " WIB • ({$itemsCount} Item)",
                    'amount' => $trx->total_amount,
                    'date'   => $trx->created_at->format('d M Y'),
                ];
            });

        $allBills   = $sppBills->merge($otherBills)->merge($canteenDebts);
        $grandTotal = $allBills->sum('amount');

        return response()->json([
            // v1 contract — backward-compat fields preserved
            'student_name' => $student->name,
            'nis'          => $student->nis,
            'class_name'   => optional($student->kelas)->nama_kelas ?? '-', // compat field
            // v1 enriched student object
            'student'      => [
                'id'     => $student->id,
                'nis'    => $student->nis,
                'name'   => $student->name,
                'status' => $student->status,
                'kelas'  => $student->kelas_id ? [
                    'id'         => $student->kelas_id,
                    'nama_kelas' => optional($student->kelas)->nama_kelas ?? '-',
                ] : null,
            ],
            'summary'      => [
                'total_tagihan' => $grandTotal,
                'count'         => $allBills->count(),
            ],
            'list_tagihan' => $allBills,
        ]);
    }

    // =================================================================
    // 3. CREATE PAYMENT
    //
    // Phase 3.1 — IDOR fix:
    //   BILL/SPP lookup is now scoped to the authenticated student.
    //   A parent cannot generate a Midtrans token for another student's bill
    //   by guessing the bill ID.
    //
    // POS orders are also scoped to the authenticated student.
    // =================================================================
    public function createPayment(Request $request)
    {
        $request->validate([
            'id'   => 'required|integer|min:1',
            'type' => 'required|string',
        ]);

        $student           = $request->user();
        $customerDetails   = [];
        $orderIdPrefix     = '';
        $amount            = 0;
        $itemName          = '';

        if ($request->type === 'BILL' || $request->type === 'SPP') {

            // Phase 3.1: scope lookup to authenticated student — prevents IDOR.
            $bill = StudentBill::where('id', $request->id)
                               ->where('student_id', $student->id)
                               ->first();

            if (! $bill) {
                return response()->json(['message' => 'Tagihan tidak ditemukan.'], 404);
            }

            if ($bill->status === 'PAID') {
                return response()->json(['message' => 'Tagihan sudah lunas.'], 400);
            }

            $amount        = $bill->amount;
            $orderIdPrefix = 'BILL';
            $itemName      = mb_substr($bill->name, 0, 50);

            $customerDetails = [
                'first_name' => $student->name,
                'phone'      => $student->parent_phone ?? '',
            ];

        } elseif ($request->type === 'POS') {

            // Scope POS lookup to authenticated student as well.
            $pos = PosOrder::where('id', $request->id)
                           ->where('student_id', $student->id)
                           ->first();

            if (! $pos) {
                return response()->json(['message' => 'Transaksi tidak ditemukan.'], 404);
            }

            if ($pos->payment_status === 'PAID') {
                return response()->json(['message' => 'Transaksi sudah lunas.'], 400);
            }

            $amount        = $pos->total_amount;
            $orderIdPrefix = 'POS';
            $itemName      = 'Jajan Kantin ' . $pos->created_at->format('d/m');

            $customerDetails = [
                'first_name' => $student->name,
                'phone'      => $student->parent_phone ?? '',
            ];

        } else {
            return response()->json(['message' => 'Tipe pembayaran tidak dikenal.'], 400);
        }

        // Phase 3.7D: check for existing pending attempt BEFORE calling Snap.
        // This fixes the ghost-session bug from Phase 3.7B where Snap was called
        // unconditionally and the new token was discarded after the fact.
        // Lock the bill row to prevent concurrent initiation race conditions.
        if (isset($bill)) {
            $existingAttempt = null;
            DB::transaction(function () use ($bill, &$existingAttempt) {
                StudentBill::lockForUpdate()->find($bill->id);
                $existingAttempt = PaymentAttempt::where('student_bill_id', $bill->id)
                                                 ->where('status', PaymentAttempt::STATUS_PENDING)
                                                 ->latest('initiated_at')
                                                 ->first();
            });

            if ($existingAttempt) {
                return response()->json([
                    'snap_token' => $existingAttempt->snap_token,
                    'order_id'   => $existingAttempt->order_id,
                ]);
            }
        }

        Config::$serverKey    = config('services.midtrans.server_key');
        Config::$isProduction = config('services.midtrans.is_production', false);
        Config::$isSanitized  = true;
        Config::$is3ds        = true;

        // Order ID format matches PaymentSiswaController for BILL type:
        //   BILL-{id}-{rand6}-{timestamp}
        // POS keeps its own format:
        //   POS-{id}-{rand6}-{timestamp}
        $orderId = $orderIdPrefix . '-' . $request->id . '-' . Str::random(6) . '-' . time();

        $params = [
            'transaction_details' => [
                'order_id'     => $orderId,
                'gross_amount' => (int) $amount,
            ],
            'customer_details' => $customerDetails,
            'item_details'     => [[
                'id'       => $orderIdPrefix . '-' . $request->id,
                'price'    => (int) $amount,
                'quantity' => 1,
                'name'     => $itemName,
            ]],
        ];

        try {
            $snapToken = Snap::getSnapToken($params);

            // Phase 3.7D: supersede all existing pending attempts atomically
            // and create the new attempt in a single transaction.
            if (isset($bill)) {
                DB::transaction(function () use ($bill, $orderId, $snapToken, $request) {
                    StudentBill::lockForUpdate()->find($bill->id);

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
                            AuditLog::SOURCE_API,
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
                        'source'          => PaymentAttempt::SOURCE_API,
                    ]);

                    // payment_token always mirrors the active attempt's snap_token.
                    // PaymentAttempt.settled_at = Midtrans settlement_time (provider clock).
                    // StudentBill.paid_at       = application processing time (set in callback).
                    $bill->update(['payment_token' => $snapToken]);

                    FinancialAuditLogger::paymentAttemptCreated(
                        $attempt,
                        AuditLog::SOURCE_API,
                        null,
                        $request
                    );
                });
            }

            return response()->json([
                'snap_token' => $snapToken,
                'order_id'   => $orderId,
            ]);

        } catch (\Exception $e) {
            Log::error('ParentApiController::createPayment Midtrans error', [
                'student_id' => $student->id,
                'type'       => $request->type,
                'id'         => $request->id,
                'error'      => $e->getMessage(),
            ]);
            return response()->json(['message' => 'Gagal membuat token pembayaran.'], 500);
        }
    }

    // =================================================================
    // 4. WEBHOOK (Callback Midtrans)
    //
    // Phase 3.1 — full parity with PaymentSiswaController::callback():
    //   - fail-closed server key check
    //   - hash_equals() signature verification (timing-safe)
    //   - regex order_id parsing
    //   - gross_amount validation against bill.amount
    //   - idempotency guard (already PAID → skip)
    //   - paid_at set on settlement/capture
    //   - midtrans_order_id stored for idempotency key
    //   - confirmed_by = NULL (gateway-confirmed, no operator)
    //   - expire/cancel/deny clears payment_token for retry
    //   - pending → no state change
    //   - always HTTP 200 to Midtrans
    // =================================================================
    public function callback(Request $request)
    {
        $payload     = $request->all();
        $orderId     = $payload['order_id']     ?? '';
        $statusCode  = $payload['status_code']  ?? '';
        $grossAmount = $payload['gross_amount'] ?? '';
        $serverKey   = config('services.midtrans.server_key');

        // ── 1. Fail-closed server key check ──────────────────────────────────
        if (empty($serverKey)) {
            Log::error('ParentApi callback: MIDTRANS_SERVER_KEY not configured', [
                'order_id' => $orderId,
                'ip'       => $request->ip(),
            ]);
            return response()->json(['status' => 'ok'], 200);
        }

        // ── 2. Signature verification (timing-safe) ───────────────────────────
        $expectedSig = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);
        $receivedSig = $payload['signature_key'] ?? '';

        if (! hash_equals($expectedSig, $receivedSig)) {
            Log::warning('ParentApi callback: invalid signature', [
                'order_id' => $orderId,
                'ip'       => $request->ip(),
            ]);
            return response()->json(['status' => 'ok'], 200);
        }

        $transactionStatus = $payload['transaction_status'] ?? '';
        $fraudStatus       = $payload['fraud_status']       ?? 'accept';

        // ── 3. Order ID routing ───────────────────────────────────────────────
        // Supported prefixes: BILL (student bill), POS (canteen order)
        if (empty($orderId)) {
            return response()->json(['status' => 'ok'], 200);
        }

        if (str_starts_with($orderId, 'BILL-')) {
            return $this->handleBillCallback(
                $orderId, $grossAmount, $transactionStatus, $fraudStatus, $request, $payload
            );
        }

        if (str_starts_with($orderId, 'POS-')) {
            return $this->handlePosCallback(
                $orderId, $grossAmount, $transactionStatus, $fraudStatus, $request
            );
        }

        Log::warning('ParentApi callback: unrecognised order_id prefix', [
            'order_id' => $orderId,
            'ip'       => $request->ip(),
        ]);
        return response()->json(['status' => 'ok'], 200);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // PRIVATE: handle BILL-* order IDs (StudentBill)
    // ─────────────────────────────────────────────────────────────────────────
    private function handleBillCallback(
        string $orderId,
        string $grossAmount,
        string $transactionStatus,
        string $fraudStatus,
        Request $request,
        array $payload = []
    ) {
        // Parse bill ID from BILL-{id}-{rand6}-{timestamp}
        preg_match('/^BILL-(\d+)-/', $orderId, $matches);
        $billId = $matches[1] ?? null;

        if (! $billId) {
            Log::warning('ParentApi callback: unrecognised BILL order_id format', [
                'order_id' => $orderId,
                'ip'       => $request->ip(),
            ]);
            return response()->json(['status' => 'ok'], 200);
        }

        $bill = StudentBill::find($billId);

        if (! $bill) {
            Log::warning('ParentApi callback: StudentBill not found', [
                'order_id' => $orderId,
                'bill_id'  => $billId,
                'ip'       => $request->ip(),
            ]);
            return response()->json(['status' => 'ok'], 200);
        }

        // ── Amount validation ─────────────────────────────────────────────────
        $receivedAmount = (int) round((float) $grossAmount);
        $expectedAmount = (int) round((float) $bill->amount);

        if ($receivedAmount !== $expectedAmount) {
            Log::error('ParentApi callback: amount mismatch on BILL — NOT marked PAID', [
                'order_id'        => $orderId,
                'bill_id'         => $bill->id,
                'expected_amount' => $expectedAmount,
                'received_amount' => $receivedAmount,
                'ip'              => $request->ip(),
            ]);
            return response()->json(['status' => 'ok'], 200);
        }

        // ── Idempotency guard ─────────────────────────────────────────────────
        if ($bill->status === 'PAID') {
            Log::info('ParentApi callback: BILL already PAID, duplicate webhook ignored', [
                'order_id'          => $orderId,
                'bill_id'           => $bill->id,
                'existing_order_id' => $bill->midtrans_order_id,
            ]);
            return response()->json(['status' => 'ok'], 200);
        }

        // ── Status handling ───────────────────────────────────────────────────
        if (in_array($transactionStatus, ['settlement', 'capture']) && $fraudStatus === 'accept') {

            DB::transaction(function () use ($bill, $orderId, $transactionStatus, $request, $payload) {

                // ── Phase 3.7D: attempt-level idempotency ─────────────────────
                $attempt = PaymentAttempt::where('order_id', $orderId)->first();

                if ($attempt) {
                    if (in_array($attempt->status, PaymentAttempt::TERMINAL_STATUSES)) {
                        // Already terminal — write SETTLEMENT_IGNORED and return.
                        FinancialAuditLogger::paymentAttemptSettlementIgnored(
                            $attempt,
                            $payload,
                            $request
                        );
                        Log::warning('ParentApi callback: settlement ignored — attempt already terminal', [
                            'order_id'       => $orderId,
                            'attempt_status' => $attempt->status,
                            'bill_id'        => $bill->id,
                        ]);
                        return;
                    }
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
                    Log::info('ParentApi callback: legacy webhook — no PaymentAttempt found, bill updated directly', [
                        'order_id' => $orderId,
                        'bill_id'  => $bill->id,
                    ]);
                }

                $bill->update([
                    'status'            => 'PAID',
                    'paid_at'           => now(),
                    'payment_method'    => 'MIDTRANS',
                    'confirmed_by'      => null,
                    'midtrans_order_id' => $orderId,
                    'payment_token'     => null,
                ]);

                $bill->refresh();
                FinancialAuditLogger::paymentConfirmed(
                    $bill,
                    AuditLog::SOURCE_MIDTRANS,
                    null,
                    $request
                );
            });

            Log::info('ParentApi callback: BILL payment settled', [
                'order_id'           => $orderId,
                'bill_id'            => $bill->id,
                'transaction_status' => $transactionStatus,
            ]);

        } elseif (in_array($transactionStatus, ['expire', 'cancel', 'deny'])) {

            DB::transaction(function () use ($bill, $orderId, $transactionStatus, $request, $payload) {

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
                    Log::info('ParentApi callback: expire/cancel ignored — attempt already terminal', [
                        'order_id'       => $orderId,
                        'attempt_status' => $attempt->status,
                    ]);
                    $attemptWasMutated = false;
                } else {
                    Log::info('ParentApi callback: legacy webhook expire/cancel — no PaymentAttempt found', [
                        'order_id' => $orderId,
                        'bill_id'  => $bill->id,
                        'status'   => $transactionStatus,
                    ]);
                    $attemptWasMutated = true; // legacy path — write PAYMENT_FAILED
                }

                $hasOtherPending = PaymentAttempt::where('student_bill_id', $bill->id)
                    ->where('status', PaymentAttempt::STATUS_PENDING)
                    ->where('order_id', '!=', $orderId)
                    ->exists();

                if ($attemptWasMutated && $bill->status !== 'PAID') {
                    FinancialAuditLogger::paymentFailed(
                        $bill,
                        $transactionStatus,
                        AuditLog::SOURCE_MIDTRANS,
                        $request
                    );

                    if (! $hasOtherPending) {
                        $bill->update(['payment_token' => null]);
                    }
                }
            });

            Log::info('ParentApi callback: BILL payment not completed, token cleared', [
                'order_id'           => $orderId,
                'bill_id'            => $bill->id,
                'transaction_status' => $transactionStatus,
            ]);

        } elseif ($transactionStatus === 'pending') {

            Log::info('ParentApi callback: BILL payment pending, no state change', [
                'order_id' => $orderId,
                'bill_id'  => $bill->id,
            ]);

        } else {

            Log::warning('ParentApi callback: unhandled transaction_status for BILL', [
                'order_id'           => $orderId,
                'bill_id'            => $bill->id,
                'transaction_status' => $transactionStatus,
            ]);
        }

        return response()->json(['status' => 'ok'], 200);
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

    // ─────────────────────────────────────────────────────────────────────────
    // PRIVATE: handle POS-* order IDs (PosOrder — canteen debts)
    // POS orders have their own payment lifecycle and do not use paid_at.
    // ─────────────────────────────────────────────────────────────────────────
    private function handlePosCallback(
        string $orderId,
        string $grossAmount,
        string $transactionStatus,
        string $fraudStatus,
        Request $request
    ) {
        // Parse POS order ID from POS-{id}-{rand6}-{timestamp}
        preg_match('/^POS-(\d+)-/', $orderId, $matches);
        $posId = $matches[1] ?? null;

        if (! $posId) {
            Log::warning('ParentApi callback: unrecognised POS order_id format', [
                'order_id' => $orderId,
                'ip'       => $request->ip(),
            ]);
            return response()->json(['status' => 'ok'], 200);
        }

        $pos = PosOrder::find($posId);

        if (! $pos) {
            Log::warning('ParentApi callback: PosOrder not found', [
                'order_id' => $orderId,
                'pos_id'   => $posId,
                'ip'       => $request->ip(),
            ]);
            return response()->json(['status' => 'ok'], 200);
        }

        // ── Amount validation ─────────────────────────────────────────────────
        $receivedAmount = (int) round((float) $grossAmount);
        $expectedAmount = (int) round((float) $pos->total_amount);

        if ($receivedAmount !== $expectedAmount) {
            Log::error('ParentApi callback: amount mismatch on POS — NOT marked PAID', [
                'order_id'        => $orderId,
                'pos_id'          => $pos->id,
                'expected_amount' => $expectedAmount,
                'received_amount' => $receivedAmount,
            ]);
            return response()->json(['status' => 'ok'], 200);
        }

        // ── Idempotency guard ─────────────────────────────────────────────────
        if ($pos->payment_status === 'PAID') {
            Log::info('ParentApi callback: POS already PAID, duplicate webhook ignored', [
                'order_id' => $orderId,
                'pos_id'   => $pos->id,
            ]);
            return response()->json(['status' => 'ok'], 200);
        }

        // ── Status handling ───────────────────────────────────────────────────
        if (in_array($transactionStatus, ['settlement', 'capture']) && $fraudStatus === 'accept') {

            DB::transaction(function () use ($pos) {
                $pos->update([
                    'payment_status' => 'PAID',
                    'payment_method' => 'MIDTRANS',
                    'paid_amount'    => $pos->total_amount,
                    'change_amount'  => 0,
                ]);
            });

            Log::info('ParentApi callback: POS payment settled', [
                'order_id'           => $orderId,
                'pos_id'             => $pos->id,
                'transaction_status' => $transactionStatus,
            ]);

        } elseif (in_array($transactionStatus, ['expire', 'cancel', 'deny'])) {

            Log::info('ParentApi callback: POS payment not completed', [
                'order_id'           => $orderId,
                'pos_id'             => $pos->id,
                'transaction_status' => $transactionStatus,
            ]);

        } else {

            Log::warning('ParentApi callback: unhandled transaction_status for POS', [
                'order_id'           => $orderId,
                'pos_id'             => $pos->id,
                'transaction_status' => $transactionStatus,
            ]);
        }

        return response()->json(['status' => 'ok'], 200);
    }
}
