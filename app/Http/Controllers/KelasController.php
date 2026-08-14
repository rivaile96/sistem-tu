<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KelasController extends Controller
{
    public function index()
    {
        $kelas = Kelas::withCount(['students', 'activeStudents'])
            ->orderBy('tingkat')
            ->orderBy('nama_kelas')
            ->get();

        $jenjang = DB::table('school_settings')->where('key', 'jenjang')->value('value') ?? 'SMA';

        return view('kelas.index', compact('kelas', 'jenjang'));
    }

    public function create()
    {
        $jenjang = DB::table('school_settings')->where('key', 'jenjang')->value('value') ?? 'SMA';
        $jenjangList = array_keys(Kelas::JENJANG);
        $tingkatMin = Kelas::tingkatMinimal();
        $tingkatMax = Kelas::tingkatMaksimal();

        return view('kelas.create', compact('jenjang', 'jenjangList', 'tingkatMin', 'tingkatMax'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_kelas'  => 'required|string|max:100|unique:kelas,nama_kelas',
            'tingkat'     => 'required|integer|min:1|max:12',
            'jurusan'     => 'nullable|string|max:50',
            'wali_kelas'  => 'nullable|string|max:100',
            'is_aktif'    => 'boolean',
        ]);

        $validated['is_aktif'] = $request->boolean('is_aktif', true);

        Kelas::create($validated);

        return redirect()->route('kelas.index')
            ->with('success', "Kelas {$validated['nama_kelas']} berhasil ditambahkan.");
    }

    public function edit(Kelas $kelas)
    {
        $jenjang = DB::table('school_settings')->where('key', 'jenjang')->value('value') ?? 'SMA';
        $tingkatMin = Kelas::tingkatMinimal();
        $tingkatMax = Kelas::tingkatMaksimal();

        return view('kelas.edit', compact('kelas', 'jenjang', 'tingkatMin', 'tingkatMax'));
    }

    public function update(Request $request, Kelas $kelas)
    {
        $validated = $request->validate([
            'nama_kelas'  => 'required|string|max:100|unique:kelas,nama_kelas,' . $kelas->id,
            'tingkat'     => 'required|integer|min:1|max:12',
            'jurusan'     => 'nullable|string|max:50',
            'wali_kelas'  => 'nullable|string|max:100',
            'is_aktif'    => 'boolean',
        ]);

        $validated['is_aktif'] = $request->boolean('is_aktif', true);

        // Sync class_name di students kalau nama_kelas berubah
        if ($kelas->nama_kelas !== $validated['nama_kelas']) {
            $kelas->students()->update(['class_name' => $validated['nama_kelas']]);
        }

        $kelas->update($validated);

        return redirect()->route('kelas.index')
            ->with('success', "Kelas {$kelas->nama_kelas} berhasil diperbarui.");
    }

    public function destroy(Kelas $kelas)
    {
        // Cek apakah masih ada siswa aktif
        $jumlahSiswaAktif = $kelas->activeStudents()->count();
        if ($jumlahSiswaAktif > 0) {
            return back()->with('error', "Tidak bisa hapus kelas {$kelas->nama_kelas} — masih ada {$jumlahSiswaAktif} siswa aktif.");
        }

        $nama = $kelas->nama_kelas;

        // Lepas kelas_id dari students yang tersisa
        $kelas->students()->update(['kelas_id' => null]);
        $kelas->delete();

        return redirect()->route('kelas.index')
            ->with('success', "Kelas {$nama} berhasil dihapus.");
    }

    /**
     * Update jenjang sekolah
     */
    public function updateJenjang(Request $request)
    {
        $request->validate([
            'jenjang' => 'required|in:SD,MI,SMP,MTs,SMA,SMK,MA',
        ]);

        DB::table('school_settings')
            ->updateOrInsert(
                ['key' => 'jenjang'],
                ['value' => $request->jenjang, 'updated_at' => now()]
            );

        return back()->with('success', 'Jenjang sekolah berhasil diperbarui ke ' . $request->jenjang);
    }
}
