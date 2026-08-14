<x-app-layout>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <div class="bg-gradient-to-br from-slate-50 to-blue-50 min-h-screen pb-12">
        
        <div class="bg-white border-b border-slate-200 px-8 py-8 shadow-lg relative overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-r from-blue-50/50 to-transparent pointer-events-none"></div>
            
            <div class="flex flex-col md:flex-row justify-between items-center gap-6 relative z-10">
                <div class="flex items-center gap-4">
                    <div class="relative">
                        <div class="absolute inset-0 bg-gradient-to-r from-[#0284c7] to-blue-400 rounded-xl blur-lg opacity-30"></div>
                        <div class="relative bg-gradient-to-br from-[#0284c7] to-blue-600 p-3 rounded-xl shadow-lg">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                        </div>
                    </div>
                    <div>
                        <h2 class="text-3xl font-bold text-slate-800 tracking-tight">Monitoring Keuangan</h2>
                        <p class="text-sm text-slate-500 mt-1">Rekapitulasi tagihan, pembayaran, dan tunggakan siswa</p>
                    </div>
                </div>
                
                <div class="flex gap-3">
                    <a href="{{ route('bills.export', request()->all()) }}" class="relative group">
                        <div class="absolute inset-0 bg-gradient-to-r from-emerald-600 to-emerald-500 rounded-xl blur opacity-75 group-hover:opacity-100 transition-opacity"></div>
                        <div class="relative flex items-center gap-3 bg-gradient-to-r from-emerald-600 to-emerald-500 hover:from-emerald-700 hover:to-emerald-600 text-white px-6 py-3 rounded-xl font-semibold text-sm shadow-lg shadow-emerald-200/50 transition-all transform group-hover:-translate-y-0.5">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                            Export Data
                        </div>
                    </a>
                    <a href="{{ route('bills.create') }}" class="relative group">
                        <div class="absolute inset-0 bg-gradient-to-r from-[#0284c7] to-blue-500 rounded-xl blur opacity-75 group-hover:opacity-100 transition-opacity"></div>
                        <div class="relative flex items-center gap-3 bg-gradient-to-r from-[#0284c7] to-blue-500 hover:from-blue-600 hover:to-[#0284c7] text-white px-6 py-3 rounded-xl font-semibold text-sm shadow-lg shadow-blue-200/50 transition-all transform group-hover:-translate-y-0.5">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                            Buat Tagihan
                        </div>
                    </a>
                </div>
            </div>
        </div>

        <div class="px-8 mt-8">
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-10">
                <div class="bg-gradient-to-br from-white to-slate-50 rounded-2xl p-8 border border-slate-100 shadow-lg hover:shadow-2xl transition-all duration-300 group relative overflow-hidden">
                    <div class="absolute -inset-0.5 bg-gradient-to-r from-blue-500 to-blue-400 rounded-2xl opacity-0 group-hover:opacity-10 blur transition duration-500"></div>
                    <div class="flex items-center justify-between relative">
                        <div>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Total Tagihan</p>
                            <h3 class="text-3xl font-extrabold text-slate-800 tracking-tight">Rp {{ number_format($totalTagihan, 0, ',', '.') }}</h3>
                            <p class="text-xs text-slate-400 mt-2">Periode saat ini</p>
                        </div>
                        <div class="relative">
                            <div class="absolute inset-0 bg-gradient-to-r from-blue-500 to-blue-400 rounded-2xl blur opacity-30 group-hover:opacity-50 transition duration-300"></div>
                            <div class="relative p-4 bg-gradient-to-br from-blue-500 to-blue-400 text-white rounded-2xl group-hover:scale-110 transition-transform duration-300 shadow-lg shadow-blue-200">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                            </div>
                        </div>
                    </div>
                    <div class="relative mt-6 pt-6 border-t border-slate-100">
                        <div class="flex items-center gap-2 text-xs text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            {{ now()->format('d M Y') }}
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-white to-slate-50 rounded-2xl p-8 border border-slate-100 shadow-lg hover:shadow-2xl transition-all duration-300 group relative overflow-hidden">
                    <div class="absolute -inset-0.5 bg-gradient-to-r from-emerald-500 to-emerald-400 rounded-2xl opacity-0 group-hover:opacity-10 blur transition duration-500"></div>
                    <div class="flex items-center justify-between relative">
                        <div>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Uang Masuk</p>
                            <h3 class="text-3xl font-extrabold text-emerald-600 tracking-tight">Rp {{ number_format($totalSudahBayar, 0, ',', '.') }}</h3>
                            <p class="text-xs text-slate-400 mt-2">Pembayaran lunas</p>
                        </div>
                        <div class="relative">
                            <div class="absolute inset-0 bg-gradient-to-r from-emerald-500 to-emerald-400 rounded-2xl blur opacity-30 group-hover:opacity-50 transition duration-300"></div>
                            <div class="relative p-4 bg-gradient-to-br from-emerald-500 to-emerald-400 text-white rounded-2xl group-hover:scale-110 transition-transform duration-300 shadow-lg shadow-emerald-200">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                        </div>
                    </div>
                    <div class="relative mt-6 pt-6 border-t border-slate-100">
                        <div class="flex items-center gap-2 text-xs text-slate-400">
                            <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                            {{ $totalTagihan > 0 ? number_format(($totalSudahBayar/$totalTagihan)*100, 0) : 0 }}% dari total
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-white to-slate-50 rounded-2xl p-8 border border-slate-100 shadow-lg hover:shadow-2xl transition-all duration-300 group relative overflow-hidden">
                    <div class="absolute -inset-0.5 bg-gradient-to-r from-rose-500 to-rose-400 rounded-2xl opacity-0 group-hover:opacity-10 blur transition duration-500"></div>
                    <div class="flex items-center justify-between relative">
                        <div>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Sisa Tunggakan</p>
                            <h3 class="text-3xl font-extrabold text-rose-600 tracking-tight">Rp {{ number_format($totalTunggakan, 0, ',', '.') }}</h3>
                            <p class="text-xs text-slate-400 mt-2">Belum dibayar</p>
                        </div>
                        <div class="relative">
                            <div class="absolute inset-0 bg-gradient-to-r from-rose-500 to-rose-400 rounded-2xl blur opacity-30 group-hover:opacity-50 transition duration-300"></div>
                            <div class="relative p-4 bg-gradient-to-br from-rose-500 to-rose-400 text-white rounded-2xl group-hover:scale-110 transition-transform duration-300 shadow-lg shadow-rose-200">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                            </div>
                        </div>
                    </div>
                    <div class="relative mt-6 pt-6 border-t border-slate-100">
                        <div class="flex items-center gap-2 text-xs text-slate-400">
                            <svg class="w-4 h-4 text-rose-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path></svg>
                            {{ $bills->where('status', 'UNPAID')->count() }} tagihan tertunda
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-br from-white to-slate-50 p-8 rounded-2xl shadow-lg border border-slate-200 mb-8 relative overflow-hidden">
                <div class="absolute top-0 right-0 -mt-8 -mr-8 w-40 h-40 bg-gradient-to-br from-[#0284c7]/5 to-transparent rounded-full blur-xl"></div>
                
                <h3 class="text-lg font-bold text-slate-800 mb-6 flex items-center gap-3">
                    <svg class="w-6 h-6 text-[#0284c7]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                    Filter & Pencarian Data
                </h3>
                
                <form method="GET" action="{{ route('bills.index') }}">
                    <div class="grid grid-cols-1 md:grid-cols-6 gap-6 items-end">
                        
                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-slate-600 mb-2 flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                Rentang Tanggal
                            </label>
                            <div class="flex items-center gap-3">
                                <div class="flex-1 relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    </div>
                                    <input type="date" name="start_date" value="{{ request('start_date') }}" class="w-full pl-10 rounded-xl border-slate-300 text-sm focus:ring-[#0284c7] focus:border-[#0284c7] text-slate-600 bg-white shadow-sm py-3">
                                </div>
                                <span class="text-slate-400 font-bold">→</span>
                                <div class="flex-1 relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    </div>
                                    <input type="date" name="end_date" value="{{ request('end_date') }}" class="w-full pl-10 rounded-xl border-slate-300 text-sm focus:ring-[#0284c7] focus:border-[#0284c7] text-slate-600 bg-white shadow-sm py-3">
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-600 mb-2">Kelas</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                </div>
                                <select name="class_name" class="w-full pl-10 rounded-xl border-slate-300 text-sm focus:ring-[#0284c7] text-slate-600 bg-white shadow-sm py-3">
                                    <option value="">Semua Kelas</option>
                                    @foreach($classes as $c)
                                        <option value="{{ $c }}" {{ request('class_name') == $c ? 'selected' : '' }}>{{ $c }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-600 mb-2">Jenis Tagihan</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                                </div>
                                <select name="type" class="w-full pl-10 rounded-xl border-slate-300 text-sm focus:ring-[#0284c7] text-slate-600 bg-white shadow-sm py-3">
                                    <option value="">Semua Tipe</option>
                                    @foreach($types as $t)
                                        <option value="{{ $t }}" {{ request('type') == $t ? 'selected' : '' }}>{{ $t }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-slate-600 mb-2">Status</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                </div>
                                <select name="status" class="w-full pl-10 rounded-xl border-slate-300 text-sm focus:ring-[#0284c7] text-slate-600 bg-white shadow-sm py-3">
                                    <option value="">Semua Status</option>
                                    <option value="UNPAID" {{ request('status') == 'UNPAID' ? 'selected' : '' }}>Belum Bayar</option>
                                    <option value="PAID" {{ request('status') == 'PAID' ? 'selected' : '' }}>Lunas</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <button type="submit" class="w-full bg-gradient-to-r from-slate-800 to-slate-700 hover:from-slate-900 hover:to-slate-800 text-white py-3.5 rounded-xl text-sm font-bold shadow-lg transition-all transform hover:-translate-y-0.5 flex justify-center items-center gap-3 group">
                                <svg class="w-4 h-4 group-hover:rotate-12 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                Terapkan Filter
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="bg-gradient-to-br from-white to-slate-50 rounded-2xl shadow-lg border border-slate-200 overflow-hidden mb-8">
                <div class="px-8 py-6 border-b border-slate-200 bg-gradient-to-r from-slate-50 to-white">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-bold text-slate-800 flex items-center gap-3">
                            <svg class="w-6 h-6 text-[#0284c7]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                            Daftar Tagihan
                        </h3>
                        <div class="text-sm text-slate-500 bg-slate-100 px-3 py-1.5 rounded-lg">
                            Total: <span class="font-bold text-[#0284c7]">{{ $bills->total() }}</span> data
                        </div>
                    </div>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-gradient-to-r from-slate-50 to-blue-50 border-b border-slate-200 text-sm uppercase text-slate-600 font-bold tracking-wider">
                                <th class="px-8 py-4 text-left">Siswa</th>
                                <th class="px-8 py-4 text-left">Detail Tagihan</th>
                                <th class="px-8 py-4 text-right">Nominal</th>
                                <th class="px-8 py-4 text-center">Jatuh Tempo</th>
                                <th class="px-8 py-4 text-center">Status</th>
                                <th class="px-8 py-4 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($bills as $bill)
                            <tr class="hover:bg-blue-50/30 transition-colors duration-200 group">
                                <td class="px-8 py-5">
                                    <div class="flex items-center gap-4">
                                        <div class="relative">
                                            <div class="absolute inset-0 bg-gradient-to-br from-blue-500 to-blue-400 rounded-full blur opacity-20 group-hover:opacity-30 transition"></div>
                                            <div class="relative w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-blue-400 text-white flex items-center justify-center font-bold text-sm shadow-md">
                                                {{ substr($bill->student->name, 0, 1) }}
                                            </div>
                                        </div>
                                        <div>
                                            <div class="font-bold text-slate-800 text-sm">{{ $bill->student->name }}</div>
                                            <div class="text-xs text-slate-400 mt-0.5 flex items-center gap-1">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                                {{ $bill->student->class_name }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                
                                <td class="px-8 py-5">
                                    <div class="text-sm text-slate-800 font-semibold">{{ $bill->name }}</div>
                                    <div class="text-xs text-slate-400 mt-1 flex items-center gap-2">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        Dibuat: {{ $bill->created_at->format('d M Y') }}
                                    </div>
                                </td>

                                <td class="px-8 py-5 text-right">
                                    <span class="font-bold text-lg text-slate-800">Rp {{ number_format($bill->amount, 0, ',', '.') }}</span>
                                </td>

                                <td class="px-8 py-5 text-center">
                                    @if($bill->due_date && $bill->status == 'UNPAID')
                                        @php $isOverdue = \Carbon\Carbon::parse($bill->due_date)->isPast(); @endphp
                                        <div class="inline-flex items-center gap-2 px-3 py-2 rounded-xl text-sm font-medium {{ $isOverdue ? 'bg-gradient-to-r from-rose-50 to-rose-100 text-rose-700 border border-rose-200 shadow-sm' : 'bg-gradient-to-r from-slate-50 to-slate-100 text-slate-600 border border-slate-200 shadow-sm' }}">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                            {{ \Carbon\Carbon::parse($bill->due_date)->format('d M Y') }}
                                            @if($isOverdue)
                                                <span class="ml-1 px-2 py-0.5 bg-rose-100 text-rose-700 text-xs rounded-lg font-bold">TERLAMBAT</span>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-slate-300 text-sm">-</span>
                                    @endif
                                </td>

                                <td class="px-8 py-5 text-center">
                                    @if($bill->status == 'PAID')
                                        <span class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-bold bg-gradient-to-r from-emerald-50 to-emerald-100 text-emerald-700 border border-emerald-200 shadow-sm">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                            LUNAS
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-bold bg-gradient-to-r from-slate-50 to-slate-100 text-slate-500 border border-slate-200 shadow-sm">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            BELUM
                                        </span>
                                    @endif
                                </td>

                                <td class="px-8 py-5 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        @if($bill->status == 'UNPAID')
                                            <form action="{{ route('bills.pay', $bill->id) }}" method="POST" class="pay-form">
                                                @csrf @method('POST')
                                                <button type="button" class="btn-pay group bg-gradient-to-r from-blue-50 to-blue-100 hover:from-blue-600 hover:to-blue-500 text-blue-600 hover:text-white p-3 rounded-xl transition-all duration-300 border border-blue-200 hover:border-blue-600 shadow-sm hover:shadow-lg transform hover:-translate-y-0.5">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                                </button>
                                            </form>

                                            <form action="{{ route('bills.destroy', $bill->id) }}" method="POST" class="delete-form">
                                                @csrf @method('DELETE')
                                                <button type="button" class="btn-delete group bg-gradient-to-r from-white to-slate-50 hover:from-rose-600 hover:to-rose-500 text-rose-500 hover:text-white p-3 rounded-xl transition-all duration-300 border border-rose-200 hover:border-rose-500 shadow-sm hover:shadow-lg transform hover:-translate-y-0.5">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                </button>
                                            </form>
                                        @else
                                            <a href="{{ route('bills.print', $bill->id) }}" target="_blank" class="bg-gradient-to-r from-slate-50 to-slate-100 hover:from-slate-200 hover:to-slate-100 text-slate-600 p-3 rounded-xl transition-all duration-300 border border-slate-200 shadow-sm hover:shadow-lg transform hover:-translate-y-0.5" title="Cetak Kwitansi">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="px-8 py-16 text-center">
                                    <div class="flex flex-col items-center justify-center max-w-md mx-auto">
                                        <div class="relative mb-6">
                                            <div class="absolute inset-0 bg-gradient-to-r from-slate-200 to-slate-100 rounded-full blur-xl opacity-50"></div>
                                            <div class="relative w-24 h-24 rounded-full bg-gradient-to-br from-slate-100 to-white border border-slate-200 flex items-center justify-center shadow-lg">
                                                <svg class="w-12 h-12 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                            </div>
                                        </div>
                                        <h4 class="text-lg font-bold text-slate-600 mb-2">Tidak ada data ditemukan</h4>
                                        <p class="text-sm text-slate-400 mb-6">Coba sesuaikan filter tanggal atau buat tagihan baru.</p>
                                        <a href="{{ route('bills.create') }}" class="inline-flex items-center gap-2 bg-gradient-to-r from-[#0284c7] to-blue-500 hover:from-blue-600 hover:to-[#0284c7] text-white px-6 py-3 rounded-xl font-semibold text-sm shadow-lg shadow-blue-200/50 transition-all transform hover:-translate-y-0.5">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                            Buat Tagihan Baru
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-8">
                {{ $bills->links() }}
            </div>
        </div>
    </div>

    <script>
        // SweetAlert Konfirmasi Bayar
        document.querySelectorAll('.btn-pay').forEach(btn => {
            btn.addEventListener('click', function() {
                const form = this.closest('.pay-form');
                Swal.fire({
                    title: 'Konfirmasi Pembayaran',
                    text: "Sistem akan mencatat pelunasan dan memotong stok barang (jika ada).",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#0284c7',
                    cancelButtonColor: '#94a3b8',
                    confirmButtonText: 'Ya, Proses Pembayaran',
                    cancelButtonText: 'Batal',
                    background: '#ffffff',
                    backdrop: 'rgba(2, 132, 199, 0.1)'
                }).then((result) => {
                    if (result.isConfirmed) form.submit();
                });
            });
        });

        // SweetAlert Konfirmasi Hapus
        document.querySelectorAll('.btn-delete').forEach(btn => {
            btn.addEventListener('click', function() {
                const form = this.closest('.delete-form');
                Swal.fire({
                    title: 'Hapus Tagihan?',
                    text: "Data ini akan dihapus permanen. Tindakan ini tidak dapat dibatalkan.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#e11d48',
                    cancelButtonColor: '#94a3b8',
                    confirmButtonText: 'Ya, Hapus Permanen',
                    cancelButtonText: 'Batal',
                    background: '#ffffff',
                    backdrop: 'rgba(225, 29, 72, 0.1)'
                }).then((result) => {
                    if (result.isConfirmed) form.submit();
                });
            });
        });

        // Notifikasi Toast
        @if(session('success'))
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                background: '#ffffff',
                iconColor: '#0284c7',
                color: '#1e293b',
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer);
                    toast.addEventListener('mouseleave', Swal.resumeTimer);
                }
            });
            Toast.fire({
                icon: 'success',
                title: "{{ session('success') }}"
            });
        @endif
        
        @if(session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Terjadi Kesalahan',
                text: "{{ session('error') }}",
                confirmButtonColor: '#0284c7',
                background: '#ffffff'
            });
        @endif
    </script>
</x-app-layout>