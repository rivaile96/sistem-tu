<x-app-layout>
    <!-- Header Section -->
    <div class="mb-8">
        <div class="flex items-center gap-3 mb-2">
            <div class="p-2 bg-gradient-to-br from-[#0284c7]/10 to-[#0ea5e9]/10 rounded-xl">
                <svg class="w-7 h-7 text-[#0284c7]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                </svg>
            </div>
            <h1 class="text-3xl font-bold text-gray-900">Daftarkan Calon Siswa Baru</h1>
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
            <span class="text-gray-700 font-medium">Daftarkan Baru</span>
        </nav>
    </div>

    <!-- Form Card -->
    <form action="{{ route('ppdb.store') }}" method="POST">
        @csrf
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
            <h2 class="text-base font-bold text-gray-800 mb-5 pb-3 border-b border-gray-100 flex items-center gap-2">
                <svg class="w-4 h-4 text-[#0284c7]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                </svg>
                Data Pribadi
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <!-- Nama Lengkap -->
                <div class="md:col-span-2">
                    <label for="name" class="block text-sm font-semibold text-gray-700 mb-1.5">
                        Nama Lengkap <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="name" name="name" value="{{ old('name') }}"
                           placeholder="Masukkan nama lengkap"
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-[#0284c7]/20 focus:border-[#0284c7] outline-none text-sm transition-all duration-200 @error('name') border-red-400 focus:border-red-400 focus:ring-red-200 @enderror">
                    @error('name')
                    <p class="mt-1.5 text-xs text-red-600 flex items-center gap-1">
                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        {{ $message }}
                    </p>
                    @enderror
                </div>

                <!-- NISN -->
                <div>
                    <label for="nisn" class="block text-sm font-semibold text-gray-700 mb-1.5">NISN</label>
                    <input type="text" id="nisn" name="nisn" value="{{ old('nisn') }}"
                           placeholder="Nomor Induk Siswa Nasional"
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-[#0284c7]/20 focus:border-[#0284c7] outline-none text-sm transition-all duration-200 @error('nisn') border-red-400 focus:border-red-400 focus:ring-red-200 @enderror">
                    @error('nisn')
                    <p class="mt-1.5 text-xs text-red-600 flex items-center gap-1">
                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        {{ $message }}
                    </p>
                    @enderror
                </div>

                <!-- Jenis Kelamin -->
                <div>
                    <label for="gender" class="block text-sm font-semibold text-gray-700 mb-1.5">
                        Jenis Kelamin <span class="text-red-500">*</span>
                    </label>
                    <select id="gender" name="gender"
                            class="w-full border border-gray-200 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-[#0284c7]/20 focus:border-[#0284c7] outline-none text-sm transition-all duration-200 bg-white @error('gender') border-red-400 focus:border-red-400 focus:ring-red-200 @enderror">
                        <option value="">-- Pilih --</option>
                        <option value="L" {{ old('gender') === 'L' ? 'selected' : '' }}>Laki-laki</option>
                        <option value="P" {{ old('gender') === 'P' ? 'selected' : '' }}>Perempuan</option>
                    </select>
                    @error('gender')
                    <p class="mt-1.5 text-xs text-red-600 flex items-center gap-1">
                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        {{ $message }}
                    </p>
                    @enderror
                </div>

                <!-- Tempat Lahir -->
                <div>
                    <label for="birth_place" class="block text-sm font-semibold text-gray-700 mb-1.5">Tempat Lahir</label>
                    <input type="text" id="birth_place" name="birth_place" value="{{ old('birth_place') }}"
                           placeholder="Kota/kabupaten tempat lahir"
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-[#0284c7]/20 focus:border-[#0284c7] outline-none text-sm transition-all duration-200 @error('birth_place') border-red-400 focus:border-red-400 focus:ring-red-200 @enderror">
                    @error('birth_place')
                    <p class="mt-1.5 text-xs text-red-600 flex items-center gap-1">
                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        {{ $message }}
                    </p>
                    @enderror
                </div>

                <!-- Tanggal Lahir -->
                <div>
                    <label for="birth_date" class="block text-sm font-semibold text-gray-700 mb-1.5">Tanggal Lahir</label>
                    <input type="date" id="birth_date" name="birth_date" value="{{ old('birth_date') }}"
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-[#0284c7]/20 focus:border-[#0284c7] outline-none text-sm transition-all duration-200 @error('birth_date') border-red-400 focus:border-red-400 focus:ring-red-200 @enderror">
                    @error('birth_date')
                    <p class="mt-1.5 text-xs text-red-600 flex items-center gap-1">
                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        {{ $message }}
                    </p>
                    @enderror
                </div>

                <!-- Alamat -->
                <div class="md:col-span-2">
                    <label for="address" class="block text-sm font-semibold text-gray-700 mb-1.5">Alamat</label>
                    <textarea id="address" name="address" rows="3"
                              placeholder="Alamat lengkap tempat tinggal"
                              class="w-full border border-gray-200 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-[#0284c7]/20 focus:border-[#0284c7] outline-none text-sm transition-all duration-200 resize-none @error('address') border-red-400 focus:border-red-400 focus:ring-red-200 @enderror">{{ old('address') }}</textarea>
                    @error('address')
                    <p class="mt-1.5 text-xs text-red-600 flex items-center gap-1">
                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        {{ $message }}
                    </p>
                    @enderror
                </div>

                <!-- No. HP Siswa -->
                <div>
                    <label for="phone" class="block text-sm font-semibold text-gray-700 mb-1.5">No. HP Siswa</label>
                    <input type="text" id="phone" name="phone" value="{{ old('phone') }}"
                           placeholder="Contoh: 08123456789"
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-[#0284c7]/20 focus:border-[#0284c7] outline-none text-sm transition-all duration-200 @error('phone') border-red-400 focus:border-red-400 focus:ring-red-200 @enderror">
                    @error('phone')
                    <p class="mt-1.5 text-xs text-red-600 flex items-center gap-1">
                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        {{ $message }}
                    </p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Data Orang Tua -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
            <h2 class="text-base font-bold text-gray-800 mb-5 pb-3 border-b border-gray-100 flex items-center gap-2">
                <svg class="w-4 h-4 text-[#0284c7]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
                Data Orang Tua / Wali
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <!-- Nama Orang Tua -->
                <div>
                    <label for="parent_name" class="block text-sm font-semibold text-gray-700 mb-1.5">Nama Orang Tua / Wali</label>
                    <input type="text" id="parent_name" name="parent_name" value="{{ old('parent_name') }}"
                           placeholder="Nama orang tua atau wali"
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-[#0284c7]/20 focus:border-[#0284c7] outline-none text-sm transition-all duration-200 @error('parent_name') border-red-400 focus:border-red-400 focus:ring-red-200 @enderror">
                    @error('parent_name')
                    <p class="mt-1.5 text-xs text-red-600 flex items-center gap-1">
                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        {{ $message }}
                    </p>
                    @enderror
                </div>

                <!-- No. HP Orang Tua -->
                <div>
                    <label for="parent_phone" class="block text-sm font-semibold text-gray-700 mb-1.5">No. HP Orang Tua</label>
                    <input type="text" id="parent_phone" name="parent_phone" value="{{ old('parent_phone') }}"
                           placeholder="Contoh: 08123456789"
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-[#0284c7]/20 focus:border-[#0284c7] outline-none text-sm transition-all duration-200 @error('parent_phone') border-red-400 focus:border-red-400 focus:ring-red-200 @enderror">
                    @error('parent_phone')
                    <p class="mt-1.5 text-xs text-red-600 flex items-center gap-1">
                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        {{ $message }}
                    </p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Catatan -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
            <h2 class="text-base font-bold text-gray-800 mb-5 pb-3 border-b border-gray-100 flex items-center gap-2">
                <svg class="w-4 h-4 text-[#0284c7]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Catatan
            </h2>

            <div>
                <label for="catatan" class="block text-sm font-semibold text-gray-700 mb-1.5">
                    Catatan <span class="text-gray-400 font-normal">(opsional)</span>
                </label>
                <textarea id="catatan" name="catatan" rows="4"
                          placeholder="Tambahkan catatan tambahan jika ada..."
                          class="w-full border border-gray-200 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-[#0284c7]/20 focus:border-[#0284c7] outline-none text-sm transition-all duration-200 resize-none @error('catatan') border-red-400 focus:border-red-400 focus:ring-red-200 @enderror">{{ old('catatan') }}</textarea>
                @error('catatan')
                <p class="mt-1.5 text-xs text-red-600 flex items-center gap-1">
                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    {{ $message }}
                </p>
                @enderror
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('ppdb.index') }}"
               class="flex items-center gap-2 bg-white border border-gray-200 text-gray-700 px-4 py-2.5 rounded-xl hover:border-[#0284c7]/50 hover:text-[#0284c7] transition-all duration-300 font-medium text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
                Batalkan
            </a>
            <button type="submit"
                    class="flex items-center gap-2 bg-[#0284c7] text-white px-5 py-2.5 rounded-xl hover:bg-[#0369a1] transition-all duration-300 font-medium shadow-sm text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                </svg>
                Daftarkan
            </button>
        </div>
    </form>

    <style>
    input:focus, select:focus, textarea:focus { outline: none; }
    </style>
</x-app-layout>
