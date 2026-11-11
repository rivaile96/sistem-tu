<x-app-layout>
    <x-slot name="header">
        Daftar & Monitoring Tagihan
    </x-slot>

    <div class="space-y-6">
        
        <!-- HEADER & ACTION BUTTONS (SPMB MATCHING) -->
        <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
            <div>
                <h1 class="flex items-center gap-3 text-2xl font-black text-slate-800">
                    <div class="bg-[#178BC1] p-2.5 rounded-xl text-white shadow-sm shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <rect x="2" y="5" width="20" height="14" rx="2"></rect>
                            <line x1="2" y1="10" x2="22" y2="10"></line>
                        </svg>
                    </div>
                    Daftar & Rekapitulasi Tagihan
                </h1>
                <p class="mt-1 text-xs text-slate-500 font-medium">
                    Monitoring status tagihan siswa, rekonsiliasi pembayaran DOKU Sandbox, dan pelunasan kasir.
                </p>
            </div>
            
            <div class="flex items-center gap-2.5 w-full md:w-auto">
                <a href="{{ route('bills.export', request()->all()) }}" 
                   class="flex-1 md:flex-none flex items-center justify-center gap-2 bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 px-4 py-2.5 rounded-xl font-bold text-xs shadow-sm transition-all">
                    <svg class="w-4 h-4 text-[#178BC1]" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                        <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                        <polyline points="7 10 12 15 17 10"></polyline>
                        <line x1="12" y1="15" x2="12" y2="3"></line>
                    </svg>
                    <span>Export Excel</span>
                </a>
                
                <a href="{{ route('bills.create') }}" 
                   class="flex-1 md:flex-none flex items-center justify-center gap-2 bg-[#178BC1] hover:bg-[#126da3] text-white px-4 py-2.5 rounded-xl font-bold text-xs shadow-sm transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    <span>Buat Tagihan Baru</span>
                </a>
            </div>
        </div>

        <!-- STAT CARDS (SOLID - NO GRADIENTS) -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
            <div class="p-6 bg-white border shadow-sm rounded-2xl border-slate-100 flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Tagihan Terbit</p>
                    <h3 class="text-2xl font-bold text-slate-800 mt-1">
                        Rp {{ number_format($totalTagihan, 0, ',', '.') }}
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5 font-medium">Akumulasi seluruh tagihan</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-blue-50 text-[#178BC1] border border-blue-100 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                        <line x1="16" y1="13" x2="8" y2="13"></line>
                        <line x1="16" y1="17" x2="8" y2="17"></line>
                    </svg>
                </div>
            </div>

            <div class="p-6 bg-white border shadow-sm rounded-2xl border-slate-100 flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Uang Masuk (Lunas)</p>
                    <h3 class="text-2xl font-bold text-emerald-600 mt-1">
                        Rp {{ number_format($totalSudahBayar, 0, ',', '.') }}
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5 font-medium">Pembayaran berhasil diverifikasi</p>
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
                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Sisa Piutang (Belum Bayar)</p>
                    <h3 class="text-2xl font-bold text-amber-600 mt-1">
                        Rp {{ number_format($totalTunggakan ?? 0, 0, ',', '.') }}
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5 font-medium">Menunggu pembayaran</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 border border-amber-100 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10"></circle>
                        <polyline points="12 6 12 12 16 14"></polyline>
                    </svg>
                </div>
            </div>
        </div>

        <!-- FILTER ZONE (SPMB MATCHING) -->
        <div class="overflow-hidden bg-white border shadow-sm rounded-2xl border-slate-200">
            <form method="GET" action="{{ route('bills.index') }}" class="p-4 flex flex-wrap items-center gap-3">
                
                <!-- Tanggal Mulai -->
                <div class="w-full sm:w-40 shrink-0">
                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Dari Tanggal</label>
                    <input type="date" 
                           name="start_date" 
                           value="{{ request('start_date') }}" 
                           class="w-full px-3 py-2 text-xs font-medium text-slate-800 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#178BC1]/20 focus:border-[#178BC1] transition-all" />
                </div>

                <!-- Tanggal Akhir -->
                <div class="w-full sm:w-40 shrink-0">
                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Sampai Tanggal</label>
                    <input type="date" 
                           name="end_date" 
                           value="{{ request('end_date') }}" 
                           class="w-full px-3 py-2 text-xs font-medium text-slate-800 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#178BC1]/20 focus:border-[#178BC1] transition-all" />
                </div>

                <!-- Status Filter -->
                <div class="w-full sm:w-40 shrink-0">
                    <label class="block text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1">Status Tagihan</label>
                    <select name="status" 
                            class="w-full px-3 py-2 text-xs font-semibold text-slate-700 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#178BC1]/20 focus:border-[#178BC1] transition-all cursor-pointer">
                        <option value="">Semua Status</option>
                        <option value="PAID" {{ request('status') === 'PAID' ? 'selected' : '' }}>Lunas (Paid)</option>
                        <option value="UNPAID" {{ request('status') === 'UNPAID' ? 'selected' : '' }}>Belum Bayar (Unpaid)</option>
                    </select>
                </div>

                <!-- Buttons -->
                <div class="flex items-end gap-2 mt-auto pt-4 sm:pt-0">
                    <button type="submit" 
                            class="flex items-center gap-1.5 bg-[#178BC1] hover:bg-[#126da3] text-white px-4 py-2 rounded-xl text-xs font-bold shadow-sm transition-all">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon>
                        </svg>
                        <span>Terapkan Filter</span>
                    </button>

                    @if(request('start_date') || request('end_date') || request('status'))
                        <a href="{{ route('bills.index') }}" 
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
            <div class="overflow-x-auto min-h-[400px]">
                <table class="w-full text-sm text-left">
                    <thead class="bg-slate-50/80 text-slate-500 uppercase font-bold text-[10px] tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-4">Siswa & Kelas</th>
                            <th class="px-6 py-4">Kewajiban Tagihan</th>
                            <th class="px-6 py-4">Nominal</th>
                            <th class="px-6 py-4">Status & Kanal</th>
                            <th class="px-6 py-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($bills as $bill)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                
                                <!-- Siswa & Kelas -->
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-full bg-blue-50 border border-blue-200 text-[#178BC1] font-bold flex items-center justify-center shrink-0 text-xs">
                                            {{ strtoupper(substr($bill->student->name ?? 'S', 0, 1)) }}
                                        </div>
                                        <div>
                                            <p class="text-xs font-bold text-slate-800">{{ $bill->student->name ?? 'Siswa Terhapus' }}</p>
                                            <p class="text-[11px] text-slate-400 mt-0.5 font-medium">
                                                Kelas {{ $bill->student->class ?? ($bill->student->class_name ?? '-') }}
                                            </p>
                                        </div>
                                    </div>
                                </td>

                                <!-- Kewajiban Tagihan -->
                                <td class="px-6 py-4">
                                    <p class="text-xs font-bold text-slate-800">{{ $bill->name }}</p>
                                    <div class="flex items-center gap-2 mt-0.5">
                                        <span class="text-[10px] font-bold text-blue-700 bg-blue-50 border border-blue-200 px-1.5 py-0.2 rounded">
                                            {{ $bill->type }}
                                        </span>
                                        @if($bill->due_date)
                                            <span class="text-[11px] text-slate-400">
                                                Tempo: {{ \Carbon\Carbon::parse($bill->due_date)->format('d M Y') }}
                                            </span>
                                        @endif
                                    </div>
                                </td>

                                <!-- Nominal -->
                                <td class="px-6 py-4 font-bold text-slate-800 text-xs">
                                    Rp {{ number_format($bill->amount, 0, ',', '.') }}
                                </td>

                                <!-- Status & Kanal -->
                                <td class="px-6 py-4">
                                    <div class="flex flex-col gap-1">
                                        <div>
                                            @if($bill->status === 'PAID')
                                                <span class="px-2.5 py-0.5 rounded-lg text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 uppercase tracking-wider inline-block">
                                                    Lunas
                                                </span>
                                            @else
                                                <span class="px-2.5 py-0.5 rounded-lg text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200 uppercase tracking-wider inline-block">
                                                    Belum Lunas
                                                </span>
                                            @endif
                                        </div>

                                        @if($bill->payment_method)
                                            <span class="text-[10px] text-slate-500 font-medium">
                                                Kanal: <span class="font-bold text-slate-700">{{ $bill->payment_method }}</span>
                                            </span>
                                        @endif
                                    </div>
                                </td>

                                <!-- Aksi -->
                                <td class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        @if($bill->status === 'UNPAID')
                                            <!-- Bayar Tunai Button -->
                                            <form method="POST" action="{{ route('bills.pay', $bill->id) }}" class="pay-form">
                                                @csrf
                                                <button type="button" 
                                                        class="btn-pay bg-[#178BC1] hover:bg-[#126da3] text-white px-3 py-1.5 rounded-xl text-xs font-bold shadow-sm transition-all flex items-center gap-1">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                                        <rect x="2" y="6" width="20" height="12" rx="2"></rect>
                                                        <circle cx="12" cy="12" r="2"></circle>
                                                    </svg>
                                                    <span>Bayar Tunai</span>
                                                </button>
                                            </form>
                                        @else
                                            <!-- Cetak Kwitansi Button -->
                                            <a href="{{ route('bills.print', $bill->id) }}" 
                                               target="_blank"
                                               class="bg-slate-100 hover:bg-slate-200 text-slate-700 border border-slate-200 px-3 py-1.5 rounded-xl text-xs font-bold shadow-sm transition-all flex items-center gap-1"
                                               title="Cetak Kwitansi">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                                    <polyline points="6 9 6 2 18 2 18 9"></polyline>
                                                    <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                                                    <rect x="6" y="14" width="12" height="8"></rect>
                                                </svg>
                                                <span>Kwitansi</span>
                                            </a>
                                        @endif

                                        <!-- Hapus Button -->
                                        <form method="POST" action="{{ route('bills.destroy', $bill->id) }}" class="delete-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" 
                                                    class="btn-delete text-rose-600 hover:bg-rose-50 p-1.5 rounded-xl border border-transparent hover:border-rose-200 transition-colors"
                                                    title="Hapus Tagihan">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                                    <polyline points="3 6 5 6 21 6"></polyline>
                                                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-16 text-center text-slate-400">
                                    <div class="flex flex-col items-center gap-3">
                                        <div class="w-14 h-14 rounded-full bg-slate-50 border border-slate-100 flex items-center justify-center">
                                            <svg class="w-7 h-7 text-slate-300" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                                <rect x="2" y="5" width="20" height="14" rx="2"></rect><line x1="2" y1="10" x2="22" y2="10"></line>
                                            </svg>
                                        </div>
                                        <p class="text-sm font-bold text-slate-600">Tidak ada tagihan yang sesuai filter.</p>
                                        <p class="text-xs text-slate-400">Silakan sesuaikan tanggal pencarian atau buat tagihan baru.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- PAGINATION -->
            @if($bills->hasPages())
                <div class="px-6 py-4 border-t border-slate-200 bg-slate-50/50">
                    {{ $bills->links() }}
                </div>
            @endif
        </div>

    </div>

    <!-- SCRIPTS KONFIRMASI -->
    <script>
        document.querySelectorAll('.btn-pay').forEach(btn => {
            btn.addEventListener('click', function() {
                const form = this.closest('.pay-form');
                Swal.fire({
                    title: 'Proses Pembayaran Tunai?',
                    text: 'Sistem akan mencatat tagihan ini sebagai LUNAS melalui loket kasir TU.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#178BC1',
                    cancelButtonColor: '#94a3b8',
                    confirmButtonText: 'Ya, Terima Pembayaran',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) form.submit();
                });
            });
        });

        document.querySelectorAll('.btn-delete').forEach(btn => {
            btn.addEventListener('click', function() {
                const form = this.closest('.delete-form');
                Swal.fire({
                    title: 'Hapus Tagihan?',
                    text: 'Tagihan ini akan dihapus permanen dari sistem.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#94a3b8',
                    confirmButtonText: 'Ya, Hapus',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) form.submit();
                });
            });
        });
    </script>
</x-app-layout>
