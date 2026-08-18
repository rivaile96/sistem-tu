<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
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
    // =========================================================================
    // LISTING & SEARCH
    // =========================================================================

    public function index(Request $request)
    {
        // Filter dropdown sourced from master kelas — not free-text class_name
        $kelasList = Kelas::aktif()->orderBy('tingkat')->orderBy('nama_kelas')->get();

        $query = Student::with('kelas');

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('nis', 'like', '%' . $request->search . '%')
                  ->orWhere('nisn', 'like', '%' . $request->search . '%');
            });
        }

        // Primary filter: kelas_id (canonical — class_name dropped Phase 9.3)
        if ($request->kelas_id) {
            $query->where('kelas_id', $request->kelas_id);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->gender) {
            $query->where('gender', $request->gender);
        }

        $students = $query->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $stats = [
            'total'       => Student::count(),
            'aktif'       => Student::where('status', 'active')->count(),
            'tidak_aktif' => Student::whereNotIn('status', ['active', 'calon_siswa'])->count(),
            'calon'       => Student::where('status', 'calon_siswa')->count(),
        ];

        return view('students.index', compact('students', 'kelasList', 'stats'));
    }

    // =========================================================================
    // FLOW B — DIRECT ADMINISTRATIVE ENTRY
    // Purpose : TU adds an already-enrolled student or migrates from old system.
    // Result  : status = active immediately, NIS + kelas_id both required.
    // NOT for new student registration — use PPDBController for that (Flow A).
    // =========================================================================

    public function create()
    {
        $kelasList = Kelas::aktif()->orderBy('tingkat')->orderBy('nama_kelas')->get();
        return view('students.create', compact('kelasList'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nis'         => 'required|string|max:20|unique:students,nis',
            'nisn'        => 'nullable|string|max:20|unique:students,nisn',
            'name'        => 'required|string|max:255',
            'gender'      => 'required|in:L,P',
            'kelas_id'    => 'required|exists:kelas,id',
            'birth_place' => 'nullable|string|max:100',
            'birth_date'  => 'nullable|date',
            'address'     => 'nullable|string',
            'agama'       => 'nullable|string|max:20',
            'tahun_masuk' => 'nullable|digits:4',
            'parent_phone'=> 'nullable|string|max:20',
        ]);

        // Phase 9.3: class_name column dropped — kelas_id is sole FK to kelas
        $kelas = Kelas::findOrFail($validated['kelas_id']);

        DB::transaction(function () use ($validated, $kelas) {
            $student = Student::create(array_merge(
                collect($validated)->except('kelas_id')->toArray(),
                [
                    'kelas_id'          => $kelas->id,
                    'status'            => 'active',
                    'status_changed_at' => now(),
                    'status_changed_by' => Auth::id(),
                ]
            ));

            StudentStatusLog::create([
                'student_id'  => $student->id,
                'status_lama' => null,
                'status_baru' => 'active',
                'catatan'     => 'Input langsung TU — siswa aktif (kelas: ' . $kelas->nama_kelas . ')',
                'diubah_oleh' => Auth::id(),
            ]);
        });

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Siswa berhasil ditambahkan.']);
        }

        return redirect()->route('students.index')
            ->with('success', 'Data siswa berhasil ditambahkan.');
    }

    // =========================================================================
    // SHOW
    // =========================================================================

    public function show($id)
    {
        $student = Student::with('kelas')->findOrFail($id);

        $posTransactions = PosOrder::where('student_id', $id)->latest()->get();
        $debtPos         = $posTransactions->where('payment_status', 'UNPAID')->sum('total_amount');

        $statusLogs = StudentStatusLog::where('student_id', $id)
            ->with('diubahOleh')
            ->latest()
            ->get();

        return view('students.show', compact('student', 'posTransactions', 'debtPos', 'statusLogs'));
    }

    // =========================================================================
    // EDIT / UPDATE
    // =========================================================================

    public function edit($id)
    {
        $student   = Student::with('kelas')->findOrFail($id);
        $kelasList = Kelas::aktif()->orderBy('tingkat')->orderBy('nama_kelas')->get();
        $statuses  = Student::STATUSES;

        if (request()->wantsJson()) {
            return response()->json(array_merge($student->toArray(), [
                'statuses'  => $statuses,
                'kelas_list' => $kelasList,
            ]));
        }

        return view('students.edit', compact('student', 'kelasList', 'statuses'));
    }

    public function update(Request $request, $id)
    {
        $student = Student::findOrFail($id);

        // kelas_id wajib hanya untuk siswa aktif dan pindah_masuk.
        // Siswa non-aktif (lulus, keluar, alumni, pindah_keluar) boleh tanpa kelas.
        $activeStatuses = ['active', 'pindah_masuk'];
        $kelasRule = in_array($student->status, $activeStatuses)
            ? 'required|exists:kelas,id'
            : 'nullable|exists:kelas,id';

        $validated = $request->validate([
            'nis'         => 'required|string|max:20|unique:students,nis,' . $id,
            'nisn'        => 'nullable|string|max:20|unique:students,nisn,' . $id,
            'name'        => 'required|string|max:255',
            'gender'      => 'required|in:L,P',
            'kelas_id'    => $kelasRule,
            'birth_place' => 'nullable|string|max:100',
            'birth_date'  => 'nullable|date',
            'address'     => 'nullable|string',
            'agama'       => 'nullable|string|max:20',
            'tahun_masuk' => 'nullable|digits:4',
            'parent_phone'=> 'nullable|string|max:20',
        ]);

        // Phase 9.3: class_name column dropped
        $updateData = collect($validated)->except('kelas_id')->toArray();
        if (!empty($validated['kelas_id'])) {
            $kelas = Kelas::findOrFail($validated['kelas_id']);
            $updateData['kelas_id'] = $kelas->id;
        } elseif (array_key_exists('kelas_id', $validated) && $validated['kelas_id'] === null) {
            // Explicitly allow clearing kelas for non-active students
            $updateData['kelas_id'] = null;
        }

        $student->update($updateData);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Data siswa {$student->name} berhasil diperbarui.",
            ]);
        }

        return redirect()->route('students.show', $student->id)
            ->with('success', "Data siswa {$student->name} berhasil diperbarui.");
    }

    // =========================================================================
    // DELETE
    // =========================================================================

    public function destroy($id)
    {
        $student = Student::findOrFail($id);

        if ($student->status !== 'calon_siswa') {
            $msg = 'Siswa tidak dapat dihapus. Gunakan fitur Ubah Status untuk mengarsipkan siswa.';
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return back()->with('error', $msg);
        }

        if ($student->bills()->exists()) {
            $msg = 'Calon siswa ini memiliki data tagihan dan tidak dapat dihapus.';
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return back()->with('error', $msg);
        }

        $nama = $student->name;
        $student->delete(); // SoftDeletes — sets deleted_at only

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => "Data siswa {$nama} berhasil dihapus."]);
        }

        return redirect()->route('students.index')
            ->with('success', "Data siswa {$nama} berhasil dihapus.");
    }

    // =========================================================================
    // STATUS MANAGEMENT
    // =========================================================================

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

        // 5.3 — Status hardening: transitioning TO active requires NIS + kelas_id
        if ($statusBaru === 'active') {
            if (empty($student->nis)) {
                return back()->with('error', 'Tidak dapat mengaktifkan siswa: NIS belum diisi. Lengkapi data siswa terlebih dahulu.');
            }
            if (empty($student->kelas_id)) {
                return back()->with('error', 'Tidak dapat mengaktifkan siswa: Kelas belum ditetapkan. Lengkapi data siswa terlebih dahulu.');
            }
        }

        DB::transaction(function () use ($student, $statusLama, $statusBaru, $request) {
            $student->update([
                'status'            => $statusBaru,
                'status_notes'      => $request->catatan,
                'status_changed_at' => now(),
                'status_changed_by' => Auth::id(),
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

    // =========================================================================
    // CSV IMPORT
    // Active-student migration tool — creates status=active directly.
    // nama_kelas column is validated against master kelas table.
    // =========================================================================

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
            // ── 1. Strip UTF-8 BOM (Excel/Windows CSVs) ──────────────────────
            $raw = file_get_contents($path);
            if (str_starts_with($raw, "\xEF\xBB\xBF")) {
                $raw = substr($raw, 3);
                file_put_contents($path, $raw);
            }

            // ── 2. Auto-detect delimiter (comma vs semicolon) ─────────────────
            $firstLine  = strtok($raw, "\n");
            $delimiter  = substr_count($firstLine, ';') > substr_count($firstLine, ',') ? ';' : ',';

            $csv = Reader::createFromPath($path, 'r');
            $csv->setDelimiter($delimiter);
            $csv->setHeaderOffset(0);

            // ── 3. Validate required columns exist in header ──────────────────
            $headers  = $csv->getHeader();
            $required = ['nis', 'name', 'nama_kelas'];
            $missing  = array_diff($required, $headers);
            if ($missing) {
                return back()->with('error',
                    'Format CSV tidak valid. Kolom wajib tidak ditemukan: ' . implode(', ', $missing) .
                    '. Gunakan template yang tersedia. (Header ditemukan: ' . implode(', ', $headers) . ')');
            }

            // ── 4. Pre-load kelas map: lowercase(nama_kelas) => [id, original] ─
            $kelasRaw = Kelas::aktif()->pluck('id', 'nama_kelas')->toArray();
            // Build case-insensitive lookup
            $kelasMap = [];
            foreach ($kelasRaw as $nama => $id) {
                $kelasMap[mb_strtolower(trim($nama))] = ['id' => $id, 'original' => $nama];
            }
            $availableKelas = implode(', ', array_column($kelasMap, 'original'));

            // ── 4b. Pre-load existing NIS & NISN to avoid N+1 queries ─────
            // Keyed by value for O(1) lookup — withTrashed agar soft-deleted
            // students juga terdeteksi sebagai duplikat.
            $existingNis  = Student::withTrashed()->pluck('nis', 'nis')->toArray();
            $existingNisn = Student::withTrashed()->whereNotNull('nisn')->pluck('nisn', 'nisn')->toArray();

            $records  = $csv->getRecords();
            $inserted = 0;
            $skipped  = 0;
            $errors   = [];

            foreach ($records as $offset => $row) {
                $row    = array_map('trim', $row);
                $lineNo = $offset + 2;
                $rowErrors = [];

                // ── 5. Collect ALL errors per row before skipping ─────────────

                // Required fields
                if (empty($row['nis']))        $rowErrors[] = 'kolom nis kosong';
                if (empty($row['name']))       $rowErrors[] = 'kolom name kosong';
                if (empty($row['nama_kelas'])) $rowErrors[] = 'kolom nama_kelas kosong';

                // Kelas lookup (case-insensitive)
                $kelasId = null;
                if (!empty($row['nama_kelas'])) {
                    $kelasKey = mb_strtolower(trim($row['nama_kelas']));
                    if (isset($kelasMap[$kelasKey])) {
                        $kelasId = $kelasMap[$kelasKey]['id'];
                    } else {
                        $rowErrors[] = "kelas '{$row['nama_kelas']}' tidak ditemukan di master kelas (tersedia: {$availableKelas})";
                    }
                }

                // Duplicate NIS — uses preloaded set, no per-row query
                if (!empty($row['nis']) && isset($existingNis[$row['nis']])) {
                    $rowErrors[] = "NIS '{$row['nis']}' sudah terdaftar";
                }

                // Duplicate NISN — uses preloaded set, no per-row query
                if (!empty($row['nisn']) && isset($existingNisn[$row['nisn']])) {
                    $rowErrors[] = "NISN '{$row['nisn']}' sudah terdaftar";
                }

                // If any errors, skip this row
                if ($rowErrors) {
                    $errors[] = "Baris {$lineNo} ({$row['name']}): " . implode('; ', $rowErrors) . ' — dilewati.';
                    $skipped++;
                    continue;
                }

                // ── 6. Insert ─────────────────────────────────────────────────
                DB::transaction(function () use ($row, $kelasId) {
                    $student = Student::create([
                        'nis'          => $row['nis'],
                        'nisn'         => $row['nisn'] ?? null,
                        'name'         => $row['name'],
                        'gender'       => in_array(strtoupper($row['gender'] ?? ''), ['L', 'P'])
                                            ? strtoupper($row['gender']) : null,
                        'kelas_id'     => $kelasId,
                        'birth_place'  => $row['birth_place'] ?? null,
                        'birth_date'   => !empty($row['birth_date']) ? $row['birth_date'] : null,
                        'address'      => $row['address'] ?? null,
                        'agama'        => $row['agama'] ?? null,
                        'tahun_masuk'  => $row['tahun_masuk'] ?? null,
                        'parent_phone' => $row['parent_phone'] ?? null,
                        'status'       => 'active',
                        'status_changed_at' => now(),
                        'status_changed_by' => Auth::id(),
                    ]);

                    StudentStatusLog::create([
                        'student_id'  => $student->id,
                        'status_lama' => null,
                        'status_baru' => 'active',
                        'catatan'     => 'Import CSV',
                        'diubah_oleh' => Auth::id(),
                    ]);
                });

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

        // Column renamed: class_name → nama_kelas (must match master kelas)
        $columns = ['nis', 'nisn', 'name', 'gender', 'nama_kelas', 'birth_place', 'birth_date', 'address', 'agama', 'tahun_masuk', 'parent_phone'];
        $example = ['2024001', '1234567890', 'Ahmad Siswa', 'L', 'X - Umum', 'Jakarta', '2008-05-10', 'Jl. Merdeka No. 1', 'Islam', '2024', '08123456789'];

        $callback = function () use ($columns, $example) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            fputcsv($file, $example);
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
