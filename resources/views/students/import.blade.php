<x-app-layout>
    <div class="mb-8">
        <!-- Header -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <div class="p-2 bg-gradient-to-br from-[#0284c7]/10 to-[#0ea5e9]/10 rounded-xl">
                        <svg class="w-7 h-7 text-[#0284c7]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"></path>
                        </svg>
                    </div>
                    <h1 class="text-3xl font-bold text-gray-900">Import Data Siswa</h1>
                </div>
                <p class="text-gray-600 ml-12">Upload file CSV untuk menambahkan data siswa secara massal.</p>
            </div>
            <a href="{{ route('students.index') }}"
               class="flex items-center gap-2 bg-gradient-to-r from-white to-gray-50 border border-gray-200 text-gray-700 px-5 py-3 rounded-xl hover:border-[#0284c7]/50 hover:shadow-md transition-all duration-300 font-medium shadow-sm">
                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Kembali ke Daftar Siswa
            </a>
        </div>

        <!-- Import Errors -->
        @if(session('import_errors') && count(session('import_errors')) > 0)
        <div class="bg-red-50 border border-red-200 rounded-xl px-5 py-4 mb-6">
            <div class="flex items-center gap-2 mb-3">
                <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span class="font-bold text-red-700">Import gagal pada beberapa baris:</span>
            </div>
            <div class="max-h-48 overflow-y-auto">
                <ul class="space-y-1">
                    @foreach(session('import_errors') as $importError)
                        <li class="flex items-start gap-2 text-sm text-red-600">
                            <svg class="w-4 h-4 text-red-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                            {{ $importError }}
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
        @endif

        <!-- Validation Errors -->
        @if($errors->any())
        <div class="bg-red-50 border border-red-200 rounded-xl px-5 py-4 mb-6">
            <div class="flex items-center gap-2 mb-2">
                <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span class="font-bold text-red-700">Terdapat kesalahan:</span>
            </div>
            <ul class="list-disc list-inside text-sm text-red-600 space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- Left: Instructions + Upload Form -->
            <div class="lg:col-span-2 space-y-6">

                <!-- Upload Form Card -->
                <div class="bg-gradient-to-br from-white to-gray-50 rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
                        <div class="flex items-center gap-2">
                            <div class="w-1 h-5 bg-[#0284c7] rounded-full"></div>
                            <h3 class="font-bold text-gray-900">Upload File CSV</h3>
                        </div>
                    </div>

                    <div class="p-6">
                        <form method="POST" action="{{ route('students.import.process') }}" enctype="multipart/form-data">
                            @csrf

                            <!-- Dropzone area -->
                            <div class="relative border-2 border-dashed border-gray-300 rounded-xl p-8 text-center hover:border-[#0284c7] transition-colors duration-300 group cursor-pointer"
                                 onclick="document.getElementById('csv-file-input').click()">
                                <input type="file"
                                       id="csv-file-input"
                                       name="file"
                                       accept=".csv,.txt"
                                       class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                                       onchange="updateFileName(this)">

                                <div id="upload-placeholder">
                                    <div class="w-16 h-16 mx-auto mb-4 bg-gradient-to-br from-[#0284c7]/10 to-[#0ea5e9]/10 rounded-full flex items-center justify-center group-hover:from-[#0284c7]/20 group-hover:to-[#0ea5e9]/20 transition-all duration-300">
                                        <svg class="w-8 h-8 text-[#0284c7]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                        </svg>
                                    </div>
                                    <p class="text-gray-700 font-semibold mb-1">Klik untuk pilih file atau seret ke sini</p>
                                    <p class="text-sm text-gray-500">Format yang didukung: <span class="font-mono text-[#0284c7]">.csv</span> atau <span class="font-mono text-[#0284c7]">.txt</span></p>
                                    <p class="text-xs text-gray-400 mt-1">Ukuran maksimal: 10MB</p>
                                </div>

                                <div id="upload-selected" class="hidden">
                                    <div class="w-16 h-16 mx-auto mb-4 bg-emerald-100 rounded-full flex items-center justify-center">
                                        <svg class="w-8 h-8 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                    <p id="selected-filename" class="text-gray-900 font-bold mb-1"></p>
                                    <p class="text-sm text-emerald-600">File siap diupload. Klik untuk ganti file.</p>
                                </div>
                            </div>

                            @error('file')
                                <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                            @enderror

                            <div class="flex flex-col sm:flex-row gap-3 mt-6">
                                <button type="submit"
                                        class="group relative flex items-center justify-center gap-2 flex-1 py-3 bg-gradient-to-r from-[#0284c7] to-[#0ea5e9] hover:from-[#027ab8] hover:to-[#0d93d7] text-white rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 font-bold overflow-hidden">
                                    <div class="absolute inset-0 bg-white/10 transform -translate-x-full group-hover:translate-x-0 transition-transform duration-700"></div>
                                    <svg class="w-5 h-5 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"></path>
                                    </svg>
                                    <span class="relative z-10">Proses Import</span>
                                </button>
                                <a href="{{ route('students.index') }}"
                                   class="flex items-center justify-center gap-2 px-6 py-3 bg-gradient-to-r from-gray-100 to-gray-200 text-gray-700 rounded-xl hover:from-gray-200 hover:to-gray-300 transition-all duration-300 font-medium">
                                    Batal
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Format Table Card -->
                <div class="bg-gradient-to-br from-white to-gray-50 rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
                        <div class="flex items-center gap-2">
                            <div class="w-1 h-5 bg-[#0284c7] rounded-full"></div>
                            <h3 class="font-bold text-gray-900">Format Kolom CSV</h3>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="bg-gradient-to-r from-gray-50/80 to-gray-100/80 text-gray-700 font-bold uppercase text-xs">
                                    <th class="px-4 py-3 text-left">#</th>
                                    <th class="px-4 py-3 text-left">Nama Kolom</th>
                                    <th class="px-4 py-3 text-left">Keterangan</th>
                                    <th class="px-4 py-3 text-center">Wajib</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr class="hover:bg-blue-50/50 transition-colors">
                                    <td class="px-4 py-3 text-gray-500 font-mono text-xs">1</td>
                                    <td class="px-4 py-3"><span class="font-mono bg-gray-100 text-[#0284c7] px-2 py-0.5 rounded text-xs font-bold">nis</span></td>
                                    <td class="px-4 py-3 text-gray-600">Nomor Induk Siswa</td>
                                    <td class="px-4 py-3 text-center"><span class="inline-block w-5 h-5 bg-red-100 text-red-600 rounded-full text-xs font-bold leading-5">✓</span></td>
                                </tr>
                                <tr class="hover:bg-blue-50/50 transition-colors">
                                    <td class="px-4 py-3 text-gray-500 font-mono text-xs">2</td>
                                    <td class="px-4 py-3"><span class="font-mono bg-gray-100 text-[#0284c7] px-2 py-0.5 rounded text-xs font-bold">nisn</span></td>
                                    <td class="px-4 py-3 text-gray-600">Nomor Induk Siswa Nasional</td>
                                    <td class="px-4 py-3 text-center"><span class="text-gray-400 text-xs">—</span></td>
                                </tr>
                                <tr class="hover:bg-blue-50/50 transition-colors">
                                    <td class="px-4 py-3 text-gray-500 font-mono text-xs">3</td>
                                    <td class="px-4 py-3"><span class="font-mono bg-gray-100 text-[#0284c7] px-2 py-0.5 rounded text-xs font-bold">name</span></td>
                                    <td class="px-4 py-3 text-gray-600">Nama lengkap siswa</td>
                                    <td class="px-4 py-3 text-center"><span class="inline-block w-5 h-5 bg-red-100 text-red-600 rounded-full text-xs font-bold leading-5">✓</span></td>
                                </tr>
                                <tr class="hover:bg-blue-50/50 transition-colors">
                                    <td class="px-4 py-3 text-gray-500 font-mono text-xs">4</td>
                                    <td class="px-4 py-3"><span class="font-mono bg-gray-100 text-[#0284c7] px-2 py-0.5 rounded text-xs font-bold">gender</span></td>
                                    <td class="px-4 py-3 text-gray-600">Jenis kelamin: <span class="font-mono font-bold">L</span> atau <span class="font-mono font-bold">P</span></td>
                                    <td class="px-4 py-3 text-center"><span class="text-gray-400 text-xs">—</span></td>
                                </tr>
                                <tr class="hover:bg-blue-50/50 transition-colors">
                                    <td class="px-4 py-3 text-gray-500 font-mono text-xs">5</td>
                                    <td class="px-4 py-3"><span class="font-mono bg-gray-100 text-[#0284c7] px-2 py-0.5 rounded text-xs font-bold">class_name</span></td>
                                    <td class="px-4 py-3 text-gray-600">Nama kelas, contoh: <span class="font-mono">X IPA 1</span></td>
                                    <td class="px-4 py-3 text-center"><span class="inline-block w-5 h-5 bg-red-100 text-red-600 rounded-full text-xs font-bold leading-5">✓</span></td>
                                </tr>
                                <tr class="hover:bg-blue-50/50 transition-colors">
                                    <td class="px-4 py-3 text-gray-500 font-mono text-xs">6</td>
                                    <td class="px-4 py-3"><span class="font-mono bg-gray-100 text-[#0284c7] px-2 py-0.5 rounded text-xs font-bold">birth_place</span></td>
                                    <td class="px-4 py-3 text-gray-600">Kota/kabupaten tempat lahir</td>
                                    <td class="px-4 py-3 text-center"><span class="text-gray-400 text-xs">—</span></td>
                                </tr>
                                <tr class="hover:bg-blue-50/50 transition-colors">
                                    <td class="px-4 py-3 text-gray-500 font-mono text-xs">7</td>
                                    <td class="px-4 py-3"><span class="font-mono bg-gray-100 text-[#0284c7] px-2 py-0.5 rounded text-xs font-bold">birth_date</span></td>
                                    <td class="px-4 py-3 text-gray-600">Format: <span class="font-mono font-bold">YYYY-MM-DD</span></td>
                                    <td class="px-4 py-3 text-center"><span class="text-gray-400 text-xs">—</span></td>
                                </tr>
                                <tr class="hover:bg-blue-50/50 transition-colors">
                                    <td class="px-4 py-3 text-gray-500 font-mono text-xs">8</td>
                                    <td class="px-4 py-3"><span class="font-mono bg-gray-100 text-[#0284c7] px-2 py-0.5 rounded text-xs font-bold">address</span></td>
                                    <td class="px-4 py-3 text-gray-600">Alamat lengkap siswa</td>
                                    <td class="px-4 py-3 text-center"><span class="text-gray-400 text-xs">—</span></td>
                                </tr>
                                <tr class="hover:bg-blue-50/50 transition-colors">
                                    <td class="px-4 py-3 text-gray-500 font-mono text-xs">9</td>
                                    <td class="px-4 py-3"><span class="font-mono bg-gray-100 text-[#0284c7] px-2 py-0.5 rounded text-xs font-bold">agama</span></td>
                                    <td class="px-4 py-3 text-gray-600">Islam / Kristen / Katolik / Hindu / Buddha / Konghucu</td>
                                    <td class="px-4 py-3 text-center"><span class="text-gray-400 text-xs">—</span></td>
                                </tr>
                                <tr class="hover:bg-blue-50/50 transition-colors">
                                    <td class="px-4 py-3 text-gray-500 font-mono text-xs">10</td>
                                    <td class="px-4 py-3"><span class="font-mono bg-gray-100 text-[#0284c7] px-2 py-0.5 rounded text-xs font-bold">tahun_masuk</span></td>
                                    <td class="px-4 py-3 text-gray-600">Tahun masuk, 4 digit: <span class="font-mono font-bold">2024</span></td>
                                    <td class="px-4 py-3 text-center"><span class="text-gray-400 text-xs">—</span></td>
                                </tr>
                                <tr class="hover:bg-blue-50/50 transition-colors">
                                    <td class="px-4 py-3 text-gray-500 font-mono text-xs">11</td>
                                    <td class="px-4 py-3"><span class="font-mono bg-gray-100 text-[#0284c7] px-2 py-0.5 rounded text-xs font-bold">parent_phone</span></td>
                                    <td class="px-4 py-3 text-gray-600">Nomor HP orang tua</td>
                                    <td class="px-4 py-3 text-center"><span class="text-gray-400 text-xs">—</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

            <!-- Right: Instructions & Template Download -->
            <div class="space-y-6">

                <!-- Download Template -->
                <div class="bg-gradient-to-br from-[#0284c7]/5 to-[#0ea5e9]/5 border border-[#0284c7]/20 rounded-2xl p-6">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="p-2 bg-[#0284c7]/10 rounded-xl">
                            <svg class="w-6 h-6 text-[#0284c7]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                        </div>
                        <div>
                            <h4 class="font-bold text-gray-900">Template CSV</h4>
                            <p class="text-xs text-gray-500">Gunakan template resmi</p>
                        </div>
                    </div>
                    <p class="text-sm text-gray-600 mb-4">
                        Download template CSV yang sudah menyertakan header kolom yang benar. Isi data siswa sesuai format, lalu upload kembali.
                    </p>
                    <a href="{{ route('students.template') }}"
                       class="group flex items-center justify-center gap-2 w-full py-3 bg-gradient-to-r from-[#0284c7] to-[#0ea5e9] hover:from-[#027ab8] hover:to-[#0d93d7] text-white rounded-xl shadow-md hover:shadow-lg transition-all duration-300 font-bold">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                        </svg>
                        Download Template
                    </a>
                </div>

                <!-- Instructions -->
                <div class="bg-gradient-to-br from-white to-gray-50 rounded-2xl shadow-lg border border-gray-100 p-6">
                    <div class="flex items-center gap-2 mb-4">
                        <svg class="w-5 h-5 text-[#0284c7]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                        </svg>
                        <h4 class="font-bold text-gray-900">Petunjuk Import</h4>
                    </div>
                    <ol class="space-y-3 text-sm text-gray-600">
                        <li class="flex items-start gap-3">
                            <span class="flex-shrink-0 w-6 h-6 bg-[#0284c7] text-white rounded-full text-xs font-bold flex items-center justify-center">1</span>
                            <span>Download template CSV di atas untuk mendapatkan format yang sudah benar.</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="flex-shrink-0 w-6 h-6 bg-[#0284c7] text-white rounded-full text-xs font-bold flex items-center justify-center">2</span>
                            <span>Buka template dengan Excel atau Google Sheets, isi data siswa sesuai format.</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="flex-shrink-0 w-6 h-6 bg-[#0284c7] text-white rounded-full text-xs font-bold flex items-center justify-center">3</span>
                            <span>Simpan file dalam format <span class="font-mono font-bold">.csv</span> dengan encoding <span class="font-mono font-bold">UTF-8</span>.</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="flex-shrink-0 w-6 h-6 bg-[#0284c7] text-white rounded-full text-xs font-bold flex items-center justify-center">4</span>
                            <span>Upload file CSV yang sudah diisi menggunakan form di sebelah kiri.</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="flex-shrink-0 w-6 h-6 bg-[#0284c7] text-white rounded-full text-xs font-bold flex items-center justify-center">5</span>
                            <span>Sistem akan memproses dan melaporkan baris yang berhasil maupun yang gagal.</span>
                        </li>
                    </ol>
                </div>

                <!-- Warning Box -->
                <div class="bg-amber-50 border border-amber-200 rounded-2xl p-5">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-amber-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                        <div>
                            <p class="font-bold text-amber-700 text-sm mb-1">Perhatian</p>
                            <p class="text-xs text-amber-600">Siswa dengan NIS yang sudah ada akan dilewati (tidak ditimpa). Pastikan data NIS unik untuk setiap baris.</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <style>
    input:focus, select:focus {
        outline: none;
        box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.1);
    }

    .overflow-x-auto::-webkit-scrollbar {
        height: 6px;
    }
    .overflow-x-auto::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
    }
    .overflow-x-auto::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 4px;
    }
    </style>

    <script>
    function updateFileName(input) {
        const placeholder = document.getElementById('upload-placeholder');
        const selected = document.getElementById('upload-selected');
        const filename = document.getElementById('selected-filename');

        if (input.files && input.files[0]) {
            placeholder.classList.add('hidden');
            selected.classList.remove('hidden');
            filename.textContent = input.files[0].name;
        } else {
            placeholder.classList.remove('hidden');
            selected.classList.add('hidden');
        }
    }

    // Drag and drop visual feedback
    const dropzone = document.querySelector('[onclick]');
    if (dropzone) {
        dropzone.addEventListener('dragover', function(e) {
            e.preventDefault();
            this.classList.add('border-[#0284c7]', 'bg-blue-50/50');
        });
        dropzone.addEventListener('dragleave', function() {
            this.classList.remove('border-[#0284c7]', 'bg-blue-50/50');
        });
        dropzone.addEventListener('drop', function(e) {
            e.preventDefault();
            this.classList.remove('border-[#0284c7]', 'bg-blue-50/50');
            const fileInput = document.getElementById('csv-file-input');
            fileInput.files = e.dataTransfer.files;
            updateFileName(fileInput);
        });
    }
    </script>
</x-app-layout>
