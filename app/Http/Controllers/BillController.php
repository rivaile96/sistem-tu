<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\StudentBill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BillController extends Controller
{
    // 1. Tampilkan Form Generator
    public function create()
    {
        // Ambil daftar kelas biar Admin tinggal pilih
        $classes = Student::select('class_name')
                          ->distinct()
                          ->orderBy('class_name')
                          ->pluck('class_name');

        return view('bills.create', compact('classes'));
    }

    // 2. Proses Generate Tagihan Massal
    public function store(Request $request)
    {
        $request->validate([
            'type' => 'required',        // SPP, GEDUNG, SERAGAM
            'name' => 'required',        // "SPP Februari 2026"
            'amount' => 'required|numeric|min:0',
            'target_class' => 'required', // Kelas X RPL 1 atau 'ALL'
        ]);

        try {
            DB::beginTransaction();

            // A. Cari Target Siswa
            $query = Student::where('status', 'active');

            // Kalau bukan 'SEMUA KELAS', filter berdasarkan kelas
            if ($request->target_class != 'ALL') {
                $query->where('class_name', $request->target_class);
            }

            $students = $query->get();
            
            if ($students->isEmpty()) {
                return back()->with('error', 'Tidak ada siswa aktif di kelas yang dipilih.');
            }

            // B. Looping Buat Tagihan
            $count = 0;
            foreach ($students as $student) {
                // Cek duplikat biar gak double tagihan (Optional, tapi aman)
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
                        'status' => 'UNPAID', // Default Belum Lunas
                    ]);
                    $count++;
                }
            }

            DB::commit();

            return back()->with('success', "Berhasil membuat tagihan untuk {$count} siswa!");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal generate: ' . $e->getMessage());
        }
    }
}