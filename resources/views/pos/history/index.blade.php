@extends('layouts.app')

@section('content')
<div x-data="{ 
    showDetail: false,
    detail: null,
    loading: false,
    async fetchDetail(id) {
        this.loading = true;
        this.showDetail = true;
        this.detail = null; // Reset dulu
        
        try {
            let res = await fetch('/pos/history/' + id);
            this.detail = await res.json();
        } catch (e) {
            alert('Gagal mengambil data');
        } finally {
            this.loading = false;
        }
    },
    formatRupiah(num) {
        return new Intl.NumberFormat('id-ID').format(num);
    },
    formatDate(dateString) {
        const options = { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' };
        return new Date(dateString).toLocaleDateString('id-ID', options);
    }
}">

    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
        <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
            <div>
                <h2 class="text-xl font-bold text-gray-800">Riwayat Penjualan</h2>
                <p class="text-sm text-gray-500">Rekap transaksi POS yang telah selesai.</p>
            </div>
            
            <form action="{{ route('pos.history.index') }}" method="GET" class="flex gap-2">
                <input type="date" name="start_date" value="{{ request('start_date', date('Y-m-d')) }}" 
                       class="px-3 py-2 border rounded-lg text-sm bg-gray-50 focus:ring-[#0ea5e9]">
                <span class="self-center">-</span>
                <input type="date" name="end_date" value="{{ request('end_date', date('Y-m-d')) }}" 
                       class="px-3 py-2 border rounded-lg text-sm bg-gray-50 focus:ring-[#0ea5e9]">
                <button type="submit" class="bg-[#0ea5e9] text-white px-4 py-2 rounded-lg text-sm shadow-md hover:bg-sky-600">
                    Filter
                </button>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 text-gray-600 font-semibold border-b">
                    <tr>
                        <th class="px-4 py-3">Tanggal</th>
                        <th class="px-4 py-3">Kode TRX</th>
                        <th class="px-4 py-3">Petugas</th> <th class="px-4 py-3">Total Belanja</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($orders as $order)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-3 text-gray-500">{{ $order->created_at->format('d M Y H:i') }}</td>
                        <td class="px-4 py-3 font-medium text-gray-800">{{ $order->transaction_code }}</td>
                        <td class="px-4 py-3">{{ $order->user->name ?? 'Admin TU' }}</td>
                        <td class="px-4 py-3 font-bold text-[#0ea5e9]">Rp {{ number_format($order->total_amount, 0, ',', '.') }}</td>
                        <td class="px-4 py-3">
                            <span class="bg-green-100 text-green-700 px-2 py-1 rounded text-xs font-bold">LUNAS</span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <button @click="fetchDetail({{ $order->id }})" 
                                    class="text-sky-500 hover:text-sky-700 font-medium text-xs border border-sky-200 px-3 py-1 rounded-lg hover:bg-sky-50 transition">
                                Detail
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-gray-400">
                            Belum ada transaksi pada tanggal ini.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="mt-4">
            {{ $orders->links() }}
        </div>
    </div>

    <div x-show="showDetail" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm"
         x-transition.opacity style="display: none;">
        
        <div class="bg-white w-full max-w-md rounded-2xl shadow-2xl overflow-hidden transform transition-all" 
             @click.away="showDetail = false">
            
            <div class="bg-[#0ea5e9] p-4 flex justify-between items-center text-white">
                <h3 class="font-bold">Detail Transaksi</h3>
                <button @click="showDetail = false" class="hover:bg-white/20 p-1 rounded">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <div class="p-6">
                <div x-show="loading" class="text-center py-8 text-gray-400">
                    <svg class="animate-spin h-8 w-8 mx-auto mb-2 text-[#0ea5e9]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Memuat data...
                </div>

                <div x-show="!loading && detail">
                    <div class="text-center mb-6">
                        <h2 class="text-2xl font-bold text-gray-800">Rp <span x-text="formatRupiah(detail?.total_amount)"></span></h2>
                        <p class="text-sm text-gray-500" x-text="detail?.transaction_code"></p>
                        <p class="text-xs text-gray-400 mt-1" x-text="detail ? formatDate(detail.created_at) : ''"></p>
                    </div>

                    <div class="border-t border-b border-gray-100 py-4 max-h-60 overflow-y-auto">
                        <template x-for="item in detail?.items" :key="item.id">
                            <div class="flex justify-between py-2 text-sm">
                                <div>
                                    <p class="font-medium text-gray-800" x-text="item.item.name"></p>
                                    <p class="text-xs text-gray-500"><span x-text="item.quantity"></span> x Rp <span x-text="formatRupiah(item.price_at_transaction)"></span></p>
                                </div>
                                <p class="font-bold text-gray-600">Rp <span x-text="formatRupiah(item.quantity * item.price_at_transaction)"></span></p>
                            </div>
                        </template>
                    </div>

                    <div class="mt-6">
                        <button class="w-full bg-gray-800 hover:bg-gray-900 text-white py-3 rounded-xl font-medium shadow-lg flex justify-center gap-2 items-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2.4-9h.01M17.2 8h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Cetak Struk (Print)
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection