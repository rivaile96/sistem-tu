<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_bills', function (Blueprint $table) {
            $table->id();
            // Relasi ke Siswa
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            
            // Jenis Tagihan: SPP, GEDUNG, SERAGAM, KEGIATAN, DLL
            $table->string('type')->index(); 
            
            // Nama Tagihan: "Januari 2026", "Uang Pangkal", "Wisuda"
            $table->string('name');
            
            // Nominal Tagihan (Misal: 150.000)
            $table->decimal('amount', 12, 2);
            
            // Status Pembayaran
            $table->enum('status', ['UNPAID', 'PAID', 'PARTIAL'])->default('UNPAID');
            
            // Buat jaga-jaga kalau nanti konek ke Payment Gateway (Midtrans)
            $table->string('payment_token')->nullable();
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_bills');
    }
};