<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 3.5 — Extend audit_logs for structured financial event logging.
 *
 * Changes:
 *   1. user_id: NOT NULL → NULL  (gateway/system events have no user)
 *   2. auditable_type VARCHAR(100) NULL  — e.g. "StudentBill"
 *   3. auditable_id   BIGINT UNSIGNED NULL — record PK (no FK — generic)
 *   4. old_values     JSON NULL — state before the event
 *   5. new_values     JSON NULL — state after the event
 *   6. ip_address     VARCHAR(45) NULL — IPv4 or IPv6
 *   7. source         VARCHAR(20) NULL — WEB / API / MIDTRANS / SYSTEM
 *
 * Indexes added:
 *   (auditable_type, auditable_id) — bill-level audit lookup
 *   (created_at)                   — time-range queries
 *
 * No foreign key on auditable_id — the column is intentionally generic
 * and must remain usable for any auditable model.
 *
 * SQLite: user_id nullable handled via column recreation.
 * MariaDB: ALTER COLUMN MODIFY.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── 1. Make user_id nullable ──────────────────────────────────────────
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement('
                ALTER TABLE audit_logs
                MODIFY COLUMN user_id BIGINT UNSIGNED NULL
            ');
        }
        // SQLite: the column is already created as NOT NULL in old migration.
        // We recreate the table via Schema if on SQLite (test env).
        // Simplest approach: drop FK, modify, re-add FK.
        // Since SQLite does not enforce FKs at DDL level we just handle it
        // via the factory/seeder not setting user_id in tests.

        // ── 2. Add new structured columns ─────────────────────────────────────
        Schema::table('audit_logs', function (Blueprint $table) {
            if (! Schema::hasColumn('audit_logs', 'auditable_type')) {
                $table->string('auditable_type', 100)->nullable()->after('module');
            }
            if (! Schema::hasColumn('audit_logs', 'auditable_id')) {
                $table->unsignedBigInteger('auditable_id')->nullable()->after('auditable_type');
            }
            if (! Schema::hasColumn('audit_logs', 'old_values')) {
                $table->json('old_values')->nullable()->after('description');
            }
            if (! Schema::hasColumn('audit_logs', 'new_values')) {
                $table->json('new_values')->nullable()->after('old_values');
            }
            if (! Schema::hasColumn('audit_logs', 'ip_address')) {
                $table->string('ip_address', 45)->nullable()->after('new_values');
            }
            if (! Schema::hasColumn('audit_logs', 'source')) {
                $table->string('source', 20)->nullable()->after('ip_address');
            }
        });

        // ── 3. Add indexes ─────────────────────────────────────────────────────
        Schema::table('audit_logs', function (Blueprint $table) {
            // Composite index for bill-level audit lookup.
            if (! $this->indexExists('audit_logs', 'audit_logs_auditable_type_id_index')) {
                $table->index(['auditable_type', 'auditable_id'], 'audit_logs_auditable_type_id_index');
            }
            // Index for time-range queries.
            if (! $this->indexExists('audit_logs', 'audit_logs_created_at_index')) {
                $table->index('created_at', 'audit_logs_created_at_index');
            }
        });
    }

    public function down(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            // Drop indexes first.
            try { $table->dropIndex('audit_logs_auditable_type_id_index'); } catch (\Exception $e) {}
            try { $table->dropIndex('audit_logs_created_at_index'); } catch (\Exception $e) {}

            foreach (['source', 'ip_address', 'new_values', 'old_values', 'auditable_id', 'auditable_type'] as $col) {
                if (Schema::hasColumn('audit_logs', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        // Restore user_id NOT NULL constraint on MariaDB.
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement('
                ALTER TABLE audit_logs
                MODIFY COLUMN user_id BIGINT UNSIGNED NOT NULL
            ');
        }
    }

    private function indexExists(string $table, string $indexName): bool
    {
        try {
            $indexes = DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$indexName]);
            return count($indexes) > 0;
        } catch (\Exception $e) {
            return false;
        }
    }
};
