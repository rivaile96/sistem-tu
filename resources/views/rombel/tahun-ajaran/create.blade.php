<x-app-layout>


<div class="p-6">
    {{-- Header --}}
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('tahun-ajaran.index') }}"
           class="text-slate-400 hover:text-slate-600 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </a>
        <div>
            <h1 class="text-xl font-semibold text-slate-800">Tambah Tahun Ajaran</h1>
            <p class="text-sm text-slate-500 mt-0.5">Buat tahun ajaran baru</p>
        </div>
    </div>

    {{-- Form Card --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6 max-w-lg">
        <form action="{{ route('tahun-ajaran.store') }}" method="POST">
            @csrf

            {{-- Nama --}}
            <div class="mb-5">
                <label for="nama" class="block text-sm font-medium text-slate-700 mb-1.5">
                    Nama Tahun Ajaran <span class="text-red-500">*</span>
                </label>
                <input
                    type="text"
                    id="nama"
                    name="nama"
                    value="{{ old('nama') }}"
                    placeholder="2025/2026"
                    class="w-full border {{ $errors->has('nama') ? 'border-red-400 bg-red-50' : 'border-slate-300' }} rounded-lg px-3 py-2 text-sm text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent transition"
                >
                @error('nama')
                    <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Tanggal Mulai --}}
            <div class="mb-5">
                <label for="tanggal_mulai" class="block text-sm font-medium text-slate-700 mb-1.5">
                    Tanggal Mulai <span class="text-red-500">*</span>
                </label>
                <input
                    type="date"
                    id="tanggal_mulai"
                    name="tanggal_mulai"
                    value="{{ old('tanggal_mulai') }}"
                    class="w-full border {{ $errors->has('tanggal_mulai') ? 'border-red-400 bg-red-50' : 'border-slate-300' }} rounded-lg px-3 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent transition"
                >
                @error('tanggal_mulai')
                    <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Tanggal Selesai --}}
            <div class="mb-5">
                <label for="tanggal_selesai" class="block text-sm font-medium text-slate-700 mb-1.5">
                    Tanggal Selesai <span class="text-red-500">*</span>
                </label>
                <input
                    type="date"
                    id="tanggal_selesai"
                    name="tanggal_selesai"
                    value="{{ old('tanggal_selesai') }}"
                    class="w-full border {{ $errors->has('tanggal_selesai') ? 'border-red-400 bg-red-50' : 'border-slate-300' }} rounded-lg px-3 py-2 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent transition"
                >
                @error('tanggal_selesai')
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
                        {{ old('is_aktif') ? 'checked' : '' }}
                        class="w-4 h-4 rounded border-slate-300 text-sky-600 focus:ring-sky-500 cursor-pointer"
                    >
                    <span class="text-sm text-slate-700 group-hover:text-slate-900 transition-colors">
                        Jadikan tahun ajaran aktif
                    </span>
                </label>
                <p class="mt-1 text-xs text-slate-400 ml-7">Mencentang ini akan menonaktifkan tahun ajaran lain yang sedang aktif.</p>
            </div>

            {{-- Actions --}}
            <div class="flex items-center gap-3 pt-2 border-t border-slate-100">
                <button type="submit"
                        class="bg-sky-600 hover:bg-sky-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                    Simpan
                </button>
                <a href="{{ route('tahun-ajaran.index') }}"
                   class="bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>
</x-app-layout>
