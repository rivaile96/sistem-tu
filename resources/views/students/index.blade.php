<x-app-layout>
    <x-slot name="header">
        Data Siswa
    </x-slot>

    <div class="w-full space-y-6">
        
        <!-- HEADER & ACTION BUTTONS (SPMB MATCHING) -->
        <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
            <div>
                <h1 class="flex items-center gap-3 text-2xl font-black text-slate-800">
                    <div class="bg-[#178BC1] p-2.5 rounded-xl text-white shadow-sm shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                            <circle cx="9" cy="7" r="4"></circle>
                            <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                        </svg>
                    </div>
                    Data Siswa & Kesiswaan
                </h1>
                <p class="mt-1 text-xs text-slate-500 font-medium">
                    Pusat rekapitulasi data profil kesiswaan, status SPP bulanan, dan histori transaksi.
                </p>
            </div>
            
            <div class="flex items-center gap-2.5 w-full md:w-auto">
                <a href="{{ route('settings.integration') }}" 
                   class="flex-1 md:flex-none flex items-center justify-center gap-2 bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 px-4 py-2.5 rounded-xl font-bold text-xs shadow-sm transition-all">
                    <svg class="w-4 h-4 text-[#178BC1]" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                        <path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67"></path>
                    </svg>
                    <span>Sync Data Kesiswaan</span>
                </a>
            </div>
        </div>

        <!-- STAT CARDS (SOLID - NO GRADIENTS) -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
            <div class="p-6 bg-white border shadow-sm rounded-2xl border-slate-100 flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Siswa</p>
                    <p class="text-2xl font-bold text-slate-800 mt-1">{{ $students->total() }}</p>
                    <p class="text-xs text-slate-500 mt-0.5 font-medium">Terdaftar di sistem TU</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-blue-50 text-[#178BC1] border border-blue-100 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                </div>
            </div>

            <div class="p-6 bg-white border shadow-sm rounded-2xl border-slate-100 flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Siswa Aktif</p>
                    <p class="text-2xl font-bold text-emerald-600 mt-1">{{ $students->total() }}</p>
                    <p class="text-xs text-slate-500 mt-0.5 font-medium">Status operasional aktif</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 border border-emerald-100 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                    </svg>
                </div>
            </div>

            <div class="p-6 bg-white border shadow-sm rounded-2xl border-slate-100 flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Kelas Tersedia</p>
                    <p class="text-2xl font-bold text-amber-600 mt-1">{{ count($classes) }}</p>
                    <p class="text-xs text-slate-500 mt-0.5 font-medium">Rombongan belajar</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 border border-amber-100 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                        <rect x="4" y="2" width="16" height="20" rx="2" ry="2"></rect>
                        <line x1="9" y1="22" x2="9" y2="22.01"></line>
                        <line x1="15" y1="22" x2="15" y2="22.01"></line>
                        <line x1="9" y1="6" x2="9" y2="6.01"></line>
                        <line x1="15" y1="6" x2="15" y2="6.01"></line>
                    </svg>
                </div>
            </div>
        </div>

        <!-- FILTER ZONE (SPMB MATCHING) -->
        <div class="overflow-hidden bg-white border shadow-sm rounded-2xl border-slate-200">
            <form method="GET" action="{{ route('students.index') }}" class="p-4 flex flex-wrap items-center gap-3">
                
                <!-- Search Input -->
                <div class="relative flex-1 min-w-[220px]">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                    </div>
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}" 
                           placeholder="Cari Nama Siswa / NISN / No. HP..." 
                           class="w-full pl-10 pr-4 py-2 text-xs font-medium text-slate-800 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#178BC1]/20 focus:border-[#178BC1] transition-all" />
                </div>

                <!-- Class Filter -->
                <div class="relative w-full sm:w-48 shrink-0">
                    <select name="class" 
                            class="w-full px-3 py-2 text-xs font-semibold text-slate-700 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#178BC1]/20 focus:border-[#178BC1] transition-all cursor-pointer">
                        <option value="">Semua Kelas</option>
                        @foreach($classes as $c)
                            <option value="{{ $c }}" {{ request('class') == $c ? 'selected' : '' }}>
                                Kelas {{ $c }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Submit / Reset Buttons -->
                <div class="flex items-center gap-2">
                    <button type="submit" 
                            class="flex items-center gap-1.5 bg-[#178BC1] hover:bg-[#126da3] text-white px-4 py-2 rounded-xl text-xs font-bold shadow-sm transition-all">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
                        </svg>
                        <span>Filter</span>
                    </button>

                    @if(request('search') || request('class'))
                        <a href="{{ route('students.index') }}" 
                           class="flex items-center gap-1 px-3 py-2 text-xs font-bold text-rose-600 bg-rose-50 hover:bg-rose-100 rounded-xl border border-rose-200 transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                <line x1="18" y1="6" x2="6" y2="18"></line>
                                <line x1="6" y1="6" x2="18" y2="18"></line>
                            </svg>
                            <span>Reset</span>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- DATA TABLE (SPMB MATCHING THEAD & TBODY) -->
        <div class="overflow-hidden bg-white border shadow-sm rounded-2xl border-slate-200">
            <div class="overflow-x-auto min-h-[350px]">
                <table class="w-full text-sm text-left">
                    <thead class="bg-slate-50/80 text-slate-500 uppercase font-bold text-[10px] tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-4">Informasi Siswa</th>
                            <th class="px-6 py-4">Kontak & Orang Tua</th>
                            <th class="px-6 py-4">Rombel & Status</th>
                            <th class="px-6 py-4 text-center">Aksi Keuangan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($students as $student)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                
                                <!-- Siswa -->
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full bg-blue-50 border border-blue-200 text-[#178BC1] font-bold flex items-center justify-center shrink-0 text-sm">
                                            {{ strtoupper(substr($student->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <p class="text-sm font-bold text-slate-800">{{ $student->name }}</p>
                                            <p class="text-xs text-slate-400 mt-0.5 font-mono">
                                                NISN: {{ $student->nisn ?? 'Belum Diisi' }}
                                            </p>
                                        </div>
                                    </div>
                                </td>

                                <!-- Kontak & Orang Tua -->
                                <td class="px-6 py-4">
                                    <p class="text-xs font-semibold text-slate-700">
                                        {{ $student->parent_name ?? ($student->father_name ?? ($student->mother_name ?? 'Data belum lengkap')) }}
                                    </p>
                                    <p class="text-xs text-slate-400 mt-0.5 flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                                        </svg>
                                        {{ $student->parent_phone ?? ($student->phone ?? '-') }}
                                    </p>
                                </td>

                                <!-- Kelas & Status -->
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <span class="px-2.5 py-1 text-xs font-bold rounded-lg bg-blue-50 text-[#178BC1] border border-blue-200">
                                            Kelas {{ $student->class ?? 'Umum' }}
                                        </span>
                                        <span class="px-2.5 py-1 text-xs font-bold rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            Aktif
                                        </span>
                                    </div>
                                </td>

                                <!-- Aksi -->
                                <td class="px-6 py-4 text-center">
                                    <a href="{{ route('students.show', $student->id) }}" 
                                       class="inline-flex items-center gap-1.5 bg-[#178BC1] hover:bg-[#126da3] text-white px-3.5 py-2 rounded-xl text-xs font-bold shadow-sm transition-all">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                            <rect x="2" y="5" width="20" height="14" rx="2"></rect>
                                            <line x1="2" y1="10" x2="22" y2="10"></line>
                                        </svg>
                                        <span>Lihat Tagihan</span>
                                    </a>
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-16 text-center text-slate-400">
                                    <div class="flex flex-col items-center gap-3">
                                        <div class="w-14 h-14 rounded-full bg-slate-50 border border-slate-100 flex items-center justify-center">
                                            <svg class="w-7 h-7 text-slate-300" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                                <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
                                            </svg>
                                        </div>
                                        <p class="text-sm font-bold text-slate-600">Tidak ada data siswa ditemukan.</p>
                                        <p class="text-xs text-slate-400">Ubah filter pencarian atau sinkronisasikan data kesiswaan.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- PAGINATION -->
            @if($students->hasPages())
                <div class="px-6 py-4 border-t border-slate-200 bg-slate-50/50">
                    {{ $students->links() }}
                </div>
            @endif
        </div>

    </div>
</x-app-layout>
