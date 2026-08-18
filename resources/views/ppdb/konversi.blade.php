<x-app-layout>
    <!-- Header Section -->
    <div class="mb-8">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <div class="p-2 bg-gradient-to-br from-[#0284c7]/10 to-[#0ea5e9]/10 rounded-xl">
                        <svg class="w-7 h-7 text-[#0284c7]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Aktivasi Massal Siswa</h1>
                        @if(isset($tahunAjaranAktif))
                        <p class="text-sm text-gray-500 mt-0.5">Tahun Ajaran: <span class="font-semibold text-gray-700">{{ $tahunAjaranAktif->nama ?? $tahunAjaranAktif }}</span></p>
                        @endif
                    </div>
                </div>
                <nav class="flex items-center gap-2 ml-12 text-sm text-gray-500">
                    <a href="{{ route('dashboard') }}" class="hover:text-[#0284c7] transition-colors">Dashboard</a>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                    <a href="{{ route('ppdb.index') }}" class="hover:text-[#0284c7] transition-colors">Registrasi Siswa Baru</a>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                    <span class="text-gray-700 font-medium">Aktivasi Massal</span>
                </nav>
            </div>
        </div>
    </div>

    @if($calonSiswa->isEmpty())
    <!-- Empty State Warning -->
    <div class="bg-amber-50 border border-amber-200 rounded-xl p-6 flex items-start gap-4">
        <div class="p-2 bg-amber-100 rounded-xl shrink-0">
            <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
            </svg>
        </div>
        <div>
            <h3 class="font-bold text-amber-800 mb-1">Tidak ada calon siswa yang perlu diproses</h3>
            <p class="text-sm text-amber-700">Belum ada calon siswa aktif dalam antrian aktivasi. Daftarkan calon siswa baru terlebih dahulu.</p>
            <a href="{{ route('ppdb.create') }}"
               class="inline-flex items-center gap-2 mt-3 px-4 py-2 bg-amber-600 text-white rounded-xl hover:bg-amber-700 transition-all text-sm font-medium">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Daftarkan Calon Siswa
            </a>
        </div>
    </div>

    @else
    <!-- Konversi Form -->
    <form action="{{ route('ppdb.konversi.eksekusi') }}" method="POST" id="konversiForm">
        @csrf

        <!-- Kelas Default -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
            <h2 class="text-base font-bold text-gray-800 mb-4 pb-3 border-b border-gray-100 flex items-center gap-2">
                <svg class="w-4 h-4 text-[#0284c7]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                </svg>
                Pengaturan Kelas
            </h2>
            <div class="flex items-center gap-4 flex-wrap">
                <div class="flex-1 min-w-[240px]">
                    <label for="kelas_id_default" class="block text-sm font-semibold text-gray-700 mb-1.5">
                        Kelas Default untuk Semua
                        <span class="text-gray-400 font-normal">(opsional)</span>
                    </label>
                    <select id="kelas_id_default" name="kelas_id_default"
                            class="w-full border border-gray-200 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-[#0284c7]/20 focus:border-[#0284c7] outline-none text-sm transition-all duration-200 bg-white">
                        <option value="">-- Tidak ada default --</option>
                        @foreach($kelasList as $kelas)
                        <option value="{{ $kelas->id }}">{{ $kelas->nama_kelas }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="pt-6 text-sm text-gray-500">
                    Kelas default akan dipakai untuk siswa yang tidak memiliki kelas individual.
                </div>
            </div>
        </div>

        <!-- Tabel Calon Siswa -->
        <div class="bg-gradient-to-br from-white to-gray-50 rounded-2xl shadow-xl border border-gray-100 overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
                <div class="flex items-center justify-between">
                    <h3 class="font-bold text-gray-900">Daftar Calon Siswa</h3>
                    <span class="text-sm text-gray-500">
                        <span id="selectedCount" class="font-bold text-[#0284c7]">0</span> dipilih dari
                        <span class="font-bold">{{ $calonSiswa->count() }}</span> calon
                    </span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gradient-to-r from-gray-50/80 to-gray-100/80 text-gray-700 text-xs font-bold uppercase tracking-wider">
                            <th class="px-6 py-4 text-center w-12">
                                <input type="checkbox" id="selectAll"
                                       class="w-4 h-4 rounded text-[#0284c7] border-gray-300 focus:ring-[#0284c7]/20 cursor-pointer">
                            </th>
                            <th class="px-6 py-4 text-left">Nama</th>
                            <th class="px-6 py-4 text-left">NISN</th>
                            <th class="px-6 py-4 text-left min-w-[140px]">NIS <span class="text-red-500">*</span></th>
                            <th class="px-6 py-4 text-center">L/P</th>
                            <th class="px-6 py-4 text-left min-w-[200px]">Kelas</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($calonSiswa as $calon)
                        <tr class="group hover:bg-blue-50/30 transition-colors duration-200" id="row-{{ $calon->id }}">
                            <td class="px-6 py-4 text-center">
                                <input type="checkbox" name="siswa_ids[]" value="{{ $calon->id }}"
                                       class="siswa-checkbox w-4 h-4 rounded text-[#0284c7] border-gray-300 focus:ring-[#0284c7]/20 cursor-pointer">
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-[#0284c7]/20 to-[#0ea5e9]/20 text-[#0284c7] flex items-center justify-center text-xs font-bold shrink-0">
                                        {{ strtoupper(substr($calon->name, 0, 1)) }}
                                    </div>
                                    <span class="font-semibold text-gray-900 text-sm">{{ $calon->name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600 font-mono">
                                {{ $calon->nisn ?? '—' }}
                            </td>
                            <td class="px-6 py-4">
                                <input type="text"
                                       name="nis_per_siswa[{{ $calon->id }}]"
                                       value="{{ old('nis_per_siswa.' . $calon->id) }}"
                                       placeholder="Masukkan NIS"
                                       maxlength="20"
                                       class="w-full border border-gray-200 rounded-xl px-3 py-2 focus:ring-2 focus:ring-[#0284c7]/20 focus:border-[#0284c7] outline-none text-sm font-mono transition-all duration-200">
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold
                                    {{ $calon->gender === 'L' ? 'bg-blue-50 text-blue-700 border border-blue-200' : 'bg-pink-50 text-pink-700 border border-pink-200' }}">
                                    {{ $calon->gender_label }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <select name="kelas_per_siswa[{{ $calon->id }}]"
                                        class="w-full border border-gray-200 rounded-xl px-3 py-2 focus:ring-2 focus:ring-[#0284c7]/20 focus:border-[#0284c7] outline-none text-sm transition-all duration-200 bg-white">
                                    <option value="">Gunakan default</option>
                                    @foreach($kelasList as $kelas)
                                    <option value="{{ $kelas->id }}">{{ $kelas->nama_kelas }}</option>
                                    @endforeach
                                </select>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center justify-between gap-4">
            <a href="{{ route('ppdb.index') }}"
               class="flex items-center gap-2 bg-white border border-gray-200 text-gray-700 px-4 py-2.5 rounded-xl hover:border-[#0284c7]/50 hover:text-[#0284c7] transition-all duration-300 font-medium text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Kembali
            </a>

            <button type="button" id="btnAktivasi"
                    onclick="konfirmasiAktivasi()"
                    class="flex items-center gap-2 bg-[#0284c7] text-white px-5 py-2.5 rounded-xl hover:bg-[#0369a1] transition-all duration-300 font-medium shadow-sm text-sm disabled:opacity-50 disabled:cursor-not-allowed">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Aktifkan Siswa Terpilih
            </button>
        </div>
    </form>
    @endif

    <script>
    // Pilih Semua
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.siswa-checkbox');
    const selectedCount = document.getElementById('selectedCount');

    function updateCount() {
        const checked = document.querySelectorAll('.siswa-checkbox:checked').length;
        if (selectedCount) selectedCount.textContent = checked;
    }

    if (selectAll) {
        selectAll.addEventListener('change', function() {
            checkboxes.forEach(cb => cb.checked = this.checked);
            updateCount();
        });
    }

    checkboxes.forEach(cb => {
        cb.addEventListener('change', function() {
            updateCount();
            if (!this.checked && selectAll) selectAll.checked = false;
            if (document.querySelectorAll('.siswa-checkbox:checked').length === checkboxes.length && selectAll) {
                selectAll.checked = true;
            }
        });
    });

    function konfirmasiAktivasi() {
        const checked = document.querySelectorAll('.siswa-checkbox:checked').length;
        if (checked === 0) {
            alert('Pilih minimal satu calon siswa untuk diaktifkan.');
            return;
        }
        // Validate NIS filled for all checked rows
        let missingNis = [];
        document.querySelectorAll('.siswa-checkbox:checked').forEach(cb => {
            const siswaId = cb.value;
            const nisInput = document.querySelector(`input[name="nis_per_siswa[${siswaId}]"]`);
            if (!nisInput || !nisInput.value.trim()) {
                const row = document.getElementById('row-' + siswaId);
                const nama = row ? row.querySelector('span.font-semibold')?.textContent?.trim() : siswaId;
                missingNis.push(nama);
            }
        });
        if (missingNis.length > 0) {
            alert('NIS wajib diisi untuk semua siswa yang dipilih.\nBelum diisi:\n' + missingNis.join('\n'));
            return;
        }
        if (confirm(`Aktifkan ${checked} calon siswa menjadi siswa aktif? Tindakan ini tidak dapat dibatalkan.`)) {
            document.getElementById('konversiForm').submit();
        }
    }
    </script>

    <style>
    input:focus, select:focus { outline: none; }
    .overflow-x-auto::-webkit-scrollbar { height: 8px; }
    .overflow-x-auto::-webkit-scrollbar-track { background: #f1f1f1; border-radius: 4px; }
    .overflow-x-auto::-webkit-scrollbar-thumb { background: #c1c1c1; border-radius: 4px; }
    .overflow-x-auto::-webkit-scrollbar-thumb:hover { background: #a1a1a1; }
    </style>
</x-app-layout>
