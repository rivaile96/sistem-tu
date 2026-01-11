<x-app-layout>
    <div class="max-w-3xl mx-auto space-y-6">
        
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Generator Tagihan Massal</h2>
            <p class="text-sm text-gray-500">Buat tagihan serentak untuk satu kelas atau seluruh siswa.</p>
        </div>

        <div class="bg-white p-8 rounded-2xl shadow-sm border border-gray-100">
            <form action="{{ route('bills.store') }}" method="POST" class="space-y-6">
                @csrf

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Target Siswa / Kelas</label>

                    @php
                        // Normalize $classes so it's always iterable:
                        // - If it's a JSON string, decode it to an array
                        // - If it's a plain string scalar, wrap it into a single-element array
                        if (isset($classes) && is_string($classes)) {
                            $decoded = json_decode($classes, true);
                            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                                $classes = $decoded;
                            } else {
                                $classes = [$classes];
                            }
                        }
                    @endphp

                    <select name="target_class" class="w-full rounded-xl border-gray-300 focus:ring-blue-500 focus:border-blue-500 py-3" required>
                        <option value="" disabled selected>-- Pilih Target --</option>
                        <option value="ALL" class="font-bold text-blue-600">📢 SEMUA SISWA (SATU SEKOLAH)</option>

                        @if(!empty($classes) && is_iterable($classes))
                            @foreach($classes as $cls)
                                <option value="{{ $cls }}">{{ $cls }}</option>
                            @endforeach
                        @else
                            <option value="" disabled>Tidak ada kelas tersedia</option>
                        @endif
                    </select>

                    <p class="text-xs text-gray-400 mt-1">*Pilih "Semua Siswa" untuk tagihan umum seperti Uang Gedung/Kegiatan.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Jenis Tagihan</label>
                        <select name="type" class="w-full rounded-xl border-gray-300 focus:ring-blue-500 focus:border-blue-500 py-3" required>
                            <option value="SPP">SPP Bulanan</option>
                            <option value="UANG_GEDUNG">Uang Gedung / Pangkal</option>
                            <option value="SERAGAM">Seragam & Atribut</option>
                            <option value="BUKU">Buku & LKS</option>
                            <option value="DAFTAR_ULANG">Daftar Ulang</option>
                            <option value="LAINNYA">Lainnya</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Nama / Keterangan</label>
                        <input type="text" name="name" placeholder="Contoh: SPP Februari 2026" class="w-full rounded-xl border-gray-300 focus:ring-blue-500 focus:border-blue-500 py-3" required>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Nominal Tagihan (Rp)</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-gray-500 font-bold">Rp</span>
                        <input type="number" name="amount" placeholder="0" class="w-full pl-12 pr-4 py-3 rounded-xl border-gray-300 focus:ring-blue-500 focus:border-blue-500 font-mono text-lg font-bold" required>
                    </div>
                </div>

                <div class="pt-4 border-t border-gray-100">
                    <button type="submit" onclick="return confirm('Yakin ingin membuat tagihan massal ini? Aksi ini tidak dapat dibatalkan satu per satu dengan cepat.')" 
                            class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 rounded-xl shadow-lg shadow-blue-200 transition transform active:scale-95 flex justify-center items-center gap-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        GENERATE TAGIHAN SEKARANG
                    </button>
                </div>

            </form>
        </div>
    </div>
</x-app-layout></button>