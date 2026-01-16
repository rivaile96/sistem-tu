<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::create('school_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        // Data Default
        DB::table('school_settings')->insert([
            ['key' => 'school_name', 'value' => 'SMK Digischool Indonesia', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'school_address', 'value' => 'Jl. Teknologi No. 1, Jakarta', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'school_phone', 'value' => '021-555-0199', 'created_at' => now(), 'updated_at' => now()],
            ['key' => 'head_of_admin', 'value' => 'Admin TU', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down()
    {
        Schema::dropIfExists('school_settings');
    }
};