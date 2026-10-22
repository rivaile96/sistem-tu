<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Sanctum personal_access_tokens table.
 *
 * Dibutuhkan untuk TU Sanctum token — dipakai oleh StudentAuthController
 * untuk login siswa mobile (Task 11).
 *
 * Filename prefix 0001_01_01_000003 agar dijalankan tepat setelah users table
 * (0001_01_01_000000) dan sebelum migration lain yang depend on users.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('personal_access_tokens', function (Blueprint $table) {
            $table->id();
            $table->morphs('tokenable');
            $table->string('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('personal_access_tokens');
    }
};
