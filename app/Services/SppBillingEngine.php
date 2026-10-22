<?php

namespace App\Services;

use App\Models\FinanceSppRule;
use App\Models\Student;
use App\Models\StudentBill;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * SppBillingEngine — Core logic untuk generate tagihan SPP.
 *
 * TIDAK menulis ke DB saat preview.
 * TIDAK generate payment token, Midtrans order, atau journal.
 * Hanya: baca aturan → tentukan eligibilitas → buat/skip student_bills.
 *
 * Canonical table: student_bills (bukan spp_bills legacy).
 *
 * Idempotent: generate dua kali untuk periode yang sama → generated = 0 pada run ke-2.
 */
class SppBillingEngine
{
    // Nama bulan Indonesia untuk bill name
    private const BULAN = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
    ];

    /**
     * Preview: hitung apa yang AKAN terjadi tanpa menulis ke DB.
     *
     * @param int $month 1-12
     * @param int $year  contoh: 2026
     * @return array
     */
    public function preview(int $month, int $year): array
    {
        $rows    = [];
        $summary = [
            'billing_period'     => self::BULAN[$month] . ' ' . $year,
            'eligible'           => 0,
            'excluded'           => 0,
            'already_billed'     => 0,
            'conflicts'          => 0,
            'new_bills'          => 0,
            'estimated_total'    => '0',
            'excluded_reasons'   => [],
            'conflict_details'   => [],
        ];

        $activeRules = FinanceSppRule::where('is_active', true)
            ->where('billing_type_code', 'SPP')
            ->get();

        if ($activeRules->isEmpty()) {
            $summary['conflicts'][] = 'Tidak ada aturan SPP aktif untuk periode ini.';
            return ['summary' => $summary, 'rows' => $rows];
        }

        Student::with('user')
            ->where('status', 'active')
            ->chunkById(200, function (Collection $students) use (
                $month, $year, $activeRules, &$rows, &$summary
            ) {
                foreach ($students as $student) {
                    $row = $this->evaluateStudent($student, $month, $year, $activeRules, dryRun: true);

                    if ($row['action'] === 'generate') {
                        $summary['eligible']++;
                        $summary['new_bills']++;
                        $summary['estimated_total'] = bcadd(
                            $summary['estimated_total'],
                            (string) $row['amount'],
                            2
                        );
                    } elseif ($row['action'] === 'skip_already_billed') {
                        $summary['already_billed']++;
                    } elseif ($row['action'] === 'conflict') {
                        $summary['eligible']++;
                        $summary['conflicts']++;
                        $summary['conflict_details'][] = [
                            'nis'    => $student->nis,
                            'name'   => $student->name,
                            'reason' => $row['reason'],
                        ];
                    } else {
                        // excluded
                        $summary['excluded']++;
                        $key = $row['reason'] ?? 'unknown';
                        $summary['excluded_reasons'][$key] = ($summary['excluded_reasons'][$key] ?? 0) + 1;
                    }

                    $rows[] = $row;
                }
            });

        return ['summary' => $summary, 'rows' => $rows];
    }

    /**
     * Generate tagihan SPP untuk periode tertentu.
     * Proses per-siswa dalam transaksi terpisah untuk failure isolation.
     *
     * @param int $month  1-12
     * @param int $year   contoh: 2026
     * @param int $adminId
     * @return array  result summary
     */
    public function generate(int $month, int $year, int $adminId): array
    {
        $result = [
            'billing_period' => self::BULAN[$month] . ' ' . $year,
            'scanned'        => 0,
            'generated'      => 0,
            'already_billed' => 0,
            'excluded'       => 0,
            'conflicts'      => 0,
            'failed'         => 0,
            'total_amount'   => '0',
        ];

        $activeRules = FinanceSppRule::where('is_active', true)
            ->where('billing_type_code', 'SPP')
            ->get();

        if ($activeRules->isEmpty()) {
            Log::warning('SppBillingEngine: tidak ada aturan SPP aktif', [
                'month' => $month, 'year' => $year,
            ]);
            return $result;
        }

        Student::with('user')
            ->where('status', 'active')
            ->chunkById(200, function (Collection $students) use (
                $month, $year, $activeRules, $adminId, &$result
            ) {
                foreach ($students as $student) {
                    $result['scanned']++;

                    $row = $this->evaluateStudent($student, $month, $year, $activeRules, dryRun: false);

                    match ($row['action']) {
                        'generate'           => $this->commitBill($student, $row, $adminId, $result),
                        'skip_already_billed'=> $result['already_billed']++,
                        'conflict'           => $result['conflicts']++,
                        default              => $result['excluded']++,
                    };
                }
            });

        Log::info('SppBillingEngine: generate selesai', array_merge($result, [
            'admin_id' => $adminId,
        ]));

        return $result;
    }

    /**
     * Evaluasi satu siswa untuk billing period tertentu.
     * Dipakai oleh preview() dan generate().
     */
    private function evaluateStudent(
        Student    $student,
        int        $month,
        int        $year,
        Collection $activeRules,
        bool       $dryRun
    ): array {
        $base = [
            'student_id'  => $student->id,
            'nis'         => $student->nis,
            'name'        => $student->name,
            'class'       => $student->class_name,
            'enrolled_at' => $student->enrolled_at?->toDateString(),
        ];

        // ── RULE MATCHING ────────────────────────────────────────────
        $rule = $this->resolveRule($student, $activeRules);

        if ($rule === null) {
            return array_merge($base, [
                'action' => 'conflict',
                'reason' => 'Tidak ada aturan SPP aktif yang cocok untuk kelas ini.',
            ]);
        }

        if ($rule === 'multiple') {
            return array_merge($base, [
                'action' => 'conflict',
                'reason' => 'Lebih dari satu aturan SPP aktif cocok — konfigurasi ambiguous.',
            ]);
        }

        // ── NEW STUDENT OFFSET ───────────────────────────────────────
        if ($student->enrolled_at !== null && $rule->new_student_start_offset !== null) {
            $firstBillable = $student->enrolled_at
                ->startOfMonth()
                ->addMonths($rule->new_student_start_offset);

            $billingDate = Carbon::create($year, $month, 1);

            if ($billingDate->lt($firstBillable)) {
                return array_merge($base, [
                    'action'           => 'excluded',
                    'reason'           => 'Bulan sebelum first billable month (offset ' . $rule->new_student_start_offset . ')',
                    'first_billable'   => $firstBillable->format('Y-m'),
                    'billing_period'   => "$year-$month",
                ]);
            }
        }

        // ── enrolled_at NULL → legacy student, normal billing ────────
        // enrolled_at NULL tidak di-exclude dari billing normal,
        // hanya offset logic yang di-skip.

        // ── DUPLICATE CHECK ──────────────────────────────────────────
        $exists = StudentBill::where('student_id', $student->id)
            ->where('type', $rule->billing_type_code)
            ->where('bill_month', $month)
            ->where('bill_year', $year)
            ->exists();

        if ($exists) {
            return array_merge($base, [
                'action' => 'skip_already_billed',
                'reason' => 'Tagihan sudah ada untuk periode ini.',
            ]);
        }

        // ── READY TO GENERATE ────────────────────────────────────────
        $dueDate  = Carbon::create($year, $month, $rule->due_day)->toDateString();
        $billName = $rule->billing_type_code . ' ' . self::BULAN[$month] . ' ' . $year;

        return array_merge($base, [
            'action'   => 'generate',
            'rule_id'  => $rule->id,
            'rule_name'=> $rule->name,
            'amount'   => $rule->amount,
            'due_date' => $dueDate,
            'bill_name'=> $billName,
            'type'     => $rule->billing_type_code,
        ]);
    }

    /**
     * Resolve aturan SPP untuk satu siswa.
     *
     * Prioritas:
     * 1. Rule dengan class_scope yang cocok persis dengan student->class_name
     * 2. Rule dengan class_scope = NULL (global)
     *
     * Return:
     *   FinanceSppRule  — satu rule yang cocok
     *   'multiple'      — lebih dari satu rule match (conflict)
     *   null            — tidak ada rule yang cocok
     */
    private function resolveRule(Student $student, Collection $activeRules): FinanceSppRule|string|null
    {
        // Specific scope first
        $specific = $activeRules->filter(fn($r) =>
            $r->class_scope !== null &&
            $r->class_scope === $student->class_name
        );

        if ($specific->count() === 1) return $specific->first();
        if ($specific->count() > 1)  return 'multiple';

        // Global scope
        $global = $activeRules->filter(fn($r) => $r->class_scope === null);

        if ($global->count() === 1) return $global->first();
        if ($global->count() > 1)  return 'multiple';

        return null;
    }

    /**
     * Commit satu bill ke DB dalam transaksi terpisah.
     * Failure satu siswa tidak menghentikan siswa lain.
     */
    private function commitBill(Student $student, array $row, int $adminId, array &$result): void
    {
        $dueDate   = Carbon::parse($row['due_date']);
        $billMonth = $dueDate->month;
        $billYear  = $dueDate->year;

        try {
            DB::transaction(function () use ($student, $row, $billMonth, $billYear, &$result) {
                // Re-check inside transaction untuk concurrency safety
                $exists = StudentBill::where('student_id', $student->id)
                    ->where('type', $row['type'])
                    ->where('bill_month', $billMonth)
                    ->where('bill_year',  $billYear)
                    ->lockForUpdate()
                    ->exists();

                if ($exists) {
                    $result['already_billed']++;
                    return;
                }

                StudentBill::create([
                    'student_id'  => $student->id,
                    'type'        => $row['type'],
                    'name'        => $row['bill_name'],
                    'amount'      => $row['amount'],
                    'status'      => 'UNPAID',
                    'paid_amount' => 0,
                    'bill_month'  => $billMonth,
                    'bill_year'   => $billYear,
                    'due_date'    => $row['due_date'],
                ]);

                $result['generated']++;
                $result['total_amount'] = bcadd($result['total_amount'], (string) $row['amount'], 2);
            });
        } catch (\Illuminate\Database\UniqueConstraintViolationException) {
            // Race condition — DB unique index caught it
            $result['already_billed']++;
        } catch (\Throwable $e) {
            $result['failed']++;
            Log::error('SppBillingEngine: gagal generate bill untuk siswa', [
                'student_id' => $student->id,
                'nis'        => $student->nis,
                'error'      => $e->getMessage(),
            ]);
        }
    }

    /**
     * Helper: nama bulan Indonesia.
     */
    public static function bulanLabel(int $month): string
    {
        return self::BULAN[$month] ?? "Bulan-$month";
    }
}
