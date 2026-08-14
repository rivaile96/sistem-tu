<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── 1. tahun_ajaran ─────────────────────────────────────────────────
        Schema::create('tahun_ajaran', function (Blueprint $table) {
            $table->id();
            $table->string('nama');          // e.g. "2025/2026"
            $table->date('tanggal_mulai')->nullable();
            $table->date('tanggal_selesai')->nullable();
            $table->boolean('is_aktif')->default(false);
            $table->timestamps();
        });

        // ── 2. rombels ──────────────────────────────────────────────────────
        Schema::create('rombels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tahun_ajaran_id')->constrained('tahun_ajaran')->cascadeOnDelete();
            $table->foreignId('kelas_id')->constrained('kelas')->cascadeOnDelete();
            $table->string('nama_rombel');           // e.g. "X RPL 1"
            $table->string('wali_kelas')->nullable(); // nama wali kelas
            $table->boolean('is_aktif')->default(true);
            $table->timestamps();

            // Satu rombel unik per (kelas, tahun_ajaran, nama_rombel)
            $table->unique(['kelas_id', 'tahun_ajaran_id', 'nama_rombel'], 'rombel_unique');
        });

        // ── 3. student_rombels (pivot) ───────────────────────────────────────
        Schema::create('student_rombels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('rombel_id')->constrained('rombels')->cascadeOnDelete();
            $table->foreignId('tahun_ajaran_id')->constrained('tahun_ajaran')->cascadeOnDelete();
            $table->timestamps();

            // Satu siswa hanya boleh di satu rombel per tahun ajaran
            $table->unique(['student_id', 'tahun_ajaran_id'], 'siswa_rombel_unik');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_rombels');
        Schema::dropIfExists('rombels');
        Schema::dropIfExists('tahun_ajaran');
    }
};
