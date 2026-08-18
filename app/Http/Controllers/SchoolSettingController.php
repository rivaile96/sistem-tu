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
        // 1. VALIDASI INPUT
        $request->validate([
            'school_name'      => 'required|string|max:255',
            'school_address'   => 'required|string',
            'school_logo'      => 'nullable|image|max:2048', // Max 2MB, Format Gambar
            'school_signature' => 'nullable|image|max:2048', // Validasi Tambahan untuk TTD
        ]);

        // 2. SIMPAN DATA TEKS (Nama, Alamat, Telp, Nama Bendahara)
        // PENTING: Kita harus kecualikan file 'school_logo' DAN 'school_signature'
        // agar sistem tidak mencoba menyimpannya sebagai teks (yang bikin error).
        // Whitelist key yang boleh diupdate — cegah arbitrary key injection
        $allowedKeys = [
            'school_name', 'school_address', 'school_phone',
            'school_email', 'school_website', 'principal_name',
            'treasurer_name', 'school_npsn', 'school_nss',
            'jenjang', 'accreditation', 'sidebar_color',
        ];

        // Validasi sidebar_color — hanya terima nilai yang dikenal
        $validColors = ['blue','indigo','violet','pink','rose','orange','amber','green','teal','brown','slate','black'];
        if ($request->filled('sidebar_color') && !in_array($request->sidebar_color, $validColors)) {
            return back()->with('error', 'Warna sidebar tidak valid.')->withInput();
        }

        $data = $request->only($allowedKeys);

        foreach ($data as $key => $value) {
            DB::table('school_settings')->updateOrInsert(
                ['key' => $key],
                ['value' => $value, 'updated_at' => now()]
            );
        }

        // 3. LOGIC UPLOAD LOGO (Existing / Kode Lama Kamu)
        if ($request->hasFile('school_logo')) {
            
            // A. Hapus logo lama jika ada (Biar storage tidak penuh)
            $oldLogo = DB::table('school_settings')->where('key', 'school_logo')->value('value');
            
            // Cek apakah file lama ada di disk 'public', jika ya hapus
            if ($oldLogo && Storage::disk('public')->exists($oldLogo)) {
                Storage::disk('public')->delete($oldLogo);
            }

            // B. Upload logo baru ke folder 'logo'
            $path = $request->file('school_logo')->store('logo', 'public');
            
            // C. Simpan path baru ke Database
            DB::table('school_settings')->updateOrInsert(
                ['key' => 'school_logo'],
                ['value' => $path, 'updated_at' => now()]
            );
        }

        // 4. LOGIC UPLOAD TANDA TANGAN (BARU 🔥)
        // Logikanya sama persis dengan upload logo, tapi foldernya beda biar rapi.
        if ($request->hasFile('school_signature')) {
            
            // A. Hapus TTD lama jika ada
            $oldSig = DB::table('school_settings')->where('key', 'school_signature')->value('value');
            
            if ($oldSig && Storage::disk('public')->exists($oldSig)) {
                Storage::disk('public')->delete($oldSig);
            }

            // B. Upload TTD baru ke folder 'signature'
            $pathSig = $request->file('school_signature')->store('signature', 'public');
            
            // C. Simpan path TTD ke Database
            DB::table('school_settings')->updateOrInsert(
                ['key' => 'school_signature'],
                ['value' => $pathSig, 'updated_at' => now()]
            );
        }

        return back()->with('success', 'Identitas sekolah dan tanda tangan berhasil diperbarui!');
    }
}