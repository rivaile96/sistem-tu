<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pos_orders', function (Blueprint $table) {
            // Kita tambah kolom Bayar & Kembali
            if (!Schema::hasColumn('pos_orders', 'payment_amount')) {
                $table->decimal('payment_amount', 12, 2)->default(0)->after('total_amount');
            }
            if (!Schema::hasColumn('pos_orders', 'change_amount')) {
                $table->decimal('change_amount', 12, 2)->default(0)->after('payment_amount');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pos_orders', function (Blueprint $table) {
            $table->dropColumn(['payment_amount', 'change_amount']);
        });
    }
};