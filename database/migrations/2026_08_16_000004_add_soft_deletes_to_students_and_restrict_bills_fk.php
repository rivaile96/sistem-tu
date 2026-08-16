<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 2.5 — Student financial data protection.
 *
 * Two changes:
 *
 * 1. Add students.deleted_at (SoftDeletes)
 *    Enables Eloquent soft deletion — Student::delete() sets deleted_at
 *    instead of issuing DELETE. Hard-deleted students are never reachable
 *    through normal Eloquent queries.
 *
 * 2. Change student_bills.student_id FK: CASCADE → RESTRICT
 *    Previously: deleting a student would cascade-delete all their bills.
 *    After:      the DB engine rejects any DELETE on students that still
 *                has related student_bills rows. This is a final safety net
 *                independent of application-level guards.
 *
 * Production safety:
 *    - Adding deleted_at nullable is a non-destructive online DDL.
 *    - Existing student rows get deleted_at = NULL (not soft-deleted).
 *    - Changing FK rule requires DROP + ADD CONSTRAINT — brief metadata
 *      lock on InnoDB, table remains readable during operation.
 *    - No data is modified.
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1. Add soft-delete column to students.
        Schema::table('students', function (Blueprint $table) {
            if (! Schema::hasColumn('students', 'deleted_at')) {
                $table->softDeletes()->after('updated_at');
            }
        });

        // 2. Change student_bills.student_id FK: CASCADE → RESTRICT.
        // Must drop the existing FK before recreating with new rule.
        Schema::table('student_bills', function (Blueprint $table) {
            // Drop existing CASCADE FK.
            $table->dropForeign(['student_id']);

            // Recreate with RESTRICT — prevents accidental student deletion
            // from cascading into financial history.
            $table->foreign('student_id')
                  ->references('id')
                  ->on('students')
                  ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        // Reverse FK back to CASCADE.
        Schema::table('student_bills', function (Blueprint $table) {
            $table->dropForeign(['student_id']);
            $table->foreign('student_id')
                  ->references('id')
                  ->on('students')
                  ->cascadeOnDelete();
        });

        // Remove soft-delete column.
        Schema::table('students', function (Blueprint $table) {
            if (Schema::hasColumn('students', 'deleted_at')) {
                $table->dropSoftDeletes();
            }
        });
    }
};
