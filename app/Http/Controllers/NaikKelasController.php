<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
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
     * Sekarang pakai master kelas — mapping otomatis berdasarkan tingkat + 1
     */
    public function index()
    {
        $jenjang    = DB::table('school_settings')->where('key', 'jenjang')->value('value') ?? 'SMA';
        $tingkatMax = Kelas::tingkatMaksimal();

        // Ambil semua kelas aktif beserta jumlah siswa aktif
        $kelasAktif = Kelas::aktif()
            ->withCount(['activeStudents'])
            ->orderBy('tingkat')
            ->orderBy('nama_kelas')
            ->get();

        // Buat mapping otomatis: kelas asal → kelas tujuan
        // Tingkat akhir → null (siswa akan di-graduate)
        $mappingOtomatis = $kelasAktif->map(function ($kelas) use ($tingkatMax) {
            $kelasTujuan = null;
            if ($kelas->tingkat < $tingkatMax) {
                // Cari kelas tujuan: tingkat + 1, jurusan sama
                $kelasTujuan = Kelas::aktif()
                    ->where('tingkat', $kelas->tingkat + 1)
                    ->where('jurusan', $kelas->jurusan)
                    ->first();
            }
            return [
                'kelas_asal'          => $kelas,
                'kelas_tujuan'        => $kelasTujuan,
                'is_tingkat_akhir'    => $kelas->tingkat >= $tingkatMax,
                'jumlah_siswa_aktif'  => $kelas->active_students_count,
            ];
        })->filter(fn($m) => $m['jumlah_siswa_aktif'] > 0);

        // Ambil semua kelas aktif untuk dropdown tujuan (manual override)
        $semuaKelas = Kelas::aktif()->orderBy('tingkat')->orderBy('nama_kelas')->get();

        return view('students.naik-kelas.index', compact(
            'kelasAktif', 'mappingOtomatis', 'semuaKelas', 'jenjang', 'tingkatMax'
        ));
    }

    /**
     * Preview siswa yang akan dinaikkan kelasnya
     */
    public function preview(Request $request)
    {
        $request->validate([
            'mappings'                      => 'required|array|min:1',
            'mappings.*.kelas_asal_id'      => 'required|integer|exists:kelas,id',
            'mappings.*.kelas_tujuan_id'    => 'nullable|integer|exists:kelas,id',
            'mappings.*.action'             => 'required|in:naik,graduate,skip',
        ]);

        $mappings = collect($request->mappings)
            ->filter(fn($m) => $m['action'] !== 'skip')
            ->values();

        if ($mappings->isEmpty()) {
            return back()->with('error', 'Minimal satu kelas harus diproses (tidak di-skip).');
        }

        $preview      = [];
        $totalSiswa   = 0;
        $totalLulus   = 0;

        foreach ($mappings as $mapping) {
            $kelasAsal = Kelas::find($mapping['kelas_asal_id']);
            if (!$kelasAsal) continue;

            $kelasTujuan = null;
            if ($mapping['action'] === 'naik' && !empty($mapping['kelas_tujuan_id'])) {
                $kelasTujuan = Kelas::find($mapping['kelas_tujuan_id']);
            }

            $siswa = Student::where('kelas_id', $kelasAsal->id)
                ->whereNotIn('status', self::SKIP_STATUSES)
                ->orderBy('name')
                ->get();

            // Juga ambil siswa yang class_name cocok tapi kelas_id null (backward compat)
            $siswaLama = Student::whereNull('kelas_id')
                ->where('class_name', $kelasAsal->nama_kelas)
                ->whereNotIn('status', self::SKIP_STATUSES)
                ->orderBy('name')
                ->get();

            $semuaSiswa = $siswa->merge($siswaLama);

            $preview[] = [
                'kelas_asal'   => $kelasAsal,
                'kelas_tujuan' => $kelasTujuan,
                'action'       => $mapping['action'],
                'siswa'        => $semuaSiswa,
                'jumlah'       => $semuaSiswa->count(),
            ];

            if ($mapping['action'] === 'graduate') {
                $totalLulus += $semuaSiswa->count();
            } else {
                $totalSiswa += $semuaSiswa->count();
            }
        }

        return view('students.naik-kelas.preview', compact(
            'preview', 'totalSiswa', 'totalLulus', 'mappings'
        ));
    }

    /**
     * Eksekusi naik kelas massal
     */
    public function eksekusi(Request $request)
    {
        $request->validate([
            'mappings'                   => 'required|array|min:1',
            'mappings.*.kelas_asal_id'   => 'required|integer|exists:kelas,id',
            'mappings.*.kelas_tujuan_id' => 'nullable|integer|exists:kelas,id',
            'mappings.*.action'          => 'required|in:naik,graduate',
            'catatan'                    => 'nullable|string|max:500',
        ]);

        $mappings         = collect($request->mappings)->values();
        $catatanTambahan  = $request->catatan ?? '';
        $totalNaik        = 0;
        $totalLulus       = 0;
        $totalDilewati    = 0;

        DB::transaction(function () use ($mappings, $catatanTambahan, &$totalNaik, &$totalLulus, &$totalDilewati) {
            foreach ($mappings as $mapping) {
                $kelasAsal   = Kelas::find($mapping['kelas_asal_id']);
                $action      = $mapping['action'];
                $kelasTujuan = ($action === 'naik' && !empty($mapping['kelas_tujuan_id']))
                    ? Kelas::find($mapping['kelas_tujuan_id'])
                    : null;

                if (!$kelasAsal) continue;

                // Siswa via kelas_id
                $siswaList = Student::where('kelas_id', $kelasAsal->id)->get();

                // Backward compat: siswa dengan class_name yang cocok tapi belum punya kelas_id
                $siswaLama = Student::whereNull('kelas_id')
                    ->where('class_name', $kelasAsal->nama_kelas)
                    ->get();

                $semuaSiswa = $siswaList->merge($siswaLama);

                foreach ($semuaSiswa as $siswa) {
                    // Skip siswa non-aktif
                    if (in_array($siswa->status, self::SKIP_STATUSES)) {
                        $totalDilewati++;
                        continue;
                    }

                    if ($action === 'graduate') {
                        // Tandai siswa sebagai lulus
                        $catatan = "Lulus dari kelas {$kelasAsal->nama_kelas}";
                        if ($catatanTambahan) $catatan .= " — {$catatanTambahan}";

                        $siswa->update([
                            'status'           => 'graduated',
                            'status_changed_at' => now(),
                            'status_changed_by' => Auth::id(),
                        ]);

                        StudentStatusLog::create([
                            'student_id'  => $siswa->id,
                            'status_lama' => $siswa->getOriginal('status'),
                            'status_baru' => 'graduated',
                            'catatan'     => $catatan,
                            'diubah_oleh' => Auth::id(),
                        ]);

                        $totalLulus++;

                    } elseif ($action === 'naik' && $kelasTujuan) {
                        $catatan = "Naik kelas dari {$kelasAsal->nama_kelas} ke {$kelasTujuan->nama_kelas}";
                        if ($catatanTambahan) $catatan .= " — {$catatanTambahan}";

                        $siswa->update([
                            'kelas_id'   => $kelasTujuan->id,
                            'class_name' => $kelasTujuan->nama_kelas,
                        ]);

                        StudentStatusLog::create([
                            'student_id'  => $siswa->id,
                            'status_lama' => $siswa->status,
                            'status_baru' => $siswa->status,
                            'catatan'     => $catatan,
                            'diubah_oleh' => Auth::id(),
                        ]);

                        $totalNaik++;
                    }
                }
            }
        });

        $msg = [];
        if ($totalNaik > 0)     $msg[] = "{$totalNaik} siswa berhasil naik kelas";
        if ($totalLulus > 0)    $msg[] = "{$totalLulus} siswa dinyatakan lulus";
        if ($totalDilewati > 0) $msg[] = "{$totalDilewati} siswa dilewati (status tidak aktif)";

        return redirect()->route('naik-kelas.index')
            ->with('success', implode(', ', $msg) . '.');
    }
}
