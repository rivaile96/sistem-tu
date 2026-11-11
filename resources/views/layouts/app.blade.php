<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Kaffah Sistem TU') }}</title>
    
    <!-- Figtree Font (SPMB Design Reference) -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet" />
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        [x-cloak] { display: none !important; }
        .custom-scrollbar::-webkit-scrollbar { width: 5px; height: 5px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.2); border-radius: 9999px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,0.4); }
    </style>
</head>
<body class="h-full font-sans antialiased bg-slate-50 text-slate-900" 
      x-data="{ 
          isCollapsed: localStorage.getItem('tu_sidebar_collapsed') === 'true',
          mobileMenuOpen: false,
          toggleCollapse() {
              this.isCollapsed = !this.isCollapsed;
              localStorage.setItem('tu_sidebar_collapsed', this.isCollapsed);
          }
      }">

    <!-- MOBILE BACKDROP -->
    <div x-show="mobileMenuOpen" 
         @click="mobileMenuOpen = false" 
         x-transition:enter="transition-opacity ease-linear duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-linear duration-300"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-40 bg-slate-900/60 lg:hidden backdrop-blur-sm" 
         x-cloak></div>

    <!-- SIDEBAR (SPMB MATCHING #178BC1) -->
    <aside 
        class="fixed top-0 left-0 z-50 h-screen flex flex-col bg-[#178BC1] text-white shadow-2xl overflow-hidden font-sans transition-all duration-300 ease-in-out"
        :class="{
            'w-[280px]': !isCollapsed,
            'w-[90px]': isCollapsed,
            'translate-x-0': mobileMenuOpen,
            '-translate-x-full lg:translate-x-0': !mobileMenuOpen
        }">
        
        <!-- HEADER DENGAN LOGO ASLI -->
        <div class="h-20 flex items-center border-b border-white/20 shrink-0 transition-all duration-300"
             :class="isCollapsed ? 'justify-center px-2' : 'justify-between px-6'">
            <div x-show="!isCollapsed" class="flex items-center gap-3 overflow-hidden" x-transition>
                <div class="flex items-center justify-center w-10 h-10 p-1 bg-white rounded-lg shadow-sm text-[#178BC1] font-black shrink-0 text-xl tracking-tight">
                    K
                </div>
                <div class="truncate">
                    <h1 class="text-sm font-bold tracking-wide truncate">
                        KAFFAH <span class="font-light opacity-80">SISTEM TU</span>
                    </h1>
                    <p class="text-[10px] text-blue-100 opacity-80 truncate font-medium">Tata Usaha & Keuangan</p>
                </div>
            </div>

            <div x-show="isCollapsed" class="flex items-center justify-center" x-cloak>
                <div class="flex items-center justify-center w-10 h-10 p-1 bg-white rounded-lg shadow-sm text-[#178BC1] font-black shrink-0 text-xl">
                    K
                </div>
            </div>

            <!-- TOGGLE BUTTON DESKTOP -->
            <button @click="toggleCollapse()" 
                    class="hidden lg:flex p-2 transition-colors rounded-lg hover:bg-white/20 shrink-0 text-white"
                    :title="isCollapsed ? 'Perluas Sidebar' : 'Ciutkan Sidebar'">
                <!-- Chevron Left -->
                <svg x-show="!isCollapsed" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                    <polyline points="15 18 9 12 15 6"></polyline>
                </svg>
                <!-- Chevron Right -->
                <svg x-show="isCollapsed" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" x-cloak>
                    <polyline points="9 18 15 12 9 6"></polyline>
                </svg>
            </button>

            <!-- CLOSE BUTTON MOBILE -->
            <button @click="mobileMenuOpen = false" class="lg:hidden p-2 text-white/80 hover:text-white rounded-lg">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <!-- MENU LIST (SCROLLABLE) -->
        <nav class="flex-1 px-3 py-6 space-y-1.5 overflow-y-auto custom-scrollbar">
            
            <!-- SECTION: UTAMA -->
            <div x-show="!isCollapsed" class="px-4 mt-2 mb-2 text-[10px] font-bold uppercase tracking-widest text-blue-200 opacity-70 truncate">
                Utama
            </div>

            <!-- Dashboard -->
            <a href="{{ route('dashboard') }}" 
               class="relative flex items-center py-3 rounded-xl transition-all group {{ request()->routeIs('dashboard') ? 'bg-white text-[#178BC1] shadow-lg font-bold' : 'text-white hover:bg-white/10 font-medium' }}"
               :class="isCollapsed ? 'justify-center px-0' : 'px-4 gap-3'"
               title="Dashboard">
                <div class="shrink-0 transition-transform group-hover:scale-110">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                        <rect x="3" y="3" width="7" height="7"></rect>
                        <rect x="14" y="3" width="7" height="7"></rect>
                        <rect x="14" y="14" width="7" height="7"></rect>
                        <rect x="3" y="14" width="7" height="7"></rect>
                    </svg>
                </div>
                <span x-show="!isCollapsed" class="text-sm whitespace-nowrap" x-transition>Dashboard</span>
            </a>

            <!-- SECTION: AKADEMIK & SISWA -->
            <div x-show="!isCollapsed" class="px-4 mt-4 mb-2 text-[10px] font-bold uppercase tracking-widest text-blue-200 opacity-70 truncate">
                Akademik & Siswa
            </div>

            <!-- Data Siswa -->
            <a href="{{ route('students.index') }}" 
               class="relative flex items-center py-3 rounded-xl transition-all group {{ request()->routeIs('students.*') ? 'bg-white text-[#178BC1] shadow-lg font-bold' : 'text-white hover:bg-white/10 font-medium' }}"
               :class="isCollapsed ? 'justify-center px-0' : 'px-4 gap-3'"
               title="Data Siswa">
                <div class="shrink-0 transition-transform group-hover:scale-110">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                        <circle cx="9" cy="7" r="4"></circle>
                        <path d="M22 21v-2a4 4 0 0 0-3-3.87"></path>
                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                    </svg>
                </div>
                <span x-show="!isCollapsed" class="text-sm whitespace-nowrap" x-transition>Data Siswa</span>
            </a>

            <!-- SECTION: KEUANGAN & TAGIHAN -->
            <div x-show="!isCollapsed" class="px-4 mt-4 mb-2 text-[10px] font-bold uppercase tracking-widest text-blue-200 opacity-70 truncate">
                Keuangan & Tagihan
            </div>

            <!-- Daftar Tagihan -->
            <a href="{{ route('bills.index') }}" 
               class="relative flex items-center py-3 rounded-xl transition-all group {{ request()->routeIs('bills.index') ? 'bg-white text-[#178BC1] shadow-lg font-bold' : 'text-white hover:bg-white/10 font-medium' }}"
               :class="isCollapsed ? 'justify-center px-0' : 'px-4 gap-3'"
               title="Daftar Tagihan">
                <div class="shrink-0 transition-transform group-hover:scale-110">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                        <rect x="2" y="5" width="20" height="14" rx="2"></rect>
                        <line x1="2" y1="10" x2="22" y2="10"></line>
                    </svg>
                </div>
                <span x-show="!isCollapsed" class="text-sm whitespace-nowrap" x-transition>Daftar Tagihan</span>
            </a>

            <!-- Buat Tagihan -->
            <a href="{{ route('bills.create') }}" 
               class="relative flex items-center py-3 rounded-xl transition-all group {{ request()->routeIs('bills.create') ? 'bg-white text-[#178BC1] shadow-lg font-bold' : 'text-white hover:bg-white/10 font-medium' }}"
               :class="isCollapsed ? 'justify-center px-0' : 'px-4 gap-3'"
               title="Buat Tagihan Manual">
                <div class="shrink-0 transition-transform group-hover:scale-110">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                        <line x1="12" y1="18" x2="12" y2="12"></line>
                        <line x1="9" y1="15" x2="15" y2="15"></line>
                    </svg>
                </div>
                <span x-show="!isCollapsed" class="text-sm whitespace-nowrap" x-transition>Buat Tagihan</span>
            </a>

            <!-- Generate SPP Bulanan -->
            <a href="{{ route('finance.spp.generate.index') }}" 
               class="relative flex items-center py-3 rounded-xl transition-all group {{ request()->routeIs('finance.spp.generate.*') ? 'bg-white text-[#178BC1] shadow-lg font-bold' : 'text-white hover:bg-white/10 font-medium' }}"
               :class="isCollapsed ? 'justify-center px-0' : 'px-4 gap-3'"
               title="Generate SPP">
                <div class="shrink-0 transition-transform group-hover:scale-110">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                        <path d="M21 2v6h-6"></path>
                        <path d="M3 12a9 9 0 0 1 15-6.7L21 8"></path>
                        <path d="M3 22v-6h6"></path>
                        <path d="M21 12a9 9 0 0 1-15 6.7L3 16"></path>
                    </svg>
                </div>
                <span x-show="!isCollapsed" class="text-sm whitespace-nowrap" x-transition>Generate SPP</span>
            </a>

            <!-- Pengaturan Finance -->
            <a href="{{ route('finance.settings.index') }}" 
               class="relative flex items-center py-3 rounded-xl transition-all group {{ request()->routeIs('finance.settings.*') ? 'bg-white text-[#178BC1] shadow-lg font-bold' : 'text-white hover:bg-white/10 font-medium' }}"
               :class="isCollapsed ? 'justify-center px-0' : 'px-4 gap-3'"
               title="Pengaturan Finance">
                <div class="shrink-0 transition-transform group-hover:scale-110">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                        <line x1="4" y1="21" x2="4" y2="14"></line>
                        <line x1="4" y1="10" x2="4" y2="3"></line>
                        <line x1="12" y1="21" x2="12" y2="12"></line>
                        <line x1="12" y1="8" x2="12" y2="3"></line>
                        <line x1="20" y1="21" x2="20" y2="16"></line>
                        <line x1="20" y1="12" x2="20" y2="3"></line>
                        <line x1="1" y1="14" x2="7" y2="14"></line>
                        <line x1="9" y1="8" x2="15" y2="8"></line>
                        <line x1="17" y1="16" x2="23" y2="16"></line>
                    </svg>
                </div>
                <span x-show="!isCollapsed" class="text-sm whitespace-nowrap" x-transition>Pengaturan Finance</span>
            </a>

            <!-- SECTION: POS & KANTIN -->
            <div x-show="!isCollapsed" class="px-4 mt-4 mb-2 text-[10px] font-bold uppercase tracking-widest text-blue-200 opacity-70 truncate">
                POS & Kantin
            </div>

            <!-- Transaksi Kasir -->
            <a href="{{ route('pos.transaction') }}" 
               class="relative flex items-center py-3 rounded-xl transition-all group {{ request()->routeIs('pos.transaction*') ? 'bg-white text-[#178BC1] shadow-lg font-bold' : 'text-white hover:bg-white/10 font-medium' }}"
               :class="isCollapsed ? 'justify-center px-0' : 'px-4 gap-3'"
               title="Kasir / Transaksi POS">
                <div class="shrink-0 transition-transform group-hover:scale-110">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                        <circle cx="9" cy="21" r="1"></circle>
                        <circle cx="20" cy="21" r="1"></circle>
                        <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                    </svg>
                </div>
                <span x-show="!isCollapsed" class="text-sm whitespace-nowrap" x-transition>Kasir POS</span>
            </a>

            <!-- Riwayat POS -->
            <a href="{{ route('pos.history.index') }}" 
               class="relative flex items-center py-3 rounded-xl transition-all group {{ request()->routeIs('pos.history.*') ? 'bg-white text-[#178BC1] shadow-lg font-bold' : 'text-white hover:bg-white/10 font-medium' }}"
               :class="isCollapsed ? 'justify-center px-0' : 'px-4 gap-3'"
               title="Riwayat Transaksi POS">
                <div class="shrink-0 transition-transform group-hover:scale-110">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                        <line x1="16" y1="13" x2="8" y2="13"></line>
                        <line x1="16" y1="17" x2="8" y2="17"></line>
                        <polyline points="10 9 9 9 8 9"></polyline>
                    </svg>
                </div>
                <span x-show="!isCollapsed" class="text-sm whitespace-nowrap" x-transition>Riwayat POS</span>
            </a>

            <!-- Master Barang POS -->
            <a href="{{ route('pos.items.index') }}" 
               class="relative flex items-center py-3 rounded-xl transition-all group {{ request()->routeIs('pos.items.*') ? 'bg-white text-[#178BC1] shadow-lg font-bold' : 'text-white hover:bg-white/10 font-medium' }}"
               :class="isCollapsed ? 'justify-center px-0' : 'px-4 gap-3'"
               title="Master Barang">
                <div class="shrink-0 transition-transform group-hover:scale-110">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                        <line x1="16.5" y1="9.4" x2="7.5" y2="4.21"></line>
                        <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                        <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                        <line x1="12" y1="22.08" x2="12" y2="12"></line>
                    </svg>
                </div>
                <span x-show="!isCollapsed" class="text-sm whitespace-nowrap" x-transition>Master Barang</span>
            </a>

            <!-- SECTION: PENGATURAN -->
            <div x-show="!isCollapsed" class="px-4 mt-4 mb-2 text-[10px] font-bold uppercase tracking-widest text-blue-200 opacity-70 truncate">
                Pengaturan
            </div>

            <!-- Integrasi PPDB -->
            <a href="{{ route('settings.integration') }}" 
               class="relative flex items-center py-3 rounded-xl transition-all group {{ request()->routeIs('settings.integration') ? 'bg-white text-[#178BC1] shadow-lg font-bold' : 'text-white hover:bg-white/10 font-medium' }}"
               :class="isCollapsed ? 'justify-center px-0' : 'px-4 gap-3'"
               title="Integrasi Kesiswaan">
                <div class="shrink-0 transition-transform group-hover:scale-110">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                        <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path>
                        <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path>
                    </svg>
                </div>
                <span x-show="!isCollapsed" class="text-sm whitespace-nowrap" x-transition>Integrasi PPDB</span>
            </a>

            <!-- Identitas Sekolah -->
            <a href="{{ route('school.settings') }}" 
               class="relative flex items-center py-3 rounded-xl transition-all group {{ request()->routeIs('school.settings') ? 'bg-white text-[#178BC1] shadow-lg font-bold' : 'text-white hover:bg-white/10 font-medium' }}"
               :class="isCollapsed ? 'justify-center px-0' : 'px-4 gap-3'"
               title="Identitas Sekolah">
                <div class="shrink-0 transition-transform group-hover:scale-110">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                        <rect x="4" y="2" width="16" height="20" rx="2" ry="2"></rect>
                        <line x1="9" y1="22" x2="9" y2="22.01"></line>
                        <line x1="15" y1="22" x2="15" y2="22.01"></line>
                        <line x1="9" y1="6" x2="9" y2="6.01"></line>
                        <line x1="15" y1="6" x2="15" y2="6.01"></line>
                        <line x1="9" y1="10" x2="9" y2="10.01"></line>
                        <line x1="15" y1="10" x2="15" y2="10.01"></line>
                        <line x1="9" y1="14" x2="9" y2="14.01"></line>
                        <line x1="15" y1="14" x2="15" y2="14.01"></line>
                        <line x1="9" y1="18" x2="9" y2="18.01"></line>
                        <line x1="15" y1="18" x2="15" y2="18.01"></line>
                    </svg>
                </div>
                <span x-show="!isCollapsed" class="text-sm whitespace-nowrap" x-transition>Identitas Sekolah</span>
            </a>

            <!-- Profile User -->
            <a href="{{ route('profile.edit') }}" 
               class="relative flex items-center py-3 rounded-xl transition-all group {{ request()->routeIs('profile.*') ? 'bg-white text-[#178BC1] shadow-lg font-bold' : 'text-white hover:bg-white/10 font-medium' }}"
               :class="isCollapsed ? 'justify-center px-0' : 'px-4 gap-3'"
               title="Pengaturan Profil">
                <div class="shrink-0 transition-transform group-hover:scale-110">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                        <circle cx="12" cy="7" r="4"></circle>
                    </svg>
                </div>
                <span x-show="!isCollapsed" class="text-sm whitespace-nowrap" x-transition>Profil User</span>
            </a>

        </nav>

        <!-- FOOTER SIDEBAR (SPMB MATCHING LOGOUT) -->
        <div class="p-4 border-t border-white/20 bg-[#126da3]/20 shrink-0">
            <form id="logout-form" method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="button" 
                        onclick="confirmLogout()" 
                        class="w-full flex items-center gap-3 bg-white/10 hover:bg-red-500 text-white py-3 rounded-xl transition-all duration-300 group"
                        :class="isCollapsed ? 'justify-center px-0' : 'px-4'"
                        title="Logout">
                    <svg class="w-5 h-5 shrink-0 transition-transform group-hover:scale-110" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                        <polyline points="16 17 21 12 16 7"></polyline>
                        <line x1="21" y1="12" x2="9" y2="12"></line>
                    </svg>
                    <span x-show="!isCollapsed" class="text-sm font-bold tracking-wide" x-transition>LOGOUT</span>
                </button>
            </form>
        </div>
    </aside>

    <!-- CONTENT WRAPPER -->
    <div class="flex flex-col min-h-screen transition-all duration-300 ease-in-out"
         :class="isCollapsed ? 'lg:ml-[90px]' : 'lg:ml-[280px]'">
        
        <!-- HEADER / TOPBAR (SPMB MATCHING h-20 bg-white/80 backdrop-blur-md) -->
        <header class="h-20 bg-white/80 backdrop-blur-md border-b border-slate-200 flex items-center justify-between px-6 lg:px-8 sticky top-0 z-30">
            <div class="flex items-center gap-4">
                <!-- Mobile Menu Button -->
                <button @click="mobileMenuOpen = true" 
                        class="lg:hidden p-2 rounded-xl text-slate-600 hover:bg-slate-100 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                        <line x1="3" y1="12" x2="21" y2="12"></line>
                        <line x1="3" y1="6" x2="21" y2="6"></line>
                        <line x1="3" y1="18" x2="21" y2="18"></line>
                    </svg>
                </button>
                
                <div class="flex flex-col">
                    <h2 class="text-xl font-bold text-slate-800">
                        @if (isset($header))
                            {{ $header }}
                        @else
                            Halo, {{ Auth::user()->name ? explode(' ', Auth::user()->name)[0] : 'Admin' }}! 👋
                        @endif
                    </h2>
                    <p class="text-xs text-slate-500 hidden md:block font-medium">
                        {{ Auth::user()->role === 'admin' ? 'Tata Usaha Access' : 'Staff Access' }} • {{ Auth::user()->email ?? 'admin@kaffah.id' }}
                    </p>
                </div>
            </div>

            <!-- Profile Avatar Circle -->
            <div class="flex items-center gap-4">
                <a href="{{ route('profile.edit') }}" 
                   title="Pengaturan Profil"
                   class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center text-[#178BC1] font-black border-2 border-blue-200 overflow-hidden hover:ring-4 hover:ring-blue-100 transition-all cursor-pointer shadow-sm">
                    {{ strtoupper(substr(Auth::user()->name ?? 'A', 0, 1)) }}
                </a>
            </div>
        </header>

        <!-- MAIN BODY -->
        <main class="flex-1 p-6 md:p-8 space-y-6">
            {{ $slot }}
        </main>

        <!-- FOOTER (SPMB MATCHING) -->
        <footer class="p-6 text-center text-xs text-slate-400 border-t border-slate-200">
            &copy; {{ date('Y') }} Kaffah Islamic School - Secure System.
        </footer>
    </div>

    <!-- NOTIFICATION & LOGOUT SCRIPT -->
    <script>
        function confirmLogout() {
            Swal.fire({
                title: 'Keluar Aplikasi?',
                text: 'Sesi Anda akan diakhiri.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#178BC1',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Logout',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('logout-form').submit();
                }
            });
        }

        document.addEventListener('DOMContentLoaded', function () {
            @if(session('success'))
                Swal.fire({ 
                    icon: 'success', 
                    title: 'Berhasil!', 
                    text: "{{ session('success') }}", 
                    confirmButtonColor: '#178BC1',
                    timer: 2000 
                });
            @endif
            @if(session('error'))
                Swal.fire({ 
                    icon: 'error', 
                    title: 'Gagal', 
                    text: "{{ session('error') }}", 
                    confirmButtonColor: '#178BC1' 
                });
            @endif
            @if(session('warning'))
                Swal.fire({ 
                    icon: 'warning', 
                    title: 'Perhatian', 
                    text: "{{ session('warning') }}", 
                    confirmButtonColor: '#178BC1' 
                });
            @endif
        });
    </script>
</body>
</html>
