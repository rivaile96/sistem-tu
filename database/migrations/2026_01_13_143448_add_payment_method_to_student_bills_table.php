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
        // Nambah kolom payment_method setelah kolom status
        $table->string('payment_method')->nullable()->after('status')->default('CASH'); 
    });
}

public function down()
{
    Schema::table('student_bills', function (Blueprint $table) {
        $table->dropColumn('payment_method');
    });
}

};
