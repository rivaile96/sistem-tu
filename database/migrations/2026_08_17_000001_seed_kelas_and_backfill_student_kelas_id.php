<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 1 — Master Data Foundation
 *
 * 1. Seed kelas records from existing students.class_name values.
 *    Uses INSERT IGNORE so the migration is re-runnable without errors
 *    if a kelas with that nama_kelas already exists (unique constraint).
 *
 * 2. Backfill students.kelas_id by joining students ↔ kelas on nama_kelas.
 *    Only updates rows where kelas_id IS NULL to avoid overwriting any
 *    kelas_id that may already be set by a future partial run.
 *
 * 3. Fix student_status_logs.student_id FK: CASCADE → RESTRICT.
 *    Prevents silent loss of audit history if a student row is hard-deleted.
 *    (Students use SoftDeletes so hard-delete should never happen in normal
 *    operation, but the FK rule is a safety net.)
 *
 * SAFETY:
 *   - No existing rows are deleted.
 *   - No financial data (student_bills, bill_items) is touched.
 *   - student.class_name is NOT removed — kept for backward compatibility
 *     until Phase 3 locks it to derived-only.
 *   - Down() reverses all three operations cleanly.
 */
return new class extends Migration
{
    // ------------------------------------------------------------------
    // Kelas seed data derived from distinct students.class_name values.
    //
    // tingkat and jurusan are inferred from the class name:
    //   "X - Umum"  → tingkat 10, jurusan Umum  (SMA/SMK/MA style)
    //   "XII-RPL"   → tingkat 12, jurusan RPL
    //
    // Add more rows here if new class_name values appear in the future.
    // ------------------------------------------------------------------
    private array $kelasSeed = [
        [
            'nama_kelas' => 'X - Umum',
            'tingkat'    => 10,
            'jurusan'    => 'Umum',
            'wali_kelas' => null,
            'is_aktif'   => true,
        ],
        [
            'nama_kelas' => 'XII-RPL',
            'tingkat'    => 12,
            'jurusan'    => 'RPL',
            'wali_kelas' => null,
            'is_aktif'   => true,
        ],
    ];

    public function up(): void
    {
        // ──────────────────────────────────────────────────────────────
        // STEP 1 — Insert kelas records
        // ──────────────────────────────────────────────────────────────
        // We check for existence before inserting to make this migration
        // idempotent (safe to run even if some kelas already exist).
        foreach ($this->kelasSeed as $kelas) {
            $exists = DB::table('kelas')
                ->where('nama_kelas', $kelas['nama_kelas'])
                ->exists();

            if (! $exists) {
                DB::table('kelas')->insert(array_merge($kelas, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
        }

        // ──────────────────────────────────────────────────────────────
        // STEP 2 — Backfill students.kelas_id
        //
        // Matches students to kelas by class_name = nama_kelas.
        // Only updates students where kelas_id IS NULL so this is safe
        // to run multiple times and won't overwrite manual assignments.
        // ──────────────────────────────────────────────────────────────
        DB::statement('
            UPDATE students s
            INNER JOIN kelas k ON k.nama_kelas = s.class_name
            SET s.kelas_id = k.id,
                s.updated_at = NOW()
            WHERE s.kelas_id IS NULL
              AND s.deleted_at IS NULL
        ');

        // ──────────────────────────────────────────────────────────────
        // STEP 3 — Fix student_status_logs FK rule: CASCADE → RESTRICT
        //
        // SQLite does not support ALTER TABLE … DROP FOREIGN KEY.
        // We skip this step on SQLite (used in tests) and only apply it
        // on MySQL/MariaDB (production).
        // ──────────────────────────────────────────────────────────────
        if (DB::getDriverName() !== 'sqlite') {
            // Drop the existing CASCADE constraint
            Schema::table('student_status_logs', function (Blueprint $table) {
                $table->dropForeign(['student_id']);
            });

            // Re-add with RESTRICT so accidental hard-deletes are blocked
            Schema::table('student_status_logs', function (Blueprint $table) {
                $table->foreign('student_id')
                      ->references('id')
                      ->on('students')
                      ->restrictOnDelete();
            });
        }
    }

    public function down(): void
    {
        // ──────────────────────────────────────────────────────────────
        // Reverse Step 3 — restore CASCADE on student_status_logs
        // ──────────────────────────────────────────────────────────────
        if (DB::getDriverName() !== 'sqlite') {
            Schema::table('student_status_logs', function (Blueprint $table) {
                $table->dropForeign(['student_id']);
            });

            Schema::table('student_status_logs', function (Blueprint $table) {
                $table->foreign('student_id')
                      ->references('id')
                      ->on('students')
                      ->cascadeOnDelete();
            });
        }

        // ──────────────────────────────────────────────────────────────
        // Reverse Step 2 — clear backfilled kelas_id values
        // Only nulls out kelas_id for kelas created by this migration.
        // ──────────────────────────────────────────────────────────────
        $names = array_column($this->kelasSeed, 'nama_kelas');
        DB::statement('
            UPDATE students s
            INNER JOIN kelas k ON k.id = s.kelas_id
            SET s.kelas_id = NULL,
                s.updated_at = NOW()
            WHERE k.nama_kelas IN (' . implode(',', array_fill(0, count($names), '?')) . ')
        ', $names);

        // ──────────────────────────────────────────────────────────────
        // Reverse Step 1 — remove only the kelas rows we seeded,
        // but only if they have no students pointing to them.
        // (Safety check: don't delete kelas that have acquired students
        // from other sources since this migration ran.)
        // ──────────────────────────────────────────────────────────────
        foreach ($this->kelasSeed as $kelas) {
            $kelasRow = DB::table('kelas')->where('nama_kelas', $kelas['nama_kelas'])->first();
            if (! $kelasRow) {
                continue;
            }

            $studentCount = DB::table('students')
                ->where('kelas_id', $kelasRow->id)
                ->whereNull('deleted_at')
                ->count();

            if ($studentCount === 0) {
                DB::table('kelas')->where('id', $kelasRow->id)->delete();
            }
        }
    }
};
