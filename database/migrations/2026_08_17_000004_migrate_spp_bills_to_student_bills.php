<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * Phase 6B-2 — Migrate spp_bills → student_bills.
 *
 * Rules:
 *   - Dedup by student_id + month: keep canonical rows only (decided by user)
 *   - Canonical rows: id=3 (Februari 2024, LUNAS) and id=4 (Maret 2024, BELUM)
 *   - General dedup rule: per student+month, prefer LUNAS with latest paid_at;
 *     if no LUNAS, prefer BELUM over PENDING
 *   - status mapping: LUNAS → PAID, BELUM/PENDING → UNPAID
 *   - Preserve spp_bills.id in student_bills.spp_legacy_id
 *   - Do NOT drop spp_bills
 *   - Runs inside a single transaction — rolls back on any error
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function () {

            // ── 1. Load all spp_bills ordered for dedup ───────────────────
            // Priority: LUNAS (latest paid_at) > BELUM > PENDING
            $sppRows = DB::table('spp_bills')
                ->orderByRaw("CASE status WHEN 'LUNAS' THEN 0 WHEN 'BELUM' THEN 1 ELSE 2 END")
                ->orderByDesc('paid_at')
                ->orderBy('id')
                ->get();

            // ── 2. Dedup: keep first row per student_id + month ───────────
            $seen     = [];
            $toMigrate = [];

            foreach ($sppRows as $row) {
                $key = $row->student_id . '|' . trim($row->month);
                if (isset($seen[$key])) continue;
                $seen[$key]  = true;
                $toMigrate[] = $row;
            }

            // ── 3. Insert into student_bills ──────────────────────────────
            $now = now();

            foreach ($toMigrate as $row) {
                // Parse month string "Februari 2024" → bill_month=2, bill_year=2024
                // Use Carbon with Indonesian locale month name
                $parsed     = $this->parseIndonesianMonth($row->month);
                $billMonth  = $parsed['month'];
                $billYear   = $parsed['year'];

                // Map status
                $status = $row->status === 'LUNAS' ? 'PAID' : 'UNPAID';

                // Skip if already migrated (idempotent re-run guard)
                $exists = DB::table('student_bills')
                    ->where('spp_legacy_id', $row->id)
                    ->exists();
                if ($exists) continue;

                DB::table('student_bills')->insert([
                    'student_id'      => $row->student_id,
                    'name'            => 'SPP ' . trim($row->month),
                    'type'            => 'SPP',
                    'amount'          => $row->amount,
                    'original_amount' => $row->amount,
                    'discount_amount' => 0,
                    'discount_note'   => null,
                    'spp_legacy_id'   => $row->id,
                    'status'          => $status,
                    'bill_month'      => $billMonth,
                    'bill_year'       => $billYear,
                    'paid_at'         => $row->status === 'LUNAS' ? $row->paid_at : null,
                    'payment_method'  => $row->status === 'LUNAS' ? $row->payment_method : null,
                    'confirmed_by'    => $row->status === 'LUNAS' ? $row->confirmed_by : null,
                    'created_by'      => null, // system migration
                    'created_at'      => $row->created_at ?? $now,
                    'updated_at'      => $now,
                ]);
            }
        });
    }

    public function down(): void
    {
        // Remove migrated rows (identified by non-null spp_legacy_id)
        DB::table('student_bills')
            ->whereNotNull('spp_legacy_id')
            ->delete();
    }

    // ── Helper: parse Indonesian month name + year ────────────────────────
    private function parseIndonesianMonth(string $monthStr): array
    {
        $map = [
            'januari'   => 1,  'februari' => 2,  'maret'    => 3,
            'april'     => 4,  'mei'       => 5,  'juni'     => 6,
            'juli'      => 7,  'agustus'   => 8,  'september'=> 9,
            'oktober'   => 10, 'november'  => 11, 'desember' => 12,
        ];

        $parts     = explode(' ', trim(strtolower($monthStr)));
        $monthName = $parts[0] ?? '';
        $year      = (int) ($parts[1] ?? date('Y'));
        $month     = $map[$monthName] ?? 1;

        return ['month' => $month, 'year' => $year];
    }
};
