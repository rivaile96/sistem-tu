<x-app-layout>
    <div class="mb-8">
        <!-- Header -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <div class="p-2 bg-gradient-to-br from-[#0284c7]/10 to-[#0ea5e9]/10 rounded-xl">
                        <svg class="w-7 h-7 text-[#0284c7]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Edit Data Siswa</h1>
                        <p class="text-gray-500 text-sm mt-0.5">NIS: {{ $student->nis }}</p>
                    </div>
                </div>
                <p class="text-gray-600 ml-12">Perbarui informasi data siswa <span class="font-semibold text-[#0284c7]">{{ $student->name }}</span>.</p>
            </div>
            <a href="{{ route('students.index') }}"
               class="flex items-center gap-2 bg-gradient-to-r from-white to-gray-50 border border-gray-200 text-gray-700 px-5 py-3 rounded-xl hover:border-[#0284c7]/50 hover:shadow-md transition-all duration-300 font-medium shadow-sm">
                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Kembali ke Daftar Siswa
            </a>
        </div>

        <!-- Validation Errors -->
        @if($errors->any())
        <div class="bg-red-50 border border-red-200 rounded-xl px-5 py-4 mb-6">
            <div class="flex items-center gap-2 mb-2">
                <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span class="font-bold text-red-700">Terdapat kesalahan pada input:</span>
            </div>
            <ul class="list-disc list-inside text-sm text-red-600 space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <!-- Form Card -->
        <form method="POST" action="{{ route('students.update', $student->id) }}">
            @csrf
            @method('PUT')

            <div class="bg-gradient-to-br from-white to-gray-50 rounded-2xl shadow-xl border border-gray-100 overflow-hidden">

                <!-- Section: Identitas Siswa -->
                <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
                    <div class="flex items-center gap-2">
                        <div class="w-1 h-5 bg-[#0284c7] rounded-full"></div>
                        <h3 class="font-bold text-gray-900">Identitas Siswa</h3>
                    </div>
                </div>

                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                        <!-- NIS -->
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">
                                NIS <span class="text-red-500">*</span>
                            </label>
                            <input type="text"
                                   name="nis"
                                   value="{{ old('nis', $student->nis) }}"
                                   placeholder="Nomor Induk Siswa"
                                   required
                                   class="w-full px-4 py-3 rounded-xl border {{ $errors->has('nis') ? 'border-red-400 bg-red-50' : 'border-gray-200' }} focus:border-[#0284c7] focus:ring-2 focus:ring-[#0284c7]/20 bg-white transition-all duration-300">
                            @error('nis')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- NISN -->
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">
                                NISN <span class="text-gray-400 font-normal text-xs">(opsional)</span>
                            </label>
                            <input type="text"
                                   name="nisn"
                                   value="{{ old('nisn', $student->nisn) }}"
                                   placeholder="Nomor Induk Siswa Nasional"
                                   class="w-full px-4 py-3 rounded-xl border {{ $errors->has('nisn') ? 'border-red-400 bg-red-50' : 'border-gray-200' }} focus:border-[#0284c7] focus:ring-2 focus:ring-[#0284c7]/20 bg-white transition-all duration-300">
                            @error('nisn')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Nama Lengkap -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-bold text-gray-700 mb-2">
                                Nama Lengkap <span class="text-red-500">*</span>
                            </label>
                            <input type="text"
                                   name="name"
                                   value="{{ old('name', $student->name) }}"
                                   placeholder="Nama lengkap siswa"
                                   required
                                   class="w-full px-4 py-3 rounded-xl border {{ $errors->has('name') ? 'border-red-400 bg-red-50' : 'border-gray-200' }} focus:border-[#0284c7] focus:ring-2 focus:ring-[#0284c7]/20 bg-white transition-all duration-300">
                            @error('name')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Jenis Kelamin -->
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">
                                Jenis Kelamin <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <select name="gender"
                                        required
                                        class="w-full px-4 py-3 rounded-xl border {{ $errors->has('gender') ? 'border-red-400 bg-red-50' : 'border-gray-200' }} focus:border-[#0284c7] focus:ring-2 focus:ring-[#0284c7]/20 bg-white appearance-none transition-all duration-300">
                                    <option value="">-- Pilih Jenis Kelamin --</option>
                                    <option value="L" {{ old('gender', $student->gender) == 'L' ? 'selected' : '' }}>Laki-laki</option>
                                    <option value="P" {{ old('gender', $student->gender) == 'P' ? 'selected' : '' }}>Perempuan</option>
                                </select>
                                <div class="absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-400 pointer-events-none">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </div>
                            </div>
                            @error('gender')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Kelas -->
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">
                                Kelas <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <select name="kelas_id"
                                        required
                                        class="w-full px-4 py-3 rounded-xl border {{ $errors->has('kelas_id') ? 'border-red-400 bg-red-50' : 'border-gray-200' }} focus:border-[#0284c7] focus:ring-2 focus:ring-[#0284c7]/20 bg-white appearance-none transition-all duration-300">
                                    <option value="">-- Pilih Kelas --</option>
                                    @foreach($kelasList as $kelas)
                                        <option value="{{ $kelas->id }}" {{ old('kelas_id', $student->kelas_id) == $kelas->id ? 'selected' : '' }}>
                                            {{ $kelas->nama_kelas }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-400 pointer-events-none">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </div>
                            </div>
                            @error('kelas_id')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Tempat Lahir -->
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">
                                Tempat Lahir
                            </label>
                            <input type="text"
                                   name="birth_place"
                                   value="{{ old('birth_place', $student->birth_place) }}"
                                   placeholder="Kota/Kabupaten tempat lahir"
                                   class="w-full px-4 py-3 rounded-xl border {{ $errors->has('birth_place') ? 'border-red-400 bg-red-50' : 'border-gray-200' }} focus:border-[#0284c7] focus:ring-2 focus:ring-[#0284c7]/20 bg-white transition-all duration-300">
                            @error('birth_place')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Tanggal Lahir -->
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">
                                Tanggal Lahir
                            </label>
                            <input type="date"
                                   name="birth_date"
                                   value="{{ old('birth_date', $student->birth_date?->format('Y-m-d')) }}"
                                   class="w-full px-4 py-3 rounded-xl border {{ $errors->has('birth_date') ? 'border-red-400 bg-red-50' : 'border-gray-200' }} focus:border-[#0284c7] focus:ring-2 focus:ring-[#0284c7]/20 bg-white transition-all duration-300">
                            @error('birth_date')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Alamat -->
                        <div class="md:col-span-2">
                            <label class="block text-sm font-bold text-gray-700 mb-2">
                                Alamat
                            </label>
                            <textarea name="address"
                                      rows="3"
                                      placeholder="Alamat lengkap siswa"
                                      class="w-full px-4 py-3 rounded-xl border {{ $errors->has('address') ? 'border-red-400 bg-red-50' : 'border-gray-200' }} focus:border-[#0284c7] focus:ring-2 focus:ring-[#0284c7]/20 bg-white transition-all duration-300 resize-none">{{ old('address', $student->address) }}</textarea>
                            @error('address')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Agama -->
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">
                                Agama
                            </label>
                            <div class="relative">
                                <select name="agama"
                                        class="w-full px-4 py-3 rounded-xl border {{ $errors->has('agama') ? 'border-red-400 bg-red-50' : 'border-gray-200' }} focus:border-[#0284c7] focus:ring-2 focus:ring-[#0284c7]/20 bg-white appearance-none transition-all duration-300">
                                    <option value="">-- Pilih Agama --</option>
                                    <option value="Islam" {{ old('agama', $student->agama) == 'Islam' ? 'selected' : '' }}>Islam</option>
                                    <option value="Kristen" {{ old('agama', $student->agama) == 'Kristen' ? 'selected' : '' }}>Kristen</option>
                                    <option value="Katolik" {{ old('agama', $student->agama) == 'Katolik' ? 'selected' : '' }}>Katolik</option>
                                    <option value="Hindu" {{ old('agama', $student->agama) == 'Hindu' ? 'selected' : '' }}>Hindu</option>
                                    <option value="Buddha" {{ old('agama', $student->agama) == 'Buddha' ? 'selected' : '' }}>Buddha</option>
                                    <option value="Konghucu" {{ old('agama', $student->agama) == 'Konghucu' ? 'selected' : '' }}>Konghucu</option>
                                </select>
                                <div class="absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-400 pointer-events-none">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </div>
                            </div>
                            @error('agama')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Tahun Masuk -->
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">
                                Tahun Masuk
                            </label>
                            <input type="number"
                                   name="tahun_masuk"
                                   value="{{ old('tahun_masuk', $student->tahun_masuk) }}"
                                   min="1990"
                                   max="{{ date('Y') + 1 }}"
                                   placeholder="Contoh: 2024"
                                   class="w-full px-4 py-3 rounded-xl border {{ $errors->has('tahun_masuk') ? 'border-red-400 bg-red-50' : 'border-gray-200' }} focus:border-[#0284c7] focus:ring-2 focus:ring-[#0284c7]/20 bg-white transition-all duration-300">
                            @error('tahun_masuk')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- No HP Orang Tua -->
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">
                                No HP Orang Tua
                            </label>
                            <input type="text"
                                   name="parent_phone"
                                   value="{{ old('parent_phone', $student->parent_phone) }}"
                                   placeholder="Contoh: 08123456789"
                                   class="w-full px-4 py-3 rounded-xl border {{ $errors->has('parent_phone') ? 'border-red-400 bg-red-50' : 'border-gray-200' }} focus:border-[#0284c7] focus:ring-2 focus:ring-[#0284c7]/20 bg-white transition-all duration-300">
                            @error('parent_phone')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                    </div>
                </div>

                <!-- Note about status -->
                <div class="mx-6 mb-6 flex items-start gap-3 bg-amber-50 border border-amber-200 rounded-xl px-4 py-3">
                    <svg class="w-5 h-5 text-amber-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <p class="text-sm text-amber-700">
                        <span class="font-bold">Catatan:</span> Perubahan status siswa dilakukan melalui halaman
                        <a href="{{ route('students.ubah-status', $student->id) }}" class="font-bold underline hover:text-amber-900 transition-colors">Ubah Status</a>
                        agar setiap perubahan tercatat dalam riwayat.
                    </p>
                </div>

                <!-- Form Actions -->
                <div class="px-6 py-4 bg-gradient-to-r from-gray-50 to-white border-t border-gray-100 flex flex-col sm:flex-row justify-end gap-3">
                    <a href="{{ route('students.index') }}"
                       class="flex items-center justify-center gap-2 px-6 py-3 bg-gradient-to-r from-gray-100 to-gray-200 text-gray-700 rounded-xl hover:from-gray-200 hover:to-gray-300 transition-all duration-300 font-medium">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        Batal
                    </a>
                    <button type="submit"
                            class="group relative flex items-center justify-center gap-2 px-8 py-3 bg-gradient-to-r from-[#0284c7] to-[#0ea5e9] hover:from-[#027ab8] hover:to-[#0d93d7] text-white rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 font-bold overflow-hidden">
                        <div class="absolute inset-0 bg-white/10 transform -translate-x-full group-hover:translate-x-0 transition-transform duration-700"></div>
                        <svg class="w-5 h-5 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="relative z-10">Simpan Perubahan</span>
                    </button>
                </div>

            </div>
        </form>
    </div>

    <style>
    input:focus, select:focus, textarea:focus {
        outline: none;
        box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.1);
    }
    </style>
</x-app-layout>
