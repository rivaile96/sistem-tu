<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_profiles', function (Blueprint $table) {
            // Timestamp saat profile terakhir di-sync dari Kaffah Backend.
            // NULL = belum pernah sync (dibuat manual di TU).
            // Dipakai untuk audit trail dan menentukan apakah data perlu di-refresh.
            if (!Schema::hasColumn('student_profiles', 'synced_at')) {
                $table->timestamp('synced_at')->nullable()->after('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('student_profiles', function (Blueprint $table) {
            $table->dropColumn('synced_at');
        });
    }
};
