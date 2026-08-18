<x-app-layout>
<x-slot name="header">Detail Rombel</x-slot>

<div class="p-6 space-y-6">

    {{-- ── Header ── --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('rombel.index') }}"
               class="text-slate-400 hover:text-slate-600 transition-colors p-1 rounded-lg hover:bg-slate-100">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <div class="flex items-center gap-2 flex-wrap">
                    <h1 class="text-xl font-semibold text-slate-800">{{ $rombel->nama_rombel }}</h1>
                    @if($rombel->is_aktif)
                        <span class="bg-green-100 text-green-700 rounded-full px-2 py-0.5 text-xs font-medium">Aktif</span>
                    @else
                        <span class="bg-slate-100 text-slate-500 rounded-full px-2 py-0.5 text-xs font-medium">Nonaktif</span>
                    @endif
                </div>
                <div class="flex flex-wrap items-center gap-3 mt-1 text-sm text-slate-500">
                    <span class="flex items-center gap-1">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                        {{ $rombel->kelas->nama_kelas ?? '-' }}
                    </span>
                    <span class="text-slate-300">·</span>
                    <span class="flex items-center gap-1">
                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        {{ $rombel->tahunAjaran->nama ?? '-' }}
                    </span>
                    @if($rombel->wali_kelas)
                        <span class="text-slate-300">·</span>
                        <span class="flex items-center gap-1">
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            {{ $rombel->wali_kelas }}
                        </span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Tombol Edit — buka modal, konsisten dengan index.blade.php --}}
        <button onclick="openEditModal()"
                class="bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 px-4 py-2 rounded-lg text-sm font-medium inline-flex items-center gap-2 transition-colors flex-shrink-0">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
            Edit Rombel
        </button>
    </div>

    {{-- ── Stat Cards ── --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-5">
            <p class="text-xs font-medium text-slate-400 uppercase tracking-wider mb-1">Total Siswa</p>
            <p class="text-3xl font-bold text-slate-800">{{ $jumlahSiswa }}</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-5">
            <p class="text-xs font-medium text-slate-400 uppercase tracking-wider mb-1">Kelas</p>
            <p class="text-xl font-bold text-slate-800">{{ $rombel->kelas->nama_kelas ?? '-' }}</p>
        </div>
        <div class="bg-white rounded-xl border border-slate-100 shadow-sm p-5">
            <p class="text-xs font-medium text-slate-400 uppercase tracking-wider mb-1">Tahun Ajaran</p>
            <p class="text-xl font-bold text-slate-800">{{ $rombel->tahunAjaran->nama ?? '-' }}</p>
        </div>
    </div>

    {{-- ── Daftar Siswa ── --}}
    <div class="bg-white rounded-xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <h2 class="font-semibold text-slate-800">Daftar Siswa</h2>
            <span class="bg-sky-100 text-sky-700 text-xs font-bold px-2.5 py-1 rounded-full">{{ $jumlahSiswa }} Siswa</span>
        </div>

        @if($rombel->studentRombels->isEmpty())
            <div class="px-6 py-12 text-center">
                <svg class="w-12 h-12 text-slate-200 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <p class="text-slate-500 text-sm font-medium">Belum ada siswa di rombel ini</p>
                <p class="text-slate-400 text-xs mt-1">Tambahkan siswa dari panel di bawah</p>
            </div>
        @else
            {{-- Search bar daftar siswa --}}
            <div class="px-6 py-3 border-b border-slate-50 bg-slate-50/50">
                <input type="text" id="searchSiswa" placeholder="Cari nama atau NIS..."
                       class="w-full sm:w-72 px-3 py-1.5 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-400 bg-white">
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm" id="tableSiswa">
                    <thead class="bg-slate-50 text-slate-500 text-xs uppercase tracking-wide">
                        <tr>
                            <th class="px-6 py-3 text-left font-medium w-12">#</th>
                            <th class="px-6 py-3 text-left font-medium">Nama Siswa</th>
                            <th class="px-6 py-3 text-left font-medium">NIS</th>
                            <th class="px-6 py-3 text-left font-medium">NISN</th>
                            <th class="px-6 py-3 text-center font-medium">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100" id="bodyTabelSiswa">
                        @foreach($rombel->studentRombels as $i => $sr)
                        {{-- FIX: pakai $sr->student dari eager load — bukan lazy load --}}
                        <tr class="hover:bg-slate-50/50 transition-colors siswa-row"
                            data-name="{{ strtolower($sr->student->name) }}"
                            data-nis="{{ $sr->student->nis }}">
                            <td class="px-6 py-3 text-slate-400 text-xs">{{ $i + 1 }}</td>
                            <td class="px-6 py-3 font-medium text-slate-800">{{ $sr->student->name }}</td>
                            <td class="px-6 py-3 text-slate-500 font-mono text-xs">{{ $sr->student->nis ?? '-' }}</td>
                            <td class="px-6 py-3 text-slate-500 font-mono text-xs">{{ $sr->student->nisn ?? '-' }}</td>
                            <td class="px-6 py-3 text-center">
                                <button onclick="confirmRemove('{{ route('rombel.remove-siswa', [$rombel, $sr->student]) }}', '{{ $sr->student->name }}')"
                                        class="text-xs text-red-500 hover:text-red-700 hover:bg-red-50 px-2 py-1 rounded transition-colors">
                                    Keluarkan
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                {{-- Row kosong saat search tidak ketemu --}}
                <div id="noSearchResult" class="hidden px-6 py-8 text-center text-sm text-slate-400">
                    Tidak ada siswa yang cocok dengan pencarian.
                </div>
            </div>
        @endif
    </div>

    {{-- ── Panel Tambah Siswa ── --}}
    @if($siswaBelumRombel->isNotEmpty())
    <div class="bg-white rounded-xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100">
            <h2 class="font-semibold text-slate-800">Tambah Siswa ke Rombel Ini</h2>
            <p class="text-xs text-slate-400 mt-0.5">{{ $siswaBelumRombel->count() }} siswa aktif belum terdaftar di rombel manapun pada tahun ajaran ini.</p>
        </div>

        <form method="POST" action="{{ route('rombel.assign-siswa', $rombel) }}" class="p-6">
            @csrf

            {{-- Search filter siswa belum rombel --}}
            <div class="mb-3">
                <input type="text" id="searchTambah" placeholder="Cari nama atau NIS untuk filter..."
                       class="w-full sm:w-80 px-3 py-1.5 text-sm border border-slate-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-400">
            </div>

            {{-- Select All --}}
            <div class="mb-2 flex items-center gap-2">
                <input type="checkbox" id="selectAll" class="w-4 h-4 rounded border-slate-300 text-sky-600 cursor-pointer">
                <label for="selectAll" class="text-xs text-slate-500 cursor-pointer select-none">Pilih semua yang terlihat</label>
                <span id="selectedCount" class="text-xs text-sky-600 font-medium ml-2 hidden">0 dipilih</span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2 max-h-72 overflow-y-auto border border-slate-100 rounded-lg p-3 mb-4" id="listTambah">
                @foreach($siswaBelumRombel as $siswa)
                <label class="tambah-row flex items-center gap-2.5 p-2 rounded-lg hover:bg-sky-50 cursor-pointer group transition-colors border border-transparent hover:border-sky-100"
                       data-name="{{ strtolower($siswa->name) }}" data-nis="{{ $siswa->nis }}">
                    <input type="checkbox" name="student_ids[]" value="{{ $siswa->id }}"
                           class="siswa-checkbox w-4 h-4 rounded border-slate-300 text-sky-600 focus:ring-sky-500 cursor-pointer flex-shrink-0">
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-slate-700 truncate group-hover:text-slate-900">{{ $siswa->name }}</p>
                        <p class="text-xs text-slate-400 font-mono">{{ $siswa->nis ?? 'NIS -' }}</p>
                    </div>
                </label>
                @endforeach
            </div>

            <div id="noTambahResult" class="hidden py-4 text-center text-sm text-slate-400">
                Tidak ada siswa yang cocok.
            </div>

            @error('student_ids')
                <p class="mb-3 text-xs text-red-600">{{ $message }}</p>
            @enderror

            <button type="submit"
                    class="bg-sky-600 hover:bg-sky-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors inline-flex items-center gap-2">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Tambah Siswa Terpilih
            </button>
        </form>
    </div>
    @else
    <div class="bg-slate-50 rounded-xl border border-slate-100 p-6 text-center">
        <svg class="w-10 h-10 text-slate-300 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <p class="text-sm text-slate-500">Semua siswa aktif sudah terdaftar di rombel pada tahun ajaran ini.</p>
    </div>
    @endif

</div>

{{-- ── Modal Edit Rombel ── --}}
<div id="modalEdit" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 backdrop-blur-sm p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md">
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
            <h3 class="font-semibold text-slate-800">Edit Rombel</h3>
            <button onclick="closeEditModal()" class="text-slate-400 hover:text-slate-600 transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <form method="POST" action="{{ route('rombel.update', $rombel) }}" class="p-6 space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Tahun Ajaran</label>
                <select name="tahun_ajaran_id" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-400">
                    @foreach(\App\Models\TahunAjaran::orderByDesc('is_aktif')->get() as $ta)
                        <option value="{{ $ta->id }}" {{ $rombel->tahun_ajaran_id == $ta->id ? 'selected' : '' }}>
                            {{ $ta->nama }}{{ $ta->is_aktif ? ' (Aktif)' : '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Kelas</label>
                <select name="kelas_id" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-400">
                    @foreach(\App\Models\Kelas::aktif()->orderBy('tingkat')->get() as $kelas)
                        <option value="{{ $kelas->id }}" {{ $rombel->kelas_id == $kelas->id ? 'selected' : '' }}>
                            {{ $kelas->nama_kelas }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Nama Rombel</label>
                <input type="text" name="nama_rombel" value="{{ old('nama_rombel', $rombel->nama_rombel) }}"
                       class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-400"
                       placeholder="contoh: A, B, Unggulan">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Wali Kelas <span class="text-slate-400 font-normal">(opsional)</span></label>
                <input type="text" name="wali_kelas" value="{{ old('wali_kelas', $rombel->wali_kelas) }}"
                       class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:border-sky-400"
                       placeholder="Nama wali kelas">
            </div>

            <div>
                <label class="flex items-center gap-2 cursor-pointer group">
                    <input type="hidden" name="is_aktif" value="0">
                    <input type="checkbox" name="is_aktif" value="1"
                           {{ $rombel->is_aktif ? 'checked' : '' }}
                           class="w-4 h-4 rounded border-slate-300 text-sky-600 focus:ring-sky-500 cursor-pointer">
                    <span class="text-sm text-slate-700">Rombel aktif</span>
                </label>
            </div>

            <div class="flex items-center gap-3 pt-2 border-t border-slate-100">
                <button type="submit"
                        class="bg-sky-600 hover:bg-sky-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                    Simpan Perubahan
                </button>
                <button type="button" onclick="closeEditModal()"
                        class="bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                    Batal
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // ── Modal Edit ────────────────────────────────────────────────────────────
    function openEditModal() {
        document.getElementById('modalEdit').classList.remove('hidden');
        document.getElementById('modalEdit').classList.add('flex');
        document.body.style.overflow = 'hidden';
    }

    function closeEditModal() {
        document.getElementById('modalEdit').classList.add('hidden');
        document.getElementById('modalEdit').classList.remove('flex');
        document.body.style.overflow = '';
    }

    // Tutup modal saat klik backdrop
    document.getElementById('modalEdit').addEventListener('click', function(e) {
        if (e.target === this) closeEditModal();
    });

    // ── Remove Siswa via SweetAlert + JSON fetch ──────────────────────────────
    function confirmRemove(url, nama) {
        Swal.fire({
            title: 'Keluarkan Siswa?',
            html: `<b>${nama}</b> akan dikeluarkan dari rombel ini.<br><span class="text-sm text-gray-500">Data siswa tidak akan dihapus.</span>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Ya, Keluarkan',
            cancelButtonText: 'Batal'
        }).then(result => {
            if (!result.isConfirmed) return;

            fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'Accept': 'application/json'
                },
                body: '_method=DELETE'
            })
            .then(async res => {
                const data = await res.json();
                if (res.ok && data.success) {
                    Swal.fire({ icon: 'success', title: 'Dikeluarkan!', text: data.message, timer: 2000, showConfirmButton: false, toast: true, position: 'top-end' });
                    setTimeout(() => location.reload(), 1500);
                } else {
                    Swal.fire({ icon: 'error', title: 'Gagal!', text: data.message || 'Gagal mengeluarkan siswa.' });
                }
            })
            .catch(() => {
                Swal.fire({ icon: 'error', title: 'Error!', text: 'Koneksi bermasalah.' });
            });
        });
    }

    // ── Search — daftar siswa di rombel ──────────────────────────────────────
    const searchSiswa = document.getElementById('searchSiswa');
    if (searchSiswa) {
        searchSiswa.addEventListener('input', function () {
            const q = this.value.toLowerCase().trim();
            const rows = document.querySelectorAll('.siswa-row');
            let visible = 0;
            rows.forEach(row => {
                const match = row.dataset.name.includes(q) || (row.dataset.nis || '').includes(q);
                row.classList.toggle('hidden', !match);
                if (match) visible++;
            });
            document.getElementById('noSearchResult').classList.toggle('hidden', visible > 0);
        });
    }

    // ── Search — daftar siswa belum rombel ───────────────────────────────────
    const searchTambah = document.getElementById('searchTambah');
    if (searchTambah) {
        searchTambah.addEventListener('input', function () {
            const q = this.value.toLowerCase().trim();
            const rows = document.querySelectorAll('.tambah-row');
            let visible = 0;
            rows.forEach(row => {
                const match = row.dataset.name.includes(q) || (row.dataset.nis || '').includes(q);
                row.classList.toggle('hidden', !match);
                if (match) visible++;
            });
            document.getElementById('noTambahResult').classList.toggle('hidden', visible > 0);
            // Uncheck selectAll jika ada filter
            if (document.getElementById('selectAll')) {
                document.getElementById('selectAll').checked = false;
            }
        });
    }

    // ── Select All (hanya yang visible) ──────────────────────────────────────
    const selectAll = document.getElementById('selectAll');
    if (selectAll) {
        selectAll.addEventListener('change', function () {
            const visibleChecks = document.querySelectorAll('.tambah-row:not(.hidden) .siswa-checkbox');
            visibleChecks.forEach(cb => cb.checked = this.checked);
            updateSelectedCount();
        });

        document.querySelectorAll('.siswa-checkbox').forEach(cb => {
            cb.addEventListener('change', updateSelectedCount);
        });

        function updateSelectedCount() {
            const checked = document.querySelectorAll('.siswa-checkbox:checked').length;
            const el = document.getElementById('selectedCount');
            el.classList.toggle('hidden', checked === 0);
            el.textContent = `${checked} dipilih`;
        }
    }
</script>
</x-app-layout>
