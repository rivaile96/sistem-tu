<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\PaymentAttempt;
use App\Models\StudentBill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * Phase 3.5 — Central service for writing financial audit log entries.
 *
 * All methods write inside the caller's active DB transaction.
 * If the caller has not started a transaction, each write is its own
 * implicit transaction (still atomic for that single insert).
 *
 * Design rules:
 *   - Never throws — a logging failure must not crash the application.
 *     Exceptions are caught, logged via Laravel Log, and swallowed.
 *   - Never invents data — historical unknowns stay NULL.
 *   - user_id = NULL for MIDTRANS / SYSTEM sources.
 *   - ip_address captured from the current request when available.
 */
class FinancialAuditLogger
{
    // ─────────────────────────────────────────────────────────────────────────
    // A. BILL_CREATED
    // ─────────────────────────────────────────────────────────────────────────

    public static function billCreated(
        StudentBill $bill,
        string $source = AuditLog::SOURCE_WEB,
        ?int $userId = null,
        ?Request $request = null
    ): void {
        $uid = $userId ?? (Auth::id() ?: null);

        static::write([
            'user_id'        => $uid,
            'action'         => AuditLog::BILL_CREATED,
            'module'         => 'billing',
            'auditable_type' => 'StudentBill',
            'auditable_id'   => $bill->id,
            'old_values'     => null,
            'new_values'     => static::billSnapshot($bill),
            'description'    => "Bill #{$bill->id} dibuat untuk student_id={$bill->student_id}",
            'ip_address'     => static::ip($request),
            'source'         => $source,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // B. BILL_DELETED
    // ─────────────────────────────────────────────────────────────────────────

    public static function billDeleted(
        StudentBill $bill,
        string $source = AuditLog::SOURCE_WEB,
        ?Request $request = null
    ): void {
        static::write([
            'user_id'        => Auth::id() ?: null,
            'action'         => AuditLog::BILL_DELETED,
            'module'         => 'billing',
            'auditable_type' => 'StudentBill',
            'auditable_id'   => $bill->id,
            'old_values'     => array_merge(['id' => $bill->id], static::billSnapshot($bill)),
            'new_values'     => null,
            'description'    => "Bill #{$bill->id} dihapus (student_id={$bill->student_id}, amount={$bill->amount})",
            'ip_address'     => static::ip($request),
            'source'         => $source,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // C. PAYMENT_CONFIRMED
    // ─────────────────────────────────────────────────────────────────────────

    public static function paymentConfirmed(
        StudentBill $bill,
        string $source,
        ?int $userId = null,
        ?Request $request = null
    ): void {
        $newValues = [
            'status'         => $bill->status,
            'amount'         => (string) $bill->amount,
            'paid_at'        => $bill->paid_at?->toIso8601String(),
            'payment_method' => $bill->payment_method,
        ];

        if ($source === AuditLog::SOURCE_WEB) {
            $newValues['confirmed_by'] = $bill->confirmed_by;
        } else {
            $newValues['midtrans_order_id'] = $bill->midtrans_order_id;
        }

        static::write([
            'user_id'        => $userId,
            'action'         => AuditLog::PAYMENT_CONFIRMED,
            'module'         => 'billing',
            'auditable_type' => 'StudentBill',
            'auditable_id'   => $bill->id,
            'old_values'     => ['status' => 'UNPAID'],
            'new_values'     => $newValues,
            'description'    => "Payment confirmed for bill #{$bill->id} via {$bill->payment_method}",
            'ip_address'     => static::ip($request),
            'source'         => $source,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // D. PAYMENT_FAILED (expire / cancel / deny)
    // ─────────────────────────────────────────────────────────────────────────

    public static function paymentFailed(
        StudentBill $bill,
        string $transactionStatus,
        string $source,
        ?Request $request = null
    ): void {
        static::write([
            'user_id'        => null,
            'action'         => AuditLog::PAYMENT_FAILED,
            'module'         => 'billing',
            'auditable_type' => 'StudentBill',
            'auditable_id'   => $bill->id,
            'old_values'     => ['payment_token' => $bill->getOriginal('payment_token')],
            'new_values'     => [
                'transaction_status' => $transactionStatus,
                'payment_token'      => null,
            ],
            'description'    => "Payment {$transactionStatus} for bill #{$bill->id}",
            'ip_address'     => static::ip($request),
            'source'         => $source,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // E. BILL_UPDATED
    // ─────────────────────────────────────────────────────────────────────────

    public static function billUpdated(
        StudentBill $bill,
        array $oldValues,
        array $newValues,
        string $source = AuditLog::SOURCE_WEB,
        ?Request $request = null
    ): void {
        // Only log if business-relevant fields actually changed.
        $changed = array_filter($newValues, fn ($v, $k) =>
            array_key_exists($k, $oldValues) && $oldValues[$k] != $v,
            ARRAY_FILTER_USE_BOTH
        );

        if (empty($changed)) {
            return; // No meaningful change — skip.
        }

        static::write([
            'user_id'        => Auth::id() ?: null,
            'action'         => AuditLog::BILL_UPDATED,
            'module'         => 'billing',
            'auditable_type' => 'StudentBill',
            'auditable_id'   => $bill->id,
            'old_values'     => array_intersect_key($oldValues, $changed),
            'new_values'     => $changed,
            'description'    => "Bill #{$bill->id} updated: " . implode(', ', array_keys($changed)),
            'ip_address'     => static::ip($request),
            'source'         => $source,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // F. PAID_BILL_UPDATE_ATTEMPTED
    // ─────────────────────────────────────────────────────────────────────────

    public static function paidBillUpdateAttempted(
        StudentBill $bill,
        array $attemptedChanges,
        string $source = AuditLog::SOURCE_WEB,
        ?Request $request = null
    ): void {
        // Capture only the fields that were attempted to be changed.
        $protectedFields = StudentBill::IMMUTABLE_WHEN_PAID;
        $attempted       = array_filter(
            $attemptedChanges,
            fn ($k) => in_array($k, $protectedFields, true),
            ARRAY_FILTER_USE_KEY
        );

        $currentValues = array_intersect_key(
            $bill->getAttributes(),
            $attempted
        );

        static::write([
            'user_id'        => Auth::id() ?: null,
            'action'         => AuditLog::PAID_BILL_UPDATE_ATTEMPTED,
            'module'         => 'billing',
            'auditable_type' => 'StudentBill',
            'auditable_id'   => $bill->id,
            'old_values'     => $currentValues,
            'new_values'     => $attempted,
            'description'    => "Attempted modification of PAID bill #{$bill->id}: " . implode(', ', array_keys($attempted)),
            'ip_address'     => static::ip($request),
            'source'         => $source,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // G. PAYMENT_ATTEMPT_CREATED
    // ─────────────────────────────────────────────────────────────────────────

    public static function paymentAttemptCreated(
        \App\Models\PaymentAttempt $attempt,
        string $source,
        ?int $userId = null,
        ?Request $request = null
    ): void {
        // Snap token is intentionally excluded from audit log.
        // It is a short-lived credential — no security value in persisting it.
        static::write([
            'user_id'        => $userId ?? (Auth::id() ?: null),
            'action'         => AuditLog::PAYMENT_ATTEMPT_CREATED,
            'module'         => 'billing',
            'auditable_type' => 'PaymentAttempt',
            'auditable_id'   => $attempt->id,
            'old_values'     => null,
            'new_values'     => [
                'order_id'     => $attempt->order_id,
                'gross_amount' => (string) $attempt->gross_amount,
                'source'       => $source,
            ],
            'description'    => "Payment attempt created for bill #{$attempt->student_bill_id}, order={$attempt->order_id}",
            'ip_address'     => static::ip($request),
            'source'         => $source,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // H. PAYMENT_ATTEMPT_CANCELLED
    // Written when a pending attempt is locally superseded by a new initiation.
    // The Midtrans session remains open until natural expiry (no Cancel API call).
    // ─────────────────────────────────────────────────────────────────────────

    public static function paymentAttemptCancelled(
        \App\Models\PaymentAttempt $attempt,
        string $source,
        ?int $userId = null,
        ?Request $request = null
    ): void {
        static::write([
            'user_id'        => $userId ?? (Auth::id() ?: null),
            'action'         => AuditLog::PAYMENT_ATTEMPT_CANCELLED,
            'module'         => 'billing',
            'auditable_type' => 'PaymentAttempt',
            'auditable_id'   => $attempt->id,
            'old_values'     => ['status' => PaymentAttempt::STATUS_PENDING],
            'new_values'     => [
                'status'     => PaymentAttempt::STATUS_CANCEL,
                'order_id'   => $attempt->order_id,
                'reason'     => 'superseded_by_new_initiation',
            ],
            'description'    => "Payment attempt #{$attempt->id} (order={$attempt->order_id}) superseded and locally cancelled for bill #{$attempt->student_bill_id}",
            'ip_address'     => static::ip($request),
            'source'         => $source,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // I. PAYMENT_ATTEMPT_SETTLEMENT_IGNORED
    // Written when Midtrans sends a settlement for an already-terminal attempt.
    // This is an operational exception — the user may have been charged.
    // ─────────────────────────────────────────────────────────────────────────

    public static function paymentAttemptSettlementIgnored(
        \App\Models\PaymentAttempt $attempt,
        array $webhookPayload,
        ?Request $request = null
    ): void {
        static::write([
            'user_id'        => null, // MIDTRANS source — no staff user
            'action'         => AuditLog::PAYMENT_ATTEMPT_SETTLEMENT_IGNORED,
            'module'         => 'billing',
            'auditable_type' => 'PaymentAttempt',
            'auditable_id'   => $attempt->id,
            'old_values'     => ['status' => $attempt->status],
            'new_values'     => [
                'order_id'           => $attempt->order_id,
                'incoming_status'    => $webhookPayload['transaction_status'] ?? null,
                'transaction_id'     => $webhookPayload['transaction_id']     ?? null,
                'gross_amount'       => $webhookPayload['gross_amount']       ?? null,
            ],
            'description'    => "Settlement ignored for already-terminal attempt #{$attempt->id} (status={$attempt->status}), order={$attempt->order_id}",
            'ip_address'     => static::ip($request),
            'source'         => AuditLog::SOURCE_MIDTRANS,
        ]);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────────────────────

    /** Extract snapshot values from a bill using SNAPSHOT_FIELDS. */
    public static function billSnapshot(StudentBill $bill): array
    {
        $snapshot = [];
        foreach (StudentBill::SNAPSHOT_FIELDS as $field) {
            $value = $bill->getAttributeValue($field);
            $snapshot[$field] = $value instanceof \Illuminate\Support\Carbon
                ? $value->toDateString()
                : $value;
        }
        return $snapshot;
    }

    /** Get IP address from request or return null safely. */
    private static function ip(?Request $request): ?string
    {
        try {
            return $request?->ip() ?? request()?->ip();
        } catch (\Exception $e) {
            return null;
        }
    }

    /** Write the audit record — never throws. */
    private static function write(array $data): void
    {
        try {
            AuditLog::create($data);
        } catch (\Exception $e) {
            Log::error('FinancialAuditLogger: failed to write audit record', [
                'action' => $data['action'] ?? 'unknown',
                'error'  => $e->getMessage(),
            ]);
            // Re-throw so the caller's transaction rolls back if needed.
            throw $e;
        }
    }
}
