<x-app-layout>
    <x-slot name="header">
        Master Barang POS
    </x-slot>

    <div x-data="{ 
        showModal: false,
        isEdit: false,
        formAction: '',
        previewImage: null,
        formData: { name: '', category: 'Umum', stock: 0, price: 0, cost_price: 0 },

        openAddModal() {
            this.isEdit = false;
            this.formAction = '{{ route('pos.items.store') }}';
            this.formData = { name: '', category: 'Umum', stock: 10, price: 5000, cost_price: 0 };
            this.previewImage = null;
            this.showModal = true;
        },

        openEditModal(item) {
            this.isEdit = true;
            this.formAction = '/pos/items/' + item.id;
            this.formData = { 
                name: item.name, 
                category: item.category, 
                stock: item.stock, 
                price: item.price,
                cost_price: item.cost_price 
            };
            this.previewImage = item.image ? '/storage/' + item.image : null;
            this.showModal = true;
        },

        fileChosen(event) {
            const file = event.target.files[0];
            if (file) {
                this.previewImage = URL.createObjectURL(file);
            }
        }
    }" class="space-y-6">

        <!-- HEADER & ACTIONS -->
        <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
            <div>
                <h1 class="flex items-center gap-3 text-2xl font-black text-slate-800">
                    <div class="bg-[#178BC1] p-2.5 rounded-xl text-white shadow-sm shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <line x1="16.5" y1="9.4" x2="7.5" y2="4.21"></line>
                            <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                            <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                            <line x1="12" y1="22.08" x2="12" y2="12"></line>
                        </svg>
                    </div>
                    Master Barang & Inventori
                </h1>
                <p class="mt-1 text-xs text-slate-500 font-medium">
                    Kelola data katalog produk kantin, stok barang koperasi, dan penentuan harga jual.
                </p>
            </div>

            <div class="flex items-center gap-2.5 w-full md:w-auto">
                <form method="GET" action="{{ route('pos.items.index') }}" class="relative flex-1 md:w-64">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                    </div>
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}" 
                           placeholder="Cari nama barang..." 
                           class="w-full pl-10 pr-4 py-2 text-xs font-medium text-slate-800 bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#178BC1]/20 focus:border-[#178BC1] transition-all">
                </form>

                <button @click="openAddModal()" 
                        class="inline-flex items-center gap-1.5 bg-[#178BC1] hover:bg-[#126da3] text-white px-4 py-2 rounded-xl font-bold text-xs shadow-sm transition-all shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    <span>Tambah Barang</span>
                </button>
            </div>
        </div>

        <!-- PRODUCTS TABLE (SPMB MATCHING THEAD & TBODY) -->
        <div class="overflow-hidden bg-white border shadow-sm rounded-2xl border-slate-200">
            <div class="overflow-x-auto min-h-[350px]">
                <table class="w-full text-sm text-left">
                    <thead class="bg-slate-50/80 text-slate-500 uppercase font-bold text-[10px] tracking-wider border-b border-slate-200">
                        <tr>
                            <th class="px-6 py-4">Informasi Produk</th>
                            <th class="px-6 py-4 text-center">Kategori</th>
                            <th class="px-6 py-4 text-center">Sisa Stok</th>
                            <th class="px-6 py-4 text-right">Harga Jual</th>
                            <th class="px-6 py-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($items as $item)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-slate-50 border border-slate-200 overflow-hidden flex items-center justify-center shrink-0">
                                            @if($item->image)
                                                <img src="{{ asset('storage/' . $item->image) }}" class="h-full w-full object-cover">
                                            @else
                                                <svg class="w-5 h-5 text-slate-300" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                                    <line x1="16.5" y1="9.4" x2="7.5" y2="4.21"></line>
                                                    <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                                                </svg>
                                            @endif
                                        </div>
                                        <div>
                                            <p class="text-xs font-bold text-slate-800">{{ $item->name }}</p>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-6 py-4 text-center">
                                    <span class="px-2.5 py-1 text-[10px] font-bold rounded-lg bg-blue-50 text-[#178BC1] border border-blue-200">
                                        {{ $item->category }}
                                    </span>
                                </td>

                                <td class="px-6 py-4 text-center">
                                    <span class="px-2.5 py-1 text-[10px] font-bold rounded-lg border {{ $item->stock <= 5 ? 'bg-rose-50 text-rose-700 border-rose-200' : 'bg-emerald-50 text-emerald-700 border-emerald-200' }}">
                                        {{ $item->stock }} Unit
                                    </span>
                                </td>

                                <td class="px-6 py-4 text-right font-bold text-xs text-slate-800">
                                    Rp {{ number_format($item->price, 0, ',', '.') }}
                                </td>

                                <td class="px-6 py-4 text-center">
                                    <div class="flex items-center justify-center gap-1.5">
                                        <button @click='openEditModal(@json($item))' 
                                                class="p-1.5 text-slate-600 hover:text-slate-900 bg-slate-100 hover:bg-slate-200 rounded-lg border border-slate-200 transition-colors"
                                                title="Edit Produk">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                            </svg>
                                        </button>

                                        <form action="{{ route('pos.items.destroy', $item->id) }}" method="POST" onsubmit="return confirmDelete(event)" class="inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="p-1.5 text-rose-600 hover:bg-rose-50 rounded-lg border border-transparent hover:border-rose-200 transition-colors"
                                                    title="Hapus Produk">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                                    <polyline points="3 6 5 6 21 6"></polyline>
                                                    <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-16 text-center text-slate-400">
                                    <p class="text-sm font-bold text-slate-600">Belum ada barang di inventori.</p>
                                    <p class="text-xs text-slate-400 mt-1">Klik tombol Tambah Barang untuk mendaftarkan produk baru.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($items->hasPages())
                <div class="px-6 py-4 border-t border-slate-200 bg-slate-50/50">
                    {{ $items->links() }}
                </div>
            @endif
        </div>

        <!-- MODAL ADD / EDIT (SPMB MATCHING SOLID) -->
        <div x-show="showModal" 
             class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-sm p-4" 
             x-cloak>
            <div class="bg-white w-full max-w-md rounded-2xl shadow-xl border border-slate-200 overflow-hidden" 
                 @click.away="showModal = false">
                
                <div class="p-5 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                    <h3 class="text-sm font-bold text-slate-800" x-text="isEdit ? 'Edit Produk' : 'Tambah Produk Baru'"></h3>
                    <button @click="showModal = false" class="text-slate-400 hover:text-slate-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                    </button>
                </div>

                <div class="p-6">
                    <form :action="formAction" method="POST" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        <template x-if="isEdit"><input type="hidden" name="_method" value="PUT"></template>
                        
                        <div class="flex justify-center">
                            <div class="relative w-28 h-28 bg-slate-50 rounded-2xl border-2 border-dashed border-slate-300 flex items-center justify-center overflow-hidden hover:border-[#178BC1] transition-colors cursor-pointer">
                                <template x-if="previewImage"><img :src="previewImage" class="w-full h-full object-cover"></template>
                                <template x-if="!previewImage">
                                    <div class="text-center text-slate-400">
                                        <svg class="w-6 h-6 mx-auto mb-1 text-slate-300" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                            <circle cx="8.5" cy="8.5" r="1.5"></circle>
                                            <polyline points="21 15 16 10 5 21"></polyline>
                                        </svg>
                                        <span class="text-[10px] font-bold">Foto Produk</span>
                                    </div>
                                </template>
                                <input type="file" name="image" @change="fileChosen" class="absolute inset-0 opacity-0 cursor-pointer">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Nama Produk <span class="text-rose-500">*</span></label>
                            <input type="text" name="name" x-model="formData.name" required 
                                   class="w-full px-3 py-2 text-xs font-medium text-slate-800 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#178BC1]/20 focus:border-[#178BC1]">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Kategori</label>
                            <select name="category" x-model="formData.category" 
                                    class="w-full px-3 py-2 text-xs font-semibold text-slate-700 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#178BC1]/20 focus:border-[#178BC1]">
                                <option value="Makanan">Makanan</option>
                                <option value="Minuman">Minuman</option>
                                <option value="ATK">ATK</option>
                                <option value="Seragam">Seragam</option>
                                <option value="Lainnya">Lainnya</option>
                            </select>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Stok Awal <span class="text-rose-500">*</span></label>
                                <input type="number" name="stock" x-model="formData.stock" required min="0" 
                                       class="w-full px-3 py-2 text-xs font-bold text-slate-800 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#178BC1]/20 focus:border-[#178BC1]">
                            </div>
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Harga Jual (Rp) <span class="text-rose-500">*</span></label>
                                <input type="number" name="price" x-model="formData.price" required min="0" 
                                       class="w-full px-3 py-2 text-xs font-bold text-slate-800 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#178BC1]/20 focus:border-[#178BC1]">
                            </div>
                        </div>

                        <div class="flex gap-2.5 pt-3 border-t border-slate-100">
                            <button type="button" @click="showModal = false" 
                                    class="flex-1 py-2 text-xs font-bold text-slate-600 border border-slate-200 rounded-xl hover:bg-slate-50">
                                Batal
                            </button>
                            <button type="submit" 
                                    class="flex-1 py-2 text-xs font-bold bg-[#178BC1] hover:bg-[#126da3] text-white rounded-xl shadow-sm">
                                Simpan Produk
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>

    <script>
        function confirmDelete(e) {
            e.preventDefault();
            const form = e.target;
            Swal.fire({
                title: 'Hapus Produk Ini?',
                text: 'Data produk akan dihapus dari sistem inventori.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#94a3b8',
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) form.submit();
            });
            return false;
        }
    </script>
</x-app-layout>
