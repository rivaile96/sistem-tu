<x-app-layout>
    <!-- Header Section -->
    <div class="mb-8">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <div class="p-2 bg-gradient-to-br from-[#0284c7]/10 to-[#0ea5e9]/10 rounded-xl">
                        <svg class="w-7 h-7 text-[#0284c7]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                    </div>
                    <h1 class="text-3xl font-bold text-gray-900">Registrasi Siswa Baru — Calon Siswa</h1>
                </div>
                <nav class="flex items-center gap-2 ml-12 text-sm text-gray-500">
                    <a href="{{ route('dashboard') }}" class="hover:text-[#0284c7] transition-colors">Dashboard</a>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                    <span class="text-gray-700 font-medium">Registrasi Siswa Baru</span>
                </nav>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('ppdb.konversi') }}"
                   class="flex items-center gap-2 bg-white border border-gray-200 text-gray-700 px-4 py-2.5 rounded-xl hover:border-[#0284c7]/50 hover:text-[#0284c7] transition-all duration-300 font-medium shadow-sm text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                    </svg>
                    <span>Aktivasi Massal</span>
                </a>
                <button onclick="openModal('modalCreate')"
                        class="flex items-center gap-2 bg-[#0284c7] text-white px-4 py-2.5 rounded-xl hover:bg-[#0369a1] transition-all duration-300 font-medium shadow-sm text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    <span>Daftarkan Siswa</span>
                </button>
            </div>
        </div>

        <!-- Stat Cards -->
        @if(session('aktivasi_gagal') && count(session('aktivasi_gagal')) > 0)
        <div class="bg-amber-50 border border-amber-200 rounded-xl px-5 py-4 mb-6">
            <div class="flex items-center gap-2 mb-2">
                <svg class="w-5 h-5 text-amber-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span class="font-bold text-amber-700">{{ count(session('aktivasi_gagal')) }} siswa gagal diaktifkan:</span>
            </div>
            <ul class="list-disc list-inside text-sm text-amber-700 space-y-0.5">
                @foreach(session('aktivasi_gagal') as $gagalMsg)
                    <li>{{ $gagalMsg }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <!-- Stat Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white border border-gray-100 rounded-xl p-4 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-500 font-medium uppercase tracking-wide">Total</p>
                        <p class="text-3xl font-bold text-gray-900">{{ $stats['total'] }}</p>
                    </div>
                    <div class="p-3 bg-blue-50 rounded-lg">
                        <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>
            <div class="bg-white border border-gray-100 rounded-xl p-4 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-500 font-medium uppercase tracking-wide">Diterima</p>
                        <p class="text-3xl font-bold text-emerald-600">{{ $stats['diterima'] }}</p>
                    </div>
                    <div class="p-3 bg-emerald-50 rounded-lg">
                        <svg class="w-6 h-6 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>
            <div class="bg-white border border-gray-100 rounded-xl p-4 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-500 font-medium uppercase tracking-wide">Pending</p>
                        <p class="text-3xl font-bold text-yellow-600">{{ $stats['pending'] }}</p>
                    </div>
                    <div class="p-3 bg-yellow-50 rounded-lg">
                        <svg class="w-6 h-6 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>
            <div class="bg-white border border-gray-100 rounded-xl p-4 shadow-sm">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-500 font-medium uppercase tracking-wide">Daftar Bulan Ini</p>
                        <p class="text-3xl font-bold text-gray-900">{{ $stats['bulan_ini'] }}</p>
                    </div>
                    <div class="p-3 bg-purple-50 rounded-lg">
                        <svg class="w-6 h-6 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search Bar -->
        <form method="GET" action="{{ route('ppdb.index') }}" class="flex gap-3 mb-6">
            <div class="relative flex-1 max-w-md">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"></path>
                    </svg>
                </div>
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Cari nama atau NISN..."
                       class="w-full border border-gray-200 rounded-xl pl-10 pr-4 py-2.5 focus:ring-2 focus:ring-[#0284c7]/20 focus:border-[#0284c7] outline-none text-sm transition-all duration-200">
            </div>
            <button type="submit"
                    class="flex items-center gap-2 bg-[#0284c7] text-white px-4 py-2.5 rounded-xl hover:bg-[#0369a1] transition-all duration-300 font-medium shadow-sm text-sm">
                Cari
            </button>
            @if(request('search'))
            <a href="{{ route('ppdb.index') }}"
               class="flex items-center gap-2 bg-white border border-gray-200 text-gray-600 px-4 py-2.5 rounded-xl hover:border-gray-300 transition-all duration-200 text-sm">
                Reset
            </a>
            @endif
        </form>

        <!-- Table Card -->
        <div class="bg-gradient-to-br from-white to-gray-50 rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
                <div class="flex items-center justify-between">
                    <h3 class="font-bold text-gray-900">Daftar Calon Siswa</h3>
                    <span class="text-sm text-gray-500">Total <span class="font-bold text-[#0284c7]">{{ $calonSiswa->total() }}</span> calon</span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gradient-to-r from-gray-50/80 to-gray-100/80 text-gray-700 text-xs font-bold uppercase tracking-wider">
                            <th class="px-6 py-4 text-left w-10">No</th>
                            <th class="px-6 py-4 text-left">Nama</th>
                            <th class="px-6 py-4 text-left">NISN</th>
                            <th class="px-6 py-4 text-center">L/P</th>
                            <th class="px-6 py-4 text-left">Alamat</th>
                            <th class="px-6 py-4 text-left">Tgl Daftar</th>
                            <th class="px-6 py-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($calonSiswa as $i => $calon)
                        <tr class="group hover:bg-gradient-to-r hover:from-blue-50/50 hover:to-blue-100/30 transition-all duration-300">
                            <td class="px-6 py-4 text-sm text-gray-500 font-medium">{{ $calonSiswa->firstItem() + $i }}</td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-[#0284c7]/20 to-[#0ea5e9]/20 flex items-center justify-center font-bold text-[#0284c7] text-sm">
                                        {{ substr($calon->name, 0, 1) }}
                                    </div>
                                    <span class="font-medium text-gray-900">{{ $calon->name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600 font-mono">{{ $calon->nisn ?: '-' }}</td>
                            <td class="px-6 py-4 text-center">
                                <span class="px-2 py-1 rounded-full text-xs font-medium {{ $calon->gender === 'L' ? 'bg-blue-100 text-blue-700' : 'bg-pink-100 text-pink-700' }}">
                                    {{ $calon->gender === 'L' ? 'L' : 'P' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600 max-w-xs truncate">{{ $calon->address ?: '-' }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $calon->created_at->format('d M Y') }}</td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button onclick="fetchAndEditPPDB({{ $calon->id }})"
                                            class="bg-amber-500 hover:bg-amber-600 text-white px-3 py-1.5 rounded-lg text-xs font-medium transition-colors">
                                        Edit
                                    </button>
                                    <a href="{{ route('ppdb.show', $calon) }}"
                                       class="bg-emerald-500 hover:bg-emerald-600 text-white px-3 py-1.5 rounded-lg text-xs font-medium transition-colors">
                                        Aktivasi
                                    </a>
                                    <button onclick="confirmDeletePPDB('{{ route('ppdb.destroy', $calon) }}', '{{ $calon->name }}')"
                                            class="bg-red-600 hover:bg-red-700 text-white px-3 py-1.5 rounded-lg text-xs font-medium transition-colors">
                                        Hapus
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-6 py-16 text-center">
                                <p class="text-gray-400 text-sm">Belum ada data calon siswa.</p>
                                <button onclick="openModal('modalCreate')"
                                        class="mt-4 inline-flex items-center gap-2 bg-[#0284c7] text-white px-5 py-2.5 rounded-xl hover:bg-[#0369a1] transition-all duration-300 font-medium shadow-sm text-sm">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                    </svg>
                                    Daftarkan Calon Pertama
                                </button>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($calonSiswa->hasPages())
            <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50">
                {{ $calonSiswa->links() }}
            </div>
            @endif
        </div>
    </div>

{{-- ===== MODAL CREATE ===== --}}
<div id="modalCreate" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between p-6 border-b border-gray-100 sticky top-0 bg-white z-10">
            <h3 class="text-lg font-bold text-gray-900">Daftarkan Calon Siswa Baru</h3>
            <button onclick="closeModal('modalCreate')" class="text-gray-400 hover:text-gray-600 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <form id="formCreate" class="p-6">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Nama Lengkap <span class="text-red-500">*</span></label>
                    <input type="text" name="name" placeholder="Masukkan nama lengkap"
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-[#0284c7]/20 focus:border-[#0284c7] outline-none text-sm transition-all">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">NISN</label>
                    <input type="text" name="nisn" placeholder="Nomor Induk Siswa Nasional"
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-[#0284c7]/20 focus:border-[#0284c7] outline-none text-sm transition-all">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Jenis Kelamin <span class="text-red-500">*</span></label>
                    <select name="gender" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-[#0284c7]/20 focus:border-[#0284c7] outline-none text-sm transition-all bg-white">
                        <option value="">-- Pilih --</option>
                        <option value="L">Laki-laki</option>
                        <option value="P">Perempuan</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Tempat Lahir</label>
                    <input type="text" name="birth_place" placeholder="Kota/kabupaten"
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-[#0284c7]/20 focus:border-[#0284c7] outline-none text-sm transition-all">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Tanggal Lahir</label>
                    <input type="date" name="birth_date"
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-[#0284c7]/20 focus:border-[#0284c7] outline-none text-sm transition-all">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">No. HP</label>
                    <input type="text" name="phone" placeholder="08xxxxxxxxxx"
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-[#0284c7]/20 focus:border-[#0284c7] outline-none text-sm transition-all">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Nama Orang Tua / Wali</label>
                    <input type="text" name="parent_name" placeholder="Nama lengkap orang tua"
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-[#0284c7]/20 focus:border-[#0284c7] outline-none text-sm transition-all">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">No. HP Orang Tua</label>
                    <input type="text" name="parent_phone" placeholder="08xxxxxxxxxx"
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-[#0284c7]/20 focus:border-[#0284c7] outline-none text-sm transition-all">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Alamat</label>
                    <textarea name="address" rows="2" placeholder="Alamat lengkap"
                              class="w-full border border-gray-200 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-[#0284c7]/20 focus:border-[#0284c7] outline-none text-sm transition-all resize-none"></textarea>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Catatan</label>
                    <textarea name="catatan" rows="2" placeholder="Catatan tambahan (opsional)"
                              class="w-full border border-gray-200 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-[#0284c7]/20 focus:border-[#0284c7] outline-none text-sm transition-all resize-none"></textarea>
                </div>
            </div>

            <div id="createErrors" class="hidden mt-4 bg-red-50 border border-red-200 rounded-xl p-4">
                <ul id="createErrorList" class="list-disc list-inside text-sm text-red-600 space-y-1"></ul>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 mt-4 border-t border-gray-100">
                <button type="button" onclick="closeModal('modalCreate')"
                        class="px-4 py-2 border border-gray-300 text-gray-700 rounded-xl hover:bg-gray-50 text-sm font-medium transition-colors">
                    Batal
                </button>
                <button type="submit" id="btnCreateSubmit"
                        class="px-6 py-2 bg-[#0284c7] hover:bg-[#0369a1] text-white rounded-xl text-sm font-medium transition-colors">
                    Daftarkan
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ===== MODAL EDIT ===== --}}
<div id="modalEdit" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between p-6 border-b border-gray-100 sticky top-0 bg-white z-10">
            <h3 class="text-lg font-bold text-gray-900">Edit Data Calon Siswa</h3>
            <button onclick="closeModal('modalEdit')" class="text-gray-400 hover:text-gray-600 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <form id="formEdit" class="p-6">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Nama Lengkap <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="editName"
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-[#0284c7]/20 focus:border-[#0284c7] outline-none text-sm transition-all">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">NISN</label>
                    <input type="text" name="nisn" id="editNisn"
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-[#0284c7]/20 focus:border-[#0284c7] outline-none text-sm transition-all">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Jenis Kelamin <span class="text-red-500">*</span></label>
                    <select name="gender" id="editGender" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-[#0284c7]/20 focus:border-[#0284c7] outline-none text-sm transition-all bg-white">
                        <option value="">-- Pilih --</option>
                        <option value="L">Laki-laki</option>
                        <option value="P">Perempuan</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Tempat Lahir</label>
                    <input type="text" name="birth_place" id="editBirthPlace"
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-[#0284c7]/20 focus:border-[#0284c7] outline-none text-sm transition-all">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Tanggal Lahir</label>
                    <input type="date" name="birth_date" id="editBirthDate"
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-[#0284c7]/20 focus:border-[#0284c7] outline-none text-sm transition-all">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">No. HP</label>
                    <input type="text" name="phone" id="editPhone"
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-[#0284c7]/20 focus:border-[#0284c7] outline-none text-sm transition-all">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Nama Orang Tua / Wali</label>
                    <input type="text" name="parent_name" id="editParentName"
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-[#0284c7]/20 focus:border-[#0284c7] outline-none text-sm transition-all">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">No. HP Orang Tua</label>
                    <input type="text" name="parent_phone" id="editParentPhone"
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-[#0284c7]/20 focus:border-[#0284c7] outline-none text-sm transition-all">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Alamat</label>
                    <textarea name="address" id="editAddress" rows="2"
                              class="w-full border border-gray-200 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-[#0284c7]/20 focus:border-[#0284c7] outline-none text-sm transition-all resize-none"></textarea>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Catatan</label>
                    <textarea name="catatan" id="editCatatan" rows="2"
                              class="w-full border border-gray-200 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-[#0284c7]/20 focus:border-[#0284c7] outline-none text-sm transition-all resize-none"></textarea>
                </div>
            </div>

            <div id="editErrors" class="hidden mt-4 bg-red-50 border border-red-200 rounded-xl p-4">
                <ul id="editErrorList" class="list-disc list-inside text-sm text-red-600 space-y-1"></ul>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 mt-4 border-t border-gray-100">
                <button type="button" onclick="closeModal('modalEdit')"
                        class="px-4 py-2 border border-gray-300 text-gray-700 rounded-xl hover:bg-gray-50 text-sm font-medium transition-colors">
                    Batal
                </button>
                <button type="submit" id="btnEditSubmit"
                        class="px-6 py-2 bg-[#0284c7] hover:bg-[#0369a1] text-white rounded-xl text-sm font-medium transition-colors">
                    Update
                </button>
            </div>
        </form>
    </div>
</div>

<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

function openModal(id) {
    const m = document.getElementById(id);
    m.classList.remove('hidden'); m.classList.add('flex');
    document.body.style.overflow = 'hidden';
}
function closeModal(id) {
    const m = document.getElementById(id);
    m.classList.add('hidden'); m.classList.remove('flex');
    document.body.style.overflow = '';
}
document.querySelectorAll('[id^="modal"]').forEach(modal => {
    modal.addEventListener('click', function(e) { if (e.target === this) closeModal(this.id); });
});

// ── Create ──
document.getElementById('formCreate').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = document.getElementById('btnCreateSubmit');
    btn.disabled = true; btn.textContent = 'Menyimpan...';
    document.getElementById('createErrors').classList.add('hidden');
    try {
        const res = await fetch('{{ route("ppdb.store") }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            body: new FormData(this)
        });
        const data = await res.json();
        if (res.ok && data.success) {
            closeModal('modalCreate'); this.reset();
            Swal.fire({ icon: 'success', title: 'Berhasil!', text: data.message, timer: 2000, showConfirmButton: false, toast: true, position: 'top-end' });
            setTimeout(() => location.reload(), 2000);
        } else if (res.status === 422) {
            const errs = Object.values(data.errors).flat();
            document.getElementById('createErrorList').innerHTML = errs.map(e => `<li>${e}</li>`).join('');
            document.getElementById('createErrors').classList.remove('hidden');
        } else {
            Swal.fire({ icon: 'error', title: 'Gagal!', text: data.message || 'Terjadi kesalahan' });
        }
    } catch(err) {
        Swal.fire({ icon: 'error', title: 'Error!', text: 'Koneksi bermasalah, coba lagi.' });
    } finally { btn.disabled = false; btn.textContent = 'Daftarkan'; }
});

// ── Fetch & Edit ──
async function fetchAndEditPPDB(id) {
    try {
        const res = await fetch(`/ppdb/${id}/edit`, { headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken } });
        const d = await res.json();
        document.getElementById('editName').value        = d.name || '';
        document.getElementById('editNisn').value        = d.nisn || '';
        document.getElementById('editGender').value      = d.gender || '';
        document.getElementById('editBirthPlace').value  = d.birth_place || '';
        document.getElementById('editBirthDate').value   = d.birth_date || '';
        document.getElementById('editPhone').value       = d.phone || '';
        document.getElementById('editParentName').value  = d.parent_name || '';
        document.getElementById('editParentPhone').value = d.parent_phone || '';
        document.getElementById('editAddress').value     = d.address || '';
        document.getElementById('editCatatan').value     = d.catatan || '';
        document.getElementById('formEdit').dataset.id  = id;
        document.getElementById('editErrors').classList.add('hidden');
        openModal('modalEdit');
    } catch(e) {
        Swal.fire({ icon: 'error', title: 'Error!', text: 'Gagal memuat data.' });
    }
}

// ── Edit Submit ──
document.getElementById('formEdit').addEventListener('submit', async function(e) {
    e.preventDefault();
    const id  = this.dataset.id;
    const btn = document.getElementById('btnEditSubmit');
    btn.disabled = true; btn.textContent = 'Menyimpan...';
    document.getElementById('editErrors').classList.add('hidden');
    const formData = new FormData(this);
    formData.append('_method', 'PUT');
    try {
        const res = await fetch(`/ppdb/${id}`, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            body: formData
        });
        const data = await res.json();
        if (res.ok && data.success) {
            closeModal('modalEdit');
            Swal.fire({ icon: 'success', title: 'Berhasil!', text: data.message, timer: 2000, showConfirmButton: false, toast: true, position: 'top-end' });
            setTimeout(() => location.reload(), 2000);
        } else if (res.status === 422) {
            const errs = Object.values(data.errors).flat();
            document.getElementById('editErrorList').innerHTML = errs.map(e => `<li>${e}</li>`).join('');
            document.getElementById('editErrors').classList.remove('hidden');
        } else {
            Swal.fire({ icon: 'error', title: 'Gagal!', text: data.message || 'Terjadi kesalahan' });
        }
    } catch(err) {
        Swal.fire({ icon: 'error', title: 'Error!', text: 'Koneksi bermasalah.' });
    } finally { btn.disabled = false; btn.textContent = 'Update'; }
});

// ── Delete ──
function confirmDeletePPDB(url, nama) {
    Swal.fire({
        title: 'Hapus Data Calon Siswa?',
        text: `"${nama}" akan dihapus permanen!`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal'
    }).then(result => {
        if (result.isConfirmed) {
            fetch(url, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Content-Type': 'application/x-www-form-urlencoded', 'Accept': 'application/json' },
                body: '_method=DELETE'
            }).then(async res => {
                const data = await res.json();
                if (res.ok && data.success) {
                    Swal.fire({ icon: 'success', title: 'Dihapus!', text: data.message, timer: 2000, showConfirmButton: false, toast: true, position: 'top-end' });
                    setTimeout(() => location.reload(), 2000);
                } else {
                    Swal.fire({ icon: 'error', title: 'Gagal!', text: data.message || 'Gagal menghapus data' });
                }
            }).catch(() => Swal.fire({ icon: 'error', title: 'Error!', text: 'Koneksi bermasalah.' }));
        }
    });
}
</script>
</x-app-layout>
