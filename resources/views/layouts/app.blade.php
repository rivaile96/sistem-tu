<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>{{ config('app.name', 'Sistem Keuangan TU') }}</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        body { font-family: 'Inter', sans-serif; }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-gray-50 text-gray-800 antialiased" x-data="{ sidebarOpen: false }">

    <div x-show="sidebarOpen" @click="sidebarOpen = false" x-transition.opacity 
         class="fixed inset-0 z-20 bg-black/50 lg:hidden"></div>

    <div class="flex min-h-screen">
        
        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
               class="w-64 bg-[#0ea5e9] text-white flex flex-col fixed h-full z-30 transition-transform duration-300 lg:translate-x-0 shadow-xl shadow-sky-900/10">
            
            <div class="h-20 flex items-center justify-between px-6 border-b border-white/10">
                <h1 class="text-2xl font-bold tracking-wide flex items-center gap-2">
                    <span class="bg-white text-[#0ea5e9] p-1 rounded-lg">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                    </span>
                    Sistem TU
                </h1>
                <button @click="sidebarOpen = false" class="lg:hidden text-white hover:bg-white/10 p-1 rounded-lg">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto">
                <p class="px-4 text-xs font-bold text-blue-100 uppercase tracking-wider mb-2 opacity-80">Menu Utama</p>
                
                <a href="{{ route('dashboard') }}" 
                   class="flex items-center gap-3 px-4 py-3 rounded-xl transition duration-200 
                   {{ request()->routeIs('dashboard') ? 'bg-white/20 text-white shadow-inner font-semibold' : 'text-blue-50 hover:bg-white/10 hover:text-white' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                    <span>Dashboard</span>
                </a>

                <a href="{{ route('spp.index') }}" 
                   class="flex items-center gap-3 px-4 py-3 rounded-xl transition duration-200 
                   {{ request()->routeIs('spp.*') ? 'bg-white/20 text-white shadow-inner font-semibold' : 'text-blue-50 hover:bg-white/10 hover:text-white' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span>SPP & Tagihan</span>
                </a>

                <div x-data="{ open: {{ request()->routeIs('pos.*') ? 'true' : 'false' }} }">
                    <button @click="open = !open" 
                       class="w-full flex justify-between items-center px-4 py-3 rounded-xl transition duration-200 
                       {{ request()->routeIs('pos.*') ? 'bg-white/10 text-white shadow-inner' : 'text-blue-50 hover:bg-white/10 hover:text-white' }}">
                        
                        <div class="flex items-center gap-3">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                            <span>POS Sekolah</span>
                        </div>
                        
                        <svg class="w-4 h-4 transition-transform duration-200" :class="{'rotate-180': open}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                    </button>

                    <div x-show="open" 
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="opacity-0 transform -translate-y-2"
                         x-transition:enter-end="opacity-100 transform translate-y-0"
                         class="space-y-1 pt-2 pb-2">
                        
                        <a href="{{ route('pos.items.index') }}" 
                           class="flex items-center gap-3 pl-12 pr-4 py-2 rounded-xl text-sm transition duration-200
                           {{ request()->routeIs('pos.items.*') ? 'text-white font-bold bg-white/10' : 'text-blue-100 hover:text-white hover:bg-white/5' }}">
                           <svg class="w-4 h-4 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                           <span>Master Barang</span>
                        </a>

                        <a href="{{ route('pos.transaction') }}" 
                           class="flex items-center gap-3 pl-12 pr-4 py-2 rounded-xl text-sm transition duration-200
                           {{ request()->routeIs('pos.transaction') ? 'text-white font-bold bg-white/10' : 'text-blue-100 hover:text-white hover:bg-white/5' }}">
                           <svg class="w-4 h-4 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                           <span>Kasir / Transaksi</span>
                        </a>

                        <a href="{{ route('pos.history.index') }}" 
                           class="flex items-center gap-3 pl-12 pr-4 py-2 rounded-xl text-sm transition duration-200
                           {{ request()->routeIs('pos.history.*') ? 'text-white font-bold bg-white/10' : 'text-blue-100 hover:text-white hover:bg-white/5' }}">
                           <svg class="w-4 h-4 opacity-70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                           <span>Riwayat Penjualan</span>
                        </a>
                    </div>
                </div>

                <a href="#" 
                   class="flex items-center gap-3 px-4 py-3 rounded-xl transition duration-200 text-blue-50 hover:bg-white/10 hover:text-white">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    <span>Laporan</span>
                </a>

                <div class="pt-6 pb-2">
                    <p class="px-4 text-xs font-bold text-blue-100 uppercase tracking-wider opacity-80">General</p>
                </div>
                
                <a href="#" 
                   class="flex items-center gap-3 px-4 py-3 rounded-xl transition duration-200 text-blue-50 hover:bg-white/10 hover:text-white">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    <span>Pengaturan</span>
                </a>
            </nav>

            <div class="p-4 border-t border-white/10 bg-black/10">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-white text-[#0ea5e9] flex items-center justify-center text-lg font-bold shadow-lg">
                        A
                    </div>
                    <div>
                        <p class="text-sm font-bold">Admin TU</p>
                        <p class="text-xs text-blue-200">Administrator</p>
                    </div>
                </div>
            </div>
        </aside>

        <main class="flex-1 lg:ml-64 p-8 transition-all duration-300">
            
            <header class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
                <div class="flex items-center gap-4">
                    <button @click="sidebarOpen = true" class="lg:hidden p-2 rounded-lg bg-white border border-gray-200 text-gray-600 shadow-sm">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                    </button>

                    <div>
                        <h2 class="text-2xl font-bold text-gray-800">Halo, Admin TU! 👋</h2>
                        <p class="text-gray-500 text-sm mt-1 hidden md:block">Selamat bekerja kembali, kelola keuangan sekolah.</p>
                    </div>
                </div>
                
                <div class="flex items-center gap-4 w-full md:w-auto">
                    <form action="{{ route('spp.index') }}" method="GET" class="relative w-full md:w-64">
                        <input type="text" name="search" value="{{ request('search') }}" 
                               placeholder="Cari siswa / NIS..." 
                               class="w-full pl-10 pr-4 py-2.5 rounded-full border border-gray-200 bg-white focus:outline-none focus:ring-2 focus:ring-[#0ea5e9]/50 focus:border-[#0ea5e9] text-sm shadow-sm transition">
                        <svg class="w-4 h-4 text-gray-400 absolute left-3 top-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </form>
                    
                    <a href="{{ route('spp.create_generate') }}" class="bg-[#0ea5e9] hover:bg-sky-600 text-white px-5 py-2.5 rounded-xl text-sm font-medium flex items-center gap-2 transition shadow-lg shadow-sky-200 active:scale-95 whitespace-nowrap">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                        Buat Tagihan
                    </a>
                </div>
            </header>

            @yield('content')
            
        </main>
    </div>

    <script>
        // Cek Session SUKSES
        @if(session('success'))
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: "{{ session('success') }}",
                showConfirmButton: false,
                timer: 2000,
                timerProgressBar: true,
                background: '#ffffff',
                iconColor: '#0ea5e9'
            });
        @endif

        // Cek Session ERROR
        @if(session('error'))
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: "{{ session('error') }}",
                confirmButtonColor: '#ef4444',
            });
        @endif

        // Fungsi Global untuk Konfirmasi Hapus
        function confirmDelete(event) {
            event.preventDefault(); 
            const form = event.target.closest('form'); 

            Swal.fire({
                title: 'Yakin mau hapus?',
                text: "Data yang dihapus tidak bisa balik lagi!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#94a3b8',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        }
    </script>
</body>
</html>