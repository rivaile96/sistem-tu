<?php

namespace App\Http\Controllers;

use App\Models\FinanceBillingType;
use App\Models\FinanceSppRule;
use App\Services\FinanceAuditService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * FinanceSettingController — Admin konfigurasi finance.
 *
 * Routes: /finance/settings
 * Middleware: auth + verified + role:admin
 *
 * PENTING: Controller ini HANYA mengatur konfigurasi.
 * TIDAK ada billing generation di sini.
 * TIDAK ada payment processing di sini.
 */
class FinanceSettingController extends Controller
{
    // ================================================================
    // BILLING TYPES
    // ================================================================

    /**
     * GET /finance/settings
     * Dashboard konfigurasi finance.
     */
    public function index()
    {
        $billingTypes = FinanceBillingType::ordered()->get();
        $sppRules     = FinanceSppRule::with('creator')
            ->orderBy('academic_year', 'desc')
            ->orderBy('billing_type_code')
            ->orderBy('class_scope')
            ->get();

        return view('finance.settings.index', compact('billingTypes', 'sppRules'));
    }

    /**
     * POST /finance/settings/billing-types
     * Buat jenis tagihan baru.
     */
    public function storeBillingType(Request $request)
    {
        $validated = $request->validate([
            'code'         => [
                'required',
                'string',
                'max:30',
                'regex:/^[A-Z0-9_]+$/',
                Rule::unique('finance_billing_types', 'code'),
            ],
            'name'         => 'required|string|max:100',
            'description'  => 'nullable|string|max:500',
            'is_recurring' => 'boolean',
            'sort_order'   => 'integer|min:0|max:9999',
        ], [
            'code.regex' => 'Kode harus huruf kapital, angka, atau underscore (contoh: SPP, GEDUNG_LANTAI_2).',
            'code.unique' => 'Kode ini sudah digunakan.',
        ]);

        $validated['code']        = strtoupper($validated['code']);
        $validated['is_recurring'] = $request->boolean('is_recurring');
        $validated['is_active']   = true;
        $validated['created_by']  = auth()->id();
        $validated['updated_by']  = auth()->id();

        $billingType = FinanceBillingType::create($validated);

        FinanceAuditService::logCreate($billingType, $request);

        return redirect()->route('finance.settings.index')
            ->with('success', "Jenis tagihan \"{$billingType->name}\" berhasil ditambahkan.");
    }

    /**
     * PUT /finance/settings/billing-types/{id}
     * Update jenis tagihan.
     */
    public function updateBillingType(Request $request, int $id)
    {
        $billingType = FinanceBillingType::findOrFail($id);
        $oldData     = $billingType->toArray();

        $validated = $request->validate([
            'name'         => 'required|string|max:100',
            'description'  => 'nullable|string|max:500',
            'is_recurring' => 'boolean',
            'sort_order'   => 'integer|min:0|max:9999',
        ]);

        // Code tidak boleh diubah setelah dibuat — bisa breaking existing bills
        $validated['is_recurring'] = $request->boolean('is_recurring');
        $validated['updated_by']   = auth()->id();

        $billingType->update($validated);

        FinanceAuditService::logUpdate($billingType, $oldData, $request);

        return redirect()->route('finance.settings.index')
            ->with('success', "Jenis tagihan \"{$billingType->name}\" berhasil diperbarui.");
    }

    /**
     * POST /finance/settings/billing-types/{id}/toggle
     * Aktifkan / nonaktifkan jenis tagihan.
     */
    public function toggleBillingType(Request $request, int $id)
    {
        $billingType = FinanceBillingType::findOrFail($id);
        $newActive   = !$billingType->is_active;

        $billingType->update([
            'is_active'  => $newActive,
            'updated_by' => auth()->id(),
        ]);

        FinanceAuditService::logToggle($billingType, 'is_active', $newActive, $request);

        $status = $newActive ? 'diaktifkan' : 'dinonaktifkan';
        return redirect()->route('finance.settings.index')
            ->with('success', "Jenis tagihan \"{$billingType->name}\" berhasil {$status}.");
    }

    // ================================================================
    // SPP RULES
    // ================================================================

