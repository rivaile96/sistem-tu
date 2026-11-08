<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (! Schema::hasColumn('users', 'pin')) {
                    $table->string('pin')->nullable()->after('password');
                }
                if (! Schema::hasColumn('users', 'is_active')) {
                    $table->boolean('is_active')->default(true)->after('role');
                }
            });
        }

        if (Schema::hasTable('student_profiles')) {
            Schema::table('student_profiles', function (Blueprint $table) {
                if (! Schema::hasColumn('student_profiles', 'pin')) {
                    $table->string('pin')->nullable()->after('status');
                }
                if (! Schema::hasColumn('student_profiles', 'parent_phone')) {
                    $table->string('parent_phone', 30)->nullable()->index()->after('class_name');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('student_profiles')) {
            Schema::table('student_profiles', function (Blueprint $table) {
                if (Schema::hasColumn('student_profiles', 'parent_phone')) {
                    $table->dropIndex(['parent_phone']);
                    $table->dropColumn('parent_phone');
                }
                if (Schema::hasColumn('student_profiles', 'pin')) {
                    $table->dropColumn('pin');
                }
            });
        }

        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                $cols = [];
                if (Schema::hasColumn('users', 'pin')) {
                    $cols[] = 'pin';
                }
                if (Schema::hasColumn('users', 'is_active')) {
                    $cols[] = 'is_active';
                }
                if (! empty($cols)) {
                    $table->dropColumn($cols);
                }
            });
        }
    }
};
