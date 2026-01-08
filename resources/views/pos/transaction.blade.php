<x-app-layout>
    <div x-data="posSystem()" x-init="init()" class="min-h-[calc(100vh-4rem)] flex flex-col">
        
        <!-- Header -->
        <div class="w-full bg-white border-b border-gray-100 px-6 py-4">
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800">Kasir Sekolah</h2>
                    <p class="text-sm text-gray-500">Transaksi penjualan tunai.</p>
                </div>
                <div class="bg-gray-50 px-4 py-2 rounded-lg border border-gray-200 text-sm font-bold text-gray-700">
                    {{ date('d M Y, H:i') }}
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 w-full p-4 md:p-6">
            <div class="flex flex-col lg:flex-row gap-6 h-full max-w-[1920px] mx-auto">
                
                <!-- Product List -->
                <div class="flex-1 bg-white rounded-xl shadow-sm border border-gray-100 flex flex-col overflow-hidden">
                    
                    <!-- Search Bar -->
                    <div class="p-5 border-b border-gray-100 bg-white z-10">
                        <div class="relative w-full">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                            </span>
                            <input type="text" x-model="search" placeholder="Cari nama barang..." 
                                   class="w-full pl-10 pr-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#0ea5e9] focus:border-[#0ea5e9] transition bg-gray-50 focus:bg-white text-sm">
                        </div>
                    </div>

                    <!-- Product Grid -->
                    <div class="flex-1 overflow-y-auto p-4 md:p-5 bg-gray-50/30">
                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-3 md:gap-4">
                            <template x-for="item in filteredItems" :key="item.id">
                                <div @click="addToCart(item)" 
                                     class="group bg-white p-3 rounded-lg border border-gray-200 shadow-sm hover:shadow-md hover:border-[#0ea5e9] cursor-pointer transition-all relative overflow-hidden active:scale-[0.98] flex flex-col h-full">
                                    
                                    <span class="absolute top-2 right-2 text-[10px] font-bold px-2 py-1 bg-gray-100 text-gray-600 rounded-md group-hover:bg-[#0ea5e9] group-hover:text-white transition" 
                                          x-text="item.category"></span>

                                    <div class="w-full aspect-square bg-blue-50 text-[#0ea5e9] rounded-lg flex items-center justify-center mb-2 group-hover:scale-105 transition overflow-hidden">
                                        <template x-if="item.image">
                                            <img :src="'/storage/' + item.image" class="w-full h-full object-cover rounded-lg">
                                        </template>
                                        <template x-if="!item.image">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                            </svg>
                                        </template>
                                    </div>
                                    
                                    <h3 class="font-semibold text-gray-800 text-xs md:text-sm mb-1 line-clamp-2" x-text="item.name"></h3>
                                    
                                    <div class="mt-auto pt-2 border-t border-gray-100">
                                        <div class="flex justify-between items-center">
                                            <span class="text-[#0ea5e9] font-bold text-xs md:text-sm" x-text="formatRupiah(item.price)"></span>
                                            <span class="text-[10px] text-gray-500">Stok: <span class="font-medium" x-text="item.stock"></span></span>
                                        </div>
                                    </div>
                                </div>
                            </template>
                            
                            <!-- Empty State -->
                            <div x-show="filteredItems.length === 0" class="col-span-full flex flex-col items-center justify-center py-12 md:py-16 text-gray-400">
                                <div class="bg-gray-100 p-4 rounded-full mb-3">
                                    <svg class="w-8 h-8 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <p class="font-medium text-gray-500">Barang tidak ditemukan</p>
                                <p class="text-sm text-gray-400 mt-1">Coba kata kunci lain</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Cart Section -->
                <div class="w-full lg:w-96 xl:w-96 bg-white rounded-xl shadow-lg border border-gray-100 flex flex-col overflow-hidden">
                    
                    <!-- Cart Header -->
                    <div class="p-4 md:p-5 border-b border-gray-100 bg-white flex justify-between items-center">
                        <h3 class="font-bold text-gray-800 text-lg flex items-center gap-2">
                            <svg class="w-5 h-5 text-[#0ea5e9]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                            Keranjang
                        </h3>
                        <button @click="resetCart()" x-show="cart.length > 0" 
                                class="text-xs font-bold text-red-600 bg-red-50 hover:bg-red-100 px-3 py-1.5 rounded-lg transition border border-red-100">
                            HAPUS SEMUA
                        </button>
                    </div>

                    <!-- Cart Items -->
                    <div class="flex-1 overflow-y-auto p-4 space-y-3 min-h-[300px]">
                        <template x-for="(item, index) in cart" :key="index">
                            <div class="flex flex-col p-3 bg-white border border-gray-200 rounded-lg hover:border-[#0ea5e9] transition relative group">
                                <div class="flex justify-between items-start mb-2 gap-2">
                                    <h4 class="text-sm font-semibold text-gray-800 line-clamp-2 flex-1" x-text="item.name"></h4>
                                    <div class="text-sm text-[#0ea5e9] font-bold whitespace-nowrap" x-text="formatRupiah(item.price * item.qty)"></div>
                                </div>
                                
                                <div class="flex justify-between items-center">
                                    <div class="text-xs text-gray-500">@ <span x-text="formatRupiah(item.price)"></span></div>
                                    
                                    <div class="flex items-center gap-2 bg-gray-50 rounded-lg p-1 border border-gray-300">
                                        <button @click="updateQty(index, -1)" 
                                                class="w-6 h-6 rounded bg-white shadow-sm flex items-center justify-center text-gray-600 hover:text-red-500 font-bold transition hover:bg-red-50">
                                            -
                                        </button>
                                        <span class="font-bold text-sm w-5 text-center" x-text="item.qty"></span>
                                        <button @click="updateQty(index, 1)" 
                                                class="w-6 h-6 rounded bg-[#0ea5e9] text-white shadow-sm flex items-center justify-center hover:bg-sky-600 font-bold transition">
                                            +
                                        </button>
                                    </div>
                                </div>

                                <!-- Remove Button -->
                                <button @click="removeItem(index)" 
                                        class="absolute -top-2 -right-2 bg-white border border-gray-200 text-gray-500 hover:text-red-500 rounded-full p-1 opacity-0 group-hover:opacity-100 transition shadow-sm hover:bg-red-50 hover:border-red-200">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                        </template>

                        <!-- Empty Cart State -->
                        <div x-show="cart.length === 0" class="flex flex-col items-center justify-center h-full text-gray-400 min-h-[300px]">
                            <div class="bg-gray-100 p-4 rounded-full mb-3">
                                <svg class="w-8 h-8 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                                </svg>
                            </div>
                            <p class="text-sm font-medium text-gray-500">Keranjang Kosong</p>
                            <p class="text-xs mt-1 text-gray-400">Pilih barang dari daftar produk</p>
                        </div>
                    </div>

                    <!-- Payment Section -->
                    <div class="p-4 md:p-5 bg-white border-t border-gray-200 shadow-[0_-4px_12px_rgba(0,0,0,0.05)]">
                        <!-- Total -->
                        <div class="flex justify-between items-center mb-4 pb-3 border-b border-gray-100">
                            <span class="text-gray-600 font-medium">Total Tagihan</span>
                            <span class="text-xl md:text-2xl font-bold text-gray-800" x-text="formatRupiah(totalAmount)"></span>
                        </div>

                        <!-- Payment Input -->
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Bayar (Rp)</label>
                            <div class="relative">
                                <input type="number" x-model="payAmount" placeholder="Masukkan jumlah pembayaran" 
                                       class="w-full pl-3 pr-4 py-3 bg-gray-50 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#0ea5e9] focus:border-[#0ea5e9] font-semibold text-right text-gray-800 transition">
                            </div>
                        </div>

                        <!-- Change Display -->
                        <div x-show="payAmount >= totalAmount && payAmount > 0" class="mb-4 p-3 bg-green-50 border border-green-100 rounded-lg">
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium text-green-700">Kembalian</span>
                                <span class="text-lg font-bold text-green-700">
                                    Rp <span x-text="(payAmount - totalAmount).toLocaleString('id-ID')"></span>
                                </span>
                            </div>
                        </div>

                        <!-- Process Button -->
                        <button @click="submitTransaction()" 
                                :disabled="cart.length === 0 || payAmount < totalAmount"
                                :class="{
                                    'opacity-50 cursor-not-allowed bg-gray-300 text-gray-500': cart.length === 0 || payAmount < totalAmount,
                                    'bg-gradient-to-r from-[#0ea5e9] to-blue-500 hover:from-blue-500 hover:to-[#0ea5e9] shadow-lg shadow-blue-100': !(cart.length === 0 || payAmount < totalAmount)
                                }"
                                class="w-full text-white font-bold py-3.5 rounded-lg transition-all flex justify-center items-center gap-2 active:scale-[0.98]">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                            PROSES PEMBAYARAN
                        </button>
                    </div>
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

                init() { 
                    console.log('POS System Ready'); 
                    // Focus search input on load
                    setTimeout(() => {
                        const searchInput = document.querySelector('input[x-model="search"]');
                        if (searchInput) searchInput.focus();
                    }, 100);
                },

                get filteredItems() {
                    if (this.search === '') return this.allItems;
                    const searchTerm = this.search.toLowerCase();
                    return this.allItems.filter(item => 
                        item.name.toLowerCase().includes(searchTerm) || 
                        (item.category && item.category.toLowerCase().includes(searchTerm))
                    );
                },

                get totalAmount() {
                    return this.cart.reduce((total, item) => total + (item.price * item.qty), 0);
                },

                addToCart(item) {
                    if (item.stock <= 0) {
                        Swal.fire({ 
                            icon: 'error', 
                            title: 'Stok Habis', 
                            text: `${item.name} sudah habis!`, 
                            timer: 1500, 
                            showConfirmButton: false 
                        });
                        return;
                    }

                    let exist = this.cart.find(c => c.id === item.id);
                    if (exist) {
                        if (exist.qty >= item.stock) {
                            Swal.fire({ 
                                icon: 'warning', 
                                title: 'Stok Tidak Cukup', 
                                text: `Hanya tersedia ${item.stock} ${item.name}`, 
                                timer: 1500, 
                                showConfirmButton: false 
                            });
                            return;
                        }
                        exist.qty++;
                    } else {
                        this.cart.push({ 
                            id: item.id, 
                            name: item.name, 
                            price: item.price, 
                            stock: item.stock, 
                            qty: 1, 
                            image: item.image 
                        });
                    }
                    
                    // Scroll to cart bottom
                    setTimeout(() => {
                        const cartContainer = document.querySelector('[x-data] div.flex-1.overflow-y-auto');
                        if (cartContainer) {
                            cartContainer.scrollTop = cartContainer.scrollHeight;
                        }
                    }, 100);
                },

                updateQty(index, amount) {
                    let item = this.cart[index];
                    let newQty = item.qty + amount;
                    
                    if (newQty > item.stock) {
                        Swal.fire({ 
                            icon: 'warning', 
                            text: `Maksimal stok ${item.stock}`, 
                            timer: 1000, 
                            showConfirmButton: false 
                        });
                        return;
                    }
                    
                    if (newQty > 0) {
                        item.qty = newQty;
                    } else {
                        this.removeItem(index);
                    }
                },

                removeItem(index) { 
                    this.cart.splice(index, 1); 
                },
                
                resetCart() { 
                    if (this.cart.length === 0) return;
                    
                    Swal.fire({
                        title: 'Hapus semua?',
                        text: "Semua item di keranjang akan dihapus",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#6b7280',
                        confirmButtonText: 'Ya, Hapus!',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            this.cart = []; 
                            this.payAmount = '';
                        }
                    });
                },
                
                formatRupiah(n) { 
                    return new Intl.NumberFormat('id-ID', { 
                        style: 'currency', 
                        currency: 'IDR', 
                        minimumFractionDigits: 0 
                    }).format(n); 
                },

                async submitTransaction() {
                    if (this.cart.length === 0) {
                        Swal.fire('Keranjang Kosong', 'Tambahkan barang terlebih dahulu', 'warning');
                        return;
                    }

                    if (this.payAmount < this.totalAmount) {
                        Swal.fire('Pembayaran Kurang', `Kurang Rp ${(this.totalAmount - this.payAmount).toLocaleString('id-ID')}`, 'error');
                        return;
                    }

                    Swal.fire({ 
                        title: 'Memproses Transaksi...', 
                        didOpen: () => Swal.showLoading(), 
                        allowOutsideClick: false, 
                        showConfirmButton: false 
                    });

                    try {
                        let res = await fetch('{{ route('pos.transaction.store') }}', {
                            method: 'POST',
                            headers: { 
                                'Content-Type': 'application/json', 
                                'X-CSRF-TOKEN': '{{ csrf_token() }}' 
                            },
                            body: JSON.stringify({ 
                                cart: this.cart, 
                                total_amount: this.totalAmount, 
                                payment_amount: this.payAmount 
                            })
                        });
                        
                        let data = await res.json();

                        if (data.success) {
                            Swal.fire({ 
                                icon: 'success', 
                                title: 'Transaksi Berhasil!',
                                html: `
                                    <div class="text-left">
                                        <p class="mb-2">Total: <span class="font-bold">${this.formatRupiah(this.totalAmount)}</span></p>
                                        <p class="mb-2">Bayar: <span class="font-bold">Rp ${Number(this.payAmount).toLocaleString('id-ID')}</span></p>
                                        <p class="mb-4">Kembali: <span class="font-bold text-green-600">Rp ${data.change.toLocaleString('id-ID')}</span></p>
                                        <p class="text-sm text-gray-500">Nomor Transaksi: ${data.transaction_code}</p>
                                    </div>
                                `,
                                confirmButtonColor: '#0ea5e9',
                                confirmButtonText: 'Cetak Struk',
                                showCancelButton: true,
                                cancelButtonText: 'Transaksi Baru'
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    // Open print window
                                    window.open(`/transactions/${data.transaction_id}/print`, '_blank');
                                }
                                window.location.reload();
                            });
                        } else {
                            throw new Error(data.message || 'Terjadi kesalahan');
                        }
                    } catch (error) {
                        console.error('Transaction error:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: error.message || 'Gagal memproses transaksi. Coba lagi.',
                            confirmButtonColor: '#0ea5e9'
                        });
                    }
                }
            }
        }
    </script>

    <style>
        /* Custom Scrollbar */
        .overflow-y-auto::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        
        .overflow-y-auto::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }
        
        .overflow-y-auto::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 4px;
        }
        
        .overflow-y-auto::-webkit-scrollbar-thumb:hover {
            background: #a1a1a1;
        }
        
        /* Remove number input arrows */
        input[type="number"]::-webkit-inner-spin-button,
        input[type="number"]::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
        
        input[type="number"] {
            -moz-appearance: textfield;
        }
        
        /* Line clamp for product names */
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
    </style>
</x-app-layout>