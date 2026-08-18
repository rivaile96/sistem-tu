<x-app-layout>
    <div class="mb-8">
        <!-- Header -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <div class="p-2 bg-gradient-to-br from-[#0284c7]/10 to-[#0ea5e9]/10 rounded-xl">
                        <svg class="w-7 h-7 text-[#0284c7]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                        </svg>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Ubah Status Siswa</h1>
                        <p class="text-gray-500 text-sm mt-0.5">NIS: {{ $student->nis }}</p>
                    </div>
                </div>
                <p class="text-gray-600 ml-12">Perubahan status siswa <span class="font-semibold text-[#0284c7]">{{ $student->name }}</span> akan dicatat dalam riwayat.</p>
            </div>
            <a href="{{ route('students.index') }}"
               class="flex items-center gap-2 bg-gradient-to-r from-white to-gray-50 border border-gray-200 text-gray-700 px-5 py-3 rounded-xl hover:border-[#0284c7]/50 hover:shadow-md transition-all duration-300 font-medium shadow-sm">
                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Kembali ke Daftar Siswa
            </a>
        </div>

        <!-- Validation Errors -->
        @if($errors->any())
        <div class="bg-red-50 border border-red-200 rounded-xl px-5 py-4 mb-6">
            <div class="flex items-center gap-2 mb-2">
                <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <span class="font-bold text-red-700">Terdapat kesalahan pada input:</span>
            </div>
            <ul class="list-disc list-inside text-sm text-red-600 space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">

            <!-- Left: Student Info + Change Form -->
            <div class="lg:col-span-2 space-y-6">

                <!-- Student Info Card -->
                <div class="bg-gradient-to-br from-white to-gray-50 rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
                        <div class="flex items-center gap-2">
                            <div class="w-1 h-5 bg-[#0284c7] rounded-full"></div>
                            <h3 class="font-bold text-gray-900">Info Siswa</h3>
                        </div>
                    </div>
                    <div class="p-6">
                        <!-- Avatar & Name -->
                        <div class="flex items-center gap-4 mb-5 pb-5 border-b border-gray-100">
                            <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-[#0284c7]/20 to-[#0ea5e9]/20 flex items-center justify-center flex-shrink-0">
                                <span class="text-xl font-bold text-[#0284c7]">{{ mb_substr($student->name, 0, 1) }}</span>
                            </div>
                            <div>
                                <p class="font-bold text-gray-900 text-lg leading-tight">{{ $student->name }}</p>
                                <p class="text-sm text-gray-500">{{ optional($student->kelas)->nama_kelas ?? '-' }}</p>
                            </div>
                        </div>

                        <!-- Detail rows -->
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-500 font-medium">NIS</span>
                                <span class="text-sm font-bold text-gray-800 font-mono">{{ $student->nis }}</span>
                            </div>
                            @if($student->nisn)
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-500 font-medium">NISN</span>
                                <span class="text-sm font-bold text-gray-800 font-mono">{{ $student->nisn }}</span>
                            </div>
                            @endif
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-500 font-medium">Kelas</span>
                                <span class="text-sm font-bold text-gray-800">{{ optional($student->kelas)->nama_kelas ?? '-' }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-500 font-medium">Status Saat Ini</span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold"
                                      style="background-color: {{ $student->status_color }}20; color: {{ $student->status_color }}; border: 1px solid {{ $student->status_color }}40;">
                                    {{ $statuses[$student->status] ?? $student->status }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Change Status Form -->
                <div class="bg-gradient-to-br from-white to-gray-50 rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
                        <div class="flex items-center gap-2">
                            <div class="w-1 h-5 bg-amber-500 rounded-full"></div>
                            <h3 class="font-bold text-gray-900">Ubah Status</h3>
                        </div>
                    </div>
                    <div class="p-6">
                        <form method="POST" action="{{ route('students.ubah-status.process', $student->id) }}">
                            @csrf

                            <!-- Status Select -->
                            <div class="mb-5">
                                <label class="block text-sm font-bold text-gray-700 mb-2">
                                    Status Baru <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <select name="status"
                                            required
                                            class="w-full px-4 py-3 rounded-xl border {{ $errors->has('status') ? 'border-red-400 bg-red-50' : 'border-gray-200' }} focus:border-[#0284c7] focus:ring-2 focus:ring-[#0284c7]/20 bg-white appearance-none transition-all duration-300">
                                        <option value="">-- Pilih Status Baru --</option>
                                        @foreach($statuses as $key => $label)
                                            <option value="{{ $key }}"
                                                    {{ (old('status') == $key) ? 'selected' : '' }}
                                                    {{ ($student->status == $key) ? 'disabled' : '' }}>
                                                {{ $label }}{{ ($student->status == $key) ? ' (saat ini)' : '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <div class="absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-400 pointer-events-none">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </div>
                                </div>
                                @error('status')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Notes Textarea -->
                            <div class="mb-6">
                                <label class="block text-sm font-bold text-gray-700 mb-2">
                                    Catatan / Alasan
                                </label>
                                <textarea name="catatan"
                                          rows="4"
                                          placeholder="Tuliskan alasan perubahan status, misal: siswa mengundurkan diri karena pindah sekolah..."
                                          class="w-full px-4 py-3 rounded-xl border {{ $errors->has('catatan') ? 'border-red-400 bg-red-50' : 'border-gray-200' }} focus:border-[#0284c7] focus:ring-2 focus:ring-[#0284c7]/20 bg-white transition-all duration-300 resize-none">{{ old('catatan') }}</textarea>
                                @error('catatan')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Submit -->
                            <button type="submit"
                                    class="group relative w-full flex items-center justify-center gap-2 py-3 bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 font-bold overflow-hidden">
                                <div class="absolute inset-0 bg-white/10 transform -translate-x-full group-hover:translate-x-0 transition-transform duration-700"></div>
                                <svg class="w-5 h-5 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path>
                                </svg>
                                <span class="relative z-10">Simpan Perubahan Status</span>
                            </button>
                        </form>
                    </div>
                </div>

            </div>

            <!-- Right: Status History Log -->
            <div class="lg:col-span-3">
                <div class="bg-gradient-to-br from-white to-gray-50 rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div class="w-1 h-5 bg-[#0284c7] rounded-full"></div>
                                <h3 class="font-bold text-gray-900">Riwayat Perubahan Status</h3>
                            </div>
                            <span class="text-sm text-gray-500 font-medium">{{ $logs->count() }} entri</span>
                        </div>
                    </div>

                    @if($logs->isEmpty())
                    <div class="flex flex-col items-center justify-center py-16 text-gray-400">
                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                            <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                        </div>
                        <p class="font-medium text-gray-500">Belum ada riwayat perubahan status</p>
                        <p class="text-sm text-gray-400 mt-1">Perubahan yang dilakukan akan muncul di sini</p>
                    </div>
                    @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="bg-gradient-to-r from-gray-50/80 to-gray-100/80 text-gray-700 font-bold uppercase text-xs">
                                    <th class="px-5 py-3 text-left">Perubahan</th>
                                    <th class="px-5 py-3 text-left">Catatan</th>
                                    <th class="px-5 py-3 text-left">Diubah Oleh</th>
                                    <th class="px-5 py-3 text-left">Tanggal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($logs as $log)
                                <tr class="hover:bg-blue-50/30 transition-colors duration-150">
                                    <!-- Status change column -->
                                    <td class="px-5 py-4">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-red-50 text-red-700 border border-red-100">
                                                {{ $statuses[$log->status_lama] ?? $log->status_lama }}
                                            </span>
                                            <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                                            </svg>
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-100">
                                                {{ $statuses[$log->status_baru] ?? $log->status_baru }}
                                            </span>
                                        </div>
                                    </td>
                                    <!-- Notes column -->
                                    <td class="px-5 py-4">
                                        @if($log->catatan)
                                            <p class="text-gray-600 text-xs max-w-xs leading-relaxed">{{ $log->catatan }}</p>
                                        @else
                                            <span class="text-gray-300 text-xs italic">—</span>
                                        @endif
                                    </td>
                                    <!-- Changed by column -->
                                    <td class="px-5 py-4">
                                        <div class="flex items-center gap-2">
                                            <div class="w-7 h-7 rounded-full bg-gradient-to-br from-[#0284c7]/20 to-[#0ea5e9]/20 flex items-center justify-center flex-shrink-0">
                                                <span class="text-xs font-bold text-[#0284c7]">
                                                    {{ mb_substr($log->diubah_oleh ?? 'S', 0, 1) }}
                                                </span>
                                            </div>
                                            <span class="text-gray-700 font-medium text-xs">{{ $log->diubah_oleh ?? 'Sistem' }}</span>
                                        </div>
                                    </td>
                                    <!-- Date column -->
                                    <td class="px-5 py-4">
                                        <div>
                                            <p class="text-gray-700 font-medium text-xs">
                                                {{ $log->created_at->format('d M Y') }}
                                            </p>
                                            <p class="text-gray-400 text-xs">
                                                {{ $log->created_at->format('H:i') }} WIB
                                            </p>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination if applicable -->
                    @if(method_exists($logs, 'hasPages') && $logs->hasPages())
                    <div class="px-6 py-4 border-t border-gray-100">
                        {{ $logs->links() }}
                    </div>
                    @endif

                    @endif
                </div>
            </div>

        </div>
    </div>

    <style>
    input:focus, select:focus, textarea:focus {
        outline: none;
        box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.1);
    }

    .overflow-x-auto::-webkit-scrollbar {
        height: 6px;
    }
    .overflow-x-auto::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
    }
    .overflow-x-auto::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 4px;
    }
    .overflow-x-auto::-webkit-scrollbar-thumb:hover {
        background: #a1a1a1;
    }
    </style>
</x-app-layout>
