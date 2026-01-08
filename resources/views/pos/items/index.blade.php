<x-app-layout>
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
    }">

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            
            <div class="p-5 border-b border-gray-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl font-bold text-gray-800">Stok Barang & Produk</h2>
                    <p class="text-sm text-gray-500">Master data inventory koperasi.</p>
                </div>

                <div class="flex flex-col sm:flex-row gap-3">
                    <form method="GET" action="{{ route('pos.items.index') }}" class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </span>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama barang..." 
                               class="w-full sm:w-64 pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-[#0ea5e9] focus:border-[#0ea5e9] focus:bg-white text-sm transition">
                    </form>

                    <button @click="openAddModal()" class="bg-[#0ea5e9] hover:bg-sky-600 text-white px-5 py-2.5 rounded-xl text-sm font-bold shadow-md hover:shadow-sky-200 transition flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                        Tambah
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-600">
                    <thead class="bg-gray-50 text-gray-700 uppercase text-xs font-bold border-b border-gray-100">
                        <tr>
                            <th class="px-6 py-4">Produk</th>
                            <th class="px-6 py-4 text-center">Kategori</th>
                            <th class="px-6 py-4 text-center">Stok</th>
                            <th class="px-6 py-4 text-right">Harga</th>
                            <th class="px-6 py-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($items as $item)
                        <tr class="hover:bg-blue-50/40 transition duration-150">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-4">
                                    <div class="h-12 w-12 rounded-xl bg-gray-100 border border-gray-200 overflow-hidden flex-shrink-0 relative">
                                        @if($item->image)
                                            <img src="{{ asset('storage/' . $item->image) }}" class="h-full w-full object-cover">
                                        @else
                                            <div class="h-full w-full flex items-center justify-center text-gray-400">
                                                <svg class="w-6 h-6 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                            </div>
                                        @endif
                                    </div>
                                    <span class="font-bold text-gray-900">{{ $item->name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="bg-gray-100 text-gray-600 px-3 py-1 rounded-lg text-xs font-bold border border-gray-200">
                                    {{ $item->category }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="{{ $item->stock <= 5 ? 'bg-red-50 text-red-600 border-red-100' : 'bg-green-50 text-green-600 border-green-100' }} px-3 py-1 rounded-lg text-xs font-bold border">
                                    {{ $item->stock }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right font-bold text-[#0ea5e9]">
                                Rp {{ number_format($item->price, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button @click='openEditModal(@json($item))' class="p-2 text-gray-500 hover:text-[#0ea5e9] hover:bg-sky-50 rounded-lg transition border border-transparent hover:border-sky-100">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                    </button>
                                    <form action="{{ route('pos.items.destroy', $item->id) }}" method="POST" onsubmit="return confirmDelete(event)" class="inline-block">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="p-2 text-gray-500 hover:text-red-600 hover:bg-red-50 rounded-lg transition border border-transparent hover:border-red-100">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="px-6 py-12 text-center text-gray-400">Data barang masih kosong.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="p-4 border-t border-gray-100 bg-gray-50">
                {{ $items->links() }}
            </div>
        </div>

        <div x-show="showModal" class="fixed inset-0 z-[60] flex items-center justify-center bg-black/50 backdrop-blur-sm p-4" x-transition.opacity x-cloak>
            <div class="bg-white w-full max-w-md rounded-2xl shadow-2xl overflow-hidden transform transition-all max-h-[90vh] overflow-y-auto" @click.away="showModal = false">
                <div class="p-5 border-b border-gray-100 flex justify-between items-center bg-gray-50">
                    <h3 class="text-lg font-bold text-gray-800" x-text="isEdit ? 'Edit Barang' : 'Tambah Baru'"></h3>
                    <button @click="showModal = false" class="text-gray-400 hover:text-gray-600">✕</button>
                </div>
                <div class="p-6">
                    <form :action="formAction" method="POST" enctype="multipart/form-data">
                        @csrf
                        <template x-if="isEdit"><input type="hidden" name="_method" value="PUT"></template>
                        <div class="mb-6 flex justify-center">
                            <div class="relative w-32 h-32 bg-gray-50 rounded-2xl border-2 border-dashed border-gray-300 flex items-center justify-center overflow-hidden group hover:border-[#0ea5e9] transition cursor-pointer">
                                <template x-if="previewImage"><img :src="previewImage" class="w-full h-full object-cover"></template>
                                <template x-if="!previewImage">
                                    <div class="text-center text-gray-400 group-hover:text-[#0ea5e9]"><span class="text-xs font-bold">Upload Foto</span></div>
                                </template>
                                <input type="file" name="image" @change="fileChosen" class="absolute inset-0 opacity-0 cursor-pointer">
                            </div>
                        </div>
                        <div class="space-y-4">
                            <div><label class="block text-sm font-bold text-gray-700 mb-1">Nama Produk</label><input type="text" name="name" x-model="formData.name" required class="w-full rounded-xl border-gray-300 focus:ring-[#0ea5e9] focus:border-[#0ea5e9]"></div>
                            <div><label class="block text-sm font-bold text-gray-700 mb-1">Kategori</label><select name="category" x-model="formData.category" class="w-full rounded-xl border-gray-300 focus:ring-[#0ea5e9] focus:border-[#0ea5e9]"><option value="Makanan">Makanan</option><option value="Minuman">Minuman</option><option value="ATK">ATK</option><option value="Seragam">Seragam</option><option value="Lainnya">Lainnya</option></select></div>
                            <div class="grid grid-cols-2 gap-4">
                                <div><label class="block text-sm font-bold text-gray-700 mb-1">Stok</label><input type="number" name="stock" x-model="formData.stock" required min="0" class="w-full rounded-xl border-gray-300 focus:ring-[#0ea5e9] focus:border-[#0ea5e9]"></div>
                                <div><label class="block text-sm font-bold text-gray-700 mb-1">Harga</label><input type="number" name="price" x-model="formData.price" required min="0" class="w-full rounded-xl border-gray-300 focus:ring-[#0ea5e9] focus:border-[#0ea5e9]"></div>
                            </div>
                        </div>
                        <div class="flex gap-3 mt-8">
                            <button type="button" @click="showModal = false" class="flex-1 px-4 py-3 border border-gray-300 text-gray-700 rounded-xl font-bold hover:bg-gray-50">Batal</button>
                            <button type="submit" class="flex-1 px-4 py-3 bg-[#0ea5e9] hover:bg-sky-600 text-white rounded-xl font-bold shadow-lg shadow-sky-200">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>