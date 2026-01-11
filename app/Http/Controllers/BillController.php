<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\StudentBill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BillController extends Controller
{
    /**
     * 1. Halaman Monitoring Tagihan (Laporan Visual)
     */
    public function index(Request $request)
    {
        // Data untuk Dropdown Filter
        $classes = Student::select('class_name')->distinct()->orderBy('class_name')->pluck('class_name');
        $types = StudentBill::select('type')->distinct()->pluck('type');

        // Panggil Logic Filter Dasar
        $query = $this->getFilteredQuery($request);

        // --- [FIXED LOGIC SUMMARY] ---
        // Kita wajib pakai (clone $query) di setiap baris perhitungan
        // Agar filter 'PAID' tidak terbawa saat menghitung 'UNPAID'
        
        $totalTagihan    = (clone $query)->sum('amount');
        $totalSudahBayar = (clone $query)->where('status', 'PAID')->sum('amount');
        $totalTunggakan  = (clone $query)->where('status', 'UNPAID')->sum('amount');

        // Ambil Data Tabel (Paginate 20 baris)
        $bills = $query->paginate(20)->withQueryString();

        return view('bills.index', compact(
            'bills', 'classes', 'types', 
            'totalTagihan', 'totalSudahBayar', 'totalTunggakan'
        ));
    }

    /**
     * 2. Form Generator Tagihan Massal
     */
    public function create()
    {
        $classes = Student::select('class_name')->distinct()->orderBy('class_name')->pluck('class_name');
        return view('bills.create', compact('classes'));
    }

    /**
     * 3. Proses Eksekusi Generate Tagihan
     */
    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required',
            'name' => 'required',
            'amount' => 'required|numeric|min:0',
            'target_class' => 'required',
        ]);

        try {
            DB::beginTransaction();

            $query = Student::where('status', 'active');
            if ($request->target_class != 'ALL') {
                $query->where('class_name', $request->target_class);
            }
            $students = $query->get();
            
            if ($students->isEmpty()) {
                return back()->with('error', 'Tidak ada siswa aktif di kelas yang dipilih.');
            }

            $count = 0;
            foreach ($students as $student) {
                $exists = StudentBill::where('student_id', $student->id)
                                     ->where('name', $request->name)
                                     ->where('type', $request->type)
                                     ->exists();

                if (!$exists) {
                    StudentBill::create([
                        'student_id' => $student->id,
                        'type' => $request->type,
                        'name' => $request->name,
                        'amount' => $request->amount,
                        'status' => 'UNPAID',
                    ]);
                    $count++;
                }
            }
            DB::commit();
            return back()->with('success', "Sukses generate tagihan untuk {$count} siswa!");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal generate: ' . $e->getMessage());
        }
    }

    /**
     * 4. Proses Bayar Manual
     */
    public function pay($id)
    {
        try {
            $bill = StudentBill::findOrFail($id);
            if ($bill->status == 'PAID') {
                return back()->with('error', 'Tagihan ini sudah lunas sebelumnya!');
            }
            $bill->update(['status' => 'PAID']);
            return back()->with('success', "Pembayaran berhasil diterima!");
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memproses pembayaran.');
        }
    }

    /**
     * 5. Cetak Kwitansi PDF
     */
    public function print($id)
    {
        $bill = StudentBill::with('student')->findOrFail($id);
        if ($bill->status == 'UNPAID') {
            return back()->with('error', 'Tagihan belum lunas, tidak bisa cetak kwitansi!');
        }
        $terbilang = $this->terbilang($bill->amount) . ' Rupiah';
        return view('bills.print', compact('bill', 'terbilang'));
    }

    /**
     * 6. Export Excel / CSV
     */
    public function export(Request $request)
    {
        $query = $this->getFilteredQuery($request);
        $bills = $query->get();

        $filename = 'Laporan-Tagihan-' . date('Y-m-d-His') . '.csv';

        return response()->streamDownload(function () use ($bills) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['No', 'Nama Siswa', 'NIS', 'Kelas', 'Jenis Tagihan', 'Keterangan', 'Nominal (Rp)', 'Status', 'Tanggal Update']);

            foreach ($bills as $k => $bill) {
                fputcsv($handle, [
                    $k + 1,
                    $bill->student->name,
                    $bill->student->nis,
                    $bill->student->class_name,
                    $bill->type,
                    $bill->name,
                    $bill->amount,
                    $bill->status == 'PAID' ? 'LUNAS' : 'BELUM LUNAS',
                    $bill->updated_at->format('d/m/Y H:i')
                ]);
            }
            fclose($handle);
        }, $filename);
    }

    // ==========================================
    // PRIVATE HELPER FUNCTIONS
    // ==========================================

    private function getFilteredQuery(Request $request)
    {
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
        return $query;
    }

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