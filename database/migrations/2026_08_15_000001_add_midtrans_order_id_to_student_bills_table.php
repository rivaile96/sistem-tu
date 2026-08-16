<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Reconstructed historical migration — originally ran on production at batch 14.
 * File was lost from the repository; reconstructed from production schema evidence.
 *
 * Production evidence (SHOW FULL COLUMNS FROM student_bills):
 *   midtrans_order_id | varchar(255) | NULL=YES | Key=UNI | DEFAULT=NULL
 *   paid_at           | timestamp    | NULL=YES | Key=    | DEFAULT=NULL
 *
 * Production index evidence (SHOW INDEX FROM student_bills):
 *   student_bills_midtrans_order_id_unique | midtrans_order_id | Non_unique=0
 *
 * This migration is the canonical source of these two columns.
 * The Phase 1.6 file (2026_08_16_000001) was a temporary workaround and
 * has been removed now that this historical record is restored.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('student_bills', function (Blueprint $table) {
            $table->string('midtrans_order_id')->nullable()->unique()->after('payment_token');
            $table->timestamp('paid_at')->nullable()->after('midtrans_order_id');
        });
    }

    public function down(): void
    {
        Schema::table('student_bills', function (Blueprint $table) {
            $table->dropUnique('student_bills_midtrans_order_id_unique');
            $table->dropColumn(['midtrans_order_id', 'paid_at']);
        });
    }
};
