<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 1. TABEL PAKET (Bundling)
        // Contoh: Paket Kelas XI (Isinya Buku A, B, C)
        Schema::create('pos_bundles', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nama Paket
            $table->decimal('price', 15, 2); // Harga Paket
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 2. TABEL ISI PAKET
        // Menghubungkan Paket dengan Barang POS yang ada
        Schema::create('pos_bundle_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pos_bundle_id')->constrained()->onDelete('cascade');
            $table->foreignId('pos_item_id')->constrained()->onDelete('cascade'); // Barang asli di gudang
            $table->integer('quantity')->default(1); // Jumlah per item dalam paket
            $table->timestamps();
        });

        // 3. TABEL RINCIAN TAGIHAN (JEMBATAN VITAL)
        // Ini yang nyatet: Tagihan ID 50 itu beli "Seragam" dan "Buku"
        Schema::create('bill_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_bill_id')->constrained('student_bills')->onDelete('cascade');
            
            // Bisa isi barang satuan, atau paket
            $table->foreignId('pos_item_id')->nullable()->constrained('pos_items'); 
            $table->foreignId('pos_bundle_id')->nullable()->constrained('pos_bundles');
            
            $table->string('item_name'); // Nama barang/paket saat itu (Snapshot)
            $table->integer('quantity');
            $table->decimal('price', 15, 2); // Harga satuan saat itu
            $table->decimal('subtotal', 15, 2); // Qty * Price
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('bill_items');
        Schema::dropIfExists('pos_bundle_items');
        Schema::dropIfExists('pos_bundles');
    }
};