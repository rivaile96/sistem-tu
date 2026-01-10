<x-app-layout>
    <div class="space-y-6">
        
        <div>
            <a href="{{ route('students.index') }}" class="inline-flex items-center gap-2 text-gray-500 hover:text-gray-700 transition font-medium text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Kembali ke Daftar Siswa
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <div class="space-y-6">
                
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 text-center relative overflow-hidden">
                    <div class="absolute top-0 left-0 w-full h-24 bg-gradient-to-r from-blue-500 to-blue-600"></div>
                    
                    <div class="relative z-10 mt-12">
                        <div class="w-24 h-24 mx-auto bg-white rounded-full p-1 shadow-lg">
                            <div class="w-full h-full rounded-full bg-blue-100 text-blue-600 flex items-center justify-center text-3xl font-bold">
                                {{ substr($student->name, 0, 1) }}
                            </div>
                        </div>
                        
                        <h2 class="mt-4 text-xl font-bold text-gray-800">{{ $student->name }}</h2>
                        <p class="text-gray-500 text-sm">{{ $student->nis }} • {{ $student->class_name }}</p>
                        
                        <div class="mt-4 inline-block px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-bold">
                            Siswa Aktif
                        </div>
                    </div>

                    <div class="mt-8 pt-6 border-t border-gray-100 text-left space-y-3">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Wali Kelas</span>
                            <span class="font-medium text-gray-800">-</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">No. HP Ortu</span>
                            <span class="font-medium text-gray-800">{{ $student->parent_phone ?? '-' }}</span>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="font-bold text-gray-800 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Tagihan & Hutang
                    </h3>
                    
                    <div class="space-y-4">
                        <div class="p-4 bg-purple-50 rounded-xl border border-purple-100">
                            <p class="text-xs text-purple-600 font-bold uppercase mb-1">Hutang Kantin/Koperasi</p>
                            <div class="flex justify-between items-end">
                                <span class="text-2xl font-bold text-purple-700">Rp {{ number_format($debtPos, 0, ',', '.') }}</span>
                                <a href="#" class="text-xs font-bold text-purple-500 underline">Detail</a>
                            </div>
                        </div>

                        <div class="p-4 bg-orange-50 rounded-xl border border-orange-100 opacity-70">
                            <p class="text-xs text-orange-600 font-bold uppercase mb-1">Tunggakan SPP</p>
                            <div class="flex justify-between items-end">
                                <span class="text-2xl font-bold text-orange-700">Rp -</span>
                                <span class="text-xs text-gray-400">(Segera Hadir)</span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <div class="lg:col-span-2 space-y-6">
                
                <div class="flex border-b border-gray-200">
                    <button class="px-6 py-3 text-sm font-bold text-blue-600 border-b-2 border-blue-600 bg-blue-50/50">
                        Riwayat Transaksi POS
                    </button>
                    <button class="px-6 py-3 text-sm font-medium text-gray-500 hover:text-gray-700 cursor-not-allowed" title="Coming Soon">
                        Kartu SPP
                    </button>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-6 border-b border-gray-100 flex justify-between items-center">
                        <h3 class="font-bold text-gray-800">Transaksi Terakhir</h3>
                        <span class="text-xs bg-gray-100 text-gray-500 px-2 py-1 rounded">Update Realtime</span>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="bg-gray-50 text-gray-500 font-bold uppercase text-xs">
                                <tr>
                                    <th class="px-6 py-3">Tanggal</th>
                                    <th class="px-6 py-3">Kode TRX</th>
                                    <th class="px-6 py-3">Total</th>
                                    <th class="px-6 py-3 text-center">Status</th>
                                    <th class="px-6 py-3 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse($posTransactions as $trx)
                                <tr class="hover:bg-gray-50 transition">
                                    <td class="px-6 py-4 text-gray-600">
                                        {{ $trx->created_at->format('d M Y') }}
                                        <div class="text-xs text-gray-400">{{ $trx->created_at->format('H:i') }}</div>
                                    </td>
                                    <td class="px-6 py-4 font-mono text-gray-500 text-xs">
                                        {{ $trx->transaction_code }}
                                    </td>
                                    <td class="px-6 py-4 font-bold text-gray-800">
                                        Rp {{ number_format($trx->total_amount, 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        @if($trx->payment_status == 'PAID')
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-green-100 text-green-700">
                                                Lunas
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-purple-100 text-purple-700 animate-pulse">
                                                Hutang
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <a href="{{ route('pos.transaction.print', $trx->id) }}" target="_blank" class="text-blue-600 hover:underline text-xs font-medium">
                                            Cetak Struk
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-8 text-center text-gray-400">
                                        Belum ada riwayat transaksi belanja.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>