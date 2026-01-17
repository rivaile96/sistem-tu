<x-app-layout>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <div class="bg-slate-50 min-h-screen pb-12">
        <div class="bg-white border-b border-gray-200 px-6 py-8 shadow-sm"></div>
            
        <div class="bg-white shadow-xl sm:rounded-2xl p-8 border border-gray-100 relative overflow-hidden">
            <div class="absolute top-0 right-0 -mt-4 -mr-4 w-32 h-32 bg-blue-50 rounded-full blur-2xl opacity-50"></div>
            
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-10 relative z-10 gap-4">
                <div>
                    <h2 class="text-3xl font-extrabold text-gray-800 tracking-tight">📝 Generator Tagihan</h2>
                    <p class="text-sm text-gray-500 mt-1">Buat tagihan SPP Bulanan atau Tagihan Barang/Jasa dengan mudah.</p>
                </div>
                <a href="{{ route('bills.index') }}" class="group flex items-center gap-2 text-gray-500 hover:text-[#0284c7] font-bold text-sm transition bg-gray-50 hover:bg-blue-50 px-4 py-2 rounded-lg border border-gray-200 hover:border-blue-200">
                    <svg class="w-4 h-4 group-hover:-translate-x-1 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                    Kembali
                </a>
            </div>

            <form action="{{ route('bills.store') }}" method="POST" id="billForm">
                @csrf
                
                <div class="bg-blue-50/50 p-6 rounded-xl border border-blue-100 mb-8">
                    <div class="flex items-center gap-2 mb-4">
                        <div class="p-1.5 bg-blue-100 rounded-lg text-[#0284c7]">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        </div>
                        <h3 class="font-bold text-gray-800">Target Penerima Tagihan</h3>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                        <label class="cursor-pointer relative">
                            <input type="radio" name="target_type" value="student" class="peer sr-only" checked onclick="toggleTarget('student')">
                            <div class="p-4 bg-white border border-gray-200 rounded-xl hover:shadow-md transition peer-checked:border-[#0284c7] peer-checked:ring-2 peer-checked:ring-blue-200 peer-checked:bg-white">
                                <span class="font-bold text-gray-700 flex items-center gap-2">
                                    <span class="w-4 h-4 rounded-full border border-gray-300 peer-checked:bg-[#0284c7] peer-checked:border-[#0284c7] block"></span>
                                    Per Siswa
                                </span>
                                <p class="text-xs text-gray-400 mt-1 pl-6">Kasus khusus (Denda/Susulan)</p>
                            </div>
                        </label>

                        <label class="cursor-pointer relative">
                            <input type="radio" name="target_type" value="class" class="peer sr-only" onclick="toggleTarget('class')">
                            <div class="p-4 bg-white border border-gray-200 rounded-xl hover:shadow-md transition peer-checked:border-[#0284c7] peer-checked:ring-2 peer-checked:ring-blue-200 peer-checked:bg-white">
                                <span class="font-bold text-gray-700 flex items-center gap-2">
                                    <span class="w-4 h-4 rounded-full border border-gray-300 peer-checked:bg-[#0284c7] peer-checked:border-[#0284c7] block"></span>
                                    Satu Kelas
                                </span>
                                <p class="text-xs text-gray-400 mt-1 pl-6">Tagihan rutin (SPP/Gedung)</p>
                            </div>
                        </label>

                        <label class="cursor-pointer relative">
                            <input type="radio" name="target_type" value="all" class="peer sr-only" onclick="toggleTarget('all')">
                            <div class="p-4 bg-white border border-gray-200 rounded-xl hover:shadow-md transition peer-checked:border-[#0284c7] peer-checked:ring-2 peer-checked:ring-blue-200 peer-checked:bg-white">
                                <span class="font-bold text-gray-700 flex items-center gap-2">
                                    <span class="w-4 h-4 rounded-full border border-gray-300 peer-checked:bg-[#0284c7] peer-checked:border-[#0284c7] block"></span>
                                    Semua Siswa
                                </span>
                                <p class="text-xs text-gray-400 mt-1 pl-6">Massal satu sekolah</p>
                            </div>
                        </label>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-1 gap-6">
                        <div id="input-student" class="w-full">
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Pilih Nama Siswa</label>
                            <select name="student_id" class="w-full rounded-lg border-gray-300 focus:ring-[#0284c7] focus:border-[#0284c7] shadow-sm">
                                <option value="">-- Cari Nama Siswa --</option>
                                @foreach($students as $student)
                                    <option value="{{ $student->id }}">{{ $student->name }} - {{ $student->class_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div id="input-class" class="w-full hidden">
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Pilih Kelas</label>
                            <select name="class_name" class="w-full rounded-lg border-gray-300 focus:ring-[#0284c7] focus:border-[#0284c7] shadow-sm">
                                <option value="">-- Pilih Kelas --</option>
                                @foreach($classes as $cls)
                                    <option value="{{ $cls }}">{{ $cls }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="mb-8">
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Jenis Tagihan</label>
                    <select name="type" id="typeSelector" class="w-full rounded-lg border-gray-300 font-bold text-gray-800 shadow-sm focus:ring-[#0284c7] focus:border-[#0284c7] text-lg py-3" onchange="toggleFormType()">
                        <option value="SPP">📅 SPP Bulanan (Rutin)</option>
                        <option value="GEDUNG">🏢 Uang Gedung</option>
                        <option value="DAFTAR_ULANG">📚 Daftar Ulang</option>
                        <option value="LAINNYA">⚡ Lainnya (Denda/Barang)</option>
                    </select>
                    <p class="text-xs text-gray-500 mt-2 ml-1">*Pilih "SPP Bulanan" untuk mengaktifkan pengaturan tanggal & bulan.</p>
                </div>

                <div id="form-spp" class="bg-blue-50 p-8 rounded-xl border border-blue-200 mb-8 relative overflow-hidden">
                    <div class="absolute top-0 right-0 -mt-2 -mr-2 w-24 h-24 bg-blue-200 rounded-full opacity-20"></div>
                    
                    <div class="flex items-center gap-3 mb-6 relative z-10">
                        <div class="p-2 bg-[#0284c7] text-white rounded-lg shadow-lg shadow-blue-200">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-blue-900 text-lg">Pengaturan Periode SPP</h3>
                            <p class="text-xs text-blue-600">Tentukan bulan tagihan dan nominal harga.</p>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6 relative z-10">
                        <div>
                            <label class="block text-xs font-bold text-blue-700 uppercase mb-2">Bulan Tagihan</label>
                            <select name="spp_month" class="w-full rounded-lg border-blue-300 focus:border-[#0284c7] focus:ring-[#0284c7] bg-white shadow-sm">
                                @foreach(range(1,12) as $m)
                                    <option value="{{ $m }}" {{ date('n') == $m ? 'selected' : '' }}>
                                        {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-blue-700 uppercase mb-2">Tahun Tagihan</label>
                            <select name="spp_year" class="w-full rounded-lg border-blue-300 focus:border-[#0284c7] focus:ring-[#0284c7] bg-white shadow-sm">
                                <option value="{{ date('Y') }}">{{ date('Y') }}</option>
                                <option value="{{ date('Y')+1 }}">{{ date('Y')+1 }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-blue-700 uppercase mb-2">Nominal SPP (Rp)</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 font-bold sm:text-sm">Rp</span>
                                </div>
                                <input type="number" name="spp_amount" placeholder="0" class="w-full rounded-lg border-blue-300 focus:border-[#0284c7] focus:ring-[#0284c7] pl-10 font-bold text-gray-800 shadow-sm text-lg">
                            </div>
                            <p class="text-[10px] text-blue-600 mt-1">*Anda bisa input harga berbeda untuk setiap kelas.</p>
                        </div>
                    </div>

                    <div class="flex items-start gap-3 bg-white/50 p-4 rounded-lg border border-blue-100">
                        <svg class="w-5 h-5 text-[#0284c7] mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <p class="text-sm text-blue-800">
                            <b>Sistem Otomatis:</b> Judul tagihan akan digenerate menjadi (contoh) <b>"SPP Agustus 2026"</b>. Tanggal jatuh tempo otomatis diset ke <b>tanggal 10</b> bulan tersebut untuk keperluan notifikasi.
                        </p>
                    </div>
                </div>

                <div id="form-regular" class="hidden">
                    <div class="mb-8">
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Judul Tagihan</label>
                        <input type="text" name="name" placeholder="Contoh: Pembelian Seragam Lengkap" class="w-full rounded-lg border-gray-300 focus:ring-[#0284c7] focus:border-[#0284c7] shadow-sm">
                    </div>

                    <div class="mb-6">
                        <div class="flex items-center gap-2 mb-4">
                            <div class="p-1.5 bg-gray-100 rounded-lg text-gray-600">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                            </div>
                            <h3 class="font-bold text-gray-800">Rincian Item (Invoice Detail)</h3>
                        </div>
                        
                        <div class="overflow-hidden border border-gray-200 rounded-xl shadow-sm">
                            <table class="w-full text-sm text-left">
                                <thead class="bg-gray-100 text-gray-700 font-bold uppercase text-xs">
                                    <tr>
                                        <th class="px-4 py-3 w-1/4">Tipe Item</th>
                                        <th class="px-4 py-3 w-1/3">Nama Item / Paket</th>
                                        <th class="px-4 py-3 w-20 text-center">Qty</th>
                                        <th class="px-4 py-3 w-1/4 text-right">Harga (Rp)</th>
                                        <th class="px-4 py-3 w-10"></th>
                                    </tr>
                                </thead>
                                <tbody id="items-container">
                                    <tr class="item-row border-b border-gray-100 bg-white hover:bg-gray-50 transition">
                                        <td class="px-4 py-3 align-top">
                                            <select class="type-selector w-full text-sm rounded-lg border-gray-300 focus:border-[#0284c7] py-2">
                                                <option value="manual">Input Manual</option>
                                                <option value="bundle">📦 Paket POS</option>
                                            </select>
                                        </td>
                                        <td class="px-4 py-3 align-top">
                                            <input type="text" name="item_names[]" placeholder="Nama Item / Keterangan" class="input-name w-full text-sm rounded-lg border-gray-300 py-2 focus:ring-[#0284c7] focus:border-[#0284c7]">
                                            <input type="hidden" name="item_bundle_ids[]" class="input-bundle-id">
                                            
                                            <select class="select-bundle w-full text-sm rounded-lg border-gray-300 py-2 hidden focus:ring-[#0284c7] focus:border-[#0284c7]">
                                                <option value="">-- Pilih Paket --</option>
                                                @foreach($bundles as $b)
                                                    <option value="{{ $b->id }}" data-price="{{ $b->price }}" data-name="{{ $b->name }}">
                                                        {{ $b->name }} (Rp {{ number_format($b->price, 0,',','.') }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td class="px-4 py-3 align-top">
                                            <input type="number" name="item_qtys[]" value="1" min="1" class="w-full text-center text-sm rounded-lg border-gray-300 py-2 focus:ring-[#0284c7] focus:border-[#0284c7]">
                                        </td>
                                        <td class="px-4 py-3 align-top">
                                            <input type="number" name="item_prices[]" placeholder="0" class="input-price w-full text-right text-sm rounded-lg border-gray-300 py-2 font-bold text-gray-700 focus:ring-[#0284c7] focus:border-[#0284c7]">
                                        </td>
                                        <td class="px-4 py-3 align-top text-center">
                                            <button type="button" class="text-red-400 hover:text-red-600 p-2 rounded-full hover:bg-red-50 transition remove-row">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                                <tfoot class="bg-gray-50">
                                    <tr>
                                        <td colspan="5" class="px-4 py-3">
                                            <button type="button" id="add-row" class="text-[#0284c7] font-bold text-sm flex items-center gap-2 hover:bg-blue-100 px-3 py-1.5 rounded-lg transition">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                                Tambah Baris Item
                                            </button>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="3" class="px-4 py-4 text-right font-bold text-gray-500 uppercase text-xs tracking-wider">Estimasi Total Per Siswa:</td>
                                        <td class="px-4 py-4 text-right font-extrabold text-2xl text-[#0284c7]" id="grand-total">Rp 0</td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end pt-6 border-t border-gray-100">
                    <button type="submit" class="bg-gradient-to-r from-[#0284c7] to-[#0369a1] hover:from-[#0369a1] hover:to-[#0284c7] text-white font-bold py-3 px-8 rounded-xl shadow-lg shadow-blue-200 transition transform hover:-translate-y-1 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                        Proses Tagihan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // 1. Logic Toggle Target (Siswa vs Kelas vs Semua)
        function toggleTarget(type) {
            const inputStudent = document.getElementById('input-student');
            const inputClass = document.getElementById('input-class');

            if (type === 'student') {
                inputStudent.classList.remove('hidden');
                inputClass.classList.add('hidden');
            } else if (type === 'class') {
                inputStudent.classList.add('hidden');
                inputClass.classList.remove('hidden');
            } else { // All
                inputStudent.classList.add('hidden');
                inputClass.classList.add('hidden');
            }
        }

        // 2. Logic Toggle Tipe (SPP vs Regular)
        function toggleFormType() {
            const type = document.getElementById('typeSelector').value;
            const formSpp = document.getElementById('form-spp');
            const formRegular = document.getElementById('form-regular');
            
            // Ambil semua input di dalam form masing-masing
            const sppInputs = formSpp.querySelectorAll('input, select');
            const regularInputs = formRegular.querySelectorAll('input, select');

            if(type === 'SPP') {
                // Tampilkan Form SPP
                formSpp.classList.remove('hidden');
                formRegular.classList.add('hidden');
                
                // Aktifkan input SPP, Matikan input Regular
                // (Penting agar validasi controller tidak error)
                sppInputs.forEach(el => el.disabled = false);
                regularInputs.forEach(el => el.disabled = true);
            } else {
                // Tampilkan Form Regular
                formSpp.classList.add('hidden');
                formRegular.classList.remove('hidden');
                
                // Aktifkan input Regular, Matikan input SPP
                sppInputs.forEach(el => el.disabled = true);
                regularInputs.forEach(el => el.disabled = false);
            }
        }

        // 3. Logic Table Item (Manual vs Bundle)
        document.addEventListener('DOMContentLoaded', function() {
            
            // Jalankan toggle type saat load halaman
            toggleFormType();

            function updateRowLogic(row) {
                const typeSelector = row.querySelector('.type-selector');
                const inputName = row.querySelector('.input-name');
                const selectBundle = row.querySelector('.select-bundle');
                const inputPrice = row.querySelector('.input-price');
                const inputBundleId = row.querySelector('.input-bundle-id');

                // A. Handle Perubahan Tipe (Manual vs Paket)
                typeSelector.addEventListener('change', function() {
                    if (this.value === 'bundle') {
                        inputName.classList.add('hidden');
                        selectBundle.classList.remove('hidden');
                        inputPrice.setAttribute('readonly', true);
                        inputPrice.classList.add('bg-gray-100', 'cursor-not-allowed');
                    } else {
                        inputName.classList.remove('hidden');
                        selectBundle.classList.add('hidden');
                        inputPrice.removeAttribute('readonly');
                        inputPrice.classList.remove('bg-gray-100', 'cursor-not-allowed');
                        inputPrice.value = '';
                        inputName.value = '';
                        inputBundleId.value = '';
                    }
                    calculateTotal();
                });

                // B. Handle Pilihan Paket
                selectBundle.addEventListener('change', function() {
                    const selectedOption = this.options[this.selectedIndex];
                    const price = selectedOption.getAttribute('data-price');
                    const name = selectedOption.getAttribute('data-name');
                    
                    if (price) {
                        inputPrice.value = price;
                        inputName.value = name; 
                        inputBundleId.value = this.value; 
                        calculateTotal();
                    }
                });

                // C. Handle Perubahan Harga Manual
                inputPrice.addEventListener('input', calculateTotal);
                row.querySelector('input[name="item_qtys[]"]').addEventListener('input', calculateTotal);
                
                // D. Handle Hapus Baris dengan SWEETALERT2
                row.querySelector('.remove-row').addEventListener('click', function() {
                    const rowCount = document.querySelectorAll('.item-row').length;
                    
                    if (rowCount <= 1) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Tidak Bisa Dihapus',
                            text: 'Minimal harus ada satu item dalam tagihan!',
                            confirmButtonColor: '#0284c7'
                        });
                        return;
                    }

                    Swal.fire({
                        title: 'Hapus Item?',
                        text: "Item ini akan dihapus dari daftar tagihan.",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#ef4444',
                        cancelButtonColor: '#9ca3af',
                        confirmButtonText: 'Ya, Hapus!',
                        cancelButtonText: 'Batal'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            row.remove(); 
                            calculateTotal(); 
                            
                            const Toast = Swal.mixin({
                                toast: true, position: 'top-end',
                                showConfirmButton: false, timer: 1500, timerProgressBar: true
                            });
                            Toast.fire({ icon: 'success', title: 'Item dihapus' });
                        }
                    });
                });
            }

            // Hitung Total Semua Baris
            function calculateTotal() {
                let total = 0;
                document.querySelectorAll('.item-row').forEach(row => {
                    const price = parseFloat(row.querySelector('input[name="item_prices[]"]').value) || 0;
                    const qty = parseFloat(row.querySelector('input[name="item_qtys[]"]').value) || 1;
                    total += (price * qty);
                });
                document.getElementById('grand-total').innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(total);
            }

            // Init Logic untuk baris pertama
            updateRowLogic(document.querySelector('.item-row'));

            // Tambah Baris Baru
            document.getElementById('add-row').addEventListener('click', function() {
                const firstRow = document.querySelector('.item-row');
                const newRow = firstRow.cloneNode(true);
                
                // Reset Value di baris baru
                newRow.querySelector('.input-name').value = '';
                newRow.querySelector('.input-price').value = '';
                newRow.querySelector('input[name="item_qtys[]"]').value = '1';
                newRow.querySelector('select.type-selector').value = 'manual';
                
                // Reset tampilan input manual vs bundle
                newRow.querySelector('.select-bundle').classList.add('hidden');
                newRow.querySelector('.input-name').classList.remove('hidden');
                newRow.querySelector('.input-price').removeAttribute('readonly');
                newRow.querySelector('.input-price').classList.remove('bg-gray-100', 'cursor-not-allowed');

                document.getElementById('items-container').appendChild(newRow);
                updateRowLogic(newRow); // Pasang event listener ke baris baru
            });
        });
    </script>
</x-app-layout>