<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Kasir Sekolah') }}
        </h2>
    </x-slot>

    <div x-data="posSystem()" x-init="init()" class="h-[calc(100vh-8rem)] p-4 md:p-6">
        
        <div class="flex flex-col lg:flex-row gap-6 h-full">
            
            <div class="flex-1 bg-white rounded-2xl shadow-sm border border-gray-100 flex flex-col overflow-hidden">
                
                <div class="p-5 border-b border-gray-100 flex gap-4 bg-white z-10">
                    <div class="relative flex-1">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </span>
                        <input type="text" x-model="search" placeholder="Cari barang / scan barcode..." 
                               class="w-full pl-10 pr-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-[#0ea5e9] focus:border-[#0ea5e9] transition shadow-sm bg-gray-50 focus:bg-white">
                    </div>
                </div>

                <div class="flex-1 overflow-y-auto p-5 bg-gray-50/50">
                    <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-4">
                        <template x-for="item in filteredItems" :key="item.id">
                            <div @click="addToCart(item)" 
                                 class="group bg-white p-4 rounded-xl border border-gray-100 shadow-sm hover:shadow-md hover:border-[#0ea5e9] cursor-pointer transition-all relative overflow-hidden active:scale-95">
                                
                                <span class="absolute top-2 right-2 text-[10px] font-bold px-2 py-0.5 bg-gray-100 text-gray-500 rounded-full group-hover:bg-[#0ea5e9] group-hover:text-white transition" x-text="item.category"></span>

                                <div class="w-10 h-10 bg-blue-50 text-[#0ea5e9] rounded-full flex items-center justify-center mb-3 group-hover:scale-110 transition">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                                </div>
                                
                                <h3 class="font-bold text-gray-800 text-sm mb-1 truncate" x-text="item.name"></h3>
                                <div class="flex justify-between items-end mt-2">
                                    <span class="text-[#0ea5e9] font-bold text-sm" x-text="formatRupiah(item.price)"></span>
                                    <span class="text-[10px] text-gray-400">Stok: <span x-text="item.stock"></span></span>
                                </div>
                            </div>
                        </template>
                        
                        <div x-show="filteredItems.length === 0" class="col-span-full flex flex-col items-center justify-center py-10 text-gray-400">
                            <svg class="w-12 h-12 mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            <p>Barang tidak ditemukan.</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="w-full lg:w-96 bg-white rounded-2xl shadow-xl border border-gray-100 flex flex-col overflow-hidden">
                
                <div class="p-5 border-b border-gray-100 bg-gray-50/50 flex justify-between items-center">
                    <h3 class="font-bold text-gray-800 flex items-center gap-2">
                        <svg class="w-5 h-5 text-[#0ea5e9]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        Keranjang
                    </h3>
                    <button @click="resetCart()" x-show="cart.length > 0" class="text-xs text-red-500 hover:text-red-700 font-bold bg-red-50 px-2 py-1 rounded-lg hover:bg-red-100 transition">
                        Reset
                    </button>
                </div>

                <div class="flex-1 overflow-y-auto p-4 space-y-3">
                    <template x-for="(item, index) in cart" :key="index">
                        <div class="flex flex-col p-3 bg-white border border-gray-100 rounded-xl shadow-sm hover:border-blue-200 transition relative group">
                            <div class="flex justify-between items-start mb-2">
                                <h4 class="text-sm font-bold text-gray-800 line-clamp-1 w-32" x-text="item.name"></h4>
                                <div class="text-sm text-[#0ea5e9] font-bold" x-text="formatRupiah(item.price * item.qty)"></div>
                            </div>
                            
                            <div class="flex justify-between items-center">
                                <div class="text-[10px] text-gray-400">@ <span x-text="formatRupiah(item.price)"></span></div>
                                
                                <div class="flex items-center gap-2 bg-gray-50 rounded-lg p-1 border border-gray-200">
                                    <button @click="updateQty(index, -1)" class="w-6 h-6 rounded bg-white shadow-sm flex items-center justify-center text-gray-600 hover:text-red-500 font-bold">-</button>
                                    <span class="font-bold text-sm w-4 text-center" x-text="item.qty"></span>
                                    <button @click="updateQty(index, 1)" class="w-6 h-6 rounded bg-[#0ea5e9] text-white shadow-sm flex items-center justify-center hover:bg-sky-600 font-bold">+</button>
                                </div>
                            </div>

                            <button @click="removeItem(index)" class="absolute -top-2 -right-2 bg-red-100 text-red-500 rounded-full p-1 opacity-0 group-hover:opacity-100 transition hover:bg-red-500 hover:text-white shadow-sm">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                        </div>
                    </template>

                    <div x-show="cart.length === 0" class="flex flex-col items-center justify-center h-48 text-gray-400">
                        <div class="bg-gray-50 p-4 rounded-full mb-3">
                            <svg class="w-8 h-8 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                        </div>
                        <p class="text-sm font-medium">Keranjang Kosong</p>
                        <p class="text-xs mt-1">Pilih barang di sebelah kiri</p>
                    </div>
                </div>

                <div class="p-5 bg-white border-t border-gray-200 shadow-[0_-10px_40px_rgba(0,0,0,0.05)] z-20">
                    <div class="flex justify-between items-center mb-4">
                        <span class="text-gray-500 font-medium text-sm">Total Belanja</span>
                        <span class="text-2xl font-bold text-gray-800" x-text="formatRupiah(totalAmount)"></span>
                    </div>

                    <div class="mb-4">
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400 font-bold text-xs uppercase">Bayar</span>
                            <input type="number" x-model="payAmount" placeholder="0" 
                                   class="w-full pl-14 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-[#0ea5e9] focus:border-[#0ea5e9] font-bold text-lg text-right text-gray-800 transition">
                        </div>
                    </div>

                    <button @click="submitTransaction()" 
                            :disabled="cart.length === 0 || payAmount < totalAmount"
                            :class="{'opacity-50 cursor-not-allowed bg-gray-300': cart.length === 0 || payAmount < totalAmount, 'bg-[#0ea5e9] hover:bg-sky-600 shadow-lg shadow-sky-200': !(cart.length === 0 || payAmount < totalAmount)}"
                            class="w-full text-white font-bold py-3.5 rounded-xl transition flex justify-center items-center gap-2 active:scale-95">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        Proses Pembayaran
                    </button>
                </div>
            </div>

        </div>
    </div>

    <script>
        function posSystem() {
            return {
                allItems: @json($items),
                search: '',
                cart: [],
                payAmount: '',

                init() { console.log('POS Ready'); },

                get filteredItems() {
                    if (this.search === '') return this.allItems;
                    return this.allItems.filter(item => item.name.toLowerCase().includes(this.search.toLowerCase()));
                },

                get totalAmount() {
                    return this.cart.reduce((total, item) => total + (item.price * item.qty), 0);
                },

                addToCart(item) {
                    if (item.stock <= 0) return Swal.fire({ icon: 'error', title: 'Habis', text: 'Stok barang kosong!', timer: 1000, showConfirmButton: false });

                    let exist = this.cart.find(c => c.id === item.id);
                    if (exist) {
                        if (exist.qty >= item.stock) return Swal.fire({ icon: 'warning', title: 'Maksimal', text: 'Stok tidak cukup', timer: 1000, showConfirmButton: false });
                        exist.qty++;
                    } else {
                        this.cart.push({ id: item.id, name: item.name, price: item.price, stock: item.stock, qty: 1 });
                    }
                },

                updateQty(index, amount) {
                    let item = this.cart[index];
                    let newQty = item.qty + amount;
                    if (newQty > item.stock) return Swal.fire({ icon: 'warning', text: 'Stok mentok!', timer: 800, showConfirmButton: false });
                    if (newQty > 0) item.qty = newQty;
                    else this.removeItem(index);
                },

                removeItem(index) { this.cart.splice(index, 1); },
                resetCart() { this.cart = []; this.payAmount = ''; },
                formatRupiah(n) { return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(n); },

                async submitTransaction() {
                    Swal.fire({ title: 'Memproses...', didOpen: () => Swal.showLoading(), allowOutsideClick: false, showConfirmButton: false });

                    try {
                        let res = await fetch('{{ route('pos.transaction.store') }}', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                            body: JSON.stringify({ cart: this.cart, total_amount: this.totalAmount, payment_amount: this.payAmount })
                        });
                        let data = await res.json();

                        if (data.success) {
                            Swal.fire({ 
                                icon: 'success', 
                                title: 'Kembalian: Rp ' + data.change, 
                                text: 'Transaksi Berhasil!',
                                confirmButtonColor: '#0ea5e9'
                            }).then(() => window.location.reload());
                        } else {
                            throw new Error(data.message);
                        }
                    } catch (e) {
                        Swal.fire('Gagal', e.message, 'error');
                    }
                }
            }
        }
    </script>
</x-app-layout>