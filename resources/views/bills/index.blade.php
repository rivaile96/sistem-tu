<x-app-layout>
    <div class="space-y-6">
        
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Monitoring Tagihan</h2>
                <p class="text-sm text-gray-500">Pantau status pembayaran, tagihan siswa, dan cetak laporan.</p>
            </div>
            
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('bills.export', request()->query()) }}" target="_blank" 
                   class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-bold shadow-md transition flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    Export Excel
                </a>

                <a href="{{ route('bills.create') }}" 
                   class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-bold shadow-md transition flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Buat Tagihan
                </a>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="p-5 bg-white rounded-xl border border-gray-100 shadow-sm relative overflow-hidden">
                <div class="relative z-10">
                    <p class="text-xs text-gray-500 font-bold uppercase tracking-wider">Total Tagihan (Terbit)</p>
                    <h3 class="text-2xl font-bold text-gray-800 mt-1">Rp {{ number_format($totalTagihan, 0, ',', '.') }}</h3>
                </div>
                <div class="absolute right-0 top-0 h-full w-1 bg-gray-200"></div>
            </div>
            
            <div class="p-5 bg-green-50 rounded-xl border border-green-100 shadow-sm relative overflow-hidden">
                <div class="relative z-10">
                    <p class="text-xs text-green-600 font-bold uppercase tracking-wider flex items-center gap-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        Uang Masuk (Lunas)
                    </p>
                    <h3 class="text-2xl font-bold text-green-700 mt-1">Rp {{ number_format($totalSudahBayar, 0, ',', '.') }}</h3>
                </div>
            </div>

            <div class="p-5 bg-red-50 rounded-xl border border-red-100 shadow-sm relative overflow-hidden">
                <div class="relative z-10">
                    <p class="text-xs text-red-600 font-bold uppercase tracking-wider flex items-center gap-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        Sisa Tunggakan
                    </p>
                    <h3 class="text-2xl font-bold text-red-700 mt-1">Rp {{ number_format($totalTunggakan, 0, ',', '.') }}</h3>
                </div>
            </div>
        </div>

        <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100">
            <form method="GET" action="{{ route('bills.index') }}" class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                
                <div class="md:col-span-4">
                    <label class="text-xs font-bold text-gray-500 uppercase mb-1 block">Cari Data</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </span>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Nama Siswa / Tagihan..." 
                               class="w-full pl-9 text-sm rounded-lg border-gray-200 focus:ring-blue-500 focus:border-blue-500 py-2">
                    </div>
                </div>

                <div class="md:col-span-3">
                    <label class="text-xs font-bold text-gray-500 uppercase mb-1 block">Kelas</label>
                    <select name="class_name" class="w-full text-sm rounded-lg border-gray-200 focus:ring-blue-500 focus:border-blue-500 bg-gray-50 py-2">
                        <option value="">-- Semua Kelas --</option>
                        @foreach($classes as $cls)
                            <option value="{{ $cls }}" {{ request('class_name') == $cls ? 'selected' : '' }}>{{ $cls }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label class="text-xs font-bold text-gray-500 uppercase mb-1 block">Jenis</label>
                    <select name="type" class="w-full text-sm rounded-lg border-gray-200 focus:ring-blue-500 focus:border-blue-500 bg-gray-50 py-2">
                        <option value="">-- Semua Jenis --</option>
                        @foreach($types as $type)
                            <option value="{{ $type }}" {{ request('type') == $type ? 'selected' : '' }}>{{ $type }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="md:col-span-3 flex gap-2">
                    <div class="w-full">
                        <label class="text-xs font-bold text-gray-500 uppercase mb-1 block">Status</label>
                        <select name="status" class="w-full text-sm rounded-lg border-gray-200 focus:ring-blue-500 focus:border-blue-500 bg-gray-50 py-2">
                            <option value="">-- Semua Status --</option>
                            <option value="UNPAID" {{ request('status') == 'UNPAID' ? 'selected' : '' }}>⏳ Belum Lunas</option>
                            <option value="PAID" {{ request('status') == 'PAID' ? 'selected' : '' }}>✅ Lunas</option>
                        </select>
                    </div>
                    <div class="mb-[1px]"> <button type="submit" class="bg-gray-800 text-white p-2.5 rounded-lg hover:bg-gray-900 transition shadow-sm h-[38px] w-[38px] flex items-center justify-center" title="Filter Data">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                        </button>
                    </div>
                </div>

            </form>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50 text-gray-500 font-bold uppercase text-xs border-b border-gray-100">
                        <tr>
                            <th class="px-6 py-4">Siswa</th>
                            <th class="px-6 py-4">Tagihan</th>
                            <th class="px-6 py-4">Nominal</th>
                            <th class="px-6 py-4 text-center">Status</th>
                            <th class="px-6 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($bills as $bill)
                        <tr class="hover:bg-gray-50 transition group">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-xs">
                                        {{ substr($bill->student->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <p class="font-bold text-gray-800">{{ $bill->student->name }}</p>
                                        <p class="text-xs text-gray-500 font-mono">{{ $bill->student->class_name }}</p>
                                    </div>
                                </div>
                            </td>

                            <td class="px-6 py-4">
                                <p class="font-medium text-gray-800">{{ $bill->name }}</p>
                                <span class="text-[10px] bg-gray-100 px-2 py-0.5 rounded text-gray-600 border border-gray-200">{{ $bill->type }}</span>
                            </td>

                            <td class="px-6 py-4 font-bold text-gray-700">
                                {{ $bill->formatted_amount }}
                            </td>

                            <td class="px-6 py-4 text-center">
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold border {{ $bill->status_color }}">
                                    {{ $bill->status == 'UNPAID' ? 'BELUM LUNAS' : 'LUNAS' }}
                                </span>
                            </td>

                            <td class="px-6 py-4 text-right">
                                @if($bill->status == 'UNPAID')
                                    <button onclick="confirmQuickPay('{{ $bill->id }}', '{{ $bill->student->name }}', '{{ $bill->name }}')" 
                                            class="text-blue-600 hover:text-white border border-blue-600 hover:bg-blue-600 font-bold text-xs px-3 py-1.5 rounded transition shadow-sm">
                                        Bayar
                                    </button>
                                @else
                                    <div class="flex items-center justify-end gap-2">
                                        <span class="text-green-600 font-bold text-xs flex items-center gap-1">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                            Selesai
                                        </span>
                                        <a href="{{ route('bills.print', $bill->id) }}" target="_blank" 
                                           class="text-gray-500 hover:text-gray-800 bg-gray-100 hover:bg-gray-200 p-1.5 rounded border border-gray-200 transition" 
                                           title="Cetak Kwitansi">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                        </a>
                                    </div>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-gray-400">
                                <div class="flex flex-col items-center">
                                    <svg class="w-12 h-12 mb-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                    <p>Tidak ada data tagihan sesuai filter.</p>
                                    <p class="text-xs mt-1">Coba ubah filter atau buat tagihan baru.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4 border-t border-gray-100 bg-gray-50">
                {{ $bills->links() }}
            </div>
        </div>
    </div>

    <script>
        function confirmQuickPay(id, studentName, billName) {
            Swal.fire({
                title: 'Konfirmasi Pembayaran',
                html: `Terima pembayaran <b>${studentName}</b><br>Untuk: <span class="text-blue-600 font-bold">${billName}</span>?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#2563eb', // Blue
                cancelButtonColor: '#d1d5db',
                confirmButtonText: 'Ya, Lunasi',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Buat Form Post Dinamis
                    let form = document.createElement('form');
                    form.method = 'POST';
                    form.action = `/bills/${id}/pay`;
                    form.innerHTML = `@csrf`; // Token CSRF Laravel
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }
    </script>
</x-app-layout>