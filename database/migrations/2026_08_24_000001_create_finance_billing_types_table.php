<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabel konfigurasi jenis tagihan finance.
     *
     * Menggantikan string free-form di student_bills.type dengan
     * master data yang dapat dikonfigurasi oleh admin.
     *
     * PENTING: Tabel ini TIDAK mengubah student_bills.type yang sudah ada.
     * Code di sini hanya sebagai master reference — billing generation
     * akan menggunakannya di task berikutnya.
     */
    public function up(): void
    {
        Schema::create('finance_billing_types', function (Blueprint $table) {
            $table->id();

            // Kode unik untuk referensi cross-system (contoh: SPP, GEDUNG, PONDOK)
            // Harus uppercase, dipakai sebagai student_bills.type value
            $table->string('code', 30)->unique();

            // Nama tampilan (contoh: "SPP Bulanan", "Biaya Gedung")
            $table->string('name', 100);

            // Deskripsi opsional untuk admin
            $table->text('description')->nullable();

            // Apakah tagihan ini berulang per bulan (SPP = true, Uang Pangkal = false)
            $table->boolean('is_recurring')->default(false);

            // Urutan tampilan di UI
            $table->unsignedSmallInteger('sort_order')->default(0);

            // Aktif/nonaktif — nonaktif tidak bisa dipakai untuk tagihan baru
            $table->boolean('is_active')->default(true);

            // Audit: siapa yang terakhir mengubah
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by')->references('id')->on('users')->nullOnDelete();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finance_billing_types');
    }
};
