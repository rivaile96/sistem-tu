<x-app-layout>
    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            
            @if(session('success'))
            <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative">
                <strong class="font-bold">Berhasil!</strong>
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
            @endif

            <div class="bg-white shadow-xl sm:rounded-2xl overflow-hidden border border-gray-100">
                
                <div class="bg-slate-800 px-8 py-6 border-b border-slate-700">
                    <h2 class="text-2xl font-bold text-white flex items-center gap-3">
                        🏫 Identitas Sekolah
                    </h2>
                    <p class="text-slate-400 text-sm mt-1">Data ini akan otomatis muncul di Kop Surat Kwitansi & Laporan.</p>
                </div>

                <form action="{{ route('school.update') }}" method="POST" enctype="multipart/form-data" class="p-8">
                    @csrf
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                        
                        <div class="md:col-span-1 flex flex-col items-center">
                            <label class="block text-sm font-bold text-gray-700 mb-3">Logo Sekolah</label>
                            
                            <div class="relative w-48 h-48 bg-gray-100 rounded-full border-4 border-white shadow-lg overflow-hidden group">
                                @if(isset($settings['school_logo']))
                                    <img src="{{ asset('storage/'.$settings['school_logo']) }}" class="w-full h-full object-cover">
                                @else
                                    <div class="flex items-center justify-center h-full text-gray-400 flex-col">
                                        <svg class="w-12 h-12 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                        <span class="text-xs">Belum ada logo</span>
                                    </div>
                                @endif
                                
                                <label class="absolute inset-0 bg-black/50 flex items-center justify-center opacity-0 group-hover:opacity-100 transition cursor-pointer">
                                    <span class="text-white text-xs font-bold">📷 Ganti Logo</span>
                                    <input type="file" name="school_logo" class="hidden" accept="image/*">
                                </label>
                            </div>
                            <p class="text-[10px] text-gray-400 mt-2 text-center">Format: PNG/JPG. Max: 2MB.</p>
                        </div>

                        <div class="md:col-span-2 space-y-5">
                            <div>
                                <label class="block font-bold text-gray-700 text-sm mb-1">Nama Sekolah</label>
                                <input type="text" name="school_name" value="{{ $settings['school_name'] ?? '' }}" class="w-full rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500" placeholder="Contoh: SMK Telkom Jakarta" required>
                            </div>

                            <div>
                                <label class="block font-bold text-gray-700 text-sm mb-1">Alamat Lengkap</label>
                                <textarea name="school_address" rows="3" class="w-full rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500" placeholder="Jl. Telekomunikasi No. 1...">{{ $settings['school_address'] ?? '' }}</textarea>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block font-bold text-gray-700 text-sm mb-1">No. Telepon</label>
                                    <input type="text" name="school_phone" value="{{ $settings['school_phone'] ?? '' }}" class="w-full rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                <div>
                                    <label class="block font-bold text-gray-700 text-sm mb-1">Bendahara / Ka. TU</label>
                                    <input type="text" name="head_of_admin" value="{{ $settings['head_of_admin'] ?? '' }}" class="w-full rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500" placeholder="Nama untuk Tanda Tangan">
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="mt-8 pt-6 border-t border-gray-100 flex justify-end">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-8 rounded-xl shadow-lg transition flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                            Simpan Perubahan
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</x-app-layout>