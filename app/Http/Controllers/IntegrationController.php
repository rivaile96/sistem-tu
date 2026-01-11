<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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
            $countNew = 0;
            $countUpdated = 0;

            foreach ($remoteStudents as $remote) {
                // LOGIC MAPPING DATA (PPDB -> TU)
                
                // 1. Tentukan Identitas Unik (NIS)
                // Kalau NISN kosong, kita pakai ID pendaftaran sementara
                $nisFix = !empty($remote->nisn) ? $remote->nisn : 'REG-' . $remote->id;

                // 2. Tentukan No HP Ortu (Prioritas: Ayah -> Ibu -> Wali -> Siswa)
                $phoneFix = $remote->no_hp_ayah ?? $remote->no_hp_ibu ?? $remote->no_hp_wali ?? $remote->no_hp_siswa;

                // 3. Tentukan Kelas (Misal: "X - RPL")
                // Karena di PPDB cuma ada jurusan, kita default-kan depannya 'X' (Kelas 10)
                $jurusan = $remote->jurusan_pilihan ?? 'Umum';
                $kelasFix = "X - " . $jurusan;

                // Eksekusi Simpan ke Database Lokal
                $local = Student::updateOrCreate(
                    ['nis' => $nisFix], // Kunci pencarian (biar gak duplikat)
                    [
                        'name' => $remote->nama_lengkap,
                        'class_name' => $kelasFix, 
                        'parent_phone' => $phoneFix,
                        // Kita anggap semua data dari PPDB statusnya 'active'
                        'status' => 'active', 
                        // Jika ada kolom nfc_uid di PPDB bisa ditarik juga, kalau gak ada biarin null
                        // 'nfc_uid' => $remote->nfc_uid ?? null 
                    ]
                );

                if ($local->wasRecentlyCreated) {
                    $countNew++;
                } else {
                    $countUpdated++;
                }
            }

            return back()->with('success', "Sukses Integrasi! Ditarik: {$remoteStudents->count()}. Siswa Baru: {$countNew}, Update Data: {$countUpdated}");

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