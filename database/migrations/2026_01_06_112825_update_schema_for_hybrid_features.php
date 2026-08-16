<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Update Users: Tambah Role & Phone
        Schema::table('users', function (Blueprint $table) {
            // Tambahkan kolom role jika belum ada
            if (!Schema::hasColumn('users', 'role')) {
                $table->enum('role', ['admin', 'tu', 'student', 'kepala_sekolah'])->default('student')->after('email');
            }
            if (!Schema::hasColumn('users', 'phone')) {
                $table->string('phone')->nullable()->after('name');
            }
        });

        // 2. Update Students: Link ke Orang Tua (User)
        Schema::table('students', function (Blueprint $table) {
            if (!Schema::hasColumn('students', 'user_id')) {
                $table->foreignId('user_id')->nullable()->after('id')->constrained('users')->onDelete('set null');
            }
            if (!Schema::hasColumn('students', 'parent_phone')) {
                $table->string('parent_phone')->nullable()->after('class_name');
            }
        });

        // 3. Update SPP Bills: Kolom Midtrans & Konfirmasi Manual
        Schema::table('spp_bills', function (Blueprint $table) {
            if (!Schema::hasColumn('spp_bills', 'midtrans_order_id')) {
                $table->string('midtrans_order_id')->nullable()->unique()->after('status');
                $table->string('snap_token')->nullable()->after('midtrans_order_id');
                // Untuk pembayaran manual cash yang dikonfirmasi TU
                $table->foreignId('confirmed_by')->nullable()->after('paid_at')->constrained('users'); 
            }
        });

        // 4. Update POS Items: Kode Barang & Gambar
        Schema::table('pos_items', function (Blueprint $table) {
            if (!Schema::hasColumn('pos_items', 'code')) {
                $table->string('code')->nullable()->unique()->after('id'); // Scan Barcode
                $table->string('image')->nullable()->after('stock'); // Foto Barang
            }
        });

        // 5. Update POS Orders: Pisah Status Bayar & Ambil
        Schema::table('pos_orders', function (Blueprint $table) {
            // Ganti nama status lama biar gak bingung (opsional, tapi kita tambah kolom baru aja)
            if (!Schema::hasColumn('pos_orders', 'payment_status')) {
                $table->enum('payment_status', ['UNPAID', 'PAID', 'EXPIRED', 'CANCELLED'])->default('UNPAID')->after('total_amount');
            }
            if (!Schema::hasColumn('pos_orders', 'redemption_status')) {
                $table->enum('redemption_status', ['PENDING', 'COMPLETED'])->default('PENDING')->comment('Status pengambilan barang')->after('payment_status');
            }
            if (!Schema::hasColumn('pos_orders', 'midtrans_order_id')) {
                $table->string('midtrans_order_id')->nullable()->after('transaction_code');
                $table->string('snap_token')->nullable()->after('midtrans_order_id');
            }
        });

        // 6. Buat Tabel Audit Logs (Baru)
        if (!Schema::hasTable('audit_logs')) {
            Schema::create('audit_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // Siapa pelakunya (nullable: gateway/system events have no user)
                $table->string('action'); // CREATE, UPDATE, DELETE, LOGIN
                $table->string('module'); // SPP, POS, STUDENT
                $table->text('description')->nullable(); // Detail perubahan (JSON atau text)
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        
        Schema::table('pos_orders', function (Blueprint $table) {
            $table->dropColumn(['payment_status', 'redemption_status', 'midtrans_order_id', 'snap_token']);
        });

        Schema::table('pos_items', function (Blueprint $table) {
            $table->dropColumn(['code', 'image']);
        });

        Schema::table('spp_bills', function (Blueprint $table) {
            $table->dropForeign(['confirmed_by']);
            $table->dropColumn(['midtrans_order_id', 'snap_token', 'confirmed_by']);
        });

        Schema::table('students', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn(['user_id', 'parent_phone']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'phone']);
        });
    }
};