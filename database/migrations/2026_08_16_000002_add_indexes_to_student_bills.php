<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 2.1 — Add performance indexes to student_bills.
 *
 * Indexes added:
 *
 * 1. (student_id, status) — composite
 *    Covers: Student portal dashboard (all bills per student filtered by status),
 *            admin whereHas('bills', fn => where status UNPAID) for unpaid student count,
 *            BillController existence checks (where student_id AND name).
 *    Note:   student_id already has a single-column index from the FK constraint.
 *            The composite (student_id, status) supersedes it for filtered queries
 *            and does not conflict — both can coexist.
 *
 * 2. (paid_at) — single column
 *    Covers: Dashboard reporting queries filtering/sorting by payment date.
 *            DashboardController currently uses updated_at; after Phase 2.3
 *            those queries will switch to paid_at. The index is added now so
 *            Phase 2.3 does not require a separate schema migration.
 *
 * 3. (status, paid_at) — composite
 *    Covers: WHERE status = 'PAID' AND DATE(paid_at) = ?  (today's income)
 *            WHERE status = 'PAID' AND MONTH(paid_at) = ? (monthly income)
 *            WHERE status = 'PAID' ORDER BY paid_at DESC  (recent activity feed)
 *            All dashboard financial aggregation queries match this pattern.
 *
 * Safety:
 *   - Additive only. No column changes, no data changes.
 *   - All three indexes are checked for existence before creation (idempotent).
 *   - No existing indexes are dropped or modified.
 *   - InnoDB CREATE INDEX acquires only a metadata lock on modern MariaDB/MySQL;
 *     the table remains readable and writable during index build.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_bills', function (Blueprint $table) {
            // 1. Composite: student_id + status
            // Covers per-student filtered queries (UNPAID list, PAID list, existence checks).
            if (! $this->indexExists('student_bills', 'student_bills_student_id_status_index')) {
                $table->index(['student_id', 'status'], 'student_bills_student_id_status_index');
            }

            // 2. Single: paid_at
            // Covers date-range queries on payment timestamp after Phase 2.3 migration.
            if (! $this->indexExists('student_bills', 'student_bills_paid_at_index')) {
                $table->index('paid_at', 'student_bills_paid_at_index');
            }

            // 3. Composite: status + paid_at
            // Covers dashboard financial aggregation: WHERE status='PAID' AND paid_at filters.
            if (! $this->indexExists('student_bills', 'student_bills_status_paid_at_index')) {
                $table->index(['status', 'paid_at'], 'student_bills_status_paid_at_index');
            }
        });
    }

    public function down(): void
    {
        Schema::table('student_bills', function (Blueprint $table) {
            if ($this->indexExists('student_bills', 'student_bills_student_id_status_index')) {
                $table->dropIndex('student_bills_student_id_status_index');
            }

            if ($this->indexExists('student_bills', 'student_bills_paid_at_index')) {
                $table->dropIndex('student_bills_paid_at_index');
            }

            if ($this->indexExists('student_bills', 'student_bills_status_paid_at_index')) {
                $table->dropIndex('student_bills_status_paid_at_index');
            }
        });
    }

    /**
     * Check whether a named index exists on the given table.
     * Uses information_schema for cross-driver compatibility (MariaDB + SQLite).
     */
    private function indexExists(string $table, string $indexName): bool
    {
        // Schema::hasIndex() is only available in Laravel 11.34+.
        // Use getIndexes() which is available from Laravel 10+.
        $indexes = Schema::getIndexes($table);
        foreach ($indexes as $index) {
            if ($index['name'] === $indexName) {
                return true;
            }
        }
        return false;
    }
};
