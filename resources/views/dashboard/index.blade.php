@extends('layouts.app')

@section('content')
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-[#0ea5e9] p-6 rounded-2xl text-white shadow-lg shadow-sky-200 relative overflow-hidden">
            <div class="relative z-10">
                <div class="flex items-center gap-3 mb-2">
                    <div class="p-2 bg-white/20 rounded-lg">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <span class="font-medium text-blue-100">Pemasukan Hari Ini</span>
                </div>
                <h3 class="text-3xl font-bold">Rp {{ number_format($totalIncomeToday, 0, ',', '.') }}</h3>
                <p class="text-sm text-blue-100 mt-1">Gabungan SPP & POS</p>
            </div>
            <div class="absolute -bottom-4 -right-4 w-24 h-24 bg-white/10 rounded-full"></div>
        </div>

        <div class="bg-[#0ea5e9] p-6 rounded-2xl text-white shadow-lg shadow-sky-200 relative overflow-hidden">
            <div class="relative z-10">
                <div class="flex items-center gap-3 mb-2">
                    <div class="p-2 bg-white/20 rounded-lg">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    </div>
                    <span class="font-medium text-blue-100">Siswa Belum Bayar</span>
                </div>
                <h3 class="text-3xl font-bold">{{ $unpaidStudents }} <span class="text-lg font-normal text-blue-100">Orang</span></h3>
                <p class="text-sm text-blue-100 mt-1">Tagihan Bulan Ini</p>
            </div>
        </div>

        <div class="bg-[#0ea5e9] p-6 rounded-2xl text-white shadow-lg shadow-sky-200 relative overflow-hidden">
            <div class="relative z-10">
                <div class="flex items-center gap-3 mb-2">
                    <div class="p-2 bg-white/20 rounded-lg">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                    </div>
                    <span class="font-medium text-blue-100">Stok Menipis</span>
                </div>
                <h3 class="text-3xl font-bold">{{ $lowStockItems->count() }} <span class="text-lg font-normal text-blue-100">Barang</span></h3>
                <p class="text-sm text-blue-100 mt-1">Perlu Restock Segera</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <div class="lg:col-span-2 space-y-8">
            
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-bold text-gray-800">Summary Keuangan</h3>
                    <select class="text-sm border-none bg-gray-50 rounded-lg px-3 py-1 text-gray-500">
                        <option>7 Hari Terakhir</option>
                    </select>
                </div>
                <div class="h-64 flex items-end justify-between gap-2 px-4">
                    <div class="w-full bg-blue-50 rounded-t-lg h-[40%] relative group"><div class="absolute bottom-0 w-full bg-[#0ea5e9] rounded-t-lg h-[0%] group-hover:h-full transition-all duration-500"></div></div>
                    <div class="w-full bg-blue-50 rounded-t-lg h-[60%] relative group"><div class="absolute bottom-0 w-full bg-[#0ea5e9] rounded-t-lg h-[0%] group-hover:h-full transition-all duration-500"></div></div>
                    <div class="w-full bg-blue-50 rounded-t-lg h-[30%] relative group"><div class="absolute bottom-0 w-full bg-[#0ea5e9] rounded-t-lg h-[0%] group-hover:h-full transition-all duration-500"></div></div>
                    <div class="w-full bg-blue-50 rounded-t-lg h-[80%] relative group"><div class="absolute bottom-0 w-full bg-[#0ea5e9] rounded-t-lg h-[0%] group-hover:h-full transition-all duration-500"></div></div>
                    <div class="w-full bg-blue-50 rounded-t-lg h-[50%] relative group"><div class="absolute bottom-0 w-full bg-[#0ea5e9] rounded-t-lg h-[0%] group-hover:h-full transition-all duration-500"></div></div>
                </div>
            </div>

            <div class="bg-[#0ea5e9] rounded-2xl shadow-lg shadow-sky-200 overflow-hidden">
                <div class="p-6 flex justify-between items-center text-white">
                    <h3 class="font-bold text-lg">Log Aktivitas Terbaru</h3>
                    <div class="flex bg-white/20 rounded-lg p-1">
                        <button class="px-3 py-1 text-xs font-medium bg-white text-[#0ea5e9] rounded shadow-sm">Masuk</button>
                        <button class="px-3 py-1 text-xs font-medium text-blue-100 hover:text-white">Keluar</button>
                    </div>
                </div>
                
                <div class="bg-white/10 p-4">
                    <table class="w-full text-sm text-left text-white">
                        <thead class="text-xs uppercase text-blue-100 border-b border-white/10">
                            <tr>
                                <th class="px-4 py-3">Waktu</th>
                                <th class="px-4 py-3">Keterangan</th>
                                <th class="px-4 py-3">Nominal</th>
                                <th class="px-4 py-3">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/10">
                            <tr class="hover:bg-white/5 transition">
                                <td class="px-4 py-3">08:00</td>
                                <td class="px-4 py-3">Pembayaran SPP - Budi</td>
                                <td class="px-4 py-3">Rp 500.000</td>
                                <td class="px-4 py-3"><span class="bg-green-400/20 text-green-300 px-2 py-1 rounded text-xs">Lunas</span></td>
                            </tr>
                            <tr class="hover:bg-white/5 transition">
                                <td class="px-4 py-3">08:15</td>
                                <td class="px-4 py-3">POS - Seragam Batik</td>
                                <td class="px-4 py-3">Rp 150.000</td>
                                <td class="px-4 py-3"><span class="bg-green-400/20 text-green-300 px-2 py-1 rounded text-xs">Lunas</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <div class="space-y-6">
            
            <div class="bg-[#0ea5e9] p-6 rounded-2xl text-white shadow-lg">
                <div class="flex justify-between items-end mb-4">
                    <h3 class="font-bold text-lg">Kalender</h3>
                    <span class="text-xs text-blue-100">{{ now()->format('F Y') }}</span>
                </div>
                <div class="grid grid-cols-4 gap-2 text-center text-sm">
                    <div class="p-2 rounded-lg bg-white/10">Sn</div>
                    <div class="p-2 rounded-lg bg-white/10">Sl</div>
                    <div class="p-2 rounded-lg bg-white text-[#0ea5e9] font-bold shadow-lg">Rb</div>
                    <div class="p-2 rounded-lg bg-white/10">Km</div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                <h3 class="font-bold text-gray-800 mb-4">Catatan Stok</h3>
                <div class="space-y-3">
                    @forelse($lowStockItems as $item)
                        <div class="flex items-center gap-3 p-3 bg-red-50 text-red-700 rounded-lg text-sm">
                            <div class="w-2 h-2 rounded-full bg-red-500"></div>
                            <span class="flex-1">{{ $item->name }}</span>
                            <span class="font-bold">{{ $item->stock }} pcs</span>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">Stok aman semua.</p>
                    @endforelse
                </div>
            </div>

        </div>
    </div>
@endsection