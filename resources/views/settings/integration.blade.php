<x-app-layout>
    <x-slot name="header">
        Integrasi Kesiswaan
    </x-slot>

    <div class="space-y-6">
        
        <!-- HEADER -->
        <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
            <div>
                <h1 class="flex items-center gap-3 text-2xl font-black text-slate-800">
                    <div class="bg-[#178BC1] p-2.5 rounded-xl text-white shadow-sm shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path>
                            <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path>
                        </svg>
                    </div>
                    Integrasi Database Kesiswaan (PPDB)
                </h1>
                <p class="mt-1 text-xs text-slate-500 font-medium">
                    Hubungkan Sistem TU dengan basis data kesiswaan terpusat untuk sinkronisasi profil siswa, NISN, dan rombel.
                </p>
            </div>
        </div>

        <!-- MAIN GRID (SPMB MATCHING SOLID) -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            
            <!-- LEFT: DATABASE CONFIGURATION -->
            <div class="lg:col-span-7 bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="p-6 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                    <div>
                        <h2 class="text-sm font-bold text-slate-800">Koneksi Database Remote</h2>
                        <p class="text-xs text-slate-400 mt-0.5">Parameter koneksi ke instance MySQL / PostgreSQL kesiswaan.</p>
                    </div>
                    <span class="px-2.5 py-1 text-[10px] font-bold rounded-lg bg-blue-50 text-[#178BC1] border border-blue-200">
                        Secure Connection
                    </span>
                </div>

                <form action="{{ route('settings.integration.update') }}" method="POST" class="p-6 space-y-4">
                    @csrf
                    
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            IP Address / Host Server
                        </label>
                        <input type="text" 
                               name="kesiswaan_host" 
                               value="{{ $settings['kesiswaan_host'] ?? 'localhost' }}" 
                               placeholder="Contoh: 127.0.0.1 / db.kaffah.id"
                               class="w-full px-3.5 py-2.5 text-xs font-medium text-slate-800 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#178BC1]/20 focus:border-[#178BC1] transition-all">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Port Database
                            </label>
                            <input type="text" 
                                   name="kesiswaan_port" 
                                   value="{{ $settings['kesiswaan_port'] ?? '3306' }}" 
                                   class="w-full px-3.5 py-2.5 text-xs font-medium text-slate-800 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#178BC1]/20 focus:border-[#178BC1] transition-all">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Nama Database
                            </label>
                            <input type="text" 
                                   name="kesiswaan_db" 
                                   value="{{ $settings['kesiswaan_db'] ?? 'db_kesiswaan' }}" 
                                   class="w-full px-3.5 py-2.5 text-xs font-medium text-slate-800 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#178BC1]/20 focus:border-[#178BC1] transition-all">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Username DB
                            </label>
                            <input type="text" 
                                   name="kesiswaan_user" 
                                   value="{{ $settings['kesiswaan_user'] ?? 'root' }}" 
                                   class="w-full px-3.5 py-2.5 text-xs font-medium text-slate-800 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#178BC1]/20 focus:border-[#178BC1] transition-all">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                                Password DB
                            </label>
                            <input type="password" 
                                   name="kesiswaan_pass" 
                                   value="{{ $settings['kesiswaan_pass'] ?? '' }}" 
                                   placeholder="••••••••"
                                   class="w-full px-3.5 py-2.5 text-xs font-medium text-slate-800 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#178BC1]/20 focus:border-[#178BC1] transition-all">
                        </div>
                    </div>

                    <div class="pt-4 border-t border-slate-100 flex justify-end">
                        <button type="submit" 
                                class="inline-flex items-center gap-2 bg-[#178BC1] hover:bg-[#126da3] text-white px-5 py-2.5 rounded-xl font-bold text-xs shadow-sm transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
                                <polyline points="17 21 17 13 7 13 7 21"></polyline>
                                <polyline points="7 3 7 8 15 8"></polyline>
                            </svg>
                            <span>Simpan Konfigurasi</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- RIGHT: SYNC ACTION & MONITORING -->
            <div class="lg:col-span-5 space-y-6">
                
                <!-- SYNC TRIGGER CARD -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 space-y-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-50 text-[#178BC1] border border-blue-100 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                <path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-800">Sinkronisasi Data Siswa</h3>
                            <p class="text-[11px] text-slate-400">Tarik data terbaru dari database kesiswaan.</p>
                        </div>
                    </div>

                    <p class="text-xs text-slate-500 leading-relaxed">
                        Data siswa yang belum ada akan otomatis dibuatkan profil, dan siswa yang sudah ada akan diperbarui NISN serta kelasnya.
                    </p>

                    <form id="syncForm" action="{{ route('settings.integration.sync') }}" method="POST">
                        @csrf
                        <button type="button" 
                                onclick="confirmSync()"
                                class="w-full inline-flex items-center justify-center gap-2 bg-[#178BC1] hover:bg-[#126da3] text-white py-3 px-4 rounded-xl font-bold text-xs shadow-sm transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                <path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.57-8.38l5.67-5.67"></path>
                            </svg>
                            <span>Tarik Data Kesiswaan Sekarang</span>
                        </button>
                    </form>
                </div>

                <!-- SYNC CHECKLIST INFO -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 space-y-3">
                    <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider mb-2">Data Yang Disinkronkan</h4>
                    
                    <div class="space-y-2 text-xs text-slate-600">
                        <div class="flex items-center gap-2.5">
                            <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                            <span>Nama Lengkap & NISN Siswa</span>
                        </div>
                        <div class="flex items-center gap-2.5">
                            <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                            <span>Rombongan Belajar (Kelas) & Jenjang</span>
                        </div>
                        <div class="flex items-center gap-2.5">
                            <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                            <span>Nomor WhatsApp Wali / Orang Tua</span>
                        </div>
                        <div class="flex items-center gap-2.5">
                            <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                            <span>Status Keaktifan Akademik</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>

    <!-- SCRIPT CONFIRM SYNC -->
    <script>
    function confirmSync() {
        Swal.fire({
            title: 'Mulai Sinkronisasi Data?',
            text: 'Data kesiswaan lokal akan diselaraskan dengan database PPDB & Kesiswaan.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#178BC1',
            cancelButtonColor: '#94a3b8',
            confirmButtonText: 'Ya, Sync Sekarang',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('syncForm').submit();
            }
        });
    }
    </script>
</x-app-layout>
