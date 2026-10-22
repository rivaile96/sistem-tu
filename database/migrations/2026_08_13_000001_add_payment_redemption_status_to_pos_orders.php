<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Tambah payment_status dan redemption_status ke pos_orders.
     *
     * Context:
     * - Kolom 'status' (original) sudah ada sejak awal tapi tidak cukup untuk
     *   merepresentasikan dua dimensi state: payment dan redemption.
     * - Seluruh codebase (PosTransactionController, MidtransController,
     *   PosReportController, blade views) sudah menggunakan payment_status
     *   dan redemption_status, tapi kolom belum pernah dibuat di DB.
     * - Tabel saat ini kosong (0 rows) sehingga data migration trivial.
     *
     * Status legacy policy:
     * - Kolom 'status' dipertahankan, tidak dihapus.
     * - Semua logic baru menggunakan payment_status dan redemption_status.
     * - StudentController yang masih pakai 'status' akan difix di code.
     */
    public function up(): void
    {
        Schema::table('pos_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('pos_orders', 'payment_status')) {
                $table->string('payment_status')->default('UNPAID')->after('status');
            }
            if (!Schema::hasColumn('pos_orders', 'redemption_status')) {
                $table->string('redemption_status')->default('PENDING')->after('payment_status');
            }
        });

        // Copy data dari status → payment_status untuk baris existing.
        // Tabel saat ini kosong tapi migration tetap aman untuk future data.
        DB::statement("UPDATE pos_orders SET payment_status = status WHERE payment_status = 'UNPAID'");
    }

    public function down(): void
    {
        Schema::table('pos_orders', function (Blueprint $table) {
            $table->dropColumn(['payment_status', 'redemption_status']);
        });
    }
};
