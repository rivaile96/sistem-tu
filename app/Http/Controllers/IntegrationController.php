<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kelas;
use App\Models\Setting;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

class IntegrationController extends Controller
{
    // 1. Tampilkan Halaman Setup
    public function index()
    {
        $settings = Setting::pluck('value', 'key')->toArray();
        return view('settings.integration', compact('settings'));
    }

    // 2. Simpan Konfigurasi Database
    public function update(Request $request)
    {
        $data = $request->validate([
            'kesiswaan_host' => 'required',
            'kesiswaan_port' => 'required',
            'kesiswaan_db'   => 'required',
            'kesiswaan_user' => 'required',
            'kesiswaan_pass' => 'nullable',
        ]);

        foreach ($data as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        return back()->with('success', 'Konfigurasi berhasil disimpan!');
    }

    // 3. Action TEST KONEKSI & SYNC (Khusus Struktur db_ppdb_sekolah)
    public function sync()
    {
        try {
            // A. Konfigurasi Koneksi "On-The-Fly"
            $this->configureConnection();

            // B. Tarik Data dari Tabel PPDB 'student_candidates'
            // Kita filter yang punya Nama Lengkap aja biar data sampah gak masuk
            $remoteStudents = DB::connection('kesiswaan_remote')
                                ->table('student_candidates')
                                ->whereNotNull('nama_lengkap')
                                ->get();

            if ($remoteStudents->isEmpty()) {
                return back()->with('error', 'Koneksi Sukses, tapi tabel student_candidates kosong/tidak ditemukan.');
            }

            // C. Looping & Update Data Lokal TU
            $countNew        = 0;
            $countUpdated    = 0;
            $countUnresolved = 0;
            $unresolvedClasses = [];

            foreach ($remoteStudents as $remote) {
                // LOGIC MAPPING DATA (PPDB -> TU)
                
                // 1. Tentukan Identitas Unik (NIS)
                // Kalau NISN kosong, kita pakai ID pendaftaran sementara
                $nisFix = !empty($remote->nisn) ? $remote->nisn : 'REG-' . $remote->id;

                // 2. Tentukan No HP Ortu (Prioritas: Ayah -> Ibu -> Wali -> Siswa)
                $phoneFix = $remote->no_hp_ayah ?? $remote->no_hp_ibu ?? $remote->no_hp_wali ?? $remote->no_hp_siswa;

                // 3. Phase 9.1: Resolve kelas_id by matching against master kelas.
                // Never write arbitrary class_name from external input.
                // If no matching kelas found → keep kelas_id=NULL, status=calon_siswa,
                // and record the unresolved class string in the sync result.
                $jurusan  = $remote->jurusan_pilihan ?? 'Umum';
                $kelasFix = 'X - ' . $jurusan;

                $kelas   = Kelas::where('nama_kelas', $kelasFix)->first();
                $kelasId = $kelas?->id;

                $payload = [
                    'name'         => $remote->nama_lengkap,
                    'parent_phone' => $phoneFix,
                ];

                if ($kelasId) {
                    // Resolved — set kelas_id from master
                    $payload['kelas_id'] = $kelasId;
                    $payload['status']   = 'active';
                } else {
                    // Unresolved — do NOT write arbitrary class_name
                    // Student stays / becomes calon_siswa until manually assigned
                    $payload['status']    = 'calon_siswa';
                    $countUnresolved++;
                    $unresolvedClasses[$kelasFix] = ($unresolvedClasses[$kelasFix] ?? 0) + 1;
                }

                // Eksekusi Simpan ke Database Lokal
                $local = Student::updateOrCreate(
                    ['nis' => $nisFix],
                    $payload
                );

                if ($local->wasRecentlyCreated) {
                    $countNew++;
                } else {
                    $countUpdated++;
                }
            }

            $msg = "Sukses Integrasi! Ditarik: {$remoteStudents->count()}. Baru: {$countNew}, Update: {$countUpdated}";
            if ($countUnresolved > 0) {
                $unresolved = collect($unresolvedClasses)
                    ->map(fn ($cnt, $kls) => "{$kls} ({$cnt}x)")
                    ->join(', ');
                $msg .= ". Kelas tidak ditemukan (jadi calon_siswa): {$unresolved}";
            }
            return back()->with('success', $msg);

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal Sync: ' . $e->getMessage());
        }
    }

    // Helper: Bikin koneksi runtime
    private function configureConnection()
    {
        $settings = Setting::pluck('value', 'key')->toArray();

        Config::set('database.connections.kesiswaan_remote', [
            'driver' => 'mysql',
            'host' => $settings['kesiswaan_host'] ?? '127.0.0.1',
            'port' => $settings['kesiswaan_port'] ?? '3306',
            'database' => $settings['kesiswaan_db'] ?? '',
            'username' => $settings['kesiswaan_user'] ?? 'root',
            'password' => $settings['kesiswaan_pass'] ?? '',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => false,
        ]);
        
        DB::purge('kesiswaan_remote');
    }
}