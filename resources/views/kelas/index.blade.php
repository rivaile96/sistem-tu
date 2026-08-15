<x-app-layout>
<x-slot name="header">Master Kelas</x-slot>

<style>
/* Modal */
.modal-overlay {
    display: none;
    position: fixed;
    inset: 0;
    z-index: 9999;
    background: rgba(0,0,0,0.55);
    backdrop-filter: blur(4px);
    align-items: center;
    justify-content: center;
    padding: 1rem;
}
.modal-overlay.open { display: flex; }
.modal-box {
    background: #fff;
    border-radius: 1.25rem;
    box-shadow: 0 25px 60px rgba(0,0,0,0.25);
    width: 100%;
    max-width: 440px;
    display: flex;
    flex-direction: column;
    height: auto;
    max-height: calc(100vh - 2rem);
}
.modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid #f1f5f9;
    flex-shrink: 0;
}
.modal-body {
    padding: 1.25rem 1.5rem;
    overflow-y: auto;
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 1rem;
}
.modal-footer {
    display: flex;
    gap: 0.75rem;
    padding: 1rem 1.5rem;
    border-top: 1px solid #f1f5f9;
    flex-shrink: 0;
    background: #fff;
    border-radius: 0 0 1.25rem 1.25rem;
}
.field-label {
    display: block;
    font-size: 0.8125rem;
    font-weight: 600;
    color: #475569;
    margin-bottom: 0.375rem;
}
.field-input {
    width: 100%;
    border: 1.5px solid #e2e8f0;
    border-radius: 0.625rem;
    padding: 0.625rem 0.875rem;
    font-size: 0.875rem;
    background: #f8fafc;
    transition: all 0.15s;
    outline: none;
    box-sizing: border-box;
}
.field-input:focus { background: #fff; border-color: #38bdf8; box-shadow: 0 0 0 3px rgba(56,189,248,0.15); }
.btn-primary {
    flex: 1;
    padding: 0.625rem 1rem;
    background: #0ea5e9;
    color: #fff;
    border: none;
    border-radius: 0.625rem;
    font-size: 0.875rem;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.15s;
}
.btn-primary:hover { background: #0284c7; }
.btn-primary:disabled { opacity: 0.6; cursor: not-allowed; }
.btn-warning {
    flex: 1;
    padding: 0.625rem 1rem;
    background: #f59e0b;
    color: #fff;
    border: none;
    border-radius: 0.625rem;
    font-size: 0.875rem;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.15s;
}
.btn-warning:hover { background: #d97706; }
.btn-warning:disabled { opacity: 0.6; cursor: not-allowed; }
.btn-cancel {
    flex: 1;
    padding: 0.625rem 1rem;
    background: #fff;
    color: #64748b;
    border: 1.5px solid #e2e8f0;
    border-radius: 0.625rem;
    font-size: 0.875rem;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.15s;
}
.btn-cancel:hover { background: #f8fafc; }
.error-box {
    background: #fef2f2;
    border: 1.5px solid #fecaca;
    border-radius: 0.625rem;
    padding: 0.875rem;
    display: none;
}
.error-box ul { margin: 0.25rem 0 0 1rem; padding: 0; font-size: 0.8125rem; color: #dc2626; }
.checkbox-row {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.875rem;
    background: #f8fafc;
    border-radius: 0.625rem;
    cursor: pointer;
    border: 1.5px solid transparent;
    transition: all 0.15s;
}
.checkbox-row:hover { background: #f0f9ff; border-color: #bae6fd; }
</style>

<div class="p-4 md:p-6 space-y-5">

    {{-- ===== TOP BAR ===== --}}
    <div style="background:#fff;border-radius:1rem;border:1px solid #f1f5f9;box-shadow:0 1px 3px rgba(0,0,0,0.06);padding:1rem 1.25rem;display:flex;align-items:center;justify-content:space-between;gap:1rem;">
        <div>
            <h1 style="font-size:1.125rem;font-weight:700;color:#1e293b;margin:0;">Master Kelas</h1>
            <p style="font-size:0.875rem;color:#94a3b8;margin:0.25rem 0 0;">{{ $kelas->count() }} kelas terdaftar di {{ $jenjang }}</p>
        </div>
        <button
            type="button"
            onclick="openModal('modalCreate')"
            style="display:inline-flex;align-items:center;gap:0.5rem;background:#0ea5e9;color:#fff;font-size:0.875rem;font-weight:600;padding:0.625rem 1.25rem;border-radius:0.75rem;border:none;cursor:pointer;white-space:nowrap;box-shadow:0 1px 3px rgba(0,0,0,0.1);flex-shrink:0;"
            onmouseover="this.style.background='#0284c7'" onmouseout="this.style.background='#0ea5e9'"
        >
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah Kelas
        </button>
    </div>

    {{-- ===== STATS ===== --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-sky-50 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-sky-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
            </div>
            <div>
                <p class="text-xs text-slate-400 font-medium">Total Kelas</p>
                <p class="text-2xl font-bold text-slate-800 leading-tight">{{ $kelas->count() }}</p>
            </div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div>
                <p class="text-xs text-slate-400 font-medium">Kelas Aktif</p>
                <p class="text-2xl font-bold text-emerald-600 leading-tight">{{ $kelas->where('is_aktif', true)->count() }}</p>
            </div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-violet-50 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-violet-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <div>
                <p class="text-xs text-slate-400 font-medium">Total Siswa</p>
                <p class="text-2xl font-bold text-violet-600 leading-tight">{{ $kelas->sum('active_students_count') }}</p>
            </div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
            </div>
            <div>
                <p class="text-xs text-slate-400 font-medium">Jenjang</p>
                <p class="text-2xl font-bold text-amber-600 leading-tight">{{ $jenjang }}</p>
            </div>
        </div>
    </div>

    {{-- ===== MOBILE CARD LIST (< md) ===== --}}
    <div class="md:hidden space-y-3">
        @forelse($kelas as $k)
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="p-4">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="font-bold text-slate-800">{{ $k->nama_kelas }}</p>
                        <p class="text-xs text-slate-400 mt-0.5">Tingkat {{ $k->tingkat }}{{ $k->jurusan ? ' · '.$k->jurusan : '' }}</p>
                    </div>
                    @if($k->is_aktif)
                        <span class="shrink-0 ml-2 inline-flex items-center gap-1 bg-emerald-100 text-emerald-700 text-xs font-bold px-2.5 py-1 rounded-full">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>Aktif
                        </span>
                    @else
                        <span class="shrink-0 ml-2 inline-flex items-center gap-1 bg-slate-100 text-slate-500 text-xs font-bold px-2.5 py-1 rounded-full">
                            <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>Nonaktif
                        </span>
                    @endif
                </div>
                <div class="flex items-center gap-4 mt-2 text-xs text-slate-500">
                    <span>Wali: {{ $k->wali_kelas ?: '—' }}</span>
                    <span>{{ $k->active_students_count }} Siswa</span>
                </div>
            </div>
            <div class="border-t border-slate-100 grid grid-cols-2 divide-x divide-slate-100">
                <button onclick="fetchAndEdit({{ $k->id }})"
                        class="flex items-center justify-center gap-1.5 py-3 text-sm font-semibold text-amber-600 hover:bg-amber-50 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Edit
                </button>
                <button onclick="confirmDelete('{{ route('kelas.destroy', $k) }}', '{{ $k->nama_kelas }}')"
                        class="flex items-center justify-center gap-1.5 py-3 text-sm font-semibold text-red-500 hover:bg-red-50 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Hapus
                </button>
            </div>
        </div>
        @empty
        <div class="bg-white rounded-2xl border border-dashed border-slate-200 p-12 text-center">
            <p class="text-slate-400 font-medium">Belum ada kelas</p>
            <p class="text-slate-400 text-sm mt-1">Klik "Tambah Kelas" untuk mulai</p>
        </div>
        @endforelse
    </div>

    {{-- ===== DESKTOP TABLE (>= md) ===== --}}
    <div class="hidden md:block bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead>
                <tr class="bg-slate-50 border-b border-slate-100">
                    <th class="text-left px-5 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wide">Kelas</th>
                    <th class="text-left px-5 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wide">Tingkat</th>
                    <th class="text-left px-5 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wide">Jurusan</th>
                    <th class="text-left px-5 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wide">Wali Kelas</th>
                    <th class="text-center px-5 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wide">Siswa</th>
                    <th class="text-center px-5 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wide">Status</th>
                    <th class="text-center px-5 py-3.5 text-xs font-bold text-slate-500 uppercase tracking-wide">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-50">
                @forelse($kelas as $k)
                <tr class="hover:bg-sky-50/50 transition-colors group">
                    <td class="px-5 py-3.5">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-lg bg-sky-100 flex items-center justify-center shrink-0">
                                <span class="text-sky-700 text-xs font-extrabold">{{ $k->tingkat }}</span>
                            </div>
                            <span class="font-semibold text-slate-800">{{ $k->nama_kelas }}</span>
                        </div>
                    </td>
                    <td class="px-5 py-3.5 text-slate-600">Tingkat {{ $k->tingkat }}</td>
                    <td class="px-5 py-3.5 text-slate-600">{{ $k->jurusan ?: '—' }}</td>
                    <td class="px-5 py-3.5 text-slate-600">{{ $k->wali_kelas ?: '—' }}</td>
                    <td class="px-5 py-3.5 text-center">
                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-violet-50 text-violet-700 text-xs font-bold">{{ $k->active_students_count }}</span>
                    </td>
                    <td class="px-5 py-3.5 text-center">
                        @if($k->is_aktif)
                            <span class="inline-flex items-center gap-1.5 bg-emerald-100 text-emerald-700 text-xs font-bold px-2.5 py-1 rounded-full">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>Aktif
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 bg-slate-100 text-slate-500 text-xs font-bold px-2.5 py-1 rounded-full">
                                <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>Nonaktif
                            </span>
                        @endif
                    </td>
                    <td class="px-5 py-3.5">
                        <div class="flex items-center justify-center gap-2">
                            <button onclick="fetchAndEdit({{ $k->id }})"
                                    class="inline-flex items-center gap-1.5 bg-amber-50 hover:bg-amber-100 text-amber-700 px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                                Edit
                            </button>
                            <button onclick="confirmDelete('{{ route('kelas.destroy', $k) }}', '{{ $k->nama_kelas }}')"
                                    class="inline-flex items-center gap-1.5 bg-red-50 hover:bg-red-100 text-red-600 px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                                Hapus
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-5 py-14 text-center text-slate-400 text-sm">Belum ada data kelas.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>

{{-- ===================== MODAL CREATE ===================== --}}
<div id="modalCreate" class="modal-overlay" onclick="if(event.target===this)closeModal('modalCreate')">
    <div class="modal-box">
        <div class="modal-header">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-sky-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-800">Tambah Kelas Baru</h3>
                    <p class="text-xs text-slate-400">Isi data kelas dengan lengkap</p>
                </div>
            </div>
            <button onclick="closeModal('modalCreate')"
                    class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-slate-100 text-slate-400 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <form id="formCreate">
            @csrf
            <div class="modal-body">
                <div>
                    <label class="field-label">Nama Kelas <span class="text-red-500">*</span></label>
                    <input type="text" name="nama_kelas" placeholder="Contoh: X RPL 1" class="field-input">
                </div>
                <div>
                    <label class="field-label">Tingkat <span class="text-red-500">*</span></label>
                    <input type="number" name="tingkat" min="{{ $tingkatMin }}" max="{{ $tingkatMax }}" placeholder="{{ $tingkatMin }}" class="field-input">
                    <p class="text-xs text-slate-400 mt-1">Nilai antara {{ $tingkatMin }}–{{ $tingkatMax }}</p>
                </div>
                <div>
                    <label class="field-label">Jurusan <span class="text-slate-400 font-normal">(opsional)</span></label>
                    <input type="text" name="jurusan" placeholder="Contoh: RPL, TKJ, IPA" class="field-input">
                </div>
                <div>
                    <label class="field-label">Wali Kelas <span class="text-slate-400 font-normal">(opsional)</span></label>
                    <input type="text" name="wali_kelas" placeholder="Nama wali kelas" class="field-input">
                </div>
                <label class="checkbox-row">
                    <input type="checkbox" name="is_aktif" id="createIsAktif" value="1" checked
                           style="width:1rem;height:1rem;border-radius:0.25rem;accent-color:#0ea5e9;">
                    <div>
                        <span class="text-sm font-semibold text-slate-700">Kelas Aktif</span>
                        <p class="text-xs text-slate-400">Centang jika kelas sedang digunakan</p>
                    </div>
                </label>
                <div id="createErrors" class="error-box">
                    <p class="text-sm font-semibold text-red-600 mb-1">Perbaiki kesalahan:</p>
                    <ul id="createErrorList"></ul>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closeModal('modalCreate')" class="btn-cancel">Batal</button>
                <button type="submit" id="btnCreate" class="btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- ===================== MODAL EDIT ===================== --}}
<div id="modalEdit" class="modal-overlay" onclick="if(event.target===this)closeModal('modalEdit')">
    <div class="modal-box">
        <div class="modal-header">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-xl bg-amber-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-800">Edit Kelas</h3>
                    <p class="text-xs text-slate-400">Perbarui data kelas</p>
                </div>
            </div>
            <button onclick="closeModal('modalEdit')"
                    class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-slate-100 text-slate-400 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <form id="formEdit" data-id="">
            @csrf
            <div class="modal-body">
                <div>
                    <label class="field-label">Nama Kelas <span class="text-red-500">*</span></label>
                    <input type="text" id="editNamaKelas" name="nama_kelas" placeholder="Contoh: X RPL 1" class="field-input">
                </div>
                <div>
                    <label class="field-label">Tingkat <span class="text-red-500">*</span></label>
                    <input type="number" id="editTingkat" name="tingkat" min="{{ $tingkatMin }}" max="{{ $tingkatMax }}" class="field-input">
                    <p class="text-xs text-slate-400 mt-1">Nilai antara {{ $tingkatMin }}–{{ $tingkatMax }}</p>
                </div>
                <div>
                    <label class="field-label">Jurusan <span class="text-slate-400 font-normal">(opsional)</span></label>
                    <input type="text" id="editJurusan" name="jurusan" placeholder="Contoh: RPL, TKJ, IPA" class="field-input">
                </div>
                <div>
                    <label class="field-label">Wali Kelas <span class="text-slate-400 font-normal">(opsional)</span></label>
                    <input type="text" id="editWaliKelas" name="wali_kelas" placeholder="Nama wali kelas" class="field-input">
                </div>
                <label class="checkbox-row">
                    <input type="checkbox" name="is_aktif" id="editIsAktif" value="1"
                           style="width:1rem;height:1rem;border-radius:0.25rem;accent-color:#f59e0b;">
                    <div>
                        <span class="text-sm font-semibold text-slate-700">Kelas Aktif</span>
                        <p class="text-xs text-slate-400">Centang jika kelas sedang digunakan</p>
                    </div>
                </label>
                <div id="editErrors" class="error-box">
                    <p class="text-sm font-semibold text-red-600 mb-1">Perbaiki kesalahan:</p>
                    <ul id="editErrorList"></ul>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="closeModal('modalEdit')" class="btn-cancel">Batal</button>
                <button type="submit" id="btnEdit" class="btn-warning">Update</button>
            </div>
        </form>
    </div>
</div>

<script>
const _csrf = document.querySelector('meta[name="csrf-token"]').content;

function openModal(id) {
    document.getElementById(id).classList.add('open');
    document.body.style.overflow = 'hidden';
}
function closeModal(id) {
    document.getElementById(id).classList.remove('open');
    document.body.style.overflow = '';
}
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') { closeModal('modalCreate'); closeModal('modalEdit'); }
});

// ── CREATE ──────────────────────────────────────────────
document.getElementById('formCreate').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = document.getElementById('btnCreate');
    const errBox = document.getElementById('createErrors');
    errBox.style.display = 'none';
    btn.disabled = true; btn.textContent = 'Menyimpan…';

    const fd = new FormData(this);
    if (!this.querySelector('[name="is_aktif"]').checked) fd.delete('is_aktif');

    try {
        const res  = await fetch('{{ route("kelas.store") }}', {
            method: 'POST', headers: { 'X-CSRF-TOKEN': _csrf, 'Accept': 'application/json' }, body: fd
        });
        const data = await res.json();
        if (res.ok && data.success) {
            closeModal('modalCreate'); this.reset();
            Swal.fire({ icon:'success', title:'Berhasil!', text:data.message, toast:true, position:'top-end', timer:2500, showConfirmButton:false, timerProgressBar:true });
            setTimeout(() => location.reload(), 2600);
        } else if (res.status === 422) {
            document.getElementById('createErrorList').innerHTML = Object.values(data.errors).flat().map(e=>`<li>${e}</li>`).join('');
            errBox.style.display = 'block';
        } else {
            Swal.fire({ icon:'error', title:'Gagal!', text: data.message||'Terjadi kesalahan.' });
        }
    } catch { Swal.fire({ icon:'error', title:'Error!', text:'Koneksi bermasalah.' }); }
    finally { btn.disabled = false; btn.textContent = 'Simpan'; }
});

// ── FETCH & EDIT ─────────────────────────────────────────
async function fetchAndEdit(id) {
    try {
        const res  = await fetch(`/kelas/${id}/edit`, { headers:{'Accept':'application/json'} });
        const data = await res.json();
        document.getElementById('editNamaKelas').value = data.nama_kelas ?? '';
        document.getElementById('editTingkat').value   = data.tingkat    ?? '';
        document.getElementById('editJurusan').value   = data.jurusan    ?? '';
        document.getElementById('editWaliKelas').value = data.wali_kelas ?? '';
        document.getElementById('editIsAktif').checked = !!data.is_aktif;
        document.getElementById('formEdit').dataset.id = id;
        document.getElementById('editErrors').style.display = 'none';
        openModal('modalEdit');
    } catch { Swal.fire({ icon:'error', title:'Error!', text:'Gagal memuat data kelas.' }); }
}

// ── EDIT SUBMIT ──────────────────────────────────────────
document.getElementById('formEdit').addEventListener('submit', async function(e) {
    e.preventDefault();
    const id  = this.dataset.id;
    const btn = document.getElementById('btnEdit');
    const errBox = document.getElementById('editErrors');
    errBox.style.display = 'none';
    btn.disabled = true; btn.textContent = 'Menyimpan…';

    // Build FormData manually to ensure all fields are included
    const fd = new FormData();
    fd.append('_method', 'PUT');
    fd.append('_token', _csrf);
    fd.append('nama_kelas', document.getElementById('editNamaKelas').value);
    fd.append('tingkat',    document.getElementById('editTingkat').value);
    fd.append('jurusan',    document.getElementById('editJurusan').value);
    fd.append('wali_kelas', document.getElementById('editWaliKelas').value);
    if (document.getElementById('editIsAktif').checked) fd.append('is_aktif', '1');

    try {
        const res  = await fetch(`/kelas/${id}`, {
            method:'POST', headers:{'X-CSRF-TOKEN':_csrf,'Accept':'application/json'}, body:fd
        });
        const data = await res.json();
        if (res.ok && data.success) {
            closeModal('modalEdit');
            Swal.fire({ icon:'success', title:'Berhasil!', text:data.message, toast:true, position:'top-end', timer:2500, showConfirmButton:false, timerProgressBar:true });
            setTimeout(() => location.reload(), 2600);
        } else if (res.status === 422) {
            document.getElementById('editErrorList').innerHTML = Object.values(data.errors).flat().map(e=>`<li>${e}</li>`).join('');
            errBox.style.display = 'block';
        } else {
            Swal.fire({ icon:'error', title:'Gagal!', text: data.message||'Terjadi kesalahan.' });
        }
    } catch { Swal.fire({ icon:'error', title:'Error!', text:'Koneksi bermasalah.' }); }
    finally { btn.disabled = false; btn.textContent = 'Update'; }
});

// ── DELETE ────────────────────────────────────────────────
function confirmDelete(url, nama) {
    Swal.fire({
        title: 'Hapus Kelas?',
        html: `Kelas <strong>${nama}</strong> akan dihapus permanen.<br><small class="text-gray-400">Aksi ini tidak bisa dibatalkan.</small>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal',
        reverseButtons: true,
        focusCancel: true
    }).then(result => {
        if (!result.isConfirmed) return;
        fetch(url, {
            method:'POST',
            headers:{'X-CSRF-TOKEN':_csrf,'Content-Type':'application/x-www-form-urlencoded','Accept':'application/json'},
            body:'_method=DELETE'
        }).then(async res => {
            const data = await res.json();
            if (res.ok && data.success) {
                Swal.fire({ icon:'success', title:'Dihapus!', text:data.message, toast:true, position:'top-end', timer:2500, showConfirmButton:false, timerProgressBar:true });
                setTimeout(() => location.reload(), 2600);
            } else {
                Swal.fire({ icon:'error', title:'Gagal!', text: data.message||'Gagal menghapus.' });
            }
        }).catch(() => Swal.fire({ icon:'error', title:'Error!', text:'Koneksi bermasalah.' }));
    });
}
</script>
</x-app-layout>
