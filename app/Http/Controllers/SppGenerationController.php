<?php

namespace App\Http\Controllers;

use App\Jobs\GenerateSppBillsJob;
use App\Models\FinanceBillingType;
use App\Models\FinanceSppRule;
use App\Models\StudentBill;
use App\Services\FinanceAuditService;
use App\Services\SppBillingEngine;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * SppGenerationController — Admin generate tagihan SPP bulanan.
 *
 * Routes: /finance/spp/generate
 * Middleware: auth + verified + role:admin
 *
 * TIDAK ada payment processing di sini.
 * TIDAK ada Midtrans di sini.
 * TIDAK ada email di sini.
 * Hanya: preview → confirm → generate.
 */
class SppGenerationController extends Controller
{
    public function __construct(private SppBillingEngine $engine) {}

    /**
     * GET /finance/spp/generate
     * Halaman form generate SPP.
     */
    public function index()
    {
        $activeRules = FinanceSppRule::with('creator')
            ->where('is_active', true)
            ->where('billing_type_code', 'SPP')
            ->orderBy('academic_year', 'desc')
            ->get();

        $currentMonth = now()->month;
        $currentYear  = now()->year;

        // Ringkasan tagihan bulan ini (untuk context)
        $thisMonthCount = StudentBill::where('type', 'SPP')
            ->where('bill_month', $currentMonth)
            ->where('bill_year', $currentYear)
            ->count();

        return view('finance.spp.generate', compact(
            'activeRules',
            'currentMonth',
            'currentYear',
            'thisMonthCount',
        ));
    }

    /**
     * POST /finance/spp/generate/preview
     * Preview apa yang akan di-generate — ZERO DB WRITES.
     */
    public function preview(Request $request)
    {
        $validated = $request->validate([
            'billing_month' => 'required|integer|min:1|max:12',
            'billing_year'  => 'required|integer|min:2020|max:2099',
        ]);

        $month = (int) $validated['billing_month'];
        $year  = (int) $validated['billing_year'];

        $result = $this->engine->preview($month, $year);

        // Log preview action
        FinanceAuditService::logRaw(
            action:   'SPP_GENERATION_PREVIEW',
            module:   'FINANCE_CONFIG',
            targetType: 'SppBilling',
            description: json_encode([
                'billing_period'  => $result['summary']['billing_period'],
                'new_bills'       => $result['summary']['new_bills'],
                'already_billed'  => $result['summary']['already_billed'],
                'conflicts'       => $result['summary']['conflicts'],
            ]),
            request: $request,
        );

        if ($request->expectsJson()) {
            return response()->json($result);
        }

        $activeRules    = FinanceSppRule::where('is_active', true)->where('billing_type_code', 'SPP')->get();
        $currentMonth   = $month;
        $currentYear    = $year;
        $thisMonthCount = StudentBill::where('type', 'SPP')
            ->where('bill_month', $month)->where('bill_year', $year)->count();
        $previewResult  = $result;

        return view('finance.spp.generate', compact(
            'activeRules', 'currentMonth', 'currentYear',
            'thisMonthCount', 'previewResult',
        ));
    }

    /**
     * POST /finance/spp/generate/confirm
     * Eksekusi generation.
     *
     * Untuk dataset kecil (<= 100 siswa aktif): langsung sync.
     * Untuk dataset besar: dispatch job ke queue.
     */
    public function confirm(Request $request)
    {
        $validated = $request->validate([
            'billing_month'  => 'required|integer|min:1|max:12',
            'billing_year'   => 'required|integer|min:2020|max:2099',
            'confirmation'   => 'required|in:KONFIRMASI',
        ], [
            'confirmation.in' => 'Ketik KONFIRMASI untuk melanjutkan.',
        ]);

        $month   = (int) $validated['billing_month'];
        $year    = (int) $validated['billing_year'];
        $adminId = auth()->id();

        // Hitung jumlah siswa aktif untuk menentukan sync vs async
        $studentCount = \App\Models\Student::where('status', 'active')->count();

        if ($studentCount > 300) {
            // Dispatch async — job sudah handle audit log
            GenerateSppBillsJob::dispatch($month, $year, $adminId);

            return redirect()->route('finance.spp.generate.index')
                ->with('success', "Generate SPP " . SppBillingEngine::bulanLabel($month) . " $year dijadwalkan untuk {$studentCount} siswa. Cek kembali dalam beberapa menit.");
        }

        // Sync untuk dataset kecil
        $result = $this->engine->generate($month, $year, $adminId);

        // Audit log
        FinanceAuditService::logRaw(
            action:      'SPP_GENERATION',
            module:      'FINANCE_CONFIG',
            targetType:  'SppBilling',
            description: json_encode($result),
            request:     $request,
        );

        return redirect()->route('finance.spp.generate.index')
            ->with('generation_result', $result);
    }
}
