<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambah kolom username ke tabel users.
     *
     * username = NIS siswa — dipakai sebagai cross-system login identifier.
     * Nullable karena user admin/tu tidak memiliki NIS.
     * Unique: satu NIS hanya boleh ada satu akun TU.
     *
     * Ini migration yang hilang — kolom sudah ada di production PostgreSQL
     * tapi belum pernah ada migration-nya, menyebabkan SQLite test gagal.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'username')) {
                $table->string('username')->nullable()->unique()->after('name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'username')) {
                // Drop unique index before dropping column (required for SQLite)
                $table->dropUnique(['username']);
                $table->dropColumn('username');
            }
        });
    }
};
