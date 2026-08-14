<x-app-layout>
    <div class="mb-8">
        <!-- Header Section -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
            <div>
                <nav class="flex items-center gap-2 text-sm text-gray-500 mb-2">
                    <a href="{{ route('students.index') }}" class="hover:text-[#0284c7] transition-colors">Siswa</a>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                    <span class="text-gray-700 font-medium">Naik Kelas Massal</span>
                </nav>
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-gradient-to-br from-[#0284c7]/10 to-[#0ea5e9]/10 rounded-xl">
                        <svg class="w-7 h-7 text-[#0284c7]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path>
                        </svg>
                    </div>
                    <h1 class="text-3xl font-bold text-gray-900">Naik Kelas Massal</h1>
                </div>
            </div>

            <a href="{{ route('students.index') }}"
               class="flex items-center gap-2 bg-white border border-gray-200 text-gray-700 px-4 py-2.5 rounded-xl hover:border-[#0284c7]/50 hover:shadow-md transition-all duration-300 font-medium shadow-sm text-sm">
                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Kembali ke Daftar Siswa
            </a>
        </div>

        <!-- Flash Messages -->
        @if(session('error'))
        <div class="mb-6 flex items-start gap-3 bg-red-50 border border-red-200 text-red-800 px-5 py-4 rounded-xl">
            <svg class="w-5 h-5 mt-0.5 flex-shrink-0 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span class="font-medium">{{ session('error') }}</span>
        </div>
        @endif

        @if($errors->any())
        <div class="mb-6 bg-red-50 border border-red-200 text-red-800 px-5 py-4 rounded-xl">
            <div class="flex items-center gap-2 mb-2">
                <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span class="font-bold">Terdapat kesalahan:</span>
            </div>
            <ul class="list-disc list-inside space-y-1 text-sm">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <!-- Info Box -->
        <div class="mb-6 flex items-start gap-3 bg-blue-50 border border-blue-200 text-blue-900 px-5 py-4 rounded-xl">
            <svg class="w-5 h-5 mt-0.5 flex-shrink-0 text-[#0284c7]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <div class="text-sm leading-relaxed">
                <p class="font-semibold mb-1">Tentang Fitur Ini</p>
                <p>Proses ini akan memperbarui <code class="bg-blue-100 px-1 py-0.5 rounded text-xs font-mono">class_name</code> semua siswa aktif sesuai mapping yang kamu tentukan. Siswa dengan status <strong>Keluar</strong>, <strong>Lulus</strong>, <strong>Alumni</strong>, atau <strong>Pindah Keluar</strong> akan dilewati secara otomatis — hanya siswa aktif yang terdampak.</p>
            </div>
        </div>

        <!-- Statistik Kelas -->
        <div class="bg-gradient-to-br from-white to-gray-50 rounded-2xl shadow-lg border border-gray-100 overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-[#0284c7]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                    </svg>
                    <h3 class="font-bold text-gray-900">Statistik Kelas Saat Ini</h3>
                    <span class="text-xs text-gray-500 font-normal ml-1">(hanya siswa aktif)</span>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gradient-to-r from-gray-50/80 to-gray-100/80 text-gray-700 text-sm font-bold uppercase">
                            <th class="px-6 py-3 text-left">Kelas</th>
                            <th class="px-6 py-3 text-right">Jumlah Siswa Aktif</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($statPerKelas as $stat)
                        <tr class="hover:bg-blue-50/40 transition-colors duration-150">
                            <td class="px-6 py-3">
                                <div class="inline-flex items-center gap-2 px-3 py-1 bg-gradient-to-r from-gray-50 to-gray-100 rounded-full border border-gray-200">
                                    <svg class="w-3.5 h-3.5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                    </svg>
                                    <span class="font-semibold text-gray-700 text-sm">{{ $stat->class_name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-3 text-right">
                                <span class="inline-flex items-center justify-center min-w-[2.5rem] px-3 py-1 bg-[#0284c7]/10 text-[#0284c7] text-sm font-bold rounded-full">
                                    {{ $stat->jumlah }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="2" class="px-6 py-8 text-center text-gray-400 text-sm">
                                Tidak ada data kelas aktif.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Form Mapping -->
        <div class="bg-gradient-to-br from-white to-gray-50 rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-[#0284c7]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                    </svg>
                    <h3 class="font-bold text-gray-900">Mapping Kelas Asal → Kelas Tujuan</h3>
                </div>
                <p class="text-sm text-gray-500 mt-1 ml-7">Tentukan kelas tujuan untuk setiap kelas asal. Kelas tujuan boleh berupa kelas baru yang belum ada.</p>
            </div>

            <form method="POST" action="{{ route('naik-kelas.preview') }}" id="mapping-form">
                @csrf

                <div class="p-6">
                    <div id="mappings-container" class="space-y-3 mb-5">
                        <!-- Baris pertama (tidak bisa dihapus) -->
                        <div class="mapping-row flex flex-col sm:flex-row items-start sm:items-center gap-3" data-index="0">
                            <div class="flex-1 min-w-0">
                                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Kelas Asal</label>
                                <select name="mappings[0][kelas_asal_id]" data-role="asal"
                                        class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-[#0284c7] focus:ring-2 focus:ring-[#0284c7]/20 bg-white text-sm transition-all duration-200">
                                    <option value="">-- Pilih Kelas Asal --</option>
                                    @foreach($kelasAktif as $kelas)
                                        <option value="{{ $kelas->id }}">{{ $kelas->nama_kelas }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="flex-shrink-0 pt-5 hidden sm:block">
                                <div class="flex items-center justify-center w-8 h-9">
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                                    </svg>
                                </div>
                            </div>

                            <div class="flex-1 min-w-0">
                                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Kelas Tujuan</label>
                                <select name="mappings[0][kelas_tujuan_id]" data-role="tujuan"
                                        class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-[#0284c7] focus:ring-2 focus:ring-[#0284c7]/20 bg-white text-sm transition-all duration-200">
                                    <option value="">-- Lulus / Tidak Dinaikkan --</option>
                                    @foreach($semuaKelas as $kt)
                                        <option value="{{ $kt->id }}">{{ $kt->nama_kelas }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Action: otomatis 'naik'; bisa di-override jika tingkat akhir --}}
                            <input type="hidden" name="mappings[0][action]" data-role="action" value="naik">

                            <!-- Placeholder tombol hapus agar layout sejajar -->
                            <div class="flex-shrink-0 pt-5 w-10 hidden sm:block"></div>
                        </div>
                    </div>

                    <!-- Tombol Tambah Mapping -->
                    <button type="button"
                            id="add-mapping-btn"
                            class="flex items-center gap-2 text-[#0284c7] hover:text-[#0369a1] font-medium text-sm px-4 py-2 rounded-xl border border-[#0284c7]/30 hover:border-[#0284c7]/60 hover:bg-[#0284c7]/5 transition-all duration-200">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                        Tambah Mapping
                    </button>
                </div>

                <div class="px-6 pb-6 flex flex-col sm:flex-row items-center justify-end gap-3 border-t border-gray-100 pt-5">
                    <a href="{{ route('students.index') }}"
                       class="w-full sm:w-auto flex items-center justify-center gap-2 px-5 py-2.5 bg-white border border-gray-200 text-gray-700 rounded-xl hover:bg-gray-50 hover:border-gray-300 transition-all duration-200 font-medium text-sm">
                        Batal
                    </a>
                    <button type="submit"
                            class="w-full sm:w-auto flex items-center justify-center gap-2 bg-[#0284c7] hover:bg-[#0369a1] text-white px-6 py-2.5 rounded-xl transition-all duration-200 font-medium shadow-sm hover:shadow-md text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                        Preview Naik Kelas
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
    (function () {
        const kelasAktif = @json($kelasAktif->map(fn($k) => ['id' => $k->id, 'nama_kelas' => $k->nama_kelas]));
        const semuaKelas  = @json($semuaKelas->map(fn($k) => ['id' => $k->id, 'nama_kelas' => $k->nama_kelas]));
        const container  = document.getElementById('mappings-container');
        const addBtn     = document.getElementById('add-mapping-btn');

        function buildOptionHTML(selectedId) {
            let opts = '<option value="">-- Pilih Kelas Asal --</option>';
            kelasAktif.forEach(function (kelas) {
                const sel = kelas.id == selectedId ? ' selected' : '';
                opts += '<option value="' + kelas.id + '"' + sel + '>' + escapeHtml(kelas.nama_kelas) + '</option>';
            });
            return opts;
        }

        function buildTujuanHTML(selectedId) {
            let opts = '<option value="">-- Lulus / Tidak Dinaikkan --</option>';
            semuaKelas.forEach(function (kelas) {
                const sel = kelas.id == selectedId ? ' selected' : '';
                opts += '<option value="' + kelas.id + '"' + sel + '>' + escapeHtml(kelas.nama_kelas) + '</option>';
            });
            return opts;
        }

        function escapeHtml(str) {
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/"/g, '&quot;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;');
        }

        function reindex() {
            const rows = container.querySelectorAll('.mapping-row');
            rows.forEach(function (row, i) {
                row.dataset.index = i;
                row.querySelector('select[data-role="asal"]').name  = 'mappings[' + i + '][kelas_asal_id]';
                row.querySelector('select[data-role="tujuan"]').name = 'mappings[' + i + '][kelas_tujuan_id]';
                const actionEl = row.querySelector('input[data-role="action"]');
                if (actionEl) actionEl.name = 'mappings[' + i + '][action]';
            });
        }

        function createRow() {
            const row = document.createElement('div');
            row.className   = 'mapping-row flex flex-col sm:flex-row items-start sm:items-center gap-3';
            row.dataset.index = 0; // akan di-reindex

            row.innerHTML = `
                <div class="flex-1 min-w-0">
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Kelas Asal</label>
                    <select class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-[#0284c7] focus:ring-2 focus:ring-[#0284c7]/20 bg-white text-sm transition-all duration-200">
                        ${buildOptionHTML('')}
                    </select>
                </div>
                <div class="flex-shrink-0 pt-5 hidden sm:block">
                    <div class="flex items-center justify-center w-8 h-9">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                        </svg>
                    </div>
                </div>
                <div class="flex-1 min-w-0">
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wide mb-1">Kelas Tujuan</label>
                    <select data-role="tujuan" class="w-full px-4 py-2.5 rounded-xl border border-gray-200 focus:border-[#0284c7] focus:ring-2 focus:ring-[#0284c7]/20 bg-white text-sm transition-all duration-200">
                        \${buildTujuanHTML('')}
                    </select>
                </div>
                <div class="flex-shrink-0 pt-5">
                    <button type="button"
                            class="remove-row-btn w-9 h-9 flex items-center justify-center rounded-xl border border-red-200 text-red-400 hover:bg-red-50 hover:text-red-600 hover:border-red-300 transition-all duration-200"
                            title="Hapus baris ini">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                    </button>
                </div>
            `;

            row.querySelector('.remove-row-btn').addEventListener('click', function () {
                row.remove();
                reindex();
            });

            return row;
        }

        addBtn.addEventListener('click', function () {
            const row = createRow();
            container.appendChild(row);
            reindex();
            row.querySelector('select').focus();
        });
    })();
    </script>
</x-app-layout>
