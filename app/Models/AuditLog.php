<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Phase 3.5 — Structured financial audit log.
 *
 * Each row records one business event. Immutable by design:
 * audit entries are never updated after insertion.
 *
 * Columns:
 *   user_id        — authenticated actor (NULL for gateway/system events)
 *   action         — BILL_CREATED | BILL_DELETED | PAYMENT_CONFIRMED |
 *                    PAYMENT_FAILED | BILL_UPDATED | PAID_BILL_UPDATE_ATTEMPTED
 *   module         — billing
 *   auditable_type — model class short name, e.g. StudentBill
 *   auditable_id   — PK of the audited record (no FK — generic)
 *   old_values     — JSON snapshot of state before event
 *   new_values     — JSON snapshot of state after event
 *   description    — human-readable summary (optional)
 *   ip_address     — IPv4 or IPv6 of the request origin
 *   source         — WEB | API | MIDTRANS | SYSTEM
 */
class AuditLog extends Model
{
    protected $fillable = [
        'user_id',
        'action',
        'module',
        'auditable_type',
        'auditable_id',
        'old_values',
        'new_values',
        'description',
        'ip_address',
        'source',
    ];

    protected $casts = [
        'old_values'  => 'array',
        'new_values'  => 'array',
        'created_at'  => 'datetime',
        'updated_at'  => 'datetime',
    ];

    // ── Relationships ─────────────────────────────────────────────────────────

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // ── Action constants ──────────────────────────────────────────────────────

    const BILL_CREATED               = 'BILL_CREATED';
    const BILL_DELETED               = 'BILL_DELETED';
    const PAYMENT_CONFIRMED          = 'PAYMENT_CONFIRMED';
    const PAYMENT_FAILED             = 'PAYMENT_FAILED';
    const BILL_UPDATED               = 'BILL_UPDATED';
    const PAID_BILL_UPDATE_ATTEMPTED = 'PAID_BILL_UPDATE_ATTEMPTED';
    const PAYMENT_ATTEMPT_CREATED              = 'PAYMENT_ATTEMPT_CREATED';
    const PAYMENT_ATTEMPT_CANCELLED            = 'PAYMENT_ATTEMPT_CANCELLED';
    const PAYMENT_ATTEMPT_SETTLEMENT_IGNORED   = 'PAYMENT_ATTEMPT_SETTLEMENT_IGNORED';

    // ── Source constants ──────────────────────────────────────────────────────

    const SOURCE_WEB      = 'WEB';
    const SOURCE_API      = 'API';
    const SOURCE_MIDTRANS = 'MIDTRANS';
    const SOURCE_SYSTEM   = 'SYSTEM';
}