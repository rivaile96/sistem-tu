<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Phase 6B-4 — Drop spp_bills table.
 *
 * All data has been migrated to student_bills (type='SPP') with spp_legacy_id
 * referencing the original spp_bills.id for audit traceability.
 *
 * Migration summary:
 *   - spp_bills.id=3 → student_bills.id=86 (PAID, spp_legacy_id=3)
 *   - spp_bills.id=4 → student_bills.id=87 (UNPAID, spp_legacy_id=4)
 *   - spp_bills.id=1 (duplicate Februari 2024) — superseded by id=3, not migrated
 *   - spp_bills.id=2 (duplicate Maret 2024, PENDING) — superseded by id=4, not migrated
 *
 * Rollback: recreates the table structure and restores the 4 rows from
 * student_bills records that have spp_legacy_id set.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('spp_bills');
    }

    public function down(): void
    {
        // Recreate the table structure
        Schema::create('spp_bills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->onDelete('restrict');
            $table->string('month');
            $table->integer('amount');
            $table->enum('status', ['LUNAS', 'BELUM', 'PENDING'])->default('BELUM');
            $table->string('midtrans_order_id')->nullable()->unique();
            $table->string('snap_token')->nullable();
            $table->string('payment_method')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->foreignId('confirmed_by')->nullable()->constrained('users')->onDelete('restrict');
            $table->timestamps();
        });

        // Restore migrated rows from student_bills back to spp_bills
        $migrated = \DB::table('student_bills')
            ->whereNotNull('spp_legacy_id')
            ->get(['student_id', 'name', 'amount', 'status', 'paid_at', 'payment_method', 'confirmed_by', 'spp_legacy_id']);

        foreach ($migrated as $row) {
            // Reverse name "SPP Februari 2024" → "Februari 2024"
            $month     = preg_replace('/^SPP\s+/i', '', $row->name);
            $sppStatus = $row->status === 'PAID' ? 'LUNAS' : 'BELUM';

            \DB::table('spp_bills')->insert([
                'student_id'     => $row->student_id,
                'month'          => $month,
                'amount'         => (int) $row->amount,
                'status'         => $sppStatus,
                'payment_method' => $row->payment_method,
                'paid_at'        => $row->paid_at,
                'confirmed_by'   => $row->confirmed_by,
                'created_at'     => now(),
                'updated_at'     => now(),
            ]);
        }
    }
};
