<x-app-layout>

    {{-- Phase 6B-3: Deprecation banner --}}
    <div class="mb-6 bg-amber-50 border border-amber-300 rounded-2xl px-6 py-4 flex items-start gap-4">
        <svg class="w-6 h-6 text-amber-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
        </svg>
        <div>
            <p class="font-bold text-amber-800">Modul SPP Lama — Arsip Saja</p>
            <p class="text-sm text-amber-700 mt-0.5">Modul SPP lama sudah dipindahkan ke <strong>Tagihan Siswa</strong>. Halaman ini hanya untuk melihat riwayat data lama. Gunakan menu <a href="{{ route('bills.index') }}" class="underline font-semibold">Tagihan Sekolah</a> untuk transaksi baru.</p>
        </div>
    </div>

    <div x-data="{ selectedBill: null }">
        
        <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('services.midtrans.client_key') }}"></script>
        
        <style>
            [x-cloak] { display: none !important; }
        </style>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 flex flex-col overflow-hidden">
            
            <div class="p-6 border-b border-gray-100 flex flex-col md:flex-row justify-between items-center gap-4 bg-white">
                <div>
                    <h2 class="text-xl font-bold text-gray-800">Riwayat Tagihan SPP Lama</h2>
                    <p class="text-sm text-gray-500 mt-1">Data arsip — hanya untuk referensi historis.</p>
                </div>
                <a href="{{ route('bills.create') }}" class="bg-[#0284c7] hover:bg-[#0369a1] text-white px-5 py-2.5 rounded-xl text-sm font-bold transition shadow-md flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Buat Tagihan Baru
                </a>
            </div>

            <div class="overflow-x-auto w-full">
                <table class="w-full text-sm text-left text-gray-600">
                    <thead class="bg-gray-50 text-gray-700 font-bold border-b border-gray-100 uppercase text-xs tracking-wider">
                        <tr>
                            <th class="px-6 py-4 whitespace-nowrap">NIS</th>
                            <th class="px-6 py-4 whitespace-nowrap">Nama Siswa</th>
                            <th class="px-6 py-4 whitespace-nowrap text-center">Bulan</th>
                            <th class="px-6 py-4 whitespace-nowrap text-right">Nominal</th>
                            <th class="px-6 py-4 whitespace-nowrap text-center">Status</th>
                            <th class="px-6 py-4 text-center whitespace-nowrap">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($bills as $bill)
                        <tr class="hover:bg-blue-50/40 transition duration-150">
                            <td class="px-6 py-4 font-medium text-gray-500 whitespace-nowrap">{{ $bill->student->nis }}</td>
                            
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="font-bold text-gray-900">{{ $bill->student->name }}</div>
                                <div class="text-xs text-gray-400 font-medium">{{ optional($bill->student->kelas)->nama_kelas ?? '-' }}</div>
                            </td>
                            
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span class="bg-gray-100 text-gray-600 py-1 px-3 rounded-lg text-xs font-bold border border-gray-200">
                                    {{ $bill->month }}
                                </span>
                            </td>
                            
                            <td class="px-6 py-4 font-bold text-[#0ea5e9] whitespace-nowrap text-right">
                                Rp {{ number_format($bill->amount, 0, ',', '.') }}
                            </td>
                            
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @if($bill->status == 'LUNAS')
                                    <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-bold inline-flex items-center gap-1.5 border border-green-200">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                        LUNAS
                                    </span>
                                @elseif($bill->status == 'PENDING')
                                    <span class="bg-yellow-100 text-yellow-700 px-3 py-1 rounded-full text-xs font-bold border border-yellow-200">PENDING</span>
                                @else
                                    <span class="bg-red-100 text-red-700 px-3 py-1 rounded-full text-xs font-bold border border-red-200">BELUM</span>
                                @endif
                            </td>

                            <td class="px-6 py-4 text-center whitespace-nowrap">
                                <div class="flex justify-center gap-2">
                                    @if($bill->status == 'LUNAS')
                                        <a href="{{ route('bills.print', $bill->id) }}" target="_blank"
                                           class="text-gray-600 hover:text-[#0284c7] bg-gray-50 hover:bg-sky-50 border border-gray-200 px-3 py-1.5 rounded-lg text-xs font-bold transition flex items-center gap-1.5">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                            Cetak Invoice
                                        </a>
                                    @else
                                        <span class="text-xs text-gray-400 italic">Lihat Tagihan Siswa</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-16 text-center text-gray-500 bg-gray-50/50">
                                <div class="flex flex-col items-center">
                                    <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                    <span class="font-medium text-gray-400">Belum ada data tagihan.</span>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="border-t border-gray-100 bg-gray-50 px-6 py-4">
                {{ $bills->links() }}
            </div>
        </div>

</x-app-layout>