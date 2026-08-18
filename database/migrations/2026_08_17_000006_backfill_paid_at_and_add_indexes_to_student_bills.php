<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 7.1 + 7.2 — student_bills hardening.
 *
 * 1. Backfill paid_at for historical PAID bills that have no paid_at.
 *    Use updated_at as the best available proxy for the payment timestamp.
 *
 * 2. Add missing compound indexes:
 *    - (student_id, type)        — for type-scoped student queries
 *    - (bill_month, bill_year)   — for period-based reporting
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── 1. Backfill paid_at ───────────────────────────────────────────
        DB::statement("
            UPDATE student_bills
            SET paid_at = updated_at
            WHERE status = 'PAID'
              AND paid_at IS NULL
              AND updated_at IS NOT NULL
        ");

        // ── 2. Add missing indexes (skip if already exist) ────────────────
        Schema::table('student_bills', function (Blueprint $table) {
            // Check and add (student_id, type) index
            $indexes = DB::select("SHOW INDEX FROM student_bills WHERE Key_name = 'student_bills_student_id_type_index'");
            if (empty($indexes)) {
                $table->index(['student_id', 'type'], 'student_bills_student_id_type_index');
            }

            // Check and add (bill_month, bill_year) index
            $indexes2 = DB::select("SHOW INDEX FROM student_bills WHERE Key_name = 'student_bills_bill_month_bill_year_index'");
            if (empty($indexes2)) {
                $table->index(['bill_month', 'bill_year'], 'student_bills_bill_month_bill_year_index');
            }
        });
    }

    public function down(): void
    {
        Schema::table('student_bills', function (Blueprint $table) {
            // Remove indexes added in up()
            $indexes = DB::select("SHOW INDEX FROM student_bills WHERE Key_name = 'student_bills_student_id_type_index'");
            if (!empty($indexes)) {
                $table->dropIndex('student_bills_student_id_type_index');
            }

            $indexes2 = DB::select("SHOW INDEX FROM student_bills WHERE Key_name = 'student_bills_bill_month_bill_year_index'");
            if (!empty($indexes2)) {
                $table->dropIndex('student_bills_bill_month_bill_year_index');
            }

            // Note: paid_at backfill is not reversible without a backup.
            // The down() intentionally does not null-out paid_at values.
        });
    }
};
