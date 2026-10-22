<x-app-layout>
    <div class="bg-gray-50 min-h-screen py-8 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto space-y-8">

            {{-- HEADER --}}
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-slate-800">Generate Tagihan SPP</h1>
                    <p class="text-sm text-slate-500 mt-1">Buat tagihan SPP bulanan berdasarkan aturan aktif.</p>
                </div>
                <a href="{{ route('finance.settings.index') }}" class="text-sm text-sky-600 hover:underline">← Konfigurasi Finance</a>
            </div>

            {{-- ALERTS --}}
            @if(session('success'))
                <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 text-sm">{{ session('success') }}</div>
            @endif
            @if($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-800 rounded-lg px-4 py-3 text-sm">
                    <ul class="list-disc list-inside">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
            @endif

            {{-- ACTIVE RULES SUMMARY --}}
            <div class="bg-white rounded-2xl shadow border border-slate-200 p-6">
                <h2 class="font-semibold text-slate-800 mb-3">Aturan SPP Aktif</h2>
                @forelse($activeRules as $rule)
                    <div class="flex items-center justify-between py-2 border-b border-slate-100 last:border-0 text-sm">
                        <div>
                            <span class="font-medium text-slate-800">{{ $rule->name }}</span>
                            <span class="text-slate-500 ml-2">TA {{ $rule->academic_year }}</span>
                            @if($rule->class_scope)
                                <span class="ml-2 bg-blue-100 text-blue-700 text-xs px-2 py-0.5 rounded-full">Kelas {{ $rule->class_scope }}</span>
                            @else
                                <span class="ml-2 bg-gray-100 text-gray-600 text-xs px-2 py-0.5 rounded-full">Semua Kelas</span>
                            @endif
                            @if($rule->new_student_start_offset !== null)
                                <span class="ml-2 bg-amber-100 text-amber-700 text-xs px-2 py-0.5 rounded-full">Offset {{ $rule->new_student_start_offset }}</span>
                            @endif
                        </div>
                        <span class="font-semibold text-slate-800">Rp {{ number_format($rule->amount, 0, ',', '.') }}</span>
                    </div>
                @empty
                    <p class="text-slate-400 text-sm">Belum ada aturan SPP aktif. <a href="{{ route('finance.settings.index') }}" class="text-sky-600 hover:underline">Tambah aturan</a></p>
                @endforelse
            </div>

            {{-- FORM --}}
            <div class="bg-white rounded-2xl shadow border border-slate-200 p-6">
                <h2 class="font-semibold text-slate-800 mb-4">Pilih Periode</h2>
                <form method="POST" action="{{ route('finance.spp.generate.preview') }}" id="form-preview">
                    @csrf
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Bulan <span class="text-red-500">*</span></label>
                            <select name="billing_month" id="billing_month" required
                                    class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-sky-500 focus:outline-none">
                                @foreach(['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'] as $i => $m)
                                    <option value="{{ $i+1 }}" {{ (isset($currentMonth) && $currentMonth == $i+1) ? 'selected' : '' }}>{{ $m }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Tahun <span class="text-red-500">*</span></label>
                            <input type="number" name="billing_year" id="billing_year"
                                   value="{{ $currentYear ?? date('Y') }}" min="2020" max="2099" required
                                   class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-sky-500 focus:outline-none">
                        </div>
                    </div>
                    <div class="flex gap-3">
                        <button type="submit"
                                class="bg-sky-600 hover:bg-sky-700 text-white text-sm font-medium px-5 py-2 rounded-lg transition">
                            Preview
                        </button>
                        @if($thisMonthCount > 0)
                            <span class="text-sm text-slate-500 self-center">
                                {{ $thisMonthCount }} tagihan sudah ada untuk bulan ini.
                            </span>
                        @endif
                    </div>
                </form>
            </div>

            {{-- PREVIEW RESULT --}}
            @if(isset($previewResult))
                @php $s = $previewResult['summary']; @endphp
                <div class="bg-white rounded-2xl shadow border border-slate-200">
                    <div class="px-6 py-4 border-b border-slate-100">
                        <h2 class="font-semibold text-slate-800">Preview: {{ $s['billing_period'] }}</h2>
                    </div>

                    {{-- SUMMARY CARDS --}}
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 p-6">
                        <div class="bg-green-50 border border-green-200 rounded-xl p-4 text-center">
                            <p class="text-2xl font-bold text-green-700">{{ $s['new_bills'] }}</p>
                            <p class="text-xs text-green-600 mt-1">Akan Di-generate</p>
                        </div>
                        <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 text-center">
                            <p class="text-2xl font-bold text-blue-700">{{ $s['already_billed'] }}</p>
                            <p class="text-xs text-blue-600 mt-1">Sudah Ada</p>
                        </div>
                        <div class="bg-slate-50 border border-slate-200 rounded-xl p-4 text-center">
                            <p class="text-2xl font-bold text-slate-700">{{ $s['excluded'] }}</p>
                            <p class="text-xs text-slate-600 mt-1">Dikecualikan</p>
                        </div>
                        <div class="bg-red-50 border border-red-200 rounded-xl p-4 text-center">
                            <p class="text-2xl font-bold text-red-700">{{ $s['conflicts'] }}</p>
                            <p class="text-xs text-red-600 mt-1">Konflik</p>
                        </div>
                    </div>

                    <div class="px-6 pb-2">
                        <p class="text-sm font-semibold text-slate-700">
                            Estimasi Total: <span class="text-sky-700">Rp {{ number_format($s['estimated_total'], 0, ',', '.') }}</span>
                        </p>
                    </div>

                    {{-- CONFLICTS --}}
                    @if(!empty($s['conflict_details']))
                        <div class="mx-6 mb-4 bg-red-50 border border-red-200 rounded-xl p-4">
                            <p class="text-sm font-semibold text-red-700 mb-2">Konflik Konfigurasi:</p>
                            @foreach($s['conflict_details'] as $c)
                                <p class="text-xs text-red-600">{{ $c['nis'] }} — {{ $c['name'] }}: {{ $c['reason'] }}</p>
                            @endforeach
                        </div>
                    @endif

                    {{-- ROWS TABLE --}}
                    @if(!empty($previewResult['rows']))
                        <div class="overflow-x-auto mx-6 mb-4 rounded-xl border border-slate-200">
                            <table class="w-full text-xs">
                                <thead class="bg-slate-50 text-slate-600 uppercase tracking-wide">
                                    <tr>
                                        <th class="px-4 py-2 text-left">NIS</th>
                                        <th class="px-4 py-2 text-left">Nama</th>
                                        <th class="px-4 py-2 text-left">Kelas</th>
                                        <th class="px-4 py-2 text-left">Enrolled At</th>
                                        <th class="px-4 py-2 text-left">Nominal</th>
                                        <th class="px-4 py-2 text-left">JT</th>
                                        <th class="px-4 py-2 text-left">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @foreach($previewResult['rows'] as $row)
                                        <tr class="{{ $row['action'] === 'generate' ? '' : 'opacity-50' }}">
                                            <td class="px-4 py-2 font-mono">{{ $row['nis'] }}</td>
                                            <td class="px-4 py-2">{{ $row['name'] }}</td>
                                            <td class="px-4 py-2">{{ $row['class'] ?? '-' }}</td>
                                            <td class="px-4 py-2">{{ $row['enrolled_at'] ?? '-' }}</td>
                                            <td class="px-4 py-2">
                                                @if(isset($row['amount']))
                                                    Rp {{ number_format($row['amount'], 0, ',', '.') }}
                                                @else — @endif
                                            </td>
                                            <td class="px-4 py-2">{{ $row['due_date'] ?? '-' }}</td>
                                            <td class="px-4 py-2">
                                                @if($row['action'] === 'generate')
                                                    <span class="bg-green-100 text-green-700 px-2 py-0.5 rounded-full">Generate</span>
                                                @elseif($row['action'] === 'skip_already_billed')
                                                    <span class="bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full">Skip</span>
                                                @elseif($row['action'] === 'conflict')
                                                    <span class="bg-red-100 text-red-700 px-2 py-0.5 rounded-full">Konflik</span>
                                                @else
                                                    <span class="bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full">Exclude</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif

                    {{-- CONFIRM FORM --}}
                    @if($s['new_bills'] > 0)
                        <div class="px-6 pb-6">
                            <form method="POST" action="{{ route('finance.spp.generate.confirm') }}">
                                @csrf
                                <input type="hidden" name="billing_month" value="{{ $currentMonth }}">
                                <input type="hidden" name="billing_year" value="{{ $currentYear }}">
                                <div class="flex items-center gap-3">
                                    <input type="text" name="confirmation" placeholder="Ketik KONFIRMASI"
                                           class="border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-sky-500 focus:outline-none w-48">
                                    <button type="submit"
                                            class="bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium px-5 py-2 rounded-lg transition">
                                        Generate {{ $s['new_bills'] }} Tagihan
                                    </button>
                                </div>
                                <p class="text-xs text-slate-400 mt-1">Ketik KONFIRMASI untuk melanjutkan. Aksi ini tidak bisa dibatalkan.</p>
                            </form>
                        </div>
                    @endif
                </div>
            @endif

            {{-- GENERATION RESULT --}}
            @if(session('generation_result'))
                @php $r = session('generation_result'); @endphp
                <div class="bg-white rounded-2xl shadow border border-emerald-200 p-6">
                    <h2 class="font-semibold text-emerald-800 mb-3">Hasil Generate: {{ $r['billing_period'] }}</h2>
                    <div class="grid grid-cols-3 sm:grid-cols-6 gap-3 text-center text-sm">
                        <div><p class="text-xl font-bold text-slate-800">{{ $r['scanned'] }}</p><p class="text-xs text-slate-500">Scanned</p></div>
                        <div><p class="text-xl font-bold text-green-700">{{ $r['generated'] }}</p><p class="text-xs text-slate-500">Generated</p></div>
                        <div><p class="text-xl font-bold text-blue-700">{{ $r['already_billed'] }}</p><p class="text-xs text-slate-500">Skipped</p></div>
                        <div><p class="text-xl font-bold text-slate-700">{{ $r['excluded'] }}</p><p class="text-xs text-slate-500">Excluded</p></div>
                        <div><p class="text-xl font-bold text-red-700">{{ $r['conflicts'] }}</p><p class="text-xs text-slate-500">Konflik</p></div>
                        <div><p class="text-xl font-bold text-red-700">{{ $r['failed'] }}</p><p class="text-xs text-slate-500">Failed</p></div>
                    </div>
                    <p class="mt-3 text-sm font-semibold text-slate-700">
                        Total: <span class="text-sky-700">Rp {{ number_format($r['total_amount'], 0, ',', '.') }}</span>
                    </p>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
