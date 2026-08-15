<x-app-layout>


<div class="p-6">
    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-3">
            <a href="{{ route('rombel.index') }}"
               class="text-slate-400 hover:text-slate-600 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-xl font-semibold text-slate-800">{{ $rombel->nama_rombel }}</h1>
                    @if($rombel->is_aktif)
                        <span class="bg-green-100 text-green-700 rounded-full px-2 py-0.5 text-xs font-medium">Aktif</span>
                    @else
                        <span class="bg-slate-100 text-slate-500 rounded-full px-2 py-0.5 text-xs font-medium">Nonaktif</span>
                    @endif
                </div>
                <div class="flex items-center gap-3 mt-1 text-sm text-slate-500">
                    <span class="flex items-center gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        {{ $rombel->kelas->nama_kelas ?? '-' }}
                    </span>
                    <span class="text-slate-300">·</span>
                    <span class="flex items-center gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        {{ $rombel->tahunAjaran->nama ?? '-' }}
                    </span>
                    @if($rombel->wali_kelas)
                        <span class="text-slate-300">·</span>
                        <span class="flex items-center gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            {{ $rombel->wali_kelas }}
                        </span>
                    @endif
                </div>
            </div>
        </div>
        <a href="{{ route('rombel.edit', $rombel) }}"
           class="bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 px-4 py-2 rounded-lg text-sm font-medium inline-flex items-center gap-2 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
            </svg>
            Edit Rombel
        </a>
    </div>

    {{-- ============================= --}}
    {{-- Section 1: Daftar Siswa       --}}
    {{-- ============================= --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6 mb-6">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-base font-semibold text-slate-800">Daftar Siswa</h2>
                <p class="text-xs text-slate-500 mt-0.5">
                    {{ $rombel->studentRombels->count() }} siswa terdaftar
                </p>
            </div>
        </div>

        @if($rombel->studentRombels->isEmpty())
            <div class="text-center py-10">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-slate-300 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <p class="text-slate-400 text-sm">Belum ada siswa di rombel ini.</p>
                <p class="text-slate-400 text-xs mt-1">Tambahkan siswa dari bagian di bawah.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-100">
                            <th class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wide pb-3 pr-4 w-8">#</th>
                            <th class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wide pb-3 pr-4">NIS</th>
                            <th class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wide pb-3 pr-4">Nama</th>
                            <th class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wide pb-3 pr-4">Status</th>
                            <th class="text-right text-xs font-semibold text-slate-500 uppercase tracking-wide pb-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach($rombel->studentRombels as $i => $sr)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="py-3 pr-4 text-slate-400 text-xs">{{ $i + 1 }}</td>
                                <td class="py-3 pr-4 text-slate-600 font-mono text-xs">
                                    {{ $sr->student->nis ?? '-' }}
                                </td>
                                <td class="py-3 pr-4 font-medium text-slate-800">
                                    {{ $sr->student->name ?? 'Siswa tidak ditemukan' }}
                                </td>
                                <td class="py-3 pr-4">
                                    @php $status = $sr->student->status ?? null; @endphp
                                    @if($status === 'aktif' || $status === 'Aktif')
                                        <span class="bg-green-100 text-green-700 rounded-full px-2 py-0.5 text-xs font-medium">Aktif</span>
                                    @elseif($status)
                                        <span class="bg-slate-100 text-slate-500 rounded-full px-2 py-0.5 text-xs font-medium">{{ ucfirst($status) }}</span>
                                    @else
                                        <span class="text-slate-400 text-xs">-</span>
                                    @endif
                                </td>
                                <td class="py-3 text-right">
                                    <form
                                        action="{{ route('rombel.remove-siswa', [$rombel, $sr->student_id]) }}"
                                        method="POST"
                                        onsubmit="return confirm('Keluarkan {{ addslashes($sr->student->name ?? 'siswa ini') }} dari rombel {{ addslashes($rombel->nama_rombel) }}?')"
                                    >
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="bg-red-600 hover:bg-red-700 text-white px-3 py-1.5 rounded-lg text-xs font-medium transition-colors">
                                            Keluarkan
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- ============================= --}}
    {{-- Section 2: Tambah Siswa       --}}
    {{-- ============================= --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6">
        <div class="mb-4">
            <h2 class="text-base font-semibold text-slate-800">Tambah Siswa ke Rombel</h2>
            <p class="text-xs text-slate-500 mt-0.5">Pilih siswa aktif yang belum masuk ke rombel ini</p>
        </div>

        @if($siswaBelumRombel->isEmpty())
            <div class="bg-slate-50 border border-slate-200 rounded-lg p-4 flex items-start gap-3">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-slate-400 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="text-sm text-slate-500">Semua siswa aktif sudah terdaftar di rombel ini.</p>
            </div>
        @else
            <form action="{{ route('rombel.assign-siswa', $rombel) }}" method="POST">
                @csrf

                {{-- Select All helper --}}
                <div class="flex items-center gap-2 mb-3 pb-3 border-b border-slate-100">
                    <input
                        type="checkbox"
                        id="select-all"
                        class="w-4 h-4 rounded border-slate-300 text-sky-600 focus:ring-sky-500 cursor-pointer"
                        onchange="document.querySelectorAll('.siswa-checkbox').forEach(cb => cb.checked = this.checked)"
                    >
                    <label for="select-all" class="text-xs text-slate-500 cursor-pointer select-none">
                        Pilih semua ({{ $siswaBelumRombel->count() }} siswa)
                    </label>
                </div>

                {{-- Siswa checkboxes --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 max-h-80 overflow-y-auto pr-1 mb-5">
                    @foreach($siswaBelumRombel as $siswa)
                        <label class="flex items-center gap-3 p-3 rounded-lg border border-slate-100 hover:bg-slate-50 hover:border-slate-200 cursor-pointer transition-colors group">
                            <input
                                type="checkbox"
                                name="student_ids[]"
                                value="{{ $siswa->id }}"
                                class="siswa-checkbox w-4 h-4 rounded border-slate-300 text-sky-600 focus:ring-sky-500 cursor-pointer shrink-0"
                            >
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-slate-800 truncate group-hover:text-slate-900">
                                    {{ $siswa->name }}
                                </p>
                                <p class="text-xs text-slate-400 font-mono">{{ $siswa->nis ?? 'NIS tidak ada' }}</p>
                            </div>
                        </label>
                    @endforeach
                </div>

                @error('student_ids')
                    <p class="mb-3 text-xs text-red-600">{{ $message }}</p>
                @enderror

                <button type="submit"
                        class="bg-sky-600 hover:bg-sky-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors inline-flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Tambah Siswa Terpilih
                </button>
            </form>
        @endif
    </div>
</div>
</x-app-layout>
