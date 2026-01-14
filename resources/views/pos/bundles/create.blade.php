<x-app-layout>
    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm sm:rounded-lg p-6">
                
                <h2 class="text-xl font-bold text-gray-800 mb-6">🛠️ Racik Paket Baru</h2>

                <form action="{{ route('pos.bundles.store') }}" method="POST">
                    @csrf
                    
                    <div class="grid grid-cols-2 gap-4 mb-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Paket</label>
                            <input type="text" name="name" required placeholder="Contoh: Paket Seragam Kelas X" 
                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Harga Jual Paket (Rp)</label>
                            <input type="number" name="price" required placeholder="0" 
                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 font-bold text-green-700">
                        </div>
                    </div>

                    <hr class="mb-6 border-gray-200">

                    <div class="mb-4">
                        <h3 class="font-bold text-gray-700 mb-2">Isi Paket:</h3>
                        <div id="items-container" class="space-y-3">
                            <div class="flex gap-2 items-center row-item">
                                <select name="products[]" required class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                                    <option value="">-- Pilih Barang --</option>
                                    @foreach($products as $p)
                                        <option value="{{ $p->id }}">{{ $p->name }} (Stok: {{ $p->stock }})</option>
                                    @endforeach
                                </select>
                                <input type="number" name="quantities[]" value="1" min="1" required placeholder="Qty" class="w-20 rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                                <button type="button" class="text-red-500 hover:text-red-700 px-2 remove-row" disabled>
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <button type="button" id="add-row" class="text-sm text-blue-600 hover:text-blue-800 font-bold flex items-center gap-1 mb-8">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        Tambah Barang Lain
                    </button>

                    <div class="flex justify-end gap-3">
                        <a href="{{ route('pos.bundles.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg font-bold">Batal</a>
                        <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg font-bold hover:bg-blue-700 shadow-lg">Simpan Paket</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('add-row').addEventListener('click', function() {
            // Clone baris pertama
            let container = document.getElementById('items-container');
            let firstRow = container.querySelector('.row-item');
            let newRow = firstRow.cloneNode(true);
            
            // Reset value input di baris baru
            newRow.querySelector('select').value = "";
            newRow.querySelector('input').value = "1";
            newRow.querySelector('.remove-row').disabled = false; // Aktifkan tombol hapus

            // Tambah event listener buat tombol hapus di baris baru
            newRow.querySelector('.remove-row').addEventListener('click', function() {
                this.closest('.row-item').remove();
            });

            container.appendChild(newRow);
        });
    </script>
</x-app-layout>