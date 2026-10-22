<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('payment_channels', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Contoh: "BCA Virtual Account"
            $table->string('code')->unique(); // Contoh: "bca_va"
            $table->string('type'); // Contoh: "virtual_account", "ewallet", "cstore", "qris"
            $table->string('logo')->nullable(); // Contoh: "/logos/bca.png"
            $table->boolean('is_active')->default(true); // 🔥 INI SAKLARNYA! 🔥
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('payment_channels');
    }
};