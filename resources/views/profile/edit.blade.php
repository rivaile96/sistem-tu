<x-app-layout>
    <x-slot name="header">
        Pengaturan Profil
    </x-slot>

    <div class="space-y-6">

        <!-- HEADER -->
        <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
            <div>
                <h1 class="flex items-center gap-3 text-2xl font-black text-slate-800">
                    <div class="bg-[#178BC1] p-2.5 rounded-xl text-white shadow-sm shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                    </div>
                    Profil & Akun Administrator
                </h1>
                <p class="mt-1 text-xs text-slate-500 font-medium">
                    Kelola data identitas login, email resmi, dan keamanan kata sandi akun sistem tata usaha.
                </p>
            </div>
        </div>

        <div class="flex flex-col lg:flex-row gap-6">
            
            <!-- LEFT COLUMN: USER INFO CARD -->
            <div class="lg:w-1/3 space-y-6">
                
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden text-center p-6 relative">
                    <div class="inline-block relative">
                        <div class="h-20 w-20 rounded-2xl bg-blue-50 border-2 border-blue-200 text-[#178BC1] mx-auto flex items-center justify-center text-3xl font-black">
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        </div>
                        <div class="absolute -bottom-1 -right-1 bg-emerald-500 h-4 w-4 rounded-full border-2 border-white" title="Aktif"></div>
                    </div>
                    
                    <h2 class="mt-3 text-base font-bold text-slate-800">{{ Auth::user()->name }}</h2>
                    <p class="text-xs text-slate-400 mt-0.5">{{ Auth::user()->email }}</p>

                    <div class="mt-3">
                        <span class="px-2.5 py-1 rounded-lg text-[10px] font-bold uppercase tracking-wider bg-blue-50 text-[#178BC1] border border-blue-200">
                            {{ Auth::user()->role === 'admin' ? 'Tata Usaha Administrator' : 'Staff Operasional' }}
                        </span>
                    </div>

                    <div class="mt-6 pt-4 border-t border-slate-100 grid grid-cols-2 divide-x divide-slate-100 text-xs">
                        <div>
                            <span class="block text-[10px] text-slate-400 uppercase font-bold">Terdaftar</span>
                            <span class="block text-xs font-bold text-slate-700 mt-0.5">{{ Auth::user()->created_at?->format('d M Y') ?? '-' }}</span>
                        </div>
                        <div>
                            <span class="block text-[10px] text-slate-400 uppercase font-bold">Status</span>
                            <span class="block text-xs font-bold text-emerald-600 mt-0.5">Aktif</span>
                        </div>
                    </div>
                </div>

                <!-- SECURITY TIPS -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6 space-y-3">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-[#178BC1]" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                        </svg>
                        <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Keamanan Akun</h3>
                    </div>
                    <p class="text-xs text-slate-500 leading-relaxed">
                        Gunakan kata sandi yang kuat dan unik. Hindari membagikan kredensial login kepada pihak lain demi menjaga kerahasiaan data keuangan kesiswaan.
                    </p>
                </div>

            </div>

            <!-- RIGHT COLUMN: UPDATE FORMS -->
            <div class="lg:w-2/3 space-y-6">
                
                <!-- 1. INFORMASI PRIBADI -->
                <div class="bg-white p-6 sm:p-8 rounded-2xl shadow-sm border border-slate-200 space-y-4">
                    <div class="flex items-center gap-3 border-b border-slate-100 pb-4">
                        <div class="p-2 bg-blue-50 text-[#178BC1] rounded-xl border border-blue-100">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-800">Informasi Profil</h3>
                            <p class="text-xs text-slate-400">Perbarui nama pengguna dan alamat email akun Anda.</p>
                        </div>
                    </div>
                    
                    <div class="max-w-xl">
                        @include('profile.partials.update-profile-information-form')
                    </div>
                </div>

                <!-- 2. GANTI PASSWORD -->
                <div class="bg-white p-6 sm:p-8 rounded-2xl shadow-sm border border-slate-200 space-y-4">
                    <div class="flex items-center gap-3 border-b border-slate-100 pb-4">
                        <div class="p-2 bg-amber-50 text-amber-600 rounded-xl border border-amber-100">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-800">Ganti Kata Sandi</h3>
                            <p class="text-xs text-slate-400">Gunakan kata sandi baru minimal 8 karakter.</p>
                        </div>
                    </div>

                    <div class="max-w-xl">
                        @include('profile.partials.update-password-form')
                    </div>
                </div>

                <!-- 3. HAPUS AKUN -->
                <div class="bg-white p-6 sm:p-8 rounded-2xl shadow-sm border border-rose-200 space-y-4">
                    <div class="flex items-center gap-3 border-b border-rose-100 pb-4">
                        <div class="p-2 bg-rose-50 text-rose-600 rounded-xl border border-rose-200">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                <polyline points="3 6 5 6 21 6"></polyline>
                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-800">Hapus Akun</h3>
                            <p class="text-xs text-slate-400">Tindakan ini menghapus akun Anda secara permanen dari sistem.</p>
                        </div>
                    </div>

                    <div class="max-w-xl">
                        @include('profile.partials.delete-user-form')
                    </div>
                </div>

            </div>
        </div>

    </div>
</x-app-layout>
