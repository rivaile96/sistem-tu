<x-app-layout>
    <!-- Flash Messages -->
    @if(session('success'))
    <div class="mb-6 flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-800 px-5 py-4 rounded-xl shadow-sm">
        <svg class="w-5 h-5 text-emerald-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <span class="font-medium">{{ session('success') }}</span>
    </div>
    @endif

    @if(session('error'))
    <div class="mb-6 flex items-center gap-3 bg-red-50 border border-red-200 text-red-800 px-5 py-4 rounded-xl shadow-sm">
        <svg class="w-5 h-5 text-red-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <span class="font-medium">{{ session('error') }}</span>
    </div>
    @endif

    <!-- Header Section -->
    <div class="mb-8">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <div class="p-2 bg-gradient-to-br from-[#0284c7]/10 to-[#0ea5e9]/10 rounded-xl">
                        <svg class="w-7 h-7 text-[#0284c7]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                        </svg>
                    </div>
                    <h1 class="text-3xl font-bold text-gray-900">Tambah Kelas Baru</h1>
                </div>
                <!-- Breadcrumb -->
                <nav class="flex items-center gap-2 ml-12 text-sm text-gray-500">
                    <a href="{{ route('dashboard') }}" class="hover:text-[#0284c7] transition-colors">Dashboard</a>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                    <a href="{{ route('kelas.index') }}" class="hover:text-[#0284c7] transition-colors">Master Kelas</a>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                    <span class="text-gray-700 font-medium">Tambah Kelas</span>
                </nav>
            </div>

            <a href="{{ route('kelas.index') }}"
               class="flex items-center gap-2 bg-white border border-gray-200 text-gray-700 px-4 py-2.5 rounded-xl hover:border-[#0284c7]/50 hover:shadow-md transition-all duration-300 font-medium shadow-sm text-sm">
                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                <span>Kembali</span>
            </a>
        </div>
    </div>

    <!-- Validation Errors -->
    @if($errors->any())
    <div class="mb-6 bg-red-50 border border-red-200 rounded-xl p-4">
        <div class="flex items-start gap-3">
            <svg class="w-5 h-5 text-red-500 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <div>
                <p class="font-bold text-red-800 mb-1">Terdapat beberapa kesalahan:</p>
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach($errors->all() as $error)
                    <li class="text-sm text-red-700">{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
    @endif

    <!-- Form Card -->
    <div class="bg-gradient-to-br from-white to-gray-50 rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
        <!-- Card Header -->
        <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-[#0284c7]/10 rounded-lg">
                    <svg class="w-5 h-5 text-[#0284c7]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                </div>
                <div>
                    <h3 class="font-bold text-gray-900">Informasi Kelas</h3>
                    <p class="text-xs text-gray-500">Jenjang: <span class="font-bold text-[#0284c7]">{{ $jenjang }}</span> · Tingkat {{ $tingkatMin }}–{{ $tingkatMax }}</p>
                </div>
            </div>
        </div>

        <!-- Form Body -->
        <form action="{{ route('kelas.store') }}" method="POST" class="p-6 space-y-6">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Nama Kelas -->
                <div class="lg:col-span-2">
                    <label for="nama_kelas" class="block text-sm font-bold text-gray-700 mb-1.5">
                        Nama Kelas <span class="text-red-500">*</span>
                    </label>
                    <input type="text"
                           id="nama_kelas"
                           name="nama_kelas"
                           value="{{ old('nama_kelas') }}"
                           placeholder="Contoh: X IPA 1, VII A, 4A"
                           class="w-full px-4 py-3 rounded-xl border {{ $errors->has('nama_kelas') ? 'border-red-400 bg-red-50 focus:border-red-500 focus:ring-red-200' : 'border-gray-200 focus:border-[#0284c7] focus:ring-[#0284c7]/20' }} focus:ring-2 transition-all duration-300 text-sm bg-white">
                    @error('nama_kelas')
                    <p class="mt-1.5 text-xs text-red-600 flex items-center gap-1">
                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        {{ $message }}
                    </p>
                    @enderror
                </div>

                <!-- Tingkat -->
                <div>
                    <label for="tingkat" class="block text-sm font-bold text-gray-700 mb-1.5">
                        Tingkat Kelas <span class="text-red-500">*</span>
                    </label>
                    <input type="number"
                           id="tingkat"
                           name="tingkat"
                           value="{{ old('tingkat') }}"
                           min="{{ $tingkatMin }}"
                           max="{{ $tingkatMax }}"
                           placeholder="Masukkan tingkat ({{ $tingkatMin }}–{{ $tingkatMax }})"
                           class="w-full px-4 py-3 rounded-xl border {{ $errors->has('tingkat') ? 'border-red-400 bg-red-50 focus:border-red-500 focus:ring-red-200' : 'border-gray-200 focus:border-[#0284c7] focus:ring-[#0284c7]/20' }} focus:ring-2 transition-all duration-300 text-sm bg-white">
                    <p class="mt-1 text-xs text-gray-400">Rentang valid: {{ $tingkatMin }}–{{ $tingkatMax }} untuk jenjang {{ $jenjang }}</p>
                    @error('tingkat')
                    <p class="mt-1 text-xs text-red-600 flex items-center gap-1">
                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        {{ $message }}
                    </p>
                    @enderror
                </div>

                <!-- Jurusan -->
                <div>
                    <label for="jurusan" class="block text-sm font-bold text-gray-700 mb-1.5">
                        Jurusan
                        <span class="text-xs font-normal text-gray-400 ml-1">(opsional)</span>
                    </label>
                    <input type="text"
                           id="jurusan"
                           name="jurusan"
                           value="{{ old('jurusan') }}"
                           placeholder="Contoh: IPA, IPS, RPL, TKJ — kosongkan jika tidak ada jurusan"
                           class="w-full px-4 py-3 rounded-xl border {{ $errors->has('jurusan') ? 'border-red-400 bg-red-50 focus:border-red-500 focus:ring-red-200' : 'border-gray-200 focus:border-[#0284c7] focus:ring-[#0284c7]/20' }} focus:ring-2 transition-all duration-300 text-sm bg-white">
                    @error('jurusan')
                    <p class="mt-1.5 text-xs text-red-600 flex items-center gap-1">
                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        {{ $message }}
                    </p>
                    @enderror
                </div>

                <!-- Wali Kelas -->
                <div>
                    <label for="wali_kelas" class="block text-sm font-bold text-gray-700 mb-1.5">
                        Wali Kelas
                        <span class="text-xs font-normal text-gray-400 ml-1">(opsional)</span>
                    </label>
                    <input type="text"
                           id="wali_kelas"
                           name="wali_kelas"
                           value="{{ old('wali_kelas') }}"
                           placeholder="Nama guru wali kelas"
                           class="w-full px-4 py-3 rounded-xl border {{ $errors->has('wali_kelas') ? 'border-red-400 bg-red-50 focus:border-red-500 focus:ring-red-200' : 'border-gray-200 focus:border-[#0284c7] focus:ring-[#0284c7]/20' }} focus:ring-2 transition-all duration-300 text-sm bg-white">
                    @error('wali_kelas')
                    <p class="mt-1.5 text-xs text-red-600 flex items-center gap-1">
                        <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        {{ $message }}
                    </p>
                    @enderror
                </div>

                <!-- Status Aktif -->
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-1.5">Status Kelas</label>
                    <div class="flex items-center gap-3 p-4 bg-white border border-gray-200 rounded-xl hover:border-[#0284c7]/30 transition-all duration-300 cursor-pointer group"
                         onclick="document.getElementById('is_aktif').click()">
                        <!-- Toggle visual -->
                        <div class="relative">
                            <input type="checkbox"
                                   id="is_aktif"
                                   name="is_aktif"
                                   value="1"
                                   {{ old('is_aktif', '1') == '1' ? 'checked' : '' }}
                                   class="sr-only peer"
                                   onclick="event.stopPropagation()">
                            <div class="w-11 h-6 bg-gray-200 peer-checked:bg-[#0284c7] rounded-full transition-all duration-300 peer-focus:ring-2 peer-focus:ring-[#0284c7]/20"></div>
                            <div class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow transition-all duration-300 peer-checked:translate-x-5"></div>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-gray-700 group-hover:text-[#0284c7] transition-colors" id="toggle-label">
                                {{ old('is_aktif', '1') == '1' ? 'Aktif' : 'Nonaktif' }}
                            </p>
                            <p class="text-xs text-gray-400">Kelas yang aktif akan tampil di pilihan tagihan siswa</p>
                        </div>
                    </div>
                    @error('is_aktif')
                    <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Divider -->
            <div class="border-t border-gray-100 pt-6">
                <div class="flex flex-col sm:flex-row items-center justify-end gap-3">
                    <a href="{{ route('kelas.index') }}"
                       class="w-full sm:w-auto text-center px-6 py-2.5 bg-white border border-gray-200 text-gray-700 rounded-xl hover:border-gray-300 hover:bg-gray-50 transition-all duration-300 font-medium text-sm">
                        Batal
                    </a>
                    <button type="submit"
                            class="w-full sm:w-auto flex items-center justify-center gap-2 px-6 py-2.5 bg-gradient-to-r from-[#0284c7] to-[#0ea5e9] text-white rounded-xl hover:from-[#0369a1] hover:to-[#0284c7] transition-all duration-300 font-medium shadow-md hover:shadow-lg text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Simpan Kelas
                    </button>
                </div>
            </div>
        </form>
    </div>

    <style>
    input:focus, select:focus {
        outline: none;
        box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.1);
    }
    </style>

    <script>
    const checkbox = document.getElementById('is_aktif');
    const label = document.getElementById('toggle-label');

    checkbox.addEventListener('change', function() {
        label.textContent = this.checked ? 'Aktif' : 'Nonaktif';
    });
    </script>
</x-app-layout>
