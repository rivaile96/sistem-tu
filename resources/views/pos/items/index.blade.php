@extends('layouts.app')

@section('content')
<div x-data="{ 
    showModal: false, 
    isEdit: false,
    modalTitle: 'Tambah Barang Baru',
    actionUrl: '{{ route('pos.items.store') }}',
    method: 'POST',
    form: { id: '', name: '', category: 'Seragam', price: '', stock: '' },
    
    openAdd() {
        this.isEdit = false;
        this.modalTitle = 'Tambah Barang Baru';
        this.actionUrl = '{{ route('pos.items.store') }}';
        this.method = 'POST';
        this.form = { id: '', name: '', category: 'Seragam', price: '', stock: '' };
        this.showModal = true;
    },
    
    openEdit(item) {
        this.isEdit = true;
        this.modalTitle = 'Edit Data Barang';
        this.actionUrl = '/pos/items/' + item.id;
        this.method = 'PUT';
        this.form = { 
            id: item.id, 
            name: item.name, 
            category: item.category, 
            price: item.price, 
            stock: item.stock 
        };
        this.showModal = true;
    }
}">

    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 mb-6">
        <div class="flex flex-col md:flex-row justify-between items-center gap-4">
            <div>
                <h2 class="text-xl font-bold text-gray-800">Master Barang & Inventory</h2>
                <p class="text-sm text-gray-500">Kelola stok barang jualan (Seragam, Buku, ATK).</p>
            </div>
            <button @click="openAdd()" 
                class="bg-[#0ea5e9] hover:bg-sky-600 text-white px-5 py-2.5 rounded-xl text-sm font-medium transition shadow-lg shadow-sky-200 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Tambah Barang
            </button>
        </div>
    </div>

    <div class="mb-6 flex justify-between items-center">
        <form action="{{ route('pos.items.index') }}" method="GET" class="relative w-full max-w-sm">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama barang..." class="w-full pl-10 pr-4 py-2 rounded-xl border border-gray-200 focus:outline-none focus:ring-2 focus:ring-[#0ea5e9]/50 text-sm">
            <svg class="w-4 h-4 text-gray-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
        </form>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($items as $item)
        <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100 hover:shadow-md transition group relative overflow-hidden">
            <span class="absolute top-0 right-0 bg-gray-100 text-gray-500 text-[10px] font-bold px-3 py-1 rounded-bl-xl uppercase tracking-wider">{{ $item->category }}</span>

            <div class="flex items-start justify-between mb-4 mt-2">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-sky-50 text-sky-500 flex items-center justify-center">
                        @if($item->category == 'Seragam')
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        @elseif($item->category == 'Buku')
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                        @else
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                        @endif
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-800 line-clamp-1">{{ $item->name }}</h3>
                        <p class="text-xs text-gray-500">ID: #{{ $item->id }}</p>
                    </div>
                </div>
            </div>

            <div class="flex justify-between items-end border-t border-gray-50 pt-4">
                <div>
                    <p class="text-xs text-gray-400 mb-1">Harga Jual</p>
                    <p class="font-bold text-[#0ea5e9]">Rp {{ number_format($item->price, 0, ',', '.') }}</p>
                </div>
                <div class="text-right">
                    <p class="text-xs text-gray-400 mb-1">Stok Tersedia</p>
                    <p class="font-bold {{ $item->stock < 10 ? 'text-red-500' : 'text-gray-800' }}">
                        {{ $item->stock }} <span class="text-xs font-normal text-gray-400">pcs</span>
                    </p>
                </div>
            </div>

            <div class="absolute inset-0 bg-white/90 opacity-0 group-hover:opacity-100 transition duration-200 flex items-center justify-center gap-3">
                <button @click="openEdit({{ $item }})" class="bg-yellow-100 text-yellow-700 p-2 rounded-lg hover:bg-yellow-200 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                </button>
                
                <form action="{{ route('pos.items.destroy', $item->id) }}" method="POST">
                    @csrf 
                    @method('DELETE')
                    <button type="submit" onclick="confirmDelete(event)" class="bg-red-100 text-red-700 p-2 rounded-lg hover:bg-red-200 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    </button>
                </form>
            </div>
        </div>
        @empty
        <div class="col-span-full text-center py-12 text-gray-400 bg-white rounded-2xl border border-dashed border-gray-200">
            <p>Belum ada data barang.</p>
        </div>
        @endforelse
    </div>

    <div class="mt-6">{{ $items->links() }}</div>

    <div x-show="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm" style="display: none;" x-transition.opacity>
        <div class="bg-white w-full max-w-lg rounded-2xl shadow-2xl p-6 transform transition-all" @click.away="showModal = false">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-bold text-gray-800" x-text="modalTitle"></h3>
                <button @click="showModal = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            <form :action="actionUrl" method="POST">
                @csrf
                <input type="hidden" name="_method" :value="method">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Barang</label>
                        <input type="text" name="name" x-model="form.name" required placeholder="Contoh: Seragam Batik Size L" class="w-full rounded-xl border-gray-300 focus:ring-[#0ea5e9] focus:border-[#0ea5e9]">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Kategori</label>
                            <select name="category" x-model="form.category" class="w-full rounded-xl border-gray-300 focus:ring-[#0ea5e9] focus:border-[#0ea5e9]">
                                <option value="Seragam">Seragam</option>
                                <option value="Buku">Buku</option>
                                <option value="ATK">ATK</option>
                                <option value="Makanan">Makanan/Minuman</option>
                                <option value="Lainnya">Lainnya</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Stok Awal</label>
                            <input type="number" name="stock" x-model="form.stock" required min="0" placeholder="0" class="w-full rounded-xl border-gray-300 focus:ring-[#0ea5e9] focus:border-[#0ea5e9]">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Harga Jual (Rp)</label>
                        <input type="number" name="price" x-model="form.price" required min="0" placeholder="Contoh: 150000" class="w-full rounded-xl border-gray-300 focus:ring-[#0ea5e9] focus:border-[#0ea5e9]">
                    </div>
                </div>
                <div class="flex gap-3 mt-8">
                    <button type="button" @click="showModal = false" class="flex-1 px-4 py-3 border border-gray-200 text-gray-600 rounded-xl font-medium hover:bg-gray-50 transition">Batal</button>
                    <button type="submit" class="flex-1 px-4 py-3 bg-[#0ea5e9] hover:bg-sky-600 text-white rounded-xl font-medium shadow-lg shadow-sky-200 transition">Simpan Barang</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection