<x-app-layout>
    <div class="py-12 bg-gradient-to-b from-gray-50 to-blue-50 min-h-screen">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Header Card -->
            <div class="bg-gradient-to-r from-[#0284c7] to-[#0ea5e9] rounded-2xl shadow-2xl mb-8 overflow-hidden">
                <div class="p-8 relative overflow-hidden">
                    <div class="absolute -top-10 -right-10 w-32 h-32 bg-white opacity-10 rounded-full blur-2xl"></div>
                    <div class="absolute -bottom-10 -left-10 w-24 h-24 bg-white opacity-10 rounded-full blur-xl"></div>
                    
                    <div class="flex items-center gap-4 mb-4">
                        <div class="bg-white/20 p-3 rounded-xl backdrop-blur-sm">
                            <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                        </div>
                        <div>
                            <h1 class="text-3xl font-bold text-white">Identitas Sekolah</h1>
                            <p class="text-blue-100 mt-2 max-w-2xl">
                                Lengkapi identitas sekolah, logo, dan tanda tangan digital. Data ini akan otomatis muncul pada Kop Surat, Kwitansi, dan Laporan Resmi.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Form Card -->
            <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100">
                <form action="{{ route('school.update') }}" method="POST" enctype="multipart/form-data" class="p-8">
                    @csrf
                    
                    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                        
                        <!-- Logo Section -->
                        <div class="lg:col-span-3">
                            <div class="bg-gradient-to-br from-blue-50 to-white p-6 rounded-xl border border-blue-100 h-full">
                                <div class="text-center mb-6">
                                    <h3 class="font-bold text-gray-800 mb-1 flex items-center justify-center gap-2">
                                        <svg class="w-5 h-5 text-[#0284c7]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                        Logo Sekolah
                                    </h3>
                                    <p class="text-xs text-gray-500">Format: PNG/JPG • Max: 2MB</p>
                                </div>
                                
                                <div class="relative w-40 h-40 mx-auto">
                                    <div class="w-full h-full rounded-full border-4 border-white shadow-lg overflow-hidden group relative">
                                        @if(isset($settings['school_logo']))
                                            <img src="{{ asset('storage/'.$settings['school_logo']) }}" 
                                                 class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                                        @else
                                            <div class="w-full h-full bg-gradient-to-br from-gray-50 to-gray-100 flex flex-col items-center justify-center">
                                                <svg class="w-12 h-12 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                                </svg>
                                                <span class="text-xs font-medium text-gray-500">Upload Logo</span>
                                            </div>
                                        @endif
                                        
                                        <label class="absolute inset-0 bg-gradient-to-br from-[#0284c7]/80 to-[#0ea5e9]/80 flex flex-col items-center justify-center opacity-0 group-hover:opacity-100 transition-all duration-300 cursor-pointer backdrop-blur-sm rounded-full">
                                            <svg class="w-8 h-8 text-white mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            </svg>
                                            <span class="text-white text-xs font-bold">Ganti Logo</span>
                                            <input type="file" name="school_logo" class="hidden" accept="image/*">
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="mt-6 text-center">
                                    <div class="inline-flex items-center gap-1 text-xs text-gray-500 bg-gray-50 px-3 py-1 rounded-full">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        Disarankan bentuk persegi/bulat
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- School Details Section -->
                        <div class="lg:col-span-6">
                            <div class="space-y-6">
                                <!-- School Name -->
                                <div class="bg-gradient-to-r from-blue-50/50 to-white p-5 rounded-xl border border-blue-50">
                                    <label class="block font-bold text-gray-800 text-sm mb-3 flex items-center gap-2">
                                        <div class="p-2 bg-[#0284c7]/10 rounded-lg">
                                            <svg class="w-4 h-4 text-[#0284c7]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                            </svg>
                                        </div>
                                        Nama Sekolah
                                    </label>
                                    <div class="relative">
                                        <input type="text" 
                                               name="school_name" 
                                               value="{{ $settings['school_name'] ?? '' }}" 
                                               class="w-full pl-12 pr-4 py-3 rounded-lg border border-gray-200 focus:border-[#0284c7] focus:ring-2 focus:ring-[#0284c7]/20 bg-white transition-all duration-300"
                                               placeholder="Contoh: SDIT Kaffah Islamic School" 
                                               required>
                                        <div class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                            </svg>
                                        </div>
                                    </div>
                                </div>

                                <!-- School Address -->
                                <div class="bg-gradient-to-r from-blue-50/50 to-white p-5 rounded-xl border border-blue-50">
                                    <label class="block font-bold text-gray-800 text-sm mb-3 flex items-center gap-2">
                                        <div class="p-2 bg-[#0284c7]/10 rounded-lg">
                                            <svg class="w-4 h-4 text-[#0284c7]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            </svg>
                                        </div>
                                        Alamat Lengkap
                                    </label>
                                    <div class="relative">
                                        <textarea name="school_address" 
                                                  rows="3" 
                                                  class="w-full pl-12 pr-4 py-3 rounded-lg border border-gray-200 focus:border-[#0284c7] focus:ring-2 focus:ring-[#0284c7]/20 bg-white transition-all duration-300">{{ $settings['school_address'] ?? '' }}</textarea>
                                        <div class="absolute left-3 top-4 text-gray-400">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            </svg>
                                        </div>
                                    </div>
                                </div>

                                <!-- Phone & Head of Admin -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                    <div class="bg-gradient-to-r from-blue-50/50 to-white p-5 rounded-xl border border-blue-50">
                                        <label class="block font-bold text-gray-800 text-sm mb-3 flex items-center gap-2">
                                            <div class="p-2 bg-[#0284c7]/10 rounded-lg">
                                                <svg class="w-4 h-4 text-[#0284c7]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                                </svg>
                                            </div>
                                            No. Telepon
                                        </label>
                                        <div class="relative">
                                            <input type="text" 
                                                   name="school_phone" 
                                                   value="{{ $settings['school_phone'] ?? '' }}" 
                                                   class="w-full pl-12 pr-4 py-3 rounded-lg border border-gray-200 focus:border-[#0284c7] focus:ring-2 focus:ring-[#0284c7]/20 bg-white transition-all duration-300">
                                            <div class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                                                </svg>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="bg-gradient-to-r from-blue-50/50 to-white p-5 rounded-xl border border-blue-50">
                                        <label class="block font-bold text-gray-800 text-sm mb-3 flex items-center gap-2">
                                            <div class="p-2 bg-[#0284c7]/10 rounded-lg">
                                                <svg class="w-4 h-4 text-[#0284c7]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                                </svg>
                                            </div>
                                            Bendahara / Ka. TU
                                        </label>
                                        <div class="relative">
                                            <input type="text" 
                                                   name="head_of_admin" 
                                                   value="{{ $settings['head_of_admin'] ?? '' }}" 
                                                   class="w-full pl-12 pr-4 py-3 rounded-lg border border-gray-200 focus:border-[#0284c7] focus:ring-2 focus:ring-[#0284c7]/20 bg-white transition-all duration-300"
                                                   placeholder="Nama Pejabat TTD">
                                            <div class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                                </svg>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Signature Section -->
                        <div class="lg:col-span-3">
                            <div class="bg-gradient-to-br from-blue-50 to-white p-6 rounded-xl border border-blue-100 h-full">
                                <div class="text-center mb-6">
                                    <h3 class="font-bold text-gray-800 mb-1 flex items-center justify-center gap-2">
                                        <svg class="w-5 h-5 text-[#0284c7]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                        </svg>
                                        Tanda Tangan Digital
                                    </h3>
                                    <p class="text-xs text-gray-500">Wajib background transparan</p>
                                </div>
                                
                                <div class="relative h-36 bg-gradient-to-br from-white to-gray-50 rounded-xl border-2 border-dashed border-gray-300 hover:border-[#0284c7] transition-all duration-300 overflow-hidden group">
                                    @if(isset($settings['school_signature']))
                                        <img src="{{ asset('storage/'.$settings['school_signature']) }}" 
                                             class="h-full w-full object-contain p-4">
                                    @else
                                        <div class="h-full flex flex-col items-center justify-center text-gray-400">
                                            <svg class="w-10 h-10 mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                                            </svg>
                                            <span class="text-xs font-medium">Belum ada TTD</span>
                                        </div>
                                    @endif

                                    <label class="absolute inset-0 bg-white/90 flex flex-col items-center justify-center opacity-0 group-hover:opacity-100 transition-all duration-300 cursor-pointer backdrop-blur-sm">
                                        <div class="p-3 bg-[#0284c7] rounded-full mb-2">
                                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                                            </svg>
                                        </div>
                                        <span class="text-[#0284c7] text-sm font-bold">Upload File</span>
                                        <p class="text-xs text-gray-600 mt-1">Format PNG dengan transparan</p>
                                        <input type="file" name="school_signature" class="hidden" accept="image/*">
                                    </label>
                                </div>
                                
                                <div class="mt-6 text-center">
                                    <div class="inline-flex items-center gap-1 text-xs text-gray-500 bg-gray-50 px-3 py-1 rounded-full">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.995-.833-2.765 0L3.07 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                                        </svg>
                                        Format PNG transparan
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="mt-10 pt-8 border-t border-gray-100 flex justify-end">
                        <button type="submit" class="group relative bg-gradient-to-r from-[#0284c7] to-[#0ea5e9] hover:from-[#027ab8] hover:to-[#0d93d7] text-white font-bold py-3.5 px-10 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:-translate-y-0.5 flex items-center gap-3 overflow-hidden">
                            <div class="absolute inset-0 bg-white/10 transform -translate-x-full group-hover:translate-x-0 transition-transform duration-1000"></div>
                            <svg class="w-5 h-5 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path>
                            </svg>
                            <span class="relative z-10">Simpan Perubahan</span>
                        </button>
                    </div>

                    {{-- ═══════════════════════════════════════
                         SECTION: WARNA SIDEBAR
                    ═══════════════════════════════════════ --}}
                    <div class="mt-10 pt-8 border-t border-gray-100">
                        <div class="flex items-center gap-3 mb-6">
                            <div class="p-2 bg-purple-50 rounded-xl">
                                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-800">Tema Warna Sidebar</h3>
                                <p class="text-xs text-gray-500">Pilih warna sidebar & login page. Perubahan langsung terlihat setelah disimpan.</p>
                            </div>
                        </div>

                        @php
                            $currentColor = $settings['sidebar_color'] ?? 'blue';
                            $colorOptions = [
                                'blue'   => ['label' => 'Sky Blue',    'bg' => '#0ea5e9', 'dark' => '#0284c7'],
                                'indigo' => ['label' => 'Indigo',      'bg' => '#6366f1', 'dark' => '#4f46e5'],
                                'violet' => ['label' => 'Violet',      'bg' => '#8b5cf6', 'dark' => '#7c3aed'],
                                'pink'   => ['label' => 'Pink',        'bg' => '#ec4899', 'dark' => '#db2777'],
                                'rose'   => ['label' => 'Rose Red',    'bg' => '#f43f5e', 'dark' => '#e11d48'],
                                'orange' => ['label' => 'Orange',      'bg' => '#f97316', 'dark' => '#ea580c'],
                                'amber'  => ['label' => 'Amber',       'bg' => '#f59e0b', 'dark' => '#d97706'],
                                'green'  => ['label' => 'Emerald',     'bg' => '#10b981', 'dark' => '#059669'],
                                'teal'   => ['label' => 'Teal',        'bg' => '#14b8a6', 'dark' => '#0d9488'],
                                'brown'  => ['label' => 'Brown',       'bg' => '#b45309', 'dark' => '#92400e'],
                                'slate'  => ['label' => 'Slate Gray',  'bg' => '#64748b', 'dark' => '#475569'],
                                'black'  => ['label' => 'Dark/Black',  'bg' => '#1e293b', 'dark' => '#0f172a'],
                            ];
                        @endphp

                        <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-3" id="colorGrid">
                            @foreach($colorOptions as $key => $opt)
                            <label class="cursor-pointer group" title="{{ $opt['label'] }}">
                                <input type="radio" name="sidebar_color" value="{{ $key }}"
                                       class="sr-only peer"
                                       {{ $currentColor === $key ? 'checked' : '' }}>
                                <div class="relative rounded-2xl overflow-hidden shadow-sm border-2 transition-all duration-200
                                            peer-checked:border-gray-800 peer-checked:shadow-lg peer-checked:scale-105
                                            border-transparent hover:border-gray-300 hover:scale-105"
                                     style="background: {{ $opt['bg'] }}">
                                    {{-- Mini preview sidebar --}}
                                    <div class="h-16 p-2 flex flex-col gap-1" style="background: {{ $opt['bg'] }}">
                                        <div class="h-2 rounded" style="background: {{ $opt['dark'] }}"></div>
                                        <div class="h-1.5 rounded bg-white/30 w-3/4"></div>
                                        <div class="h-1.5 rounded bg-white/30 w-1/2"></div>
                                        <div class="h-1.5 rounded bg-white/50 w-2/3"></div>
                                        <div class="h-1.5 rounded bg-white/30 w-3/4"></div>
                                    </div>
                                    {{-- Checkmark --}}
                                    <div class="absolute top-1 right-1 w-4 h-4 bg-white rounded-full items-center justify-center shadow
                                                hidden peer-checked:flex">
                                        <svg class="w-2.5 h-2.5 text-gray-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </div>
                                </div>
                                <p class="text-center text-xs text-gray-600 mt-1.5 font-medium truncate">{{ $opt['label'] }}</p>
                            </label>
                            @endforeach
                        </div>

                        {{-- Live preview strip --}}
                        <div class="mt-6 rounded-xl overflow-hidden border border-gray-200 shadow-sm">
                            <div class="flex items-center gap-2 px-4 py-2 text-xs font-bold text-white" id="previewHeader"
                                 style="background: {{ $colorOptions[$currentColor]['dark'] ?? '#0284c7' }}">
                                <div class="w-3 h-3 rounded-full bg-white/30"></div>
                                <span>Preview Sidebar</span>
                            </div>
                            <div class="flex gap-2 px-4 py-3" id="previewBody"
                                 style="background: {{ $colorOptions[$currentColor]['bg'] ?? '#0ea5e9' }}">
                                <div class="h-2 rounded bg-white w-1/4"></div>
                                <div class="h-2 rounded bg-white/40 w-1/3"></div>
                                <div class="h-2 rounded bg-white/40 w-1/5"></div>
                            </div>
                        </div>
                    </div>

                </form>
            </div>

            <!-- Footer Note -->
            <div class="mt-6 text-center">
                <p class="text-sm text-gray-500">
                    Perubahan akan diterapkan secara otomatis pada semua dokumen yang menggunakan identitas sekolah ini.
                </p>
            </div>
        </div>
    </div>

    <script>
        const colorMap = {
            blue:   { bg: '#0ea5e9', dark: '#0284c7' },
            indigo: { bg: '#6366f1', dark: '#4f46e5' },
            violet: { bg: '#8b5cf6', dark: '#7c3aed' },
            pink:   { bg: '#ec4899', dark: '#db2777' },
            rose:   { bg: '#f43f5e', dark: '#e11d48' },
            orange: { bg: '#f97316', dark: '#ea580c' },
            amber:  { bg: '#f59e0b', dark: '#d97706' },
            green:  { bg: '#10b981', dark: '#059669' },
            teal:   { bg: '#14b8a6', dark: '#0d9488' },
            brown:  { bg: '#b45309', dark: '#92400e' },
            slate:  { bg: '#64748b', dark: '#475569' },
            black:  { bg: '#1e293b', dark: '#0f172a' },
        };
        document.querySelectorAll('input[name="sidebar_color"]').forEach(radio => {
            radio.addEventListener('change', function () {
                const p = colorMap[this.value];
                if (!p) return;
                document.getElementById('previewHeader').style.background = p.dark;
                document.getElementById('previewBody').style.background   = p.bg;
            });
        });
    </script>

    <style>
        @keyframes fade-in {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .animate-fade-in {
            animation: fade-in 0.3s ease-out;
        }
        
        input:focus, textarea:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.1);
        }
        
        .group:hover .group-hover\:scale-110 {
            transform: scale(1.1);
        }
        
        .transition-all {
            transition-property: all;
            transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
            transition-duration: 300ms;
        }
    </style>
</x-app-layout>