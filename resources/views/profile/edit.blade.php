<x-app-layout>
    
    <div class="relative bg-gradient-to-r from-[#0284c7] via-[#0ea5e9] to-[#38bdf8] h-48 sm:h-56"></div>
        <div class="absolute inset-0 bg-black/10"></div>
        <div class="absolute bottom-0 left-0 w-full h-16 bg-gradient-to-t from-gray-100 to-transparent"></div>
    </>

    <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8 -mt-20 relative z-10 pb-12">
        
        <div class="flex flex-col lg:flex-row gap-8">
            
            <div class="lg:w-1/3 space-y-6">
                
                <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden text-center p-6 relative">
                    <div class="inline-block relative">
                        <div class="h-24 w-24 rounded-full bg-gray-200 border-4 border-white shadow-md mx-auto flex items-center justify-center text-2xl font-bold text-gray-500 overflow-hidden">
                            @if(Auth::user()->profile_photo_url ?? false)
                                <img src="{{ Auth::user()->profile_photo_url }}" alt="{{ Auth::user()->name }}" class="h-full w-full object-cover">
                            @else
                                {{ substr(Auth::user()->name, 0, 1) }}
                            @endif
                        </div>
                        <div class="absolute bottom-1 right-1 bg-green-500 h-5 w-5 rounded-full border-2 border-white" title="Online"></div>
                    </div>
                    
                    <h2 class="mt-4 text-xl font-bold text-gray-900">{{ Auth::user()->name }}</h2>
                    <p class="text-sm text-gray-500">{{ Auth::user()->email }}</p>

                    <div class="mt-4 flex justify-center gap-2">
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-600 border border-blue-100">
                            Administrator
                        </span>
                    </div>

                    <div class="mt-6 pt-6 border-t border-gray-100 grid grid-cols-2 divide-x divide-gray-100">
                        <div>
                            <span class="block text-xs text-gray-400 uppercase font-bold">Bergabung</span>
                            <span class="block text-sm font-bold text-gray-700">{{ Auth::user()->created_at->format('d M Y') }}</span>
                        </div>
                        <div>
                            <span class="block text-xs text-gray-400 uppercase font-bold">Status</span>
                            <span class="block text-sm font-bold text-green-600">Active</span>
                        </div>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-indigo-50 to-white rounded-2xl shadow-sm border border-indigo-100 p-6">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="p-2 bg-indigo-100 rounded-lg text-indigo-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                        </div>
                        <h3 class="font-bold text-gray-800">Keamanan Akun</h3>
                    </div>
                    <p class="text-sm text-gray-600 leading-relaxed">
                        Pastikan password Anda kuat dan tidak digunakan di aplikasi lain. Ganti password secara berkala untuk menjaga keamanan data sekolah.
                    </p>
                </div>

            </div>

            <div class="lg:w-2/3 space-y-6">
                
                <div class="bg-white p-6 sm:p-8 rounded-2xl shadow-sm border border-gray-100">
                    <div class="flex items-center gap-3 mb-6 border-b border-gray-100 pb-4">
                        <div class="p-2 bg-blue-50 rounded-lg text-blue-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-800">Informasi Pribadi</h3>
                            <p class="text-xs text-gray-500">Update nama dan alamat email akun Anda.</p>
                        </div>
                    </div>
                    
                    <div class="max-w-xl">
                        @include('profile.partials.update-profile-information-form')
                    </div>
                </div>

                <div class="bg-white p-6 sm:p-8 rounded-2xl shadow-sm border border-gray-100">
                    <div class="flex items-center gap-3 mb-6 border-b border-gray-100 pb-4">
                        <div class="p-2 bg-orange-50 rounded-lg text-orange-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-800">Ganti Password</h3>
                            <p class="text-xs text-gray-500">Pastikan menggunakan password yang panjang dan acak.</p>
                        </div>
                    </div>

                    <div class="max-w-xl">
                        @include('profile.partials.update-password-form')
                    </div>
                </div>

                <div class="bg-red-50 p-6 sm:p-8 rounded-2xl shadow-sm border border-red-100">
                    <div class="flex items-center gap-3 mb-6 border-b border-red-200 pb-4">
                        <div class="p-2 bg-red-100 rounded-lg text-red-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-red-800">Hapus Akun</h3>
                            <p class="text-xs text-red-600">Tindakan ini permanen dan data tidak bisa dikembalikan.</p>
                        </div>
                    </div>

                    <div class="max-w-xl">
                        @include('profile.partials.delete-user-form')
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>