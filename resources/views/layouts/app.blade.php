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
    
    <style>
        [x-cloak] { display: none !important; }
        .custom-scroll::-webkit-scrollbar { width: 5px; height: 5px; }
        .custom-scroll::-webkit-scrollbar-track { background: transparent; }
        .custom-scroll::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.1); border-radius: 10px; }
    </style>
</head>
<body class="h-full font-sans antialiased text-gray-900" x-data="{ sidebarOpen: false }">

    <aside 
        class="fixed inset-y-0 left-0 z-50 w-64 bg-[#0ea5e9] text-white transition-transform duration-300 transform flex flex-col shadow-2xl h-full"
        :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'">
        
        <div class="h-16 flex items-center justify-between px-6 bg-[#0284c7] flex-shrink-0">
            <div class="flex items-center gap-2 font-bold text-xl tracking-wide">
                <div class="p-1 bg-white text-[#0ea5e9] rounded-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                </div>
                Sistem TU
            </div>
            <button @click="sidebarOpen = false" class="lg:hidden text-white/80 hover:text-white">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <nav class="flex-1 overflow-y-auto custom-scroll px-3 py-6 space-y-2">
            <p class="px-3 text-[10px] font-bold text-blue-100 uppercase tracking-widest mb-2 opacity-70">Menu Utama</p>
            
            <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm transition font-medium {{ request()->routeIs('dashboard') ? 'bg-white text-[#0ea5e9] shadow-md' : 'text-blue-50 hover:bg-white/10 hover:text-white' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                <span>Dashboard</span>
            </a>

            <a href="{{ route('students.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm transition font-medium {{ request()->routeIs('students.*') ? 'bg-white text-[#0ea5e9] shadow-md' : 'text-blue-50 hover:bg-white/10 hover:text-white' }}">
    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
    <span>Manajemen Siswa</span>
</a>

            <a href="{{ route('bills.create') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm transition font-medium {{ request()->routeIs('bills.*') ? 'bg-white text-[#0ea5e9] shadow-md' : 'text-blue-50 hover:bg-white/10 hover:text-white' }}">
    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
    <span>Buat Tagihan Massal</span>
</a>

            <a href="{{ route('bills.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm transition font-medium {{ request()->routeIs('bills.index') ? 'bg-white text-[#0ea5e9] shadow-md' : 'text-blue-50 hover:bg-white/10 hover:text-white' }}">
    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
    <span>Monitoring Tagihan</span>
</a>

            <div x-data="{ open: {{ request()->routeIs('pos.*') ? 'true' : 'false' }} }">
                <button @click="open = !open" class="w-full flex justify-between items-center px-3 py-2.5 rounded-xl text-sm transition font-medium {{ request()->routeIs('pos.*') ? 'bg-white/10 text-white' : 'text-blue-50 hover:bg-white/10 hover:text-white' }}">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                        <span>POS Sekolah</span>
                    </div>
                    <svg class="w-4 h-4 transition-transform duration-200" :class="{'rotate-180': open}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </button>
                <div x-show="open" x-cloak class="mt-1 space-y-1 bg-[#0284c7]/30 rounded-xl p-1">
                    <a href="{{ route('pos.items.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-xs transition {{ request()->routeIs('pos.items.*') ? 'bg-white text-[#0ea5e9] font-bold shadow-sm' : 'text-blue-100 hover:text-white hover:bg-white/10' }}">
                       <span>Master Barang</span>
                    </a>
                    <a href="{{ route('pos.transaction') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-xs transition {{ request()->routeIs('pos.transaction') ? 'bg-white text-[#0ea5e9] font-bold shadow-sm' : 'text-blue-100 hover:text-white hover:bg-white/10' }}">
                       <span>Kasir / Transaksi</span>
                    </a>
                    <a href="{{ route('pos.history.index') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-xs transition {{ request()->routeIs('pos.history.*') ? 'bg-white text-[#0ea5e9] font-bold shadow-sm' : 'text-blue-100 hover:text-white hover:bg-white/10' }}">
                       <span>Riwayat Transaksi</span>
                    </a>
                </div>
            </div>

            <p class="px-3 text-[10px] font-bold text-blue-100 uppercase tracking-widest mb-2 mt-6 opacity-70">Pengaturan</p>

            <div x-data="{ open: {{ request()->routeIs('settings.*') ? 'true' : 'false' }} }">
                <button @click="open = !open" class="w-full flex justify-between items-center px-3 py-2.5 rounded-xl text-sm transition font-medium {{ request()->routeIs('settings.*') ? 'bg-white/10 text-white' : 'text-blue-50 hover:bg-white/10 hover:text-white' }}">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        <span>Konfigurasi</span>
                    </div>
                    <svg class="w-4 h-4 transition-transform duration-200" :class="{'rotate-180': open}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                </button>
                <div x-show="open" x-cloak class="mt-1 space-y-1 bg-[#0284c7]/30 rounded-xl p-1">
                    <a href="{{ route('settings.integration') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-xs transition {{ request()->routeIs('settings.integration') ? 'bg-white text-[#0ea5e9] font-bold shadow-sm' : 'text-blue-100 hover:text-white hover:bg-white/10' }}">
                       <span>Integrasi Kesiswaan</span>
                    </a>
                </div>
            </div>

            <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm transition font-medium {{ request()->routeIs('profile.*') ? 'bg-white text-[#0ea5e9] shadow-md' : 'text-blue-50 hover:bg-white/10 hover:text-white' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                <span>Profile User</span>
            </a>
        </nav>

        <div class="p-4 border-t border-white/10 bg-[#0284c7] flex-shrink-0">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-full bg-white text-[#0ea5e9] flex items-center justify-center font-bold shadow-lg">
                    {{ substr(Auth::user()->name, 0, 1) }}
                </div>
                <div class="overflow-hidden flex-1">
                    <p class="text-xs font-bold truncate text-white">{{ Auth::user()->name }}</p>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-[10px] text-blue-200 hover:text-white hover:underline">Logout</button>
                    </form>
                </div>
            </div>
        </div>
    </aside>

    <div x-show="sidebarOpen" @click="sidebarOpen = false" 
         x-transition.opacity 
         class="fixed inset-0 z-40 bg-black/50 lg:hidden backdrop-blur-sm" x-cloak></div>

    <div class="flex flex-col min-h-screen lg:pl-64">
        
        <header class="h-16 flex items-center justify-between px-6 bg-white border-b border-gray-200 shadow-sm sticky top-0 z-30">
            <div class="flex items-center gap-4">
                <button @click="sidebarOpen = true" class="lg:hidden p-2 rounded-lg text-gray-500 hover:bg-gray-100">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                </button>
                
                @if (isset($header))
                    <div class="font-bold text-gray-800 text-lg">{{ $header }}</div>
                @else
                    <h2 class="text-lg font-bold text-gray-800">Dashboard</h2>
                @endif
            </div>
        </header>

        <main class="flex-1 p-6 lg:p-10">
            <div class="w-full">
                {{ $slot }}
            </div>
        </main>

        <footer class="py-6 text-center text-xs text-gray-400">
            &copy; {{ date('Y') }} Sistem Manajemen Sekolah. All rights reserved.
        </footer>
    </div>

    <script>
        @if(session('success'))
            Swal.fire({ icon: 'success', title: 'Berhasil!', text: "{{ session('success') }}", showConfirmButton: false, timer: 1500, background: '#fff', iconColor: '#0ea5e9' });
        @endif
        @if(session('error'))
            Swal.fire({ icon: 'error', title: 'Gagal', text: "{{ session('error') }}", confirmButtonColor: '#ef4444' });
        @endif
    </script>
</body>
</html>