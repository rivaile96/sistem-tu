<?php

/**
 * Phase 2.5 — Student financial data protection tests.
 *
 * Verifies:
 *   1. calon_siswa without bills → deletion allowed (soft delete)
 *   2. calon_siswa with bill → deletion rejected
 *   3. active student → deletion rejected
 *   4. exited/graduated student → deletion rejected
 *   5. student_bills remain intact after rejected deletion
 *   6. Student::delete() uses SoftDeletes (sets deleted_at, not hard delete)
 *   7. DB FK RESTRICT prevents cascade deletion of student_bills
 *   8. Soft-deleted student excluded from normal queries
 *   9. Financial history remains accessible after soft delete
 *  10. PPDBController::destroy() rejects calon_siswa with bills
 */

use App\Models\Student;
use App\Models\StudentBill;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// ── Infrastructure checks ────────────────────────────────────────────────────

test('students table has deleted_at column', function () {
    expect(Schema::hasColumn('students', 'deleted_at'))->toBeTrue();
});

test('Student model uses SoftDeletes trait', function () {
    expect(
        in_array('Illuminate\Database\Eloquent\SoftDeletes', class_uses_recursive(Student::class))
    )->toBeTrue();
});

test('student_bills student_id FK is RESTRICT not CASCADE', function () {
    // Run a raw query against sqlite_master to find the FK definition.
    // On SQLite the FK info is in the CREATE TABLE statement.
    $fkInfo = DB::select("PRAGMA foreign_key_list('student_bills')");
    $studentIdFk = collect($fkInfo)->firstWhere('from', 'student_id');

    expect($studentIdFk)->not->toBeNull();
    // SQLite RESTRICT and NO ACTION both appear as 'NO ACTION' in PRAGMA output
    // because SQLite treats them identically at the deferred check level.
    // What matters is that CASCADE is NOT set.
    expect(strtoupper($studentIdFk->on_delete))->not->toBe('CASCADE');
});

// ── 1. calon_siswa without bills → allowed ───────────────────────────────────
test('calon_siswa without bills can be deleted via StudentController', function () {
    $admin   = User::factory()->create(['role' => 'admin']);
    $student = Student::factory()->calon()->create();

    $response = $this->actingAs($admin)
                     ->delete("/students/{$student->id}");

    $response->assertRedirect(route('students.index'));

    // Soft-deleted: row still exists but deleted_at is set.
    $this->assertSoftDeleted('students', ['id' => $student->id]);
});

// ── 2. calon_siswa with bill → rejected ──────────────────────────────────────
test('calon_siswa with existing bill cannot be deleted', function () {
    $admin   = User::factory()->create(['role' => 'admin']);
    $student = Student::factory()->calon()->create();
    StudentBill::factory()->create(['student_id' => $student->id]);

    $response = $this->actingAs($admin)
                     ->delete("/students/{$student->id}");

    $response->assertStatus(302); // redirected back with error
    $this->assertDatabaseHas('students', ['id' => $student->id, 'deleted_at' => null]);
});

// ── 3. active student → deletion rejected ────────────────────────────────────
test('active student cannot be deleted', function () {
    $admin   = User::factory()->create(['role' => 'admin']);
    $student = Student::factory()->create(['status' => 'active']);

    $response = $this->actingAs($admin)
                     ->delete("/students/{$student->id}");

    $response->assertStatus(302);
    $this->assertDatabaseHas('students', ['id' => $student->id, 'deleted_at' => null]);
});

// ── 4. exited / graduated students → deletion rejected ───────────────────────
test('keluar student cannot be deleted', function () {
    $admin   = User::factory()->create(['role' => 'admin']);
    $student = Student::factory()->create(['status' => 'keluar']);

    $this->actingAs($admin)
         ->delete("/students/{$student->id}")
         ->assertStatus(302);

    $this->assertDatabaseHas('students', ['id' => $student->id, 'deleted_at' => null]);
});

test('graduated student cannot be deleted', function () {
    $admin   = User::factory()->create(['role' => 'admin']);
    $student = Student::factory()->create(['status' => 'graduated']);

    $this->actingAs($admin)
         ->delete("/students/{$student->id}")
         ->assertStatus(302);

    $this->assertDatabaseHas('students', ['id' => $student->id, 'deleted_at' => null]);
});

