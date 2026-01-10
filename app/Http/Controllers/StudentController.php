<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\PosOrder;
use Illuminate\Http\Request;

class StudentController extends Controller
{
    /**
     * Tampilkan Daftar Siswa (Management)
     */
    public function index(Request $request)
    {
        // 1. Ambil List Kelas untuk Dropdown Filter (Unik)
        $classes = Student::select('class_name')
                          ->distinct()
                          ->orderBy('class_name')
                          ->pluck('class_name');

        // 2. Query Data Siswa
        $query = Student::query();

        // Filter: Pencarian Nama / NIS
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('nis', 'like', '%' . $request->search . '%');
            });
        }

        // Filter: Berdasarkan Kelas
        if ($request->class_name) {
            $query->where('class_name', $request->class_name);
        }

        // Ambil data (Paginate 20 per halaman biar enteng)
        $students = $query->orderBy('class_name')
                          ->orderBy('name')
                          ->paginate(20)
                          ->withQueryString();

        return view('students.index', compact('students', 'classes'));
    }

    /**
     * Tampilkan Detail Keuangan Siswa (Kartu SPP & Hutang)
     */
    public function show($id)
    {
        $student = Student::findOrFail($id);

        // A. Ambil Riwayat Belanja / Hutang di POS
        $posTransactions = PosOrder::where('student_id', $id)
                                   ->latest()
                                   ->get();
        
        // Hitung Total Hutang POS
        $debtPos = $posTransactions->where('payment_status', 'UNPAID')->sum('total_amount');

        // B. TODO: Ambil Data SPP (Nanti kita integrasikan dengan Tabel SPP)
        // $sppData = ...
        
        return view('students.show', compact('student', 'posTransactions', 'debtPos'));
    }
}