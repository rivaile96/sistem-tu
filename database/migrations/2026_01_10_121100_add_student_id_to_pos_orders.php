<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pos_orders', function (Blueprint $table) {
            // Tambah kolom student_id (Boleh kosong kalau beli Cash/Anonim)
            if (!Schema::hasColumn('pos_orders', 'student_id')) {
                $table->foreignId('student_id')
                      ->nullable()
                      ->after('user_id') // Taruh setelah user_id (kasir)
                      ->constrained('students')
                      ->nullOnDelete(); // Kalau siswa dihapus, history transaksi tetap ada (tapi null)
            }
        });
    }

    public function down(): void
    {
        Schema::table('pos_orders', function (Blueprint $table) {
            $table->dropForeign(['student_id']);
            $table->dropColumn('student_id');
        });
    }
};