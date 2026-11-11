<x-guest-layout>
    <!-- LEFT PANEL: BRANDING (SPMB MATCHING #178BC1) -->
    <div class="relative flex flex-col justify-between p-8 md:p-12 text-white md:w-5/12 bg-[#178BC1] shrink-0">
        <div>
            <!-- LOGO & BADGE -->
            <div class="flex items-center gap-3 mb-8">
                <div class="flex items-center justify-center w-12 h-12 bg-white rounded-xl shadow-md text-[#178BC1] font-black text-2xl tracking-tight">
                    K
                </div>
                <div>
                    <h2 class="text-lg font-bold tracking-wide">KAFFAH ISLAMIC SCHOOL</h2>
                    <p class="text-xs text-blue-100 font-medium opacity-90">Sistem Tata Usaha & Keuangan</p>
                </div>
            </div>

            <!-- TITLE & DESCRIPTION -->
            <div class="space-y-3">
                <h3 class="text-2xl font-bold leading-tight">Portal Manajemen Operasional & Keuangan</h3>
                <p class="text-xs text-blue-100 leading-relaxed opacity-85">
                    Kelola data kesiswaan, tagihan SPP bulanan, pembayaran DOKU Gateway, dan sistem kasir kantin dalam satu panel terintegrasi.
                </p>
            </div>

            <!-- KEY PILLARS -->
            <div class="mt-8 space-y-3">
                <div class="flex items-center gap-3 text-xs text-blue-50 bg-white/10 px-3.5 py-2.5 rounded-xl border border-white/10">
                    <svg class="w-4 h-4 text-white shrink-0" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                    </svg>
                    <span>Autentikasi Terproteksi & Audit Trail</span>
                </div>
                <div class="flex items-center gap-3 text-xs text-blue-50 bg-white/10 px-3.5 py-2.5 rounded-xl border border-white/10">
                    <svg class="w-4 h-4 text-white shrink-0" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                        <rect x="2" y="5" width="20" height="14" rx="2"></rect>
                        <line x1="2" y1="10" x2="22" y2="10"></line>
                    </svg>
                    <span>DOKU Direct API & QRIS Sandbox</span>
                </div>
            </div>
        </div>

        <div class="mt-8 pt-4 border-t border-white/20 text-[11px] text-blue-100 opacity-75">
            &copy; {{ date('Y') }} Kaffah Islamic School. Secure System.
        </div>
    </div>

    <!-- RIGHT PANEL: LOGIN FORM -->
    <div class="p-8 md:p-12 md:w-7/12 flex flex-col justify-center bg-white">
        <div class="max-w-md w-full mx-auto">
            
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-slate-800">Masuk ke Akun</h1>
                <p class="text-xs text-slate-500 mt-1 font-medium">Gunakan email dan kata sandi yang telah terdaftar.</p>
            </div>

            <!-- Session Status Alert -->
            <x-auth-session-status class="mb-4" :status="session('status')" />

            @if($errors->any())
                <div class="mb-4 p-3.5 bg-rose-50 border border-rose-200 rounded-xl text-xs text-rose-700 font-medium">
                    <div class="flex items-center gap-2 mb-1 font-bold">
                        <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="8" x2="12" y2="12"></line>
                            <line x1="12" y1="16" x2="12.01" y2="16"></line>
                        </svg>
                        Terjadi kesalahan autentikasi:
                    </div>
                    <ul class="list-disc list-inside space-y-0.5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-4">
                @csrf

                <!-- Email Address -->
                <div>
                    <label for="email" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                        Alamat Email
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                                <polyline points="22,6 12,13 2,6"></polyline>
                            </svg>
                        </div>
                        <input id="email" 
                               type="email" 
                               name="email" 
                               value="{{ old('email') }}" 
                               required 
                               autofocus 
                               autocomplete="username"
                               placeholder="nama@kaffah.id"
                               class="w-full pl-10 pr-4 py-2.5 text-xs font-medium text-slate-800 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#178BC1]/20 focus:border-[#178BC1] transition-all" />
                    </div>
                </div>

                <!-- Password -->
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">
                            Kata Sandi
                        </label>
                        @if (Route::has('password.request'))
                            <a class="text-xs text-[#178BC1] hover:underline font-medium" href="{{ route('password.request') }}">
                                Lupa sandi?
                            </a>
                        @endif
                    </div>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                            </svg>
                        </div>
                        <input id="password" 
                               type="password" 
                               name="password" 
                               required 
                               autocomplete="current-password"
                               placeholder="••••••••"
                               class="w-full pl-10 pr-4 py-2.5 text-xs font-medium text-slate-800 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#178BC1]/20 focus:border-[#178BC1] transition-all" />
                    </div>
                </div>

                <!-- Remember Me -->
                <div class="flex items-center justify-between pt-1">
                    <label for="remember_me" class="inline-flex items-center cursor-pointer">
                        <input id="remember_me" 
                               type="checkbox" 
                               name="remember"
                               class="w-4 h-4 rounded border-slate-300 text-[#178BC1] focus:ring-[#178BC1]/30 transition">
                        <span class="ms-2 text-xs font-medium text-slate-600">Ingat sesi saya</span>
                    </label>
                </div>

                <!-- Submit Button -->
                <div class="pt-2">
                    <button type="submit" 
                            class="w-full bg-[#178BC1] hover:bg-[#126da3] text-white py-3 px-4 rounded-xl font-bold text-xs uppercase tracking-wider shadow-sm hover:shadow transition-all flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"></path>
                            <polyline points="10 17 15 12 10 7"></polyline>
                            <line x1="15" y1="12" x2="3" y2="12"></line>
                        </svg>
                        <span>Masuk ke Sistem</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-guest-layout>
