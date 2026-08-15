<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Siswa — Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        input:-webkit-autofill,
        input:-webkit-autofill:hover,
        input:-webkit-autofill:focus {
            -webkit-box-shadow: 0 0 0 30px white inset !important;
        }
    </style>
</head>
<body class="min-h-screen bg-gradient-to-br from-sky-400 via-sky-600 to-blue-900 flex items-center justify-center px-4 py-10">

    <div class="w-full max-w-sm">

        {{-- Logo & Header --}}
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-20 h-20 bg-white rounded-2xl shadow-2xl mb-4">
                <svg class="w-11 h-11 text-sky-600" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M12 14l9-5-9-5-9 5 9 5z"/>
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M12 14l6.16-3.422A12.083 12.083 0 0121 13c0 5.523-4.03 10.075-9.284 10.95a.75.75 0 01-.432 0C6.03 23.075 2 18.523 2 13c0-.538.05-1.064.144-1.578L12 14z"/>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Portal Siswa</h1>
            <p class="text-sky-200 text-sm mt-1">Sistem Informasi Sekolah</p>
        </div>

        {{-- Login Card --}}
        <div class="bg-white rounded-3xl shadow-2xl px-8 py-8">
            <h2 class="text-xl font-bold text-gray-900 mb-1">Masuk Akun</h2>
            <p class="text-gray-400 text-sm mb-6">Gunakan NIS dan tanggal lahir Anda</p>

            {{-- Error Messages --}}
            @if ($errors->any())
                <div class="bg-red-50 border border-red-200 rounded-xl p-3 mb-5">
                    @foreach ($errors->all() as $error)
                        <p class="text-red-600 text-sm flex items-start gap-1.5">
                            <svg class="w-4 h-4 mt-0.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                            {{ $error }}
                        </p>
                    @endforeach
                </div>
            @endif

            {{-- Success Flash --}}
            @if (session('success'))
                <div class="bg-green-50 border border-green-200 rounded-xl p-3 mb-5">
                    <p class="text-green-700 text-sm">{{ session('success') }}</p>
                </div>
            @endif

            <form method="POST" action="{{ route('siswa.login.post') }}" class="space-y-5">
                @csrf

                {{-- NIS Field --}}
                <div>
                    <label for="nis" class="block text-sm font-semibold text-gray-700 mb-1.5">
                        NIS (Nomor Induk Siswa)
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </div>
                        <input
                            type="text"
                            id="nis"
                            name="nis"
                            value="{{ old('nis') }}"
                            autocomplete="username"
                            inputmode="numeric"
                            placeholder="Masukkan NIS Anda"
                            required
                            class="w-full pl-10 pr-4 py-3 border {{ $errors->has('nis') ? 'border-red-400 bg-red-50' : 'border-gray-200' }} rounded-xl text-gray-900 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent transition"
                        >
                    </div>
                </div>

                {{-- Password Field --}}
                <div>
                    <label for="password" class="block text-sm font-semibold text-gray-700 mb-1.5">
                        Password
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </div>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            inputmode="numeric"
                            placeholder="Tanggal lahir (contoh: 120706)"
                            required
                            class="w-full pl-10 pr-10 py-3 border {{ $errors->has('password') ? 'border-red-400 bg-red-50' : 'border-gray-200' }} rounded-xl text-gray-900 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent transition"
                        >
                        {{-- Toggle visibility --}}
                        <button type="button" onclick="togglePassword()" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-gray-400 hover:text-gray-600">
                            <svg id="eye-icon" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                        </button>
                    </div>
                    <p class="text-xs text-gray-400 mt-1.5">
                        Format: <span class="font-medium text-gray-500">ddmmyy</span>
                        — contoh lahir 12 Juli 2006 → <span class="font-mono font-medium text-sky-600">120706</span>
                    </p>
                </div>

                {{-- Submit --}}
                <button
                    type="submit"
                    class="w-full bg-sky-600 hover:bg-sky-700 active:bg-sky-800 text-white font-bold py-3.5 rounded-xl transition-colors text-sm shadow-lg shadow-sky-200 mt-1"
                >
                    Masuk ke Portal
                </button>
            </form>
        </div>

        <p class="text-center text-sky-200 text-xs mt-6">
            Lupa password? Hubungi TU Sekolah
        </p>

    </div>

    <script>
        function togglePassword() {
            const input = document.getElementById('password');
            const icon  = document.getElementById('eye-icon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                `;
            } else {
                input.type = 'password';
                icon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                `;
            }
        }
    </script>
</body>
</html>
