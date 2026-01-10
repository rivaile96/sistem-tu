<x-app-layout>
    <div class="p-6">
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            
            <div class="p-5 border-b border-gray-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl font-bold text-gray-800">Riwayat Transaksi</h2>
                    <p class="text-sm text-gray-500">Pantau penjualan dan status pembayaran.</p>
                </div>

                <form method="GET" class="flex flex-col sm:flex-row gap-3">
                    <div class="flex items-center gap-2 bg-gray-50 px-3 py-2 rounded-xl border border-gray-200">
                        <span class="text-gray-400 text-xs font-bold">TANGGAL:</span>
                        <input type="date" name="start_date" value="{{ request('start_date', date('Y-m-d')) }}" class="bg-transparent border-none p-0 text-sm focus:ring-0">
                        <span class="text-gray-400">-</span>
                        <input type="date" name="end_date" value="{{ request('end_date', date('Y-m-d')) }}" class="bg-transparent border-none p-0 text-sm focus:ring-0">
                    </div>
                    <button type="submit" class="bg-gray-800 text-white px-5 py-2 rounded-xl text-sm font-bold hover:bg-black transition">Filter</button>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-600">
                    <thead class="bg-gray-50 text-gray-700 uppercase text-xs font-bold">
                        <tr>
                            <th class="px-6 py-4">Kode TRX</th>
                            <th class="px-6 py-4">Waktu</th>
                            <th class="px-6 py-4">Kasir</th>
                            <th class="px-6 py-4 text-right">Total</th>
                            <th class="px-6 py-4 text-center">Status</th>
                            <th class="px-6 py-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($orders as $order)
                        <tr class="hover:bg-gray-50/50 transition">
                            <td class="px-6 py-4 font-mono font-bold text-gray-800">
                                #{{ $order->transaction_code }}
                                @if($order->payment_status == 'UNPAID')
                                    <span class="ml-2 text-[10px] bg-purple-100 text-purple-700 px-1.5 py-0.5 rounded border border-purple-200">HUTANG</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                {{ $order->created_at->format('d M Y, H:i') }}
                            </td>
                            <td class="px-6 py-4">
                                {{ $order->user->name ?? 'Sistem' }}
                            </td>
                            <td class="px-6 py-4 text-right font-bold text-gray-900">
                                Rp {{ number_format($order->total_amount, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($order->payment_status == 'PAID')
                                    <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-bold border border-green-200">LUNAS</span>
                                @elseif($order->payment_status == 'UNPAID')
                                    <span class="bg-yellow-100 text-yellow-700 px-3 py-1 rounded-full text-xs font-bold border border-yellow-200">PENDING</span>
                                @else
                                    <span class="bg-red-100 text-red-700 px-3 py-1 rounded-full text-xs font-bold border border-red-200">BATAL</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                <a href="{{ route('pos.transaction.print', $order->id) }}" target="_blank" class="text-[#0ea5e9] hover:text-sky-700 font-bold hover:underline">
                                    Cetak Struk
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-400">
                                Belum ada transaksi pada tanggal ini.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-gray-100">
                {{ $orders->links() }}
            </div>
        </div>
    </div>
</x-app-layout>