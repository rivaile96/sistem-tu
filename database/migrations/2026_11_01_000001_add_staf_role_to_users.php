<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Phase 11 — Add 'staf' to users.role enum.
 *
 * Before: enum('admin','tu','student','kepala_sekolah')
 * After:  enum('admin','tu','staf','student','kepala_sekolah')
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin','tu','staf','student','kepala_sekolah') NOT NULL DEFAULT 'student'");
    }

    public function down(): void
    {
        // Remove staf — any staf users will lose their role (fallback to student)
        DB::statement("UPDATE users SET role = 'student' WHERE role = 'staf'");
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin','tu','student','kepala_sekolah') NOT NULL DEFAULT 'student'");
    }
};