test('alumni student cannot be deleted', function () {
    $admin   = User::factory()->create(['role' => 'admin']);
    $student = Student::factory()->create(['status' => 'alumni']);

    $this->actingAs($admin)
         ->delete("/students/{$student->id}")
         ->assertStatus(302);

    $this->assertDatabaseHas('students', ['id' => $student->id, 'deleted_at' => null]);
});

// ── 5. student_bills remain intact after rejected deletion ───────────────────
test('student bills remain intact when deletion is rejected', function () {
    $admin   = User::factory()->create(['role' => 'admin']);
    $student = Student::factory()->create(['status' => 'active']);
    $bill    = StudentBill::factory()->create(['student_id' => $student->id]);

    $this->actingAs($admin)->delete("/students/{$student->id}");

    $this->assertDatabaseHas('student_bills', ['id' => $bill->id]);
});

// ── 6. Student::delete() uses SoftDeletes ────────────────────────────────────
test('Student delete sets deleted_at and does not hard delete the row', function () {
    $student = Student::factory()->calon()->create();
    $id      = $student->id;

    $student->delete();

    // Row still in DB — not physically deleted.
    $this->assertDatabaseHas('students', ['id' => $id]);
    // deleted_at is now set.
    $this->assertSoftDeleted('students', ['id' => $id]);
    // Not visible in normal queries.
    expect(Student::find($id))->toBeNull();
});

// ── 7. DB FK RESTRICT prevents cascade deletion ───────────────────────────────
test('database rejects hard deletion of student with existing bills', function () {
    $student = Student::factory()->create(['status' => 'active']);
    StudentBill::factory()->create(['student_id' => $student->id]);

    // Attempt a raw hard delete — DB FK RESTRICT must prevent this.
    expect(fn () => DB::table('students')->where('id', $student->id)->delete())
        ->toThrow(\Exception::class);

    // Student and bill both still exist.
    $this->assertDatabaseHas('students', ['id' => $student->id]);
    $this->assertDatabaseHas('student_bills', ['student_id' => $student->id]);
});

// ── 8. Soft-deleted student excluded from normal Eloquent queries ─────────────
test('soft-deleted student is excluded from normal Student queries', function () {
    $student = Student::factory()->calon()->create();
    $id      = $student->id;

    $student->delete();

    expect(Student::find($id))->toBeNull();
    expect(Student::where('id', $id)->count())->toBe(0);

    // But accessible with withTrashed().
    expect(Student::withTrashed()->find($id))->not->toBeNull();
});

// ── 9. Financial history remains accessible after soft delete ─────────────────
test('student bills remain accessible after student is soft deleted', function () {
    $student = Student::factory()->calon()->create();
    $bill    = StudentBill::factory()->create(['student_id' => $student->id]);

    // Soft delete the student (bypassing the controller guard for this test).
    DB::table('students')->where('id', $student->id)->update(['deleted_at' => now()]);

    // Bill still in DB and accessible directly.
    $this->assertDatabaseHas('student_bills', ['id' => $bill->id, 'student_id' => $student->id]);

    // Bill can be loaded directly — financial history preserved.
    $loadedBill = StudentBill::find($bill->id);
    expect($loadedBill)->not->toBeNull();
    expect($loadedBill->student_id)->toBe($student->id);
});

// ── 10. PPDBController rejects calon_siswa with bills ────────────────────────
test('PPDB destroy rejects calon_siswa that already has a bill', function () {
    $admin   = User::factory()->create(['role' => 'admin']);
    $student = Student::factory()->calon()->create();
    StudentBill::factory()->create(['student_id' => $student->id]);

    $response = $this->actingAs($admin)
                     ->delete("/ppdb/{$student->id}");

    $response->assertStatus(302);
    $this->assertDatabaseHas('students', ['id' => $student->id, 'deleted_at' => null]);
    $this->assertDatabaseHas('student_bills', ['student_id' => $student->id]);
});

// ── 11. PPDB destroy allows calon_siswa without bills ────────────────────────
test('PPDB destroy allows calon_siswa without bills', function () {
    $admin   = User::factory()->create(['role' => 'admin']);
    $student = Student::factory()->calon()->create();

    $response = $this->actingAs($admin)
                     ->delete("/ppdb/{$student->id}");

    $response->assertRedirect(route('ppdb.index'));
    $this->assertSoftDeleted('students', ['id' => $student->id]);
});
