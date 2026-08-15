<x-app-layout>
    <!-- Header Section -->
    <div class="mb-8">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-2">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <div class="w-12 h-12 rounded-full bg-gradient-to-br from-[#0284c7]/20 to-[#0ea5e9]/20 text-[#0284c7] flex items-center justify-center text-lg font-bold shrink-0">
                        {{ strtoupper(substr($siswa->name, 0, 1)) }}
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h1 class="text-2xl font-bold text-gray-900">{{ $siswa->name }}</h1>
                            <span class="inline-flex items-center bg-yellow-100 text-yellow-700 border border-yellow-200 px-2 py-0.5 rounded-full text-xs font-medium">
                                Calon Siswa
                            </span>
                        </div>
                        <p class="text-sm text-gray-500 mt-0.5">
                            NISN: {{ $siswa->nisn ?? '—' }}
                            @if($siswa->nisn) &middot; @endif
                            Terdaftar {{ $siswa->created_at->format('d M Y') }}
                        </p>
                    </div>
                </div>
                <nav class="flex items-center gap-2 ml-15 text-sm text-gray-500 mt-1">
                    <a href="{{ route('dashboard') }}" class="hover:text-[#0284c7] transition-colors">Dashboard</a>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                    <a href="{{ route('ppdb.index') }}" class="hover:text-[#0284c7] transition-colors">PPDB</a>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                    <span class="text-gray-700 font-medium">{{ $siswa->name }}</span>
                </nav>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('ppdb.edit', $siswa->id) }}"
                   class="flex items-center gap-2 bg-[#0284c7] text-white px-4 py-2.5 rounded-xl hover:bg-[#0369a1] transition-all duration-300 font-medium shadow-sm text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    Edit Data
                </a>
                <a href="{{ route('ppdb.index') }}"
                   class="flex items-center gap-2 bg-white border border-gray-200 text-gray-700 px-4 py-2.5 rounded-xl hover:border-[#0284c7]/50 hover:text-[#0284c7] transition-all duration-300 font-medium shadow-sm text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Kembali
                </a>
            </div>
        </div>
    </div>

    <!-- Main Content: 2/3 + 1/3 -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">

        <!-- Data Lengkap (2/3) -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Data Pribadi -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h2 class="text-base font-bold text-gray-800 mb-5 pb-3 border-b border-gray-100 flex items-center gap-2">
                    <svg class="w-4 h-4 text-[#0284c7]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    Data Pribadi
                </h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-4">
                    <div>
                        <p class="text-xs text-gray-400 font-medium uppercase tracking-wide mb-1">Nama Lengkap</p>
                        <p class="text-sm font-semibold text-gray-800">{{ $siswa->name }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 font-medium uppercase tracking-wide mb-1">NISN</p>
                        <p class="text-sm font-semibold text-gray-800 font-mono">{{ $siswa->nisn ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 font-medium uppercase tracking-wide mb-1">Jenis Kelamin</p>
                        <p class="text-sm font-semibold text-gray-800">{{ $siswa->gender_label }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 font-medium uppercase tracking-wide mb-1">Tempat, Tanggal Lahir</p>
                        <p class="text-sm font-semibold text-gray-800">
                            @if($siswa->birth_place || $siswa->birth_date)
                                {{ $siswa->birth_place ?? '—' }}{{ $siswa->birth_date ? ', ' . $siswa->birth_date->format('d M Y') : '' }}
                            @else
                                —
                            @endif
                        </p>
                    </div>
                    <div class="sm:col-span-2">
                        <p class="text-xs text-gray-400 font-medium uppercase tracking-wide mb-1">Alamat</p>
                        <p class="text-sm font-semibold text-gray-800">{{ $siswa->address ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 font-medium uppercase tracking-wide mb-1">No. HP Siswa</p>
                        <p class="text-sm font-semibold text-gray-800">{{ $siswa->phone ?? '—' }}</p>
                    </div>
                </div>
            </div>

            <!-- Data Orang Tua -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
                <h2 class="text-base font-bold text-gray-800 mb-5 pb-3 border-b border-gray-100 flex items-center gap-2">
                    <svg class="w-4 h-4 text-[#0284c7]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    Data Orang Tua / Wali
                </h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-4">
                    <div>
                        <p class="text-xs text-gray-400 font-medium uppercase tracking-wide mb-1">Nama Orang Tua / Wali</p>
                        <p class="text-sm font-semibold text-gray-800">{{ $siswa->parent_name ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 font-medium uppercase tracking-wide mb-1">No. HP Orang Tua</p>
                        <p class="text-sm font-semibold text-gray-800">{{ $siswa->parent_phone ?? '—' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Seleksi (1/3) -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 sticky top-6">
                <h2 class="text-base font-bold text-gray-800 mb-5 pb-3 border-b border-gray-100 flex items-center gap-2">
                    <svg class="w-4 h-4 text-[#0284c7]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                    </svg>
                    Keputusan Seleksi
                </h2>

                <form action="{{ route('ppdb.seleksi', $siswa->id) }}" method="POST"
                      x-data="{
                          aksi: '{{ old('aksi', '') }}',
                          get showTerima() { return this.aksi === 'terima'; }
                      }">
                    @csrf

                    <!-- Radio Aksi -->
                    <div class="space-y-3 mb-5">
                        <label class="flex items-center gap-3 p-3 rounded-xl border-2 cursor-pointer transition-all duration-200"
                               :class="aksi === 'terima' ? 'border-emerald-400 bg-emerald-50' : 'border-gray-200 hover:border-gray-300'">
                            <input type="radio" name="aksi" value="terima" x-model="aksi"
                                   class="w-4 h-4 text-emerald-600 focus:ring-emerald-500">
                            <div>
                                <p class="text-sm font-semibold text-gray-800">Terima</p>
                                <p class="text-xs text-gray-500">Siswa diterima & akan dikonversi</p>
                            </div>
                        </label>

                        <label class="flex items-center gap-3 p-3 rounded-xl border-2 cursor-pointer transition-all duration-200"
                               :class="aksi === 'tolak' ? 'border-red-400 bg-red-50' : 'border-gray-200 hover:border-gray-300'">
                            <input type="radio" name="aksi" value="tolak" x-model="aksi"
                                   class="w-4 h-4 text-red-600 focus:ring-red-500">
                            <div>
                                <p class="text-sm font-semibold text-gray-800">Tolak</p>
                                <p class="text-xs text-gray-500">Siswa tidak diterima</p>
                            </div>
                        </label>
                    </div>

                    @error('aksi')
                    <p class="mb-3 text-xs text-red-600 flex items-center gap-1">
                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        {{ $message }}
                    </p>
                    @enderror

                    <!-- Fields untuk Terima -->
                    <div x-show="showTerima" x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 -translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         class="space-y-4 mb-4">

                        <!-- Kelas -->
                        <div>
                            <label for="kelas_id" class="block text-sm font-semibold text-gray-700 mb-1.5">
                                Kelas <span class="text-red-500">*</span>
                            </label>
                            <select id="kelas_id" name="kelas_id"
                                    class="w-full border border-gray-200 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-[#0284c7]/20 focus:border-[#0284c7] outline-none text-sm transition-all duration-200 bg-white @error('kelas_id') border-red-400 @enderror">
                                <option value="">-- Pilih Kelas --</option>
                                @foreach($kelasList as $kelas)
                                <option value="{{ $kelas->id }}" {{ old('kelas_id') == $kelas->id ? 'selected' : '' }}>
                                    {{ $kelas->nama_kelas }}
                                </option>
                                @endforeach
                            </select>
                            @error('kelas_id')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- NIS -->
                        <div>
                            <label for="nis" class="block text-sm font-semibold text-gray-700 mb-1.5">NIS</label>
                            <input type="text" id="nis" name="nis" value="{{ old('nis') }}"
                                   placeholder="Nomor Induk Siswa"
                                   class="w-full border border-gray-200 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-[#0284c7]/20 focus:border-[#0284c7] outline-none text-sm transition-all duration-200 @error('nis') border-red-400 @enderror">
                            @error('nis')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Catatan -->
                    <div class="mb-5">
                        <label for="catatan" class="block text-sm font-semibold text-gray-700 mb-1.5">
                            Catatan <span class="text-gray-400 font-normal">(opsional)</span>
                        </label>
                        <textarea id="catatan" name="catatan" rows="3"
                                  placeholder="Catatan keputusan seleksi..."
                                  class="w-full border border-gray-200 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-[#0284c7]/20 focus:border-[#0284c7] outline-none text-sm transition-all duration-200 resize-none">{{ old('catatan') }}</textarea>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="space-y-2">
                        <button type="submit" x-show="aksi === 'terima'"
                                class="w-full flex items-center justify-center gap-2 bg-emerald-600 text-white px-4 py-2.5 rounded-xl hover:bg-emerald-700 transition-all duration-300 font-medium shadow-sm text-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Terima ✓
                        </button>
                        <button type="submit" x-show="aksi === 'tolak'"
                                class="w-full flex items-center justify-center gap-2 bg-red-600 text-white px-4 py-2.5 rounded-xl hover:bg-red-700 transition-all duration-300 font-medium shadow-sm text-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                            Tolak ✗
                        </button>
                        <p x-show="aksi === ''" class="text-center text-xs text-gray-400 py-2">Pilih keputusan di atas</p>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Riwayat Log -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 bg-gray-50/50">
            <h2 class="font-bold text-gray-800 flex items-center gap-2">
                <svg class="w-4 h-4 text-[#0284c7]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                Riwayat Log
            </h2>
        </div>

        @if(isset($siswa->logs) && $siswa->logs->count() > 0)
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50/80 text-gray-600 text-xs font-bold uppercase tracking-wider">
                        <th class="px-6 py-3 text-left">Tanggal</th>
                        <th class="px-6 py-3 text-left">Perubahan Status</th>
                        <th class="px-6 py-3 text-left">Catatan</th>
                        <th class="px-6 py-3 text-left">Diubah Oleh</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($siswa->logs as $log)
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="px-6 py-3 text-sm text-gray-600 whitespace-nowrap">
                            {{ $log->created_at->format('d M Y, H:i') }}
                        </td>
                        <td class="px-6 py-3">
                            <div class="flex items-center gap-2 text-sm">
                                <span class="text-gray-500">{{ $log->status_lama ?? '—' }}</span>
                                <svg class="w-3.5 h-3.5 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                </svg>
                                <span class="font-semibold text-gray-800">{{ $log->status_baru ?? '—' }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-3 text-sm text-gray-600">{{ $log->catatan ?? '—' }}</td>
                        <td class="px-6 py-3 text-sm text-gray-600">{{ $log->user->name ?? '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="px-6 py-10 text-center">
            <svg class="w-10 h-10 text-gray-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <p class="text-sm text-gray-400">Belum ada riwayat log untuk calon siswa ini.</p>
        </div>
        @endif
    </div>

    <style>
    input:focus, select:focus, textarea:focus { outline: none; }
    </style>
</x-app-layout>
