<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tabel Siswa
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('nis')->unique();
            $table->string('name');
            $table->string('class_name');
            $table->timestamps();
        });

        // 2. Tabel Barang (POS)
        Schema::create('pos_items', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('category');
            $table->integer('price');
            $table->integer('stock')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 3. Tabel Tagihan SPP
        Schema::create('spp_bills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students');
            $table->string('month');
            $table->integer('amount');
            $table->enum('status', ['LUNAS', 'BELUM', 'PENDING'])->default('BELUM');
            $table->string('payment_method')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });

        // 4. Tabel Transaksi POS
        Schema::create('pos_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users');
            $table->string('transaction_code')->unique();
            $table->integer('total_amount');
            $table->enum('status', ['UNPAID', 'PAID', 'CANCELLED', 'COMPLETED'])->default('UNPAID');
            $table->string('qr_token')->nullable(); 
            $table->timestamps();
        });

        // 5. Detail Transaksi
        Schema::create('pos_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pos_order_id')->constrained('pos_orders');
            $table->foreignId('pos_item_id')->constrained('pos_items');
            $table->integer('quantity');
            $table->integer('price_at_transaction');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pos_order_items');
        Schema::dropIfExists('pos_orders');
        Schema::dropIfExists('spp_bills');
        Schema::dropIfExists('pos_items');
        Schema::dropIfExists('students');
    }
};