<x-app-layout>
    <div x-data="posSystem()" x-init="init()" class="min-h-[calc(100vh-4rem)] flex flex-col">
        
        <div class="w-full bg-white border-b border-gray-100 px-6 py-4">
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800">Kasir Sekolah</h2>
                    <p class="text-sm text-gray-500">Transaksi penjualan & kantin.</p>
                </div>
                <div class="bg-gray-50 px-4 py-2 rounded-lg border border-gray-200 text-sm font-bold text-gray-700">
                    {{ date('d M Y, H:i') }}
                </div>
            </div>
        </div>

        <div class="flex-1 w-full p-4 md:p-6">
            <div class="flex flex-col lg:flex-row gap-6 h-full max-w-[1920px] mx-auto">
                
                <div class="flex-1 bg-white rounded-xl shadow-sm border border-gray-100 flex flex-col overflow-hidden">
                    
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
                            
                            <div x-show="filteredItems.length === 0" class="col-span-full flex flex-col items-center justify-center py-12 md:py-16 text-gray-400">
                                <div class="bg-gray-100 p-4 rounded-full mb-3">
                                    <svg class="w-8 h-8 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                                <p class="font-medium text-gray-500">Barang tidak ditemukan</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="w-full lg:w-96 xl:w-96 bg-white rounded-xl shadow-lg border border-gray-100 flex flex-col overflow-hidden h-fit lg:h-auto">
                    
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

                    <div class="flex-1 overflow-y-auto p-4 space-y-3 min-h-[300px] lg:max-h-[calc(100vh-350px)]">
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

                                <button @click="removeItem(index)" 
                                        class="absolute -top-2 -right-2 bg-white border border-gray-200 text-gray-500 hover:text-red-500 rounded-full p-1 opacity-0 group-hover:opacity-100 transition shadow-sm hover:bg-red-50 hover:border-red-200">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                        </template>

                        <div x-show="cart.length === 0" class="flex flex-col items-center justify-center h-full text-gray-400 min-h-[200px]">
                            <div class="bg-gray-100 p-4 rounded-full mb-3">
                                <svg class="w-8 h-8 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                                </svg>
                            </div>
                            <p class="text-sm font-medium text-gray-500">Keranjang Kosong</p>
                        </div>
                    </div>

                    <div class="p-4 md:p-5 bg-white border-t border-gray-200 shadow-[0_-4px_12px_rgba(0,0,0,0.05)]">
                        
                        <div class="flex justify-between items-center mb-4 pb-3 border-b border-gray-100">
                            <span class="text-gray-600 font-medium">Total Tagihan</span>
                            <span class="text-xl md:text-2xl font-bold text-gray-800" x-text="formatRupiah(totalAmount)"></span>
                        </div>

                        <div class="flex bg-gray-100 p-1 rounded-lg mb-4">
                            <button @click="paymentMode = 'cash'; payAmount = ''" 
                                    :class="paymentMode === 'cash' ? 'bg-white text-[#0ea5e9] shadow-sm' : 'text-gray-500 hover:text-gray-700'"
                                    class="flex-1 py-2 text-sm font-bold rounded-md transition duration-200 flex items-center justify-center gap-2">
                                💵 Tunai
                            </button>
                            <button @click="paymentMode = 'online'; payAmount = 0" 
                                    :class="paymentMode === 'online' ? 'bg-white text-purple-600 shadow-sm' : 'text-gray-500 hover:text-gray-700'"
                                    class="flex-1 py-2 text-sm font-bold rounded-md transition duration-200 flex items-center justify-center gap-2">
                                📱 QRIS / Hutang
                            </button>
                        </div>

                        <div x-show="paymentMode === 'cash'" x-transition class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Uang Diterima (Rp)</label>
                            <div class="relative">
                                <input type="number" x-model="payAmount" placeholder="0" 
                                       class="w-full pl-3 pr-4 py-3 bg-gray-50 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#0ea5e9] focus:border-[#0ea5e9] font-semibold text-right text-gray-800 transition text-lg">
                            </div>
                            
                            <div x-show="payAmount >= totalAmount && payAmount > 0" class="mt-3 p-3 bg-green-50 border border-green-100 rounded-lg animate-fade-in-up">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm font-medium text-green-700">Kembalian</span>
                                    <span class="text-lg font-bold text-green-700">
                                        Rp <span x-text="(payAmount - totalAmount).toLocaleString('id-ID')"></span>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div x-show="paymentMode === 'online'" x-transition class="mb-4 p-3 bg-purple-50 border border-purple-100 rounded-lg text-sm text-purple-700">
                            <p class="font-bold flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                Mode Simpan Tagihan
                            </p>
                            <p class="mt-1 text-xs opacity-80 leading-relaxed">Transaksi akan disimpan sebagai "PENDING". Siswa dapat melakukan pembayaran nanti via Aplikasi Sekolah.</p>
                        </div>

                        <button @click="submitTransaction()" 
                                :disabled="cart.length === 0 || (paymentMode === 'cash' && payAmount < totalAmount)"
                                :class="{
                                    'opacity-50 cursor-not-allowed bg-gray-300 text-gray-500': cart.length === 0 || (paymentMode === 'cash' && payAmount < totalAmount),
                                    'bg-gradient-to-r from-[#0ea5e9] to-blue-600 shadow-blue-200': paymentMode === 'cash',
                                    'bg-gradient-to-r from-purple-500 to-indigo-600 shadow-purple-200': paymentMode === 'online'
                                }"
                                class="w-full text-white font-bold py-3.5 rounded-lg transition-all flex justify-center items-center gap-2 shadow-lg active:scale-[0.98]">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                            <span x-text="paymentMode === 'cash' ? 'BAYAR & SELESAI' : 'SIMPAN TRANSAKSI'"></span>
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
                paymentMode: 'cash', // Default: Tunai

                init() { 
                    console.log('POS System Ready - Hybrid Mode'); 
                    // Auto focus ke search bar
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
                    // Validasi Stok Habis
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
                        // Cek Stok sebelum nambah
                        if (exist.qty >= item.stock) {
                            Swal.fire({ 
                                icon: 'warning', 
                                title: 'Stok Terbatas', 
                                text: `Hanya tersisa ${item.stock} item`, 
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
                        title: 'Kosongkan Keranjang?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        confirmButtonText: 'Ya, Hapus',
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
                    // 1. Validasi Cart
                    if (this.cart.length === 0) {
                        Swal.fire('Keranjang Kosong', 'Tambahkan barang dulu.', 'warning');
                        return;
                    }

                    // 2. Validasi Pembayaran (Khusus Mode Cash)
                    if (this.paymentMode === 'cash' && this.payAmount < this.totalAmount) {
                        Swal.fire('Uang Kurang', `Kurang Rp ${(this.totalAmount - this.payAmount).toLocaleString('id-ID')}`, 'error');
                        return;
                    }

                    // 3. Tentukan Nilai Bayar (Kalau online dianggap 0 dulu)
                    let finalPayAmount = this.paymentMode === 'cash' ? this.payAmount : 0;

                    Swal.fire({ 
                        title: 'Memproses...', 
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
                                payment_amount: finalPayAmount 
                            })
                        });
                        
                        let data = await res.json();

                        if (data.success) {
                            let titleText = this.paymentMode === 'cash' ? 'Lunas!' : 'Tagihan Disimpan!';
                            let iconType = this.paymentMode === 'cash' ? 'success' : 'info';
                            
                            Swal.fire({ 
                                icon: iconType, 
                                title: titleText,
                                html: `
                                    <div class="text-left text-sm mt-2">
                                        <p>Total: <b>${this.formatRupiah(this.totalAmount)}</b></p>
                                        <p>Kode TRX: <span class="font-mono bg-gray-100 px-1 rounded">${data.trx_id}</span></p>
                                        ${this.paymentMode === 'cash' ? `<p class="mt-2 text-green-600 font-bold">Kembali: Rp ${data.change}</p>` : ''}
                                    </div>
                                `,
                                confirmButtonColor: '#0ea5e9',
                                confirmButtonText: 'OK / Cetak',
                                showCancelButton: true,
                                cancelButtonText: 'Tutup'
                            }).then((result) => {
                                // TODO: Nanti arahkan ke Route Print Struk disini
                                // if (result.isConfirmed) window.open(...)
                                window.location.reload();
                            });
                        } else {
                            throw new Error(data.message || 'Error server');
                        }
                    } catch (error) {
                        console.error(error);
                        Swal.fire('Gagal', error.message, 'error');
                    }
                }
            }
        }
    </script>
</x-app-layout>