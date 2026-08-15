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
                    <a href="{{ route('naik-kelas.index') }}" class="hover:text-[#0284c7] transition-colors">Naik Kelas Massal</a>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                    <span class="text-gray-700 font-medium">Preview</span>
                </nav>
                <div class="flex items-center gap-3">
                    <div class="p-2 bg-gradient-to-br from-amber-50 to-orange-100 rounded-xl">
                        <svg class="w-7 h-7 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Preview Naik Kelas</h1>
                        <p class="text-sm text-gray-500 mt-0.5">
                            <span class="font-semibold text-[#0284c7]">{{ $totalSiswa }}</span> siswa aktif akan diproses
                        </p>
                    </div>
                </div>
            </div>

            <a href="{{ route('naik-kelas.index') }}"
               class="flex items-center gap-2 bg-white border border-gray-200 text-gray-700 px-4 py-2.5 rounded-xl hover:border-[#0284c7]/50 hover:shadow-md transition-all duration-300 font-medium shadow-sm text-sm">
                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Ubah Mapping
            </a>
        </div>



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

        <!-- Warning Box -->
        <div class="mb-6 flex items-start gap-3 bg-amber-50 border border-amber-300 text-amber-900 px-5 py-4 rounded-xl">
            <svg class="w-5 h-5 mt-0.5 flex-shrink-0 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
            </svg>
            <div class="text-sm leading-relaxed">
                <p class="font-bold mb-0.5">Perhatian — Aksi Tidak Dapat Dibatalkan</p>
                <p>Proses ini tidak dapat dibatalkan. Pastikan data mapping sudah benar sebelum melanjutkan. Semua siswa aktif pada kelas asal akan dipindahkan ke kelas tujuan yang kamu tentukan.</p>
            </div>
        </div>

        <!-- Preview Per Mapping -->
        <div class="space-y-5 mb-6">
            @foreach($preview as $item)
            <div class="bg-gradient-to-br from-white to-gray-50 rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
                <!-- Card Header -->
                <div class="px-6 py-4 bg-gradient-to-r from-[#0284c7]/5 to-[#0ea5e9]/5 border-b border-[#0284c7]/10">
                    <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-4">
                        <div class="flex items-center gap-3 flex-1 min-w-0">
                            <!-- Kelas Asal -->
                            <div class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border border-gray-200 rounded-full shadow-sm">
                                <svg class="w-3.5 h-3.5 text-gray-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                </svg>
                                <span class="font-semibold text-gray-700 text-sm">{{ $item['kelas_asal']->nama_kelas }}</span>
                            </div>

                            <!-- Arrow -->
                            <svg class="w-5 h-5 text-[#0284c7] flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                            </svg>

                            <!-- Kelas Tujuan -->
                            <div class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-[#0284c7] rounded-full shadow-sm">
                                <svg class="w-3.5 h-3.5 text-white flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                </svg>
                                <span class="font-semibold text-white text-sm">{{ $item['kelas_tujuan'] ? $item['kelas_tujuan']->nama_kelas : 'Lulus' }}</span>
                            </div>
                        </div>

                        <!-- Jumlah Badge -->
                        <div class="flex-shrink-0">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-white border border-[#0284c7]/20 text-[#0284c7] text-sm font-bold rounded-full shadow-sm">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                {{ $item['jumlah'] }} siswa
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Tabel Siswa -->
                @if($item['siswa']->isEmpty())
                <div class="px-6 py-8 text-center text-gray-400 text-sm">
                    Tidak ada siswa aktif di kelas ini.
                </div>
                @else
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-gradient-to-r from-gray-50/80 to-gray-100/80 text-gray-600 text-xs font-bold uppercase tracking-wide">
                                <th class="px-6 py-3 text-left w-8">#</th>
                                <th class="px-6 py-3 text-left">Nama Siswa</th>
                                <th class="px-6 py-3 text-left">NIS</th>
                                <th class="px-6 py-3 text-left">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($item['siswa'] as $i => $siswa)
                            <tr class="hover:bg-blue-50/30 transition-colors duration-150">
                                <td class="px-6 py-3 text-xs text-gray-400 font-mono">{{ $i + 1 }}</td>
                                <td class="px-6 py-3">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-7 h-7 rounded-full bg-gradient-to-br from-[#0284c7]/20 to-[#0ea5e9]/20 flex items-center justify-center flex-shrink-0">
                                            <span class="text-[#0284c7] text-xs font-bold">{{ strtoupper(substr($siswa->name, 0, 1)) }}</span>
                                        </div>
                                        <span class="font-medium text-gray-900 text-sm">{{ $siswa->name }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-3">
                                    <span class="font-mono text-gray-600 text-sm">{{ $siswa->nis ?? '-' }}</span>
                                </td>
                                <td class="px-6 py-3">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $siswa->status_color }}">
                                        {{ $siswa->status_label ?? ucfirst($siswa->status) }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
            @endforeach
        </div>

        <!-- Form Konfirmasi -->
        <div class="bg-gradient-to-br from-white to-gray-50 rounded-2xl shadow-lg border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                    </svg>
                    <h3 class="font-bold text-gray-900">Konfirmasi Eksekusi</h3>
                </div>
                <p class="text-sm text-gray-500 mt-1 ml-7">Tambahkan catatan opsional lalu klik "Eksekusi Naik Kelas" untuk melanjutkan.</p>
            </div>

            <form method="POST" action="{{ route('naik-kelas.eksekusi') }}">
                @csrf

                {{-- Hidden fields untuk semua mapping — pakai $preview bukan $mappings (raw) --}}
                @foreach($preview as $i => $item)
                    <input type="hidden" name="mappings[{{ $i }}][kelas_asal_id]"   value="{{ $item['kelas_asal']->id }}">
                    <input type="hidden" name="mappings[{{ $i }}][kelas_tujuan_id]" value="{{ $item['kelas_tujuan'] ? $item['kelas_tujuan']->id : '' }}">
                    <input type="hidden" name="mappings[{{ $i }}][action]"          value="{{ $item['action'] }}">
                @endforeach

                <div class="p-6 space-y-4">
                    <!-- Summary -->
                    <div class="bg-gray-50 rounded-xl border border-gray-100 px-5 py-4 text-sm text-gray-700">
                        <div class="flex flex-wrap gap-x-6 gap-y-2">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-[#0284c7]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                                </svg>
                                <span><strong class="text-gray-900">{{ count($mappings) }}</strong> mapping kelas</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-[#0284c7]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                                <span><strong class="text-gray-900">{{ $totalSiswa }}</strong> siswa aktif akan diproses</span>
                            </div>
                        </div>
                    </div>

                    <!-- Catatan -->
                    <div>
                        <label for="catatan" class="block text-sm font-semibold text-gray-700 mb-1.5">
                            Catatan
                            <span class="text-gray-400 font-normal ml-1">(opsional)</span>
                        </label>
                        <textarea id="catatan"
                                  name="catatan"
                                  rows="3"
                                  placeholder="Contoh: Naik kelas TP 2025/2026"
                                  class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-[#0284c7] focus:ring-2 focus:ring-[#0284c7]/20 bg-white text-sm transition-all duration-200 resize-none">{{ old('catatan') }}</textarea>
                    </div>
                </div>

                <div class="px-6 pb-6 flex flex-col sm:flex-row items-center justify-between gap-3 border-t border-gray-100 pt-5">
                    <!-- Tombol Batalkan -->
                    <a href="{{ route('naik-kelas.index') }}"
                       class="w-full sm:w-auto flex items-center justify-center gap-2 px-5 py-2.5 bg-white border border-gray-200 text-gray-700 rounded-xl hover:bg-gray-50 hover:border-gray-300 transition-all duration-200 font-medium text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        Batalkan
                    </a>

                    <!-- Tombol Eksekusi -->
                    <button type="submit"
                            id="eksekusi-btn"
                            class="w-full sm:w-auto flex items-center justify-center gap-2 bg-red-600 hover:bg-red-700 text-white px-6 py-2.5 rounded-xl transition-all duration-200 font-semibold shadow-sm hover:shadow-md text-sm"
                            onclick="return confirmEksekusi(this)">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                        Eksekusi Naik Kelas
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function confirmEksekusi(btn) {
        var total = {{ $totalSiswa }};
        var confirmed = window.confirm(
            'Konfirmasi Eksekusi Naik Kelas\n\n' +
            'Kamu akan memproses ' + total + ' siswa aktif.\n' +
            'Aksi ini tidak dapat dibatalkan.\n\n' +
            'Lanjutkan?'
        );
        if (confirmed) {
            btn.disabled = true;
            btn.innerHTML = '<svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Memproses...';
        }
        return confirmed;
    }
    </script>
</x-app-layout>
