<x-app-layout>
    <x-slot name="header">
        Kasir POS & Kantin
    </x-slot>

    <div x-data="posSystem()" x-init="init()" class="min-h-[calc(100vh-8rem)] flex flex-col space-y-6">
        
        <!-- HEADER & STATUS -->
        <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
            <div>
                <h1 class="flex items-center gap-3 text-2xl font-black text-slate-800">
                    <div class="bg-[#178BC1] p-2.5 rounded-xl text-white shadow-sm shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle>
                            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                        </svg>
                    </div>
                    Kasir & Penjualan POS
                </h1>
                <p class="mt-1 text-xs text-slate-500 font-medium">
                    Sistem transaksi kasir kantin & koperasi sekolah dengan pencatatan tunai dan tagihan siswa.
                </p>
            </div>
            
            <div class="flex items-center gap-3">
                <a href="{{ route('pos.history.index') }}" 
                   class="inline-flex items-center gap-2 bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 px-4 py-2 rounded-xl font-bold text-xs shadow-sm transition-all">
                    <svg class="w-4 h-4 text-[#178BC1]" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                        <line x1="16" y1="13" x2="8" y2="13"></line>
                        <line x1="16" y1="17" x2="8" y2="17"></line>
                    </svg>
                    <span>Riwayat Transaksi</span>
                </a>
                <span class="px-3.5 py-2 bg-slate-100 border border-slate-200 rounded-xl text-xs font-mono font-bold text-slate-700">
                    {{ date('d M Y, H:i') }}
                </span>
            </div>
        </div>

        <!-- MAIN SPLIT WORKSPACE -->
        <div class="flex flex-col lg:flex-row gap-6 h-full">
            
            <!-- LEFT: PRODUCT CATALOG -->
            <div class="flex-1 bg-white rounded-2xl shadow-sm border border-slate-200 flex flex-col overflow-hidden">
                
                <!-- SEARCH BAR -->
                <div class="p-4 border-b border-slate-100 bg-white">
                    <div class="relative w-full">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                <circle cx="11" cy="11" r="8"></circle>
                                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                            </svg>
                        </div>
                        <input type="text" 
                               x-model="search" 
                               placeholder="Cari nama barang atau kategori..." 
                               class="w-full pl-10 pr-4 py-2.5 text-xs font-medium text-slate-800 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#178BC1]/20 focus:border-[#178BC1] transition-all">
                    </div>
                </div>

                <!-- PRODUCT CARDS GRID -->
                <div class="flex-1 overflow-y-auto p-4 md:p-6 bg-slate-50/50">
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-5 gap-3 md:gap-4">
                        <template x-for="item in filteredItems" :key="item.id">
                            <div @click="addToCart(item)" 
                                 class="group bg-white p-3 rounded-2xl border border-slate-200 shadow-sm hover:border-[#178BC1] hover:shadow-md cursor-pointer transition-all flex flex-col justify-between active:scale-[0.98]">
                                
                                <div class="flex items-center justify-between gap-1 mb-2">
                                    <span class="text-[9px] font-bold px-2 py-0.5 bg-blue-50 text-[#178BC1] border border-blue-200 rounded-md truncate max-w-[90px]" x-text="item.category || 'Umum'"></span>
                                    <span class="text-[10px] font-semibold text-slate-400" x-text="'Stok: ' + item.stock"></span>
                                </div>

                                <div class="w-full aspect-square bg-slate-50 rounded-xl flex items-center justify-center mb-2 overflow-hidden border border-slate-100">
                                    <template x-if="item.image">
                                        <img :src="'/storage/' + item.image" class="w-full h-full object-cover">
                                    </template>
                                    <template x-if="!item.image">
                                        <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                            <line x1="16.5" y1="9.4" x2="7.5" y2="4.21"></line>
                                            <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                                            <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                                            <line x1="12" y1="22.08" x2="12" y2="12"></line>
                                        </svg>
                                    </template>
                                </div>

                                <div>
                                    <h3 class="font-bold text-slate-800 text-xs line-clamp-2" x-text="item.name"></h3>
                                    <p class="text-xs font-black text-[#178BC1] mt-1" x-text="formatRupiah(item.price)"></p>
                                </div>
                            </div>
                        </template>

                        <div x-show="filteredItems.length === 0" class="col-span-full py-16 text-center text-slate-400">
                            <p class="text-sm font-bold text-slate-600">Barang tidak ditemukan</p>
                            <p class="text-xs text-slate-400 mt-1">Coba gunakan kata kunci pencarian yang lain.</p>
                        </div>
                    </div>
                </div>

            </div>

            <!-- RIGHT: CART DRAWER / SIDE PANEL -->
            <div class="w-full lg:w-96 bg-white rounded-2xl shadow-sm border border-slate-200 flex flex-col overflow-hidden h-fit">
                
                <!-- CART HEADER -->
                <div class="p-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-[#178BC1]" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle>
                            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                        </svg>
                        <h3 class="font-bold text-sm text-slate-800">Keranjang Kasir</h3>
                    </div>
                    <button @click="resetCart()" 
                            x-show="cart.length > 0" 
                            class="text-[11px] font-bold text-rose-600 bg-rose-50 hover:bg-rose-100 px-2.5 py-1 rounded-lg border border-rose-200 transition-colors">
                        Reset
                    </button>
                </div>

                <!-- CART ITEMS LIST -->
                <div class="flex-1 overflow-y-auto p-4 space-y-2.5 min-h-[220px] max-h-[350px]">
                    <template x-for="(item, index) in cart" :key="index">
                        <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl flex items-center justify-between gap-2 relative">
                            <div class="flex-1 min-w-0">
                                <h4 class="text-xs font-bold text-slate-800 truncate" x-text="item.name"></h4>
                                <p class="text-[11px] font-bold text-[#178BC1] mt-0.5" x-text="formatRupiah(item.price * item.qty)"></p>
                            </div>

                            <div class="flex items-center gap-1.5 bg-white rounded-lg p-1 border border-slate-200 shrink-0">
                                <button @click="updateQty(index, -1)" class="w-5 h-5 rounded bg-slate-100 text-slate-700 hover:bg-slate-200 font-bold text-xs flex items-center justify-center">-</button>
                                <span class="font-bold text-xs w-6 text-center text-slate-800" x-text="item.qty"></span>
                                <button @click="updateQty(index, 1)" class="w-5 h-5 rounded bg-[#178BC1] text-white hover:bg-[#126da3] font-bold text-xs flex items-center justify-center">+</button>
                            </div>

                            <button @click="removeItem(index)" class="text-slate-400 hover:text-rose-500 p-1" title="Hapus">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                    <line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line>
                                </svg>
                            </button>
                        </div>
                    </template>

                    <div x-show="cart.length === 0" class="flex flex-col items-center justify-center py-12 text-slate-400">
                        <svg class="w-8 h-8 text-slate-300 mb-2" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle>
                            <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                        </svg>
                        <p class="text-xs font-bold text-slate-500">Keranjang masih kosong</p>
                        <p class="text-[11px] text-slate-400">Klik item di sebelah kiri untuk menambah.</p>
                    </div>
                </div>

                <!-- PAYMENT PANEL -->
                <div class="p-4 border-t border-slate-200 bg-white space-y-4">
                    
                    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                        <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Belanja</span>
                        <span class="text-xl font-black text-slate-800" x-text="formatRupiah(totalAmount)"></span>
                    </div>

                    <!-- PAYMENT MODE SWITCHER -->
                    <div class="grid grid-cols-2 gap-1.5 p-1 bg-slate-100 rounded-xl">
                        <button type="button"
                                @click="setPaymentMode('cash')" 
                                :class="paymentMode === 'cash' ? 'bg-white text-[#178BC1] shadow-sm font-bold' : 'text-slate-500 hover:text-slate-800 font-medium'"
                                class="py-2 text-xs rounded-lg transition-all flex items-center justify-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                <rect x="2" y="6" width="20" height="12" rx="2"></rect>
                                <circle cx="12" cy="12" r="2"></circle>
                            </svg>
                            <span>Tunai (Cash)</span>
                        </button>
                        <button type="button"
                                @click="setPaymentMode('online')" 
                                :class="paymentMode === 'online' ? 'bg-white text-purple-700 shadow-sm font-bold' : 'text-slate-500 hover:text-slate-800 font-medium'"
                                class="py-2 text-xs rounded-lg transition-all flex items-center justify-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                                <circle cx="9" cy="7" r="4"></circle>
                            </svg>
                            <span>Hutang Siswa</span>
                        </button>
                    </div>

                    <!-- ONLINE/HUTANG MODE: SELECT SISWA -->
                    <div x-show="paymentMode === 'online'" class="space-y-2 p-3 bg-purple-50 rounded-xl border border-purple-200">
                        <label class="block text-[10px] font-bold text-purple-800 uppercase tracking-wider">
                            Pilih Siswa Tertagih (Wajib)
                        </label>
                        
                        <div class="relative" @click.away="showStudentDropdown = false">
                            <input type="text" 
                                   x-model="studentSearch" 
                                   @focus="showStudentDropdown = true" 
                                   placeholder="Ketik nama atau NISN siswa..."
                                   class="w-full px-3 py-2 text-xs font-medium text-slate-800 bg-white border border-purple-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-400">
                            
                            <div x-show="showStudentDropdown && filteredStudents.length > 0" 
                                 class="absolute z-20 top-full left-0 right-0 mt-1 bg-white border border-slate-200 rounded-xl shadow-lg overflow-hidden max-h-48 overflow-y-auto">
                                <template x-for="st in filteredStudents" :key="st.id">
                                    <div @click="selectStudent(st)" 
                                         class="p-2.5 hover:bg-purple-50 cursor-pointer border-b border-slate-100 last:border-0 text-xs">
                                        <p class="font-bold text-slate-800" x-text="st.name"></p>
                                        <p class="text-[10px] text-slate-400 mt-0.5" x-text="'NISN: ' + (st.nisn || st.nis || '-') + ' • Kelas ' + (st.class || st.class_name || '-')"></p>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <template x-if="selectedStudent">
                            <div class="flex items-center justify-between p-2 bg-white rounded-lg border border-purple-200 text-xs mt-1">
                                <span class="font-bold text-purple-900" x-text="selectedStudent.name"></span>
                                <button type="button" @click="selectedStudent = null; studentSearch = ''" class="text-rose-500 font-bold hover:underline">Hapus</button>
                            </div>
                        </template>
                    </div>

                    <!-- CASH MODE: INPUT UANG DITERIMA -->
                    <div x-show="paymentMode === 'cash'" class="space-y-2">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                            Uang Diterima (Rp)
                        </label>
                        <input type="number" 
                               x-model="payAmount" 
                               placeholder="Contoh: 50000" 
                               class="w-full px-3 py-2 text-xs font-bold text-slate-800 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#178BC1]/20 focus:border-[#178BC1]">
                        
                        <div x-show="payAmount >= totalAmount && payAmount > 0" class="p-2.5 bg-emerald-50 border border-emerald-200 rounded-xl flex items-center justify-between text-xs">
                            <span class="font-bold text-emerald-800">Kembalian:</span>
                            <span class="font-black text-emerald-700 text-sm">Rp <span x-text="(payAmount - totalAmount).toLocaleString('id-ID')"></span></span>
                        </div>
                    </div>

                    <!-- SUBMIT BUTTON -->
                    <button @click="submitTransaction()" 
                            :disabled="cart.length === 0"
                            :class="{
                                'opacity-50 cursor-not-allowed bg-slate-300': cart.length === 0,
                                'bg-[#178BC1] hover:bg-[#126da3] text-white': paymentMode === 'cash' && cart.length > 0,
                                'bg-purple-700 hover:bg-purple-800 text-white': paymentMode === 'online' && cart.length > 0
                            }"
                            class="w-full py-3 px-4 rounded-xl font-bold text-xs uppercase tracking-wider shadow-sm transition-all flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                        <span x-text="paymentMode === 'cash' ? 'Bayar Tunai & Selesai' : 'Simpan Tagihan Siswa'"></span>
                    </button>

                </div>

            </div>

        </div>

    </div>

    <!-- POS SCRIPT -->
    <script>
        function posSystem() {
            return {
                allItems: @json($items),
                allStudents: @json($students),
                search: '',
                studentSearch: '',
                cart: [],
                payAmount: '',
                paymentMode: 'cash',
                showStudentDropdown: false,
                selectedStudent: null,

                init() { 
                    setTimeout(() => { document.querySelector('input[x-model="search"]')?.focus(); }, 100);
                },

                get filteredItems() {
                    if (this.search === '') return this.allItems;
                    const term = this.search.toLowerCase();
                    return this.allItems.filter(i => i.name.toLowerCase().includes(term) || (i.category && i.category.toLowerCase().includes(term)));
                },

                get filteredStudents() {
                    if (this.studentSearch === '') return [];
                    const term = this.studentSearch.toLowerCase();
                    return this.allStudents.filter(s => 
                        s.name.toLowerCase().includes(term) || 
                        (s.nisn && s.nisn.toLowerCase().includes(term)) ||
                        (s.nis && s.nis.toLowerCase().includes(term))
                    ).slice(0, 5);
                },

                get totalAmount() {
                    return this.cart.reduce((total, item) => total + (item.price * item.qty), 0);
                },

                setPaymentMode(mode) {
                    this.paymentMode = mode;
                    if (mode === 'online') {
                        this.payAmount = 0;
                    } else {
                        this.payAmount = '';
                        this.selectedStudent = null;
                        this.studentSearch = '';
                    }
                },

                selectStudent(student) {
                    this.selectedStudent = student;
                    this.studentSearch = '';
                    this.showStudentDropdown = false;
                },

                addToCart(item) {
                    if (item.stock <= 0) return Swal.fire({ icon: 'error', title: 'Stok Habis', text: 'Barang tidak dapat dipilih karena stok 0.', confirmButtonColor: '#178BC1' });
                    let exist = this.cart.find(c => c.id === item.id);
                    if (exist) {
                        if (exist.qty >= item.stock) return Swal.fire({ icon: 'warning', title: 'Maksimal Stok', text: 'Jumlah melebihi stok yang tersedia.', confirmButtonColor: '#178BC1' });
                        exist.qty++;
                    } else {
                        this.cart.push({ ...item, qty: 1 });
                    }
                },

                updateQty(index, amount) {
                    let item = this.cart[index];
                    if (item.qty + amount > item.stock) return Swal.fire({ icon: 'warning', title: 'Maksimal Stok', text: 'Jumlah melebihi stok yang tersedia.', confirmButtonColor: '#178BC1' });
                    item.qty += amount;
                    if (item.qty <= 0) this.removeItem(index);
                },

                removeItem(index) { this.cart.splice(index, 1); },
                
                resetCart() { 
                    if(this.cart.length > 0) {
                        Swal.fire({
                            title: 'Kosongkan Keranjang?',
                            icon: 'question',
                            showCancelButton: true,
                            confirmButtonColor: '#178BC1',
                            cancelButtonColor: '#d33',
                            confirmButtonText: 'Ya, Kosongkan',
                            cancelButtonText: 'Batal'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                this.cart = [];
                                this.payAmount = '';
                            }
                        });
                    }
                },
                
                formatRupiah(n) { return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(n); },

                async submitTransaction() {
                    if (this.cart.length === 0) return Swal.fire({ icon: 'warning', title: 'Keranjang Kosong', text: 'Pilih minimal 1 barang.', confirmButtonColor: '#178BC1' });
                    
                    if (this.paymentMode === 'cash' && this.payAmount < this.totalAmount) {
                        return Swal.fire({ icon: 'error', title: 'Nominal Kurang', text: `Uang kurang Rp ${(this.totalAmount - this.payAmount).toLocaleString('id-ID')}`, confirmButtonColor: '#178BC1' });
                    }

                    if (this.paymentMode === 'online' && !this.selectedStudent) {
                        return Swal.fire({ icon: 'warning', title: 'Pilih Siswa', text: 'Wajib memilih nama siswa untuk transaksi hutang/catat tagihan.', confirmButtonColor: '#178BC1' });
                    }

                    Swal.fire({ title: 'Memproses Transaksi...', didOpen: () => Swal.showLoading(), showConfirmButton: false });

                    try {
                        let res = await fetch('{{ route('pos.transaction.store') }}', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                            body: JSON.stringify({ 
                                cart: this.cart, 
                                total_amount: this.totalAmount, 
                                payment_amount: this.paymentMode === 'cash' ? this.payAmount : 0,
                                student_id: this.selectedStudent ? this.selectedStudent.id : null
                            })
                        });
                        
                        let data = await res.json();
                        if (data.success) {
                            Swal.fire({ 
                                icon: 'success', 
                                title: 'Transaksi Berhasil!',
                                text: this.paymentMode === 'online' ? `Tagihan dicatat atas nama ${this.selectedStudent.name}` : 'Pembayaran tunai diterima.',
                                confirmButtonColor: '#178BC1',
                                confirmButtonText: 'Cetak Struk',
                                showCancelButton: true,
                                cancelButtonText: 'Tutup'
                            }).then((result) => {
                                if (result.isConfirmed) window.open(`{{ url('/pos/transaction') }}/${data.trx_id}/print`, '_blank');
                                window.location.reload();
                            });
                        } else {
                            throw new Error(data.message);
                        }
                    } catch (error) {
                        Swal.fire({ icon: 'error', title: 'Gagal', text: error.message, confirmButtonColor: '#178BC1' });
                    }
                }
            }
        }
    </script>
</x-app-layout>
