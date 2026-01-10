<x-app-layout>
    <div class="space-y-6">
        
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800">Riwayat Transaksi</h2>
                    <p class="text-sm text-gray-500">Pantau arus kas, piutang, dan pelunasan.</p>
                </div>
                
                <form method="GET" action="{{ route('pos.history.index') }}" class="flex flex-wrap items-center gap-3">
                    <div class="flex items-center gap-2 bg-gray-50 px-3 py-2 rounded-lg border border-gray-200">
                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        <input type="date" name="start_date" value="{{ $startDate }}" class="bg-transparent border-none text-sm p-0 focus:ring-0 text-gray-600">
                        <span class="text-gray-400">-</span>
                        <input type="date" name="end_date" value="{{ $endDate }}" class="bg-transparent border-none text-sm p-0 focus:ring-0 text-gray-600">
                    </div>

                    <select name="status" class="bg-gray-50 border-gray-200 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 py-2">
                        <option value="">Semua Status</option>
                        <option value="PAID" {{ request('status') == 'PAID' ? 'selected' : '' }}>✅ Lunas (Cash)</option>
                        <option value="UNPAID" {{ request('status') == 'UNPAID' ? 'selected' : '' }}>⏳ Belum Lunas</option>
                    </select>

                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-bold transition shadow-md">
                        Filter Data
                    </button>
                </form>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="p-4 rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 text-white shadow-lg shadow-blue-200">
                    <p class="text-blue-100 text-xs font-medium uppercase tracking-wider mb-1">Total Penjualan</p>
                    <h3 class="text-2xl font-bold">Rp {{ number_format($totalOmset, 0, ',', '.') }}</h3>
                </div>

                <div class="p-4 rounded-xl bg-white border border-green-100 shadow-sm">
                    <div class="flex items-center gap-3 mb-1">
                        <div class="p-1.5 bg-green-100 text-green-600 rounded-lg">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <p class="text-gray-500 text-xs font-bold uppercase">Uang Masuk (Cash)</p>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800">Rp {{ number_format($totalCashIn, 0, ',', '.') }}</h3>
                </div>

                <div class="p-4 rounded-xl bg-white border border-purple-100 shadow-sm">
                    <div class="flex items-center gap-3 mb-1">
                        <div class="p-1.5 bg-purple-100 text-purple-600 rounded-lg">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <p class="text-gray-500 text-xs font-bold uppercase">Piutang (Belum Bayar)</p>
                    </div>
                    <h3 class="text-xl font-bold text-purple-600">Rp {{ number_format($totalUnpaid, 0, ',', '.') }}</h3>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50 text-gray-500 font-bold uppercase text-xs border-b border-gray-100">
                        <tr>
                            <th class="px-6 py-4">Kode TRX</th>
                            <th class="px-6 py-4">Waktu</th>
                            <th class="px-6 py-4">Siswa / Pelanggan</th>
                            <th class="px-6 py-4">Total</th>
                            <th class="px-6 py-4 text-center">Status</th>
                            <th class="px-6 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($transactions as $trx)
                        <tr class="hover:bg-gray-50/50 transition">
                            <td class="px-6 py-4 font-medium text-gray-900">
                                #{{ $trx->transaction_code }}
                                <div class="text-[10px] text-gray-400 font-normal">Kasir: {{ $trx->user->name ?? 'Admin' }}</div>
                            </td>
                            <td class="px-6 py-4 text-gray-500">
                                {{ $trx->created_at->format('d M Y') }}<br>
                                <span class="text-xs">{{ $trx->created_at->format('H:i') }}</span>
                            </td>
                            <td class="px-6 py-4">
                                @if($trx->student)
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 rounded-full bg-purple-100 text-purple-600 flex items-center justify-center text-xs font-bold">
                                            {{ substr($trx->student->name, 0, 1) }}
                                        </div>
                                        <div>
                                            <p class="font-bold text-gray-700">{{ $trx->student->name }}</p>
                                            <p class="text-[10px] text-gray-400">{{ $trx->student->class_name }}</p>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-gray-400 italic">Umum / Guest</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 font-bold text-gray-800">
                                Rp {{ number_format($trx->total_amount, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($trx->payment_status == 'PAID')
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-green-100 text-green-700">
                                        ✅ Lunas
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-bold bg-purple-100 text-purple-700 animate-pulse">
                                        ⏳ Hutang
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('pos.transaction.print', $trx->id) }}" target="_blank" 
                                       class="text-gray-500 hover:text-blue-600 p-2 rounded-lg hover:bg-blue-50 transition" title="Cetak Struk">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                    </a>

                                    @if($trx->payment_status == 'UNPAID')
                                        <button onclick="confirmRepay('{{ $trx->id }}', '{{ $trx->student->name ?? 'Siswa' }}', '{{ number_format($trx->total_amount, 0,',','.') }}')"
                                                class="bg-purple-600 hover:bg-purple-700 text-white px-3 py-1.5 rounded-lg text-xs font-bold shadow-sm transition">
                                            Bayar
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-400">
                                <div class="flex flex-col items-center">
                                    <svg class="w-12 h-12 mb-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                                    <p>Belum ada data transaksi sesuai filter.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="px-6 py-4 border-t border-gray-100">
                {{ $transactions->links() }}
            </div>
        </div>
    </div>

    <script>
        function confirmRepay(id, name, amount) {
            Swal.fire({
                title: 'Pelunasan Hutang',
                text: `Terima pembayaran sebesar Rp ${amount} dari ${name}?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#9333ea', // Purple
                cancelButtonColor: '#d1d5db',
                confirmButtonText: 'Ya, Lunasi Sekarang!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Buat Form Post Dinamis
                    let form = document.createElement('form');
                    form.method = 'POST';
                    form.action = `/pos/history/${id}/repay`;
                    form.innerHTML = `@csrf`; // Masukkan CSRF Token
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }
    </script>
</x-app-layout>