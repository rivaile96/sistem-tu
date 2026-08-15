<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\StudentStatusLog;
use App\Models\PosOrder;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use League\Csv\Reader;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $classes = Student::select('class_name')
            ->distinct()
            ->orderBy('class_name')
            ->pluck('class_name');

        $query = Student::query();

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('nis', 'like', '%' . $request->search . '%')
                  ->orWhere('nisn', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->class_name) {
            $query->where('class_name', $request->class_name);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->gender) {
            $query->where('gender', $request->gender);
        }

        $students = $query->orderBy('class_name')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $stats = [
            'total'         => Student::count(),
            'aktif'         => Student::where('status', 'active')->count(),
            'tidak_aktif'   => Student::whereNotIn('status', ['active', 'calon_siswa'])->count(),
            'calon'         => Student::where('status', 'calon_siswa')->count(),
        ];

        return view('students.index', compact('students', 'classes', 'stats'));
    }

    public function create()
    {
        $statuses = Student::STATUSES;
        return view('students.create', compact('statuses'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nis'         => 'required|string|max:20|unique:students,nis',
            'nisn'        => 'nullable|string|max:20|unique:students,nisn',
            'name'        => 'required|string|max:255',
            'gender'      => 'nullable|in:L,P',
            'class_name'  => 'required|string|max:50',
            'birth_place' => 'nullable|string|max:100',
            'birth_date'  => 'nullable|date',
            'address'     => 'nullable|string',
            'agama'       => 'nullable|string|max:20',
            'tahun_masuk' => 'nullable|digits:4',
            'parent_phone'=> 'nullable|string|max:20',
            'status'      => 'nullable|in:' . implode(',', array_keys(Student::STATUSES)),
        ]);

        $validated['status'] = $validated['status'] ?? 'active';

        $student = Student::create($validated);

        StudentStatusLog::create([
            'student_id'  => $student->id,
            'status_lama' => null,
            'status_baru' => $student->status,
            'catatan'     => 'Data siswa dibuat',
            'diubah_oleh' => Auth::id(),
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Siswa {$student->name} berhasil ditambahkan.",
            ]);
        }

        return redirect()->route('students.index')
            ->with('success', "Siswa {$student->name} berhasil ditambahkan.");
    }

    public function show($id)
    {
        $student = Student::findOrFail($id);

        $posTransactions = PosOrder::where('student_id', $id)->latest()->get();
        $debtPos = $posTransactions->where('payment_status', 'UNPAID')->sum('total_amount');

        $statusLogs = StudentStatusLog::where('student_id', $id)
            ->with('diubahOleh')
            ->latest()
            ->get();

        return view('students.show', compact('student', 'posTransactions', 'debtPos', 'statusLogs'));
    }

    public function edit($id)
    {
        $student  = Student::findOrFail($id);
        $statuses = Student::STATUSES;

        if (request()->wantsJson()) {
            return response()->json(array_merge($student->toArray(), ['statuses' => $statuses]));
        }

        return view('students.edit', compact('student', 'statuses'));
    }

    public function update(Request $request, $id)
    {
        $student = Student::findOrFail($id);

        $validated = $request->validate([
            'nis'         => 'required|string|max:20|unique:students,nis,' . $id,
            'nisn'        => 'nullable|string|max:20|unique:students,nisn,' . $id,
            'name'        => 'required|string|max:255',
            'gender'      => 'nullable|in:L,P',
            'class_name'  => 'required|string|max:50',
            'birth_place' => 'nullable|string|max:100',
            'birth_date'  => 'nullable|date',
            'address'     => 'nullable|string',
            'agama'       => 'nullable|string|max:20',
            'tahun_masuk' => 'nullable|digits:4',
            'parent_phone'=> 'nullable|string|max:20',
        ]);

        $student->update($validated);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Data siswa {$student->name} berhasil diperbarui.",
            ]);
        }

        return redirect()->route('students.show', $student->id)
            ->with('success', "Data siswa {$student->name} berhasil diperbarui.");
    }

    public function destroy($id)
    {
        $student = Student::findOrFail($id);
        $nama    = $student->name;
        $student->delete();

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Data siswa {$nama} berhasil dihapus.",
            ]);
        }

        return redirect()->route('students.index')
            ->with('success', "Data siswa {$nama} berhasil dihapus.");
    }

    public function formUbahStatus($id)
    {
        $student  = Student::findOrFail($id);
        $statuses = Student::STATUSES;
        $logs     = StudentStatusLog::where('student_id', $id)
            ->with('diubahOleh')
            ->latest()
            ->take(10)
            ->get();

        return view('students.ubah-status', compact('student', 'statuses', 'logs'));
    }

    public function ubahStatus(Request $request, $id)
    {
        $student = Student::findOrFail($id);

        $request->validate([
            'status'  => 'required|in:' . implode(',', array_keys(Student::STATUSES)),
            'catatan' => 'nullable|string|max:500',
        ]);

        $statusLama = $student->status;
        $statusBaru = $request->status;

        if ($statusLama === $statusBaru) {
            return back()->with('info', 'Status siswa tidak berubah.');
        }

        DB::transaction(function () use ($student, $statusLama, $statusBaru, $request) {
            $student->update([
                'status'             => $statusBaru,
                'status_notes'       => $request->catatan,
                'status_changed_at'  => now(),
                'status_changed_by'  => Auth::id(),
            ]);

            StudentStatusLog::create([
                'student_id'  => $student->id,
                'status_lama' => $statusLama,
                'status_baru' => $statusBaru,
                'catatan'     => $request->catatan,
                'diubah_oleh' => Auth::id(),
            ]);
        });

        $labelBaru = Student::STATUSES[$statusBaru] ?? $statusBaru;

        return redirect()->route('students.show', $student->id)
            ->with('success', "Status {$student->name} diubah menjadi {$labelBaru}.");
    }

    public function importForm()
    {
        return view('students.import');
    }

    public function importCsv(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $path = $request->file('file')->getRealPath();

        try {
            $csv = Reader::createFromPath($path, 'r');
            $csv->setHeaderOffset(0);

            $records  = $csv->getRecords();
            $inserted = 0;
            $skipped  = 0;
            $errors   = [];

            foreach ($records as $offset => $row) {
                $row = array_map('trim', $row);

                if (empty($row['nis']) || empty($row['name']) || empty($row['class_name'])) {
                    $errors[] = "Baris " . ($offset + 2) . ": NIS, Nama, atau Kelas kosong — dilewati.";
                    $skipped++;
                    continue;
                }

                if (Student::where('nis', $row['nis'])->exists()) {
                    $errors[] = "Baris " . ($offset + 2) . ": NIS {$row['nis']} sudah ada — dilewati.";
                    $skipped++;
                    continue;
                }

                $student = Student::create([
                    'nis'          => $row['nis'],
                    'nisn'         => $row['nisn'] ?? null,
                    'name'         => $row['name'],
                    'gender'       => in_array(strtoupper($row['gender'] ?? ''), ['L', 'P']) ? strtoupper($row['gender']) : null,
                    'class_name'   => $row['class_name'],
                    'birth_place'  => $row['birth_place'] ?? null,
                    'birth_date'   => !empty($row['birth_date']) ? $row['birth_date'] : null,
                    'address'      => $row['address'] ?? null,
                    'agama'        => $row['agama'] ?? null,
                    'tahun_masuk'  => $row['tahun_masuk'] ?? null,
                    'parent_phone' => $row['parent_phone'] ?? null,
                    'status'       => 'active',
                ]);

                StudentStatusLog::create([
                    'student_id'  => $student->id,
                    'status_lama' => null,
                    'status_baru' => 'active',
                    'catatan'     => 'Import CSV',
                    'diubah_oleh' => Auth::id(),
                ]);

                $inserted++;
            }

            $msg = "Import selesai: {$inserted} siswa berhasil diimport";
            if ($skipped > 0) $msg .= ", {$skipped} dilewati";

            return redirect()->route('students.index')
                ->with('success', $msg)
                ->with('import_errors', $errors);

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memproses file CSV: ' . $e->getMessage());
        }
    }

    public function downloadTemplate()
    {
        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="template-import-siswa.csv"',
        ];

        $columns = ['nis', 'nisn', 'name', 'gender', 'class_name', 'birth_place', 'birth_date', 'address', 'agama', 'tahun_masuk', 'parent_phone'];
        $example = ['2024001', '1234567890', 'Ahmad Siswa', 'L', 'X IPA 1', 'Jakarta', '2008-05-10', 'Jl. Merdeka No. 1', 'Islam', '2024', '08123456789'];

        $callback = function () use ($columns, $example) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            fputcsv($file, $example);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
