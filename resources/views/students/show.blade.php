<x-app-layout>
    <div class="space-y-6">
        
        <a href="{{ route('students.index') }}" class="inline-flex items-center gap-2 text-gray-500 hover:text-blue-600 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Kembali ke Daftar Siswa
        </a>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <div class="space-y-6">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden text-center p-6">
                    <div class="w-24 h-24 mx-auto bg-gradient-to-br from-blue-500 to-indigo-600 rounded-full flex items-center justify-center text-white text-3xl font-bold mb-4 shadow-lg shadow-blue-200">
                        {{ substr($student->name, 0, 1) }}
                    </div>
                    <h2 class="text-xl font-bold text-gray-800">{{ $student->name }}</h2>
                    <p class="text-sm text-gray-500 font-mono mb-2">{{ $student->nis }} • {{ $student->class_name }}</p>
                    <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-bold border border-green-200">
                        Siswa Aktif
                    </span>

                    <div class="mt-6 border-t border-gray-100 pt-4 text-left space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Wali Kelas</span>
                            <span class="font-medium">-</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">No. HP Ortu</span>
                            <span class="font-medium">{{ $student->parent_phone ?? '-' }}</span>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="font-bold text-gray-800 flex items-center gap-2 mb-4">
                        <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Tagihan & Hutang
                    </h3>
                    
                    <div class="space-y-3">
                        <div class="p-3 bg-purple-50 rounded-xl border border-purple-100">
                            <p class="text-xs text-purple-600 font-bold uppercase mb-1">Hutang Kantin/Koperasi</p>
                            <p class="text-xl font-bold text-purple-700">Rp {{ number_format($debtPos, 0, ',', '.') }}</p>
                        </div>

                        @php
                            $totalBillUnpaid = $student->bills->where('status', 'UNPAID')->sum('amount');
                        @endphp
                        <div class="p-3 bg-orange-50 rounded-xl border border-orange-100">
                            <p class="text-xs text-orange-600 font-bold uppercase mb-1">Tunggakan Sekolah (SPP dll)</p>
                            <p class="text-xl font-bold text-orange-700">Rp {{ number_format($totalBillUnpaid, 0, ',', '.') }}</p>
                        </div>

                        <div class="pt-2 border-t border-gray-100">
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-bold text-gray-600">Total Kewajiban</span>
                                <span class="text-lg font-bold text-red-600">Rp {{ number_format($debtPos + $totalBillUnpaid, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 min-h-[500px]" x-data="{ tab: 'bills' }">
                
                <div class="flex border-b border-gray-100">
                    <button @click="tab = 'bills'" 
                            :class="tab === 'bills' ? 'border-blue-500 text-blue-600 bg-blue-50/50' : 'border-transparent text-gray-500 hover:text-gray-700'"
                            class="flex-1 py-4 text-sm font-bold border-b-2 transition">
                        📄 Tagihan Sekolah (SPP/Gedung)
                    </button>
                    <button @click="tab = 'pos'" 
                            :class="tab === 'pos' ? 'border-purple-500 text-purple-600 bg-purple-50/50' : 'border-transparent text-gray-500 hover:text-gray-700'"
                            class="flex-1 py-4 text-sm font-bold border-b-2 transition">
                        🛒 Riwayat Jajan (POS)
                    </button>
                </div>

                <div x-show="tab === 'bills'" class="p-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">Daftar Tagihan Siswa</h3>
                    
                    @if($student->bills->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left">
                                <thead class="bg-gray-50 text-gray-500 font-bold uppercase text-xs">
                                    <tr>
                                        <th class="px-4 py-3 rounded-l-lg">Keterangan</th>
                                        <th class="px-4 py-3">Jenis</th>
                                        <th class="px-4 py-3">Nominal</th>
                                        <th class="px-4 py-3 text-center">Status</th>
                                        <th class="px-4 py-3 text-right rounded-r-lg">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach($student->bills as $bill)
                                    <tr class="hover:bg-gray-50 transition">
                                        <td class="px-4 py-3 font-medium text-gray-800">{{ $bill->name }}</td>
                                        <td class="px-4 py-3 text-xs">
                                            <span class="bg-gray-100 px-2 py-1 rounded text-gray-600">{{ $bill->type }}</span>
                                        </td>
                                        <td class="px-4 py-3 font-bold text-gray-700">{{ $bill->formatted_amount }}</td>
                                        <td class="px-4 py-3 text-center">
                                            <span class="px-2.5 py-1 rounded-full text-xs font-bold border {{ $bill->status_color }}">
                                                {{ $bill->status == 'UNPAID' ? 'BELUM LUNAS' : 'LUNAS' }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            @if($bill->status == 'UNPAID')
                                                <button disabled class="text-xs bg-gray-100 text-gray-400 px-3 py-1.5 rounded cursor-not-allowed" title="Fitur Bayar Segera Hadir">
                                                    Bayar
                                                </button>
                                            @else
                                                <span class="text-green-600 text-xs font-bold">✓ Selesai</span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-10 text-gray-400">
                            <svg class="w-12 h-12 mx-auto mb-2 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            <p>Belum ada tagihan untuk siswa ini.</p>
                        </div>
                    @endif
                </div>

                <div x-show="tab === 'pos'" class="p-6" style="display: none;">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">Transaksi Kantin & Koperasi</h3>
                    
                    @if($posTransactions->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left">
                                <thead class="bg-purple-50 text-purple-800 font-bold uppercase text-xs">
                                    <tr>
                                        <th class="px-4 py-3 rounded-l-lg">Tanggal</th>
                                        <th class="px-4 py-3">Kode TRX</th>
                                        <th class="px-4 py-3">Total</th>
                                        <th class="px-4 py-3 text-center">Status</th>
                                        <th class="px-4 py-3 text-right rounded-r-lg">Lihat</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach($posTransactions as $trx)
                                    <tr class="hover:bg-purple-50/30 transition">
                                        <td class="px-4 py-3 text-gray-500">{{ $trx->created_at->format('d/m/Y H:i') }}</td>
                                        <td class="px-4 py-3 font-mono text-xs">{{ $trx->transaction_code }}</td>
                                        <td class="px-4 py-3 font-bold text-gray-800">Rp {{ number_format($trx->total_amount, 0, ',', '.') }}</td>
                                        <td class="px-4 py-3 text-center">
                                            @if($trx->payment_status == 'PAID')
                                                <span class="text-green-600 bg-green-100 px-2 py-0.5 rounded text-[10px] font-bold">LUNAS</span>
                                            @else
                                                <span class="text-purple-600 bg-purple-100 px-2 py-0.5 rounded text-[10px] font-bold animate-pulse">HUTANG</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            <a href="{{ route('pos.transaction.print', $trx->id) }}" target="_blank" class="text-blue-500 hover:underline text-xs">Struk</a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-10 text-gray-400">
                            <p>Belum ada riwayat belanja.</p>
                        </div>
                    @endif
                </div>

            </div>
        </div>
    </div>
</x-app-layout>