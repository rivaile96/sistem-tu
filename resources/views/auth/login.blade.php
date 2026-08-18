<x-guest-layout>

    {{-- Header --}}
    <div class="mb-8">
        <h2 class="text-2xl sm:text-3xl font-extrabold text-gray-900">Selamat Datang</h2>
        <p class="mt-1.5 text-sm text-gray-500">Masuk ke panel administrasi sekolah</p>
    </div>

    {{-- Session Status --}}
    @if (session('status'))
        <div class="mb-5 flex items-center gap-2.5 bg-green-50 border border-green-200 text-green-700 text-sm px-4 py-3 rounded-xl">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            {{ session('status') }}
        </div>
    @endif

    {{-- Error global --}}
    @if ($errors->any())
        <div class="mb-5 flex items-start gap-2.5 bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-3 rounded-xl">
            <svg class="w-4 h-4 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <span>{{ $errors->first() }}</span>
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        {{-- Email --}}
        <div>
            <label for="email" class="block text-sm font-semibold text-gray-700 mb-1.5">
                Alamat Email
            </label>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path>
                    </svg>
                </div>
                <input
                    id="email"
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    required
                    autofocus
                    autocomplete="username"
                    placeholder="admin@sekolah.sch.id"
                    class="w-full pl-10 pr-4 py-3 rounded-xl border {{ $errors->has('email') ? 'border-red-400 bg-red-50 focus:border-red-500' : 'border-gray-200 focus:border-[#0284c7]' }} focus:outline-none focus:ring-2 {{ $errors->has('email') ? 'focus:ring-red-200' : 'focus:ring-[#0284c7]/20' }} text-sm transition-all bg-white text-gray-900 placeholder-gray-400"
                />
            </div>
        </div>

        {{-- Password --}}
        <div>
            <div class="flex items-center justify-between mb-1.5">
                <label for="password" class="block text-sm font-semibold text-gray-700">
                    Password
                </label>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}"
                       class="text-xs text-[#0284c7] hover:text-[#0369a1] font-medium transition-colors">
                        Lupa password?
                    </a>
                @endif
            </div>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                </div>
                <input
                    id="password"
                    type="password"
                    name="password"
                    required
                    autocomplete="current-password"
                    placeholder="••••••••"
                    class="w-full pl-10 pr-11 py-3 rounded-xl border {{ $errors->has('password') ? 'border-red-400 bg-red-50 focus:border-red-500' : 'border-gray-200 focus:border-[#0284c7]' }} focus:outline-none focus:ring-2 {{ $errors->has('password') ? 'focus:ring-red-200' : 'focus:ring-[#0284c7]/20' }} text-sm transition-all bg-white text-gray-900 placeholder-gray-400"
                />
                {{-- Toggle show/hide password --}}
                <button type="button" onclick="togglePassword()"
                        class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-gray-400 hover:text-gray-600 transition-colors">
                    <svg id="eye-icon" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                    <svg id="eye-off-icon" class="w-4 h-4 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"></path>
                    </svg>
                </button>
            </div>
        </div>

        {{-- Remember me --}}
        <div class="flex items-center">
            <input
                id="remember_me"
                type="checkbox"
                name="remember"
                class="w-4 h-4 rounded border-gray-300 text-[#0284c7] focus:ring-[#0284c7]/30 transition"
            >
            <label for="remember_me" class="ml-2.5 text-sm text-gray-600 select-none cursor-pointer">
                Ingat saya di perangkat ini
            </label>
        </div>

        {{-- Submit --}}
        <button
            type="submit"
            class="w-full flex items-center justify-center gap-2 bg-gradient-to-r from-[#0284c7] to-[#0ea5e9] hover:from-[#0369a1] hover:to-[#0284c7] text-white font-semibold py-3 px-6 rounded-xl shadow-md shadow-sky-200 hover:shadow-sky-300 transition-all duration-200 text-sm mt-2"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
            </svg>
            Masuk ke Sistem
        </button>

    </form>

    {{-- Divider info portal siswa --}}
    <div class="mt-6 pt-5 border-t border-gray-100">
        <p class="text-center text-xs text-gray-400 mb-3">Bukan staf sekolah?</p>
        <a href="{{ route('siswa.login') }}"
           class="w-full flex items-center justify-center gap-2 bg-white border border-gray-200 hover:border-[#0284c7]/40 hover:bg-sky-50 text-gray-600 hover:text-[#0284c7] font-medium py-2.5 px-6 rounded-xl transition-all duration-200 text-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
            </svg>
            Portal Login Siswa
        </a>
    </div>

    <script>
        function togglePassword() {
            const input   = document.getElementById('password');
            const eyeOn   = document.getElementById('eye-icon');
            const eyeOff  = document.getElementById('eye-off-icon');
            if (input.type === 'password') {
                input.type = 'text';
                eyeOn.classList.add('hidden');
                eyeOff.classList.remove('hidden');
            } else {
                input.type = 'password';
                eyeOn.classList.remove('hidden');
                eyeOff.classList.add('hidden');
            }
        }
    </script>

</x-guest-layout>
