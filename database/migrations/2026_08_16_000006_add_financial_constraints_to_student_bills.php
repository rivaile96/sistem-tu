<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Phase 3.3 — Financial constraints on student_bills.
 *
 * Two changes:
 *
 * 1. CHECK constraint: amount > 0
 *    Production audit: min(amount) = 150000, no zero/negative rows. Safe.
 *
 * 2. Unique index for SPP duplicate prevention.
 *    Production data reality (audited 2026-08-16):
 *      - 31 SPP bills have bill_month = NULL (pre-spp_fields migration)
 *      - 15 "duplicate" groups are all (student_id, NULL, NULL) — not real
 *      - A plain UNIQUE on (student_id, bill_month, bill_year) would fail
 *    Solution: generated (virtual) column scoped to SPP rows that have
 *    month+year set. NULL values in a UNIQUE index are ignored by MariaDB,
 *    so NULL-month SPP rows and non-SPP rows are excluded automatically.
 *
 * SQLite (test env): DDL skipped — enforced at application layer.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        // 1. CHECK constraint: amount > 0
        DB::statement(
            'ALTER TABLE student_bills
             ADD CONSTRAINT chk_amount_positive CHECK (amount > 0)'
        );

        // 2. Virtual generated column for SPP deduplication
        DB::statement("
            ALTER TABLE student_bills
            ADD COLUMN spp_dedup_key VARCHAR(50)
                GENERATED ALWAYS AS (
                    IF(
                        type = 'SPP'
                        AND bill_month IS NOT NULL
                        AND bill_year  IS NOT NULL,
                        CONCAT(student_id, '-', bill_month, '-', bill_year),
                        NULL
                    )
                ) VIRTUAL
        ");

        // 3. Unique index on generated column (NULLs ignored — safe)
        DB::statement(
            'ALTER TABLE student_bills
             ADD UNIQUE INDEX student_bills_spp_dedup_unique (spp_dedup_key)'
        );
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        DB::statement('ALTER TABLE student_bills DROP INDEX student_bills_spp_dedup_unique');
        DB::statement('ALTER TABLE student_bills DROP COLUMN spp_dedup_key');
        DB::statement('ALTER TABLE student_bills DROP CONSTRAINT chk_amount_positive');
    }
};
