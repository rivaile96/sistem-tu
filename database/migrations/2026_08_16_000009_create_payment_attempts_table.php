<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 3.7A — Create payment_attempts table.
 *
 * Records every individual Midtrans payment session for a StudentBill.
 * One bill may have many attempts (expire/cancel → retry → settle).
 *
 * StudentBill remains the canonical financial state.
 * PaymentAttempt is the per-session ledger.
 *
 * FK: student_bill_id → student_bills.id ON DELETE CASCADE
 *   Rationale: attempts have no meaning without their bill.
 *
 * Historical bills will NOT have attempt records — no fabrication.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_attempts', function (Blueprint $table) {
            $table->id();

            $table->foreignId('student_bill_id')
                  ->constrained('student_bills')
                  ->cascadeOnDelete();

            // Midtrans order_id: "BILL-{bill_id}-{rand6}-{timestamp}"
            // Unique per attempt — this is the primary correlation key
            // between the application and Midtrans.
            $table->string('order_id', 100)->unique();

            // Snap token returned by Midtrans::getSnapToken().
            // Used by the frontend to open the Snap popup.
            // NULL after the attempt resolves (settled/expired/cancelled).
            $table->string('snap_token', 255)->nullable();

            // Midtrans internal transaction_id from webhook payload.
            // Different from order_id — Midtrans's own reference number.
            $table->string('transaction_id', 255)->nullable();

            // Lifecycle status — values mirror Midtrans transaction_status.
            $table->string('status', 20)->default('pending');
            // Valid values: pending | settlement | capture | expire | cancel | deny

            // Payment channel details — populated from webhook payload.
            $table->string('payment_method', 50)->nullable(); // e.g. bank_transfer, gopay, qris
            $table->string('bank', 50)->nullable();           // e.g. mandiri, bca, bni
            $table->string('va_number', 50)->nullable();      // virtual account number if applicable

            // Amount from Snap params at attempt creation time.
            // Must match the bill amount — recorded here for audit integrity.
            $table->decimal('gross_amount', 12, 2);

            // Lifecycle timestamps.
            $table->timestamp('initiated_at');          // when createToken/createPayment called
            $table->timestamp('settled_at')->nullable(); // when settlement webhook arrived
            $table->timestamp('expired_at')->nullable(); // when expire/cancel webhook arrived

            // Origin of the attempt.
            $table->string('source', 20)->nullable(); // WEB | API | SYSTEM

            $table->timestamps();
        });

        // ── Indexes ───────────────────────────────────────────────────────────
        // UNIQUE(order_id) already created above via ->unique().

        // For "show all attempts for bill #X filtered by status"
        Schema::table('payment_attempts', function (Blueprint $table) {
            $table->index(['student_bill_id', 'status'],       'pa_bill_status_index');
            $table->index(['student_bill_id', 'initiated_at'], 'pa_bill_initiated_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_attempts');
    }
};
