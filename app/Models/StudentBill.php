<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentBill extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'paid_at'  => 'datetime',
        'due_date' => 'date',
        'amount'   => 'decimal:2',
    ];

    // ── Auditable fields snapshot ─────────────────────────────────────────────
    // Used by FinancialAuditLogger to capture old/new values.

    /** Fields included in BILL_CREATED / BILL_DELETED snapshots. */
    public const SNAPSHOT_FIELDS = [
        'student_id', 'type', 'amount', 'status',
        'bill_month', 'bill_year', 'due_date', 'name',
    ];

    /** Fields watched for BILL_UPDATED meaningful-change detection. */
    public const TRACKED_FIELDS = [
        'amount', 'name', 'type', 'bill_month', 'bill_year', 'due_date',
    ];

    /**
     * Fields that become immutable once a bill is PAID.
     *
     * Phase 3.3: PAID financial records must not have their core financial
     * identity changed. Payment fields (paid_at, payment_method, confirmed_by,
     * midtrans_order_id, payment_token, status) are intentionally excluded
     * from this list — the Midtrans callback and pay() method need to write
     * those fields to transition UNPAID → PAID.
     */
    public const IMMUTABLE_WHEN_PAID = [
        'amount',
        'student_id',
        'type',
        'bill_month',
        'bill_year',
    ];

    /**
     * Intercept every update to enforce PAID immutability.
     *
     * Throws a RuntimeException (caught by the controller try/catch) if any
     * attempt is made to change a protected field on a PAID bill.
     * This fires on Model::save(), Model::update(), and Model::fill() + save().
     */
    protected static function booted(): void
    {
        static::updating(function (self $bill) {
            if ($bill->getOriginal('status') !== 'PAID') {
                return; // bill not yet paid — all edits allowed
            }

            $dirty = array_keys($bill->getDirty());

            foreach (self::IMMUTABLE_WHEN_PAID as $field) {
                if (in_array($field, $dirty, true)) {
                    throw new \RuntimeException(
                        "Field '{$field}' tidak dapat diubah karena tagihan sudah berstatus PAID."
                    );
                }
            }
        });
    }

    // ── Relationships ─────────────────────────────────────────────────────────

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function confirmedBy()
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items()
    {
        return $this->hasMany(BillItem::class, 'student_bill_id');
    }

    public function paymentAttempts()
    {
        return $this->hasMany(PaymentAttempt::class, 'student_bill_id');
    }

    // ── Accessors ─────────────────────────────────────────────────────────────

    public function getFormattedAmountAttribute(): string
    {
        return 'Rp ' . number_format($this->amount, 0, ',', '.');
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'PAID'    => 'bg-green-100 text-green-700 border-green-200',
            'UNPAID'  => 'bg-red-100 text-red-700 border-red-200',
            'PARTIAL' => 'bg-yellow-100 text-yellow-700 border-yellow-200',
            default   => 'bg-gray-100 text-gray-700'
        };
    }
}

