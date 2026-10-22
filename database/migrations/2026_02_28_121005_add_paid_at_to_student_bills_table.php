<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('student_bills', function (Blueprint $table) {
            // Tambahin kolom paid_at (Boleh kosong/nullable kalau belum lunas)
            $table->timestamp('paid_at')->nullable()->after('status');
        });
    }

    public function down()
    {
        Schema::table('student_bills', function (Blueprint $table) {
            $table->dropColumn('paid_at');
        });
    }
};