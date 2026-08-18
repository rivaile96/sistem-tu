<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\Student;
use App\Models\StudentBill;
use App\Models\BillItem;
use App\Models\PosBundle;
use App\Models\PosItem;
use App\Services\FinancialAuditLogger;
use App\Services\BillingPaymentService;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BillController extends Controller
{
    // =========================================================================
    // 1. INDEX — Monitoring & Laporan
    // =========================================================================
    public function index(Request $request)
    {
        // Dropdown filter: sourced from master kelas, not distinct class_name
        $kelasList = Kelas::aktif()->orderBy('tingkat')->orderBy('nama_kelas')->get();
        $types     = StudentBill::select('type')->distinct()->pluck('type');

        $query = StudentBill::with('student.kelas')->latest();

        // Filter 1 — Kelas (via kelas_id relation, falls back to class_name for legacy)
        if ($request->kelas_id) {
            $query->whereHas('student', fn($q) => $q->where('kelas_id', $request->kelas_id));
        } elseif ($request->class_name) {
            // Phase 9.3: class_name dropped — ignore legacy param, no-op
            // (old bookmarks with ?class_name= will show unfiltered results)
        }

        // Filter 2 — Status
        if ($request->status) {
            $query->where('status', $request->status);
        }

        // Filter 3 — Tipe
        if ($request->type) {
            $query->where('type', $request->type);
        }

        // Filter 4 — Search
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhereHas('student', fn($sq) => $sq->where('name', 'like', '%' . $request->search . '%'));
            });
        }

        // Filter 5 — Tanggal
        if ($request->start_date && $request->end_date) {
            $query->whereBetween('created_at', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59',
            ]);
        }

        $totalTagihan    = (clone $query)->sum('amount');
        $totalSudahBayar = (clone $query)->where('status', 'PAID')->sum('amount');
        $totalTunggakan  = (clone $query)->where('status', 'UNPAID')->sum('amount');

        $bills = $query->paginate(20)->withQueryString();

        return view('bills.index', compact(
            'bills', 'kelasList', 'types',
            'totalTagihan', 'totalSudahBayar', 'totalTunggakan'
        ));
    }

    // =========================================================================
    // 2. CREATE — Generator Tagihan
    // =========================================================================
    public function create()
    {
        // Students for individual picker — show name + kelas.nama_kelas (from relation)
        $students = Student::with('kelas')
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        // Kelas for class-target picker — sourced from master
        $kelasList = Kelas::aktif()->orderBy('tingkat')->orderBy('nama_kelas')->get();

        $bundles = PosBundle::where('is_active', true)->orderBy('name')->get();

        return view('bills.create', compact('students', 'kelasList', 'bundles'));
    }

    // =========================================================================
    // 3. STORE — Proses Tagihan
    // =========================================================================
    public function store(Request $request)
    {
        $request->validate([
            'target_type' => 'required|in:student,class,all',
            'type'        => 'required|in:SPP,PAKET,DAFTAR_ULANG,LAINNYA',
        ]);

        $targetType = $request->target_type;
        $type       = strtoupper($request->type);
        $isSpp      = ($type === 'SPP');

        // ── Resolve target students ──────────────────────────────────────────
        if ($targetType === 'student') {
            $request->validate(['student_id' => 'required|exists:students,id']);
            $students = Student::where('id', $request->student_id)
                ->where('status', 'active')
                ->get();
        } elseif ($targetType === 'class') {
            $request->validate(['kelas_id' => 'required|exists:kelas,id']);
            $students = Student::where('kelas_id', $request->kelas_id)
                ->where('status', 'active')
                ->get();
        } else {
            $students = Student::where('status', 'active')->get();
        }

        // Discount fields — validated once, applied per-bill
        $discountAmount = max(0, (float) ($request->discount_amount ?? 0));
        $discountNote   = $request->discount_note ?? null;

        if ($students->isEmpty()) {
            return back()->with('error', 'Tidak ada siswa aktif yang sesuai target.')->withInput();
        }

        $count   = 0;
        $skipped = 0;

        try {
            DB::transaction(function () use (
                $students, $type, $isSpp, $request, $discountAmount, $discountNote, &$count, &$skipped
            ) {
            foreach ($students as $student) {
                if ($isSpp) {
                    // ── SPP flow ─────────────────────────────────────────────
                    $request->validate([
                        'spp_month'  => 'required|integer|between:1,12',
                        'spp_year'   => 'required|digits:4',
                        'spp_amount' => 'required|numeric|min:1',
                    ]);

                    $month  = (int) $request->spp_month;
                    $year   = (int) $request->spp_year;
                    $amount = (float) $request->spp_amount;

                    // Dedup: one SPP bill per student per month/year
                    $exists = StudentBill::where('student_id', $student->id)
                        ->where('type', 'SPP')
                        ->where('bill_month', $month)
                        ->where('bill_year', $year)
                        ->exists();

                    if ($exists) {
                        $skipped++;
                        continue;
                    }

                    $monthName = Carbon::createFromDate($year, $month, 1)->translatedFormat('F');
                    $dueDate   = Carbon::createFromDate($year, $month, 10)->toDateString();

                    $finalAmount    = max(0, $amount - $discountAmount);

                    $bill = StudentBill::create([
                        'student_id'      => $student->id,
                        'name'            => "SPP {$monthName} {$year}",
                        'type'            => 'SPP',
                        'amount'          => $finalAmount,
                        'original_amount' => $amount,
                        'discount_amount' => $discountAmount,
                        'discount_note'   => $discountNote,
                        'bill_month'      => $month,
                        'bill_year'       => $year,
                        'due_date'        => $dueDate,
                        'status'          => 'UNPAID',
                        'created_by'      => Auth::id(),
                    ]);

                    FinancialAuditLogger::billCreated($bill);

                } else {
                    // ── Regular bill flow ─────────────────────────────────────
                    $request->validate([
                        'name'          => 'required|string|max:255',
                        'item_names'    => 'required|array|min:1',
                        'item_names.*'  => 'nullable|string',
                        'item_prices'   => 'required|array|min:1',
                        'item_prices.*' => 'nullable|numeric|min:1',
                        'item_qtys'     => 'required|array|min:1',
                        'item_qtys.*'   => 'nullable|integer|min:1',
                    ]);

                    // Calculate total from items
                    $total = 0;
                    $items = [];
                    foreach ($request->item_names as $i => $itemName) {
                        if (empty($itemName) && empty($request->item_prices[$i])) continue;
                        $qty      = (int)   ($request->item_qtys[$i]   ?? 1);
                        $price    = (float) ($request->item_prices[$i]  ?? 0);
                        $subtotal = $qty * $price;
                        $total   += $subtotal;
                        $items[]  = [
                            'item_name'     => $itemName,
                            'quantity'      => $qty,
                            'price'         => $price,
                            'subtotal'      => $subtotal,
                            'pos_bundle_id' => $request->item_bundle_ids[$i] ?? null,
                        ];
                    }

                    $finalAmount = max(0, $total - $discountAmount);

                    $bill = StudentBill::create([
                        'student_id'      => $student->id,
                        'name'            => $request->name,
                        'type'            => $type,
                        'amount'          => $finalAmount,
                        'original_amount' => $total,
                        'discount_amount' => $discountAmount,
                        'discount_note'   => $discountNote,
                        'status'          => 'UNPAID',
                        'created_by'      => Auth::id(),
                    ]);

                    foreach ($items as $item) {
                        BillItem::create(array_merge($item, [
                            'student_bill_id' => $bill->id,
                        ]));
                    }

                    FinancialAuditLogger::billCreated($bill);
                }

                $count++;
            }
        });
        } catch (\Illuminate\Database\QueryException $e) {
            return back()->withInput()
                ->withErrors(['amount' => 'Nominal tagihan tidak valid: ' . $e->getMessage()]);
        }

        $msg = "Berhasil membuat {$count} tagihan";
        if ($skipped > 0) $msg .= ", {$skipped} dilewati (sudah ada)";

        return redirect()->route('bills.index')->with('success', $msg . '.');
    }

    // =========================================================================
    // 4. PAY — Konfirmasi Pembayaran Tunai
    // =========================================================================
    public function pay(Request $request, $id)
    {
        $bill = StudentBill::with(['student', 'items.product'])->findOrFail($id);

        try {
            app(BillingPaymentService::class)->payCash($bill, Auth::id());
        } catch (\RuntimeException $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
            }
            return back()->with('error', $e->getMessage());
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Pembayaran berhasil dicatat.']);
        }

        return back()->with('success', "Pembayaran tagihan {$bill->name} untuk {$bill->student->name} berhasil dicatat.");
    }

    // =========================================================================
    // 5. DESTROY — Hapus Tagihan (hanya UNPAID)
    // =========================================================================
    public function destroy($id)
    {
        $bill = StudentBill::findOrFail($id);

        if ($bill->status === 'PAID') {
            if (request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Tagihan lunas tidak dapat dihapus.'], 422);
            }
            return back()->with('error', 'Tagihan lunas tidak dapat dihapus.');
        }

        $name = $bill->name;
        $bill->items()->delete();
        $bill->delete();

        FinancialAuditLogger::billDeleted($bill, AuditLog::SOURCE_WEB);

        if (request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => "Tagihan {$name} berhasil dihapus."]);
        }

        return back()->with('success', "Tagihan {$name} berhasil dihapus.");
    }

    // =========================================================================
    // 6. PRINT — Cetak Kwitansi
    // =========================================================================
    public function print($id)
    {
        $bill   = StudentBill::with(['student.kelas', 'items'])->findOrFail($id);
        $school = DB::table('school_settings')->pluck('value', 'key');

        return view('bills.print', compact('bill', 'school'));
    }

    // =========================================================================
    // 7. EXPORT — CSV Export
    // =========================================================================
    public function export(Request $request)
    {
        $query = StudentBill::with('student.kelas')->latest();

        // Same filters as index — use kelas_id as primary
        if ($request->kelas_id) {
            $query->whereHas('student', fn($q) => $q->where('kelas_id', $request->kelas_id));
        } elseif ($request->class_name) {
            // Phase 9.3: class_name dropped — ignore legacy export param
        }
        if ($request->status)     $query->where('status', $request->status);
        if ($request->type)       $query->where('type', $request->type);
        if ($request->start_date && $request->end_date) {
            $query->whereBetween('created_at', [
                $request->start_date . ' 00:00:00',
                $request->end_date   . ' 23:59:59',
            ]);
        }

        $bills = $query->get();

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="tagihan-' . now()->format('Ymd-His') . '.csv"',
        ];

        $callback = function () use ($bills) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['No', 'Nama Siswa', 'NIS', 'Kelas', 'Tagihan', 'Tipe', 'Nominal', 'Status', 'Tgl Buat', 'Tgl Bayar']);

            foreach ($bills as $i => $bill) {
                // kelas column: use relation (kelas.nama_kelas), fall back to class_name
                $kelasLabel = optional($bill->student->kelas)->nama_kelas ?? '-';

                fputcsv($file, [
                    $i + 1,
                    $bill->student->name ?? '-',
                    $bill->student->nis  ?? '-',
                    $kelasLabel,
                    $bill->name,
                    $bill->type,
                    $bill->amount,
                    $bill->status,
                    $bill->created_at->format('d/m/Y'),
                    $bill->payment_date ? Carbon::parse($bill->payment_date)->format('d/m/Y') : '-',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    // =========================================================================
    // HELPER — Terbilang (angka → huruf Indonesia)
    // =========================================================================
    public function terbilang($nilai)
    {
        $nilai = abs($nilai);
        $huruf = ["", "Satu", "Dua", "Tiga", "Empat", "Lima", "Enam", "Tujuh", "Delapan", "Sembilan", "Sepuluh", "Sebelas"];
        $temp  = "";
        if ($nilai < 12) {
            $temp = " " . $huruf[$nilai];
        } elseif ($nilai < 20) {
            $temp = $this->terbilang($nilai - 10) . " Belas";
        } elseif ($nilai < 100) {
            $temp = $this->terbilang($nilai / 10) . " Puluh" . $this->terbilang($nilai % 10);
        } elseif ($nilai < 200) {
            $temp = " Seratus" . $this->terbilang($nilai - 100);
        } elseif ($nilai < 1000) {
            $temp = $this->terbilang($nilai / 100) . " Ratus" . $this->terbilang($nilai % 100);
        } elseif ($nilai < 2000) {
            $temp = " Seribu" . $this->terbilang($nilai - 1000);
        } elseif ($nilai < 1000000) {
            $temp = $this->terbilang($nilai / 1000) . " Ribu" . $this->terbilang($nilai % 1000);
        } elseif ($nilai < 1000000000) {
            $temp = $this->terbilang($nilai / 1000000) . " Juta" . $this->terbilang($nilai % 1000000);
        }
        return $temp;
    }
}
