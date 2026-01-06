@extends('layouts.app')

@section('content')
<div class="flex flex-col lg:flex-row h-[calc(100vh-8rem)] gap-6" 
     x-data="posSystem()">

    <div class="flex-1 flex flex-col bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-4 border-b border-gray-100">
            <div class="relative">
                <input type="text" x-model="search" placeholder="Cari barang..." 
                       class="w-full pl-10 pr-4 py-3 rounded-xl border border-gray-200 bg-gray-50 focus:bg-white focus:ring-2 focus:ring-[#0ea5e9]/50 transition">
                <svg class="w-5 h-5 text-gray-400 absolute left-3 top-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto p-4 bg-gray-50">
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                @foreach($items as $item)
                <div @click="addToCart({{ $item }})" 
                     x-show="matchesSearch('{{ strtolower($item->name) }}')"
                     class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 cursor-pointer hover:border-[#0ea5e9] hover:shadow-md transition group relative overflow-hidden">
                    
                    <div class="h-20 w-full bg-blue-50 rounded-lg mb-3 flex items-center justify-center text-blue-400 group-hover:bg-[#0ea5e9] group-hover:text-white transition">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                    </div>
                    
                    <h4 class="font-bold text-gray-800 text-sm line-clamp-2 leading-tight h-10">{{ $item->name }}</h4>
                    <div class="flex justify-between items-center mt-2">
                        <span class="text-[#0ea5e9] font-bold text-sm">Rp {{ number_format($item->price, 0, ',', '.') }}</span>
                        <span class="text-[10px] bg-gray-100 text-gray-500 px-2 py-0.5 rounded">Stok: {{ $item->stock }}</span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="w-full lg:w-96 bg-white rounded-2xl shadow-xl border border-gray-100 flex flex-col h-full z-10">
        <div class="p-5 border-b border-gray-100 bg-gray-50 rounded-t-2xl">
            <h3 class="font-bold text-gray-800 flex items-center gap-2">
                <svg class="w-5 h-5 text-[#0ea5e9]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                Keranjang Belanja
            </h3>
        </div>

        <div class="flex-1 overflow-y-auto p-4 space-y-3">
            <template x-if="cart.length === 0">
                <div class="h-full flex flex-col items-center justify-center text-gray-400 text-center">
                    <svg class="w-12 h-12 mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                    <p class="text-sm">Keranjang masih kosong.<br>Pilih barang di sebelah kiri.</p>
                </div>
            </template>

            <template x-for="(item, index) in cart" :key="item.id">
                <div class="flex items-center justify-between p-3 bg-white border border-gray-100 rounded-xl shadow-sm">
                    <div class="flex-1">
                        <h4 class="font-bold text-gray-800 text-sm" x-text="item.name"></h4>
                        <p class="text-xs text-[#0ea5e9] font-medium">Rp <span x-text="formatRupiah(item.price)"></span></p>
                    </div>
                    <div class="flex items-center gap-3">
                        <button @click="updateQty(index, -1)" class="w-6 h-6 rounded bg-gray-100 hover:bg-red-100 hover:text-red-600 flex items-center justify-center transition">-</button>
                        <span class="font-bold text-sm w-4 text-center" x-text="item.qty"></span>
                        <button @click="updateQty(index, 1)" class="w-6 h-6 rounded bg-gray-100 hover:bg-green-100 hover:text-green-600 flex items-center justify-center transition">+</button>
                    </div>
                </div>
            </template>
        </div>

        <div class="p-5 border-t border-gray-100 bg-gray-50 rounded-b-2xl">
            <div class="flex justify-between items-center mb-4">
                <span class="text-gray-500 font-medium">Total Tagihan</span>
                <span class="text-2xl font-bold text-gray-800">Rp <span x-text="formatRupiah(totalAmount)"></span></span>
            </div>
            
            <button @click="processPayment()" 
                    :disabled="cart.length === 0"
                    class="w-full py-3 bg-[#0ea5e9] hover:bg-sky-600 disabled:bg-gray-300 disabled:cursor-not-allowed text-white font-bold rounded-xl shadow-lg shadow-sky-200 transition flex items-center justify-center gap-2">
                <span>Bayar Sekarang</span>
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
            </button>
        </div>
    </div>
</div>

<script>
    function posSystem() {
        return {
            search: '',
            cart: [],
            
            get totalAmount() {
                return this.cart.reduce((sum, item) => sum + (item.price * item.qty), 0);
            },

            matchesSearch(name) {
                return name.includes(this.search.toLowerCase());
            },

            addToCart(item) {
                // Cek stok dulu
                if(item.stock <= 0) {
                    alert('Stok habis!'); return;
                }

                let existingItem = this.cart.find(c => c.id === item.id);
                if (existingItem) {
                    if(existingItem.qty < item.stock) {
                        existingItem.qty++;
                    } else {
                        alert('Stok tidak cukup!');
                    }
                } else {
                    this.cart.push({
                        id: item.id,
                        name: item.name,
                        price: item.price,
                        stock: item.stock,
                        qty: 1
                    });
                }
            },

            updateQty(index, change) {
                let item = this.cart[index];
                let newQty = item.qty + change;

                if (newQty > item.stock) {
                    alert('Melebihi stok tersedia!');
                    return;
                }

                if (newQty > 0) {
                    item.qty = newQty;
                } else {
                    // Hapus item jika qty 0
                    this.cart.splice(index, 1);
                }
            },

            formatRupiah(number) {
                return new Intl.NumberFormat('id-ID').format(number);
            },

            async processPayment() {
                if (!confirm('Proses pembayaran senilai Rp ' + this.formatRupiah(this.totalAmount) + '?')) return;

                try {
                    // Kirim Data ke Laravel via Fetch API
                    let response = await fetch('{{ route("pos.transaction.store") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            cart: this.cart,
                            total_amount: this.totalAmount,
                            payment_method: 'CASH'
                        })
                    });

                    let result = await response.json();

                    if (response.ok) {
                        alert('Transaksi Berhasil!');
                        this.cart = []; // Kosongkan keranjang
                        window.location.reload(); // Reload untuk update stok di tampilan
                    } else {
                        alert('Gagal: ' + JSON.stringify(result));
                    }
                } catch (error) {
                    console.error(error);
                    alert('Terjadi kesalahan sistem.');
                }
            }
        };
    }
</script>
@endsection