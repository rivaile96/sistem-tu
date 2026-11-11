<x-app-layout>
    <x-slot name="header">
        Identitas Sekolah
    </x-slot>

    <div class="space-y-6">
        
        <!-- HEADER -->
        <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
            <div>
                <h1 class="flex items-center gap-3 text-2xl font-black text-slate-800">
                    <div class="bg-[#178BC1] p-2.5 rounded-xl text-white shadow-sm shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <rect x="4" y="2" width="16" height="20" rx="2" ry="2"></rect>
                            <line x1="9" y1="22" x2="9" y2="22.01"></line><line x1="15" y1="22" x2="15" y2="22.01"></line>
                            <line x1="9" y1="6" x2="9" y2="6.01"></line><line x1="15" y1="6" x2="15" y2="6.01"></line>
                        </svg>
                    </div>
                    Identitas & Kop Surat Resmi
                </h1>
                <p class="mt-1 text-xs text-slate-500 font-medium">
                    Atur nama sekolah, alamat, logo resmi, dan tanda tangan digital untuk kop surat dan kwitansi pembayaran.
                </p>
            </div>
        </div>

        <!-- FORM CARD (SPMB MATCHING SOLID) -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
            <form action="{{ route('school.update') }}" method="POST" enctype="multipart/form-data" class="p-6 md:p-8 space-y-8">
                @csrf
                
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                    
                    <!-- LOGO SECTION -->
                    <div class="lg:col-span-3">
                        <div class="p-6 bg-slate-50 rounded-2xl border border-slate-200 text-center h-full flex flex-col items-center justify-between">
                            <div>
                                <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider mb-1">Logo Sekolah</h3>
                                <p class="text-[11px] text-slate-400 mb-4">Format PNG / JPG (Maks. 2MB)</p>
                                
                                <div class="relative w-36 h-36 mx-auto rounded-2xl border-2 border-dashed border-slate-300 flex items-center justify-center overflow-hidden bg-white hover:border-[#178BC1] transition-colors group cursor-pointer">
                                    @if(isset($settings['school_logo']))
                                        <img src="{{ asset('storage/'.$settings['school_logo']) }}" class="w-full h-full object-contain p-2">
                                    @else
                                        <div class="flex flex-col items-center text-slate-300">
                                            <svg class="w-10 h-10 mb-1" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                                <circle cx="8.5" cy="8.5" r="1.5"></circle>
                                                <polyline points="21 15 16 10 5 21"></polyline>
                                            </svg>
                                            <span class="text-[10px] font-bold text-slate-400">Pilih Logo</span>
                                        </div>
                                    @endif

                                    <label class="absolute inset-0 bg-slate-900/60 opacity-0 group-hover:opacity-100 flex flex-col items-center justify-center transition-opacity cursor-pointer text-white">
                                        <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                            <polyline points="17 8 12 3 7 8"></polyline>
                                            <line x1="12" y1="3" x2="12" y2="15"></line>
                                        </svg>
                                        <span class="text-[10px] font-bold">Ganti Logo</span>
                                        <input type="file" name="school_logo" class="hidden" accept="image/*">
                                    </label>
                                </div>
                            </div>

                            <p class="text-[10px] text-slate-400 mt-4">
                                Muncul di kop surat dan kwitansi cetak.
                            </p>
                        </div>
                    </div>

                    <!-- DETAILS SECTION -->
                    <div class="lg:col-span-6 space-y-4">
                        
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Nama Lengkap Lembaga / Sekolah <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" 
                                   name="school_name" 
                                   value="{{ $settings['school_name'] ?? '' }}" 
                                   required
                                   placeholder="Contoh: SDIT Kaffah Islamic School"
                                   class="w-full px-3.5 py-2.5 text-xs font-medium text-slate-800 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#178BC1]/20 focus:border-[#178BC1] transition-all">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Alamat Lengkap & Kontak Resmi <span class="text-rose-500">*</span>
                            </label>
                            <textarea name="school_address" 
                                      rows="3" 
                                      placeholder="Alamat jalan, kelurahan, kecamatan, kota..."
                                      class="w-full px-3.5 py-2 text-xs font-medium text-slate-800 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#178BC1]/20 focus:border-[#178BC1] transition-all">{{ $settings['school_address'] ?? '' }}</textarea>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                    No. Telepon / WhatsApp
                                </label>
                                <input type="text" 
                                       name="school_phone" 
                                       value="{{ $settings['school_phone'] ?? '' }}" 
                                       placeholder="Contoh: 0812-XXXX-XXXX"
                                       class="w-full px-3.5 py-2.5 text-xs font-medium text-slate-800 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#178BC1]/20 focus:border-[#178BC1] transition-all">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                    Nama Kepala TU / Bendahara
                                </label>
                                <input type="text" 
                                       name="head_of_admin" 
                                       value="{{ $settings['head_of_admin'] ?? '' }}" 
                                       placeholder="Nama Pejabat Penandatangan"
                                       class="w-full px-3.5 py-2.5 text-xs font-medium text-slate-800 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#178BC1]/20 focus:border-[#178BC1] transition-all">
                            </div>
                        </div>

                    </div>

                    <!-- SIGNATURE SECTION -->
                    <div class="lg:col-span-3">
                        <div class="p-6 bg-slate-50 rounded-2xl border border-slate-200 text-center h-full flex flex-col items-center justify-between">
                            <div>
                                <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider mb-1">Tanda Tangan Digital</h3>
                                <p class="text-[11px] text-slate-400 mb-4">PNG Transparan (Maks. 1MB)</p>
                                
                                <div class="relative w-36 h-28 mx-auto rounded-2xl border-2 border-dashed border-slate-300 flex items-center justify-center overflow-hidden bg-white hover:border-[#178BC1] transition-colors group cursor-pointer">
                                    @if(isset($settings['school_signature']))
                                        <img src="{{ asset('storage/'.$settings['school_signature']) }}" class="w-full h-full object-contain p-2">
                                    @else
                                        <div class="flex flex-col items-center text-slate-300">
                                            <svg class="w-8 h-8 mb-1" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                                <path d="M12 20h9"></path>
                                                <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path>
                                            </svg>
                                            <span class="text-[10px] font-bold text-slate-400">Pilih TTD</span>
                                        </div>
                                    @endif

                                    <label class="absolute inset-0 bg-slate-900/60 opacity-0 group-hover:opacity-100 flex flex-col items-center justify-center transition-opacity cursor-pointer text-white">
                                        <svg class="w-5 h-5 mb-1" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                            <polyline points="17 8 12 3 7 8"></polyline>
                                            <line x1="12" y1="3" x2="12" y2="15"></line>
                                        </svg>
                                        <span class="text-[10px] font-bold">Ganti TTD</span>
                                        <input type="file" name="school_signature" class="hidden" accept="image/*">
                                    </label>
                                </div>
                            </div>

                            <p class="text-[10px] text-slate-400 mt-4">
                                Dicetak otomatis pada stempel kwitansi.
                            </p>
                        </div>
                    </div>

                </div>

                <div class="pt-6 border-t border-slate-100 flex justify-end">
                    <button type="submit" 
                            class="inline-flex items-center gap-2 bg-[#178BC1] hover:bg-[#126da3] text-white px-6 py-2.5 rounded-xl font-bold text-xs shadow-sm transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                            <polyline points="17 21 17 13 7 13 7 21"></polyline>
                            <polyline points="7 3 7 8 15 8"></polyline>
                        </svg>
                        <span>Simpan Pengaturan Sekolah</span>
                    </button>
                </div>
            </form>
        </div>

    </div>
</x-app-layout>
