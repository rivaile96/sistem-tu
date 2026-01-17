<x-app-layout>
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div></div></div>
        
        <!-- Header Section -->
        <div class="mb-10">
            <div class="flex items-center gap-4 mb-4">
                <div class="p-3 bg-gradient-to-br from-[#0284c7]/10 to-[#0ea5e9]/10 rounded-xl">
                    <svg class="w-8 h-8 text-[#0284c7]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                    </svg>
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Integrasi Kesiswaan</h1>
                    <p class="text-gray-600 mt-2 max-w-2xl">Hubungkan Sistem TU dengan Database Kesiswaan untuk sinkronisasi data siswa secara otomatis dan real-time.</p>
                </div>
            </div>
        </div>

        <!-- Main Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            
            <!-- Database Configuration Card -->
            <div class="bg-gradient-to-br from-white to-gray-50 rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
                <div class="bg-gradient-to-r from-[#0284c7] to-[#0ea5e9] p-6">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-white/20 rounded-lg backdrop-blur-sm">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                        </div>
                        <h2 class="text-xl font-bold text-white">Koneksi Database Remote</h2>
                    </div>
                    <p class="text-blue-100 text-sm mt-2">Konfigurasi koneksi ke database kesiswaan untuk sinkronisasi otomatis</p>
                </div>

                <form action="{{ route('settings.integration.update') }}" method="POST" class="p-6">
                    @csrf
                    
                    <div class="space-y-5">
                        <!-- Host Input -->
                        <div class="group">
                            <label class="block text-sm font-bold text-gray-800 mb-2 flex items-center gap-2">
                                <svg class="w-4 h-4 text-[#0284c7]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3"></path>
                                </svg>
                                IP Address / Host
                            </label>
                            <div class="relative">
                                <input type="text" 
                                       name="kesiswaan_host" 
                                       value="{{ $settings['kesiswaan_host'] ?? 'localhost' }}" 
                                       class="w-full pl-12 pr-4 py-3 rounded-xl border border-gray-200 focus:border-[#0284c7] focus:ring-2 focus:ring-[#0284c7]/20 bg-white transition-all duration-300 group-hover:border-[#0284c7]/50"
                                       placeholder="Contoh: 192.168.1.50">
                                <div class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <!-- Port & Database Grid -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="group">
                                <label class="block text-sm font-bold text-gray-800 mb-2 flex items-center gap-2">
                                    <svg class="w-4 h-4 text-[#0284c7]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                    </svg>
                                    Port Database
                                </label>
                                <div class="relative">
                                    <input type="text" 
                                           name="kesiswaan_port" 
                                           value="{{ $settings['kesiswaan_port'] ?? '3306' }}" 
                                           class="w-full pl-12 pr-4 py-3 rounded-xl border border-gray-200 focus:border-[#0284c7] focus:ring-2 focus:ring-[#0284c7]/20 bg-white transition-all duration-300 group-hover:border-[#0284c7]/50">
                                    <div class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="group">
                                <label class="block text-sm font-bold text-gray-800 mb-2 flex items-center gap-2">
                                    <svg class="w-4 h-4 text-[#0284c7]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path>
                                    </svg>
                                    Nama Database
                                </label>
                                <div class="relative">
                                    <input type="text" 
                                           name="kesiswaan_db" 
                                           value="{{ $settings['kesiswaan_db'] ?? '' }}" 
                                           class="w-full pl-12 pr-4 py-3 rounded-xl border border-gray-200 focus:border-[#0284c7] focus:ring-2 focus:ring-[#0284c7]/20 bg-white transition-all duration-300 group-hover:border-[#0284c7]/50"
                                           placeholder="db_kesiswaan">
                                    <div class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"></path>
                                    </svg>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Username & Password Grid -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="group">
                                <label class="block text-sm font-bold text-gray-800 mb-2 flex items-center gap-2">
                                    <svg class="w-4 h-4 text-[#0284c7]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                    Username
                                </label>
                                <div class="relative">
                                    <input type="text" 
                                           name="kesiswaan_user" 
                                           value="{{ $settings['kesiswaan_user'] ?? 'root' }}" 
                                           class="w-full pl-12 pr-4 py-3 rounded-xl border border-gray-200 focus:border-[#0284c7] focus:ring-2 focus:ring-[#0284c7]/20 bg-white transition-all duration-300 group-hover:border-[#0284c7]/50">
                                    <div class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="group">
                                <label class="block text-sm font-bold text-gray-800 mb-2 flex items-center gap-2">
                                    <svg class="w-4 h-4 text-[#0284c7]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                    </svg>
                                    Password
                                </label>
                                <div class="relative">
                                    <input type="password" 
                                           name="kesiswaan_pass" 
                                           value="{{ $settings['kesiswaan_pass'] ?? '' }}" 
                                           class="w-full pl-12 pr-4 py-3 rounded-xl border border-gray-200 focus:border-[#0284c7] focus:ring-2 focus:ring-[#0284c7]/20 bg-white transition-all duration-300 group-hover:border-[#0284c7]/50"
                                           placeholder="••••••••">
                                    <div class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="mt-8 pt-6 border-t border-gray-100">
                        <button type="submit" class="group relative w-full bg-gradient-to-r from-[#0284c7] to-[#0ea5e9] hover:from-[#027ab8] hover:to-[#0d93d7] text-white font-bold py-3.5 px-6 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-0.5 overflow-hidden">
                            <div class="absolute inset-0 bg-white/10 transform -translate-x-full group-hover:translate-x-0 transition-transform duration-700"></div>
                            <span class="relative z-10 flex items-center justify-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                SIMPAN KONFIGURASI DATABASE
                            </span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Right Column -->
            <div class="space-y-6">
                
                <!-- Status Integration Card -->
                <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-2xl border border-blue-100 shadow-lg p-6">
                    <div class="flex items-start gap-3 mb-4">
                        <div class="p-2 bg-[#0284c7]/10 rounded-lg">
                            <svg class="w-6 h-6 text-[#0284c7]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-900 text-lg">Status Integrasi</h3>
                            <p class="text-gray-600 text-sm mt-1">
                                Pastikan Database Kesiswaan dapat diakses dari jaringan komputer ini. 
                                Gunakan tombol di bawah untuk mengetes dan menarik data.
                            </p>
                        </div>
                    </div>

                    <form action="{{ route('settings.integration.sync') }}" method="POST" id="syncForm">
                        @csrf
                        <button type="button" 
                                onclick="confirmSync()" 
                                class="group w-full flex items-center justify-center gap-3 bg-gradient-to-r from-[#0284c7] to-[#0ea5e9] hover:from-[#027ab8] hover:to-[#0d93d7] text-white font-bold py-4 px-6 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-0.5">
                            <svg class="w-6 h-6 group-hover:animate-spin-slow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            <span class="text-center">TEST KONEKSI & SYNC DATA</span>
                        </button>
                    </form>
                </div>

                <!-- What's Synced Card -->
                <div class="bg-gradient-to-br from-white to-gray-50 rounded-2xl border border-gray-100 shadow-lg p-6">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="p-2 bg-[#0284c7]/10 rounded-lg">
                            <svg class="w-6 h-6 text-[#0284c7]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                        </div>
                        <h3 class="font-bold text-gray-900 text-lg">Data yang Disinkronisasi</h3>
                    </div>
                    
                    <div class="space-y-3">
                        @foreach([
                            ['icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z', 'text' => 'Nomor Induk Siswa (NIS)'],
                            ['icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z', 'text' => 'Nama Lengkap Siswa'],
                            ['icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253', 'text' => 'Kelas Terbaru'],
                            ['icon' => 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z', 'text' => 'Kode Kartu NFC (Untuk Pembayaran)'],
                            ['icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z', 'text' => 'Status Keaktifan']
                        ] as $item)
                            <div class="flex items-center gap-3 p-3 rounded-lg hover:bg-blue-50/50 transition-colors">
                                <div class="p-1.5 bg-blue-100 rounded-md">
                                    <svg class="w-4 h-4 text-[#0284c7]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item['icon'] }}"></path>
                                    </svg>
                                </div>
                                <span class="text-gray-700 font-medium">{{ $item['text'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Connection Tips -->
                <div class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-2xl border border-gray-200 p-5">
                    <div class="flex items-center gap-2 mb-3">
                        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <h4 class="font-bold text-gray-800 text-sm">Tips Koneksi Aman</h4>
                    </div>
                    <ul class="text-xs text-gray-600 space-y-2">
                        <li class="flex items-start gap-2">
                            <div class="w-1.5 h-1.5 bg-[#0284c7] rounded-full mt-1.5"></div>
                            Pastikan server database dalam jaringan lokal yang sama
                        </li>
                        <li class="flex items-start gap-2">
                            <div class="w-1.5 h-1.5 bg-[#0284c7] rounded-full mt-1.5"></div>
                            Gunakan firewall untuk membatasi akses remote
                        </li>
                        <li class="flex items-start gap-2">
                            <div class="w-1.5 h-1.5 bg-[#0284c7] rounded-full mt-1.5"></div>
                            Simpan konfigurasi dengan aman setelah pengaturan
                        </li>
                    </ul>
                </div>

            </div>
        </div>
    </div>

    <script>
    function confirmSync() {
        Swal.fire({
            title: 'Konfirmasi Sinkronisasi',
            text: 'Yakin ingin menarik data? Data siswa lokal akan diperbarui dengan data dari database kesiswaan.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#0284c7',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Ya, Sync Sekarang',
            cancelButtonText: 'Batal',
            backdrop: 'rgba(0,0,0,0.4)',
            customClass: {
                confirmButton: 'px-6 py-2.5 rounded-lg font-bold',
                cancelButton: 'px-6 py-2.5 rounded-lg font-bold'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('syncForm').submit();
            }
        });
    }

    // Add animation to sync button
    document.addEventListener('DOMContentLoaded', function() {
        const syncBtn = document.querySelector('button[onclick="confirmSync()"]');
        if (syncBtn) {
            syncBtn.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-2px)';
            });
            syncBtn.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
            });
        }
    });
    </script>

    <style>
    @keyframes spin-slow {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    .animate-spin-slow {
        animation: spin-slow 2s linear infinite;
    }
    
    input:focus, button:focus {
        outline: none;
        box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.1);
    }
    
    .group:hover .group-hover\:border-\[\#0284c7\]\/50 {
        border-color: rgba(2, 132, 199, 0.5);
    }
    
    .transition-all {
        transition-property: all;
        transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
        transition-duration: 300ms;
    }
    
    .shadow-lg {
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }
    
    .hover\:shadow-xl:hover {
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }
    </style>
</x-app-layout>