<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\Rombel;
use App\Models\Student;
use App\Models\StudentRombel;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RombelController extends Controller
{
    // ── Tahun Ajaran CRUD ─────────────────────────────────────────────────────

    public function tahunAjaranIndex()
    {
        $tahunAjaran = TahunAjaran::orderByDesc('is_aktif')->orderByDesc('id')->get();
        return view('rombel.tahun-ajaran.index', compact('tahunAjaran'));
    }

    public function tahunAjaranCreate()
    {
        return view('rombel.tahun-ajaran.create');
    }

    public function tahunAjaranStore(Request $request)
    {
        $data = $request->validate([
            'nama'             => 'required|string|max:20|unique:tahun_ajaran,nama',
            'tanggal_mulai'    => 'nullable|date',
            'tanggal_selesai'  => 'nullable|date|after_or_equal:tanggal_mulai',
            'is_aktif'         => 'boolean',
        ]);

        $ta = TahunAjaran::create($data);

        if ($request->boolean('is_aktif')) {
            $ta->setAktif();
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => "Tahun ajaran {$ta->nama} berhasil dibuat."]);
        }

        return redirect()->route('rombel.tahun-ajaran.index')
            ->with('success', "Tahun ajaran {$ta->nama} berhasil dibuat.");
    }

    public function tahunAjaranEdit(TahunAjaran $tahunAjaran)
    {
        if (request()->wantsJson()) {
            return response()->json([
                'id'              => $tahunAjaran->id,
                'nama'            => $tahunAjaran->nama,
                'tanggal_mulai'   => $tahunAjaran->tanggal_mulai
                    ? \Carbon\Carbon::parse($tahunAjaran->tanggal_mulai)->format('Y-m-d') : '',
                'tanggal_selesai' => $tahunAjaran->tanggal_selesai
                    ? \Carbon\Carbon::parse($tahunAjaran->tanggal_selesai)->format('Y-m-d') : '',
                'is_aktif'        => $tahunAjaran->is_aktif,
            ]);
        }
        return view('rombel.tahun-ajaran.edit', compact('tahunAjaran'));
    }

    public function tahunAjaranUpdate(Request $request, TahunAjaran $tahunAjaran)
    {
        $data = $request->validate([
            'nama'             => 'required|string|max:20|unique:tahun_ajaran,nama,' . $tahunAjaran->id,
            'tanggal_mulai'    => 'nullable|date',
            'tanggal_selesai'  => 'nullable|date|after_or_equal:tanggal_mulai',
            'is_aktif'         => 'boolean',
        ]);

        $tahunAjaran->update($data);

        if ($request->boolean('is_aktif')) {
            $tahunAjaran->setAktif();
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => "Tahun ajaran {$tahunAjaran->nama} berhasil diupdate."]);
        }

        return redirect()->route('rombel.tahun-ajaran.index')
            ->with('success', "Tahun ajaran {$tahunAjaran->nama} berhasil diupdate.");
    }

    public function tahunAjaranDestroy(TahunAjaran $tahunAjaran)
    {
        if ($tahunAjaran->rombels()->count() > 0) {
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Tahun ajaran tidak bisa dihapus karena masih ada rombel yang terkait.'], 422);
            }
            return back()->with('error', 'Tahun ajaran tidak bisa dihapus karena masih ada rombel yang terkait.');
        }

        $nama = $tahunAjaran->nama;
        $tahunAjaran->delete();

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => "Tahun ajaran {$nama} berhasil dihapus."]);
        }

        return redirect()->route('rombel.tahun-ajaran.index')
            ->with('success', "Tahun ajaran {$nama} berhasil dihapus.");
    }

    // ── Rombel CRUD ──────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $tahunAjaranAktif = TahunAjaran::aktifSekarang();
        $tahunAjaranId    = $request->input('tahun_ajaran_id', $tahunAjaranAktif?->id);
        $semuaTahunAjaran = TahunAjaran::orderByDesc('is_aktif')->orderByDesc('id')->get();

        // FIX: eager load kelas + tahunAjaran sekalian withCount — cegah N+1
        $rombels = Rombel::with(['kelas', 'tahunAjaran'])
            ->when($tahunAjaranId, fn($q) => $q->where('tahun_ajaran_id', $tahunAjaranId))
            ->withCount('studentRombels')
            ->orderBy('kelas_id')
            ->orderBy('nama_rombel')
            ->get();

        $kelasAktif = Kelas::aktif()->orderBy('tingkat')->orderBy('nama_kelas')->get();

        return view('rombel.index', compact(
            'rombels', 'semuaTahunAjaran', 'tahunAjaranId',
            'tahunAjaranAktif', 'kelasAktif'
        ));
    }

    public function create()
    {
        $kelasAktif       = Kelas::aktif()->orderBy('tingkat')->orderBy('nama_kelas')->get();
        $semuaTahunAjaran = TahunAjaran::orderByDesc('is_aktif')->orderByDesc('id')->get();
        $tahunAjaranAktif = TahunAjaran::aktifSekarang();

        return view('rombel.create', compact('kelasAktif', 'semuaTahunAjaran', 'tahunAjaranAktif'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'tahun_ajaran_id' => 'required|exists:tahun_ajaran,id',
            'kelas_id'        => 'required|exists:kelas,id',
            'nama_rombel'     => 'required|string|max:50',
            'wali_kelas'      => 'nullable|string|max:100',
            'is_aktif'        => 'boolean',
        ]);

        $exists = Rombel::where('kelas_id', $data['kelas_id'])
            ->where('tahun_ajaran_id', $data['tahun_ajaran_id'])
            ->where('nama_rombel', $data['nama_rombel'])
            ->exists();

        if ($exists) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => "Rombel {$data['nama_rombel']} sudah ada untuk kelas dan tahun ajaran ini."], 422);
            }
            return back()->withInput()->with('error', "Rombel {$data['nama_rombel']} sudah ada untuk kelas dan tahun ajaran ini.");
        }

        $rombel = Rombel::create($data);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => "Rombel {$rombel->nama_rombel} berhasil dibuat."]);
        }

        return redirect()->route('rombel.show', $rombel)
            ->with('success', "Rombel {$rombel->nama_rombel} berhasil dibuat.");
    }

    public function show(Rombel $rombel)
    {
        // FIX: eager load student di dalam studentRombels — cegah N+1 saat render tabel
        $rombel->load(['kelas', 'tahunAjaran', 'studentRombels.student']);

        // FIX: exclude siswa yang sudah ada di rombel MANA PUN di tahun ajaran yang sama
        // (termasuk rombel ini sendiri) — cegah duplikat siswa antar rombel
        $sudahAdaIds = StudentRombel::where('tahun_ajaran_id', $rombel->tahun_ajaran_id)
            ->pluck('student_id');

        $siswaBelumRombel = Student::whereNotIn('id', $sudahAdaIds)
            ->where('status', 'active')
            ->whereNull('deleted_at')
            ->orderBy('name')
            ->get();

        // Hitung jumlah siswa sekali — dipakai di view tanpa lazy load ulang
        $jumlahSiswa = $rombel->studentRombels->count();

        return view('rombel.show', compact('rombel', 'siswaBelumRombel', 'jumlahSiswa'));
    }

    public function edit(Rombel $rombel)
    {
        $kelasAktif       = Kelas::aktif()->orderBy('tingkat')->orderBy('nama_kelas')->get();
        $semuaTahunAjaran = TahunAjaran::orderByDesc('is_aktif')->orderByDesc('id')->get();

        if (request()->wantsJson()) {
            return response()->json([
                'rombel'           => $rombel,
                'kelasAktif'       => $kelasAktif,
                'semuaTahunAjaran' => $semuaTahunAjaran,
            ]);
        }

        return view('rombel.edit', compact('rombel', 'kelasAktif', 'semuaTahunAjaran'));
    }

    public function update(Request $request, Rombel $rombel)
    {
        $data = $request->validate([
            'tahun_ajaran_id' => 'required|exists:tahun_ajaran,id',
            'kelas_id'        => 'required|exists:kelas,id',
            'nama_rombel'     => 'required|string|max:50',
            'wali_kelas'      => 'nullable|string|max:100',
            'is_aktif'        => 'boolean',
        ]);

        $rombel->update($data);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => "Rombel {$rombel->nama_rombel} berhasil diupdate."]);
        }

        return redirect()->route('rombel.show', $rombel)
            ->with('success', "Rombel {$rombel->nama_rombel} berhasil diupdate.");
    }

    public function destroy(Rombel $rombel)
    {
        if ($rombel->studentRombels()->count() > 0) {
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Rombel tidak bisa dihapus karena masih ada siswa yang terdaftar.'], 422);
            }
            return back()->with('error', 'Rombel tidak bisa dihapus karena masih ada siswa yang terdaftar.');
        }

        $nama = $rombel->nama_rombel;
        $rombel->delete();

        if (request()->ajax() || request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => "Rombel {$nama} berhasil dihapus."]);
        }

        return redirect()->route('rombel.index')
            ->with('success', "Rombel {$nama} berhasil dihapus.");
    }

    // ── Assign / Remove Siswa ────────────────────────────────────────────────

    public function assignSiswa(Request $request, Rombel $rombel)
    {
        $request->validate([
            'student_ids'   => 'required|array|min:1',
            'student_ids.*' => 'exists:students,id',
        ]);

        $added   = 0;
        $skipped = 0;

        foreach ($request->student_ids as $studentId) {
            $alreadyExists = StudentRombel::where('student_id', $studentId)
                ->where('tahun_ajaran_id', $rombel->tahun_ajaran_id)
                ->exists();

            if (!$alreadyExists) {
                StudentRombel::create([
                    'student_id'      => $studentId,
                    'rombel_id'       => $rombel->id,
                    'tahun_ajaran_id' => $rombel->tahun_ajaran_id,
                ]);
                $added++;
            } else {
                $skipped++;
            }
        }

        $msg = "{$added} siswa berhasil ditambahkan ke rombel.";
        if ($skipped > 0) {
            $msg .= " {$skipped} siswa dilewati karena sudah terdaftar di rombel lain.";
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => $msg, 'added' => $added, 'skipped' => $skipped]);
        }

        return back()->with('success', $msg);
    }

    // FIX: return JSON agar view bisa pakai SweetAlert (sebelumnya hanya redirect back)
    public function removeSiswa(Request $request, Rombel $rombel, Student $student)
    {
        $deleted = StudentRombel::where('rombel_id', $rombel->id)
            ->where('student_id', $student->id)
            ->delete();

        if (!$deleted) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Siswa tidak ditemukan di rombel ini.'], 404);
            }
            return back()->with('error', 'Siswa tidak ditemukan di rombel ini.');
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json(['success' => true, 'message' => "{$student->name} berhasil dikeluarkan dari rombel."]);
        }

        return back()->with('success', "{$student->name} berhasil dikeluarkan dari rombel.");
    }
}
