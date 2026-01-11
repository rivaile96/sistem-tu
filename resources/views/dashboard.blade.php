<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Executive Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                
                <div class="bg-gradient-to-br from-emerald-500 to-emerald-600 p-6 rounded-2xl text-white shadow-lg shadow-emerald-200 relative overflow-hidden">
                    <div class="relative z-10">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="p-2 bg-white/20 rounded-lg">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                            <span class="font-bold text-emerald-100">Pemasukan Hari Ini</span>
                        </div>
                        <h3 class="text-3xl font-bold">Rp {{ number_format($totalIncomeToday, 0, ',', '.') }}</h3>
                        <p class="text-sm text-emerald-100 mt-1">Gabungan SPP & Kantin</p>
                    </div>
                    <div class="absolute -bottom-6 -right-6 w-32 h-32 bg-white/10 rounded-full"></div>
                </div>

                <div class="bg-gradient-to-br from-rose-500 to-rose-600 p-6 rounded-2xl text-white shadow-lg shadow-rose-200 relative overflow-hidden">
                    <div class="relative z-10">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="p-2 bg-white/20 rounded-lg">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                            </div>
                            <span class="font-bold text-rose-100">Siswa Nunggak</span>
                        </div>
                        <h3 class="text-3xl font-bold">{{ $unpaidStudents }} <span class="text-lg font-normal text-rose-100">Siswa</span></h3>
                        <p class="text-sm text-rose-100 mt-1">Perlu Ditagih Segera</p>
                    </div>
                    <div class="absolute -bottom-6 -right-6 w-32 h-32 bg-white/10 rounded-full"></div>
                </div>

                <div class="bg-gradient-to-br from-blue-500 to-blue-600 p-6 rounded-2xl text-white shadow-lg shadow-blue-200 relative overflow-hidden">
                    <div class="relative z-10">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="p-2 bg-white/20 rounded-lg">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                            </div>
                            <span class="font-bold text-blue-100">Stok Menipis</span>
                        </div>
                        <h3 class="text-3xl font-bold">{{ $lowStockItems->count() }} <span class="text-lg font-normal text-blue-100">Item</span></h3>
                        <p class="text-sm text-blue-100 mt-1">Segera Restock Barang</p>
                    </div>
                    <div class="absolute -bottom-6 -right-6 w-32 h-32 bg-white/10 rounded-full"></div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <div class="lg:col-span-2 space-y-8">
                    
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-lg font-bold text-gray-800">Tren Pemasukan (7 Hari)</h3>
                            <span class="text-xs bg-gray-100 px-2 py-1 rounded text-gray-500">Realtime Data</span>
                        </div>
                        
                        <div class="h-64 flex items-end justify-between gap-2 px-2">
                            @foreach($chartData as $data)
                            @php
                                // Hitung persentase tinggi batang (Max 100%)
                                $height = ($data['total'] / $maxIncome) * 100;
                                $height = $height > 0 ? $height : 2; // Minimal 2% biar kelihatan
                            @endphp
                            <div class="flex flex-col items-center gap-2 w-full group cursor-pointer">
                                <div class="opacity-0 group-hover:opacity-100 transition absolute mb-20 bg-gray-800 text-white text-xs px-2 py-1 rounded z-10">
                                    Rp {{ number_format($data['total'], 0,',','.') }}
                                </div>
                                <div class="w-full bg-blue-50 rounded-t-lg relative h-48 overflow-hidden">
                                    <div style="height: {{ $height }}%" 
                                         class="absolute bottom-0 w-full bg-[#0ea5e9] rounded-t-lg transition-all duration-1000 group-hover:bg-blue-600"></div>
                                </div>
                                <span class="text-xs font-bold text-gray-500">{{ $data['day'] }}</span>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                        <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                            <h3 class="font-bold text-gray-800">Aktivitas Keuangan Terkini</h3>
                            <span class="text-xs text-gray-500">Gabungan POS & SPP</span>
                        </div>
                        
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left">
                                <tbody class="divide-y divide-gray-100">
                                    @forelse($recentActivities as $log)
                                    <tr class="hover:bg-gray-50 transition">
                                        <td class="px-6 py-4 text-gray-500 whitespace-nowrap">
                                            {{ $log['time']->format('H:i') }}
                                        </td>
                                        <td class="px-6 py-4 font-medium text-gray-800">
                                            {{ $log['desc'] }}
                                        </td>
                                        <td class="px-6 py-4 font-bold text-gray-700 text-right">
                                            Rp {{ number_format($log['amount'], 0, ',', '.') }}
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <span class="bg-green-100 text-green-700 px-2 py-1 rounded text-xs font-bold">Sukses</span>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-8 text-center text-gray-400">Belum ada aktivitas hari ini.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>

                <div class="space-y-6">
                    
                    <div class="bg-[#0ea5e9] p-6 rounded-2xl text-white shadow-lg shadow-sky-100">
                        <div class="flex justify-between items-end mb-4">
                            <div>
                                <h3 class="font-bold text-2xl">{{ now()->format('d') }}</h3>
                                <span class="text-blue-100">{{ now()->format('l') }}</span>
                            </div>
                            <span class="text-sm font-bold bg-white/20 px-3 py-1 rounded-lg">{{ now()->format('F Y') }}</span>
                        </div>
                        <div class="w-full h-1 bg-white/20 rounded-full mb-2"></div>
                        <p class="text-xs text-blue-50">Sistem berjalan normal.</p>
                    </div>

                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                        <h3 class="font-bold text-gray-800 mb-4 flex items-center gap-2">
                            <svg class="w-5 h-5 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                            Stok Menipis!
                        </h3>
                        <div class="space-y-3">
                            @forelse($lowStockItems as $item)
                                <div class="flex items-center gap-3 p-3 bg-orange-50 text-orange-800 rounded-lg text-sm border border-orange-100">
                                    <div class="w-2 h-2 rounded-full bg-orange-500 animate-pulse"></div>
                                    <span class="flex-1 font-medium">{{ $item->name }}</span>
                                    <span class="font-bold bg-white px-2 py-0.5 rounded shadow-sm text-xs border border-orange-200">{{ $item->stock }} pcs</span>
                                </div>
                            @empty
                                <p class="text-sm text-gray-400 italic text-center py-4">Aman! Stok melimpah.</p>
                            @endforelse
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
</x-app-layout>