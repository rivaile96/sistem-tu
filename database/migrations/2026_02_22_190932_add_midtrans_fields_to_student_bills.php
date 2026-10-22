<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up(): void
    {
        Schema::table('student_bills', function (Blueprint $table) {
            // Tambahin 2 kolom ini bro
            $table->string('midtrans_order_id')->nullable()->after('status'); // Buat nampung ID keranjang Midtrans
            $table->integer('paid_amount')->default(0)->after('amount');      // Buat nampung jumlah cicilan yang udah dibayar
        });
    }

    public function down(): void
    {
        Schema::table('student_bills', function (Blueprint $table) {
            // Drop unique index on midtrans_order_id if it exists
            // (added by migration 2026_08_24_000007) before dropping the column.
            // SQLite does not allow dropping a column with an active index.
            if (Schema::hasColumn('student_bills', 'midtrans_order_id')) {
                try {
                    $table->dropUnique('student_bills_midtrans_order_id_unique');
                } catch (\Throwable) {
                    // index may not exist in all environments
                }
            }
            // Kalau di-rollback, hapus 2 kolom ini
            $table->dropColumn(['midtrans_order_id', 'paid_amount']);
        });
    }
};
