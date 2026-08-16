<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 3.2 — Add kepala_sekolah to users.role enum (MariaDB/MySQL production).
 *
 * The source migration (2026_01_06) already includes kepala_sekolah in its
 * enum definition, so fresh test databases built via migrate:fresh are correct
 * automatically. This migration handles the existing production database where
 * the column was created with the old enum before Phase 3.2.
 *
 * SQLite (test env): no-op — already handled by the updated source migration.
 * MariaDB/MySQL (production): ALTER TABLE MODIFY COLUMN.
 *
 * Rollback safety: verifies no kepala_sekolah users exist before reverting.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            // Test DB rebuilt from source migration — nothing to do here.
            return;
        }

        DB::statement("
            ALTER TABLE users
            MODIFY COLUMN role
            ENUM('admin','tu','student','kepala_sekolah')
            NOT NULL DEFAULT 'student'
        ");
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        $count = DB::table('users')->where('role', 'kepala_sekolah')->count();
        if ($count > 0) {
            throw new \RuntimeException(
                "Cannot rollback: {$count} user(s) with role='kepala_sekolah' exist. " .
                "Reassign their roles before rolling back this migration."
            );
        }

        DB::statement("
            ALTER TABLE users
            MODIFY COLUMN role
            ENUM('admin','tu','student')
            NOT NULL DEFAULT 'student'
        ");
    }
};
