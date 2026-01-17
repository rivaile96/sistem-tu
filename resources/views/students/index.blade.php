<x-app-layout>
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4"></div>
        
        <!-- Header Section -->
        <div class="mb-8">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
                <div>
                    <div class="flex items-center gap-3 mb-2">
                        <div class="p-2 bg-gradient-to-br from-[#0284c7]/10 to-[#0ea5e9]/10 rounded-xl">
                            <svg class="w-7 h-7 text-[#0284c7]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                            </svg>
                        </div>
                        <h1 class="text-3xl font-bold text-gray-900">Manajemen Siswa</h1>
                    </div>
                    <p class="text-gray-600 ml-12">Pusat data tagihan SPP, Uang Gedung, dan Transaksi Siswa.</p>
                </div>
                
                <a href="{{ route('settings.integration') }}" 
                   class="group flex items-center gap-3 bg-gradient-to-r from-white to-gray-50 border border-gray-200 text-gray-700 px-5 py-3 rounded-xl hover:border-[#0284c7]/50 hover:shadow-md transition-all duration-300 font-medium shadow-sm">
                    <svg class="w-5 h-5 text-[#0284c7] group-hover:animate-spin-slow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    <span>Sync Data Kesiswaan</span>
                </a>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="bg-gradient-to-br from-blue-50 to-blue-100 border border-blue-200 rounded-xl p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-blue-800 font-medium">Total Siswa</p>
                            <p class="text-2xl font-bold text-[#0284c7] mt-1">{{ $students->total() }}</p>
                        </div>
                        <div class="p-3 bg-[#0284c7]/10 rounded-lg">
                            <svg class="w-6 h-6 text-[#0284c7]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
                
                <div class="bg-gradient-to-br from-emerald-50 to-emerald-100 border border-emerald-200 rounded-xl p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-emerald-800 font-medium">Siswa Aktif</p>
                            <p class="text-2xl font-bold text-emerald-600 mt-1">{{ $students->total() }}</p>
                        </div>
                        <div class="p-3 bg-emerald-500/10 rounded-lg">
                            <svg class="w-6 h-6 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
                
                <div class="bg-gradient-to-br from-amber-50 to-amber-100 border border-amber-200 rounded-xl p-4">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-amber-800 font-medium">Kelas Tersedia</p>
                            <p class="text-2xl font-bold text-amber-600 mt-1">{{ count($classes) }}</p>
                        </div>
                        <div class="p-3 bg-amber-500/10 rounded-lg">
                            <svg class="w-6 h-6 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search Filter Card -->
        <div class="bg-gradient-to-br from-white to-gray-50 p-6 rounded-2xl shadow-lg border border-gray-100 mb-6">
            <form method="GET" action="{{ route('students.index') }}" class="flex flex-col lg:flex-row gap-4 items-end">
                
                <!-- Search Input -->
                <div class="flex-1 w-full">
                    <label class="block text-sm font-bold text-gray-700 mb-2 flex items-center gap-2">
                        <svg class="w-4 h-4 text-[#0284c7]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        Cari Siswa
                    </label>
                    <div class="relative group">
                        <input type="text" 
                               name="search" 
                               value="{{ request('search') }}" 
                               placeholder="Cari berdasarkan Nama atau NIS..." 
                               class="w-full pl-12 pr-4 py-3.5 rounded-xl border border-gray-200 focus:border-[#0284c7] focus:ring-2 focus:ring-[#0284c7]/20 bg-white transition-all duration-300 group-hover:border-[#0284c7]/50">
                        <div class="absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Class Filter -->
                <div class="w-full lg:w-64">
                    <label class="block text-sm font-bold text-gray-700 mb-2 flex items-center gap-2">
                        <svg class="w-4 h-4 text-[#0284c7]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                        Filter Kelas
                    </label>
                    <div class="relative group">
                        <select name="class_name" 
                                onchange="this.form.submit()" 
                                class="w-full pl-12 pr-4 py-3.5 rounded-xl border border-gray-200 focus:border-[#0284c7] focus:ring-2 focus:ring-[#0284c7]/20 bg-white appearance-none transition-all duration-300 group-hover:border-[#0284c7]/50">
                            <option value="">-- Semua Kelas --</option>
                            @foreach($classes as $cls)
                                <option value="{{ $cls }}" {{ request('class_name') == $cls ? 'selected' : '' }}>{{ $cls }}</option>
                            @endforeach
                        </select>
                        <div class="absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                            </svg>
                        </div>
                        <div class="absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-400 pointer-events-none">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Reset Button (if filters applied) -->
                @if(request('search') || request('class_name'))
                <div class="w-full lg:w-auto">
                    <label class="block text-sm font-bold text-gray-700 mb-2 opacity-0">Reset</label>
                    <a href="{{ route('students.index') }}" 
                       class="group flex items-center justify-center gap-2 w-full lg:w-auto px-6 py-3.5 bg-gradient-to-r from-gray-100 to-gray-200 text-gray-700 rounded-xl hover:from-gray-200 hover:to-gray-300 transition-all duration-300 font-medium">
                        <svg class="w-4 h-4 group-hover:rotate-180 transition-transform duration-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        Reset Filter
                    </a>
                </div>
                @endif
            </form>
        </div>

        <!-- Students Table Card -->
        <div class="bg-gradient-to-br from-white to-gray-50 rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
            <!-- Table Header -->
            <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
                <div class="flex items-center justify-between">
                    <h3 class="font-bold text-gray-900">Daftar Siswa</h3>
                    <p class="text-sm text-gray-500">
                        Menampilkan <span class="font-bold text-[#0284c7]">{{ $students->firstItem() ?? 0 }}-{{ $students->lastItem() ?? 0 }}</span> 
                        dari <span class="font-bold text-[#0284c7]">{{ $students->total() }}</span> siswa
                    </p>
                </div>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gradient-to-r from-gray-50/80 to-gray-100/80 text-gray-700 text-sm font-bold uppercase">
                            <th class="px-6 py-4 text-left">Siswa</th>
                            <th class="px-6 py-4 text-left">Kelas</th>
                            <th class="px-6 py-4 text-left">Status</th>
                            <th class="px-6 py-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($students as $student)
                        <tr class="group hover:bg-gradient-to-r hover:from-blue-50/50 hover:to-blue-100/30 transition-all duration-300">
                            <!-- Student Info -->
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-4">
                                    <div class="relative">
                                        <div class="w-12 h-12 rounded-full bg-gradient-to-br from-[#0284c7] to-[#0ea5e9] text-white flex items-center justify-center font-bold text-lg shadow-md">
                                            {{ substr($student->name, 0, 1) }}
                                        </div>
                                        <div class="absolute -bottom-1 -right-1 w-5 h-5 bg-emerald-500 rounded-full border-2 border-white flex items-center justify-center">
                                            <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                        </div>
                                    </div>
                                    <div>
                                        <p class="font-bold text-gray-900 group-hover:text-[#0284c7] transition-colors">{{ $student->name }}</p>
                                        <div class="flex items-center gap-2 mt-1">
                                            <span class="text-xs font-mono bg-gray-100 text-gray-600 px-2 py-1 rounded border border-gray-200">
                                                NIS: {{ $student->nis }}
                                            </span>
                                            @if($student->nisn)
                                            <span class="text-xs font-mono bg-blue-50 text-blue-600 px-2 py-1 rounded border border-blue-100">
                                                NISN: {{ $student->nisn }}
                                            </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Class -->
                            <td class="px-6 py-4">
                                <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-gradient-to-r from-gray-50 to-gray-100 rounded-full border border-gray-200">
                                    <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                    </svg>
                                    <span class="font-bold text-gray-700">{{ $student->class_name }}</span>
                                </div>
                            </td>

                            <!-- Status -->
                            <td class="px-6 py-4">
                                <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-gradient-to-r from-emerald-50 to-emerald-100 rounded-full border border-emerald-200">
                                    <div class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></div>
                                    <span class="font-bold text-emerald-700 text-xs">AKTIF</span>
                                </div>
                            </td>

                            <!-- Action -->
                            <td class="px-6 py-4">
                                <div class="flex justify-center">
                                    <a href="{{ route('students.show', $student->id) }}" 
                                       class="group relative bg-gradient-to-r from-[#0284c7] to-[#0ea5e9] hover:from-[#027ab8] hover:to-[#0d93d7] text-white px-5 py-2.5 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-0.5 flex items-center gap-2 font-bold text-sm">
                                        <div class="absolute inset-0 bg-white/10 transform -translate-x-full group-hover:translate-x-0 transition-transform duration-700"></div>
                                        <svg class="w-4 h-4 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                                        </svg>
                                        <span class="relative z-10">Lihat Tagihan</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <!-- Empty State -->
                        <tr>
                            <td colspan="4" class="px-6 py-16">
                                <div class="flex flex-col items-center justify-center text-center">
                                    <div class="w-24 h-24 mb-4 bg-gradient-to-br from-gray-100 to-gray-200 rounded-full flex items-center justify-center">
                                        <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                                        </svg>
                                    </div>
                                    <h3 class="text-xl font-bold text-gray-700 mb-2">Tidak Ada Data Siswa</h3>
                                    <p class="text-gray-500 max-w-md mb-6">
                                        Tidak ada data siswa ditemukan. Coba sinkronisasi data kesiswaan atau ubah filter pencarian.
                                    </p>
                                    <div class="flex gap-3">
                                        <a href="{{ route('settings.integration') }}" 
                                           class="px-5 py-2.5 bg-gradient-to-r from-[#0284c7] to-[#0ea5e9] text-white rounded-xl font-medium hover:shadow-lg transition-all">
                                            Sync Data Kesiswaan
                                        </a>
                                        <a href="{{ route('students.index') }}" 
                                           class="px-5 py-2.5 bg-gradient-to-r from-gray-100 to-gray-200 text-gray-700 rounded-xl font-medium hover:bg-gray-300 transition-all">
                                            Reset Pencarian
                                        </a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($students->hasPages())
            <div class="px-6 py-4 border-t border-gray-100 bg-gradient-to-r from-gray-50/50 to-white">
                <div class="flex items-center justify-between">
                    <p class="text-sm text-gray-600">
                        Halaman <span class="font-bold">{{ $students->currentPage() }}</span> 
                        dari <span class="font-bold">{{ $students->lastPage() }}</span>
                    </p>
                    <div class="flex items-center gap-2">
                        {{ $students->links('vendor.pagination.custom') }}
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Footer Note -->
        <div class="mt-6 text-center">
            <p class="text-sm text-gray-500">
                Data siswa diperbarui secara otomatis melalui integrasi dengan database kesiswaan.
            </p>
        </div>
    </div>

    <style>
    @keyframes spin-slow {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    .animate-spin-slow {
        animation: spin-slow 2s linear infinite;
    }
    
    input:focus, select:focus {
        outline: none;
        box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.1);
    }
    
    .group:hover .group-hover\:border-\[\#0284c7\]\/50 {
        border-color: rgba(2, 132, 199, 0.5);
    }
    
    .transition-all {
        transition-property: all;
        transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
        transition-duration: 300ms;
    }
    
    .hover\:shadow-xl:hover {
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }
    
    /* Custom scrollbar for table */
    .overflow-x-auto::-webkit-scrollbar {
        height: 8px;
    }
    
    .overflow-x-auto::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
    }
    
    .overflow-x-auto::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 4px;
    }
    
    .overflow-x-auto::-webkit-scrollbar-thumb:hover {
        background: #a1a1a1;
    }
    </style>
</x-app-layout>