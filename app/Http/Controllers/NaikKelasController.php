<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\StudentStatusLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class NaikKelasController extends Controller
{
    // Status yang tidak boleh diproses naik kelas
    const SKIP_STATUSES = ['keluar', 'lulus', 'graduated', 'alumni', 'pindah_keluar'];

    /**
     * Halaman utama naik kelas massal
     */
    public function index()
    {
        // Ambil semua kelas unik yang ada siswa aktifnya
        $kelasAktif = Student::whereNotIn('status', self::SKIP_STATUSES)
            ->select('class_name')
            ->distinct()
            ->orderBy('class_name')
            ->pluck('class_name');

        // Statistik per kelas
        $statPerKelas = Student::whereNotIn('status', self::SKIP_STATUSES)
            ->select('class_name', DB::raw('count(*) as jumlah'))
            ->groupBy('class_name')
            ->orderBy('class_name')
            ->get()
            ->keyBy('class_name');

        return view('students.naik-kelas.index', compact('kelasAktif', 'statPerKelas'));
    }

    /**
     * Preview siswa yang akan dinaikkan kelasnya
     */
    public function preview(Request $request)
    {
        $request->validate([
            'mappings'              => 'required|array|min:1',
            'mappings.*.kelas_asal' => 'required|string',
            'mappings.*.kelas_tujuan' => 'required|string|different:mappings.*.kelas_asal',
        ]);

        $mappings = collect($request->mappings)
            ->filter(fn($m) => !empty($m['kelas_asal']) && !empty($m['kelas_tujuan']))
            ->values();

        if ($mappings->isEmpty()) {
            return back()->with('error', 'Minimal satu mapping kelas harus diisi.');
        }

        // Cek duplikat kelas asal
        $kelasAsalList = $mappings->pluck('kelas_asal');
        if ($kelasAsalList->unique()->count() !== $kelasAsalList->count()) {
            return back()->with('error', 'Kelas asal tidak boleh sama dalam satu proses naik kelas.');
        }

        // Ambil siswa per mapping
        $preview = [];
        $totalSiswa = 0;

        foreach ($mappings as $mapping) {
            $siswa = Student::where('class_name', $mapping['kelas_asal'])
                ->whereNotIn('status', self::SKIP_STATUSES)
                ->orderBy('name')
                ->get();

            $preview[] = [
                'kelas_asal'    => $mapping['kelas_asal'],
                'kelas_tujuan'  => $mapping['kelas_tujuan'],
                'siswa'         => $siswa,
                'jumlah'        => $siswa->count(),
            ];

            $totalSiswa += $siswa->count();
        }

        return view('students.naik-kelas.preview', compact('preview', 'totalSiswa', 'mappings'));
    }

    /**
     * Eksekusi naik kelas massal
     */
    public function eksekusi(Request $request)
    {
        $request->validate([
            'mappings'                => 'required|array|min:1',
            'mappings.*.kelas_asal'   => 'required|string',
            'mappings.*.kelas_tujuan' => 'required|string',
            'catatan'                 => 'nullable|string|max:500',
        ]);

        $mappings = collect($request->mappings)
            ->filter(fn($m) => !empty($m['kelas_asal']) && !empty($m['kelas_tujuan']))
            ->values();

        if ($mappings->isEmpty()) {
            return back()->with('error', 'Data mapping tidak valid.');
        }

        $totalDiproses = 0;
        $totalDilewati = 0;
        $catatanTambahan = $request->catatan ?? '';

        DB::transaction(function () use ($mappings, $catatanTambahan, &$totalDiproses, &$totalDilewati) {
            foreach ($mappings as $mapping) {
                $kelasAsal   = $mapping['kelas_asal'];
                $kelasTujuan = $mapping['kelas_tujuan'];

                $siswaList = Student::where('class_name', $kelasAsal)->get();

                foreach ($siswaList as $siswa) {
                    // Lewati siswa dengan status tidak aktif
                    if (in_array($siswa->status, self::SKIP_STATUSES)) {
                        $totalDilewati++;
                        continue;
                    }

                    $kelasLama = $siswa->class_name;

                    // Update kelas siswa
                    $siswa->update(['class_name' => $kelasTujuan]);

                    // Catat log
                    $catatan = "Naik kelas dari {$kelasLama} ke {$kelasTujuan}";
                    if ($catatanTambahan) {
                        $catatan .= " — {$catatanTambahan}";
                    }

                    StudentStatusLog::create([
                        'student_id'  => $siswa->id,
                        'status_lama' => $siswa->status,
                        'status_baru' => $siswa->status, // status tidak berubah
                        'catatan'     => $catatan,
                        'diubah_oleh' => Auth::id(),
                    ]);

                    $totalDiproses++;
                }
            }
        });

        $msg = "{$totalDiproses} siswa berhasil dinaikkan kelasnya";
        if ($totalDilewati > 0) {
            $msg .= ", {$totalDilewati} siswa dilewati (status tidak aktif)";
        }

        return redirect()->route('naik-kelas.index')
            ->with('success', $msg);
    }
}
