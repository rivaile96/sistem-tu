<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambah kolom audit ke audit_logs yang sudah ada.
     *
     * audit_logs dibuat di migration 2026_01_06 dengan kolom minimal:
     *   id, user_id, action, module, description, timestamps
     *
     * Finance config membutuhkan:
     *   target_type  — model yang diubah (contoh: FinanceBillingType)
     *   target_id    — ID record yang diubah
     *   old_value    — JSON snapshot sebelum perubahan
     *   new_value    — JSON snapshot setelah perubahan
     *   ip_address   — untuk audit trail keamanan
     */
    public function up(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            if (!Schema::hasColumn('audit_logs', 'target_type')) {
                $table->string('target_type')->nullable()->after('module')
                      ->comment('Model class name, contoh: FinanceBillingType');
            }
            if (!Schema::hasColumn('audit_logs', 'target_id')) {
                $table->unsignedBigInteger('target_id')->nullable()->after('target_type')
                      ->comment('ID record yang diubah');
            }
            if (!Schema::hasColumn('audit_logs', 'old_value')) {
                $table->json('old_value')->nullable()->after('target_id')
                      ->comment('JSON snapshot sebelum perubahan');
            }
            if (!Schema::hasColumn('audit_logs', 'new_value')) {
                $table->json('new_value')->nullable()->after('old_value')
                      ->comment('JSON snapshot setelah perubahan');
            }
            if (!Schema::hasColumn('audit_logs', 'ip_address')) {
                $table->string('ip_address', 45)->nullable()->after('new_value');
            }
        });
    }

    public function down(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            $cols = [];
            foreach (['target_type', 'target_id', 'old_value', 'new_value', 'ip_address'] as $col) {
                if (Schema::hasColumn('audit_logs', $col)) $cols[] = $col;
            }
            if (!empty($cols)) $table->dropColumn($cols);
        });
    }
};
