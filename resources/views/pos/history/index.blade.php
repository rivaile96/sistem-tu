<x-app-layout>

    <div class="bg-gradient-to-br from-slate-50 to-blue-50 min-h-screen py-8 px-4 sm:px-6 lg:px-8">
        
        <div class="max-w-7xl mx-auto space-y-8">
            <!-- Header Section -->
            <div class="bg-gradient-to-br from-white to-slate-50 p-8 rounded-3xl shadow-2xl border border-slate-200 relative overflow-hidden">
                <div class="absolute top-0 right-0 -mt-12 -mr-12 w-64 h-64 bg-gradient-to-br from-[#0284c7]/5 to-transparent rounded-full blur-3xl opacity-50"></div>
                
                <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-8 mb-10 relative z-10">
                    <div class="flex items-center gap-4">
                        <div class="relative">
                            <div class="absolute inset-0 bg-gradient-to-r from-[#0284c7] to-blue-400 rounded-2xl blur-lg opacity-30"></div>
                            <div class="relative bg-gradient-to-br from-[#0284c7] to-blue-600 p-3 rounded-2xl shadow-lg">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                                </svg>
                            </div>
                        </div>
                        <div>
                            <h2 class="text-3xl font-bold text-slate-800 tracking-tight">Riwayat Transaksi</h2>
                            <p class="text-sm text-slate-500 mt-1">Pantau arus kas, piutang, dan pelunasan secara real-time</p>
                        </div>
                    </div>
                    
                    <div class="w-full lg:w-auto">
                        <form method="GET" action="{{ route('pos.history.index') }}" class="flex flex-col sm:flex-row items-stretch sm:items-center gap-4">
                            <div class="flex-1 grid grid-cols-2 gap-3">
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                    </div>
                                    <input type="date" name="start_date" value="{{ $startDate }}" 
                                           class="w-full pl-10 rounded-xl border-slate-300 bg-white text-slate-600 text-sm py-3 focus:ring-[#0284c7] focus:border-[#0284c7] shadow-sm">
                                </div>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                    </div>
                                    <input type="date" name="end_date" value="{{ $endDate }}" 
                                           class="w-full pl-10 rounded-xl border-slate-300 bg-white text-slate-600 text-sm py-3 focus:ring-[#0284c7] focus:border-[#0284c7] shadow-sm">
                                </div>
                            </div>
                            
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <select name="status" class="pl-10 w-full rounded-xl border-slate-300 bg-white text-slate-600 text-sm py-3 focus:ring-[#0284c7] focus:border-[#0284c7] shadow-sm">
                                    <option value="">Semua Status</option>
                                    <option value="PAID" {{ request('status') == 'PAID' ? 'selected' : '' }}>Lunas (Cash)</option>
                                    <option value="UNPAID" {{ request('status') == 'UNPAID' ? 'selected' : '' }}>Belum Lunas</option>
                                </select>
                            </div>

                            <button type="submit" 
                                    class="bg-gradient-to-r from-slate-800 to-slate-700 hover:from-slate-900 hover:to-slate-800 text-white px-6 py-3.5 rounded-xl text-sm font-bold shadow-lg transition-all transform hover:-translate-y-0.5 flex items-center justify-center gap-2 group">
                                <svg class="w-4 h-4 group-hover:rotate-12 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                                </svg>
                                Terapkan Filter
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 relative z-10">
                    <div class="bg-gradient-to-br from-[#0284c7] to-blue-500 p-6 rounded-2xl text-white shadow-xl shadow-blue-200/50 relative overflow-hidden group hover:shadow-2xl hover:shadow-blue-300/50 transition-all duration-300">
                        <div class="absolute inset-0 bg-gradient-to-r from-white/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                        <div class="relative">
                            <div class="flex items-center gap-3 mb-3">
                                <div class="p-2 bg-white/20 rounded-xl backdrop-blur-sm">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <p class="text-blue-100 text-sm font-bold uppercase tracking-wider">Total Penjualan</p>
                            </div>
                            <h3 class="text-3xl font-extrabold tracking-tight">Rp {{ number_format($totalOmset, 0, ',', '.') }}</h3>
                            <p class="text-blue-100/80 text-xs mt-2">Periode yang dipilih</p>
                        </div>
                    </div>

                    <div class="bg-gradient-to-br from-white to-slate-50 p-6 rounded-2xl border border-emerald-200 shadow-lg hover:shadow-xl transition-shadow group">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="flex items-center gap-2 mb-2">
                                    <div class="p-1.5 bg-emerald-100 text-emerald-600 rounded-lg">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                                        </svg>
                                    </div>
                                    <p class="text-slate-500 text-xs font-bold uppercase tracking-wider">Uang Masuk (Cash)</p>
                                </div>
                                <h3 class="text-2xl font-extrabold text-slate-800">Rp {{ number_format($totalCashIn, 0, ',', '.') }}</h3>
                            </div>
                            <div class="text-emerald-600 opacity-0 group-hover:opacity-100 transition-opacity">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="bg-gradient-to-br from-white to-slate-50 p-6 rounded-2xl border border-purple-200 shadow-lg hover:shadow-xl transition-shadow group">
                        <div class="flex items-center justify-between">
                            <div>
                                <div class="flex items-center gap-2 mb-2">
                                    <div class="p-1.5 bg-purple-100 text-purple-600 rounded-lg">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                    <p class="text-slate-500 text-xs font-bold uppercase tracking-wider">Piutang (Belum Bayar)</p>
                                </div>
                                <h3 class="text-2xl font-extrabold text-purple-600">Rp {{ number_format($totalUnpaid, 0, ',', '.') }}</h3>
                            </div>
                            <div class="text-purple-600 opacity-0 group-hover:opacity-100 transition-opacity">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Transaction Table -->
            <div class="bg-gradient-to-br from-white to-slate-50 rounded-3xl shadow-2xl border border-slate-200 overflow-hidden">
                <div class="px-8 py-6 border-b border-slate-200 bg-gradient-to-r from-slate-50 to-white">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-bold text-slate-800 flex items-center gap-3">
                            <svg class="w-6 h-6 text-[#0284c7]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                            </svg>
                            Daftar Transaksi
                        </h3>
                        <div class="text-sm text-slate-500 bg-slate-100 px-3 py-1.5 rounded-lg">
                            Total: <span class="font-bold text-[#0284c7]">{{ $transactions->total() }}</span> transaksi
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-gradient-to-r from-slate-50 to-blue-50 border-b border-slate-200 text-sm uppercase text-slate-600 font-bold tracking-wider">
                                <th class="px-8 py-4 text-left">Kode Transaksi</th>
                                <th class="px-8 py-4 text-left">Waktu</th>
                                <th class="px-8 py-4 text-left">Siswa / Pelanggan</th>
                                <th class="px-8 py-4 text-left">Detail Transaksi</th>
                                <th class="px-8 py-4 text-center">Status</th>
                                <th class="px-8 py-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($transactions as $trx)
                            <tr class="hover:bg-blue-50/30 transition-colors duration-200 group">
                                <td class="px-8 py-5">
                                    <div class="font-bold text-slate-800 text-base">#{{ $trx->transaction_code }}</div>
                                    <div class="text-xs text-slate-400 mt-1 flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                        </svg>
                                        Kasir: {{ $trx->user->name ?? 'Admin' }}
                                    </div>
                                </td>
                                
                                <td class="px-8 py-5">
                                    <div class="text-sm text-slate-800 font-semibold">{{ $trx->created_at->format('d M Y') }}</div>
                                    <div class="text-xs text-slate-400 mt-1 flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        {{ $trx->created_at->format('H:i') }} WIB
                                    </div>
                                </td>

                                <td class="px-8 py-5">
                                    @if($trx->student)
                                    <div class="flex items-center gap-3">
                                        <div class="relative">
                                            <div class="absolute inset-0 bg-gradient-to-br from-purple-500 to-purple-400 rounded-full blur opacity-20 group-hover:opacity-30 transition"></div>
                                            <div class="relative w-10 h-10 rounded-full bg-gradient-to-br from-purple-500 to-purple-400 text-white flex items-center justify-center font-bold text-sm shadow-md">
                                                {{ substr($trx->student->name, 0, 1) }}
                                            </div>
                                        </div>
                                        <div>
                                            <div class="font-bold text-slate-800 text-sm">{{ $trx->student->name }}</div>
                                            <div class="text-xs text-slate-400 mt-0.5 flex items-center gap-1">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                                </svg>
                                                {{ optional($trx->student->kelas)->nama_kelas ?? '-' }}
                                            </div>
                                        </div>
                                    </div>
                                    @else
                                    <div class="text-slate-400 italic text-sm">Umum / Guest</div>
                                    @endif
                                </td>

                                <td class="px-8 py-5">
                                    <div class="text-lg font-bold text-slate-800">Rp {{ number_format($trx->total_amount, 0, ',', '.') }}</div>
                                    <div class="text-xs text-slate-400 mt-1">
                                        {{ $trx->items_count }} item barang
                                    </div>
                                </td>

                                <td class="px-8 py-5 text-center">
                                    @if($trx->payment_status == 'PAID')
                                        <span class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-bold bg-gradient-to-r from-emerald-50 to-emerald-100 text-emerald-700 border border-emerald-200 shadow-sm">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                            Lunas
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-bold bg-gradient-to-r from-purple-50 to-purple-100 text-purple-700 border border-purple-200 shadow-sm animate-pulse">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                            Hutang
                                        </span>
                                    @endif
                                </td>

                                <td class="px-8 py-5 text-right">
                                    <div class="flex items-center justify-end gap-3">
                                        <a href="{{ route('pos.transaction.print', $trx->id) }}" target="_blank" 
                                           class="group bg-gradient-to-r from-slate-50 to-slate-100 hover:from-slate-200 hover:to-slate-100 text-slate-600 p-3 rounded-xl transition-all duration-300 border border-slate-200 shadow-sm hover:shadow-lg transform hover:-translate-y-0.5"
                                           title="Cetak Struk">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path>
                                            </svg>
                                        </a>

                                        @if($trx->payment_status == 'UNPAID')
                                            <button onclick="confirmRepay('{{ $trx->id }}', '{{ optional($trx->student)->name ?? 'Siswa' }}', '{{ number_format($trx->total_amount, 0,',','.') }}')"
                                                    class="group bg-gradient-to-r from-purple-600 to-purple-500 hover:from-purple-700 hover:to-purple-600 text-white px-4 py-2.5 rounded-xl text-sm font-bold shadow-lg transition-all transform hover:-translate-y-0.5 flex items-center gap-2">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                                </svg>
                                                Bayar
                                            </button>
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
                                                <svg class="w-12 h-12 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                                                </svg>
                                            </div>
                                        </div>
                                        <h4 class="text-lg font-bold text-slate-600 mb-2">Belum ada data transaksi</h4>
                                        <p class="text-sm text-slate-400 mb-6">Coba sesuaikan filter tanggal atau periode lain</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <div class="px-8 py-6 border-t border-slate-200 bg-gradient-to-r from-slate-50 to-white">
                    {{ $transactions->links() }}
                </div>
            </div>
        </div>
    </div>

    <script>
        function confirmRepay(id, name, amount) {
            Swal.fire({
                title: 'Konfirmasi Pelunasan',
                html: `Terima pembayaran dari <strong>${name}</strong> sebesar<br>
                      <span class="text-2xl font-bold text-emerald-600">Rp ${amount}</span><br>
                      <span class="text-xs text-slate-400">Transaksi akan dicatat sebagai lunas</span>`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#0284c7',
                cancelButtonColor: '#94a3b8',
                confirmButtonText: 'Ya, Lunasi Sekarang',
                cancelButtonText: 'Batal',
                background: '#ffffff',
                backdrop: 'rgba(2, 132, 199, 0.1)',
                customClass: {
                    title: 'text-lg font-bold text-slate-800',
                    htmlContainer: 'text-slate-600'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    // Buat Form Post Dinamis
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = `/pos/history/${id}/repay`;
                    
                    const csrfToken = document.createElement('input');
                    csrfToken.type = 'hidden';
                    csrfToken.name = '_token';
                    csrfToken.value = '{{ csrf_token() }}';
                    
                    form.appendChild(csrfToken);
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }

    </script>
</x-app-layout>