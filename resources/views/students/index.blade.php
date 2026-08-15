<x-app-layout>
    <div class="mb-8">
        <!-- Header Section -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <div class="p-2 bg-gradient-to-br from-[#0284c7]/10 to-[#0ea5e9]/10 rounded-xl">
                        <svg class="w-7 h-7 text-[#0284c7]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                    </div>
                    <h1 class="text-3xl font-bold text-gray-900">Manajemen Siswa</h1>
                </div>
                <p class="text-gray-600 ml-12">Pusat data tagihan SPP, Uang Gedung, dan Transaksi Siswa.</p>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('naik-kelas.index') }}"
                   class="flex items-center gap-2 bg-white border border-amber-300 text-amber-700 px-4 py-2.5 rounded-xl hover:border-amber-400 hover:shadow-md transition-all duration-300 font-medium shadow-sm text-sm">
                    <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path>
                    </svg>
                    <span>Naik Kelas</span>
                </a>
                <a href="{{ route('students.import') }}"
                   class="flex items-center gap-2 bg-white border border-gray-200 text-gray-700 px-4 py-2.5 rounded-xl hover:border-[#0284c7]/50 hover:shadow-md transition-all duration-300 font-medium shadow-sm text-sm">
                    <svg class="w-4 h-4 text-[#0284c7]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path>
                    </svg>
                    <span>Import CSV</span>
                </a>
                <button onclick="openModal('modalCreate')"
                        class="flex items-center gap-2 bg-[#0284c7] text-white px-4 py-2.5 rounded-xl hover:bg-[#0369a1] transition-all duration-300 font-medium shadow-sm text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    <span>Tambah Siswa</span>
                </button>
            </div>
        </div>

        <!-- Stats -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-gradient-to-br from-blue-50 to-blue-100 border border-blue-200 rounded-xl p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-blue-800 font-medium">Total Siswa</p>
                        <p class="text-2xl font-bold text-blue-600 mt-1">{{ $stats['total'] }}</p>
                    </div>
                    <div class="p-3 bg-blue-500/10 rounded-lg">
                        <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>
            <div class="bg-gradient-to-br from-emerald-50 to-emerald-100 border border-emerald-200 rounded-xl p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-emerald-800 font-medium">Aktif</p>
                        <p class="text-2xl font-bold text-emerald-600 mt-1">{{ $stats['aktif'] }}</p>
                    </div>
                    <div class="p-3 bg-emerald-500/10 rounded-lg">
                        <svg class="w-6 h-6 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>
            <div class="bg-gradient-to-br from-red-50 to-red-100 border border-red-200 rounded-xl p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-red-800 font-medium">Tidak Aktif</p>
                        <p class="text-2xl font-bold text-red-600 mt-1">{{ $stats['tidak_aktif'] }}</p>
                    </div>
                    <div class="p-3 bg-red-500/10 rounded-lg">
                        <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
                        </svg>
                    </div>
                </div>
            </div>
            <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 border border-yellow-200 rounded-xl p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-yellow-800 font-medium">Calon Siswa</p>
                        <p class="text-2xl font-bold text-yellow-600 mt-1">{{ $stats['calon'] }}</p>
                    </div>
                    <div class="p-3 bg-yellow-500/10 rounded-lg">
                        <svg class="w-6 h-6 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search Filter -->
        <div class="bg-gradient-to-br from-white to-gray-50 p-6 rounded-2xl shadow-lg border border-gray-100 mb-6">
            <form method="GET" action="{{ route('students.index') }}" class="flex flex-col lg:flex-row gap-4 items-end">
                <div class="flex-1 w-full">
                    <label class="block text-sm font-bold text-gray-700 mb-2">Cari Siswa</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                        <input type="text" name="search" value="{{ request('search') }}"
                               placeholder="Cari berdasarkan Nama atau NIS..."
                               class="w-full pl-12 pr-4 py-3.5 rounded-xl border border-gray-200 focus:border-[#0284c7] focus:ring-2 focus:ring-[#0284c7]/20 bg-white transition-all duration-300">
                    </div>
                </div>
                <div class="w-full lg:w-48">
                    <label class="block text-sm font-bold text-gray-700 mb-2">Status</label>
                    <select name="status" class="w-full py-3.5 px-4 rounded-xl border border-gray-200 focus:border-[#0284c7] focus:ring-2 focus:ring-[#0284c7]/20 bg-white transition-all duration-300">
                        <option value="">Semua Status</option>
                        <option value="aktif" {{ request('status') === 'aktif' ? 'selected' : '' }}>Aktif</option>
                        <option value="tidak_aktif" {{ request('status') === 'tidak_aktif' ? 'selected' : '' }}>Tidak Aktif</option>
                        <option value="calon" {{ request('status') === 'calon' ? 'selected' : '' }}>Calon Siswa</option>
                    </select>
                </div>
                <button type="submit"
                        class="flex items-center gap-2 bg-[#0284c7] text-white px-6 py-3.5 rounded-xl hover:bg-[#0369a1] transition-all duration-300 font-medium shadow-sm text-sm whitespace-nowrap">
                    Cari
                </button>
                @if(request('search') || request('status'))
                <a href="{{ route('students.index') }}"
                   class="flex items-center gap-2 bg-white border border-gray-200 text-gray-600 px-6 py-3.5 rounded-xl hover:border-gray-300 transition-all duration-200 text-sm whitespace-nowrap">
                    Reset
                </a>
                @endif
            </form>
        </div>

        <!-- Table -->
        <div class="bg-gradient-to-br from-white to-gray-50 rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
                <div class="flex items-center justify-between">
                    <h3 class="font-bold text-gray-900">Daftar Siswa</h3>
                    <span class="text-sm text-gray-500">Total <span class="font-bold text-[#0284c7]">{{ $students->total() }}</span> siswa</span>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gradient-to-r from-gray-50/80 to-gray-100/80 text-gray-700 text-xs font-bold uppercase tracking-wider">
                            <th class="px-6 py-4 text-left">Siswa</th>
                            <th class="px-6 py-4 text-left">Kelas</th>
                            <th class="px-6 py-4 text-left">Status</th>
                            <th class="px-6 py-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($students as $student)
                        <tr class="group hover:bg-gradient-to-r hover:from-blue-50/50 hover:to-blue-100/30 transition-all duration-300">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 rounded-full bg-gradient-to-br from-[#0284c7] to-[#0ea5e9] text-white flex items-center justify-center font-bold text-lg shadow-md shrink-0">
                                        {{ substr($student->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <p class="font-bold text-gray-900">{{ $student->name }}</p>
                                        <div class="flex items-center gap-2 mt-1">
                                            <span class="text-xs font-mono bg-gray-100 text-gray-600 px-2 py-1 rounded border border-gray-200">NIS: {{ $student->nis }}</span>
                                            @if($student->nisn)
                                            <span class="text-xs font-mono bg-blue-50 text-blue-600 px-2 py-1 rounded border border-blue-100">NISN: {{ $student->nisn }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center gap-2 px-3 py-1.5 bg-gradient-to-r from-gray-50 to-gray-100 rounded-full border border-gray-200 font-bold text-gray-700 text-sm">
                                    {{ $student->class_name }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                @if($student->status === 'active')
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">Aktif</span>
                                @elseif($student->status === 'calon_siswa')
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-yellow-50 text-yellow-700 border border-yellow-200">Calon</span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-red-50 text-red-700 border border-red-200">{{ \App\Models\Student::STATUSES[$student->status] ?? ucfirst($student->status) }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('students.show', $student) }}"
                                       class="bg-sky-50 hover:bg-sky-100 text-sky-700 px-3 py-1.5 rounded-lg text-xs font-medium transition-colors border border-sky-200">
                                        Detail
                                    </a>
                                    <button onclick="fetchAndEditStudent({{ $student->id }})"
                                            class="bg-amber-500 hover:bg-amber-600 text-white px-3 py-1.5 rounded-lg text-xs font-medium transition-colors">
                                        Edit
                                    </button>
                                    <button onclick="confirmDeleteStudent('{{ route('students.destroy', $student) }}', '{{ $student->name }}')"
                                            class="bg-red-600 hover:bg-red-700 text-white px-3 py-1.5 rounded-lg text-xs font-medium transition-colors">
                                        Hapus
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-6 py-16 text-center">
                                <p class="text-gray-400 text-sm">Belum ada data siswa.</p>
                                <button onclick="openModal('modalCreate')"
                                        class="mt-4 inline-flex items-center gap-2 bg-[#0284c7] text-white px-5 py-2.5 rounded-xl hover:bg-[#0369a1] transition-all duration-300 font-medium shadow-sm text-sm">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                    </svg>
                                    Tambah Siswa Pertama
                                </button>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($students->hasPages())
            <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50">
                {{ $students->links() }}
            </div>
            @endif
        </div>
    </div>

{{-- ===== MODAL CREATE ===== --}}
<div id="modalCreate" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between p-6 border-b border-gray-100 sticky top-0 bg-white z-10">
            <h3 class="text-lg font-bold text-gray-900">Tambah Siswa Baru</h3>
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
                    <input type="text" name="name" placeholder="Nama lengkap siswa"
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-[#0284c7]/20 focus:border-[#0284c7] outline-none text-sm transition-all">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">NIS <span class="text-red-500">*</span></label>
                    <input type="text" name="nis" placeholder="Nomor Induk Siswa"
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
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Kelas</label>
                    <input type="text" name="class_name" placeholder="Contoh: X IPA 1"
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-[#0284c7]/20 focus:border-[#0284c7] outline-none text-sm transition-all">
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
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Agama</label>
                    <select name="agama" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-[#0284c7]/20 focus:border-[#0284c7] outline-none text-sm transition-all bg-white">
                        <option value="">-- Pilih --</option>
                        <option value="Islam">Islam</option>
                        <option value="Kristen">Kristen</option>
                        <option value="Katolik">Katolik</option>
                        <option value="Hindu">Hindu</option>
                        <option value="Buddha">Buddha</option>
                        <option value="Konghucu">Konghucu</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Tahun Masuk</label>
                    <input type="number" name="tahun_masuk" placeholder="{{ date('Y') }}" min="2000" max="{{ date('Y') + 1 }}"
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-[#0284c7]/20 focus:border-[#0284c7] outline-none text-sm transition-all">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">No. HP Siswa</label>
                    <input type="text" name="phone" placeholder="08xxxxxxxxxx"
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-[#0284c7]/20 focus:border-[#0284c7] outline-none text-sm transition-all">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">No. HP Orang Tua</label>
                    <input type="text" name="parent_phone" placeholder="08xxxxxxxxxx"
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-[#0284c7]/20 focus:border-[#0284c7] outline-none text-sm transition-all">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Status</label>
                    <select name="status" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-[#0284c7]/20 focus:border-[#0284c7] outline-none text-sm transition-all bg-white">
                        <option value="active">Aktif</option>
                        <option value="pindah_keluar">Pindah Keluar</option>
                        <option value="keluar">Keluar / DO</option>
                        <option value="graduated">Lulus</option>
                        <option value="alumni">Alumni</option>
                        <option value="calon_siswa">Calon Siswa</option>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Alamat</label>
                    <textarea name="address" rows="3" placeholder="Alamat lengkap"
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
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ===== MODAL EDIT ===== --}}
<div id="modalEdit" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between p-6 border-b border-gray-100 sticky top-0 bg-white z-10">
            <h3 class="text-lg font-bold text-gray-900">Edit Data Siswa</h3>
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
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">NIS <span class="text-red-500">*</span></label>
                    <input type="text" name="nis" id="editNis"
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
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Kelas</label>
                    <input type="text" name="class_name" id="editClassName"
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-[#0284c7]/20 focus:border-[#0284c7] outline-none text-sm transition-all">
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
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Agama</label>
                    <select name="agama" id="editAgama" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-[#0284c7]/20 focus:border-[#0284c7] outline-none text-sm transition-all bg-white">
                        <option value="">-- Pilih --</option>
                        <option value="Islam">Islam</option>
                        <option value="Kristen">Kristen</option>
                        <option value="Katolik">Katolik</option>
                        <option value="Hindu">Hindu</option>
                        <option value="Buddha">Buddha</option>
                        <option value="Konghucu">Konghucu</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Tahun Masuk</label>
                    <input type="number" name="tahun_masuk" id="editTahunMasuk" min="2000" max="{{ date('Y') + 1 }}"
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-[#0284c7]/20 focus:border-[#0284c7] outline-none text-sm transition-all">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">No. HP Siswa</label>
                    <input type="text" name="phone" id="editPhone"
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-[#0284c7]/20 focus:border-[#0284c7] outline-none text-sm transition-all">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">No. HP Orang Tua</label>
                    <input type="text" name="parent_phone" id="editParentPhone"
                           class="w-full border border-gray-200 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-[#0284c7]/20 focus:border-[#0284c7] outline-none text-sm transition-all">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Status</label>
                    <select name="status" id="editStatus" class="w-full border border-gray-200 rounded-xl px-4 py-2.5 focus:ring-2 focus:ring-[#0284c7]/20 focus:border-[#0284c7] outline-none text-sm transition-all bg-white">
                        <option value="active">Aktif</option>
                        <option value="pindah_keluar">Pindah Keluar</option>
                        <option value="keluar">Keluar / DO</option>
                        <option value="graduated">Lulus</option>
                        <option value="alumni">Alumni</option>
                        <option value="calon_siswa">Calon Siswa</option>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-gray-700 mb-1.5">Alamat</label>
                    <textarea name="address" id="editAddress" rows="3"
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
        const res = await fetch('{{ route("students.store") }}', {
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
    } finally { btn.disabled = false; btn.textContent = 'Simpan'; }
});

// ── Fetch & Edit ──
async function fetchAndEditStudent(id) {
    try {
        const res = await fetch(`/students/${id}/edit`, { headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken } });
        const d = await res.json();
        document.getElementById('editName').value        = d.name || '';
        document.getElementById('editNis').value         = d.nis || '';
        document.getElementById('editNisn').value        = d.nisn || '';
        document.getElementById('editGender').value      = d.gender || '';
        document.getElementById('editClassName').value   = d.class_name || '';
        document.getElementById('editBirthPlace').value  = d.birth_place || '';
        document.getElementById('editBirthDate').value   = d.birth_date || '';
        document.getElementById('editAgama').value       = d.agama || '';
        document.getElementById('editTahunMasuk').value  = d.tahun_masuk || '';
        document.getElementById('editPhone').value       = d.phone || '';
        document.getElementById('editParentPhone').value = d.parent_phone || '';
        document.getElementById('editStatus').value      = d.status || 'active';
        document.getElementById('editAddress').value     = d.address || '';
        document.getElementById('formEdit').dataset.id  = id;
        document.getElementById('editErrors').classList.add('hidden');
        openModal('modalEdit');
    } catch(e) {
        Swal.fire({ icon: 'error', title: 'Error!', text: 'Gagal memuat data siswa.' });
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
        const res = await fetch(`/students/${id}`, {
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
function confirmDeleteStudent(url, nama) {
    Swal.fire({
        title: 'Hapus Siswa?',
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
                    Swal.fire({ icon: 'error', title: 'Gagal!', text: data.message || 'Gagal menghapus siswa' });
                }
            }).catch(() => Swal.fire({ icon: 'error', title: 'Error!', text: 'Koneksi bermasalah.' }));
        }
    });
}
</script>
</x-app-layout>
