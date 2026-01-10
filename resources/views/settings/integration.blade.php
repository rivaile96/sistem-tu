<x-app-layout>
    <div class="p-6">
        <div class="max-w-4xl mx-auto">
            
            <div class="mb-8">
                <h2 class="text-2xl font-bold text-gray-800">Integrasi Kesiswaan</h2>
                <p class="text-gray-500">Hubungkan Sistem TU dengan Database Kesiswaan untuk sinkronisasi data siswa otomatis.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 h-fit">
                    <h3 class="font-bold text-gray-800 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        Koneksi Database Remote
                    </h3>

                    <form action="{{ route('settings.integration.update') }}" method="POST">
                        @csrf
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-bold text-gray-700 mb-1">IP Address / Host</label>
                                <input type="text" name="kesiswaan_host" value="{{ $settings['kesiswaan_host'] ?? 'localhost' }}" class="w-full rounded-xl border-gray-300 focus:ring-blue-500 focus:border-blue-500" placeholder="Contoh: 192.168.1.50">
                            </div>
                            
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-1">Port</label>
                                    <input type="text" name="kesiswaan_port" value="{{ $settings['kesiswaan_port'] ?? '3306' }}" class="w-full rounded-xl border-gray-300">
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-1">Nama Database</label>
                                    <input type="text" name="kesiswaan_db" value="{{ $settings['kesiswaan_db'] ?? '' }}" class="w-full rounded-xl border-gray-300" placeholder="db_kesiswaan">
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-1">Username DB</label>
                                    <input type="text" name="kesiswaan_user" value="{{ $settings['kesiswaan_user'] ?? 'root' }}" class="w-full rounded-xl border-gray-300">
                                </div>
                                <div>
                                    <label class="block text-sm font-bold text-gray-700 mb-1">Password DB</label>
                                    <input type="password" name="kesiswaan_pass" value="{{ $settings['kesiswaan_pass'] ?? '' }}" class="w-full rounded-xl border-gray-300" placeholder="******">
                                </div>
                            </div>
                        </div>

                        <div class="mt-6">
                            <button type="submit" class="w-full bg-gray-800 text-white font-bold py-3 rounded-xl hover:bg-black transition shadow-lg">
                                SIMPAN KONFIGURASI
                            </button>
                        </div>
                    </form>
                </div>

                <div class="flex flex-col gap-6">
                    
                    <div class="bg-blue-50 p-6 rounded-2xl border border-blue-100">
                        <h4 class="font-bold text-blue-800 text-lg mb-2">Status Integrasi</h4>
                        <p class="text-sm text-blue-600 mb-4">
                            Pastikan Database Kesiswaan dapat diakses dari jaringan komputer ini. Gunakan tombol di bawah untuk mengetes dan menarik data.
                        </p>
                        
                        <form action="{{ route('settings.integration.sync') }}" method="POST">
                            @csrf
                            <button type="submit" onclick="return confirm('Yakin ingin menarik data? Data siswa lokal akan diperbarui.')" class="w-full flex items-center justify-center gap-2 bg-blue-600 text-white font-bold py-4 rounded-xl hover:bg-blue-700 transition shadow-lg shadow-blue-200">
                                <svg class="w-6 h-6 animate-spin-slow" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                TEST KONEKSI & SYNC DATA
                            </button>
                        </form>
                    </div>

                    <div class="bg-white p-6 rounded-2xl border border-gray-100 shadow-sm">
                        <h4 class="font-bold text-gray-800 mb-2">Apa yang disinkronisasi?</h4>
                        <ul class="text-sm text-gray-500 space-y-2 list-disc list-inside">
                            <li>Nomor Induk Siswa (NIS)</li>
                            <li>Nama Lengkap Siswa</li>
                            <li>Kelas Terbaru</li>
                            <li>Kode Kartu NFC (Untuk Pembayaran)</li>
                            <li>Status Keaktifan</li>
                        </ul>
                    </div>

                </div>
            </div>

        </div>
    </div>
</x-app-layout>