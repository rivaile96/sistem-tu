<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\StudentBill;
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

            // Simpan snap token ke record tagihan untuk referensi UI.
            // NOTE: payment_token = Snap token (NOT the order_id).
            // order_id is embedded in the Snap token params above and will
            // arrive back via webhook in payload['order_id'].
            $bill->update(['payment_token' => $snapToken]);

            return response()->json([
                'snap_token'    => $snapToken,
                'client_key'    => config('services.midtrans.client_key'),
                'order_id'      => $orderId,
                'is_production' => config('services.midtrans.is_production', false),
            ]);
        } catch (\Exception $e) {
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
            DB::transaction(function () use ($bill, $orderId) {
                $bill->update([
                    'status'            => 'PAID',
                    'paid_at'           => now(),
                    'payment_method'    => 'MIDTRANS',
                    'midtrans_order_id' => $orderId,
                    'payment_token'     => null,    // clear active Snap token after success
                ]);
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
            // paid_at is NOT set. midtrans_order_id is NOT set.
            $bill->update([
                'payment_token' => null,
            ]);

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
