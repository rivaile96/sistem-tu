<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Sistem TU') }}</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            body { font-family: 'Inter', sans-serif; -webkit-font-smoothing: antialiased; }

            .hero-pattern {
                background-color: #0284c7;
                background-image:
                    radial-gradient(circle at 20% 20%, rgba(255,255,255,0.08) 0%, transparent 50%),
                    radial-gradient(circle at 80% 80%, rgba(14,165,233,0.4) 0%, transparent 50%),
                    radial-gradient(circle at 50% 50%, rgba(2,132,199,0.3) 0%, transparent 70%);
            }

            .floating-card {
                animation: float 6s ease-in-out infinite;
            }
            .floating-card-delay {
                animation: float 6s ease-in-out 2s infinite;
            }
            @keyframes float {
                0%, 100% { transform: translateY(0px); }
                50%       { transform: translateY(-10px); }
            }

            .input-focus:focus {
                box-shadow: 0 0 0 3px rgba(2,132,199,0.15);
            }
        </style>
    </head>
    <body class="font-sans text-gray-900 antialiased">

        <div class="min-h-screen flex">

            {{-- ── Sisi kiri: Branding (sembunyi di mobile) ── --}}
            <div class="hidden lg:flex lg:w-1/2 hero-pattern flex-col justify-between p-12 relative overflow-hidden">

                {{-- Dekorasi lingkaran --}}
                <div class="absolute top-0 right-0 w-96 h-96 bg-white/5 rounded-full -translate-y-1/2 translate-x-1/2"></div>
                <div class="absolute bottom-0 left-0 w-64 h-64 bg-white/5 rounded-full translate-y-1/2 -translate-x-1/2"></div>
                <div class="absolute top-1/2 left-1/2 w-32 h-32 bg-white/5 rounded-full -translate-x-1/2 -translate-y-1/2"></div>

                {{-- Logo --}}
                <div class="relative z-10">
                    <div class="flex items-center gap-3">
                        <div class="bg-white/20 backdrop-blur-sm p-2.5 rounded-xl border border-white/20">
                            <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                            </svg>
                        </div>
                        <span class="text-white font-bold text-xl tracking-tight">{{ config('app.name', 'Sistem TU') }}</span>
                    </div>
                </div>

                {{-- Hero text --}}
                <div class="relative z-10 space-y-6">
                    <div>
                        <h1 class="text-4xl xl:text-5xl font-extrabold text-white leading-tight">
                            Sistem Tata Usaha<br>
                            <span class="text-sky-200">Sekolah Modern</span>
                        </h1>
                        <p class="mt-4 text-sky-100 text-lg leading-relaxed max-w-sm">
                            Kelola data siswa, tagihan, dan administrasi sekolah dalam satu platform terintegrasi.
                        </p>
                    </div>

                    {{-- Feature cards --}}
                    <div class="space-y-3">
                        <div class="floating-card flex items-center gap-3 bg-white/10 backdrop-blur-sm border border-white/15 rounded-2xl px-4 py-3">
                            <div class="w-9 h-9 bg-white/20 rounded-xl flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            </div>
                            <div>
                                <p class="text-white font-semibold text-sm">Manajemen Siswa</p>
                                <p class="text-sky-200 text-xs">Data lengkap & terstruktur</p>
                            </div>
                        </div>

                        <div class="floating-card-delay flex items-center gap-3 bg-white/10 backdrop-blur-sm border border-white/15 rounded-2xl px-4 py-3">
                            <div class="w-9 h-9 bg-white/20 rounded-xl flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                            </div>
                            <div>
                                <p class="text-white font-semibold text-sm">Tagihan & Pembayaran</p>
                                <p class="text-sky-200 text-xs">SPP, gedung & koperasi</p>
                            </div>
                        </div>

                        <div class="floating-card flex items-center gap-3 bg-white/10 backdrop-blur-sm border border-white/15 rounded-2xl px-4 py-3">
                            <div class="w-9 h-9 bg-white/20 rounded-xl flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                            </div>
                            <div>
                                <p class="text-white font-semibold text-sm">Kantin & Koperasi</p>
                                <p class="text-sky-200 text-xs">POS terintegrasi</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Bottom credit --}}
                <div class="relative z-10">
                    <p class="text-sky-300 text-sm">&copy; {{ date('Y') }} Sistem Manajemen Sekolah</p>
                </div>
            </div>

            {{-- ── Sisi kanan: Form login ── --}}
            <div class="w-full lg:w-1/2 flex flex-col justify-center items-center p-6 sm:p-12 bg-gray-50 min-h-screen">

                {{-- Logo mobile (muncul hanya di mobile) --}}
                <div class="lg:hidden mb-8 flex flex-col items-center gap-3">
                    <div class="bg-[#0284c7] p-3 rounded-2xl shadow-lg shadow-sky-200">
                        <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                        </svg>
                    </div>
                    <span class="text-2xl font-bold text-gray-800">{{ config('app.name', 'Sistem TU') }}</span>
                </div>

                {{-- Form card --}}
                <div class="w-full max-w-md">
                    {{ $slot }}
                </div>

                {{-- Footer mobile --}}
                <div class="lg:hidden mt-8 text-center text-xs text-gray-400">
                    &copy; {{ date('Y') }} Sistem Manajemen Sekolah
                </div>
            </div>

        </div>
    </body>
</html>
