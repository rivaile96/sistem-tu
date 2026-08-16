<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\StudentBill;
use App\Models\BillItem;
use App\Models\PosBundle;
use App\Models\PosItem;
use App\Services\FinancialAuditLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BillController extends Controller
{
    /**
     * =================================================================
     * 1. MONITORING & LAPORAN (INDEX)
     * =================================================================
     * Menampilkan daftar tagihan, filter (termasuk tanggal), dan ringkasan.
     */
    public function index(Request $request)
    {
        // Data untuk Dropdown Filter
        $classes = Student::select('class_name')->distinct()->orderBy('class_name')->pluck('class_name');
        $types = StudentBill::select('type')->distinct()->pluck('type');

        // Query Dasar
        $query = StudentBill::with('student')->latest();

        // --- FILTERING ---
        
        // 1. Filter Kelas
        if ($request->class_name) {
            $query->whereHas('student', fn($q) => $q->where('class_name', $request->class_name));
        }
        // 2. Filter Status
        if ($request->status) {
            $query->where('status', $request->status);
        }
        // 3. Filter Tipe
        if ($request->type) {
            $query->where('type', $request->type);
        }
        // 4. Filter Search (Nama Siswa / Tagihan)
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhereHas('student', fn($sq) => $sq->where('name', 'like', '%' . $request->search . '%'));
            });
        }
        // 5. Filter Tanggal
        if ($request->start_date && $request->end_date) {
            $query->whereBetween('created_at', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59'
            ]);
        }

        // Hitung Summary (Clone query agar angka summary sesuai filter yang aktif)
        $totalTagihan    = (clone $query)->sum('amount');
        $totalSudahBayar = (clone $query)->where('status', 'PAID')->sum('amount');
        $totalTunggakan  = (clone $query)->where('status', 'UNPAID')->sum('amount');

        // Pagination
        $bills = $query->paginate(20)->withQueryString();

        return view('bills.index', compact(
            'bills', 'classes', 'types',
            'totalTagihan', 'totalSudahBayar', 'totalTunggakan'
        ));
    }

    /**
     * =================================================================
     * 2. HALAMAN GENERATOR TAGIHAN (CREATE)
     * =================================================================
     */
    public function create()
    {
        $students = Student::where('status', 'active')->orderBy('class_name')->orderBy('name')->get();
        $classes = Student::select('class_name')->distinct()->orderBy('class_name')->pluck('class_name');
        $bundles = PosBundle::where('is_active', true)->get();

        return view('bills.create', compact('students', 'bundles', 'classes'));
    }

    /**
     * =================================================================
     * 3. PROSES SIMPAN (STORE)
     * =================================================================
     */
    public function store(Request $request)
    {
        // Phase 3.3 — hardened bill creation validation.
        // All financial amounts must be > 0. Items must have at least one entry.
        $allowedTypes = ['SPP', 'DAFTAR_ULANG', 'LAINNYA'];

        $rules = [
            'target_type' => 'required|in:student,class,all',
            'student_id'  => 'required_if:target_type,student|integer',
            'class_name'  => 'required_if:target_type,class|string',
            // Phase 3.3: type must be one of the known values — not just any string.
            'type'        => 'required|in:' . implode(',', $allowedTypes),
        ];

        if ($request->type == 'SPP') {
            $rules['spp_month']  = 'required|integer|min:1|max:12';
            $rules['spp_year']   = 'required|integer|min:2020|max:2099';
            // Phase 3.3: min:1 not min:0 — zero-amount SPP bill is not valid.
            $rules['spp_amount'] = 'required|numeric|min:1';
        } else {
            $rules['name']         = 'required|string|max:255';
            // Phase 3.3: require at least 1 item, validate each element.
            $rules['item_names']   = 'required|array|min:1';
            $rules['item_names.*'] = 'required|string|max:255';
            // Phase 3.3: price and qty must be positive — prevents zero/negative bills.
            $rules['item_prices']   = 'required|array|min:1';
            $rules['item_prices.*'] = 'required|numeric|min:0.01';
            $rules['item_qtys']     = 'required|array|min:1';
            $rules['item_qtys.*']   = 'required|integer|min:1';
        }

        $request->validate($rules);

        try {
            DB::beginTransaction();

            // Cari Target Siswa
            $studentsToBill = collect([]);
            if ($request->target_type == 'student') {
                $studentsToBill = Student::where('id', $request->student_id)->get();
            } elseif ($request->target_type == 'class') {
                $studentsToBill = Student::where('class_name', $request->class_name)->where('status', 'active')->get();
            } elseif ($request->target_type == 'all') {
                $studentsToBill = Student::where('status', 'active')->get();
            }

            if ($studentsToBill->isEmpty()) {
                return back()->with('error', 'Tidak ada siswa yang ditemukan.');
            }

            $count = 0;
            $bulanIndo = [1=>'Januari', 2=>'Februari', 3=>'Maret', 4=>'April', 5=>'Mei', 6=>'Juni', 7=>'Juli', 8=>'Agustus', 9=>'September', 10=>'Oktober', 11=>'November', 12=>'Desember'];

            foreach ($studentsToBill as $student) {

                // Logic Penamaan & Jatuh Tempo
                if ($request->type == 'SPP') {
                    $billName    = "SPP " . $bulanIndo[$request->spp_month] . " " . $request->spp_year;
                    $totalAmount = $request->spp_amount;
                    $billMonth   = $request->spp_month;
                    $billYear    = $request->spp_year;

                    try {
                        $dueDate = Carbon::createFromDate($billYear, $billMonth, 10);
                    } catch (\Exception $e) {
                        $dueDate = now();
                    }

                    $exists = StudentBill::where('student_id', $student->id)
                                         ->where('type', 'SPP')
                                         ->where('bill_month', $billMonth)
                                         ->where('bill_year', $billYear)
                                         ->exists();
                } else {
                    $billName    = $request->name;
                    $totalAmount = 0;
                    foreach ($request->item_prices as $idx => $price) {
                        $totalAmount += ($price * $request->item_qtys[$idx]);
                    }
                    $billMonth = null;
                    $billYear  = null;
                    $dueDate   = null;

                    $exists = StudentBill::where('student_id', $student->id)
                                         ->where('name', $billName)
                                         ->where('type', $request->type)
                                         ->where('status', 'UNPAID')
                                         ->exists();
                }

                if (!$exists) {
                    // Create Bill Header
                    // Phase 3.5: record created_by for audit trail.
                    $bill = StudentBill::create([
                        'student_id' => $student->id,
                        'name'       => $billName,
                        'type'       => $request->type,
                        'amount'     => $totalAmount,
                        'status'     => 'UNPAID',
                        'bill_month' => $billMonth,
                        'bill_year'  => $billYear,
                        'due_date'   => $dueDate,
                        'created_by' => Auth::id(),
                    ]);

                    // Create Bill Items
                    if ($request->type == 'SPP') {
                        BillItem::create([
                            'student_bill_id' => $bill->id,
                            'item_name'       => $billName,
                            'quantity'        => 1,
                            'price'           => $totalAmount,
                            'subtotal'        => $totalAmount
                        ]);
                    } else {
                        foreach ($request->item_names as $index => $itemName) {
                            $bundleId = $request->item_bundle_ids[$index] ?? null;
                            BillItem::create([
                                'student_bill_id' => $bill->id,
                                'pos_bundle_id'   => $bundleId,
                                'item_name'       => $itemName,
                                'quantity'        => $request->item_qtys[$index],
                                'price'           => $request->item_prices[$index],
                                'subtotal'        => $request->item_prices[$index] * $request->item_qtys[$index],
                            ]);
                        }
                    }
                    // Phase 3.5: log BILL_CREATED inside the same transaction.
                    FinancialAuditLogger::billCreated($bill, 'WEB', Auth::id(), $request);
                    $count++;
                }
            }

            DB::commit();
            return redirect()->route('bills.index')->with('success', "Sukses! Tagihan berhasil dibuat untuk {$count} siswa.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal membuat tagihan: ' . $e->getMessage());
        }
    }

    /**
     * =================================================================
     * 4. PROSES BAYAR (PAY) — CASH / MANUAL
     * =================================================================
     *
     * Phase 2.3 changes:
     *   - paid_at = now()     (canonical payment timestamp)
     *   - confirmed_by = Auth::id()  (audit trail: which TU confirmed)
     *   - removed manual 'updated_at' override (Eloquent handles this)
     *   - stock pre-validation BEFORE any mutation (fail-fast)
     *   - all mutations inside ONE DB::transaction()
     *
     * Timezone: now() uses app timezone Asia/Jakarta (config/app.php).
     * No manual offset needed.
     */
    public function pay($id)
    {
        try {
            $bill = StudentBill::with('items')->findOrFail($id);

            // Guard: already paid — do not overwrite any payment fields.
            if ($bill->status == 'PAID') {
                return back()->with('error', 'Tagihan ini sudah lunas sebelumnya!');
            }

            DB::beginTransaction();

            // ── PHASE 2.3: Stock Pre-Validation ──────────────────────────
            // Validate ALL required stock deductions BEFORE mutating anything.
            // If any product has insufficient stock, the entire transaction
            // is aborted cleanly — bill stays UNPAID, no stock is touched.
            if ($bill->items && $bill->items->count() > 0) {
                foreach ($bill->items as $billItem) {
                    if ($billItem->pos_bundle_id) {
                        $bundle = PosBundle::with('items')->find($billItem->pos_bundle_id);
                        if ($bundle) {
                            foreach ($bundle->items as $bundleItem) {
                                $product = PosItem::find($bundleItem->pos_item_id);
                                if ($product) {
                                    $qtyRequired = $billItem->quantity * $bundleItem->quantity;
                                    if ($product->stock < $qtyRequired) {
                                        DB::rollBack();
                                        return back()->with('error',
                                            "Stok barang '{$product->name}' tidak mencukupi. " .
                                            "Dibutuhkan: {$qtyRequired}, tersedia: {$product->stock}."
                                        );
                                    }
                                }
                            }
                        }
                    }
                }
            }
            // ── End Stock Pre-Validation ──────────────────────────────────

            // ── PHASE 2.3: Bill Update ────────────────────────────────────
            // Set all payment audit fields in one atomic update.
            // confirmed_by: the authenticated admin/TU user confirming payment.
            // paid_at:      exact timestamp of this confirmation (Asia/Jakarta).
            $bill->update([
                'status'         => 'PAID',
                'paid_at'        => now(),
                'payment_method' => 'CASH',
                'confirmed_by'   => Auth::id(),
            ]);
            // ── End Bill Update ───────────────────────────────────────────

            // Phase 3.5: log PAYMENT_CONFIRMED inside the same transaction.
            $bill->refresh();
            FinancialAuditLogger::paymentConfirmed($bill, 'WEB', Auth::id(), request());

            // ── Stock Deduction ───────────────────────────────────────────
            // Pre-validation passed — safe to decrement.
            if ($bill->items && $bill->items->count() > 0) {
                foreach ($bill->items as $billItem) {
                    if ($billItem->pos_bundle_id) {
                        $bundle = PosBundle::with('items')->find($billItem->pos_bundle_id);
                        if ($bundle) {
                            foreach ($bundle->items as $bundleItem) {
                                $product = PosItem::find($bundleItem->pos_item_id);
                                if ($product) {
                                    $qtyOut = $billItem->quantity * $bundleItem->quantity;
                                    $product->decrement('stock', $qtyOut);
                                }
                            }
                        }
                    }
                }
            }
            // ── End Stock Deduction ───────────────────────────────────────

            DB::commit();
            return back()->with('success', "Pembayaran LUNAS (CASH) diterima. Stok barang (jika ada) otomatis dipotong.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memproses pembayaran: ' . $e->getMessage());
        }
    }

    /**
     * =================================================================
     * 5. HAPUS TAGIHAN (DESTROY)
     * =================================================================
     */
    public function destroy($id)
    {
        try {
            $bill = StudentBill::findOrFail($id);
            if ($bill->status == 'PAID') {
                return back()->with('error', 'Dilarang menghapus tagihan yang sudah LUNAS.');
            }

            DB::beginTransaction();

            // Phase 3.5: capture snapshot BEFORE deletion, then log atomically.
            FinancialAuditLogger::billDeleted($bill, 'WEB', request());
            $bill->delete();

            DB::commit();
            return back()->with('success', 'Tagihan berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menghapus: ' . $e->getMessage());
        }
    }

    /**
     * =================================================================
     * 6. CETAK KWITANSI (PRINT)
     * =================================================================
     */
    public function print($id)
    {
        $bill = StudentBill::with(['student', 'items'])->findOrFail($id);

        if ($bill->status == 'UNPAID') {
            return back()->with('error', 'Tagihan belum lunas, tidak bisa cetak kwitansi!');
        }

        $school    = DB::table('school_settings')->pluck('value', 'key');
        $terbilang = $this->terbilang($bill->amount) . ' Rupiah';

        return view('bills.print', compact('bill', 'terbilang', 'school'));
    }

    /**
     * =================================================================
     * 7. EXPORT CSV (INDEX FILTERED)
     * =================================================================
     */
    public function export(Request $request)
    {
        $query = StudentBill::with('student')->latest();

        if ($request->class_name) $query->whereHas('student', fn($q) => $q->where('class_name', $request->class_name));
        if ($request->status)     $query->where('status', $request->status);
        if ($request->type)       $query->where('type', $request->type);
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhereHas('student', fn($sq) => $sq->where('name', 'like', '%' . $request->search . '%'));
            });
        }
        if ($request->start_date && $request->end_date) {
            $query->whereBetween('created_at', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59'
            ]);
        }

        $bills    = $query->get();
        $filename = 'Laporan-Tagihan-' . date('Y-m-d-His') . '.csv';

        return response()->streamDownload(function () use ($bills) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['No', 'Nama Siswa', 'NIS', 'Kelas', 'Jenis', 'Keterangan', 'Metode Bayar', 'Nominal (Rp)', 'Status', 'Tgl Jatuh Tempo', 'Tgl Update']);

            foreach ($bills as $k => $bill) {
                fputcsv($handle, [
                    $k + 1,
                    $bill->student->name,
                    $bill->student->nis,
                    $bill->student->class_name,
                    $bill->type,
                    $bill->name,
                    $bill->payment_method ?? '-',
                    $bill->amount,
                    $bill->status == 'PAID' ? 'LUNAS' : 'BELUM',
                    $bill->due_date ? Carbon::parse($bill->due_date)->format('d/m/Y') : '-',
                    $bill->updated_at->format('d/m/Y H:i')
                ]);
            }
            fclose($handle);
        }, $filename);
    }

    private function terbilang($nilai)
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
