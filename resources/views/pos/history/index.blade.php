<x-app-layout>
    <x-slot name="header">
        Riwayat Transaksi POS
    </x-slot>

    <div class="space-y-6">
        
        <!-- HEADER & NAVIGATION -->
        <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
            <div>
                <h1 class="flex items-center gap-3 text-2xl font-black text-slate-800">
                    <div class="bg-[#178BC1] p-2.5 rounded-xl text-white shadow-sm shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                            <line x1="16" y1="13" x2="8" y2="13"></line>
                            <line x1="16" y1="17" x2="8" y2="17"></line>
                        </svg>
                    </div>
                    Riwayat Transaksi Kasir POS
                </h1>
                <p class="mt-1 text-xs text-slate-500 font-medium">
                    Pantau arus transaksi harian, pelunasan kasir, dan piutang belanja siswa.
                </p>
            </div>
            
            <a href="{{ route('pos.transaction') }}" 
               class="inline-flex items-center gap-2 bg-[#178BC1] hover:bg-[#126da3] text-white px-4 py-2.5 rounded-xl font-bold text-xs shadow-sm transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                    <circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle>
                    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                </svg>
                <span>Buka Kasir POS →</span>
            </a>
        </div>

        <!-- STAT CARDS (SOLID - NO GRADIENTS) -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
            <div class="p-6 bg-white border shadow-sm rounded-2xl border-slate-100 flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Penjualan</p>
                    <h3 class="text-2xl font-bold text-slate-800 mt-1">
                        Rp {{ number_format($totalOmset, 0, ',', '.') }}
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5 font-medium">Periode filter aktif</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-blue-50 text-[#178BC1] border border-blue-100 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                        <line x1="12" y1="1" x2="12" y2="23"></line>
                        <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                    </svg>
                </div>
            </div>

            <div class="p-6 bg-white border shadow-sm rounded-2xl border-slate-100 flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Uang Masuk (Cash)</p>
                    <h3 class="text-2xl font-bold text-emerald-600 mt-1">
                        Rp {{ number_format($totalCashIn, 0, ',', '.') }}
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5 font-medium">Pelunasan di laci kasir</p>
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
                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Piutang Belanja</p>
                    <h3 class="text-2xl font-bold text-purple-700 mt-1">
                        Rp {{ number_format($totalUnpaid, 0, ',', '.') }}
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5 font-medium">Hutang kantin belum lunas</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-700 border border-purple-200 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="12 6 12 12 16 14"></polyline>
                    </svg>
                </div>
            </div>
        </div>

        <!-- FILTER ZONE (SPMB MATCHING) -->
        <div class="overflow-hidden bg-white border shadow-sm rounded-2xl border-slate-200">
            <form method="GET" action="{{ route('pos.history.index') }}" class="p-4 flex flex-wrap items-center gap-3">
                <div class="w-full sm:w-40 shrink-0">
                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Dari Tanggal</label>
                    <input type="date" 
                           name="start_date" 
                           value="{{ $startDate }}" 
                           class="w-full px-3 py-2 text-xs font-medium text-slate-800 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#178BC1]/20 focus:border-[#178BC1] transition-all">
                </div>

                <div class="w-full sm:w-40 shrink-0">
                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Sampai Tanggal</label>
                    <input type="date" 
                           name="end_date" 
                           value="{{ $endDate }}" 
                           class="w-full px-3 py-2 text-xs font-medium text-slate-800 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#178BC1]/20 focus:border-[#178BC1] transition-all">
                </div>

                <div class="w-full sm:w-40 shrink-0">
                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Status Pembayaran</label>
                    <select name="status" 
                            class="w-full px-3 py-2 text-xs font-semibold text-slate-700 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#178BC1]/20 focus:border-[#178BC1] transition-all cursor-pointer">
                        <option value="">Semua Status</option>
                        <option value="PAID" {{ request('status') === 'PAID' ? 'selected' : '' }}>Lunas (Paid)</option>
                        <option value="UNPAID" {{ request('status') === 'UNPAID' ? 'selected' : '' }}>Hutang (Unpaid)</option>
                    </select>
                </div>

                <div class="flex items-end gap-2 mt-auto pt-4 sm:pt-0">
                    <button type="submit" 
                            class="flex items-center gap-1.5 bg-[#178BC1] hover:bg-[#126da3] text-white px-4 py-2 rounded-xl text-xs font-bold shadow-sm transition-all">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
                        </svg>
                        <span>Filter</span>
                    </button>

                    @if(request('start_date') || request('end_date') || request('status'))
                        <a href="{{ route('pos.history.index') }}" 
                           class="flex items-center gap-1 px-3 py-2 text-xs font-bold text-rose-600 bg-rose-50 hover:bg-rose-100 rounded-xl border border-rose-200 transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                <line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line>
                            </svg>
                            <span>Reset</span>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- TABLE TRANSAKSI (SPMB MATCHING THEAD & TBODY) -->
        <div class="overflow-hidden bg-white border shadow-sm rounded-2xl border-slate-200">
            <div class="overflow-x-auto min-h-[400px]">
                <table class="w-full text-sm text-left">
                    <thead class="bg-slate-50/80 text-slate-500 uppercase font-bold text-[10px] tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-4">Kode & Kasir</th>
                            <th class="px-6 py-4">Waktu Transaksi</th>
                            <th class="px-6 py-4">Siswa / Pelanggan</th>
                            <th class="px-6 py-4">Total Belanja</th>
                            <th class="px-6 py-4 text-center">Status</th>
                            <th class="px-6 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($transactions as $trx)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                
                                <td class="px-6 py-4">
                                    <p class="font-mono font-bold text-xs text-slate-800">#{{ $trx->transaction_code }}</p>
                                    <p class="text-[11px] text-slate-400 mt-0.5">Kasir: {{ $trx->user->name ?? 'Admin TU' }}</p>
                                </td>

                                <td class="px-6 py-4 text-xs text-slate-600">
                                    <p class="font-bold text-slate-800">{{ $trx->created_at->format('d M Y') }}</p>
                                    <p class="text-[11px] text-slate-400 mt-0.5">{{ $trx->created_at->format('H:i') }} WIB</p>
                                </td>

                                <td class="px-6 py-4">
                                    @if($trx->student)
                                        <div class="flex items-center gap-2.5">
                                            <div class="w-7 h-7 rounded-full bg-purple-50 text-purple-700 font-bold border border-purple-200 text-xs flex items-center justify-center shrink-0">
                                                {{ strtoupper(substr($trx->student->name, 0, 1)) }}
                                            </div>
                                            <div>
                                                <p class="text-xs font-bold text-slate-800">{{ $trx->student->name }}</p>
                                                <p class="text-[10px] text-slate-400">Kelas {{ $trx->student->class ?? ($trx->student->class_name ?? '-') }}</p>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-xs text-slate-400 font-medium">Pelanggan Umum (Tunai)</span>
                                    @endif
                                </td>

                                <td class="px-6 py-4 font-bold text-slate-800 text-xs">
                                    Rp {{ number_format($trx->total_amount, 0, ',', '.') }}
                                </td>

                                <td class="px-6 py-4 text-center">
                                    @if($trx->payment_status === 'PAID')
                                        <span class="px-2.5 py-0.5 rounded-lg text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 uppercase tracking-wider">
                                            Lunas
                                        </span>
                                    @else
                                        <span class="px-2.5 py-0.5 rounded-lg text-[10px] font-bold bg-purple-50 text-purple-700 border border-purple-200 uppercase tracking-wider">
                                            Hutang
                                        </span>
                                    @endif
                                </td>

                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        @if($trx->payment_status === 'UNPAID')
                                            <form method="POST" action="{{ route('pos.history.repay', $trx->id) }}">
                                                @csrf
                                                <button type="submit" 
                                                        class="bg-emerald-600 hover:bg-emerald-700 text-white px-2.5 py-1 rounded-lg text-[10px] font-bold shadow-sm transition-all">
                                                    Pelunasan
                                                </button>
                                            </form>
                                        @endif

                                        <a href="{{ route('pos.transaction.print', $trx->id) }}" 
                                           target="_blank"
                                           class="text-slate-600 hover:text-slate-900 bg-slate-100 hover:bg-slate-200 p-1.5 rounded-lg border border-slate-200 transition-colors"
                                           title="Cetak Struk">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                                <polyline points="6 9 6 2 18 2 18 9"></polyline>
                                                <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                                                <rect x="6" y="14" width="12" height="8"></rect>
                                            </svg>
                                        </a>
                                    </div>
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-16 text-center text-slate-400">
                                    <p class="text-sm font-bold text-slate-600">Tidak ada riwayat transaksi kasir.</p>
                                    <p class="text-xs text-slate-400 mt-1">Ubah filter periode tanggal atau lakukan transaksi di kasir POS.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- PAGINATION -->
            @if($transactions->hasPages())
                <div class="px-6 py-4 border-t border-slate-200 bg-slate-50/50">
                    {{ $transactions->links() }}
                </div>
            @endif
        </div>

    </div>
</x-app-layout>
