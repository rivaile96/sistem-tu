<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Guard: skip jika tabel 'students' tidak ada.
     * Skema baru menggunakan 'student_profiles' — tabel 'students' adalah legacy.
     */
    public function up(): void
    {
        if (!Schema::hasTable('students')) {
            return;
        }

        Schema::table('students', function (Blueprint $table) {
            if (!Schema::hasColumn('students', 'nis')) {
                $table->string('nis')->unique()->nullable()->after('id');
            }
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
        if (!Schema::hasTable('students')) {
            return;
        }

        Schema::table('students', function (Blueprint $table) {
            // SQLite: index harus di-drop sebelum kolom bisa di-drop
            if (Schema::hasColumn('students', 'nfc_uid')) {
                $table->dropIndex('students_nfc_uid_index');
                $table->dropColumn('nfc_uid');
            }
            if (Schema::hasColumn('students', 'parent_phone')) {
                $table->dropColumn('parent_phone');
            }
            if (Schema::hasColumn('students', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};
