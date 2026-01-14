<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\StudentBill;
use App\Models\BillItem;
use App\Models\PosBundle;
use App\Models\PosItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BillController extends Controller
{
    /**
     * =================================================================
     * 1. MONITORING & LAPORAN (INDEX)
     * =================================================================
     * Menampilkan daftar tagihan, filter, dan ringkasan keuangan.
     */
    public function index(Request $request)
    {
        // Data untuk Dropdown Filter
        $classes = Student::select('class_name')->distinct()->orderBy('class_name')->pluck('class_name');
        $types = StudentBill::select('type')->distinct()->pluck('type');

        // Query Dasar
        $query = StudentBill::with('student')->latest();

        // Terapkan Filter
        if ($request->class_name) {
            $query->whereHas('student', fn($q) => $q->where('class_name', $request->class_name));
        }
        if ($request->status) {
            $query->where('status', $request->status);
        }
        if ($request->type) {
            $query->where('type', $request->type);
        }
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhereHas('student', fn($sq) => $sq->where('name', 'like', '%' . $request->search . '%'));
            });
        }

        // Hitung Summary (Clone query agar filter tidak bentrok)
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
     * Menyiapkan data siswa, kelas, dan paket POS untuk form input.
     */
    public function create()
    {
        // Ambil siswa aktif
        $students = Student::where('status', 'active')->orderBy('class_name')->orderBy('name')->get();
        
        // Ambil daftar kelas unik
        $classes = Student::select('class_name')->distinct()->orderBy('class_name')->pluck('class_name');
        
        // Ambil paket bundling aktif dari POS
        $bundles = PosBundle::where('is_active', true)->get(); 
        
        return view('bills.create', compact('students', 'bundles', 'classes'));
    }

    /**
     * =================================================================
     * 3. PROSES SIMPAN (STORE) - LOGIC INTI
     * =================================================================
     * Menangani SPP (dengan tanggal jatuh tempo) dan Tagihan Barang (dengan item).
     */
    public function store(Request $request)
    {
        // A. VALIDASI INPUT (DINAMIS BERDASARKAN TIPE)
        $rules = [
            'target_type' => 'required|in:student,class,all',
            'student_id' => 'required_if:target_type,student',
            'class_name' => 'required_if:target_type,class',
            'type' => 'required',
        ];

        if ($request->type == 'SPP') {
            // Jika SPP: Wajib isi Bulan, Tahun, dan Nominal Manual
            $rules['spp_month'] = 'required|integer|min:1|max:12';
            $rules['spp_year'] = 'required|integer|min:2020';
            $rules['spp_amount'] = 'required|numeric|min:0';
        } else {
            // Jika Reguler: Wajib isi Nama Tagihan dan Rincian Item
            $rules['name'] = 'required';
            $rules['item_names'] = 'required|array';
            $rules['item_prices'] = 'required|array';
            $rules['item_qtys'] = 'required|array';
        }

        $request->validate($rules);

        try {
            DB::beginTransaction();

            // B. RESOLUSI TARGET SISWA
            $studentsToBill = collect([]);

            if ($request->target_type == 'student') {
                $studentsToBill = Student::where('id', $request->student_id)->get();
            } elseif ($request->target_type == 'class') {
                $studentsToBill = Student::where('class_name', $request->class_name)->where('status', 'active')->get();
            } elseif ($request->target_type == 'all') {
                $studentsToBill = Student::where('status', 'active')->get();
            }

            if ($studentsToBill->isEmpty()) {
                return back()->with('error', 'Tidak ada siswa yang ditemukan untuk target tersebut.');
            }

            // C. PROSES LOOPING GENERATE TAGIHAN
            $count = 0;
            $bulanIndo = [
                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 
                5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus', 
                9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
            ];

            foreach ($studentsToBill as $student) {
                
                // --- SKENARIO 1: TAGIHAN TIPE SPP ---
                if ($request->type == 'SPP') {
                    // Generate Nama Otomatis: "SPP Agustus 2026"
                    $billName = "SPP " . $bulanIndo[$request->spp_month] . " " . $request->spp_year;
                    $totalAmount = $request->spp_amount;
                    
                    $billMonth = $request->spp_month;
                    $billYear = $request->spp_year;

                    // Setting Tanggal Jatuh Tempo (Due Date)
                    // Diset otomatis tanggal 10 pada bulan & tahun tagihan tersebut
                    try {
                        $dueDate = Carbon::createFromDate($billYear, $billMonth, 10);
                    } catch (\Exception $e) {
                        // Fallback jika tanggal tidak valid, set ke hari ini
                        $dueDate = now();
                    }

                    // Cek Duplikasi SPP (Mencegah double billing di bulan yang sama)
                    $exists = StudentBill::where('student_id', $student->id)
                                         ->where('type', 'SPP')
                                         ->where('bill_month', $billMonth)
                                         ->where('bill_year', $billYear)
                                         ->exists();
                } 
                // --- SKENARIO 2: TAGIHAN TIPE REGULER (POS/Manual) ---
                else {
                    $billName = $request->name;
                    
                    // Hitung Total dari Rincian Item
                    $totalAmount = 0;
                    foreach ($request->item_prices as $idx => $price) {
                        $totalAmount += ($price * $request->item_qtys[$idx]);
                    }
                    
                    $billMonth = null;
                    $billYear = null;
                    $dueDate = null; // Tagihan reguler tidak punya jatuh tempo spesifik (opsional)

                    // Cek Duplikasi Nama (Mencegah double input nama sama persis yang belum lunas)
                    $exists = StudentBill::where('student_id', $student->id)
                                         ->where('name', $billName)
                                         ->where('type', $request->type)
                                         ->where('status', 'UNPAID')
                                         ->exists();
                }

                // D. EKSEKUSI INSERT DATABASE
                if (!$exists) {
                    // 1. Buat Header Tagihan
                    $bill = StudentBill::create([
                        'student_id' => $student->id,
                        'name' => $billName,
                        'type' => $request->type,
                        'amount' => $totalAmount,
                        'status' => 'UNPAID',
                        'bill_month' => $billMonth, // Terisi jika SPP
                        'bill_year' => $billYear,   // Terisi jika SPP
                        'due_date' => $dueDate,     // Terisi jika SPP (Tgl 10)
                    ]);

                    // 2. Buat Rincian Item (BillItem)
                    if ($request->type == 'SPP') {
                        // Jika SPP, buat 1 item dummy agar struktur data konsisten
                        BillItem::create([
                            'student_bill_id' => $bill->id,
                            'item_name' => $billName,
                            'quantity' => 1,
                            'price' => $totalAmount,
                            'subtotal' => $totalAmount
                        ]);
                    } else {
                        // Jika Reguler, ambil dari input form dinamis
                        foreach ($request->item_names as $index => $itemName) {
                            $bundleId = $request->item_bundle_ids[$index] ?? null;
                            
                            BillItem::create([
                                'student_bill_id' => $bill->id,
                                'pos_bundle_id' => $bundleId, // Terisi ID Paket POS jika ada
                                'item_name' => $itemName,
                                'quantity' => $request->item_qtys[$index],
                                'price' => $request->item_prices[$index],
                                'subtotal' => $request->item_prices[$index] * $request->item_qtys[$index],
                            ]);
                        }
                    }
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
     * 4. PROSES BAYAR MANUAL (PAY) & INTEGRASI STOK
     * =================================================================
     * Mengubah status jadi PAID dan memotong stok jika tagihan berisi barang POS.
     */
    public function pay($id)
    {
        try {
            $bill = StudentBill::with('items')->findOrFail($id);

            if ($bill->status == 'PAID') {
                return back()->with('error', 'Tagihan ini sudah lunas sebelumnya!');
            }

            DB::beginTransaction();

            // 1. Update Status Tagihan
            $bill->update([
                'status' => 'PAID',
                'payment_method' => 'CASH', 
                'updated_at' => now(),
            ]);

            // 2. Logic Potong Stok (Inventory Cut)
            // Mengecek apakah ada item yang terhubung dengan Paket POS
            if ($bill->items && $bill->items->count() > 0) {
                foreach ($bill->items as $billItem) {
                    
                    // Cek jika item ini adalah Paket Bundling
                    if ($billItem->pos_bundle_id) {
                        $bundle = PosBundle::with('items')->find($billItem->pos_bundle_id);
                        
                        if ($bundle) {
                            // Loop isi paket (misal: Seragam, Topi, Dasi)
                            foreach ($bundle->items as $bundleItem) {
                                $product = PosItem::find($bundleItem->pos_item_id);
                                
                                if ($product) {
                                    // Kurangi Stok: Qty Beli x Qty per Paket
                                    $qtyOut = $billItem->quantity * $bundleItem->quantity;
                                    $product->decrement('stock', $qtyOut);
                                }
                            }
                        }
                    }
                }
            }

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
     * Menghapus tagihan yang salah input, tapi melarang hapus yang sudah lunas.
     */
    public function destroy($id)
    {
        try {
            $bill = StudentBill::findOrFail($id);
            
            if ($bill->status == 'PAID') {
                return back()->with('error', 'Dilarang menghapus tagihan yang sudah LUNAS demi keamanan arsip keuangan.');
            }

            $bill->delete(); // Otomatis hapus rincian item (Cascade)
            return back()->with('success', 'Tagihan berhasil dihapus.');

        } catch (\Exception $e) {
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
        
        $terbilang = $this->terbilang($bill->amount) . ' Rupiah';
        return view('bills.print', compact('bill', 'terbilang'));
    }

    /**
     * =================================================================
     * 7. EXPORT CSV (FULL LOGIC)
     * =================================================================
     * Mengunduh laporan tagihan sesuai filter yang sedang aktif.
     */
    public function export(Request $request)
    {
        // Gunakan filter yang sama dengan index
        $query = StudentBill::with('student')->latest();

        if ($request->class_name) {
            $query->whereHas('student', fn($q) => $q->where('class_name', $request->class_name));
        }
        if ($request->status) {
            $query->where('status', $request->status);
        }
        if ($request->type) {
            $query->where('type', $request->type);
        }
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhereHas('student', fn($sq) => $sq->where('name', 'like', '%' . $request->search . '%'));
            });
        }

        $bills = $query->get();
        $filename = 'Laporan-Tagihan-' . date('Y-m-d-His') . '.csv';

        return response()->streamDownload(function () use ($bills) {
            $handle = fopen('php://output', 'w');
            
            // Header CSV
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

    /**
     * =================================================================
     * 8. HELPER PRIVATE
     * =================================================================
     */
    private function terbilang($nilai) {
        $nilai = abs($nilai);
        $huruf = array("", "Satu", "Dua", "Tiga", "Empat", "Lima", "Enam", "Tujuh", "Delapan", "Sembilan", "Sepuluh", "Sebelas");
        $temp = "";
        if ($nilai < 12) {
            $temp = " ". $huruf[$nilai];
        } else if ($nilai <20) {
            $temp = $this->terbilang($nilai - 10). " Belas";
        } else if ($nilai < 100) {
            $temp = $this->terbilang($nilai/10)." Puluh". $this->terbilang($nilai % 10);
        } else if ($nilai < 200) {
            $temp = " Seratus" . $this->terbilang($nilai - 100);
        } else if ($nilai < 1000) {
            $temp = $this->terbilang($nilai/100) . " Ratus" . $this->terbilang($nilai % 100);
        } else if ($nilai < 2000) {
            $temp = " Seribu" . $this->terbilang($nilai - 1000);
        } else if ($nilai < 1000000) {
            $temp = $this->terbilang($nilai/1000) . " Ribu" . $this->terbilang($nilai % 1000);
        } else if ($nilai < 1000000000) {
            $temp = $this->terbilang($nilai/1000000) . " Juta" . $this->terbilang($nilai % 1000000);
        }
        return $temp;
    }
}