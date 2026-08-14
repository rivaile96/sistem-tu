@extends('layouts.app')

@section('content')
<div class="p-6">
    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-semibold text-slate-800">Tahun Ajaran</h1>
            <p class="text-sm text-slate-500 mt-0.5">Kelola tahun ajaran aktif dan arsip</p>
        </div>
        <a href="{{ route('tahun-ajaran.create') }}"
           class="bg-sky-600 hover:bg-sky-700 text-white px-4 py-2 rounded-lg text-sm font-medium inline-flex items-center gap-2 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Tambah Tahun Ajaran
        </a>
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

    {{-- Table Card --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6">
        @if($tahunAjaran->isEmpty())
            <div class="text-center py-12">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-slate-300 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <p class="text-slate-500 text-sm">Belum ada tahun ajaran. Tambahkan yang pertama.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-100">
                            <th class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wide pb-3 pr-4">#</th>
                            <th class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wide pb-3 pr-4">Nama</th>
                            <th class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wide pb-3 pr-4">Tgl Mulai</th>
                            <th class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wide pb-3 pr-4">Tgl Selesai</th>
                            <th class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wide pb-3 pr-4">Status</th>
                            <th class="text-right text-xs font-semibold text-slate-500 uppercase tracking-wide pb-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach($tahunAjaran as $i => $ta)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="py-3 pr-4 text-slate-400">{{ $i + 1 }}</td>
                                <td class="py-3 pr-4 font-medium text-slate-800">{{ $ta->nama }}</td>
                                <td class="py-3 pr-4 text-slate-600">
                                    {{ $ta->tanggal_mulai ? \Carbon\Carbon::parse($ta->tanggal_mulai)->format('d M Y') : '-' }}
                                </td>
                                <td class="py-3 pr-4 text-slate-600">
                                    {{ $ta->tanggal_selesai ? \Carbon\Carbon::parse($ta->tanggal_selesai)->format('d M Y') : '-' }}
                                </td>
                                <td class="py-3 pr-4">
                                    @if($ta->is_aktif)
                                        <span class="bg-green-100 text-green-700 rounded-full px-2 py-0.5 text-xs font-medium">Aktif</span>
                                    @else
                                        <span class="bg-slate-100 text-slate-500 rounded-full px-2 py-0.5 text-xs font-medium">Nonaktif</span>
                                    @endif
                                </td>
                                <td class="py-3 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('tahun-ajaran.edit', $ta) }}"
                                           class="bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 px-3 py-1.5 rounded-lg text-xs font-medium transition-colors">
                                            Edit
                                        </a>
                                        <form action="{{ route('tahun-ajaran.destroy', $ta) }}" method="POST"
                                              onsubmit="return confirm('Hapus tahun ajaran {{ $ta->nama }}? Aksi ini tidak bisa dibatalkan.')">
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
