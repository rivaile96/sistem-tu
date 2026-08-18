<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Reconstructed historical migration — originally ran on production at batch 10.
 * File was lost from the repository; reconstructed from production schema evidence.
 *
 * Production evidence (SHOW FULL COLUMNS FROM students):
 *   fcm_token | text | NULL=YES | Key= | DEFAULT=NULL
 *
 * This column is used by the Android parent app for push notifications.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            // Phase 8.1: guard ->after() — status_changed_at is added in a later
            // migration (2026_08_14). On fresh test DBs it may not exist yet.
            if (Schema::hasColumn('students', 'status_changed_at')) {
                $table->text('fcm_token')->nullable()->after('status_changed_at');
            } else {
                $table->text('fcm_token')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn('fcm_token');
        });
    }
};
