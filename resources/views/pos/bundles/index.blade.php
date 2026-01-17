<x-app-layout>
    <div class="bg-gradient-to-br from-slate-50 to-blue-50 min-h-screen pb-12">
        
        <div class="bg-white border-b border-slate-200 px-8 py-8 shadow-lg relative overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-r from-blue-50/50 to-transparent pointer-events-none"></div>
            <div class="relative z-10">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                    <div class="flex items-center gap-4">
                        <div class="relative">
                            <div class="absolute inset-0 bg-gradient-to-r from-[#0284c7] to-blue-400 rounded-xl blur-lg opacity-30"></div>
                            <div class="relative bg-gradient-to-br from-[#0284c7] to-blue-600 p-3 rounded-xl shadow-lg">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                </svg>
                            </div>
                        </div>
                        <div>
                            <h2 class="text-3xl font-bold text-slate-800 tracking-tight">Daftar Paket Bundling</h2>
                            <p class="text-sm text-slate-500 mt-1">Kelola paket barang untuk sistem POS dan penjualan</p>
                        </div>
                    </div>
                    
                    <a href="{{ route('pos.bundles.create') }}" class="relative group">
                        <div class="absolute inset-0 bg-gradient-to-r from-[#0284c7] to-blue-500 rounded-xl blur opacity-75 group-hover:opacity-100 transition-opacity"></div>
                        <div class="relative flex items-center gap-3 bg-gradient-to-r from-[#0284c7] to-blue-500 hover:from-blue-600 hover:to-[#0284c7] text-white px-6 py-3.5 rounded-xl font-bold text-sm shadow-lg shadow-blue-200/50 transition-all transform group-hover:-translate-y-0.5">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            Buat Paket Baru
                        </div>
                    </a>
                </div>
            </div>
        </div>

        <div class="px-8 mt-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-gradient-to-br from-white to-slate-50 rounded-2xl p-6 border border-slate-100 shadow-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Total Paket</p>
                            <h3 class="text-2xl font-extrabold text-slate-800">{{ $bundles->count() }}</h3>
                        </div>
                        <div class="p-3 bg-blue-50 text-blue-600 rounded-xl">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-white to-slate-50 rounded-2xl p-6 border border-slate-100 shadow-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Total Item</p>
                            <h3 class="text-2xl font-extrabold text-slate-800">{{ $bundles->sum('items_count') }}</h3>
                        </div>
                        <div class="p-3 bg-emerald-50 text-emerald-600 rounded-xl">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-white to-slate-50 rounded-2xl p-6 border border-slate-100 shadow-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Paket Aktif</p>
                            <h3 class="text-2xl font-extrabold text-emerald-600">{{ $bundles->count() }}</h3>
                        </div>
                        <div class="p-3 bg-emerald-50 text-emerald-600 rounded-xl">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-white to-slate-50 rounded-2xl p-6 border border-slate-100 shadow-lg">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Rata-rata Harga</p>
                            <h3 class="text-2xl font-extrabold text-slate-800">
                                Rp {{ number_format($bundles->avg('price') ?? 0, 0, ',', '.') }}
                            </h3>
                        </div>
                        <div class="p-3 bg-amber-50 text-amber-600 rounded-xl">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-br from-white to-slate-50 rounded-2xl shadow-lg border border-slate-200 overflow-hidden mb-8">
                <div class="px-8 py-6 border-b border-slate-200 bg-gradient-to-r from-slate-50 to-white">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-bold text-slate-800 flex items-center gap-3">
                            <svg class="w-6 h-6 text-[#0284c7]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                            </svg>
                            Daftar Paket
                        </h3>
                        <div class="text-sm text-slate-500 bg-slate-100 px-3 py-1.5 rounded-lg">
                            Total: <span class="font-bold text-[#0284c7]">{{ $bundles->count() }}</span> paket
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-gradient-to-r from-slate-50 to-blue-50 border-b border-slate-200 text-sm uppercase text-slate-600 font-bold tracking-wider">
                                <th class="px-8 py-4 text-left">Nama Paket</th>
                                <th class="px-8 py-4 text-left">Detail Harga</th>
                                <th class="px-8 py-4 text-center">Komposisi Item</th>
                                <th class="px-8 py-4 text-center">Status</th>
                                <th class="px-8 py-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($bundles as $bundle)
                            <tr class="hover:bg-blue-50/30 transition-colors duration-200 group">
                                <td class="px-8 py-5">
                                    <div class="flex items-center gap-4">
                                        <div class="relative">
                                            <div class="absolute inset-0 bg-gradient-to-br from-blue-500 to-blue-400 rounded-full blur opacity-20 group-hover:opacity-30 transition"></div>
                                            <div class="relative w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-blue-400 text-white flex items-center justify-center font-bold text-sm shadow-md">
                                                {{ substr($bundle->name, 0, 1) }}
                                            </div>
                                        </div>
                                        <div>
                                            <div class="font-bold text-slate-800 text-base">{{ $bundle->name }}</div>
                                            <div class="text-xs text-slate-400 mt-0.5 flex items-center gap-1">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                                Dibuat: {{ $bundle->created_at->format('d M Y') }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                
                                <td class="px-8 py-5">
                                    <div class="text-2xl font-extrabold text-emerald-600">Rp {{ number_format($bundle->price, 0, ',', '.') }}</div>
                                    <div class="text-xs text-slate-400 mt-1 flex items-center gap-2">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        Harga per paket
                                    </div>
                                </td>

                                <td class="px-8 py-5 text-center">
                                    <div class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gradient-to-r from-slate-50 to-slate-100 border border-slate-200 shadow-sm">
                                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                        </svg>
                                        <span class="font-bold text-slate-700">{{ $bundle->items_count }}</span>
                                        <span class="text-sm text-slate-500">Jenis Barang</span>
                                    </div>
                                </td>

                                <td class="px-8 py-5 text-center">
                                    <span class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-bold bg-gradient-to-r from-emerald-50 to-emerald-100 text-emerald-700 border border-emerald-200 shadow-sm">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        Aktif
                                    </span>
                                </td>

                                <td class="px-8 py-5 text-right">
                                    <div class="flex items-center justify-end gap-3">
                                        <a href="{{ route('pos.bundles.edit', $bundle->id) }}" class="group bg-gradient-to-r from-blue-50 to-blue-100 hover:from-blue-600 hover:to-blue-500 text-blue-600 hover:text-white p-3 rounded-xl transition-all duration-300 border border-blue-200 hover:border-blue-600 shadow-sm hover:shadow-lg transform hover:-translate-y-0.5">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                        </a>
                                        
                                        <form action="{{ route('pos.bundles.destroy', $bundle->id) }}" method="POST" class="delete-form">
                                            @csrf @method('DELETE')
                                            <button type="button" class="btn-delete group bg-gradient-to-r from-white to-slate-50 hover:from-rose-600 hover:to-rose-500 text-rose-500 hover:text-white p-3 rounded-xl transition-all duration-300 border border-rose-200 hover:border-rose-500 shadow-sm hover:shadow-lg transform hover:-translate-y-0.5">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="px-8 py-16 text-center">
                                    <div class="flex flex-col items-center justify-center max-w-md mx-auto">
                                        <div class="relative mb-6">
                                            <div class="absolute inset-0 bg-gradient-to-r from-slate-200 to-slate-100 rounded-full blur-xl opacity-50"></div>
                                            <div class="relative w-24 h-24 rounded-full bg-gradient-to-br from-slate-100 to-white border border-slate-200 flex items-center justify-center shadow-lg">
                                                <svg class="w-12 h-12 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                                </svg>
                                            </div>
                                        </div>
                                        <h4 class="text-lg font-bold text-slate-600 mb-2">Belum ada paket bundling</h4>
                                        <p class="text-sm text-slate-400 mb-6">Mulai dengan membuat paket bundling pertama Anda</p>
                                        <a href="{{ route('pos.bundles.create') }}" class="relative group">
                                            <div class="absolute inset-0 bg-gradient-to-r from-[#0284c7] to-blue-500 rounded-xl blur opacity-75 group-hover:opacity-100 transition-opacity"></div>
                                            <div class="relative flex items-center gap-3 bg-gradient-to-r from-[#0284c7] to-blue-500 hover:from-blue-600 hover:to-[#0284c7] text-white px-6 py-3.5 rounded-xl font-bold text-sm shadow-lg shadow-blue-200/50 transition-all transform group-hover:-translate-y-0.5">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                                </svg>
                                                Buat Paket Baru
                                            </div>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // SweetAlert Konfirmasi Hapus
        document.querySelectorAll('.btn-delete').forEach(btn => {
            btn.addEventListener('click', function() {
                const form = this.closest('.delete-form');
                const row = this.closest('tr');
                const bundleName = row.querySelector('.font-bold.text-slate-800')?.textContent || 'paket ini';
                
                Swal.fire({
                    title: 'Hapus Paket?',
                    html: `Paket <strong>"${bundleName}"</strong> akan dihapus permanen.<br><span class="text-xs text-slate-400">Tindakan ini tidak dapat dibatalkan.</span>`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#e11d48',
                    cancelButtonColor: '#94a3b8',
                    confirmButtonText: 'Ya, Hapus Permanen',
                    cancelButtonText: 'Batal',
                    background: '#ffffff',
                    backdrop: 'rgba(225, 29, 72, 0.1)',
                    customClass: {
                        title: 'text-lg font-bold text-slate-800',
                        htmlContainer: 'text-slate-600'
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });

        // Notifikasi Toast
        @if(session('success'))
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                background: '#ffffff',
                iconColor: '#0284c7',
                color: '#1e293b',
                didOpen: (toast) => {
                    toast.addEventListener('mouseenter', Swal.stopTimer);
                    toast.addEventListener('mouseleave', Swal.resumeTimer);
                }
            });
            Toast.fire({
                icon: 'success',
                title: "{{ session('success') }}"
            });
        @endif
        
        @if(session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Terjadi Kesalahan',
                text: "{{ session('error') }}",
                confirmButtonColor: '#0284c7',
                background: '#ffffff'
            });
        @endif
    </script>
</x-app-layout>