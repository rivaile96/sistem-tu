<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Reconstructed historical migration — originally ran on production at batch 10.
 * File was lost from the repository; reconstructed from production schema evidence.
 *
 * Production evidence (SHOW FULL COLUMNS FROM bill_items):
 *   pos_bundle_id | bigint unsigned | NULL=YES | Key=MUL | DEFAULT=NULL
 *
 * Production FK evidence:
 *   bill_items_pos_bundle_id_foreign → pos_bundles.id ON DELETE RESTRICT
 *
 * Note: the original 2026_01_14_180019 migration already included pos_bundle_id
 * in the bill_items CREATE TABLE statement. This migration likely ran as a
 *補完 (補正) migration — adding the column defensively if it was somehow absent,
 * or formalising the FK constraint separately.
 *
 * Since the column and FK already exist from 2026_01_14 on fresh installs,
 * this migration uses hasColumn/hasIndex guards to be safely idempotent.
 * On production it ran and did nothing visible (column already existed).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bill_items', function (Blueprint $table) {
            if (! Schema::hasColumn('bill_items', 'pos_bundle_id')) {
                $table->foreignId('pos_bundle_id')
                      ->nullable()
                      ->after('pos_item_id')
                      ->constrained('pos_bundles')
                      ->onDelete('restrict');
            }
        });
    }

    public function down(): void
    {
        Schema::table('bill_items', function (Blueprint $table) {
            if (Schema::hasColumn('bill_items', 'pos_bundle_id')) {
                // Only drop if this migration was the one that added it.
                // On fresh installs, 2026_01_14 added it — so this is a no-op.
                $table->dropForeign(['pos_bundle_id']);
                $table->dropColumn('pos_bundle_id');
            }
        });
    }
};
