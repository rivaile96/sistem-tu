<x-app-layout>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <div class="bg-gradient-to-br from-slate-50 to-blue-50 min-h-screen py-12">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-gradient-to-br from-white to-slate-50 shadow-2xl sm:rounded-3xl p-8 border border-slate-200 relative overflow-hidden">
                <div class="absolute top-0 right-0 -mt-12 -mr-12 w-64 h-64 bg-gradient-to-br from-amber-400/10 to-transparent rounded-full blur-3xl opacity-50"></div>
                
                <div class="flex items-center gap-4 mb-8 relative z-10">
                    <div class="relative">
                        <div class="absolute inset-0 bg-gradient-to-r from-amber-400 to-orange-400 rounded-2xl blur-lg opacity-30"></div>
                        <div class="relative bg-gradient-to-br from-amber-400 to-orange-500 p-3 rounded-2xl shadow-lg">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                        </div>
                    </div>
                    <div>
                        <h2 class="text-3xl font-bold text-slate-800 tracking-tight">Edit Paket Bundling</h2>
                        <p class="text-sm text-slate-500 mt-1">Perbarui komposisi barang atau harga paket</p>
                    </div>
                </div>

                <form action="{{ route('pos.bundles.update', $bundle->id) }}" method="POST" id="bundleForm">
                    @csrf
                    @method('PUT') <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-10">
                        <div class="space-y-6">
                            <div class="bg-gradient-to-br from-white to-slate-50 p-6 rounded-2xl border border-slate-200 shadow-sm">
                                <label class="block text-sm font-bold text-slate-600 mb-3 flex items-center gap-2">
                                    <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    Detail Paket
                                </label>
                                
                                <div class="space-y-4">
                                    <div>
                                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Nama Paket</label>
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                                </svg>
                                            </div>
                                            <input type="text" name="name" value="{{ $bundle->name }}" required 
                                                   class="w-full pl-10 rounded-xl border-slate-300 shadow-sm focus:ring-amber-500 focus:border-amber-500 text-slate-800 font-medium py-3.5 bg-white">
                                        </div>
                                    </div>
                                    
                                    <div>
                                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Harga Jual Paket</label>
                                        <div class="relative">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <span class="text-slate-500 font-bold">Rp</span>
                                            </div>
                                            <input type="number" name="price" value="{{ $bundle->price }}" required 
                                                   class="w-full pl-12 rounded-xl border-slate-300 shadow-sm focus:ring-amber-500 focus:border-amber-500 font-bold text-emerald-600 text-lg py-3.5 bg-white">
                                        </div>
                                        <p class="text-xs text-slate-400 mt-2">Harga total yang akan ditagihkan ke siswa</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="bg-gradient-to-br from-white to-slate-50 p-6 rounded-2xl border border-slate-200 shadow-sm">
                                <h3 class="text-sm font-bold text-slate-600 mb-4 flex items-center gap-2">
                                    <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    Estimasi Profit
                                </h3>
                                <div class="space-y-3">
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-slate-500">Total Harga Beli (Modal):</span>
                                        <span class="font-bold text-slate-800" id="totalCost">Rp 0</span>
                                    </div>
                                    <div class="flex justify-between items-center">
                                        <span class="text-sm text-slate-500">Harga Jual Paket:</span>
                                        <span class="font-bold text-emerald-600" id="sellingPrice">Rp 0</span>
                                    </div>
                                    <div class="pt-3 border-t border-slate-200">
                                        <div class="flex justify-between items-center">
                                            <span class="text-sm text-slate-500">Estimasi Profit:</span>
                                            <span class="font-bold text-lg" id="profitEstimation">Rp 0</span>
                                        </div>
                                        <div class="mt-2">
                                            <span class="text-xs text-slate-400" id="profitPercentage">0% margin</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="space-y-6">
                            <div class="bg-gradient-to-br from-white to-slate-50 p-6 rounded-2xl border border-slate-200 shadow-sm">
                                <div class="flex items-center justify-between mb-4">
                                    <h3 class="text-sm font-bold text-slate-600 flex items-center gap-2">
                                        <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                                        </svg>
                                        Komposisi Paket
                                    </h3>
                                    <span class="text-xs font-bold text-slate-500 bg-slate-100 px-3 py-1 rounded-lg" id="itemCount">0 Item</span>
                                </div>
                                
                                <div id="items-container" class="space-y-4">
                                    @foreach($bundle->items as $index => $item)
                                    <div class="row-item bg-white p-4 rounded-xl border border-slate-200 shadow-sm hover:shadow-md transition-shadow group">
                                        <div class="flex gap-4 items-center">
                                            <div class="relative">
                                                <div class="absolute inset-0 bg-gradient-to-br from-slate-200 to-slate-100 rounded-lg blur opacity-50"></div>
                                                <div class="relative w-10 h-10 rounded-lg bg-gradient-to-br from-slate-100 to-slate-50 border border-slate-200 flex items-center justify-center">
                                                    <span class="text-slate-500 font-bold text-sm counter">{{ $index + 1 }}</span>
                                                </div>
                                            </div>
                                            
                                            <div class="flex-1 space-y-3">
                                                <div>
                                                    <label class="block text-xs font-bold text-slate-500 mb-1">Pilih Barang</label>
                                                    <div class="relative">
                                                        <select name="products[]" required class="product-select w-full rounded-lg border-slate-300 shadow-sm focus:ring-amber-500 focus:border-amber-500 text-sm py-2.5 bg-white">
                                                            <option value="">-- Pilih Barang --</option>
                                                            @foreach($products as $p)
                                                                <option value="{{ $p->id }}" 
                                                                        data-price="{{ $p->buy_price ?? 0 }}" 
                                                                        data-stock="{{ $p->stock }}" 
                                                                        {{ $p->id == $item->pos_item_id ? 'selected' : '' }}>
                                                                    {{ $p->name }} (Stok: {{ $p->stock }})
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                
                                                <div>
                                                    <label class="block text-xs font-bold text-slate-500 mb-1">Jumlah (Qty)</label>
                                                    <div class="flex items-center gap-2">
                                                        <div class="relative flex-1">
                                                            <input type="number" name="quantities[]" value="{{ $item->quantity }}" min="1" required 
                                                                   class="quantity-input w-full rounded-lg border-slate-300 shadow-sm focus:ring-amber-500 focus:border-amber-500 text-sm py-2.5 text-center bg-white">
                                                        </div>
                                                        <button type="button" class="remove-row p-2 text-slate-400 hover:text-rose-500 hover:bg-rose-50 rounded-lg transition-colors group-hover:opacity-100 opacity-50">
                                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                            </svg>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                                
                                <button type="button" id="add-row" class="mt-6 w-full py-3.5 border-2 border-dashed border-slate-300 hover:border-amber-500 text-slate-500 hover:text-amber-500 rounded-xl font-bold text-sm transition-all duration-300 hover:bg-amber-50/50 flex items-center justify-center gap-2 group">
                                    <svg class="w-5 h-5 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                    </svg>
                                    Tambah Barang Lain
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end gap-4 pt-8 mt-8 border-t border-slate-200">
                        <a href="{{ route('pos.bundles.index') }}" class="px-6 py-3.5 bg-gradient-to-r from-slate-100 to-slate-200 hover:from-slate-200 hover:to-slate-300 text-slate-700 rounded-xl font-bold shadow-sm transition-all transform hover:-translate-y-0.5 flex items-center gap-2">
                            Batal
                        </a>
                        <button type="submit" class="px-8 py-3.5 bg-gradient-to-r from-amber-500 to-orange-500 hover:from-orange-600 hover:to-amber-500 text-white rounded-xl font-bold shadow-lg shadow-amber-200/50 transition-all transform hover:-translate-y-0.5 flex items-center gap-3">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Fungsi hitung cost & profit
            function calculateCostAndProfit() {
                let totalCost = 0;
                const rows = document.querySelectorAll('.row-item');
                
                rows.forEach((row, index) => {
                    const select = row.querySelector('.product-select');
                    const quantityInput = row.querySelector('.quantity-input');
                    const selectedOption = select.options[select.selectedIndex];
                    
                    if (selectedOption && selectedOption.value) {
                        const buyPrice = parseFloat(selectedOption.getAttribute('data-price')) || 0;
                        const quantity = parseInt(quantityInput.value) || 1;
                        totalCost += (buyPrice * quantity);
                    }
                });
                
                // Update item count
                document.getElementById('itemCount').textContent = `${rows.length} Item${rows.length > 1 ? 's' : ''}`;
                
                // Update tampilan harga modal
                document.getElementById('totalCost').textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(totalCost);
                
                // Ambil harga jual
                const sellingPriceInput = document.querySelector('input[name="price"]');
                const sellingPrice = parseFloat(sellingPriceInput.value) || 0;
                
                // Update tampilan harga jual
                document.getElementById('sellingPrice').textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(sellingPrice);
                
                // Hitung Profit
                const profit = sellingPrice - totalCost;
                const profitElement = document.getElementById('profitEstimation');
                const percentageElement = document.getElementById('profitPercentage');
                
                if (profit >= 0) {
                    profitElement.textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(profit);
                    profitElement.className = 'font-bold text-lg text-emerald-600';
                    
                    const percentage = totalCost > 0 ? ((profit / totalCost) * 100).toFixed(1) : 0;
                    percentageElement.textContent = `${percentage}% margin`;
                    percentageElement.className = 'text-xs text-emerald-500';
                } else {
                    profitElement.textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(Math.abs(profit));
                    profitElement.className = 'font-bold text-lg text-rose-600';
                    percentageElement.textContent = 'Rugi';
                    percentageElement.className = 'text-xs text-rose-500';
                }
            }
            
            // Fungsi update listener pada baris
            function updateRowEventListeners(row) {
                const select = row.querySelector('.product-select');
                const quantityInput = row.querySelector('.quantity-input');
                const removeBtn = row.querySelector('.remove-row');
                
                select.addEventListener('change', calculateCostAndProfit);
                quantityInput.addEventListener('input', calculateCostAndProfit);
                
                removeBtn.addEventListener('click', function() {
                    const rows = document.querySelectorAll('.row-item');
                    if (rows.length > 1) {
                        Swal.fire({
                            title: 'Hapus Barang?',
                            text: "Barang ini akan dihapus dari paket.",
                            icon: 'question',
                            showCancelButton: true,
                            confirmButtonColor: '#f59e0b', // Amber
                            cancelButtonColor: '#94a3b8',
                            confirmButtonText: 'Ya, Hapus',
                            cancelButtonText: 'Batal'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                row.remove();
                                updateRowNumbers();
                                calculateCostAndProfit();
                            }
                        });
                    } else {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Tidak Bisa Dihapus',
                            text: 'Minimal harus ada satu barang dalam paket!',
                            confirmButtonColor: '#f59e0b'
                        });
                    }
                });
            }
            
            // Update nomor urut
            function updateRowNumbers() {
                const rows = document.querySelectorAll('.row-item');
                rows.forEach((row, index) => {
                    row.querySelector('.counter').textContent = index + 1;
                });
            }
            
            // Tambah Baris Baru
            document.getElementById('add-row').addEventListener('click', function() {
                const container = document.getElementById('items-container');
                // Clone baris pertama sebagai template
                const firstRow = container.querySelector('.row-item'); 
                const newRow = firstRow.cloneNode(true);
                
                // Reset nilai di baris baru
                newRow.querySelector('.product-select').value = '';
                newRow.querySelector('.quantity-input').value = '1';
                
                container.appendChild(newRow);
                updateRowNumbers();
                updateRowEventListeners(newRow); // Pasang listener baru
                calculateCostAndProfit();
            });
            
            // Listener Global Harga Jual
            document.querySelector('input[name="price"]').addEventListener('input', calculateCostAndProfit);
            
            // Inisialisasi Listener untuk Item yang sudah ada (dari Database)
            document.querySelectorAll('.row-item').forEach(row => {
                updateRowEventListeners(row);
            });
            
            // Form Validation
            document.getElementById('bundleForm').addEventListener('submit', function(e) {
                const price = parseFloat(document.querySelector('input[name="price"]').value) || 0;
                const rows = document.querySelectorAll('.row-item');
                let hasEmptyProduct = false;
                
                rows.forEach(row => {
                    const select = row.querySelector('.product-select');
                    if (!select.value) {
                        hasEmptyProduct = true;
                        select.classList.add('border-rose-500');
                    } else {
                        select.classList.remove('border-rose-500');
                    }
                });
                
                if (hasEmptyProduct) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'error',
                        title: 'Data Belum Lengkap',
                        text: 'Semua barang harus dipilih!',
                        confirmButtonColor: '#f59e0b'
                    });
                } else if (price <= 0) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'error',
                        title: 'Harga Tidak Valid',
                        text: 'Harga jual paket harus lebih dari 0!',
                        confirmButtonColor: '#f59e0b'
                    });
                }
            });
            
            // JALANKAN SEKALI SAAT LOAD (Biar angka profit langsung muncul)
            calculateCostAndProfit();
        });
    </script>
</x-app-layout>