<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_schedules', function (Blueprint $table) {
            $table->id();
            // Sambungin ke tabel Agreement yang baru kita buat
            $table->foreignId('payment_agreement_id')->constrained('payment_agreements')->cascadeOnDelete();
            
            $table->integer('sequence'); // 0 untuk DP, 1 untuk Cicilan Pertama, dst.
            $table->string('name'); // Cth: "Cicilan 1 - Daftar Ulang"
            $table->decimal('amount', 15, 2);
            $table->date('due_date')->nullable(); // Jatuh Tempo
            
            $table->enum('status', ['UNPAID', 'PAID', 'OVERDUE'])->default('UNPAID');
            
            // Riwayat Pembayaran (Biar connect ke Midtrans / Kasir)
            $table->dateTime('paid_at')->nullable();
            $table->decimal('paid_amount', 15, 2)->nullable();
            $table->string('payment_method')->nullable();
            $table->string('midtrans_order_id')->nullable()->unique();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_schedules');
    }
};