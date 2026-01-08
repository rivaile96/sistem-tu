<x-app-layout>
    
    <div x-data="{ 
        showPayModal: false, 
        showGenerateModal: false,
        selectedBill: null,
        actionUrl: '',

        openPayModal(bill) {
            this.selectedBill = bill;
            this.actionUrl = '/spp/' + bill.id + '/pay-manual';
            this.showPayModal = true;
        },

        async payOnline(bill) {
            try {
                Swal.fire({ 
                    title: 'Memproses...', 
                    didOpen: () => Swal.showLoading(), 
                    allowOutsideClick: false, 
                    showConfirmButton: false,
                    background: 'transparent',
                    color: '#fff',
                    backdrop: 'rgba(0,0,0,0.8)'
                });

                let response = await fetch('/spp/' + bill.id + '/midtrans-token');
                let data = await response.json();

                Swal.close();

                if (data.error) {
                    Swal.fire({ icon: 'error', title: 'Oops...', text: data.error });
                    return;
                }

                window.snap.pay(data.token, {
                    onSuccess: function(result){
                        Swal.fire('Berhasil', 'Pembayaran diterima!', 'success').then(() => location.reload());
                    },
                    onPending: function(result){
                        Swal.fire('Pending', 'Menunggu pembayaran...', 'info').then(() => location.reload());
                    },
                    onError: function(result){
                        Swal.fire('Gagal', 'Pembayaran gagal.', 'error');
                    }
                });
            } catch (error) {
                console.error(error);
                Swal.fire({ icon: 'error', title: 'Error', text: 'Terjadi kesalahan sistem' });
            }
        }
    }">
        
        <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('services.midtrans.client_key') }}"></script>
        
        <style>
            [x-cloak] { display: none !important; }
        </style>

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 flex flex-col overflow-hidden">
            
            <div class="p-6 border-b border-gray-100 flex flex-col md:flex-row justify-between items-center gap-4 bg-white">
                <div>
                    <h2 class="text-xl font-bold text-gray-800">Daftar Tagihan SPP</h2>
                    <p class="text-sm text-gray-500 mt-1">Kelola data tagihan siswa (Manual & Otomatis).</p>
                </div>
                
                @if(auth()->user()->role !== 'student')
                    <button type="button" @click="showGenerateModal = true" class="bg-[#0ea5e9] hover:bg-sky-600 text-white px-5 py-2.5 rounded-xl text-sm font-bold transition shadow-md hover:shadow-sky-200 flex items-center gap-2 active:scale-95">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                        Generate Tagihan
                    </button>
                @endif
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
                                <div class="text-xs text-gray-400 font-medium">{{ $bill->student->class_name }}</div>
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
                                    @if($bill->status != 'LUNAS')
                                        @if(auth()->user()->role !== 'student')
                                            <button type="button" @click='openPayModal(@json($bill))' 
                                                class="text-gray-600 hover:text-gray-900 font-bold text-xs px-3 py-1.5 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                                                Manual
                                            </button>
                                        @endif
                                        
                                        <button type="button" @click='payOnline(@json($bill))' 
                                            class="bg-[#0ea5e9] text-white hover:bg-sky-600 px-4 py-1.5 rounded-lg text-xs font-bold shadow-md hover:shadow-sky-100 transition active:scale-95">
                                            Bayar Online
                                        </button>
                                    @else
                                        <a href="{{ route('spp.print', $bill->id) }}" target="_blank"
                                           class="text-gray-600 hover:text-[#0ea5e9] bg-gray-50 hover:bg-sky-50 border border-gray-200 px-3 py-1.5 rounded-lg text-xs font-bold transition flex items-center gap-1.5">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                            Cetak Invoice
                                        </a>
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

        <div x-show="showGenerateModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4" 
             x-transition.opacity x-cloak>
            <div class="bg-white w-full max-w-lg rounded-2xl shadow-2xl p-6 transform transition-all max-h-[90vh] overflow-y-auto" @click.away="showGenerateModal = false">
                <div class="flex justify-between items-center mb-6 border-b border-gray-100 pb-4">
                    <h3 class="text-xl font-bold text-gray-800">Generate Tagihan Massal</h3>
                    <button @click="showGenerateModal = false" class="text-gray-400 hover:text-gray-600 bg-gray-100 rounded-full p-1 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                    </button>
                </div>
                <form id="generateForm" action="{{ route('spp.store_generate') }}" method="POST">
                    @csrf
                    <div class="mb-5">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Pilih Bulan & Tahun</label>
                        <input type="month" name="month" required class="w-full rounded-xl border-gray-300 focus:ring-[#0ea5e9] focus:border-[#0ea5e9] py-3 px-4 text-gray-700 shadow-sm" value="{{ date('Y-m') }}">
                    </div>
                    <div class="mb-6">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Nominal SPP (Rp)</label>
                        <div class="relative">
                            <span class="absolute left-4 top-3.5 text-gray-400 font-bold">Rp</span>
                            <input type="number" name="amount" required min="0" value="350000" class="w-full pl-12 pr-4 py-3 rounded-xl border-gray-300 focus:ring-[#0ea5e9] focus:border-[#0ea5e9] font-bold text-gray-800 text-lg shadow-sm">
                        </div>
                    </div>
                    <div class="bg-blue-50 border border-blue-100 rounded-xl p-4 mb-6 flex gap-3 text-sm text-blue-800">
                        <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <p class="leading-relaxed">Sistem akan membuat tagihan otomatis untuk <b>SEMUA SISWA</b> yang aktif saat ini.</p>
                    </div>
                    <div class="flex gap-3">
                        <button type="button" @click="showGenerateModal = false" class="flex-1 px-4 py-3 border border-gray-300 text-gray-700 rounded-xl font-bold hover:bg-gray-50 transition">Batal</button>
                        <button type="button" onclick="confirmGenerateInModal()" class="flex-1 px-4 py-3 bg-[#0ea5e9] hover:bg-sky-600 text-white rounded-xl font-bold shadow-lg shadow-sky-200 transition">Proses Sekarang</button>
                    </div>
                </form>
            </div>
        </div>

        <div x-show="showPayModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4" 
             x-transition.opacity x-cloak>
            <div class="bg-white w-full max-w-md rounded-2xl shadow-2xl p-6 transform transition-all" @click.away="showPayModal = false">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-bold flex items-center gap-2 text-gray-800">
                        <div class="p-2 bg-blue-50 text-[#0ea5e9] rounded-lg">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        </div>
                        Input Bayar Manual
                    </h3>
                    <button @click="showPayModal = false" class="text-gray-400 hover:text-gray-600">✕</button>
                </div>

                <div class="bg-gray-50 p-4 rounded-xl mb-6 border border-gray-200">
                    <div class="flex justify-between mb-2">
                        <span class="text-xs text-gray-500 font-bold uppercase tracking-wide">Siswa</span>
                        <span class="text-sm font-bold text-gray-900" x-text="selectedBill?.student?.name"></span>
                    </div>
                    <div class="flex justify-between pt-3 border-t border-gray-200 mt-2">
                        <span class="text-xs text-gray-500 font-bold uppercase tracking-wide mt-1">Total Tagihan</span>
                        <span class="text-lg font-bold text-[#0ea5e9]">Rp <span x-text="new Intl.NumberFormat('id-ID').format(selectedBill?.amount)"></span></span>
                    </div>
                </div>

                <form :action="actionUrl" method="POST">
                    @csrf
                    <div class="mb-6">
                        <label class="block text-sm font-bold text-gray-700 mb-2">Metode Penerimaan</label>
                        <select name="payment_method" class="w-full rounded-xl border-gray-300 focus:ring-[#0ea5e9] focus:border-[#0ea5e9] text-sm py-3 px-4 appearance-none font-medium shadow-sm">
                            <option value="Tunai (Loket TU)">Tunai (Loket TU)</option>
                            <option value="Transfer Bank Manual">Transfer Bank (Cek Mutasi)</option>
                        </select>
                    </div>
                    <div class="flex gap-3">
                        <button type="button" @click="showPayModal = false" class="flex-1 px-4 py-3 border border-gray-300 text-gray-700 rounded-xl font-bold hover:bg-gray-50 transition">Batal</button>
                        <button type="submit" class="flex-1 px-4 py-3 bg-[#0ea5e9] hover:bg-sky-600 text-white rounded-xl font-bold shadow-lg shadow-sky-200 transition">Konfirmasi Lunas</button>
                    </div>
                </form>
            </div>
        </div>

    </div>

    <script>
        function confirmGenerateInModal() {
            Swal.fire({
                title: 'Konfirmasi',
                text: "Yakin ingin membuat tagihan massal untuk bulan ini?",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#0ea5e9',
                cancelButtonColor: '#9ca3af',
                confirmButtonText: 'Ya, Proses!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({ title: 'Memproses...', didOpen: () => Swal.showLoading(), showConfirmButton: false });
                    document.getElementById('generateForm').submit();
                }
            });
        }
    </script>
</x-app-layout>