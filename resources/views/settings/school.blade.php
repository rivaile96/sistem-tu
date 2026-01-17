<x-app-layout>
    <div class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            
            @if(session('success'))
            <div class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 rounded-r shadow-sm flex items-center gap-3">
                <div class="text-green-500">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div>
                    <p class="font-bold text-green-800">Berhasil!</p>
                    <p class="text-sm text-green-700">{{ session('success') }}</p>
                </div>
            </div>
            @endif

            <div class="bg-white shadow-xl sm:rounded-2xl overflow-hidden border border-gray-100">
                
                <div class="bg-gradient-to-r from-blue-600 to-cyan-500 px-8 py-8 relative overflow-hidden">
                    <div class="absolute top-0 right-0 -mr-10 -mt-10 w-40 h-40 bg-white opacity-10 rounded-full blur-2xl"></div>
                    <div class="absolute bottom-0 left-0 -ml-10 -mb-10 w-20 h-20 bg-white opacity-10 rounded-full blur-xl"></div>

                    <h2 class="text-3xl font-bold text-white flex items-center gap-3 relative z-10">
                        🏫 Identitas Sekolah
                    </h2>
                    <p class="text-blue-100 text-sm mt-2 relative z-10 max-w-2xl">
                        Lengkapi identitas sekolah, logo, dan tanda tangan digital. Data ini akan otomatis muncul pada Kop Surat, Kwitansi, dan Laporan Resmi.
                    </p>
                </div>

                <form action="{{ route('school.update') }}" method="POST" enctype="multipart/form-data" class="p-8">
                    @csrf
                    
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-10">
                        
                        <div class="lg:col-span-3 flex flex-col items-center border-b lg:border-b-0 lg:border-r border-gray-100 pb-8 lg:pb-0 lg:pr-6">
                            <label class="block text-sm font-bold text-gray-700 mb-4 text-center">Logo Sekolah</label>
                            
                            <div class="relative w-40 h-40 bg-gray-50 rounded-full border-4 border-white shadow-lg overflow-hidden group ring-2 ring-gray-100">
                                @if(isset($settings['school_logo']))
                                    <img src="{{ asset('storage/'.$settings['school_logo']) }}" class="w-full h-full object-cover transform transition group-hover:scale-110 duration-500">
                                @else
                                    <div class="flex items-center justify-center h-full text-gray-400 flex-col bg-gray-50">
                                        <svg class="w-10 h-10 mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                        <span class="text-[10px] uppercase font-bold tracking-wider">Upload Logo</span>
                                    </div>
                                @endif
                                
                                <label class="absolute inset-0 bg-blue-900/60 flex flex-col items-center justify-center opacity-0 group-hover:opacity-100 transition cursor-pointer backdrop-blur-sm">
                                    <svg class="w-8 h-8 text-white mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                    <span class="text-white text-xs font-bold">Ganti Logo</span>
                                    <input type="file" name="school_logo" class="hidden" accept="image/*">
                                </label>
                            </div>
                            <p class="text-[11px] text-gray-400 mt-3 text-center">Format: PNG/JPG. Max: 2MB.<br>Disarankan bentuk persegi/bulat.</p>
                        </div>

                        <div class="lg:col-span-6 space-y-6">
                            
                            <div>
                                <label class="block font-bold text-gray-700 text-sm mb-2 flex items-center gap-2">
                                    <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                    Nama Sekolah
                                </label>
                                <input type="text" name="school_name" value="{{ $settings['school_name'] ?? '' }}" class="w-full rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500 bg-gray-50 transition-colors focus:bg-white" placeholder="Contoh: SDIT Kaffah Islamic School" required>
                            </div>

                            <div>
                                <label class="block font-bold text-gray-700 text-sm mb-2 flex items-center gap-2">
                                    <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                    Alamat Lengkap
                                </label>
                                <textarea name="school_address" rows="3" class="w-full rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500 bg-gray-50 transition-colors focus:bg-white">{{ $settings['school_address'] ?? '' }}</textarea>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div>
                                    <label class="block font-bold text-gray-700 text-sm mb-2 flex items-center gap-2">
                                        <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                                        No. Telepon
                                    </label>
                                    <input type="text" name="school_phone" value="{{ $settings['school_phone'] ?? '' }}" class="w-full rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500 bg-gray-50 transition-colors focus:bg-white">
                                </div>
                                
                                <div>
                                    <label class="block font-bold text-gray-700 text-sm mb-2 flex items-center gap-2">
                                        <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                        Bendahara / Ka. TU
                                    </label>
                                    <input type="text" name="head_of_admin" value="{{ $settings['head_of_admin'] ?? '' }}" class="w-full rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500 bg-gray-50 transition-colors focus:bg-white" placeholder="Nama Pejabat TTD">
                                </div>
                            </div>
                        </div>

                        <div class="lg:col-span-3 flex flex-col items-center border-t lg:border-t-0 lg:border-l border-gray-100 pt-8 lg:pt-0 lg:pl-6">
                            <label class="block text-sm font-bold text-gray-700 mb-4 text-center">Scan Tanda Tangan</label>
                            
                            <div class="relative w-full h-32 bg-white rounded-xl border-2 border-dashed border-gray-300 hover:border-blue-400 flex items-center justify-center overflow-hidden group transition-all">
                                @if(isset($settings['school_signature']))
                                    <img src="{{ asset('storage/'.$settings['school_signature']) }}" class="h-full object-contain p-2">
                                @else
                                    <div class="text-center text-gray-400 p-4">
                                        <svg class="w-8 h-8 mx-auto mb-1 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path></svg>
                                        <span class="text-[10px] font-medium">Belum ada TTD</span>
                                    </div>
                                @endif

                                <label class="absolute inset-0 bg-white/80 flex flex-col items-center justify-center opacity-0 group-hover:opacity-100 transition cursor-pointer backdrop-blur-sm">
                                    <svg class="w-6 h-6 text-blue-600 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                                    <span class="text-blue-600 text-xs font-bold">Upload File</span>
                                    <input type="file" name="school_signature" class="hidden" accept="image/*">
                                </label>
                            </div>
                            <p class="text-[11px] text-gray-400 mt-3 text-center">Wajib background transparan.<br>(Format: PNG)</p>
                        </div>

                    </div>

                    <div class="mt-10 pt-6 border-t border-gray-100 flex justify-end">
                        <button type="submit" class="bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-bold py-3 px-8 rounded-xl shadow-lg hover:shadow-xl transition-all transform hover:-translate-y-1 flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                            Simpan Perubahan
                        </button>
                    </div>

                </form>
            </div>
        </div>
    </div>
</x-app-layout>