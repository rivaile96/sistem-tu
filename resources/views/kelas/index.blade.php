<x-app-layout>
    <!-- Flash Messages -->
    @if(session('success'))
    <div class="mb-6 flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-800 px-5 py-4 rounded-xl shadow-sm">
        <svg class="w-5 h-5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <span class="font-medium">{{ session('success') }}</span>
    </div>
    @endif

    @if(session('error'))
    <div class="mb-6 flex items-center gap-3 bg-red-50 border border-red-200 text-red-800 px-5 py-4 rounded-xl shadow-sm">
        <svg class="w-5 h-5 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <span class="font-medium">{{ session('error') }}</span>
    </div>
    @endif

    <!-- Header Section -->
    <div class="mb-8">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <div class="p-2 bg-gradient-to-br from-[#0284c7]/10 to-[#0ea5e9]/10 rounded-xl">
                        <svg class="w-7 h-7 text-[#0284c7]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                    </div>
                    <h1 class="text-3xl font-bold text-gray-900">Master Kelas</h1>
                </div>
                <!-- Breadcrumb -->
                <nav class="flex items-center gap-2 ml-12 text-sm text-gray-500">
                    <a href="{{ route('dashboard') }}" class="hover:text-[#0284c7] transition-colors">Dashboard</a>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                    <span class="text-gray-700 font-medium">Master Kelas</span>
                </nav>
            </div>

            <a href="{{ route('kelas.create') }}"
               class="flex items-center gap-2 bg-[#0284c7] text-white px-4 py-2.5 rounded-xl hover:bg-[#0369a1] transition-all duration-300 font-medium shadow-sm text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                <span>Tambah Kelas</span>
            </a>
        </div>

        <!-- Jenjang Card -->
        <div class="bg-gradient-to-br from-white to-gray-50 rounded-2xl shadow-lg border border-gray-100 p-6 mb-6">
            <div class="flex flex-col lg:flex-row gap-6 items-start lg:items-center">
                <!-- Info Jenjang -->
                <div class="flex items-center gap-4">
                    <div class="p-3 bg-gradient-to-br from-[#0284c7]/10 to-[#0ea5e9]/10 rounded-xl">
                        <svg class="w-6 h-6 text-[#0284c7]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 font-medium uppercase tracking-wide">Jenjang Aktif</p>
                        <p class="text-xl font-bold text-gray-900">{{ $jenjang }}</p>
                        <p class="text-xs text-gray-500 mt-0.5">Tingkat {{ $tingkatMin }}–{{ $tingkatMax }}</p>
                    </div>
                </div>

                <div class="hidden lg:block w-px h-12 bg-gray-200"></div>

                <!-- Form Ganti Jenjang -->
                <div class="flex-1">
                    <form action="{{ route('kelas.update-jenjang') }}" method="POST" class="flex flex-col sm:flex-row gap-3 items-start sm:items-end">
                        @csrf
                        <div class="flex-1">
                            <label class="block text-sm font-bold text-gray-700 mb-1.5">Ganti Jenjang Sekolah</label>
                            <select name="jenjang"
                                    class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-[#0284c7] focus:ring-2 focus:ring-[#0284c7]/20 bg-white appearance-none transition-all duration-300 text-sm">
                                @foreach(['SD', 'MI', 'SMP', 'MTs', 'SMA', 'SMK', 'MA'] as $j)
                                    <option value="{{ $j }}" {{ $jenjang === $j ? 'selected' : '' }}>{{ $j }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit"
                                class="flex items-center gap-2 bg-gray-800 text-white px-4 py-2.5 rounded-xl hover:bg-gray-700 transition-all duration-300 font-medium text-sm whitespace-nowrap">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Simpan Jenjang
                        </button>
                    </form>
                    <p class="text-xs text-amber-600 mt-2 flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"></path>
                        </svg>
                        Mengubah jenjang tidak mengubah data kelas yang sudah ada.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Table Card -->
    <div class="bg-gradient-to-br from-white to-gray-50 rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
        <!-- Table Header -->
        <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
            <div class="flex items-center justify-between">
                <h3 class="font-bold text-gray-900">Daftar Kelas</h3>
                <span class="text-sm text-gray-500">
                    Total <span class="font-bold text-[#0284c7]">{{ $kelas->count() }}</span> kelas
                </span>
            </div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gradient-to-r from-gray-50/80 to-gray-100/80 text-gray-700 text-xs font-bold uppercase tracking-wider">
                        <th class="px-6 py-4 text-left w-10">No</th>
                        <th class="px-6 py-4 text-left">Nama Kelas</th>
                        <th class="px-6 py-4 text-center">Tingkat</th>
                        <th class="px-6 py-4 text-left">Jurusan</th>
                        <th class="px-6 py-4 text-left">Wali Kelas</th>
                        <th class="px-6 py-4 text-center">Siswa</th>
                        <th class="px-6 py-4 text-center">Status</th>
                        <th class="px-6 py-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($kelas as $i => $k)
                    <tr class="group hover:bg-gradient-to-r hover:from-blue-50/50 hover:to-blue-100/30 transition-all duration-300">
                        <!-- No -->
                        <td class="px-6 py-4 text-sm text-gray-500 font-medium">{{ $i + 1 }}</td>

                        <!-- Nama Kelas -->
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-gray-900 group-hover:text-[#0284c7] transition-colors">
                                    {{ $k->nama_kelas }}
                                </span>
                                @if($k->is_tingkat_akhir)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-700 border border-amber-200">
                                    Tingkat Akhir
                                </span>
                                @endif
                            </div>
                        </td>

                        <!-- Tingkat -->
                        <td class="px-6 py-4 text-center">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-gradient-to-r from-[#0284c7]/10 to-[#0ea5e9]/10 text-[#0284c7] border border-[#0284c7]/20">
                                {{ $k->tingkat_label }}
                            </span>
                        </td>

                        <!-- Jurusan -->
                        <td class="px-6 py-4 text-sm text-gray-600">
                            {{ $k->jurusan ?? '—' }}
                        </td>

                        <!-- Wali Kelas -->
                        <td class="px-6 py-4 text-sm text-gray-600">
                            @if($k->wali_kelas)
                                <div class="flex items-center gap-2">
                                    <div class="w-7 h-7 rounded-full bg-gradient-to-br from-gray-300 to-gray-400 text-white flex items-center justify-center text-xs font-bold">
                                        {{ substr($k->wali_kelas, 0, 1) }}
                                    </div>
                                    <span>{{ $k->wali_kelas }}</span>
                                </div>
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </td>

                        <!-- Siswa -->
                        <td class="px-6 py-4 text-center">
                            <div class="flex flex-col items-center">
                                <span class="text-lg font-bold text-gray-900">{{ $k->active_students_count }}</span>
                                <span class="text-xs text-gray-400">/ {{ $k->students_count }} total</span>
                            </div>
                        </td>

                        <!-- Status -->
                        <td class="px-6 py-4 text-center">
                            @if($k->is_aktif)
                                <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-50 border border-emerald-200">
                                    <div class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></div>
                                    <span class="text-xs font-bold text-emerald-700">Aktif</span>
                                </div>
                            @else
                                <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-gray-100 border border-gray-200">
                                    <div class="w-1.5 h-1.5 rounded-full bg-gray-400"></div>
                                    <span class="text-xs font-bold text-gray-500">Nonaktif</span>
                                </div>
                            @endif
                        </td>

                        <!-- Aksi -->
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('kelas.edit', $k->id) }}"
                                   class="flex items-center gap-1.5 px-3 py-1.5 bg-white border border-gray-200 text-gray-700 rounded-lg hover:border-[#0284c7]/50 hover:text-[#0284c7] transition-all duration-300 text-xs font-medium shadow-sm">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                    Edit
                                </a>
                                <form action="{{ route('kelas.destroy', $k->id) }}" method="POST" class="inline"
                                      onsubmit="return confirm('Hapus kelas {{ addslashes($k->nama_kelas) }}? Tindakan ini tidak dapat dibatalkan.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="flex items-center gap-1.5 px-3 py-1.5 bg-white border border-gray-200 text-gray-700 rounded-lg hover:border-red-300 hover:text-red-600 transition-all duration-300 text-xs font-medium shadow-sm">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <!-- Empty State -->
                    <tr>
                        <td colspan="8" class="px-6 py-16">
                            <div class="flex flex-col items-center justify-center text-center">
                                <div class="w-24 h-24 mb-4 bg-gradient-to-br from-gray-100 to-gray-200 rounded-full flex items-center justify-center">
                                    <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                    </svg>
                                </div>
                                <h3 class="text-xl font-bold text-gray-700 mb-2">Belum Ada Data Kelas</h3>
                                <p class="text-gray-500 max-w-md mb-6">
                                    Belum ada kelas yang ditambahkan. Mulai dengan menambahkan kelas pertama untuk jenjang {{ $jenjang }}.
                                </p>
                                <a href="{{ route('kelas.create') }}"
                                   class="px-5 py-2.5 bg-gradient-to-r from-[#0284c7] to-[#0ea5e9] text-white rounded-xl font-medium hover:shadow-lg transition-all flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                    </svg>
                                    Tambah Kelas Pertama
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <style>
    input:focus, select:focus {
        outline: none;
        box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.1);
    }
    .overflow-x-auto::-webkit-scrollbar { height: 8px; }
    .overflow-x-auto::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 4px; }
    .overflow-x-auto::-webkit-scrollbar-thumb { background: #c1c1c1; border-radius: 4px; }
    .overflow-x-auto::-webkit-scrollbar-thumb:hover { background: #a1a1a1; }
    </style>
</x-app-layout>
