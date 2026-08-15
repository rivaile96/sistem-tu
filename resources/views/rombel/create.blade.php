<x-app-layout>


<div class="p-6">
    {{-- Header --}}
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('rombel.index') }}"
           class="text-slate-400 hover:text-slate-600 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </a>
        <div>
            <h1 class="text-xl font-semibold text-slate-800">Tambah Rombel</h1>
            <p class="text-sm text-slate-500 mt-0.5">Buat rombongan belajar baru</p>
        </div>
    </div>

    {{-- No active tahun ajaran warning --}}
    @if(!$tahunAjaranAktif && $semuaTahunAjaran->isEmpty())
        <div class="bg-amber-50 border border-amber-200 text-amber-800 rounded-lg p-4 mb-6 flex items-start gap-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-amber-500 mt-0.5 shrink-0" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
            </svg>
            <div>
                <p class="text-sm font-medium">Belum ada tahun ajaran</p>
                <p class="text-xs mt-0.5">
                    Silakan <a href="{{ route('tahun-ajaran.create') }}" class="underline font-medium">buat tahun ajaran</a> terlebih dahulu.
                </p>
            </div>
        </div>
    @endif

    {{-- Form Card --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6 max-w-lg">
        <form action="{{ route('rombel.store') }}" method="POST">
            @csrf

            {{-- Tahun Ajaran --}}
            <div class="mb-5">
                <label for="tahun_ajaran_id" class="block text-sm font-medium text-slate-700 mb-1.5">
                    Tahun Ajaran <span class="text-red-500">*</span>
                </label>
                <select
                    id="tahun_ajaran_id"
                    name="tahun_ajaran_id"
                    class="w-full border {{ $errors->has('tahun_ajaran_id') ? 'border-red-400 bg-red-50' : 'border-slate-300' }} rounded-lg px-3 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent transition bg-white"
                >
                    <option value="">-- Pilih Tahun Ajaran --</option>
                    @foreach($semuaTahunAjaran as $ta)
                        <option value="{{ $ta->id }}"
                            {{ old('tahun_ajaran_id', $tahunAjaranAktif?->id) == $ta->id ? 'selected' : '' }}>
                            {{ $ta->nama }}{{ $ta->is_aktif ? ' (Aktif)' : '' }}
                        </option>
                    @endforeach
                </select>
                @error('tahun_ajaran_id')
                    <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Kelas --}}
            <div class="mb-5">
                <label for="kelas_id" class="block text-sm font-medium text-slate-700 mb-1.5">
                    Kelas <span class="text-red-500">*</span>
                </label>
                <select
                    id="kelas_id"
                    name="kelas_id"
                    class="w-full border {{ $errors->has('kelas_id') ? 'border-red-400 bg-red-50' : 'border-slate-300' }} rounded-lg px-3 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent transition bg-white"
                >
                    <option value="">-- Pilih Kelas --</option>
                    @foreach($kelasAktif as $kelas)
                        <option value="{{ $kelas->id }}" {{ old('kelas_id') == $kelas->id ? 'selected' : '' }}>
                            {{ $kelas->nama_kelas }}
                        </option>
                    @endforeach
                </select>
                @error('kelas_id')
                    <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Nama Rombel --}}
            <div class="mb-5">
                <label for="nama_rombel" class="block text-sm font-medium text-slate-700 mb-1.5">
                    Nama Rombel <span class="text-red-500">*</span>
                </label>
                <input
                    type="text"
                    id="nama_rombel"
                    name="nama_rombel"
                    value="{{ old('nama_rombel') }}"
                    placeholder="contoh: X RPL 1"
                    class="w-full border {{ $errors->has('nama_rombel') ? 'border-red-400 bg-red-50' : 'border-slate-300' }} rounded-lg px-3 py-2 text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent transition"
                >
                @error('nama_rombel')
                    <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Wali Kelas --}}
            <div class="mb-5">
                <label for="wali_kelas" class="block text-sm font-medium text-slate-700 mb-1.5">
                    Wali Kelas
                    <span class="text-slate-400 font-normal text-xs ml-1">opsional</span>
                </label>
                <input
                    type="text"
                    id="wali_kelas"
                    name="wali_kelas"
                    value="{{ old('wali_kelas') }}"
                    placeholder="Nama wali kelas"
                    class="w-full border {{ $errors->has('wali_kelas') ? 'border-red-400 bg-red-50' : 'border-slate-300' }} rounded-lg px-3 py-2 text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent transition"
                >
                @error('wali_kelas')
                    <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Is Aktif --}}
            <div class="mb-6">
                <label class="flex items-center gap-3 cursor-pointer group">
                    <input
                        type="checkbox"
                        name="is_aktif"
                        id="is_aktif"
                        value="1"
                        {{ old('is_aktif', '1') ? 'checked' : '' }}
                        class="w-4 h-4 rounded border-slate-300 text-sky-600 focus:ring-sky-500 cursor-pointer"
                    >
                    <span class="text-sm text-slate-700 group-hover:text-slate-900 transition-colors">
                        Rombel aktif
                    </span>
                </label>
            </div>

            {{-- Actions --}}
            <div class="flex items-center gap-3 pt-2 border-t border-slate-100">
                <button type="submit"
                        class="bg-sky-600 hover:bg-sky-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                    Simpan
                </button>
                <a href="{{ route('rombel.index') }}"
                   class="bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>
</x-app-layout>
