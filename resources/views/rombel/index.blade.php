@extends('layouts.app')

@section('content')
<div class="p-6">
    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-semibold text-slate-800">Rombel</h1>
            <p class="text-sm text-slate-500 mt-0.5">Manajemen rombongan belajar per tahun ajaran</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('tahun-ajaran.index') }}"
               class="bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 px-4 py-2 rounded-lg text-sm font-medium inline-flex items-center gap-2 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                Kelola Tahun Ajaran
            </a>
            <a href="{{ route('rombel.create') }}"
               class="bg-sky-600 hover:bg-sky-700 text-white px-4 py-2 rounded-lg text-sm font-medium inline-flex items-center gap-2 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Tambah Rombel
            </a>
        </div>
    </div>

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg p-4 mb-6 flex items-start gap-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-500 mt-0.5 shrink-0" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            </svg>
            <span class="text-sm">{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-800 rounded-lg p-4 mb-6 flex items-start gap-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-500 mt-0.5 shrink-0" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
            </svg>
            <span class="text-sm">{{ session('error') }}</span>
        </div>
    @endif

    {{-- No Active Tahun Ajaran Banner --}}
    @if(!$tahunAjaranAktif)
        <div class="bg-amber-50 border border-amber-200 text-amber-800 rounded-lg p-4 mb-6 flex items-start gap-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-amber-500 mt-0.5 shrink-0" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
            </svg>
            <div>
                <p class="text-sm font-medium">Belum ada tahun ajaran aktif</p>
                <p class="text-xs mt-0.5">
                    Silakan
                    <a href="{{ route('tahun-ajaran.index') }}" class="underline font-medium hover:text-amber-900">kelola tahun ajaran</a>
                    terlebih dahulu sebelum membuat rombel.
                </p>
            </div>
        </div>
    @endif

    {{-- Filter --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-4 mb-4">
        <form method="GET" action="{{ route('rombel.index') }}" class="flex items-center gap-3">
            <label for="tahun_ajaran_id" class="text-sm font-medium text-slate-600 whitespace-nowrap">Tahun Ajaran:</label>
            <select
                id="tahun_ajaran_id"
                name="tahun_ajaran_id"
                onchange="this.form.submit()"
                class="border border-slate-300 rounded-lg px-3 py-1.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent transition bg-white"
            >
                <option value="">-- Semua --</option>
                @foreach($semuaTahunAjaran as $ta)
                    <option value="{{ $ta->id }}" {{ $tahunAjaranId == $ta->id ? 'selected' : '' }}>
                        {{ $ta->nama }}{{ $ta->is_aktif ? ' (Aktif)' : '' }}
                    </option>
                @endforeach
            </select>
            @if($tahunAjaranId)
                <a href="{{ route('rombel.index') }}"
                   class="text-xs text-slate-400 hover:text-slate-600 transition-colors">Reset</a>
            @endif
        </form>
    </div>

    {{-- Table Card --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6">
        @if($rombels->isEmpty())
            <div class="text-center py-12">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-slate-300 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <p class="text-slate-500 text-sm">Belum ada rombel untuk filter ini.</p>
                @if($tahunAjaranAktif)
                    <a href="{{ route('rombel.create') }}" class="mt-3 inline-block text-sky-600 text-sm hover:underline">Tambah rombel sekarang</a>
                @endif
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-100">
                            <th class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wide pb-3 pr-4">#</th>
                            <th class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wide pb-3 pr-4">Nama Rombel</th>
                            <th class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wide pb-3 pr-4">Kelas</th>
                            <th class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wide pb-3 pr-4">Tahun Ajaran</th>
                            <th class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wide pb-3 pr-4">Wali Kelas</th>
                            <th class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wide pb-3 pr-4">Siswa</th>
                            <th class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wide pb-3 pr-4">Status</th>
                            <th class="text-right text-xs font-semibold text-slate-500 uppercase tracking-wide pb-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach($rombels as $i => $rombel)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="py-3 pr-4 text-slate-400">{{ $i + 1 }}</td>
                                <td class="py-3 pr-4 font-medium text-slate-800">{{ $rombel->nama_rombel }}</td>
                                <td class="py-3 pr-4 text-slate-600">{{ $rombel->kelas->nama_kelas ?? '-' }}</td>
                                <td class="py-3 pr-4 text-slate-600">{{ $rombel->tahunAjaran->nama ?? '-' }}</td>
                                <td class="py-3 pr-4 text-slate-600">{{ $rombel->wali_kelas ?: '-' }}</td>
                                <td class="py-3 pr-4">
                                    <span class="inline-flex items-center gap-1 text-slate-600">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        {{ $rombel->student_rombels_count }}
                                    </span>
                                </td>
                                <td class="py-3 pr-4">
                                    @if($rombel->is_aktif)
                                        <span class="bg-green-100 text-green-700 rounded-full px-2 py-0.5 text-xs font-medium">Aktif</span>
                                    @else
                                        <span class="bg-slate-100 text-slate-500 rounded-full px-2 py-0.5 text-xs font-medium">Nonaktif</span>
                                    @endif
                                </td>
                                <td class="py-3 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('rombel.show', $rombel) }}"
                                           class="bg-sky-50 hover:bg-sky-100 text-sky-700 px-3 py-1.5 rounded-lg text-xs font-medium transition-colors">
                                            Lihat
                                        </a>
                                        <a href="{{ route('rombel.edit', $rombel) }}"
                                           class="bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 px-3 py-1.5 rounded-lg text-xs font-medium transition-colors">
                                            Edit
                                        </a>
                                        <form action="{{ route('rombel.destroy', $rombel) }}" method="POST"
                                              onsubmit="return confirm('Hapus rombel {{ $rombel->nama_rombel }}? Semua data siswa di rombel ini juga akan dihapus.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="bg-red-600 hover:bg-red-700 text-white px-3 py-1.5 rounded-lg text-xs font-medium transition-colors">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
