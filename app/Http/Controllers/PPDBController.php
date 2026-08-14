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
    // ──────────────────────────────────────────────────────────────────────────
    // INDEX — Daftar semua calon siswa
    // ──────────────────────────────────────────────────────────────────────────
    public function index(Request $request)
    {
        $query = Student::byStatus('calon_siswa')->latest();

        // Filter pencarian
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
        ];

        return view('ppdb.index', compact('calonSiswa', 'stats'));
    }

    // ──────────────────────────────────────────────────────────────────────────
    // CREATE / STORE — Form pendaftaran calon siswa baru
    // ──────────────────────────────────────────────────────────────────────────
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

            // Log status awal
            StudentStatusLog::create([
                'student_id'  => $siswa->id,
                'status_lama' => null,
                'status_baru' => 'calon_siswa',
                'catatan'     => 'Pendaftaran PPDB baru' . ($validated['catatan'] ? ': ' . $validated['catatan'] : ''),
                'diubah_oleh' => Auth::id(),
            ]);
        });

        return redirect()->route('ppdb.index')
            ->with('success', 'Calon siswa berhasil didaftarkan.');
    }

    // ──────────────────────────────────────────────────────────────────────────
    // SHOW — Detail calon siswa + riwayat log
    // ──────────────────────────────────────────────────────────────────────────
    public function show(int $id)
    {
        $siswa = Student::with('statusLogs.statusChangedBy')->findOrFail($id);

        // Pastikan hanya calon_siswa yang bisa dibuka di halaman ini
        if ($siswa->status !== 'calon_siswa') {
            return redirect()->route('students.show', $id)
                ->with('info', 'Siswa ini sudah diproses dan tidak lagi berstatus Calon Siswa.');
        }

        $kelasList = Kelas::where('is_aktif', true)->orderBy('tingkat')->get();

        return view('ppdb.show', compact('siswa', 'kelasList'));
    }

    // ──────────────────────────────────────────────────────────────────────────
    // EDIT / UPDATE — Edit data calon siswa
    // ──────────────────────────────────────────────────────────────────────────
    public function edit(int $id)
    {
        $siswa = Student::findOrFail($id);

        if ($siswa->status !== 'calon_siswa') {
            return redirect()->route('students.show', $id)
                ->with('info', 'Data siswa ini sudah tidak bisa diedit via PPDB.');
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

        return redirect()->route('ppdb.show', $id)
            ->with('success', 'Data calon siswa berhasil diperbarui.');
    }

    // ──────────────────────────────────────────────────────────────────────────
    // SELEKSI — Terima atau tolak calon siswa (satu per satu)
    // ──────────────────────────────────────────────────────────────────────────
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
                if (!empty($validated['catatan'])) {
                    $catatanLog .= '. ' . $validated['catatan'];
                }
            } else {
                // Tolak → ubah status ke 'keluar'
                $siswa->update([
                    'status'            => 'keluar',
                    'status_changed_at' => now(),
                    'status_changed_by' => Auth::id(),
                ]);

                $catatanLog = 'Ditolak via PPDB';
                if (!empty($validated['catatan'])) {
                    $catatanLog .= ': ' . $validated['catatan'];
                }
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

    // ──────────────────────────────────────────────────────────────────────────
    // KONVERSI MASSAL — Preview + eksekusi konversi semua calon ke aktif
    // ──────────────────────────────────────────────────────────────────────────
    public function konversiIndex()
    {
        $calonSiswa = Student::byStatus('calon_siswa')
            ->orderBy('name')
            ->get();

        $kelasList = Kelas::where('is_aktif', true)
            ->orderBy('tingkat')
            ->get();

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

                // Kelas per-siswa lebih prioritas daripada default
                $kelasId = $validated['kelas_per_siswa'][$siswaId]
                    ?? $validated['kelas_id_default']
                    ?? null;

                $kelas = $kelasId ? Kelas::find($kelasId) : null;

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
                    'catatan'     => 'Konversi massal PPDB'
                                   . ($kelas ? ', ditempatkan di ' . $kelas->nama_kelas : ''),
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

    // ──────────────────────────────────────────────────────────────────────────
    // DESTROY — Hapus calon siswa (sebelum diterima)
    // ──────────────────────────────────────────────────────────────────────────
    public function destroy(int $id)
    {
        $siswa = Student::findOrFail($id);

        if ($siswa->status !== 'calon_siswa') {
            return back()->with('error', 'Hanya calon siswa yang bisa dihapus via PPDB.');
        }

        $nama = $siswa->name;
        $siswa->statusLogs()->delete();
        $siswa->delete();

        return redirect()->route('ppdb.index')
            ->with('success', "Data calon siswa {$nama} berhasil dihapus.");
    }
}
