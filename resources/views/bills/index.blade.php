<x-app-layout>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <div class="bg-slate-50 min-h-screen pb-12">
        
        <div class="bg-white border-b border-gray-200 px-6 py-8 shadow-sm">
            <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-slate-800 flex items-center gap-2">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                        Monitoring Keuangan
                    </h2>
                    <p class="text-sm text-slate-500 mt-1 ml-10">Rekapitulasi tagihan, pembayaran, dan tunggakan siswa.</p>
                </div>
                
                <div class="flex gap-3">
                    <a href="{{ route('bills.export', request()->all()) }}" class="flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2.5 rounded-lg font-semibold text-sm shadow-lg shadow-emerald-200 transition-all transform hover:-translate-y-1">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                        Export Data
                    </a>
                    <a href="{{ route('bills.create') }}" class="flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-5 py-2.5 rounded-lg font-semibold text-sm shadow-lg shadow-blue-200 transition-all transform hover:-translate-y-1">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        Buat Tagihan
                    </a>
                </div>
            </div>
        </div>

        <div class="px-6 mt-8">
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white rounded-xl p-6 border border-slate-100 shadow-sm hover:shadow-md transition-shadow group relative overflow-hidden">
                    <div class="absolute right-0 top-0 h-full w-1 bg-blue-500"></div>
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Total Tagihan (Periode Ini)</p>
                            <h3 class="text-2xl font-extrabold text-slate-800">Rp {{ number_format($totalTagihan, 0, ',', '.') }}</h3>
                        </div>
                        <div class="p-3 bg-blue-50 text-blue-600 rounded-lg group-hover:scale-110 transition-transform">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl p-6 border border-slate-100 shadow-sm hover:shadow-md transition-shadow group relative overflow-hidden">
                    <div class="absolute right-0 top-0 h-full w-1 bg-emerald-500"></div>
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Uang Masuk (Lunas)</p>
                            <h3 class="text-2xl font-extrabold text-emerald-600">Rp {{ number_format($totalSudahBayar, 0, ',', '.') }}</h3>
                        </div>
                        <div class="p-3 bg-emerald-50 text-emerald-600 rounded-lg group-hover:scale-110 transition-transform">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl p-6 border border-slate-100 shadow-sm hover:shadow-md transition-shadow group relative overflow-hidden">
                    <div class="absolute right-0 top-0 h-full w-1 bg-rose-500"></div>
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Sisa Tunggakan</p>
                            <h3 class="text-2xl font-extrabold text-rose-600">Rp {{ number_format($totalTunggakan, 0, ',', '.') }}</h3>
                        </div>
                        <div class="p-3 bg-rose-50 text-rose-600 rounded-lg group-hover:scale-110 transition-transform">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-xl shadow-sm border border-slate-200 mb-6">
                <form method="GET" action="{{ route('bills.index') }}">
                    <div class="grid grid-cols-1 md:grid-cols-6 gap-4 items-end">
                        
                        <div class="md:col-span-2">
                            <label class="block text-xs font-bold text-slate-500 mb-1">Rentang Tanggal</label>
                            <div class="flex items-center gap-2">
                                <input type="date" name="start_date" value="{{ request('start_date') }}" class="w-full rounded-lg border-slate-300 text-sm focus:ring-blue-500 focus:border-blue-500 text-slate-600">
                                <span class="text-slate-400">-</span>
                                <input type="date" name="end_date" value="{{ request('end_date') }}" class="w-full rounded-lg border-slate-300 text-sm focus:ring-blue-500 focus:border-blue-500 text-slate-600">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-500 mb-1">Kelas</label>
                            <select name="class_name" class="w-full rounded-lg border-slate-300 text-sm focus:ring-blue-500 text-slate-600">
                                <option value="">Semua Kelas</option>
                                @foreach($classes as $c)
                                    <option value="{{ $c }}" {{ request('class_name') == $c ? 'selected' : '' }}>{{ $c }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-500 mb-1">Jenis</label>
                            <select name="type" class="w-full rounded-lg border-slate-300 text-sm focus:ring-blue-500 text-slate-600">
                                <option value="">Semua Tipe</option>
                                @foreach($types as $t)
                                    <option value="{{ $t }}" {{ request('type') == $t ? 'selected' : '' }}>{{ $t }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-500 mb-1">Status</label>
                            <select name="status" class="w-full rounded-lg border-slate-300 text-sm focus:ring-blue-500 text-slate-600">
                                <option value="">Semua Status</option>
                                <option value="UNPAID" {{ request('status') == 'UNPAID' ? 'selected' : '' }}>Belum Bayar</option>
                                <option value="PAID" {{ request('status') == 'PAID' ? 'selected' : '' }}>Lunas</option>
                            </select>
                        </div>

                        <div>
                            <button type="submit" class="w-full bg-slate-800 hover:bg-slate-900 text-white py-2.5 rounded-lg text-sm font-bold shadow-md transition-all flex justify-center items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                Filter Data
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-xs uppercase text-slate-500 font-bold tracking-wider">
                            <th class="px-6 py-4">Siswa</th>
                            <th class="px-6 py-4">Tagihan</th>
                            <th class="px-6 py-4 text-right">Nominal</th>
                            <th class="px-6 py-4 text-center">Jatuh Tempo</th>
                            <th class="px-6 py-4 text-center">Status</th>
                            <th class="px-6 py-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($bills as $bill)
                        <tr class="hover:bg-slate-50 transition-colors duration-200">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-xs">
                                        {{ substr($bill->student->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-700 text-sm">{{ $bill->student->name }}</div>
                                        <div class="text-xs text-slate-400">{{ $bill->student->class_name }}</div>
                                    </div>
                                </div>
                            </td>
                            
                            <td class="px-6 py-4">
                                <div class="text-sm text-slate-700 font-medium">{{ $bill->name }}</div>
                                <div class="text-xs text-slate-400 mt-0.5">Dibuat: {{ $bill->created_at->format('d M Y') }}</div>
                            </td>

                            <td class="px-6 py-4 text-right">
                                <span class="font-bold text-slate-700">Rp {{ number_format($bill->amount, 0, ',', '.') }}</span>
                            </td>

                            <td class="px-6 py-4 text-center">
                                @if($bill->due_date && $bill->status == 'UNPAID')
                                    @php $isOverdue = \Carbon\Carbon::parse($bill->due_date)->isPast(); @endphp
                                    <div class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-xs font-medium {{ $isOverdue ? 'bg-rose-50 text-rose-600 border border-rose-100' : 'bg-slate-100 text-slate-500 border border-slate-200' }}">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                        {{ \Carbon\Carbon::parse($bill->due_date)->format('d M') }}
                                    </div>
                                @else
                                    <span class="text-slate-300 text-xs">-</span>
                                @endif
                            </td>

                            <td class="px-6 py-4 text-center">
                                @if($bill->status == 'PAID')
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700 border border-emerald-200">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                        LUNAS
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-500 border border-slate-200">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        BELUM
                                    </span>
                                @endif
                            </td>

                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    @if($bill->status == 'UNPAID')
                                        <form action="{{ route('bills.pay', $bill->id) }}" method="POST" class="pay-form">
                                            @csrf @method('PATCH')
                                            <button type="button" class="btn-pay group bg-blue-50 hover:bg-blue-600 text-blue-600 hover:text-white p-2 rounded-lg transition-colors border border-blue-100 hover:border-blue-600" title="Bayar Sekarang">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                            </button>
                                        </form>

                                        <form action="{{ route('bills.destroy', $bill->id) }}" method="POST" class="delete-form">
                                            @csrf @method('DELETE')
                                            <button type="button" class="btn-delete group bg-white hover:bg-rose-500 text-rose-500 hover:text-white p-2 rounded-lg transition-colors border border-rose-200 hover:border-rose-500" title="Hapus Tagihan">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                        </form>
                                    @else
                                        <a href="{{ route('bills.print', $bill->id) }}" target="_blank" class="bg-gray-50 hover:bg-gray-100 text-gray-600 p-2 rounded-lg transition-colors border border-gray-200" title="Cetak Kwitansi">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                                <div class="flex flex-col items-center justify-center">
                                    <svg class="w-16 h-16 text-slate-200 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                    <p class="text-sm font-medium">Tidak ada data tagihan ditemukan.</p>
                                    <p class="text-xs mt-1">Coba sesuaikan filter tanggal atau buat tagihan baru.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-6">
                {{ $bills->links() }}
            </div>
        </div>
    </div>

    <script>
        // SweetAlert Konfirmasi Bayar
        document.querySelectorAll('.btn-pay').forEach(btn => {
            btn.addEventListener('click', function() {
                const form = this.closest('.pay-form');
                Swal.fire({
                    title: 'Terima Pembayaran?',
                    text: "Sistem akan mencatat pelunasan dan memotong stok barang (jika ada).",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#2563eb',
                    cancelButtonColor: '#94a3b8',
                    confirmButtonText: 'Ya, Proses!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) form.submit();
                });
            });
        });

        // SweetAlert Konfirmasi Hapus
        document.querySelectorAll('.btn-delete').forEach(btn => {
            btn.addEventListener('click', function() {
                const form = this.closest('.delete-form');
                Swal.fire({
                    title: 'Hapus Tagihan?',
                    text: "Data ini akan dihapus permanen. Lanjutkan?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#e11d48',
                    cancelButtonColor: '#94a3b8',
                    confirmButtonText: 'Ya, Hapus!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) form.submit();
                });
            });
        });

        // Notifikasi Toast
        @if(session('success'))
            const Toast = Swal.mixin({
                toast: true, position: 'top-end', showConfirmButton: false, timer: 3000, timerProgressBar: true,
                didOpen: (toast) => { toast.addEventListener('mouseenter', Swal.stopTimer); toast.addEventListener('mouseleave', Swal.resumeTimer); }
            });
            Toast.fire({ icon: 'success', title: "{{ session('success') }}" });
        @endif
        
        @if(session('error'))
            Swal.fire({ icon: 'error', title: 'Oops...', text: "{{ session('error') }}" });
        @endif
    </script>
</x-app-layout>