<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\PaymentAttempt;
use App\Models\StudentBill;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Phase 8.4 — BillingPaymentService
 *
 * Centralises all payment state transitions for StudentBill.
 * Controllers delegate to this service — they handle only request
 * binding and HTTP responses.
 *
 * Responsibilities:
 *   - Validate that a bill is in a payable state before writing.
 *   - Transition bill status atomically inside a DB transaction.
 *   - Deduct POS stock when a bundle bill is paid.
 *   - Write a FinancialAuditLogger entry within the same transaction.
 *
 * Design rules:
 *   - Throws \RuntimeException for business-rule violations (already PAID, etc.)
 *     so the caller can catch and convert to the appropriate HTTP response.
 *   - Never returns a raw boolean — either succeeds or throws.
 */
class BillingPaymentService
{
    // ─────────────────────────────────────────────────────────────────────────
    // 1. payCash — manual cash payment at TU counter
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Mark a bill as paid via cash.
     *
     * @throws \RuntimeException if the bill is already PAID.
     */
    public function payCash(StudentBill $bill, ?int $confirmedBy = null): StudentBill
    {
        $this->validatePaymentState($bill);

        DB::transaction(function () use ($bill, $confirmedBy) {
            // Deduct POS stock for bundle items
            $this->deductBundleStock($bill);

            $bill->update([
                'status'         => 'PAID',
                'paid_at'        => now(),
                'payment_method' => 'CASH',
                'confirmed_by'   => $confirmedBy ?? Auth::id(),
            ]);

            FinancialAuditLogger::paymentConfirmed(
                $bill,
                AuditLog::SOURCE_WEB,
                $confirmedBy ?? Auth::id()
            );
        });

        return $bill->refresh();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 2. handleMidtransSuccess — called by Midtrans webhook callback
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Settle a bill after a successful Midtrans payment.
     * Idempotent — silently returns if the bill is already PAID.
     *
     * @param  string $orderId  Midtrans order_id (e.g. "BILL-{id}-{token}")
     * @return StudentBill|null  null if no matching bill found
     */
    public function handleMidtransSuccess(string $orderId): ?StudentBill
    {
        $bill = StudentBill::where('midtrans_order_id', $orderId)->first();

        if (! $bill) {
            Log::warning('BillingPaymentService: no bill found for Midtrans order', [
                'order_id' => $orderId,
            ]);
            return null;
        }

        // Idempotency guard — Midtrans may send duplicate settlement callbacks
        if ($bill->status === 'PAID') {
            Log::info('BillingPaymentService: duplicate Midtrans callback ignored (already PAID)', [
                'bill_id'  => $bill->id,
                'order_id' => $orderId,
            ]);
            return $bill;
        }

        DB::transaction(function () use ($bill, $orderId) {
            $this->deductBundleStock($bill);

            $bill->update([
                'status'         => 'PAID',
                'paid_at'        => now(),
                'payment_method' => 'MIDTRANS',
                'payment_token'  => null, // clear token after settlement
            ]);

            FinancialAuditLogger::paymentConfirmed(
                $bill,
                AuditLog::SOURCE_MIDTRANS,
                null // no user — automated callback
            );
        });

        return $bill->refresh();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // 3. validatePaymentState — shared guard
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Assert that a bill can accept a payment.
     *
     * @throws \RuntimeException if the bill is already PAID or CANCELLED.
     */
    public function validatePaymentState(StudentBill $bill): void
    {
        if ($bill->status === 'PAID') {
            throw new \RuntimeException('Tagihan ini sudah lunas.');
        }

        if ($bill->status === 'CANCELLED') {
            throw new \RuntimeException('Tagihan ini sudah dibatalkan dan tidak dapat dibayar.');
        }
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Private helpers
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Deduct POS stock for each BillItem that links to a PosItem.
     * Only runs for PAKET-type bills with items.
     * Safe to call for SPP/LAINNYA bills — no-op if no items.
     */
    private function deductBundleStock(StudentBill $bill): void
    {
        if ($bill->relationLoaded('items') === false) {
            $bill->load('items.product');
        }

        // Phase 8.4: validate stock before any decrement so the transaction
        // rolls back atomically if any item is out of stock.
        // Resolves the PosItem via direct pos_item_id first, then via pos_bundle_items.
        foreach ($bill->items as $item) {
            $posItem  = $item->product; // direct link
            $required = $item->quantity;

            if (! $posItem && $item->pos_bundle_id) {
                // Resolve via bundle — find the bundled PosItem row
                $bundleItem = \DB::table('pos_bundle_items')
                    ->where('pos_bundle_id', $item->pos_bundle_id)
                    ->first();

                if ($bundleItem) {
                    $posItem  = \App\Models\PosItem::find($bundleItem->pos_item_id);
                    $required = $item->quantity * $bundleItem->quantity;
                }
            }

            if ($posItem && $posItem->stock < $required) {
                throw new \RuntimeException(
                    "Stok {$posItem->name} tidak mencukupi. "
                    . "Tersedia: {$posItem->stock}, dibutuhkan: {$required}."
                );
            }
        }

        // All stock checks passed — now perform the decrements
        foreach ($bill->items as $item) {
            $posItem  = $item->product;
            $required = $item->quantity;

            if (! $posItem && $item->pos_bundle_id) {
                $bundleItem = \DB::table('pos_bundle_items')
                    ->where('pos_bundle_id', $item->pos_bundle_id)
                    ->first();

                if ($bundleItem) {
                    $posItem  = \App\Models\PosItem::find($bundleItem->pos_item_id);
                    $required = $item->quantity * $bundleItem->quantity;
                }
            }

            if ($posItem) {
                $posItem->decrement('stock', $required);
            }
        }
    }
}
