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

        @php
            $schoolLogo   = \DB::table('school_settings')->where('key', 'school_logo')->value('value');
            $schoolName   = \DB::table('school_settings')->where('key', 'school_name')->value('value') ?? config('app.name', 'Sistem TU');
            $sidebarColor = \DB::table('school_settings')->where('key', 'sidebar_color')->value('value') ?? 'blue';

            // Warna hero panel kiri login mengikuti pilihan sidebar_color
            $heroPalette = [
                'blue'   => '#0284c7', 'indigo' => '#4f46e5', 'violet' => '#7c3aed',
                'pink'   => '#db2777', 'rose'   => '#e11d48', 'orange' => '#ea580c',
                'amber'  => '#d97706', 'green'  => '#059669', 'teal'   => '#0d9488',
                'brown'  => '#78350f', 'slate'  => '#334155', 'black'  => '#0f172a',
            ];
            $heroColor = $heroPalette[$sidebarColor] ?? $heroPalette['blue'];
        @endphp

        <style>
            body { font-family: 'Inter', sans-serif; -webkit-font-smoothing: antialiased; }

            .hero-pattern {
                background-color: {{ $heroColor }};
                background-image:
                    radial-gradient(circle at 20% 20%, rgba(255,255,255,0.08) 0%, transparent 50%),
                    radial-gradient(circle at 80% 80%, rgba(0,0,0,0.15) 0%, transparent 50%),
                    radial-gradient(circle at 50% 50%, rgba(0,0,0,0.08) 0%, transparent 70%);
            }

            .floating-card       { animation: float 6s ease-in-out infinite; }
            .floating-card-delay { animation: float 6s ease-in-out 2s infinite; }

            @keyframes float {
                0%, 100% { transform: translateY(0px); }
                50%       { transform: translateY(-10px); }
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

                {{-- Logo Sekolah --}}
                <div class="relative z-10">
                    <div class="flex items-center gap-3">
                        @if($schoolLogo)
                            <img src="{{ Storage::url($schoolLogo) }}"
                                 alt="Logo {{ $schoolName }}"
                                 class="w-12 h-12 rounded-xl object-contain bg-white/20 p-1 border border-white/20 shadow-lg">
                        @else
                            <div class="bg-white/20 backdrop-blur-sm p-2.5 rounded-xl border border-white/20">
                                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                </svg>
                            </div>
                        @endif
                        <span class="text-white font-bold text-xl tracking-tight">{{ $schoolName }}</span>
                    </div>
                </div>

                {{-- Hero text --}}
                <div class="relative z-10 space-y-6">
                    <div>
                        <h1 class="text-4xl xl:text-5xl font-extrabold text-white leading-tight">
                            Sistem Tata Usaha<br>
                            <span class="text-white/70">Sekolah Modern</span>
                        </h1>
                        <p class="mt-4 text-white/80 text-lg leading-relaxed max-w-sm">
                            Kelola data siswa, tagihan, dan administrasi sekolah dalam satu platform terintegrasi.
                        </p>
                    </div>

                    {{-- Feature cards --}}
                    <div class="space-y-3">
                        <div class="floating-card flex items-center gap-3 bg-white/10 backdrop-blur-sm border border-white/15 rounded-2xl px-4 py-3">
                            <div class="w-9 h-9 bg-white/20 rounded-xl flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="text-white font-semibold text-sm">Manajemen Siswa</p>
                                <p class="text-white/60 text-xs">Data lengkap & terstruktur</p>
                            </div>
                        </div>

                        <div class="floating-card-delay flex items-center gap-3 bg-white/10 backdrop-blur-sm border border-white/15 rounded-2xl px-4 py-3">
                            <div class="w-9 h-9 bg-white/20 rounded-xl flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="text-white font-semibold text-sm">Tagihan & Pembayaran</p>
                                <p class="text-white/60 text-xs">SPP, gedung & koperasi</p>
                            </div>
                        </div>

                        <div class="floating-card flex items-center gap-3 bg-white/10 backdrop-blur-sm border border-white/15 rounded-2xl px-4 py-3">
                            <div class="w-9 h-9 bg-white/20 rounded-xl flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                                </svg>
                            </div>
                            <div>
                                <p class="text-white font-semibold text-sm">Kantin & Koperasi</p>
                                <p class="text-white/60 text-xs">POS terintegrasi</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Bottom credit --}}
                <div class="relative z-10">
                    <p class="text-white/40 text-sm">&copy; {{ date('Y') }} {{ $schoolName }}</p>
                </div>
            </div>

            {{-- ── Sisi kanan: Form login ── --}}
            <div class="w-full lg:w-1/2 flex flex-col justify-center items-center p-6 sm:p-12 bg-gray-50 min-h-screen">

                {{-- Logo mobile --}}
                <div class="lg:hidden mb-8 flex flex-col items-center gap-3">
                    @if($schoolLogo)
                        <img src="{{ Storage::url($schoolLogo) }}"
                             alt="Logo {{ $schoolName }}"
                             class="w-16 h-16 rounded-2xl object-contain shadow-lg border border-gray-200 bg-white p-1">
                    @else
                        <div class="p-3 rounded-2xl shadow-lg" style="background-color: {{ $heroColor }}">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                            </svg>
                        </div>
                    @endif
                    <span class="text-2xl font-bold text-gray-800">{{ $schoolName }}</span>
                </div>

                {{-- Form card --}}
                <div class="w-full max-w-md">
                    {{ $slot }}
                </div>

                {{-- Footer mobile --}}
                <div class="lg:hidden mt-8 text-center text-xs text-gray-400">
                    &copy; {{ date('Y') }} {{ $schoolName }}
                </div>
            </div>

        </div>
    </body>
</html>
