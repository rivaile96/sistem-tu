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
            <h1 class="text-xl font-semibold text-slate-800">Edit Rombel</h1>
            <p class="text-sm text-slate-500 mt-0.5">{{ $rombel->nama_rombel }}</p>
        </div>
    </div>

    {{-- Form Card --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6 max-w-lg">
        <form action="{{ route('rombel.update', $rombel) }}" method="POST">
            @csrf
            @method('PUT')

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
                            {{ old('tahun_ajaran_id', $rombel->tahun_ajaran_id) == $ta->id ? 'selected' : '' }}>
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
                        <option value="{{ $kelas->id }}"
                            {{ old('kelas_id', $rombel->kelas_id) == $kelas->id ? 'selected' : '' }}>
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
                    value="{{ old('nama_rombel', $rombel->nama_rombel) }}"
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
                    value="{{ old('wali_kelas', $rombel->wali_kelas) }}"
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
                        {{ old('is_aktif', $rombel->is_aktif) ? 'checked' : '' }}
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
                    Update
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
