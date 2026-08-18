<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\Student;
use App\Models\StudentStatusLog;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PPDBController extends Controller
{
    public function index(Request $request)
    {
        $query = Student::byStatus('calon_siswa')->latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('nis', 'like', "%{$search}%")
                  ->orWhere('nisn', 'like', "%{$search}%");
            });
        }

        $calonSiswa = $query->paginate(20)->withQueryString();

        $stats = [
            'total'     => Student::byStatus('calon_siswa')->count(),
            'bulan_ini' => Student::byStatus('calon_siswa')
                            ->whereMonth('created_at', now()->month)
                            ->whereYear('created_at', now()->year)
                            ->count(),
            'diterima'  => Student::where('status', 'active')
                            ->whereHas('statusLogs', fn($q) => $q->where('status_lama', 'calon_siswa'))
                            ->count(),
            'pending'   => Student::byStatus('calon_siswa')->count(),
        ];

        return view('ppdb.index', compact('calonSiswa', 'stats'));
    }

    public function create()
    {
        return view('ppdb.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'nisn'        => 'nullable|string|max:20|unique:students,nisn',
            'birth_place' => 'nullable|string|max:100',
            'birth_date'  => 'nullable|date',
            'gender'      => 'required|in:L,P',
            'address'     => 'nullable|string|max:500',
            'phone'       => 'nullable|string|max:20',
            'parent_name' => 'nullable|string|max:255',
            'parent_phone'=> 'nullable|string|max:20',
            'catatan'     => 'nullable|string|max:500',
        ]);

        DB::transaction(function () use ($validated) {
            $siswa = Student::create(array_merge($validated, [
                'status'            => 'calon_siswa',
                'status_changed_at' => now(),
                'status_changed_by' => Auth::id(),
            ]));

            StudentStatusLog::create([
                'student_id'  => $siswa->id,
                'status_lama' => null,
                'status_baru' => 'calon_siswa',
                'catatan'     => 'Pendaftaran Registrasi Siswa Baru' . (!empty($validated['catatan']) ? ': ' . $validated['catatan'] : ''),
                'diubah_oleh' => Auth::id(),
            ]);
        });

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Calon siswa berhasil didaftarkan.',
            ]);
        }

        return redirect()->route('ppdb.index')
            ->with('success', 'Calon siswa berhasil didaftarkan.');
    }

    public function show(int $id)
    {
        $siswa = Student::with('statusLogs.diubahOleh')->findOrFail($id);

        // Allow post-activation view for bundle prompt (session show_bundle_prompt).
        // Only redirect away if NOT coming from activation flow.
        if ($siswa->status !== 'calon_siswa' && !session('show_bundle_prompt')) {
            return redirect()->route('students.show', $id)
                ->with('info', 'Siswa ini sudah diproses dan tidak lagi berstatus Calon Siswa.');
        }

        $kelasList = Kelas::where('is_aktif', true)->orderBy('tingkat')->get();

        return view('ppdb.show', compact('siswa', 'kelasList'));
    }

    public function edit(int $id)
    {
        $siswa = Student::findOrFail($id);

        if ($siswa->status !== 'calon_siswa') {
            if (request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Data ini sudah tidak bisa diedit via PPDB.'], 422);
            }
            return redirect()->route('students.show', $id)
                ->with('info', 'Data siswa ini sudah tidak bisa diedit via PPDB.');
        }

        if (request()->wantsJson()) {
            return response()->json($siswa);
        }

        return view('ppdb.edit', compact('siswa'));
    }

    public function update(Request $request, int $id)
    {
        $siswa = Student::findOrFail($id);

        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'nisn'        => 'nullable|string|max:20|unique:students,nisn,' . $id,
            'birth_place' => 'nullable|string|max:100',
            'birth_date'  => 'nullable|date',
            'gender'      => 'required|in:L,P',
            'address'     => 'nullable|string|max:500',
            'phone'       => 'nullable|string|max:20',
            'parent_name' => 'nullable|string|max:255',
            'parent_phone'=> 'nullable|string|max:20',
        ]);

        $siswa->update($validated);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Data calon siswa berhasil diperbarui.',
            ]);
        }

        return redirect()->route('ppdb.show', $id)
            ->with('success', 'Data calon siswa berhasil diperbarui.');
    }

    /**
     * Aktivasi Siswa (individual).
     * Replaces the old "seleksi" concept — no selection/rejection workflow.
     * aksi=terima  → active (requires NIS + kelas_id)
     * aksi=tolak   → keluar (no kelas required)
     *
     * Route kept as ppdb.seleksi for backward-compat with existing views.
     * UI label is now "Aktivasi Siswa".
     */
    public function seleksi(Request $request, int $id)
    {
        $siswa = Student::findOrFail($id);

        $validated = $request->validate([
            'aksi'     => 'required|in:terima,tolak',
            'kelas_id' => [
                'required_if:aksi,terima',
                'nullable',
                function ($attribute, $value, $fail) {
                    if ($value && !Kelas::where('id', $value)->where('is_aktif', true)->exists()) {
                        $fail('Kelas tidak valid atau tidak aktif.');
                    }
                },
            ],
            'nis'      => 'required_if:aksi,terima|nullable|string|max:20|unique:students,nis,' . $id,
            'catatan'  => 'nullable|string|max:500',
        ]);

        $aksi = $validated['aksi'];

        // 5.3 — Active status hardening: NIS + kelas_id are mandatory
        if ($aksi === 'terima') {
            if (empty($validated['kelas_id'])) {
                return back()->withErrors(['kelas_id' => 'Kelas wajib dipilih untuk mengaktifkan siswa.'])->withInput();
            }
            if (empty($validated['nis'])) {
                return back()->withErrors(['nis' => 'NIS wajib diisi untuk mengaktifkan siswa.'])->withInput();
            }
        }

        DB::transaction(function () use ($siswa, $validated, $aksi) {
            $statusLama = $siswa->status;

            if ($aksi === 'terima') {
                $kelas = Kelas::findOrFail($validated['kelas_id']);
                $siswa->update([
                    'status'            => 'active',
                    'kelas_id'          => $kelas->id,
                    'nis'               => $validated['nis'],
                    'status_changed_at' => now(),
                    'status_changed_by' => Auth::id(),
                ]);
                $catatanLog = 'Aktivasi siswa via Registrasi Siswa Baru, ditempatkan di ' . $kelas->nama_kelas;
                if (!empty($validated['catatan'])) $catatanLog .= '. ' . $validated['catatan'];
            } else {
                $siswa->update([
                    'status'            => 'keluar',
                    'status_changed_at' => now(),
                    'status_changed_by' => Auth::id(),
                ]);
                $catatanLog = 'Siswa tidak dilanjutkan (keluar) via Registrasi Siswa Baru';
                if (!empty($validated['catatan'])) $catatanLog .= ': ' . $validated['catatan'];
            }

            StudentStatusLog::create([
                'student_id'  => $siswa->id,
                'status_lama' => $statusLama,
                'status_baru' => $siswa->status,
                'catatan'     => $catatanLog,
                'diubah_oleh' => Auth::id(),
            ]);
        });

        if ($aksi === 'terima') {
            // After activation: redirect to students.show with bundle prompt.
            // ppdb.show redirects away for non-calon unless show_bundle_prompt is set,
            // so we go directly to the canonical student detail page which has the bundle prompt.
            return redirect()->route('students.show', $siswa->id)
                ->with('success', "{$siswa->name} berhasil diaktifkan sebagai siswa.")
                ->with('show_bundle_prompt', true);
        }

        return redirect()->route('ppdb.index')
            ->with('success', "{$siswa->name} tidak dilanjutkan dan ditandai keluar.");
    }

    public function konversiIndex()
    {
        $calonSiswa       = Student::byStatus('calon_siswa')->orderBy('name')->get();
        $kelasList        = Kelas::where('is_aktif', true)->orderBy('tingkat')->get();
        $tahunAjaranAktif = TahunAjaran::where('is_aktif', true)->first();

        return view('ppdb.konversi', compact('calonSiswa', 'kelasList', 'tahunAjaranAktif'));
    }

    public function konversiEksekusi(Request $request)
    {
        $request->validate([
            'siswa_ids'         => 'required|array|min:1',
            'siswa_ids.*'       => 'exists:students,id',
            'kelas_id_default'  => 'nullable|exists:kelas,id',
            'kelas_per_siswa'   => 'nullable|array',
            'kelas_per_siswa.*' => 'nullable|exists:kelas,id',
            'nis_per_siswa'     => 'nullable|array',
            'nis_per_siswa.*'   => 'nullable|string|max:20',
        ]);

        $siswaIds       = $request->input('siswa_ids', []);
        $kelasDefault   = $request->input('kelas_id_default');
        $kelasPerSiswa  = $request->input('kelas_per_siswa', []);
        $nisPerSiswa    = $request->input('nis_per_siswa', []);
        $userId         = Auth::id();

        // Pre-load existing NIS to detect duplicates without per-row queries
        $existingNis = Student::withTrashed()->whereNotNull('nis')->pluck('nis', 'nis')->toArray();

        $berhasil = [];
        $gagal    = [];

        foreach ($siswaIds as $siswaId) {
            $siswa = Student::find($siswaId);

            // ── Per-siswa validation ──────────────────────────────────────────
            if (!$siswa) {
                $gagal[] = ['id' => $siswaId, 'nama' => 'Unknown', 'alasan' => 'Data tidak ditemukan'];
                continue;
            }
            if ($siswa->status !== 'calon_siswa') {
                $gagal[] = ['id' => $siswaId, 'nama' => $siswa->name, 'alasan' => 'Status bukan calon siswa'];
                continue;
            }

            $nis     = trim($nisPerSiswa[$siswaId] ?? '');
            $kelasId = $kelasPerSiswa[$siswaId] ?? $kelasDefault ?? null;

            if (empty($nis)) {
                $gagal[] = ['id' => $siswaId, 'nama' => $siswa->name, 'alasan' => 'NIS kosong'];
                continue;
            }
            // Check duplicate NIS (exclude self)
            if (isset($existingNis[$nis]) ) {
                // The NIS might belong to this same student (re-submission) — skip that case
                $owner = Student::withTrashed()->where('nis', $nis)->first();
                if (!$owner || $owner->id !== $siswa->id) {
                    $gagal[] = ['id' => $siswaId, 'nama' => $siswa->name, 'alasan' => "NIS '{$nis}' sudah dipakai"];
                    continue;
                }
            }
            if (!$kelasId) {
                $gagal[] = ['id' => $siswaId, 'nama' => $siswa->name, 'alasan' => 'Kelas tidak dipilih'];
                continue;
            }

            $kelas = Kelas::where('id', $kelasId)->where('is_aktif', true)->first();
            if (!$kelas) {
                $gagal[] = ['id' => $siswaId, 'nama' => $siswa->name, 'alasan' => 'Kelas tidak valid atau tidak aktif'];
                continue;
            }

            // ── Atomic per-siswa transaction ─────────────────────────────────
            // Each student is independent — one failure does not roll back others.
            try {
                DB::transaction(function () use ($siswa, $kelas, $nis, $userId) {
                    $statusLama = $siswa->status;
                    $siswa->update([
                        'status'            => 'active',
                        'nis'               => $nis,
                        'kelas_id'          => $kelas->id,
                        'status_changed_at' => now(),
                        'status_changed_by' => $userId,
                    ]);
                    StudentStatusLog::create([
                        'student_id'  => $siswa->id,
                        'status_lama' => $statusLama,
                        'status_baru' => 'active',
                        'catatan'     => 'Aktivasi massal Registrasi Siswa Baru, ditempatkan di ' . $kelas->nama_kelas,
                        'diubah_oleh' => $userId,
                    ]);
                });

                // Mark NIS as used so next iterations in this batch detect it as duplicate
                $existingNis[$nis] = $nis;
                $berhasil[] = $siswa->name;

            } catch (\Throwable $e) {
                $gagal[] = ['id' => $siswaId, 'nama' => $siswa->name, 'alasan' => 'Gagal disimpan: ' . $e->getMessage()];
            }
        }

        // ── Build summary message ─────────────────────────────────────────────
        $jumlahBerhasil = count($berhasil);
        $jumlahGagal    = count($gagal);

        $pesan = "Aktivasi selesai: {$jumlahBerhasil} siswa berhasil diaktifkan.";
        if ($jumlahGagal > 0) {
            $pesan .= " {$jumlahGagal} siswa gagal.";
        }

        $rincianGagal = array_map(
            fn($g) => "{$g['nama']}: {$g['alasan']}",
            $gagal
        );

        return redirect()->route('ppdb.index')
            ->with('success', $pesan)
            ->with('aktivasi_gagal', $rincianGagal);
    }

    public function destroy(int $id)
    {
        $siswa = Student::findOrFail($id);

        if ($siswa->status !== 'calon_siswa') {
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Hanya calon siswa yang bisa dihapus via PPDB.'], 422);
            }
            return back()->with('error', 'Hanya calon siswa yang bisa dihapus via PPDB.');
        }

        // Phase 2.5: reject deletion if calon_siswa already has financial records.
        // A PPDB applicant who received a bill has entered the financial system
        // and their history must be preserved.
        if ($siswa->bills()->exists()) {
            $msg = 'Calon siswa ini memiliki data tagihan dan tidak dapat dihapus.';
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return back()->with('error', $msg);
        }

        $nama = $siswa->name;
        // statusLogs are non-financial audit records — safe to delete with the applicant.
        $siswa->statusLogs()->delete();
        // SoftDeletes: sets deleted_at, does not physically delete the row.
        $siswa->delete();

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Data calon siswa {$nama} berhasil dihapus.",
            ]);
        }

        return redirect()->route('ppdb.index')
            ->with('success', "Data calon siswa {$nama} berhasil dihapus.");
    }
}
