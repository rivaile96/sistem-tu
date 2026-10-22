<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\PosOrder;
use App\Services\FinanceAuditService;
use Carbon\Carbon;
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

    /**
     * POST /students/{id}/enrolled-at
     * Admin mengisi tanggal enrollment resmi siswa.
     *
     * PENTING:
     * - Hanya admin yang boleh mengisi field ini (dikontrol via route middleware role:admin).
     * - Jangan mengisi dengan created_at atau synced_at — semanticnya berbeda.
     * - enrolled_at = tanggal siswa RESMI menjadi student aktif di TU.
     * - NULL valid: siswa provisioned tapi belum official enrolled.
     * - Perubahan selalu dicatat di audit_logs (admin_id, old_value, new_value, ip).
     */
    public function updateEnrolledAt(Request $request, int $id)
    {
        $student = Student::findOrFail($id);

        $request->validate([
            // Nullable: admin boleh mengosongkan untuk reset enrollment date
            'enrolled_at' => 'nullable|date|before_or_equal:today',
        ], [
            'enrolled_at.before_or_equal' => 'Tanggal enrollment tidak boleh di masa depan.',
            'enrolled_at.date'            => 'Format tanggal tidak valid.',
        ]);

        // Snapshot sebelum perubahan untuk audit log
        $oldData = ['enrolled_at' => $student->enrolled_at?->toDateString()];

        $student->update([
            'enrolled_at' => $request->enrolled_at ?: null,
        ]);

        FinanceAuditService::logUpdate($student, $oldData, $request);

        $msg = $request->enrolled_at
            ? 'Enrollment date berhasil disimpan: ' . Carbon::parse($request->enrolled_at)->format('d M Y')
            : 'Enrollment date dikosongkan.';

        return back()->with('success', $msg);
    }
}
