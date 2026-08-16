<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Reconstructed historical migration — originally ran on production at batch 10.
 * File was lost from the repository; reconstructed from production schema evidence.
 *
 * This is the EARLIER version of school_settings creation (batch 10).
 * It was superseded by 2026_01_15_224353_create_school_settings_table (batch 11)
 * which also creates the same table with the same structure.
 *
 * Production evidence (SHOW FULL COLUMNS FROM school_settings):
 *   id         | bigint unsigned | NOT NULL | PRI | auto_increment
 *   key        | varchar(255)    | NOT NULL | UNI |
 *   value      | text            | NULL     |     |
 *   created_at | timestamp       | NULL     |     |
 *   updated_at | timestamp       | NULL     |     |
 *
 * Since the later migration (224353) also creates the table with identical
 * structure and uses Schema::create() without a hasTable guard, this earlier
 * migration must NOT attempt to create the table if it already exists —
 * otherwise migrate:fresh would fail on the second Schema::create() call.
 *
 * On fresh install: this runs first (batch 10), creates the table.
 * Then 224353 (batch 11) would fail with "table already exists".
 *
 * Resolution: this migration creates the table. The 224353 migration file
 * already exists in the repo and will be updated to use hasTable guard
 * to avoid duplicate creation. See 2026_01_15_224353 for the seeded version.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('school_settings')) {
            Schema::create('school_settings', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique();
                $table->text('value')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        // Intentionally left to the later 224353 migration's down() method.
        // Dropping here would break the superseding migration's down() rollback.
    }
};
