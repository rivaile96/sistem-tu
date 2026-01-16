<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage; // Pastikan ini ada untuk fitur hapus file

class SchoolSettingController extends Controller
{
    // Menampilkan Halaman Pengaturan
    public function index()
    {
        // Ambil data dari tabel school_settings dan ubah jadi format [key => value]
        $settings = DB::table('school_settings')->pluck('value', 'key');
        
        return view('settings.school', compact('settings'));
    }

    // Menyimpan Perubahan
    public function update(Request $request)
    {
        $request->validate([
            'school_name' => 'required|string|max:255',
            'school_address' => 'required|string',
            'school_logo' => 'nullable|image|max:2048', // Max 2MB, Format Gambar
        ]);

        // 1. Simpan Data Teks (Nama, Alamat, Telp, TTD)
        // Kita ambil semua inputan KECUALI token dan file logo
        $data = $request->except(['_token', 'school_logo']);
        
        foreach ($data as $key => $value) {
            DB::table('school_settings')->updateOrInsert(
                ['key' => $key], // Cari berdasarkan key
                ['value' => $value, 'updated_at' => now()] // Update valuenya
            );
        }

        // 2. Simpan Upload Logo (Revised & Fixed)
        if ($request->hasFile('school_logo')) {
            
            // A. Hapus logo lama jika ada (Biar storage tidak penuh)
            $oldLogo = DB::table('school_settings')->where('key', 'school_logo')->value('value');
            
            // Cek apakah file lama ada di disk 'public', jika ya hapus
            if ($oldLogo && Storage::disk('public')->exists($oldLogo)) {
                Storage::disk('public')->delete($oldLogo);
            }

            // B. Upload logo baru
            // Parameter kedua 'public' memastikan file masuk ke storage/app/public/logo
            // Hasil $path nanti otomatis bersih, contoh: "logo/namafileacak.jpg"
            $path = $request->file('school_logo')->store('logo', 'public');
            
            // C. Simpan nama file baru ke Database
            DB::table('school_settings')->updateOrInsert(
                ['key' => 'school_logo'],
                ['value' => $path, 'updated_at' => now()]
            );
        }

        return back()->with('success', 'Identitas sekolah berhasil diperbarui!');
    }
}