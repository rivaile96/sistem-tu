<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambah unique index pada student_bills untuk mencegah duplikasi SPP.
     *
     * Uniqueness: satu siswa hanya boleh punya satu tagihan SPP
     * per bulan per tahun (student_id + type + bill_month + bill_year).
     *
     * Partial unique: hanya berlaku jika bill_month IS NOT NULL,
     * sehingga tagihan non-SPP (bill_month = NULL) tidak terpengaruh.
     *
     * PostgreSQL supports partial index, SQLite does not — gunakan
     * conditional migration yang aman untuk kedua environment.
     */
    public function up(): void
    {
        // Cek apakah index sudah ada sebelum buat
        if (!$this->indexExists()) {
            Schema::table('student_bills', function (Blueprint $table) {
                // Index ini mencegah race condition saat generate massal.
                // Bill engine JUGA melakukan existence check sebelum insert,
                // tapi index ini adalah last-line-of-defense di DB level.
                $table->unique(
                    ['student_id', 'type', 'bill_month', 'bill_year'],
                    'student_bills_spp_unique'
                );
            });
        }
    }

    public function down(): void
    {
        Schema::table('student_bills', function (Blueprint $table) {
            $table->dropUnique('student_bills_spp_unique');
        });
    }

    private function indexExists(): bool
    {
        try {
            $indexes = \Illuminate\Support\Facades\DB::select(
                "SELECT indexname FROM pg_indexes WHERE tablename = 'student_bills' AND indexname = 'student_bills_spp_unique'"
            );
            return !empty($indexes);
        } catch (\Throwable) {
            return false; // SQLite — index belum ada
        }
    }
};
