<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('student_bills', function (Blueprint $table) {
        // Kolom khusus SPP
        $table->integer('bill_month')->nullable(); // 1 - 12
        $table->integer('bill_year')->nullable();  // 2026
        $table->date('due_date')->nullable();      // Tgl Jatuh Tempo (buat Notif Android)
    });
}

public function down()
{
    Schema::table('student_bills', function (Blueprint $table) {
        $table->dropColumn(['bill_month', 'bill_year', 'due_date']);
    });
}
};
