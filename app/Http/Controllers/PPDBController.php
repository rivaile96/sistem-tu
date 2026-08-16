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
                'catatan'     => 'Pendaftaran PPDB baru' . ($validated['catatan'] ? ': ' . $validated['catatan'] : ''),
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
        $siswa = Student::with('statusLogs.statusChangedBy')->findOrFail($id);

        if ($siswa->status !== 'calon_siswa') {
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

    public function seleksi(Request $request, int $id)
    {
        $siswa = Student::findOrFail($id);

        $validated = $request->validate([
            'aksi'      => 'required|in:terima,tolak',
            'kelas_id'  => 'required_if:aksi,terima|nullable|exists:kelas,id',
            'nis'       => 'nullable|string|max:20|unique:students,nis,' . $id,
            'catatan'   => 'nullable|string|max:500',
        ]);

        $aksi = $validated['aksi'];

        DB::transaction(function () use ($siswa, $validated, $aksi) {
            $statusLama = $siswa->status;

            if ($aksi === 'terima') {
                $kelas = Kelas::find($validated['kelas_id']);
                $siswa->update([
                    'status'            => 'active',
                    'kelas_id'          => $validated['kelas_id'],
                    'class_name'        => $kelas?->nama_kelas,
                    'nis'               => $validated['nis'] ?? $siswa->nis,
                    'status_changed_at' => now(),
                    'status_changed_by' => Auth::id(),
                ]);
                $catatanLog = 'Diterima via PPDB' . ($kelas ? ', ditempatkan di ' . $kelas->nama_kelas : '');
                if (!empty($validated['catatan'])) $catatanLog .= '. ' . $validated['catatan'];
            } else {
                $siswa->update([
                    'status'            => 'keluar',
                    'status_changed_at' => now(),
                    'status_changed_by' => Auth::id(),
                ]);
                $catatanLog = 'Ditolak via PPDB';
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

        $pesan = $aksi === 'terima'
            ? "{$siswa->name} berhasil diterima dan dijadikan siswa aktif."
            : "{$siswa->name} ditolak dari proses PPDB.";

        return redirect()->route('ppdb.index')->with('success', $pesan);
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
        $validated = $request->validate([
            'siswa_ids'          => 'required|array|min:1',
            'siswa_ids.*'        => 'exists:students,id',
            'kelas_id_default'   => 'nullable|exists:kelas,id',
            'kelas_per_siswa'    => 'nullable|array',
            'kelas_per_siswa.*'  => 'nullable|exists:kelas,id',
        ]);

        $userId   = Auth::id();
        $diproses = 0;
        $gagal    = [];

        DB::transaction(function () use ($validated, $userId, &$diproses, &$gagal) {
            foreach ($validated['siswa_ids'] as $siswaId) {
                $siswa = Student::find($siswaId);
                if (!$siswa || $siswa->status !== 'calon_siswa') {
                    $gagal[] = $siswaId;
                    continue;
                }

                $kelasId = $validated['kelas_per_siswa'][$siswaId]
                    ?? $validated['kelas_id_default']
                    ?? null;

                $kelas      = $kelasId ? Kelas::find($kelasId) : null;
                $statusLama = $siswa->status;

                $siswa->update([
                    'status'            => 'active',
                    'kelas_id'          => $kelas?->id,
                    'class_name'        => $kelas?->nama_kelas,
                    'status_changed_at' => now(),
                    'status_changed_by' => $userId,
                ]);

                StudentStatusLog::create([
                    'student_id'  => $siswa->id,
                    'status_lama' => $statusLama,
                    'status_baru' => 'active',
                    'catatan'     => 'Konversi massal PPDB' . ($kelas ? ', ditempatkan di ' . $kelas->nama_kelas : ''),
                    'diubah_oleh' => $userId,
                ]);

                $diproses++;
            }
        });

        $pesan = "{$diproses} calon siswa berhasil dikonversi menjadi siswa aktif.";
        if (count($gagal) > 0) {
            $pesan .= ' ' . count($gagal) . ' siswa gagal diproses (mungkin status sudah berubah).';
        }

        return redirect()->route('ppdb.index')->with('success', $pesan);
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
