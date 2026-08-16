<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 2.2 — Add confirmed_by to student_bills.
 *
 * Business meaning:
 *   confirmed_by = the authenticated admin/TU user who manually confirmed
 *   a cash or manual payment. NULL for Midtrans settlements and all
 *   historical records.
 *
 * FK convention:
 *   ON DELETE SET NULL — deleting a user must not destroy bill records.
 *   Matches the pattern used by audit_logs.user_id and
 *   students.status_changed_by in this project.
 *
 * Precedent:
 *   spp_bills.confirmed_by → users.id exists in the legacy system.
 *   That FK uses ON DELETE RESTRICT; this migration intentionally uses
 *   SET NULL per the business contract defined in Phase 2.2 spec.
 *
 * Safety:
 *   - Additive only. No data migration, no backfill.
 *   - Nullable column: all existing rows receive NULL automatically.
 *   - No existing column or FK is modified.
 *   - hasColumn guard ensures idempotency on repeated runs.
 *
 * Write behavior (confirmed_by = auth()->id()) is implemented in Phase 2.3
 * when BillController::pay() is updated for the manual payment flow.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_bills', function (Blueprint $table) {
            if (! Schema::hasColumn('student_bills', 'confirmed_by')) {
                $table->foreignId('confirmed_by')
                      ->nullable()
                      ->after('paid_at')
                      ->constrained('users')
                      ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('student_bills', function (Blueprint $table) {
            if (Schema::hasColumn('student_bills', 'confirmed_by')) {
                $table->dropForeign(['confirmed_by']);
                $table->dropColumn('confirmed_by');
            }
        });
    }
};
