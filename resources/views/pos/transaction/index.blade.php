<x-app-layout>
    <div x-data="posSystem()" x-init="init()" class="min-h-[calc(100vh-4rem)] flex flex-col">
        
        <div class="w-full bg-white border-b border-gray-100 px-6 py-4">
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800">Kasir Sekolah</h2>
                    <p class="text-sm text-gray-500">Transaksi Penjualan & Kantin</p>
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
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            </span>
                            <input type="text" x-model="search" placeholder="Cari nama barang..." 
                                   class="w-full pl-10 pr-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#0ea5e9] transition bg-gray-50 focus:bg-white text-sm">
                        </div>
                    </div>

                    <div class="flex-1 overflow-y-auto p-4 md:p-5 bg-gray-50/30">
                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-3 md:gap-4">
                            <template x-for="item in filteredItems" :key="item.id">
                                <div @click="addToCart(item)" 
                                     class="group bg-white p-3 rounded-lg border border-gray-200 shadow-sm hover:shadow-md hover:border-[#0ea5e9] cursor-pointer transition-all relative overflow-hidden active:scale-[0.98] flex flex-col h-full">
                                    <span class="absolute top-2 right-2 text-[10px] font-bold px-2 py-1 bg-gray-100 text-gray-600 rounded-md group-hover:bg-[#0ea5e9] group-hover:text-white transition" x-text="item.category"></span>
                                    <div class="w-full aspect-square bg-blue-50 text-[#0ea5e9] rounded-lg flex items-center justify-center mb-2 group-hover:scale-105 transition overflow-hidden">
                                        <template x-if="item.image">
                                            <img :src="'/storage/' + item.image" class="w-full h-full object-cover rounded-lg">
                                        </template>
                                        <template x-if="!item.image">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
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
                                    <svg class="w-8 h-8 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                </div>
                                <p class="font-medium text-gray-500">Barang tidak ditemukan</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="w-full lg:w-96 xl:w-96 bg-white rounded-xl shadow-lg border border-gray-100 flex flex-col overflow-hidden h-fit lg:h-auto">
                    
                    <div class="p-4 md:p-5 border-b border-gray-100 bg-white flex justify-between items-center">
                        <h3 class="font-bold text-gray-800 text-lg flex items-center gap-2">
                            <svg class="w-5 h-5 text-[#0ea5e9]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                            Keranjang
                        </h3>
                        <button @click="resetCart()" x-show="cart.length > 0" class="text-xs font-bold text-red-600 bg-red-50 hover:bg-red-100 px-3 py-1.5 rounded-lg transition border border-red-100">HAPUS SEMUA</button>
                    </div>

                    <div class="flex-1 overflow-y-auto p-4 space-y-3 min-h-[250px] lg:max-h-[calc(100vh-450px)]">
                        <template x-for="(item, index) in cart" :key="index">
                            <div class="flex flex-col p-3 bg-white border border-gray-200 rounded-lg hover:border-[#0ea5e9] transition relative group">
                                <div class="flex justify-between items-start mb-2 gap-2">
                                    <h4 class="text-sm font-semibold text-gray-800 line-clamp-2 flex-1" x-text="item.name"></h4>
                                    <div class="text-sm text-[#0ea5e9] font-bold whitespace-nowrap" x-text="formatRupiah(item.price * item.qty)"></div>
                                </div>
                                <div class="flex justify-between items-center">
                                    <div class="text-xs text-gray-500">@ <span x-text="formatRupiah(item.price)"></span></div>
                                    <div class="flex items-center gap-2 bg-gray-50 rounded-lg p-1 border border-gray-300">
                                        <button @click="updateQty(index, -1)" class="w-6 h-6 rounded bg-white shadow-sm flex items-center justify-center text-gray-600 hover:text-red-500 font-bold transition hover:bg-red-50">-</button>
                                        <span class="font-bold text-sm w-5 text-center" x-text="item.qty"></span>
                                        <button @click="updateQty(index, 1)" class="w-6 h-6 rounded bg-[#0ea5e9] text-white shadow-sm flex items-center justify-center hover:bg-sky-600 font-bold transition">+</button>
                                    </div>
                                </div>
                                <button @click="removeItem(index)" class="absolute -top-2 -right-2 bg-white border border-gray-200 text-gray-500 hover:text-red-500 rounded-full p-1 opacity-0 group-hover:opacity-100 transition shadow-sm hover:bg-red-50 hover:border-red-200">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                </button>
                            </div>
                        </template>
                        <div x-show="cart.length === 0" class="flex flex-col items-center justify-center h-full text-gray-400 min-h-[200px]">
                            <div class="bg-gray-100 p-4 rounded-full mb-3">
                                <svg class="w-8 h-8 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                            </div>
                            <p class="text-sm font-medium text-gray-500">Keranjang Kosong</p>
                            <p class="text-xs mt-1 text-gray-400">Pilih barang dari daftar produk</p>
                        </div>
                    </div>

                    <div class="p-4 md:p-5 bg-white border-t border-gray-200 shadow-[0_-4px_12px_rgba(0,0,0,0.05)]">
                        <div class="flex justify-between items-center mb-4 pb-3 border-b border-gray-100">
                            <span class="text-gray-600 font-medium">Total Tagihan</span>
                            <span class="text-xl md:text-2xl font-bold text-gray-800" x-text="formatRupiah(totalAmount)"></span>
                        </div>

                        <div class="flex bg-gray-100 p-1 rounded-lg mb-4">
                            <button @click="setPaymentMode('cash')" 
                                    :class="paymentMode === 'cash' ? 'bg-white text-[#0ea5e9] shadow-sm' : 'text-gray-500 hover:text-gray-700'"
                                    class="flex-1 py-2 text-sm font-bold rounded-md transition duration-200 flex items-center justify-center gap-2">
                                💵 Tunai
                            </button>
                            <button @click="setPaymentMode('online')" 
                                    :class="paymentMode === 'online' ? 'bg-white text-purple-600 shadow-sm' : 'text-gray-500 hover:text-gray-700'"
                                    class="flex-1 py-2 text-sm font-bold rounded-md transition duration-200 flex items-center justify-center gap-2">
                                📱 QRIS / Hutang
                            </button>
                        </div>

                        <div x-show="paymentMode === 'online'" x-transition class="mb-4 bg-purple-50 p-3 rounded-lg border border-purple-100">
                            <label class="block text-xs font-bold text-purple-700 mb-2 uppercase">Pilih Siswa (Wajib)</label>
                            
                            <div class="relative" @click.away="showStudentDropdown = false">
                                <input type="text" x-model="studentSearch" @focus="showStudentDropdown = true" placeholder="Ketik Nama / NIS..."
                                       class="w-full pl-9 pr-4 py-2 bg-white border border-purple-200 rounded-lg focus:ring-2 focus:ring-purple-500 text-sm">
                                <span class="absolute left-3 top-2.5 text-purple-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                </span>

                                <div x-show="showStudentDropdown && filteredStudents.length > 0" 
                                     class="absolute z-50 left-0 right-0 mt-1 bg-white border border-gray-200 rounded-lg shadow-xl max-h-48 overflow-y-auto">
                                    <template x-for="student in filteredStudents" :key="student.id">
                                        <div @click="selectStudent(student)" class="px-4 py-2 hover:bg-purple-50 cursor-pointer border-b border-gray-50 last:border-none">
                                            <p class="font-bold text-gray-800 text-sm" x-text="student.name"></p>
                                            <p class="text-xs text-gray-500"><span x-text="student.nis"></span> - <span x-text="student.class_name"></span></p>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <template x-if="selectedStudent">
                                <div class="mt-2 flex items-center justify-between bg-purple-200 text-purple-800 px-3 py-2 rounded-lg text-sm animate-fade-in-up">
                                    <div class="flex items-center gap-2">
                                        <div class="bg-purple-500 text-white rounded-full w-5 h-5 flex items-center justify-center text-xs font-bold">✓</div>
                                        <span class="font-bold" x-text="selectedStudent.name"></span>
                                    </div>
                                    <button @click="selectedStudent = null; studentSearch = ''" class="text-purple-600 hover:text-purple-900 font-bold px-2">✕</button>
                                </div>
                            </template>
                        </div>

                        <div x-show="paymentMode === 'cash'" x-transition class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Uang Diterima (Rp)</label>
                            <input type="number" x-model="payAmount" placeholder="0" 
                                   class="w-full pl-3 pr-4 py-3 bg-gray-50 border border-gray-300 rounded-lg focus:ring-2 focus:ring-[#0ea5e9] font-semibold text-right text-gray-800 text-lg">
                            
                            <div x-show="payAmount >= totalAmount && payAmount > 0" class="mt-3 p-3 bg-green-50 border border-green-100 rounded-lg animate-fade-in-up">
                                <div class="flex justify-between items-center">
                                    <span class="text-sm font-medium text-green-700">Kembalian</span>
                                    <span class="text-lg font-bold text-green-700">Rp <span x-text="(payAmount - totalAmount).toLocaleString('id-ID')"></span></span>
                                </div>
                            </div>
                        </div>

                        <button @click="submitTransaction()" 
                                :disabled="cart.length === 0"
                                :class="{
                                    'opacity-50 cursor-not-allowed bg-gray-300': cart.length === 0,
                                    'bg-gradient-to-r from-[#0ea5e9] to-blue-600 shadow-blue-200': paymentMode === 'cash',
                                    'bg-gradient-to-r from-purple-500 to-indigo-600 shadow-purple-200': paymentMode === 'online'
                                }"
                                class="w-full text-white font-bold py-3.5 rounded-lg transition-all flex justify-center items-center gap-2 shadow-lg active:scale-[0.98]">
                            <svg x-show="paymentMode === 'cash'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                            <svg x-show="paymentMode === 'online'" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
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
                allStudents: @json($students), // Data Siswa dari Controller
                search: '',
                studentSearch: '',
                cart: [],
                payAmount: '',
                paymentMode: 'cash',
                
                // Student Logic
                showStudentDropdown: false,
                selectedStudent: null,

                init() { 
                    console.log('POS System Ready - Student Integration'); 
                    setTimeout(() => { document.querySelector('input[x-model="search"]')?.focus(); }, 100);
                },

                get filteredItems() {
                    if (this.search === '') return this.allItems;
                    const term = this.search.toLowerCase();
                    return this.allItems.filter(i => i.name.toLowerCase().includes(term) || (i.category && i.category.toLowerCase().includes(term)));
                },

                // Filter Siswa untuk Dropdown
                get filteredStudents() {
                    if (this.studentSearch === '') return [];
                    const term = this.studentSearch.toLowerCase();
                    return this.allStudents.filter(s => 
                        s.name.toLowerCase().includes(term) || 
                        s.nis.toLowerCase().includes(term)
                    ).slice(0, 5); // Limit 5 hasil biar gak panjang
                },

                get totalAmount() {
                    return this.cart.reduce((total, item) => total + (item.price * item.qty), 0);
                },

                setPaymentMode(mode) {
                    this.paymentMode = mode;
                    if (mode === 'online') {
                        this.payAmount = 0;
                        setTimeout(() => document.querySelector('input[x-model="studentSearch"]')?.focus(), 100);
                    } else {
                        this.payAmount = '';
                        this.selectedStudent = null;
                        this.studentSearch = '';
                    }
                },

                selectStudent(student) {
                    this.selectedStudent = student;
                    this.studentSearch = ''; // Clear search text
                    this.showStudentDropdown = false;
                },

                addToCart(item) {
                    if (item.stock <= 0) return Swal.fire('Stok Habis', '', 'error');
                    let exist = this.cart.find(c => c.id === item.id);
                    if (exist) {
                        if (exist.qty >= item.stock) return Swal.fire('Stok Terbatas', '', 'warning');
                        exist.qty++;
                    } else {
                        this.cart.push({ ...item, qty: 1 });
                    }
                },

                updateQty(index, amount) {
                    let item = this.cart[index];
                    if (item.qty + amount > item.stock) return Swal.fire('Max Stok', '', 'warning');
                    item.qty += amount;
                    if (item.qty <= 0) this.removeItem(index);
                },

                removeItem(index) { this.cart.splice(index, 1); },
                
                resetCart() { 
                    if(this.cart.length > 0 && confirm('Hapus semua?')) { this.cart = []; this.payAmount = ''; } 
                },
                
                formatRupiah(n) { return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(n); },

                async submitTransaction() {
                    // Validasi Dasar
                    if (this.cart.length === 0) return Swal.fire('Keranjang Kosong', '', 'warning');
                    
                    // Validasi Cash
                    if (this.paymentMode === 'cash' && this.payAmount < this.totalAmount) {
                        return Swal.fire('Uang Kurang', `Kurang Rp ${(this.totalAmount - this.payAmount).toLocaleString('id-ID')}`, 'error');
                    }

                    // Validasi Hutang (WAJIB PILIH SISWA)
                    if (this.paymentMode === 'online' && !this.selectedStudent) {
                        return Swal.fire('Pilih Siswa', 'Untuk transaksi Hutang/QRIS, wajib memilih nama siswa.', 'warning');
                    }

                    Swal.fire({ title: 'Memproses...', didOpen: () => Swal.showLoading(), showConfirmButton: false });

                    try {
                        let res = await fetch('{{ route('pos.transaction.store') }}', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                            body: JSON.stringify({ 
                                cart: this.cart, 
                                total_amount: this.totalAmount, 
                                payment_amount: this.paymentMode === 'cash' ? this.payAmount : 0,
                                student_id: this.selectedStudent ? this.selectedStudent.id : null // Kirim ID Siswa
                            })
                        });
                        
                        let data = await res.json();
                        if (data.success) {
                            Swal.fire({ 
                                icon: 'success', 
                                title: 'Berhasil!',
                                text: this.paymentMode === 'online' ? `Tagihan tersimpan atas nama ${this.selectedStudent.name}` : 'Lunas!',
                                confirmButtonText: 'Cetak Struk',
                                showCancelButton: true
                            }).then((result) => {
                                if (result.isConfirmed) window.open(`{{ url('/pos/transaction') }}/${data.trx_id}/print`, '_blank');
                                window.location.reload();
                            });
                        } else {
                            throw new Error(data.message);
                        }
                    } catch (error) {
                        Swal.fire('Gagal', error.message, 'error');
                    }
                }
            }
        }
    </script>
</x-app-layout>