    /**
     * POST /finance/settings/spp-rules
     * Buat aturan SPP baru.
     */
    public function storeSppRule(Request $request)
    {
        $validated = $request->validate([
            'name'                     => 'required|string|max:150',
            'billing_type_code'        => [
                'required',
                'string',
                Rule::exists('finance_billing_types', 'code')->where('is_active', true),
            ],
            'academic_year'            => ['required', 'regex:/^\d{4}\/\d{4}$/'],
            'start_month'              => 'required|integer|min:1|max:12',
            'start_year'               => 'required|integer|min:2020|max:2099',
            'amount'                   => 'required|numeric|min:1000|max:99999999',
            'due_day'                  => 'required|integer|min:1|max:28',
            'class_scope'              => 'nullable|string|max:50',
            'new_student_start_offset' => 'nullable|integer|min:0|max:12',
            'notes'                    => 'nullable|string|max:500',
        ], [
            'academic_year.regex'         => 'Format tahun ajaran: 2026/2027',
            'billing_type_code.exists'    => 'Jenis tagihan tidak ditemukan atau tidak aktif.',
            'amount.min'                  => 'Nominal minimum Rp 1.000.',
            'due_day.max'                 => 'Tanggal jatuh tempo maksimal 28 (menghindari masalah bulan pendek).',
        ]);

        // Cek duplikasi kombinasi unik
        $exists = FinanceSppRule::where('academic_year', $validated['academic_year'])
            ->where('billing_type_code', $validated['billing_type_code'])
            ->where('class_scope', $validated['class_scope'] ?? null)
            ->exists();

        if ($exists) {
            return back()
                ->withInput()
                ->withErrors(['billing_type_code' => 'Aturan dengan kombinasi tahun ajaran, jenis tagihan, dan scope kelas ini sudah ada.']);
        }

        $validated['is_active']  = true;
        $validated['created_by'] = auth()->id();
        $validated['updated_by'] = auth()->id();

        $rule = FinanceSppRule::create($validated);

        FinanceAuditService::logCreate($rule, $request);

        return redirect()->route('finance.settings.index')
            ->with('success', "Aturan SPP \"{$rule->name}\" berhasil ditambahkan.");
    }

    /**
     * PUT /finance/settings/spp-rules/{id}
     * Update aturan SPP.
     */
    public function updateSppRule(Request $request, int $id)
    {
        $rule    = FinanceSppRule::findOrFail($id);
        $oldData = $rule->toArray();

        $validated = $request->validate([
            'name'                     => 'required|string|max:150',
            'amount'                   => 'required|numeric|min:1000|max:99999999',
            'due_day'                  => 'required|integer|min:1|max:28',
            'start_month'              => 'required|integer|min:1|max:12',
            'start_year'               => 'required|integer|min:2020|max:2099',
            'new_student_start_offset' => 'nullable|integer|min:0|max:12',
            'notes'                    => 'nullable|string|max:500',
        ]);

        // billing_type_code, academic_year, class_scope tidak bisa diubah setelah dibuat
        // karena bisa mempengaruhi logika billing yang sudah berjalan
        $validated['updated_by'] = auth()->id();

        $rule->update($validated);

        FinanceAuditService::logUpdate($rule, $oldData, $request);

        return redirect()->route('finance.settings.index')
            ->with('success', "Aturan SPP \"{$rule->name}\" berhasil diperbarui.");
    }

    /**
     * POST /finance/settings/spp-rules/{id}/toggle
     * Aktifkan / nonaktifkan aturan SPP.
     */
    public function toggleSppRule(Request $request, int $id)
    {
        $rule      = FinanceSppRule::findOrFail($id);
        $newActive = !$rule->is_active;

        $rule->update([
            'is_active'  => $newActive,
            'updated_by' => auth()->id(),
        ]);

        FinanceAuditService::logToggle($rule, 'is_active', $newActive, $request);

        $status = $newActive ? 'diaktifkan' : 'dinonaktifkan';
        return redirect()->route('finance.settings.index')
            ->with('success', "Aturan SPP \"{$rule->name}\" berhasil {$status}.");
    }
}
