<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_agreements', function (Blueprint $table) {
            $table->id();
            $table->string('agreement_number')->unique(); // Cth: AGR-2026-0001
            
            // 🔥 FLEKSIBILITAS PPDB -> KESISWAAN 🔥
            $table->unsignedBigInteger('registration_id')->nullable()->comment('ID Calon Siswa dari modul PPDB'); 
            $table->unsignedBigInteger('student_id')->nullable()->comment('ID Siswa Resmi'); 
            $table->foreignId('student_bill_id')->nullable()->constrained('student_bills')->nullOnDelete(); 

            // Kalkulasi Uang
            $table->decimal('total_amount', 15, 2);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('net_amount', 15, 2); // total_amount - discount
            $table->decimal('dp_amount', 15, 2)->default(0);
            $table->decimal('remaining_amount', 15, 2); // net_amount - dp_amount
            $table->integer('installments_count'); // Berapa kali dicicil

            // Status Kontrak & Approval
            $table->enum('status', ['PENDING', 'APPROVED', 'ACTIVE', 'COMPLETED', 'REJECTED', 'CANCELED'])->default('PENDING');
            $table->unsignedBigInteger('approved_by')->nullable()->index()->comment('ID Admin dari Kaffah Backend');
            
            // Dokumen Resmi
            $table->text('notes')->nullable();
            $table->string('document_path')->nullable(); // Kalau nanti mau simpan PDF E-Sign
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_agreements');
    }
};