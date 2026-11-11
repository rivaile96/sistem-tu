<x-app-layout>
    <x-slot name="header">
        Racik Paket Bundling
    </x-slot>

    <div class="space-y-6">
        
        <!-- HEADER & BACK -->
        <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
            <div>
                <h1 class="flex items-center gap-3 text-2xl font-black text-slate-800">
                    <div class="bg-[#178BC1] p-2.5 rounded-xl text-white shadow-sm shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <line x1="12" y1="5" x2="12" y2="19"></line>
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                        </svg>
                    </div>
                    Racik Paket Bundling Baru
                </h1>
                <p class="mt-1 text-xs text-slate-500 font-medium">
                    Gabungkan beberapa jenis barang/seragam ke dalam satu paket penjualan kasir atau tagihan.
                </p>
            </div>
            
            <a href="{{ route('pos.bundles.index') }}" 
               class="inline-flex items-center gap-2 bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 px-4 py-2.5 rounded-xl font-bold text-xs shadow-sm transition-all">
                <svg class="w-4 h-4 text-[#178BC1]" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
                <span>Kembali ke Daftar Paket</span>
            </a>
        </div>

        <!-- FORM CARD (SPMB MATCHING SOLID) -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 md:p-8">
            <form action="{{ route('pos.bundles.store') }}" method="POST" id="bundleForm">
                @csrf
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                    
                    <!-- LEFT COLUMN: DETAIL PAKET & ESTIMASI PROFIT -->
                    <div class="space-y-6">
                        
                        <div class="bg-slate-50 p-6 rounded-2xl border border-slate-200 space-y-4">
                            <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2">
                                <svg class="w-4 h-4 text-[#178BC1]" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                    <polyline points="14 2 14 8 20 8"></polyline>
                                </svg>
                                Detail Informasi Paket
                            </h3>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                    Nama Paket <span class="text-rose-500">*</span>
                                </label>
                                <input type="text" 
                                       name="name" 
                                       required 
                                       placeholder="Contoh: Paket Seragam Lengkap Kelas 1" 
                                       class="w-full px-3.5 py-2.5 text-xs font-medium text-slate-800 bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#178BC1]/20 focus:border-[#178BC1] transition-all">
                            </div>
                            
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                    Harga Jual Paket (Rp) <span class="text-rose-500">*</span>
                                </label>
                                <input type="number" 
                                       name="price" 
                                       required 
                                       min="0"
                                       placeholder="Contoh: 450000" 
                                       class="w-full px-3.5 py-2.5 text-xs font-bold text-slate-800 bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#178BC1]/20 focus:border-[#178BC1] transition-all">
                                <p class="text-[11px] text-slate-400 mt-1">Harga total yang akan ditagihkan ke kasir / kwitansi.</p>
                            </div>
                        </div>

                        <!-- ESTIMASI PROFIT -->
                        <div class="bg-slate-50 p-6 rounded-2xl border border-slate-200 space-y-3">
                            <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2 mb-3">
                                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                    <line x1="12" y1="1" x2="12" y2="23"></line>
                                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                                </svg>
                                Estimasi Margin Keuntungan
                            </h3>

                            <div class="flex justify-between items-center text-xs">
                                <span class="text-slate-500">Total Harga Modal Barang:</span>
                                <span class="font-bold text-slate-800" id="totalCost">Rp 0</span>
                            </div>

                            <div class="flex justify-between items-center text-xs">
                                <span class="text-slate-500">Harga Jual Paket:</span>
                                <span class="font-bold text-emerald-700" id="sellingPrice">Rp 0</span>
                            </div>

                            <div class="pt-3 border-t border-slate-200 flex justify-between items-center">
                                <span class="text-xs font-bold text-slate-700">Estimasi Margin:</span>
                                <span class="font-black text-sm text-emerald-700" id="profitEstimation">Rp 0</span>
                            </div>
                        </div>

                    </div>

                    <!-- RIGHT COLUMN: KOMPOSISI BARANG -->
                    <div class="bg-slate-50 p-6 rounded-2xl border border-slate-200 space-y-4">
                        <div class="flex items-center justify-between">
                            <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-2">
                                <svg class="w-4 h-4 text-[#178BC1]" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                    <line x1="16.5" y1="9.4" x2="7.5" y2="4.21"></line>
                                    <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                                </svg>
                                Komposisi Isi Paket
                            </h3>
                            <span class="px-2.5 py-0.5 rounded-md bg-white border border-slate-200 text-[10px] font-bold text-slate-600" id="itemCount">
                                1 Item
                            </span>
                        </div>

                        <div id="items-container" class="space-y-3">
                            <div class="row-item bg-white p-4 rounded-xl border border-slate-200 flex gap-3 items-center">
                                <div class="w-7 h-7 rounded-lg bg-blue-50 text-[#178BC1] border border-blue-200 font-bold text-xs flex items-center justify-center shrink-0">
                                    <span class="row-number">1</span>
                                </div>

                                <div class="flex-1 space-y-2">
                                    <div>
                                        <select name="products[]" required class="product-select w-full px-3 py-1.5 rounded-lg border border-slate-200 text-xs font-medium text-slate-800 bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#178BC1]/20 focus:border-[#178BC1]">
                                            <option value="">-- Pilih Barang --</option>
                                            @foreach($products as $p)
                                                <option value="{{ $p->id }}" data-price="{{ $p->buy_price ?? 0 }}" data-stock="{{ $p->stock }}" data-name="{{ $p->name }}">
                                                    {{ $p->name }} (Stok: {{ $p->stock }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="flex items-center gap-2">
                                        <div class="w-24">
                                            <input type="number" name="quantities[]" value="1" min="1" required 
                                                   class="quantity-input w-full px-2 py-1 rounded-lg border border-slate-200 text-xs font-bold text-center text-slate-800 bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#178BC1]/20">
                                        </div>
                                        <span class="text-[11px] text-slate-400">Unit</span>
                                        <button type="button" class="remove-row ml-auto p-1 text-slate-400 hover:text-rose-500 rounded transition-colors" disabled>
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                                <line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <button type="button" id="add-row" 
                                class="w-full py-2.5 border-2 border-dashed border-slate-300 hover:border-[#178BC1] text-slate-600 hover:text-[#178BC1] rounded-xl font-bold text-xs transition-colors flex items-center justify-center gap-1.5 bg-white">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                <line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line>
                            </svg>
                            <span>Tambah Barang Lain</span>
                        </button>
                    </div>

                </div>

                <div class="pt-6 border-t border-slate-200 flex justify-end gap-3">
                    <a href="{{ route('pos.bundles.index') }}" 
                       class="px-4 py-2.5 text-xs font-bold text-slate-600 border border-slate-200 rounded-xl hover:bg-slate-50 transition-colors">
                        Batal
                    </a>
                    <button type="submit" 
                            class="inline-flex items-center gap-2 bg-[#178BC1] hover:bg-[#126da3] text-white px-6 py-2.5 rounded-xl font-bold text-xs shadow-sm transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                            <polyline points="17 21 17 13 7 13 7 21"></polyline>
                        </svg>
                        <span>Simpan Racikan Paket</span>
                    </button>
                </div>

            </form>
        </div>

    </div>

    <!-- CALCULATION SCRIPT -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let rowCounter = 1;
            
            function calculateCostAndProfit() {
                let totalCost = 0;
                const rows = document.querySelectorAll('.row-item');
                
                rows.forEach((row) => {
                    const select = row.querySelector('.product-select');
                    const quantityInput = row.querySelector('.quantity-input');
                    const selectedOption = select.options[select.selectedIndex];
                    
                    if (selectedOption && selectedOption.value) {
                        const buyPrice = parseFloat(selectedOption.getAttribute('data-price')) || 0;
                        const quantity = parseInt(quantityInput.value) || 1;
                        totalCost += (buyPrice * quantity);
                    }
                });
                
                document.getElementById('itemCount').textContent = `${rows.length} Item`;
                document.getElementById('totalCost').textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(totalCost);
                
                const sellingPriceInput = document.querySelector('input[name="price"]');
                const sellingPrice = parseFloat(sellingPriceInput.value) || 0;
                document.getElementById('sellingPrice').textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(sellingPrice);
                
                const profit = sellingPrice - totalCost;
                const profitElement = document.getElementById('profitEstimation');
                profitElement.textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(profit);
                if (profit >= 0) {
                    profitElement.className = 'font-black text-sm text-emerald-700';
                } else {
                    profitElement.className = 'font-black text-sm text-rose-600';
                }
            }
            
            function updateRowEventListeners(row) {
                const select = row.querySelector('.product-select');
                const quantityInput = row.querySelector('.quantity-input');
                const removeBtn = row.querySelector('.remove-row');
                
                select.addEventListener('change', calculateCostAndProfit);
                quantityInput.addEventListener('input', calculateCostAndProfit);
                
                removeBtn.addEventListener('click', function() {
                    if (document.querySelectorAll('.row-item').length > 1) {
                        row.remove();
                        renumberRows();
                        calculateCostAndProfit();
                    }
                });
            }

            function renumberRows() {
                document.querySelectorAll('.row-item').forEach((row, idx) => {
                    row.querySelector('.row-number').textContent = idx + 1;
                    const removeBtn = row.querySelector('.remove-row');
                    removeBtn.disabled = (document.querySelectorAll('.row-item').length === 1);
                });
            }
            
            updateRowEventListeners(document.querySelector('.row-item'));
            document.querySelector('input[name="price"]').addEventListener('input', calculateCostAndProfit);
            
            document.getElementById('add-row').addEventListener('click', function() {
                const container = document.getElementById('items-container');
                const firstRow = container.querySelector('.row-item');
                const newRow = firstRow.cloneNode(true);
                
                newRow.querySelector('.product-select').value = '';
                newRow.querySelector('.quantity-input').value = '1';
                newRow.querySelector('.remove-row').disabled = false;
                
                updateRowEventListeners(newRow);
                container.appendChild(newRow);
                renumberRows();
                calculateCostAndProfit();
            });
        });
    </script>
</x-app-layout>
