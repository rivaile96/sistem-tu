<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 6B-2 — Add spp_legacy_id to student_bills.
 *
 * Nullable reference back to the original spp_bills.id for rows that were
 * migrated from the legacy SPP system. NULL for all native student_bills rows.
 * This column is audit-only — no FK constraint (spp_bills may be dropped later).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_bills', function (Blueprint $table) {
            $table->unsignedBigInteger('spp_legacy_id')
                  ->nullable()
                  ->after('discount_note')
                  ->comment('Original spp_bills.id — set only for rows migrated from legacy SPP system');
        });
    }

    public function down(): void
    {
        Schema::table('student_bills', function (Blueprint $table) {
            $table->dropColumn('spp_legacy_id');
        });
    }
};
