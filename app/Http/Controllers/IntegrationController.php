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
        // Ambil settingan yang tersimpan (biar form terisi otomatis)
        $settings = Setting::pluck('value', 'key')->toArray();
        return view('settings.integration', compact('settings'));
    }

    // 2. Simpan Konfigurasi
    public function update(Request $request)
    {
        $data = $request->validate([
            'kesiswaan_host' => 'required',
            'kesiswaan_port' => 'required',
            'kesiswaan_db'   => 'required',
            'kesiswaan_user' => 'required',
            'kesiswaan_pass' => 'nullable',
        ]);

        // Simpan ke database settings (Looping biar rapi)
        foreach ($data as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        return back()->with('success', 'Konfigurasi berhasil disimpan!');
    }

    // 3. Action TEST KONEKSI & SYNC
    public function sync()
    {
        try {
            // A. Konfigurasi Koneksi "On-The-Fly" (Tanpa edit .env)
            $this->configureConnection();

            // B. Coba Connect & Ambil Data
            // Asumsi tabel di sana namanya 'students' juga
            $remoteStudents = DB::connection('kesiswaan_remote')->table('students')->get();

            // C. Looping & Update Data Lokal TU
            $countNew = 0;
            $countUpdated = 0;

            foreach ($remoteStudents as $remote) {
                // Kita pakai NIS sebagai patokan (Unique Key)
                $local = Student::updateOrCreate(
                    ['nis' => $remote->nis], // Cari berdasarkan NIS
                    [
                        'name' => $remote->name,
                        'class_name' => $remote->class_name ?? $remote->kelas, // Sesuaikan nama kolom
                        // Penting: Kita tarik UID NFC juga
                        // Pastikan di kesiswaan ada kolom nfc_uid atau sejenisnya
                        // 'nfc_uid' => $remote->nfc_uid ?? null, 
                        // 'parent_phone' => $remote->phone_ortu ?? null,
                    ]
                );

                if ($local->wasRecentlyCreated) {
                    $countNew++;
                } else {
                    $countUpdated++;
                }
            }

            return back()->with('success', "Sukses! Data Ditarik: {$remoteStudents->count()}. Baru: {$countNew}, Update: {$countUpdated}");

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal Konek: ' . $e->getMessage());
        }
    }

    // Helper: Bikin koneksi runtime
    private function configureConnection()
    {
        $settings = Setting::pluck('value', 'key')->toArray();

        // Set config laravel secara dinamis saat runtime
        Config::set('database.connections.kesiswaan_remote', [
            'driver' => 'mysql', // atau mariadb
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
        
        // Refresh cache database
        DB::purge('kesiswaan_remote');
    }
}