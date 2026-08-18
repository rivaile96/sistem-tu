<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 9.3 — Drop students.class_name column.
 *
 * Safety preconditions (verified before applying):
 *   1. Zero active students with kelas_id IS NULL.
 *   2. IntegrationController no longer writes arbitrary class_name (Phase 9.1).
 *   3. All remaining class_name writes are derived from kelas.nama_kelas.
 *   4. All fallback reads removed from controllers and views.
 *   5. Full test suite passes.
 *
 * DO NOT apply this migration until all safety conditions are confirmed.
 * Run the pre-flight check first:
 *   SELECT COUNT(*) FROM students WHERE status='active' AND kelas_id IS NULL;
 *   -- Expected: 0
 *
 * Rollback: restores column and backfills from kelas.nama_kelas via JOIN.
 */
return new class extends Migration
{
    public function up(): void
    {
        // ── Pre-flight safety check ───────────────────────────────────────────
        $activeWithoutKelas = DB::table('students')
            ->where('status', 'active')
            ->whereNull('kelas_id')
            ->count();

        if ($activeWithoutKelas > 0) {
            throw new \RuntimeException(
                "SAFETY CHECK FAILED: {$activeWithoutKelas} active student(s) have kelas_id=NULL. "
                . "Assign all active students to a kelas before dropping class_name."
            );
        }

        // ── Drop column ───────────────────────────────────────────────────────
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn('class_name');
        });
    }

    public function down(): void
    {
        // Restore column
        Schema::table('students', function (Blueprint $table) {
            $table->string('class_name')->nullable()->after('name');
        });

        // Backfill from kelas relation
        DB::statement("
            UPDATE students s
            INNER JOIN kelas k ON k.id = s.kelas_id
            SET s.class_name = k.nama_kelas
            WHERE s.kelas_id IS NOT NULL
              AND s.deleted_at IS NULL
        ");
    }
};
