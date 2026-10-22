<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * personal_access_tokens sudah dibuat oleh migration awal
     * (0001_01_01_000003_create_personal_access_tokens_table.php).
     *
     * Migration ini adalah no-op — tabel sudah pasti ada saat ini dijalankan
     * karena migration 0001_01_01_000003 selalu dijalankan lebih dulu
     * (urutan timestamp menjamin ini).
     *
     * Kenapa tidak dihapus saja:
     * - Menghapus migration yang sudah tercatat di migrations table production
     *   akan menyebabkan inconsistency.
     * - No-op jauh lebih aman daripada delete.
     */
    public function up(): void
    {
        // no-op: tabel dibuat oleh 0001_01_01_000003_create_personal_access_tokens_table.php
        // Schema::createIfNotExists tidak tersedia di semua versi Laravel/driver,
        // jadi gunakan guard eksplisit.
        if (!Schema::hasTable('personal_access_tokens')) {
            Schema::create('personal_access_tokens', function (Blueprint $table) {
                $table->id();
                $table->morphs('tokenable');
                $table->text('name');
                $table->string('token', 64)->unique();
                $table->text('abilities')->nullable();
                $table->timestamp('last_used_at')->nullable();
                $table->timestamp('expires_at')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     * Tidak drop tabel — migration awal yang bertanggung jawab atas tabel ini.
     */
    public function down(): void
    {
        // no-op: tabel dikelola oleh migration 0001_01_01_000003
    }
};
