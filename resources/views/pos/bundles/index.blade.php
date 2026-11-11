<x-app-layout>
    <x-slot name="header">
        Paket Bundel POS
    </x-slot>

    <div class="space-y-6">
        
        <!-- HEADER & ACTIONS -->
        <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
            <div>
                <h1 class="flex items-center gap-3 text-2xl font-black text-slate-800">
                    <div class="bg-[#178BC1] p-2.5 rounded-xl text-white shadow-sm shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <line x1="16.5" y1="9.4" x2="7.5" y2="4.21"></line>
                            <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                            <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                            <line x1="12" y1="22.08" x2="12" y2="12"></line>
                        </svg>
                    </div>
                    Daftar Paket Bundling
                </h1>
                <p class="mt-1 text-xs text-slate-500 font-medium">
                    Kelola paket bundling barang untuk kemudahan transaksi kasir dan tagihan seragam/buku.
                </p>
            </div>
            
            <a href="{{ route('pos.bundles.create') }}" 
               class="inline-flex items-center gap-2 bg-[#178BC1] hover:bg-[#126da3] text-white px-4 py-2.5 rounded-xl font-bold text-xs shadow-sm transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                <span>Buat Paket Baru</span>
            </a>
        </div>

        <!-- STAT CARDS (SOLID - NO GRADIENTS) -->
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-6">
            <div class="p-6 bg-white border shadow-sm rounded-2xl border-slate-100 flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Paket</p>
                    <h3 class="text-2xl font-bold text-slate-800 mt-1">{{ $bundles->count() }}</h3>
                </div>
                <div class="w-12 h-12 rounded-xl bg-blue-50 text-[#178BC1] border border-blue-100 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                        <line x1="16.5" y1="9.4" x2="7.5" y2="4.21"></line>
                        <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                    </svg>
                </div>
            </div>

            <div class="p-6 bg-white border shadow-sm rounded-2xl border-slate-100 flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Item</p>
                    <h3 class="text-2xl font-bold text-slate-800 mt-1">{{ $bundles->sum('items_count') }}</h3>
                </div>
                <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 border border-emerald-100 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                        <line x1="8" y1="6" x2="21" y2="6"></line><line x1="8" y1="12" x2="21" y2="12"></line><line x1="8" y1="18" x2="21" y2="18"></line>
                    </svg>
                </div>
            </div>

            <div class="p-6 bg-white border shadow-sm rounded-2xl border-slate-100 flex items-center justify-between">
                <div>
                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Paket Aktif</p>
                    <h3 class="text-2xl font-bold text-emerald-600 mt-1">{{ $bundles->count() }}</h3>
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
                    <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Rata-rata Harga</p>
                    <h3 class="text-2xl font-bold text-slate-800 mt-1">
                        Rp {{ number_format($bundles->avg('price') ?? 0, 0, ',', '.') }}
                    </h3>
                </div>
                <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 border border-amber-100 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                        <line x1="12" y1="1" x2="12" y2="23"></line>
                        <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- TABLE BUNDLES (SPMB MATCHING THEAD & TBODY) -->
        <div class="overflow-hidden bg-white border shadow-sm rounded-2xl border-slate-200">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                <h3 class="font-bold text-slate-800 text-sm">Daftar Paket Bundling</h3>
                <span class="text-xs text-slate-500 font-medium">{{ $bundles->count() }} paket terdaftar</span>
            </div>

            <div class="overflow-x-auto min-h-[300px]">
                <table class="w-full text-sm text-left">
                    <thead class="bg-slate-50/80 text-slate-500 uppercase font-bold text-[10px] tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-4">Nama Paket</th>
                            <th class="px-6 py-4">Harga Paket</th>
                            <th class="px-6 py-4 text-center">Komposisi Item</th>
                            <th class="px-6 py-4 text-center">Status</th>
                            <th class="px-6 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($bundles as $bundle)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-xl bg-blue-50 border border-blue-200 text-[#178BC1] font-bold flex items-center justify-center shrink-0 text-xs">
                                            {{ strtoupper(substr($bundle->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <p class="text-xs font-bold text-slate-800">{{ $bundle->name }}</p>
                                            <p class="text-[11px] text-slate-400 mt-0.5">Dibuat {{ $bundle->created_at->format('d M Y') }}</p>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-6 py-4 font-bold text-xs text-slate-800">
                                    Rp {{ number_format($bundle->price, 0, ',', '.') }}
                                </td>

                                <td class="px-6 py-4 text-center">
                                    <span class="px-2.5 py-1 text-[10px] font-bold rounded-lg bg-slate-100 text-slate-700 border border-slate-200">
                                        {{ $bundle->items_count }} Jenis Barang
                                    </span>
                                </td>

                                <td class="px-6 py-4 text-center">
                                    <span class="px-2.5 py-0.5 rounded-lg text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        Aktif
                                    </span>
                                </td>

                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('pos.bundles.edit', $bundle->id) }}" 
                                           class="p-1.5 text-slate-600 hover:text-slate-900 bg-slate-100 hover:bg-slate-200 rounded-lg border border-slate-200 transition-colors"
                                           title="Edit Paket">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                            </svg>
                                        </a>

                                        <form action="{{ route('pos.bundles.destroy', $bundle->id) }}" method="POST" onsubmit="return confirm('Hapus paket ini?')" class="inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="p-1.5 text-rose-600 hover:bg-rose-50 rounded-lg border border-transparent hover:border-rose-200 transition-colors"
                                                    title="Hapus Paket">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
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
                                    <p class="text-sm font-bold text-slate-600">Belum ada paket bundling yang dibuat.</p>
                                    <p class="text-xs text-slate-400 mt-1">Klik Buat Paket Baru untuk membuat paket bundel barang.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</x-app-layout>
