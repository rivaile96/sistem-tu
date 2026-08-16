<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Phase 3.7A — Payment attempt ledger.
 *
 * Represents one individual Midtrans payment session for a StudentBill.
 * A bill may accumulate many attempts before one settles.
 *
 * @property int         $id
 * @property int         $student_bill_id
 * @property string      $order_id
 * @property string|null $snap_token
 * @property string|null $transaction_id
 * @property string      $status
 * @property string|null $payment_method
 * @property string|null $bank
 * @property string|null $va_number
 * @property float       $gross_amount
 * @property \Carbon\Carbon      $initiated_at
 * @property \Carbon\Carbon|null $settled_at
 * @property \Carbon\Carbon|null $expired_at
 * @property string|null $source
 */
class PaymentAttempt extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'gross_amount' => 'decimal:2',
        'initiated_at' => 'datetime',
        'settled_at'   => 'datetime',
        'expired_at'   => 'datetime',
    ];

    // ── Status constants ──────────────────────────────────────────────────────
    // Mirror Midtrans transaction_status values exactly.

    const STATUS_PENDING    = 'pending';
    const STATUS_SETTLEMENT = 'settlement';
    const STATUS_CAPTURE    = 'capture';
    const STATUS_EXPIRE     = 'expire';
    const STATUS_CANCEL     = 'cancel';
    const STATUS_DENY       = 'deny';

    const TERMINAL_STATUSES = [
        self::STATUS_SETTLEMENT,
        self::STATUS_CAPTURE,
        self::STATUS_EXPIRE,
        self::STATUS_CANCEL,
        self::STATUS_DENY,
    ];

    const SUCCESS_STATUSES = [
        self::STATUS_SETTLEMENT,
        self::STATUS_CAPTURE,
    ];

    const FAILED_STATUSES = [
        self::STATUS_EXPIRE,
        self::STATUS_CANCEL,
        self::STATUS_DENY,
    ];

    // ── Source constants ──────────────────────────────────────────────────────

    const SOURCE_WEB    = 'WEB';
    const SOURCE_API    = 'API';
    const SOURCE_SYSTEM = 'SYSTEM';

    // ── Relationships ─────────────────────────────────────────────────────────

    public function bill()
    {
        return $this->belongsTo(StudentBill::class, 'student_bill_id');
    }

    // ── Accessors ─────────────────────────────────────────────────────────────

    public function isSettled(): bool
    {
        return in_array($this->status, self::SUCCESS_STATUSES, true);
    }

    public function isFailed(): bool
    {
        return in_array($this->status, self::FAILED_STATUSES, true);
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }
}
