<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Supersedes 2026_01_15_132336_create_school_settings_table (batch 10).
 * That earlier migration created the table structure; this one (batch 11)
 * seeds the default data and re-asserts the structure idempotently.
 *
 * Uses hasTable guard so a fresh install (where 132336 already ran first)
 * does not crash with "table already exists".
 * Uses insertOrIgnore so re-running does not duplicate seed rows.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Table was created by 2026_01_15_132336. Guard against fresh-install
        // race where both migrations run in sequence.
        if (! Schema::hasTable('school_settings')) {
            Schema::create('school_settings', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique();
                $table->text('value')->nullable();
                $table->timestamps();
            });
        }

        // Seed default values. insertOrIgnore prevents duplicate-key errors
        // if this migration is re-run or if data was already inserted.
        DB::table('school_settings')->insertOrIgnore([
            ['key' => 'school_name',    'value' => 'SMK Digischool Indonesia',    'created_at' => now(), 'updated_at' => now()],
            ['key' => 'school_address', 'value' => 'Jl. Teknologi No. 1, Jakarta','created_at' => now(), 'updated_at' => now()],
            ['key' => 'school_phone',   'value' => '021-555-0199',                'created_at' => now(), 'updated_at' => now()],
            ['key' => 'head_of_admin',  'value' => 'Admin TU',                    'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('school_settings');
    }
};
