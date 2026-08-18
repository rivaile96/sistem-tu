<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 4 — Add discount fields to student_bills.
 *
 * New columns:
 *   original_amount  — price before discount (nullable for existing rows)
 *   discount_amount  — deduction applied     (default 0)
 *   discount_note    — free-text reason       (nullable)
 *
 * Rule enforced at application layer:
 *   amount = original_amount - discount_amount
 *
 * Existing 61 bills are NOT touched.
 * Migration is fully backward-compatible — all new columns are nullable
 * or have defaults, so existing INSERT/UPDATE statements without these
 * fields continue to work without modification.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_bills', function (Blueprint $table) {
            // Add after 'amount' column for logical grouping
            $table->decimal('original_amount', 12, 2)->nullable()->after('amount');
            $table->decimal('discount_amount', 12, 2)->default(0)->after('original_amount');
            $table->string('discount_note', 255)->nullable()->after('discount_amount');
        });
    }

    public function down(): void
    {
        Schema::table('student_bills', function (Blueprint $table) {
            $table->dropColumn(['original_amount', 'discount_amount', 'discount_note']);
        });
    }
};
