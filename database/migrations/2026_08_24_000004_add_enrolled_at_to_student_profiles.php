<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambah enrolled_at ke student_profiles.
     *
     * Semantics:
     *   enrolled_at = tanggal/waktu siswa RESMI menjadi student aktif di TU.
     *   Ini adalah business event "official enrollment", bukan technical event.
     *
     * BUKAN:
     *   - created_at  : waktu record dibuat di database
     *   - synced_at   : waktu terakhir sync dari Kaffah (technical event)
     *   - payment_date: waktu pembayaran SPMB
     *   - provisioning date: waktu Kaffah membuat akun
     *
     * Nullability:
     *   NULL = siswa sudah ada di TU (provisioned) tapi belum official enrolled.
     *   Ini terjadi ketika Kaffah sync mendahului official enrollment event.
     *   Finance HARUS memeriksa NULL sebelum menggunakan field ini.
     *
     * Who sets this:
     *   - Ideal: dikirim oleh Kaffah Backend saat official enrollment event.
     *   - Saat ini: GAP — sync payload belum membawa enrolled_at.
     *   - Fallback sementara: admin TU dapat mengisi manual via UI.
     *
     * Existing records:
     *   enrolled_at = NULL untuk semua siswa yang sudah ada.
     *   JANGAN backfill dengan created_at atau synced_at.
     */
    public function up(): void
    {
        Schema::table('student_profiles', function (Blueprint $table) {
            if (!Schema::hasColumn('student_profiles', 'enrolled_at')) {
                $table->timestamp('enrolled_at')
                      ->nullable()
                      ->after('synced_at')
                      ->comment('Tanggal resmi enrollment. NULL = provisioned tapi belum enrolled. BUKAN created_at.');
            }
        });
    }

    public function down(): void
    {
        Schema::table('student_profiles', function (Blueprint $table) {
            if (Schema::hasColumn('student_profiles', 'enrolled_at')) {
                $table->dropColumn('enrolled_at');
            }
        });
    }
};
