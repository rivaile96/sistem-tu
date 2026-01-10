<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            // Kita cek satu-satu, kalau belum ada baru ditambahin
            if (!Schema::hasColumn('students', 'nis')) {
                $table->string('nis')->unique()->nullable()->after('id');
            }
            
            // Kolom penting buat Integrasi
            if (!Schema::hasColumn('students', 'nfc_uid')) {
                $table->string('nfc_uid')->nullable()->index()->after('class_name');
            }
            
            if (!Schema::hasColumn('students', 'parent_phone')) {
                $table->string('parent_phone')->nullable()->after('nfc_uid');
            }
            
            if (!Schema::hasColumn('students', 'status')) {
                $table->string('status')->default('active')->after('parent_phone');
            }
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            // Hapus kolom kalau rollback
            $table->dropColumn(['nfc_uid', 'parent_phone', 'status']);
        });
    }
};