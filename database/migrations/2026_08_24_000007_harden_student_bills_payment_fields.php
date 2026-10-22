<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Harden student_bills untuk payment lifecycle:
     *
     * 1. paid_amount: int4 → decimal(14,2)
     *    Alasan: bill.amount adalah NUMERIC (DECIMAL). paid_amount harus presisi sama.
     *    Existing values: semua 0 — konversi aman.
     *
     * 2. midtrans_order_id: tambah unique index
     *    Alasan: satu order harus memetakan tepat satu bill. Tanpa constraint,
     *    race condition bisa menyebabkan dua bills menerima order_id yang sama.
     *    Nullable unique: NULL diperbolehkan banyak (belum ada order), tapi
     *    non-NULL harus unik.
     *
     * 3. Tambah kolom 'PENDING' support di status — status sudah varchar,
     *    tidak perlu enum migration. Engine akan menulis 'PENDING' langsung.
     */
    public function up(): void
    {
        // 1. Fix paid_amount precision: int4 → decimal(14,2)
        // Use Blueprint::change() — cross-database safe (PostgreSQL + SQLite in tests)
        Schema::table('student_bills', function (Blueprint $table) {
            $table->decimal('paid_amount', 14, 2)->default(0)->change();
        });

        // 2. Unique index on midtrans_order_id
        Schema::table('student_bills', function (Blueprint $table) {
            if (!$this->indexExists('student_bills', 'student_bills_midtrans_order_id_unique')) {
                $table->unique('midtrans_order_id', 'student_bills_midtrans_order_id_unique');
            }
        });
    }

    public function down(): void
    {
        // Drop unique index FIRST — must happen before the earlier migration
        // (2026_02_22_190932) drops the midtrans_order_id column in its own down().
        // SQLite does not allow dropping a column that has an index on it.
        Schema::table('student_bills', function (Blueprint $table) {
            if ($this->indexExists('student_bills', 'student_bills_midtrans_order_id_unique')) {
                $table->dropUnique('student_bills_midtrans_order_id_unique');
            }
        });

        Schema::table('student_bills', function (Blueprint $table) {
            $table->integer('paid_amount')->default(0)->change();
        });
    }

    private function indexExists(string $table, string $index): bool
    {
        try {
            $result = DB::select(
                "SELECT 1 FROM pg_indexes WHERE tablename = ? AND indexname = ?",
                [$table, $index]
            );
            return !empty($result);
        } catch (\Throwable) {
            return false;
        }
    }
};
