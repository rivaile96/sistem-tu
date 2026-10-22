<?php

namespace App\Jobs;

use App\Services\SppBillingEngine;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * GenerateSppBillsJob — Async SPP billing generation.
 *
 * Dipakai untuk dataset besar (1000+ siswa).
 * Menggunakan SppBillingEngine::generate() yang sudah chunked.
 *
 * TIDAK membuat payment token, Midtrans order, atau journal.
 */
class GenerateSppBillsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 1;   // billing generation tidak perlu retry otomatis
    public int $timeout = 300; // 5 menit untuk 1000+ siswa

    public function __construct(
        private int $month,
        private int $year,
        private int $adminId,
    ) {}

    public function handle(SppBillingEngine $engine): void
    {
        Log::info('GenerateSppBillsJob: mulai', [
            'month'    => $this->month,
            'year'     => $this->year,
            'admin_id' => $this->adminId,
        ]);

        $result = $engine->generate($this->month, $this->year, $this->adminId);

        // Audit log hasil generation
        try {
            DB::table('audit_logs')->insert([
                'user_id'     => $this->adminId,
                'action'      => 'SPP_GENERATION',
                'module'      => 'FINANCE_CONFIG',
                'target_type' => 'SppBilling',
                'target_id'   => null,
                'description' => json_encode([
                    'billing_period' => $result['billing_period'],
                    'generated'      => $result['generated'],
                    'already_billed' => $result['already_billed'],
                    'excluded'       => $result['excluded'],
                    'conflicts'      => $result['conflicts'],
                    'failed'         => $result['failed'],
                    'total_amount'   => $result['total_amount'],
                ]),
                'old_value'  => null,
                'new_value'  => json_encode($result),
                'ip_address' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('GenerateSppBillsJob: audit log gagal', ['error' => $e->getMessage()]);
        }

        Log::info('GenerateSppBillsJob: selesai', $result);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('GenerateSppBillsJob: job gagal', [
            'month'    => $this->month,
            'year'     => $this->year,
            'admin_id' => $this->adminId,
            'error'    => $exception->getMessage(),
        ]);
    }
}
