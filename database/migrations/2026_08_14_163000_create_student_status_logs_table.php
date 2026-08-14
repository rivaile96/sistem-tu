<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tambah kolom baru ke students
        Schema::table('students', function (Blueprint $table) {
            if (!Schema::hasColumn('students', 'nisn')) {
                $table->string('nisn', 20)->nullable()->unique()->after('nis')->comment('Nomor Induk Siswa Nasional');
            }
            if (!Schema::hasColumn('students', 'gender')) {
                $table->enum('gender', ['L', 'P'])->nullable()->after('name')->comment('L=Laki-laki, P=Perempuan');
            }
            if (!Schema::hasColumn('students', 'birth_place')) {
                $table->string('birth_place')->nullable()->after('gender');
            }
            if (!Schema::hasColumn('students', 'birth_date')) {
                $table->date('birth_date')->nullable()->after('birth_place');
            }
            if (!Schema::hasColumn('students', 'address')) {
                $table->text('address')->nullable()->after('birth_date');
            }
            if (!Schema::hasColumn('students', 'agama')) {
                $table->string('agama', 20)->nullable()->after('address');
            }
            if (!Schema::hasColumn('students', 'tahun_masuk')) {
                $table->year('tahun_masuk')->nullable()->after('agama');
            }
            if (!Schema::hasColumn('students', 'status_notes')) {
                $table->text('status_notes')->nullable()->after('status')->comment('Catatan perubahan status terakhir');
            }
            if (!Schema::hasColumn('students', 'status_changed_at')) {
                $table->timestamp('status_changed_at')->nullable()->after('status_notes');
            }
            if (!Schema::hasColumn('students', 'status_changed_by')) {
                $table->foreignId('status_changed_by')->nullable()->constrained('users')->nullOnDelete()->after('status_changed_at');
            }
        });

        // Buat tabel log perubahan status siswa
        Schema::create('student_status_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->string('status_lama', 50)->nullable()->comment('Status sebelum diubah');
            $table->string('status_baru', 50)->comment('Status baru');
            $table->text('catatan')->nullable()->comment('Alasan / keterangan perubahan');
            $table->string('dokumen_path')->nullable()->comment('Path file surat/dokumen pendukung');
            $table->foreignId('diubah_oleh')->constrained('users')->comment('User yang mengubah status');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_status_logs');
        Schema::table('students', function (Blueprint $table) {
            $table->dropColumn([
                'nisn', 'gender', 'birth_place', 'birth_date',
                'address', 'agama', 'tahun_masuk',
                'status_notes', 'status_changed_at', 'status_changed_by',
            ]);
        });
    }
};
