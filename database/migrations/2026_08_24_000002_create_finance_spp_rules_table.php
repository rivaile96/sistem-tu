<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel konfigurasi aturan SPP.
     *
     * Admin mendefinisikan nominal SPP per tahun ajaran / jenjang / kelas.
     * Satu rule = satu kombinasi (tahun ajaran + kode tagihan + scope kelas opsional).
     *
     * PENTING: Tabel ini HANYA konfigurasi — TIDAK otomatis generate tagihan.
     * Billing generation adalah task terpisah.
     *
     * PENTING: Tidak ada FK ke tabel kelas karena TU menggunakan string
     * (student_profiles.class_name) — bukan tabel kelas relasional.
     */
    public function up(): void
    {
        Schema::create('finance_spp_rules', function (Blueprint $table) {
            $table->id();

            // Nama aturan untuk identifikasi admin (contoh: "SPP Kelas 10 2026/2027")
            $table->string('name', 150);

            // Referensi ke jenis tagihan (harus code yang ada di finance_billing_types)
            // Tidak pakai FK ke finance_billing_types agar rule tetap valid
            // jika billing type di-nonaktifkan sementara.
            $table->string('billing_type_code', 30)->index();

            // Tahun ajaran: format "2026/2027"
            $table->string('academic_year', 9);

            // Bulan mulai berlaku (1-12)
            $table->unsignedTinyInteger('start_month');

            // Tahun mulai berlaku
            $table->unsignedSmallInteger('start_year');

            // Nominal tagihan — DECIMAL, tidak pernah float
            $table->decimal('amount', 14, 2);

            // Tanggal jatuh tempo tiap bulan (1-28, hindari 29-31 untuk bulan pendek)
            $table->unsignedTinyInteger('due_day')->default(10);

            // Scope kelas opsional — string sesuai student_profiles.class_name
            // NULL berarti berlaku untuk semua kelas
            $table->string('class_scope', 50)->nullable()
                  ->comment('NULL = semua kelas, isi jika hanya untuk kelas tertentu');

            // Aturan siswa baru:
            // NULL = ikuti aturan normal
            // 0    = SPP mulai bulan yang sama dengan bulan masuk
            // 1    = SPP mulai bulan berikutnya setelah bulan masuk
            // 2    = SPP mulai 2 bulan setelah bulan masuk (jika biaya daftar ulang sudah termasuk)
            $table->unsignedTinyInteger('new_student_start_offset')->nullable()
                  ->comment('Offset bulan mulai SPP untuk siswa baru. NULL=ikuti normal, 0=bulan masuk, 1=bulan berikutnya');

            // Aktif/nonaktif
            $table->boolean('is_active')->default(true);

            // Catatan admin
            $table->text('notes')->nullable();

            // Audit
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();

            $table->timestamps();

            // Unique: satu rule per kombinasi tahun ajaran + billing type + scope kelas
            $table->unique(
                ['academic_year', 'billing_type_code', 'class_scope'],
                'spp_rule_unique_scope'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finance_spp_rules');
    }
};
