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
        Schema::create('payment_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_id', 64)->unique();
            $table->unsignedBigInteger('student_id')->index();
            $table->decimal('amount', 14, 2);
            $table->string('currency', 3)->default('IDR');
            $table->string('provider', 20)->default('doku')->index();
            $table->string('payment_method', 50)->default('DOKU');
            $table->string('payment_channel', 50)->nullable();
            $table->string('va_number', 64)->nullable();
            $table->string('payment_code', 64)->nullable();
            $table->text('qr_content')->nullable();
            $table->text('qr_url')->nullable();
            $table->text('payment_url')->nullable();
            $table->string('status', 20)->default('PENDING')->index(); // PENDING, PAID, EXPIRED, CANCELLED, FAILED
            $table->string('provider_reference', 128)->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('expiry_time')->nullable();
            $table->text('raw_response')->nullable();
            $table->timestamps();

            $table->foreign('student_id')
                ->references('id')
                ->on('student_profiles')
                ->onDelete('cascade');
        });

        Schema::create('payment_order_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('payment_order_id')->index();
            $table->unsignedBigInteger('bill_id')->nullable()->index();
            $table->unsignedBigInteger('pos_order_id')->nullable()->index();
            $table->decimal('amount', 14, 2);
            $table->string('name', 255);
            $table->timestamps();

            $table->foreign('payment_order_id')
                ->references('id')
                ->on('payment_orders')
                ->onDelete('cascade');

            $table->foreign('bill_id')
                ->references('id')
                ->on('student_bills')
                ->onDelete('set null');

            $table->foreign('pos_order_id')
                ->references('id')
                ->on('pos_orders')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_order_items');
        Schema::dropIfExists('payment_orders');
    }
};
