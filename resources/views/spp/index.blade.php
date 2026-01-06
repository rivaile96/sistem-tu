@extends('layouts.app')

@section('content')
<div x-data="{ 
    showPayModal: false, 
    showGenerateModal: false,
    selectedBill: null,
    actionUrl: '',

    openPayModal(bill) {
        this.selectedBill = bill;
        this.actionUrl = '/spp/' + bill.id + '/pay';
        this.showPayModal = true;
    },

    async payOnline(bill) {
        try {
            let response = await fetch('/spp/' + bill.id + '/midtrans');
            let data = await response.json();

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

    <!-- Alpine.js for x-data / x-show -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>[x-cloak]{display:none!important;}</style>

    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h2 class="text-xl font-bold text-gray-800">Data Tagihan SPP</h2>
                <p class="text-sm text-gray-500">Kelola pembayaran siswa (Manual & Otomatis).</p>
            </div>
            
            <button type="button" @click="showGenerateModal = true" class="bg-[#0ea5e9] hover:bg-sky-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition shadow-lg shadow-sky-200 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                Generate Tagihan
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 text-gray-600 font-semibold border-b">
                    <tr>
                        <th class="px-4 py-3">NIS</th>
                        <th class="px-4 py-3">Nama Siswa</th>
                        <th class="px-4 py-3">Bulan</th>
                        <th class="px-4 py-3">Nominal</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($bills as $bill)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-3 font-medium">{{ $bill->student->nis }}</td>
                        <td class="px-4 py-3">
                            <div class="font-semibold text-gray-800">{{ $bill->student->name }}</div>
                            <div class="text-xs text-gray-400">{{ $bill->student->class_name }}</div>
                        </td>
                        <td class="px-4 py-3 font-medium text-sky-600">{{ $bill->month }}</td>
                        <td class="px-4 py-3">Rp {{ number_format($bill->amount, 0, ',', '.') }}</td>
                        <td class="px-4 py-3">
                            @if($bill->status == 'LUNAS')
                                <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-bold inline-flex items-center gap-1">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    LUNAS
                                </span>
                            @elseif($bill->status == 'PENDING')
                                <span class="bg-yellow-100 text-yellow-700 px-3 py-1 rounded-full text-xs font-bold">PENDING</span>
                            @else
                                <span class="bg-red-100 text-red-700 px-3 py-1 rounded-full text-xs font-bold">BELUM</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right flex justify-end gap-2">
                            @if($bill->status != 'LUNAS')
                                <button type="button" @click='openPayModal(@json($bill))' 
                                    class="text-gray-500 hover:text-gray-700 font-medium text-xs px-2 py-1 border border-gray-200 rounded hover:bg-gray-50 transition">
                                    Manual
                                </button>
                                
                                <button type="button" @click='payOnline(@json($bill))' 
                                    class="bg-[#0ea5e9] text-white hover:bg-sky-600 px-3 py-1 rounded text-xs font-medium shadow-md shadow-sky-200 transition">
                                    Bayar Online
                                </button>
                            @else
                                <span class="text-xs text-gray-400 italic">Terbayar</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="mt-4">{{ $bills->links() }}</div>
        </div>
    </div>

    <div x-show="showGenerateModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm" 
         x-transition.opacity x-cloak>
        <div class="bg-white w-full max-w-lg rounded-2xl shadow-2xl p-6 transform transition-all" @click.away="showGenerateModal = false">
            
            <div class="flex justify-between items-center mb-6 border-b border-gray-100 pb-4">
                <h3 class="text-xl font-bold text-gray-800">Generate Tagihan Massal</h3>
                <button @click="showGenerateModal = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <form id="generateForm" action="{{ route('spp.store_generate') }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Pilih Bulan & Tahun</label>
                    <input type="month" name="month" required class="w-full rounded-xl border-gray-300 focus:ring-[#0ea5e9] focus:border-[#0ea5e9] py-2.5 px-4 text-gray-700" value="{{ date('Y-m') }}">
                </div>
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nominal SPP (Rp)</label>
                    <div class="relative">
                        <span class="absolute left-4 top-2.5 text-gray-400 font-bold">Rp</span>
                        <input type="number" name="amount" required min="0" value="350000" class="w-full pl-12 pr-4 py-2.5 rounded-xl border-gray-300 focus:ring-[#0ea5e9] focus:border-[#0ea5e9] font-bold text-gray-800">
                    </div>
                </div>
                <div class="bg-blue-50 border border-blue-100 rounded-xl p-3 mb-6 flex gap-3 text-sm text-blue-800">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <p>Tagihan akan dibuat untuk <b>SEMUA SISWA</b> aktif. Siswa yang sudah punya tagihan di bulan ini akan dilewati.</p>
                </div>
                <div class="flex gap-3">
                    <button type="button" @click="showGenerateModal = false" class="flex-1 px-4 py-3 border border-gray-200 text-gray-600 rounded-xl font-medium hover:bg-gray-50 transition">Batal</button>
                    <button type="button" onclick="confirmGenerateInModal()" class="flex-1 px-4 py-3 bg-[#0ea5e9] hover:bg-sky-600 text-white rounded-xl font-bold shadow-lg shadow-sky-200 transition">Proses Sekarang</button>
                </div>
            </form>
        </div>
    </div>

    <div x-show="showPayModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm" 
         x-transition.opacity x-cloak>
        <div class="bg-white w-full max-w-md rounded-2xl shadow-2xl p-6" @click.away="showPayModal = false">
            <h3 class="text-lg font-bold mb-4">Input Bayar Manual</h3>
            
            <div class="bg-blue-50 p-4 rounded-xl mb-4 border border-blue-100">
                <div class="flex justify-between mb-1">
                    <span class="text-xs text-blue-600 font-semibold uppercase">Siswa</span>
                    <span class="text-sm font-bold text-gray-800" x-text="selectedBill?.student?.name"></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-xs text-blue-600 font-semibold uppercase">Nominal</span>
                    <span class="text-sm font-bold text-[#0ea5e9]">Rp <span x-text="new Intl.NumberFormat('id-ID').format(selectedBill?.amount)"></span></span>
                </div>
            </div>

            <form :action="actionUrl" method="POST">
                @csrf
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Metode Penerimaan</label>
                    <select name="payment_method" class="w-full rounded-xl border-gray-300 focus:ring-[#0ea5e9] focus:border-[#0ea5e9] text-sm py-2.5">
                        <option value="Tunai (Loket TU)">Tunai (Loket TU)</option>
                        <option value="Transfer Bank Manual">Transfer Bank (Cek Mutasi)</option>
                        <option value="EDC Sekolah">EDC Mesin Sekolah</option>
                    </select>
                </div>
                <div class="flex gap-3">
                    <button type="button" @click="showPayModal = false" class="flex-1 px-4 py-2 border border-gray-200 text-gray-600 rounded-xl font-medium hover:bg-gray-50 transition">Batal</button>
                    <button type="submit" class="flex-1 px-4 py-2 bg-[#0ea5e9] hover:bg-sky-600 text-white rounded-xl font-medium shadow-lg shadow-sky-200 transition">Konfirmasi Lunas</button>
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
                Swal.fire({ title: 'Memproses...', didOpen: () => Swal.showLoading() });
                document.getElementById('generateForm').submit();
            }
        });
    }
</script>
@endsection