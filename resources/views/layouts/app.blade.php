<!DOCTYPE html>
<html lang="id" class="h-full bg-gray-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Sistem TU') }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @php
        // Ambil pengaturan sidebar & logo dari school_settings (cached per-request)
        $sidebarColor = \DB::table('school_settings')->where('key', 'sidebar_color')->value('value') ?? 'blue';
        $schoolLogo   = \DB::table('school_settings')->where('key', 'school_logo')->value('value');
        $schoolName   = \DB::table('school_settings')->where('key', 'school_name')->value('value') ?? config('app.name', 'Sistem TU');

        // Palet warna — [bg utama, bg header/footer (lebih gelap), warna aktif text, label]
        $palettes = [
            'blue'   => ['#0ea5e9', '#0284c7', '#0ea5e9', 'Sky Blue'],
            'indigo' => ['#6366f1', '#4f46e5', '#6366f1', 'Indigo'],
            'violet' => ['#8b5cf6', '#7c3aed', '#8b5cf6', 'Violet'],
            'pink'   => ['#ec4899', '#db2777', '#ec4899', 'Pink'],
            'rose'   => ['#f43f5e', '#e11d48', '#f43f5e', 'Rose Red'],
            'orange' => ['#f97316', '#ea580c', '#f97316', 'Orange'],
            'amber'  => ['#f59e0b', '#d97706', '#f59e0b', 'Amber'],
            'green'  => ['#10b981', '#059669', '#10b981', 'Emerald'],
            'teal'   => ['#14b8a6', '#0d9488', '#14b8a6', 'Teal'],
            'brown'  => ['#92400e', '#78350f', '#92400e', 'Brown'],
            'slate'  => ['#475569', '#334155', '#475569', 'Slate Gray'],
            'black'  => ['#1e293b', '#0f172a', '#1e293b', 'Dark/Black'],
        ];

        $palette = $palettes[$sidebarColor] ?? $palettes['blue'];
        [$sidebarBg, $sidebarDark, $activeText] = $palette;
    @endphp

    <style>
        [x-cloak] { display: none !important; }
        .custom-scroll::-webkit-scrollbar { width: 5px; height: 5px; }
        .custom-scroll::-webkit-scrollbar-track { background: transparent; }
        .custom-scroll::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.2); border-radius: 10px; }

        /* CSS Custom Properties untuk warna sidebar — tidak bergantung Tailwind purge */
        :root {
            --sb-bg:     {{ $sidebarBg }};
            --sb-dark:   {{ $sidebarDark }};
            --sb-active: {{ $activeText }};
        }

        .sidebar-wrap        { background-color: var(--sb-bg); }
        .sidebar-header      { background-color: var(--sb-dark); }
        .sidebar-footer      { background-color: var(--sb-dark); }
        .sidebar-submenu     { background-color: color-mix(in srgb, var(--sb-dark) 60%, transparent); }
        .sidebar-active      { background-color: #ffffff; color: var(--sb-active) !important; }
        .sidebar-active svg  { color: var(--sb-active) !important; stroke: var(--sb-active) !important; }
    </style>
</head>
<body class="h-full font-sans antialiased text-gray-900" x-data="{ sidebarOpen: false }">

    {{-- ═══════════════════════════════════════════════════════
         SIDEBAR
    ═══════════════════════════════════════════════════════ --}}
    <aside class="sidebar-wrap fixed inset-y-0 left-0 z-50 w-64 text-white transition-transform duration-300 transform flex flex-col shadow-2xl h-full"
           :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'">

        {{-- Header Sidebar: Logo Sekolah + Nama --}}
        <div class="sidebar-header h-auto min-h-[4.5rem] flex items-center justify-between px-4 py-3 flex-shrink-0">
            <div class="flex items-center gap-3 min-w-0">
                @if($schoolLogo)
                    <img src="{{ Storage::url($schoolLogo) }}"
                         alt="Logo Sekolah"
                         class="w-10 h-10 rounded-lg object-contain bg-white p-0.5 flex-shrink-0 shadow">
                @else
                    <div class="p-1.5 bg-white rounded-lg flex-shrink-0">
                        <svg class="w-5 h-5" style="color: var(--sb-active)" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                @endif
                <span class="font-bold text-sm leading-tight truncate text-white">{{ $schoolName }}</span>
            </div>
            <button @click="sidebarOpen = false" class="lg:hidden text-white/80 hover:text-white ml-2 flex-shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Nav Menu --}}
        <nav class="flex-1 overflow-y-auto custom-scroll px-3 py-5 space-y-1">
            <p class="px-3 text-[10px] font-bold text-white/50 uppercase tracking-widest mb-2">Menu Utama</p>

            {{-- Dashboard --}}
            <a href="{{ route('dashboard') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition
                      {{ request()->routeIs('dashboard') ? 'sidebar-active shadow-md' : 'text-white/80 hover:bg-white/10 hover:text-white' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                </svg>
                <span>Dashboard</span>
            </a>

            {{-- Manajemen Siswa --}}
            @if(in_array(Auth::user()->role, ['admin','tu','staf','kepala_sekolah']))
            <a href="{{ route('students.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition
                      {{ request()->routeIs('students.*') || request()->routeIs('naik-kelas.*') ? 'sidebar-active shadow-md' : 'text-white/80 hover:bg-white/10 hover:text-white' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                </svg>
                <span>Manajemen Siswa</span>
            </a>
            @endif

            {{-- Master Kelas & Rombel --}}
            @if(in_array(Auth::user()->role, ['admin','tu']))
            <a href="{{ route('kelas.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition
                      {{ request()->routeIs('kelas.*') ? 'sidebar-active shadow-md' : 'text-white/80 hover:bg-white/10 hover:text-white' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
                <span>Master Kelas</span>
            </a>
            <a href="{{ route('rombel.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition
                      {{ request()->routeIs('rombel.*') || request()->routeIs('tahun-ajaran.*') ? 'sidebar-active shadow-md' : 'text-white/80 hover:bg-white/10 hover:text-white' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <span>Rombel</span>
            </a>
            @endif

            {{-- PPDB --}}
            @if(in_array(Auth::user()->role, ['admin','tu','staf','kepala_sekolah']))
            <a href="{{ route('ppdb.index') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition
                      {{ request()->routeIs('ppdb.*') ? 'sidebar-active shadow-md' : 'text-white/80 hover:bg-white/10 hover:text-white' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                </svg>
                <span>Registrasi Siswa Baru</span>
            </a>
            @endif

            {{-- Tagihan Sekolah --}}
            @if(in_array(Auth::user()->role, ['admin','tu','staf','kepala_sekolah']))
            <div x-data="{ open: {{ request()->routeIs('bills.*') ? 'true' : 'false' }} }">
                <button @click="open = !open"
                        class="w-full flex justify-between items-center px-3 py-2.5 rounded-xl text-sm font-medium transition
                               {{ request()->routeIs('bills.*') ? 'bg-white/15 text-white' : 'text-white/80 hover:bg-white/10 hover:text-white' }}">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <span>Tagihan Sekolah</span>
                    </div>
                    <svg class="w-4 h-4 transition-transform duration-200" :class="{'rotate-180': open}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div x-show="open" x-cloak class="sidebar-submenu mt-1 space-y-1 rounded-xl p-1">
                    <a href="{{ route('bills.index') }}"
                       class="flex items-center gap-3 px-3 py-2 rounded-lg text-xs transition
                              {{ request()->routeIs('bills.index') ? 'sidebar-active font-bold shadow-sm' : 'text-white/70 hover:text-white hover:bg-white/10' }}">
                        <span>Monitoring Tagihan</span>
                    </a>
                    @if(in_array(Auth::user()->role, ['admin','tu']))
                    <a href="{{ route('bills.create') }}"
                       class="flex items-center gap-3 px-3 py-2 rounded-lg text-xs transition
                              {{ request()->routeIs('bills.create') ? 'sidebar-active font-bold shadow-sm' : 'text-white/70 hover:text-white hover:bg-white/10' }}">
                        <span>Buat Tagihan Massal</span>
                    </a>
                    @endif
                </div>
            </div>
            @endif

            {{-- POS Sekolah --}}
            @if(in_array(Auth::user()->role, ['admin','tu','staf','kepala_sekolah']))
            <div x-data="{ open: {{ request()->routeIs('pos.*') ? 'true' : 'false' }} }">
                <button @click="open = !open"
                        class="w-full flex justify-between items-center px-3 py-2.5 rounded-xl text-sm font-medium transition
                               {{ request()->routeIs('pos.*') ? 'bg-white/15 text-white' : 'text-white/80 hover:bg-white/10 hover:text-white' }}">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                        </svg>
                        <span>POS Sekolah</span>
                    </div>
                    <svg class="w-4 h-4 transition-transform duration-200" :class="{'rotate-180': open}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div x-show="open" x-cloak class="sidebar-submenu mt-1 space-y-1 rounded-xl p-1">
                    @if(in_array(Auth::user()->role, ['admin','tu']))
                    <a href="{{ route('pos.items.index') }}"
                       class="flex items-center gap-3 px-3 py-2 rounded-lg text-xs transition
                              {{ request()->routeIs('pos.items.*') ? 'sidebar-active font-bold shadow-sm' : 'text-white/70 hover:text-white hover:bg-white/10' }}">
                        <span>Master Barang</span>
                    </a>
                    <a href="{{ route('pos.bundles.index') }}"
                       class="flex items-center gap-3 px-3 py-2 rounded-lg text-xs transition
                              {{ request()->routeIs('pos.bundles.*') ? 'sidebar-active font-bold shadow-sm' : 'text-white/70 hover:text-white hover:bg-white/10' }}">
                        <span>Paket Bundling</span>
                    </a>
                    @endif
                    @if(in_array(Auth::user()->role, ['admin','tu','staf']))
                    <a href="{{ route('pos.transaction') }}"
                       class="flex items-center gap-3 px-3 py-2 rounded-lg text-xs transition
                              {{ request()->routeIs('pos.transaction') ? 'sidebar-active font-bold shadow-sm' : 'text-white/70 hover:text-white hover:bg-white/10' }}">
                        <span>Kasir / Transaksi</span>
                    </a>
                    @endif
                    @if(in_array(Auth::user()->role, ['admin','tu','kepala_sekolah']))
                    <a href="{{ route('pos.history.index') }}"
                       class="flex items-center gap-3 px-3 py-2 rounded-lg text-xs transition
                              {{ request()->routeIs('pos.history.*') ? 'sidebar-active font-bold shadow-sm' : 'text-white/70 hover:text-white hover:bg-white/10' }}">
                        <span>Riwayat Transaksi</span>
                    </a>
                    @endif
                </div>
            </div>
            @endif

            {{-- Konfigurasi: admin only --}}
            @if(Auth::user()->role === 'admin')
            <p class="px-3 text-[10px] font-bold text-white/50 uppercase tracking-widest mb-1 mt-5">Pengaturan</p>
            <div x-data="{ open: {{ request()->routeIs('settings.*') || request()->routeIs('school.*') ? 'true' : 'false' }} }">
                <button @click="open = !open"
                        class="w-full flex justify-between items-center px-3 py-2.5 rounded-xl text-sm font-medium transition
                               {{ request()->routeIs('settings.*') || request()->routeIs('school.*') ? 'bg-white/15 text-white' : 'text-white/80 hover:bg-white/10 hover:text-white' }}">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <span>Konfigurasi</span>
                    </div>
                    <svg class="w-4 h-4 transition-transform duration-200" :class="{'rotate-180': open}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div x-show="open" x-cloak class="sidebar-submenu mt-1 space-y-1 rounded-xl p-1">
                    <a href="{{ route('settings.integration') }}"
                       class="flex items-center gap-3 px-3 py-2 rounded-lg text-xs transition
                              {{ request()->routeIs('settings.integration') ? 'sidebar-active font-bold shadow-sm' : 'text-white/70 hover:text-white hover:bg-white/10' }}">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 010 5.656m-3.656-5.656a4 4 0 010 5.656M8 12h8"/>
                        </svg>
                        <span>Integrasi Kesiswaan</span>
                    </a>
                    <a href="{{ route('school.settings') }}"
                       class="flex items-center gap-3 px-3 py-2 rounded-lg text-xs transition
                              {{ request()->routeIs('school.settings') ? 'sidebar-active font-bold shadow-sm' : 'text-white/70 hover:text-white hover:bg-white/10' }}">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                        <span>Identitas Sekolah</span>
                    </a>
                </div>
            </div>
            @endif

            {{-- Profile --}}
            <a href="{{ route('profile.edit') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition
                      {{ request()->routeIs('profile.*') ? 'sidebar-active shadow-md' : 'text-white/80 hover:bg-white/10 hover:text-white' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                <span>Profile User</span>
            </a>
        </nav>

        {{-- Footer Sidebar: Avatar + Logout --}}
        <div class="sidebar-footer p-4 border-t border-white/10 flex-shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-full bg-white flex items-center justify-center font-bold shadow-lg flex-shrink-0"
                     style="color: var(--sb-active)">
                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                </div>
                <div class="overflow-hidden flex-1 min-w-0">
                    <p class="text-xs font-bold truncate text-white">{{ Auth::user()->name }}</p>
                    <p class="text-[10px] text-white/50 capitalize">{{ Auth::user()->role }}</p>
                </div>
                <form method="POST" action="{{ route('logout') }}" class="flex-shrink-0">
                    @csrf
                    <button type="submit" title="Logout"
                            class="text-white/50 hover:text-white transition p-1 rounded-lg hover:bg-white/10">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    {{-- Overlay mobile --}}
    <div x-show="sidebarOpen" @click="sidebarOpen = false"
         x-transition.opacity
         class="fixed inset-0 z-40 bg-black/50 lg:hidden backdrop-blur-sm" x-cloak></div>

    {{-- ═══════════════════════════════════════════════════════
         MAIN CONTENT
    ═══════════════════════════════════════════════════════ --}}
    <div class="flex flex-col min-h-screen lg:pl-64">

        <header class="h-16 flex items-center justify-between gap-3 px-6 md:px-8 bg-white border-b border-gray-200 shadow-sm sticky top-0 z-30">
            <div class="flex items-center gap-3 min-w-0 flex-1">
                <button @click="sidebarOpen = true" class="lg:hidden p-2 rounded-lg text-gray-500 hover:bg-gray-100 shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
                @if (isset($header))
                    <div class="font-bold text-gray-800 text-base md:text-lg truncate">{{ $header }}</div>
                @elseif (isset($pageTitle))
                    <h2 class="text-base md:text-lg font-bold text-gray-800 truncate">{{ $pageTitle }}</h2>
                @else
                    <h2 class="text-base md:text-lg font-bold text-gray-800">Dashboard</h2>
                @endif
            </div>
            @if (isset($headerAction))
            <div class="shrink-0">{!! $headerAction !!}</div>
            @endif
        </header>

        <main class="flex-1">
            <div class="w-full min-h-full px-6 py-6 lg:px-8 lg:py-8">
                {{ $slot }}
            </div>
        </main>

        <footer class="px-6 lg:px-8 py-4 text-xs text-gray-400">
            &copy; {{ date('Y') }} {{ $schoolName }}. All rights reserved.
        </footer>
    </div>

    {{-- Global SweetAlert Flash Handler --}}
    <script>
        const _swalToast = Swal.mixin({
            toast: true, position: 'top-end',
            showConfirmButton: false, timer: 3000, timerProgressBar: true,
            didOpen: (toast) => {
                toast.onmouseenter = Swal.stopTimer;
                toast.onmouseleave = Swal.resumeTimer;
            }
        });
        @if(session('success'))
            _swalToast.fire({ icon: 'success', title: '{{ addslashes(session('success')) }}' });
        @endif
        @if(session('error'))
            Swal.fire({ icon: 'error', title: 'Gagal!', text: '{{ addslashes(session('error')) }}', confirmButtonColor: '#ef4444' });
        @endif
        @if(session('warning'))
            _swalToast.fire({ icon: 'warning', title: '{{ addslashes(session('warning')) }}' });
        @endif
        @if(session('info'))
            _swalToast.fire({ icon: 'info', title: '{{ addslashes(session('info')) }}' });
        @endif
    </script>
</body>
</html>
