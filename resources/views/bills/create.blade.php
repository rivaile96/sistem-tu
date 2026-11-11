<x-app-layout>
    <x-slot name="header">
        Generator Tagihan
    </x-slot>

    <div class="space-y-6">
        
        <!-- HEADER & BACK BUTTON -->
        <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
            <div>
                <h1 class="flex items-center gap-3 text-2xl font-black text-slate-800">
                    <div class="bg-[#178BC1] p-2.5 rounded-xl text-white shadow-sm shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                            <line x1="12" y1="18" x2="12" y2="12"></line>
                            <line x1="9" y1="15" x2="15" y2="15"></line>
                        </svg>
                    </div>
                    Generator Tagihan Siswa
                </h1>
                <p class="mt-1 text-xs text-slate-500 font-medium">
                    Buat tagihan biaya pendidikan, seragam, buku, atau tagihan insidental untuk siswa atau rombel.
                </p>
            </div>
            
            <a href="{{ route('bills.index') }}" 
               class="inline-flex items-center gap-2 bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 px-4 py-2.5 rounded-xl font-bold text-xs shadow-sm transition-all">
                <svg class="w-4 h-4 text-[#178BC1]" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
                <span>Kembali ke Daftar Tagihan</span>
            </a>
        </div>

        <!-- FORM CARD (SPMB MATCHING SOLID) -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 sm:p-8">
            <form action="{{ route('bills.store') }}" method="POST" id="billForm">
                @csrf
                
                <!-- SECTION 1: TARGET PENERIMA TAGIHAN -->
                <div class="bg-slate-50 p-6 rounded-2xl border border-slate-200 mb-8">
                    <div class="flex items-center gap-2 mb-4">
                        <div class="p-1.5 bg-blue-50 text-[#178BC1] rounded-lg border border-blue-100">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                <circle cx="9" cy="7" r="4"></circle>
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                            </svg>
                        </div>
                        <h3 class="font-bold text-sm text-slate-800">Target Penerima Tagihan</h3>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        
                        <!-- Per Siswa -->
                        <label class="cursor-pointer relative">
                            <input type="radio" name="target_type" value="student" class="peer sr-only" checked onclick="toggleTarget('student')">
                            <div class="p-4 bg-white border border-slate-200 rounded-xl hover:border-slate-300 transition-all peer-checked:border-[#178BC1] peer-checked:ring-2 peer-checked:ring-[#178BC1]/20">
                                <span class="font-bold text-xs text-slate-800 flex items-center gap-2">
                                    <span class="w-3.5 h-3.5 rounded-full border border-slate-300 peer-checked:bg-[#178BC1] peer-checked:border-[#178BC1] block"></span>
                                    Per Siswa (Individu)
                                </span>
                                <p class="text-[11px] text-slate-400 mt-1 pl-5">Tagihan khusus untuk satu orang siswa</p>
                            </div>
                        </label>

                        <!-- Satu Kelas -->
                        <label class="cursor-pointer relative">
                            <input type="radio" name="target_type" value="class" class="peer sr-only" onclick="toggleTarget('class')">
                            <div class="p-4 bg-white border border-slate-200 rounded-xl hover:border-slate-300 transition-all peer-checked:border-[#178BC1] peer-checked:ring-2 peer-checked:ring-[#178BC1]/20">
                                <span class="font-bold text-xs text-slate-800 flex items-center gap-2">
                                    <span class="w-3.5 h-3.5 rounded-full border border-slate-300 peer-checked:bg-[#178BC1] peer-checked:border-[#178BC1] block"></span>
                                    Satu Rombongan Belajar
                                </span>
                                <p class="text-[11px] text-slate-400 mt-1 pl-5">Tagihan massal untuk satu rombel kelas</p>
                            </div>
                        </label>

                        <!-- Seluruh Siswa -->
                        <label class="cursor-pointer relative">
                            <input type="radio" name="target_type" value="all" class="peer sr-only" onclick="toggleTarget('all')">
                            <div class="p-4 bg-white border border-slate-200 rounded-xl hover:border-slate-300 transition-all peer-checked:border-[#178BC1] peer-checked:ring-2 peer-checked:ring-[#178BC1]/20">
                                <span class="font-bold text-xs text-slate-800 flex items-center gap-2">
                                    <span class="w-3.5 h-3.5 rounded-full border border-slate-300 peer-checked:bg-[#178BC1] peer-checked:border-[#178BC1] block"></span>
                                    Seluruh Siswa Aktif
                                </span>
                                <p class="text-[11px] text-slate-400 mt-1 pl-5">Tagihan serentak ke semua siswa terdaftar</p>
                            </div>
                        </label>
                    </div>

                    <!-- SELECTOR PER SISWA -->
                    <div id="target-student" class="space-y-1.5">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                            Pilih Siswa <span class="text-rose-500">*</span>
                        </label>
                        <select name="student_id" id="student_id" 
                                class="w-full px-3 py-2 text-xs font-medium text-slate-800 bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#178BC1]/20 focus:border-[#178BC1] transition-all cursor-pointer">
                            <option value="">-- Pilih Siswa --</option>
                            @foreach($students as $s)
                                <option value="{{ $s->id }}">
                                    {{ $s->name }} (NISN: {{ $s->nisn ?? ($s->nis ?? '-') }} - Kelas {{ $s->class ?? ($s->class_name ?? '-') }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- SELECTOR KELAS -->
                    <div id="target-class" class="hidden space-y-1.5">
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                            Pilih Rombongan Belajar <span class="text-rose-500">*</span>
                        </label>
                        <select name="class_name" id="class_name" 
                                class="w-full px-3 py-2 text-xs font-medium text-slate-800 bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#178BC1]/20 focus:border-[#178BC1] transition-all cursor-pointer">
                            <option value="">-- Pilih Kelas --</option>
                            @foreach($classes as $c)
                                <option value="{{ $c }}">Kelas {{ $c }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- INFO ALL STUDENTS -->
                    <div id="target-all" class="hidden p-3.5 bg-blue-50 border border-blue-200 rounded-xl text-xs text-[#178BC1] font-medium flex items-center gap-2">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="16" x2="12" y2="12"></line>
                            <line x1="12" y1="8" x2="12.01" y2="8"></line>
                        </svg>
                        <span>Tagihan akan dibuat serentak untuk seluruh siswa yang berstatus aktif di database.</span>
                    </div>

                    <!-- JATUH TEMPO -->
                    <div class="mt-4 pt-4 border-t border-slate-200 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Batas Akhir Pembayaran (Jatuh Tempo) <span class="text-rose-500">*</span>
                            </label>
                            <input type="date" 
                                   name="due_date" 
                                   value="{{ date('Y-m-d', strtotime('+30 days')) }}" 
                                   required 
                                   class="w-full px-3 py-2 text-xs font-medium text-slate-800 bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#178BC1]/20 focus:border-[#178BC1] transition-all" />
                        </div>
                    </div>
                </div>

                <!-- SECTION 2: RINCIAN ITEM TAGIHAN -->
                <div class="space-y-4">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <div class="p-1.5 bg-blue-50 text-[#178BC1] rounded-lg border border-blue-100">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                    <line x1="8" y1="6" x2="21" y2="6"></line>
                                    <line x1="8" y1="12" x2="21" y2="12"></line>
                                    <line x1="8" y1="18" x2="21" y2="18"></line>
                                    <line x1="3" y1="6" x2="3.01" y2="6"></line>
                                    <line x1="3" y1="12" x2="3.01" y2="12"></line>
                                    <line x1="3" y1="18" x2="3.01" y2="18"></line>
                                </svg>
                            </div>
                            <h3 class="font-bold text-sm text-slate-800">Daftar Item & Biaya</h3>
                        </div>

                        <button type="button" 
                                id="add-row" 
                                class="inline-flex items-center gap-1.5 bg-white hover:bg-slate-50 text-[#178BC1] border border-[#178BC1]/30 hover:border-[#178BC1] px-3.5 py-1.5 rounded-xl font-bold text-xs shadow-sm transition-all">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                <line x1="12" y1="5" x2="12" y2="19"></line>
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                            </svg>
                            <span>Tambah Item</span>
                        </button>
                    </div>

                    <div id="items-container" class="space-y-3">
                        <!-- BARIS PERTAMA (TEMPLATE) -->
                        <div class="item-row p-4 bg-slate-50 rounded-2xl border border-slate-200 flex flex-col md:flex-row gap-3 items-start md:items-center">
                            
                            <!-- Mode (Manual / Bundle) -->
                            <div class="w-full md:w-36 shrink-0">
                                <label class="block md:hidden text-[10px] font-bold text-slate-400 uppercase mb-1">Tipe Item</label>
                                <select name="item_types[]" 
                                        class="type-selector w-full px-3 py-2 text-xs font-semibold text-slate-700 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-[#178BC1]/20 focus:border-[#178BC1] transition-all cursor-pointer">
                                    <option value="manual">Item Manual</option>
                                    <option value="bundle">Paket Bundel</option>
                                </select>
                            </div>

                            <!-- Nama Item / Pilih Paket -->
                            <div class="flex-1 w-full relative">
                                <label class="block md:hidden text-[10px] font-bold text-slate-400 uppercase mb-1">Nama Tagihan</label>
                                
                                <input type="text" 
                                       name="item_names[]" 
                                       placeholder="Contoh: SPP Bulan November 2026 / Uang Seragam" 
                                       required
                                       class="input-name w-full px-3 py-2 text-xs font-medium text-slate-800 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-[#178BC1]/20 focus:border-[#178BC1] transition-all" />

                                <select name="bundle_ids[]" 
                                        class="select-bundle hidden w-full px-3 py-2 text-xs font-semibold text-slate-700 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-[#178BC1]/20 focus:border-[#178BC1] transition-all cursor-pointer">
                                    <option value="">-- Pilih Paket Bundel POS --</option>
                                    @foreach($bundles as $b)
                                        <option value="{{ $b->id }}" data-price="{{ $b->price }}" data-name="{{ $b->name }}">
                                            {{ $b->name }} (Rp {{ number_format($b->price, 0, ',', '.') }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Harga / Nominal -->
                            <div class="w-full md:w-44 shrink-0">
                                <label class="block md:hidden text-[10px] font-bold text-slate-400 uppercase mb-1">Nominal (Rp)</label>
                                <input type="number" 
                                       name="item_prices[]" 
                                       placeholder="Nominal Rp" 
                                       required 
                                       min="0"
                                       class="input-price w-full px-3 py-2 text-xs font-bold text-slate-800 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-[#178BC1]/20 focus:border-[#178BC1] transition-all" />
                            </div>

                            <!-- Qty -->
                            <div class="w-full md:w-20 shrink-0">
                                <label class="block md:hidden text-[10px] font-bold text-slate-400 uppercase mb-1">Qty</label>
                                <input type="number" 
                                       name="item_qtys[]" 
                                       value="1" 
                                       min="1" 
                                       required
                                       class="input-qty w-full px-3 py-2 text-xs font-bold text-slate-800 bg-white border border-slate-200 rounded-xl text-center focus:ring-2 focus:ring-[#178BC1]/20 focus:border-[#178BC1] transition-all" />
                            </div>

                            <!-- Remove Button -->
                            <div class="shrink-0 self-end md:self-center">
                                <button type="button" 
                                        class="remove-row p-2 text-rose-500 hover:text-rose-700 hover:bg-rose-50 rounded-xl border border-transparent hover:border-rose-200 transition-colors"
                                        title="Hapus Baris">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                        <polyline points="3 6 5 6 21 6"></polyline>
                                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                                    </svg>
                                </button>
                            </div>

                        </div>
                    </div>

                    <!-- TOTAL SUMMARY & SUBMIT -->
                    <div class="p-6 bg-slate-50 rounded-2xl border border-slate-200 flex flex-col sm:flex-row items-center justify-between gap-4 mt-6">
                        <div>
                            <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Estimasi Tagihan Per Siswa</p>
                            <h2 id="grand-total" class="text-2xl font-black text-slate-800 mt-1">Rp 0</h2>
                        </div>

                        <div class="flex items-center gap-3 w-full sm:w-auto">
                            <a href="{{ route('bills.index') }}" 
                               class="flex-1 sm:flex-none text-center px-4 py-2.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold transition-all">
                                Batal
                            </a>
                            <button type="submit" 
                                    class="flex-1 sm:flex-none flex items-center justify-center gap-2 bg-[#178BC1] hover:bg-[#126da3] text-white px-6 py-2.5 rounded-xl font-bold text-xs shadow-sm transition-all">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                    <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                                    <polyline points="17 21 17 13 7 13 7 21"></polyline>
                                    <polyline points="7 3 7 8 15 8"></polyline>
                                </svg>
                                <span>Terbitkan Tagihan</span>
                            </button>
                        </div>
                    </div>

                </div>

            </form>
        </div>

    </div>

    <!-- SCRIPT FORM HANDLER -->
    <script>
        function toggleTarget(type) {
            document.getElementById('target-student').classList.add('hidden');
            document.getElementById('target-class').classList.add('hidden');
            document.getElementById('target-all').classList.add('hidden');

            document.getElementById('student_id').required = false;
            document.getElementById('class_name').required = false;

            if (type === 'student') {
                document.getElementById('target-student').classList.remove('hidden');
                document.getElementById('student_id').required = true;
            } else if (type === 'class') {
                document.getElementById('target-class').classList.remove('hidden');
                document.getElementById('class_name').required = true;
            } else if (type === 'all') {
                document.getElementById('target-all').classList.remove('hidden');
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            
            function updateRowLogic(row) {
                const selectType = row.querySelector('.type-selector');
                const selectBundle = row.querySelector('.select-bundle');
                const inputName = row.querySelector('.input-name');
                const inputPrice = row.querySelector('.input-price');
                const inputQty = row.querySelector('.input-qty');

                selectType.addEventListener('change', function() {
                    if (this.value === 'bundle') {
                        selectBundle.classList.remove('hidden');
                        inputName.classList.add('hidden');
                        inputName.required = false;
                        selectBundle.required = true;
                        inputPrice.setAttribute('readonly', true);
                        inputPrice.classList.add('bg-slate-100', 'cursor-not-allowed');
                        selectBundle.value = '';
                        inputPrice.value = '';
                    } else {
                        selectBundle.classList.add('hidden');
                        inputName.classList.remove('hidden');
                        inputName.required = true;
                        selectBundle.required = false;
                        inputPrice.removeAttribute('readonly');
                        inputPrice.classList.remove('bg-slate-100', 'cursor-not-allowed');
                        inputPrice.value = '';
                        inputName.value = '';
                    }
                    calculateTotal();
                });

                selectBundle.addEventListener('change', function() {
                    const selected = this.options[this.selectedIndex];
                    const price = selected.getAttribute('data-price');
                    const name = selected.getAttribute('data-name');
                    
                    if (price) {
                        inputPrice.value = price;
                        inputName.value = name;
                        calculateTotal();
                    }
                });

                inputPrice.addEventListener('input', calculateTotal);
                inputQty.addEventListener('input', calculateTotal);

                row.querySelector('.remove-row').addEventListener('click', function() {
                    const rowCount = document.querySelectorAll('.item-row').length;
                    if (rowCount <= 1) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Tidak Bisa Dihapus',
                            text: 'Minimal harus ada 1 item dalam tagihan!',
                            confirmButtonColor: '#178BC1'
                        });
                        return;
                    }
                    row.remove();
                    calculateTotal();
                });
            }

            function calculateTotal() {
                let total = 0;
                document.querySelectorAll('.item-row').forEach(row => {
                    const price = parseFloat(row.querySelector('.input-price').value) || 0;
                    const qty = parseFloat(row.querySelector('.input-qty').value) || 1;
                    total += (price * qty);
                });
                document.getElementById('grand-total').innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(total);
            }

            updateRowLogic(document.querySelector('.item-row'));

            document.getElementById('add-row').addEventListener('click', function() {
                const firstRow = document.querySelector('.item-row');
                const newRow = firstRow.cloneNode(true);

                newRow.querySelector('.input-name').value = '';
                newRow.querySelector('.input-price').value = '';
                newRow.querySelector('.input-qty').value = '1';
                newRow.querySelector('.type-selector').value = 'manual';
                
                newRow.querySelector('.select-bundle').classList.add('hidden');
                newRow.querySelector('.input-name').classList.remove('hidden');
                newRow.querySelector('.input-price').removeAttribute('readonly');
                newRow.querySelector('.input-price').classList.remove('bg-slate-100', 'cursor-not-allowed');

                document.getElementById('items-container').appendChild(newRow);
                updateRowLogic(newRow);
            });
        });
    </script>
</x-app-layout>
