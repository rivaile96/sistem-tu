<x-app-layout>
    <x-slot name="header">
        SPP Billing Engine
    </x-slot>

    <div class="space-y-6">

        <!-- HEADER & BREADCRUMB -->
        <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
            <div>
                <h1 class="flex items-center gap-3 text-2xl font-black text-slate-800">
                    <div class="bg-[#178BC1] p-2.5 rounded-xl text-white shadow-sm shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <path d="M21 2v6h-6"></path>
                            <path d="M3 12a9 9 0 0 1 15-6.7L21 8"></path>
                            <path d="M3 22v-6h6"></path>
                            <path d="M21 12a9 9 0 0 1-15 6.7L3 16"></path>
                        </svg>
                    </div>
                    Generate Tagihan SPP Bulanan
                </h1>
                <p class="mt-1 text-xs text-slate-500 font-medium">
                    Jalankan billing engine otomatis untuk menerbitkan tagihan SPP bulanan sesuai aturan tarif aktif.
                </p>
            </div>

            <div class="flex items-center gap-2.5">
                <a href="{{ route('finance.settings.index') }}" 
                   class="inline-flex items-center gap-2 bg-white hover:bg-slate-50 text-slate-700 border border-slate-200 px-4 py-2.5 rounded-xl font-bold text-xs shadow-sm transition-all">
                    <svg class="w-4 h-4 text-[#178BC1]" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                        <line x1="4" y1="21" x2="4" y2="14"></line><line x1="4" y1="10" x2="4" y2="3"></line>
                        <line x1="12" y1="21" x2="12" y2="12"></line><line x1="12" y1="8" x2="12" y2="3"></line>
                        <line x1="20" y1="21" x2="20" y2="16"></line><line x1="20" y1="12" x2="20" y2="3"></line>
                    </svg>
                    <span>Konfigurasi Aturan SPP</span>
                </a>
            </div>
        </div>

        <!-- ACTIVE RULES SUMMARY CARD -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <div class="p-1.5 bg-blue-50 text-[#178BC1] rounded-lg border border-blue-100">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                        </svg>
                    </div>
                    <h2 class="text-sm font-bold text-slate-800">Aturan SPP Aktif Saat Ini</h2>
                </div>
                <span class="text-xs text-slate-400 font-medium">{{ count($activeRules) }} aturan terpasang</span>
            </div>

            <div class="divide-y divide-slate-100">
                @forelse($activeRules as $rule)
                    <div class="py-3 flex flex-col sm:flex-row sm:items-center justify-between gap-2 text-xs">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="font-bold text-slate-800">{{ $rule->name }}</span>
                            <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-600 font-mono text-[10px] font-bold">
                                TA {{ $rule->academic_year }}
                            </span>
                            @if($rule->class_scope)
                                <span class="px-2 py-0.5 rounded-md bg-blue-50 text-[#178BC1] font-bold text-[10px] border border-blue-200">
                                    Kelas {{ $rule->class_scope }}
                                </span>
                            @else
                                <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-600 font-bold text-[10px]">
                                    Semua Kelas
                                </span>
                            @endif
                            @if($rule->new_student_start_offset !== null)
                                <span class="px-2 py-0.5 rounded-md bg-amber-50 text-amber-700 font-bold text-[10px] border border-amber-200">
                                    Offset Masuk {{ $rule->new_student_start_offset }} Bln
                                </span>
                            @endif
                        </div>
                        <span class="font-bold text-slate-800 text-sm">
                            Rp {{ number_format($rule->amount, 0, ',', '.') }}
                        </span>
                    </div>
                @empty
                    <div class="py-6 text-center text-xs text-slate-400">
                        Belum ada aturan SPP aktif. Silakan buat aturan di menu <a href="{{ route('finance.settings.index') }}" class="text-[#178BC1] underline font-bold">Konfigurasi Finance</a> terlebih dahulu.
                    </div>
                @endforelse
            </div>
        </div>

        <!-- GENERATE FILTER FORM -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
            <h2 class="text-sm font-bold text-slate-800 mb-4 flex items-center gap-2">
                <svg class="w-4 h-4 text-[#178BC1]" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                    <line x1="16" y1="2" x2="16" y2="6"></line>
                    <line x1="8" y1="2" x2="8" y2="6"></line>
                    <line x1="3" y1="10" x2="21" y2="10"></line>
                </svg>
                Pilih Periode Penagihan
            </h2>

            <form method="POST" action="{{ route('finance.spp.generate.preview') }}" class="space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Bulan Penagihan <span class="text-rose-500">*</span>
                        </label>
                        <select name="billing_month" required
                                class="w-full px-3 py-2 text-xs font-semibold text-slate-700 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#178BC1]/20 focus:border-[#178BC1] transition-all cursor-pointer">
                            @foreach(['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'] as $i => $m)
                                <option value="{{ $i+1 }}" {{ (isset($currentMonth) && $currentMonth == $i+1) ? 'selected' : (date('n') == $i+1 ? 'selected' : '') }}>
                                    {{ $m }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
                            Tahun Penagihan <span class="text-rose-500">*</span>
                        </label>
                        <input type="number" 
                               name="billing_year" 
                               value="{{ $currentYear ?? date('Y') }}" 
                               min="2020" 
                               max="2099" 
                               required
                               class="w-full px-3 py-2 text-xs font-bold text-slate-800 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#178BC1]/20 focus:border-[#178BC1] transition-all">
                    </div>
                </div>

                <div class="flex items-center gap-3 pt-2">
                    <button type="submit" 
                            class="inline-flex items-center gap-2 bg-[#178BC1] hover:bg-[#126da3] text-white px-5 py-2.5 rounded-xl font-bold text-xs shadow-sm transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                        <span>Preview Simulasi Penagihan</span>
                    </button>

                    @if(isset($thisMonthCount) && $thisMonthCount > 0)
                        <span class="text-xs text-slate-400 font-medium">
                            <span class="font-bold text-slate-600">{{ $thisMonthCount }} tagihan</span> sudah tercatat untuk periode ini.
                        </span>
                    @endif
                </div>
            </form>
        </div>

        <!-- PREVIEW SECTION (JIKA ADA PREVIEW) -->
        @if(isset($preview))
            @php $s = $preview['summary']; @endphp
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden space-y-6">
                
                <div class="p-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h2 class="text-base font-bold text-slate-800">
                            Simulasi Hasil: {{ $preview['billing_period'] }}
                        </h2>
                        <p class="text-xs text-slate-400 mt-0.5">
                            Periksa rincian sebelum melakukan konfirmasi penerbitan tagihan final.
                        </p>
                    </div>

                    <div class="flex items-center gap-2">
                        <span class="text-xs font-bold text-emerald-700 bg-emerald-50 px-3 py-1 rounded-xl border border-emerald-200">
                            {{ $s['new_bills'] }} Tagihan Baru Siap Terbit
                        </span>
                    </div>
                </div>

                <!-- SUMMARY COUNTERS -->
                <div class="px-6 grid grid-cols-2 sm:grid-cols-5 gap-3">
                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 text-center">
                        <p class="text-xl font-bold text-slate-800">{{ $s['scanned'] }}</p>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mt-0.5">Siswa Discanned</p>
                    </div>
                    <div class="p-3 bg-emerald-50 rounded-xl border border-emerald-200 text-center">
                        <p class="text-xl font-bold text-emerald-700">{{ $s['new_bills'] }}</p>
                        <p class="text-[10px] font-bold text-emerald-600 uppercase tracking-wider mt-0.5">Tagihan Baru</p>
                    </div>
                    <div class="p-3 bg-blue-50 rounded-xl border border-blue-200 text-center">
                        <p class="text-xl font-bold text-[#178BC1]">{{ $s['already_billed'] }}</p>
                        <p class="text-[10px] font-bold text-[#178BC1] uppercase tracking-wider mt-0.5">Sudah Ada (Skip)</p>
                    </div>
                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 text-center">
                        <p class="text-xl font-bold text-slate-600">{{ $s['excluded'] }}</p>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mt-0.5">Di-exclude</p>
                    </div>
                    <div class="p-3 bg-rose-50 rounded-xl border border-rose-200 text-center col-span-2 sm:col-span-1">
                        <p class="text-xl font-bold text-rose-700">{{ $s['conflicts'] }}</p>
                        <p class="text-[10px] font-bold text-rose-600 uppercase tracking-wider mt-0.5">Konflik Aturan</p>
                    </div>
                </div>

                <!-- TABLE SIMULASI -->
                @if(count($preview['rows']) > 0)
                    <div class="overflow-x-auto min-h-[300px]">
                        <table class="w-full text-sm text-left">
                            <thead class="bg-slate-50/80 text-slate-500 uppercase font-bold text-[10px] tracking-wider border-b border-slate-200">
                                <tr>
                                    <th class="px-6 py-3.5">Nama Siswa</th>
                                    <th class="px-6 py-3.5">NISN / Rombel</th>
                                    <th class="px-6 py-3.5">Tgl Masuk</th>
                                    <th class="px-6 py-3.5">Nominal SPP</th>
                                    <th class="px-6 py-3.5">Jatuh Tempo</th>
                                    <th class="px-6 py-3.5 text-center">Aksi Engine</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($preview['rows'] as $row)
                                    <tr class="hover:bg-slate-50/50 transition-colors">
                                        <td class="px-6 py-3.5">
                                            <p class="text-xs font-bold text-slate-800">{{ $row['student_name'] }}</p>
                                        </td>
                                        <td class="px-6 py-3.5 text-xs text-slate-500">
                                            <span class="font-mono">{{ $row['nis'] ?? '-' }}</span> • Kelas {{ $row['class'] ?? '-' }}
                                        </td>
                                        <td class="px-6 py-3.5 text-xs text-slate-500">
                                            {{ $row['enrolled_at'] ?? '-' }}
                                        </td>
                                        <td class="px-6 py-3.5 font-bold text-slate-800 text-xs">
                                            @if(isset($row['amount']))
                                                Rp {{ number_format($row['amount'], 0, ',', '.') }}
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td class="px-6 py-3.5 text-xs text-slate-500">
                                            {{ $row['due_date'] ?? '-' }}
                                        </td>
                                        <td class="px-6 py-3.5 text-center">
                                            @if($row['action'] === 'generate')
                                                <span class="px-2.5 py-0.5 rounded-lg text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                    Generate Baru
                                                </span>
                                            @elseif($row['action'] === 'skip_already_billed')
                                                <span class="px-2.5 py-0.5 rounded-lg text-[10px] font-bold bg-blue-50 text-[#178BC1] border border-blue-200">
                                                    Lewati (Ada)
                                                </span>
                                            @elseif($row['action'] === 'conflict')
                                                <span class="px-2.5 py-0.5 rounded-lg text-[10px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                                    Konflik
                                                </span>
                                            @else
                                                <span class="px-2.5 py-0.5 rounded-lg text-[10px] font-bold bg-slate-100 text-slate-600 border border-slate-200">
                                                    Exclude
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif

                <!-- CONFIRM ACTION FORM -->
                @if($s['new_bills'] > 0)
                    <div class="p-6 bg-slate-50 border-t border-slate-200">
                        <form method="POST" action="{{ route('finance.spp.generate.confirm') }}" class="space-y-3">
                            @csrf
                            <input type="hidden" name="billing_month" value="{{ $currentMonth }}">
                            <input type="hidden" name="billing_year" value="{{ $currentYear }}">

                            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3">
                                <div class="w-full sm:w-56">
                                    <input type="text" 
                                           name="confirmation" 
                                           placeholder="Ketik KONFIRMASI" 
                                           required
                                           class="w-full px-3 py-2 text-xs font-mono font-bold text-slate-800 bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-[#178BC1]/20 focus:border-[#178BC1]">
                                </div>
                                
                                <button type="submit" 
                                        class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2.5 rounded-xl font-bold text-xs shadow-sm transition-all">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                                    </svg>
                                    <span>Eksekusi {{ $s['new_bills'] }} Tagihan SPP</span>
                                </button>
                            </div>
                            <p class="text-[11px] text-slate-400">
                                Ketik kata <strong>KONFIRMASI</strong> dalam huruf kapital untuk menjalankan generator tagihan. Tindakan ini mencatat tagihan ke database.
                            </p>
                        </form>
                    </div>
                @endif

            </div>
        @endif

        <!-- GENERATION RESULT (ALERT SESUDAH CONFIRM) -->
        @if(session('generation_result'))
            @php $r = session('generation_result'); @endphp
            <div class="bg-white rounded-2xl shadow-sm border border-emerald-200 p-6 space-y-4">
                <div class="flex items-center gap-2">
                    <div class="p-2 bg-emerald-50 text-emerald-600 rounded-xl border border-emerald-200">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                            <polyline points="22 4 12 14.01 9 11.01"></polyline>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-emerald-800">Eksekusi Berhasil: {{ $r['billing_period'] }}</h2>
                        <p class="text-xs text-slate-500">Seluruh tagihan baru telah berhasil diterbitkan dan siap ditagihkan.</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-6 gap-3 text-center text-xs">
                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-200"><p class="text-xl font-bold text-slate-800">{{ $r['scanned'] }}</p><p class="text-[10px] text-slate-400 mt-1 uppercase font-bold">Scanned</p></div>
                    <div class="p-3 bg-emerald-50 rounded-xl border border-emerald-200"><p class="text-xl font-bold text-emerald-700">{{ $r['generated'] }}</p><p class="text-[10px] text-emerald-600 mt-1 uppercase font-bold">Generated</p></div>
                    <div class="p-3 bg-blue-50 rounded-xl border border-blue-200"><p class="text-xl font-bold text-[#178BC1]">{{ $r['already_billed'] }}</p><p class="text-[10px] text-[#178BC1] mt-1 uppercase font-bold">Skipped</p></div>
                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-200"><p class="text-xl font-bold text-slate-600">{{ $r['excluded'] }}</p><p class="text-[10px] text-slate-400 mt-1 uppercase font-bold">Excluded</p></div>
                    <div class="p-3 bg-rose-50 rounded-xl border border-rose-200"><p class="text-xl font-bold text-rose-700">{{ $r['conflicts'] }}</p><p class="text-[10px] text-rose-600 mt-1 uppercase font-bold">Konflik</p></div>
                    <div class="p-3 bg-rose-50 rounded-xl border border-rose-200"><p class="text-xl font-bold text-rose-700">{{ $r['failed'] }}</p><p class="text-[10px] text-rose-600 mt-1 uppercase font-bold">Gagal</p></div>
                </div>

                <div class="pt-2 flex items-center justify-between text-xs font-bold text-slate-700">
                    <span>Total Nominal Terbit: <span class="text-[#178BC1] font-black text-sm">Rp {{ number_format($r['total_amount'], 0, ',', '.') }}</span></span>
                    <a href="{{ route('bills.index') }}" class="text-[#178BC1] hover:underline">
                        Lihat Daftar Tagihan →
                    </a>
                </div>
            </div>
        @endif

    </div>
</x-app-layout>
