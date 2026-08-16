<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 3.5 — Add created_by to student_bills.
 *
 * Records which authenticated staff user created each bill.
 * NULL for system-generated bills and all historical bills
 * that predate this column (no fabrication of historical data).
 *
 * ON DELETE SET NULL: if the user account is deleted, the audit
 * reference is cleared rather than blocking the user deletion.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_bills', function (Blueprint $table) {
            if (! Schema::hasColumn('student_bills', 'created_by')) {
                $table->foreignId('created_by')
                      ->nullable()
                      ->after('confirmed_by')
                      ->constrained('users')
                      ->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('student_bills', function (Blueprint $table) {
            if (Schema::hasColumn('student_bills', 'created_by')) {
                $table->dropForeign(['created_by']);
                $table->dropColumn('created_by');
            }
        });
    }
};
