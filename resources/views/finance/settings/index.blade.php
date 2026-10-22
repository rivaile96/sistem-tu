<x-app-layout>
    <div class="bg-gray-50 min-h-screen py-8 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto space-y-8">

            {{-- HEADER --}}
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-slate-800">Konfigurasi Finance</h1>
                    <p class="text-sm text-slate-500 mt-1">Jenis tagihan dan aturan SPP — konfigurasi saja, belum generate tagihan.</p>
                </div>
                <a href="{{ route('dashboard') }}" class="text-sm text-sky-600 hover:underline">← Kembali ke Dashboard</a>
            </div>

            {{-- ALERT --}}
            @if(session('success'))
                <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 text-sm">
                    {{ session('success') }}
                </div>
            @endif
            @if($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-800 rounded-lg px-4 py-3 text-sm">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                    </ul>
                </div>
            @endif

            {{-- ============================================================ --}}
            {{-- SECTION 1: BILLING TYPES --}}
            {{-- ============================================================ --}}
            <div class="bg-white rounded-2xl shadow border border-slate-200">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-semibold text-slate-800">Jenis Tagihan</h2>
                        <p class="text-xs text-slate-500 mt-0.5">Master kode yang dipakai saat membuat tagihan siswa.</p>
                    </div>
                    <button onclick="document.getElementById('modal-add-billing-type').classList.remove('hidden')"
                            class="bg-sky-600 hover:bg-sky-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
                        + Tambah Jenis
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-50 text-slate-600 text-xs uppercase tracking-wide">
                            <tr>
                                <th class="px-6 py-3 text-left">Urutan</th>
                                <th class="px-6 py-3 text-left">Kode</th>
                                <th class="px-6 py-3 text-left">Nama</th>
                                <th class="px-6 py-3 text-left">Berulang</th>
                                <th class="px-6 py-3 text-left">Status</th>
                                <th class="px-6 py-3 text-left">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($billingTypes as $bt)
                            <tr class="hover:bg-slate-50 {{ $bt->is_active ? '' : 'opacity-50' }}">
                                <td class="px-6 py-3 text-slate-500">{{ $bt->sort_order }}</td>
                                <td class="px-6 py-3 font-mono font-semibold text-sky-700">{{ $bt->code }}</td>
                                <td class="px-6 py-3 text-slate-800">
                                    {{ $bt->name }}
                                    @if($bt->description)
                                        <span class="block text-xs text-slate-400">{{ $bt->description }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-3">
                                    @if($bt->is_recurring)
                                        <span class="bg-blue-100 text-blue-700 text-xs px-2 py-0.5 rounded-full">Bulanan</span>
                                    @else
                                        <span class="bg-gray-100 text-gray-600 text-xs px-2 py-0.5 rounded-full">Sekali</span>
                                    @endif
                                </td>
                                <td class="px-6 py-3">
                                    @if($bt->is_active)
                                        <span class="bg-green-100 text-green-700 text-xs px-2 py-0.5 rounded-full">Aktif</span>
                                    @else
                                        <span class="bg-red-100 text-red-600 text-xs px-2 py-0.5 rounded-full">Nonaktif</span>
                                    @endif
                                </td>
                                <td class="px-6 py-3 flex items-center gap-2">
                                    {{-- Edit --}}
                                    <button onclick="openEditBillingType({{ $bt->id }}, '{{ addslashes($bt->name) }}', '{{ addslashes($bt->description ?? '') }}', {{ $bt->is_recurring ? 'true' : 'false' }}, {{ $bt->sort_order }})"
                                            class="text-sky-600 hover:underline text-xs">Edit</button>
                                    {{-- Toggle --}}
                                    <form method="POST" action="{{ route('finance.settings.billing-types.toggle', $bt->id) }}">
                                        @csrf
                                        <button type="submit" class="text-xs {{ $bt->is_active ? 'text-red-500' : 'text-green-600' }} hover:underline">
                                            {{ $bt->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="6" class="px-6 py-8 text-center text-slate-400 text-sm">Belum ada jenis tagihan.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- ============================================================ --}}
            {{-- SECTION 2: SPP RULES --}}
            {{-- ============================================================ --}}
            <div class="bg-white rounded-2xl shadow border border-slate-200">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h2 class="text-base font-semibold text-slate-800">Aturan SPP</h2>
                        <p class="text-xs text-slate-500 mt-0.5">Nominal dan kebijakan per tahun ajaran. Belum generate tagihan otomatis.</p>
                    </div>
                    <button onclick="document.getElementById('modal-add-spp-rule').classList.remove('hidden')"
                            class="bg-sky-600 hover:bg-sky-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition">
                        + Tambah Aturan
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-50 text-slate-600 text-xs uppercase tracking-wide">
                            <tr>
                                <th class="px-6 py-3 text-left">Nama Aturan</th>
                                <th class="px-6 py-3 text-left">Jenis</th>
                                <th class="px-6 py-3 text-left">Tahun Ajaran</th>
                                <th class="px-6 py-3 text-left">Nominal</th>
                                <th class="px-6 py-3 text-left">JT Tgl</th>
                                <th class="px-6 py-3 text-left">Scope Kelas</th>
                                <th class="px-6 py-3 text-left">Siswa Baru</th>
                                <th class="px-6 py-3 text-left">Status</th>
                                <th class="px-6 py-3 text-left">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($sppRules as $rule)
                            <tr class="hover:bg-slate-50 {{ $rule->is_active ? '' : 'opacity-50' }}">
                                <td class="px-6 py-3 text-slate-800 font-medium">
                                    {{ $rule->name }}
                                    @if($rule->notes)
                                        <span class="block text-xs text-slate-400">{{ Str::limit($rule->notes, 60) }}</span>
                                    @endif
                                </td>
                                <td class="px-6 py-3 font-mono text-sky-700 text-xs">{{ $rule->billing_type_code }}</td>
                                <td class="px-6 py-3 text-slate-600">{{ $rule->academic_year }}</td>
                                <td class="px-6 py-3 font-semibold text-slate-800">Rp {{ number_format($rule->amount, 0, ',', '.') }}</td>
                                <td class="px-6 py-3 text-slate-600">Tgl {{ $rule->due_day }}</td>
                                <td class="px-6 py-3 text-slate-600">{{ $rule->class_scope_label }}</td>
                                <td class="px-6 py-3 text-slate-600 text-xs">{{ $rule->new_student_offset_label }}</td>
                                <td class="px-6 py-3">
                                    @if($rule->is_active)
                                        <span class="bg-green-100 text-green-700 text-xs px-2 py-0.5 rounded-full">Aktif</span>
                                    @else
                                        <span class="bg-red-100 text-red-600 text-xs px-2 py-0.5 rounded-full">Nonaktif</span>
                                    @endif
                                </td>
                                <td class="px-6 py-3 flex items-center gap-2">
                                    <button onclick="openEditSppRule({{ $rule->id }}, '{{ addslashes($rule->name) }}', {{ $rule->amount }}, {{ $rule->due_day }}, {{ $rule->start_month }}, {{ $rule->start_year }}, {{ $rule->new_student_start_offset ?? 'null' }}, '{{ addslashes($rule->notes ?? '') }}')"
                                            class="text-sky-600 hover:underline text-xs">Edit</button>
                                    <form method="POST" action="{{ route('finance.settings.spp-rules.toggle', $rule->id) }}">
                                        @csrf
                                        <button type="submit" class="text-xs {{ $rule->is_active ? 'text-red-500' : 'text-green-600' }} hover:underline">
                                            {{ $rule->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="9" class="px-6 py-8 text-center text-slate-400 text-sm">Belum ada aturan SPP.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>{{-- /max-w --}}
    </div>{{-- /bg-gray --}}

    {{-- ================================================================ --}}
    {{-- MODAL: Tambah Billing Type --}}
    {{-- ================================================================ --}}
    <div id="modal-add-billing-type" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md">
            <div class="px-6 py-4 border-b flex items-center justify-between">
                <h3 class="font-semibold text-slate-800">Tambah Jenis Tagihan</h3>
                <button onclick="document.getElementById('modal-add-billing-type').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 text-xl">&times;</button>
            </div>
            <form method="POST" action="{{ route('finance.settings.billing-types.store') }}" class="px-6 py-4 space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Kode <span class="text-red-500">*</span></label>
                    <input type="text" name="code" value="{{ old('code') }}" required
                           class="w-full border rounded-lg px-3 py-2 text-sm font-mono uppercase focus:ring-2 focus:ring-sky-500 focus:outline-none"
                           placeholder="contoh: SPP" pattern="[A-Z0-9_]+" oninput="this.value=this.value.toUpperCase()">
                    <p class="text-xs text-slate-400 mt-1">Huruf kapital, angka, underscore. Tidak bisa diubah setelah disimpan.</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Nama <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" required maxlength="100"
                           class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-sky-500 focus:outline-none"
                           placeholder="contoh: SPP Bulanan">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Deskripsi</label>
                    <textarea name="description" rows="2" maxlength="500"
                              class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-sky-500 focus:outline-none"
                              placeholder="Opsional">{{ old('description') }}</textarea>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Berulang Bulanan?</label>
                        <select name="is_recurring" class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-sky-500 focus:outline-none">
                            <option value="0">Tidak (sekali)</option>
                            <option value="1">Ya (bulanan)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Urutan Tampil</label>
                        <input type="number" name="sort_order" value="{{ old('sort_order', 0) }}" min="0" max="9999"
                               class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-sky-500 focus:outline-none">
                    </div>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('modal-add-billing-type').classList.add('hidden')"
                            class="px-4 py-2 text-sm text-slate-600 border rounded-lg hover:bg-slate-50">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm bg-sky-600 hover:bg-sky-700 text-white rounded-lg font-medium">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ================================================================ --}}
    {{-- MODAL: Edit Billing Type --}}
    {{-- ================================================================ --}}
    <div id="modal-edit-billing-type" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md">
            <div class="px-6 py-4 border-b flex items-center justify-between">
                <h3 class="font-semibold text-slate-800">Edit Jenis Tagihan</h3>
                <button onclick="document.getElementById('modal-edit-billing-type').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 text-xl">&times;</button>
            </div>
            <form id="form-edit-billing-type" method="POST" action="" class="px-6 py-4 space-y-4">
                @csrf @method('PUT')
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Nama <span class="text-red-500">*</span></label>
                    <input type="text" id="edit-bt-name" name="name" required maxlength="100"
                           class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-sky-500 focus:outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Deskripsi</label>
                    <textarea id="edit-bt-desc" name="description" rows="2" maxlength="500"
                              class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-sky-500 focus:outline-none"></textarea>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Berulang Bulanan?</label>
                        <select id="edit-bt-recurring" name="is_recurring"
                                class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-sky-500 focus:outline-none">
                            <option value="0">Tidak (sekali)</option>
                            <option value="1">Ya (bulanan)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Urutan Tampil</label>
                        <input type="number" id="edit-bt-sort" name="sort_order" min="0" max="9999"
                               class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-sky-500 focus:outline-none">
                    </div>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('modal-edit-billing-type').classList.add('hidden')"
                            class="px-4 py-2 text-sm text-slate-600 border rounded-lg hover:bg-slate-50">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm bg-sky-600 hover:bg-sky-700 text-white rounded-lg font-medium">Perbarui</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ================================================================ --}}
    {{-- MODAL: Tambah SPP Rule --}}
    {{-- ================================================================ --}}
    <div id="modal-add-spp-rule" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg max-h-[90vh] overflow-y-auto">
            <div class="px-6 py-4 border-b flex items-center justify-between sticky top-0 bg-white">
                <h3 class="font-semibold text-slate-800">Tambah Aturan SPP</h3>
                <button onclick="document.getElementById('modal-add-spp-rule').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 text-xl">&times;</button>
            </div>
            <form method="POST" action="{{ route('finance.settings.spp-rules.store') }}" class="px-6 py-4 space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Nama Aturan <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" required maxlength="150"
                           class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-sky-500 focus:outline-none"
                           placeholder="contoh: SPP Kelas 10 TA 2026/2027">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Jenis Tagihan <span class="text-red-500">*</span></label>
                        <select name="billing_type_code" required
                                class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-sky-500 focus:outline-none">
                            <option value="">-- Pilih --</option>
                            @foreach($billingTypes->where('is_active', true) as $bt)
                                <option value="{{ $bt->code }}" {{ old('billing_type_code') == $bt->code ? 'selected' : '' }}>
                                    {{ $bt->code }} — {{ $bt->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Tahun Ajaran <span class="text-red-500">*</span></label>
                        <input type="text" name="academic_year" value="{{ old('academic_year') }}" required
                               class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-sky-500 focus:outline-none"
                               placeholder="2026/2027" pattern="\d{4}/\d{4}">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Nominal (Rp) <span class="text-red-500">*</span></label>
                        <input type="number" name="amount" value="{{ old('amount') }}" required min="1000" max="99999999"
                               class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-sky-500 focus:outline-none"
                               placeholder="150000">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Jatuh Tempo Tgl <span class="text-red-500">*</span></label>
                        <input type="number" name="due_day" value="{{ old('due_day', 10) }}" required min="1" max="28"
                               class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-sky-500 focus:outline-none">
                        <p class="text-xs text-slate-400 mt-0.5">Maks tgl 28</p>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Bulan Mulai <span class="text-red-500">*</span></label>
                        <select name="start_month" class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-sky-500 focus:outline-none">
                            @foreach(['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'] as $i => $m)
                                <option value="{{ $i+1 }}" {{ old('start_month', 7) == $i+1 ? 'selected' : '' }}>{{ $m }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Tahun Mulai <span class="text-red-500">*</span></label>
                        <input type="number" name="start_year" value="{{ old('start_year', date('Y')) }}" required min="2020" max="2099"
                               class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-sky-500 focus:outline-none">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Scope Kelas</label>
                    <input type="text" name="class_scope" value="{{ old('class_scope') }}" maxlength="50"
                           class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-sky-500 focus:outline-none"
                           placeholder="Kosongkan = semua kelas. Isi jika khusus kelas tertentu, contoh: 10">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Aturan Siswa Baru</label>
                    <select name="new_student_start_offset"
                            class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-sky-500 focus:outline-none">
                        <option value="">Ikuti aturan normal</option>
                        <option value="0" {{ old('new_student_start_offset') === '0' ? 'selected' : '' }}>Mulai bulan masuk (offset 0)</option>
                        <option value="1" {{ old('new_student_start_offset') == '1' ? 'selected' : '' }}>Mulai bulan berikutnya (offset 1) — biaya daftar sudah termasuk SPP pertama</option>
                        <option value="2" {{ old('new_student_start_offset') == '2' ? 'selected' : '' }}>Mulai 2 bulan setelah masuk (offset 2)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Catatan</label>
                    <textarea name="notes" rows="2" maxlength="500"
                              class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-sky-500 focus:outline-none"
                              placeholder="Opsional">{{ old('notes') }}</textarea>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('modal-add-spp-rule').classList.add('hidden')"
                            class="px-4 py-2 text-sm text-slate-600 border rounded-lg hover:bg-slate-50">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm bg-sky-600 hover:bg-sky-700 text-white rounded-lg font-medium">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- ================================================================ --}}
    {{-- MODAL: Edit SPP Rule --}}
    {{-- ================================================================ --}}
    <div id="modal-edit-spp-rule" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg max-h-[90vh] overflow-y-auto">
            <div class="px-6 py-4 border-b flex items-center justify-between sticky top-0 bg-white">
                <h3 class="font-semibold text-slate-800">Edit Aturan SPP</h3>
                <button onclick="document.getElementById('modal-edit-spp-rule').classList.add('hidden')" class="text-slate-400 hover:text-slate-600 text-xl">&times;</button>
            </div>
            <form id="form-edit-spp-rule" method="POST" action="" class="px-6 py-4 space-y-4">
                @csrf @method('PUT')
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Nama Aturan <span class="text-red-500">*</span></label>
                    <input type="text" id="edit-spp-name" name="name" required maxlength="150"
                           class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-sky-500 focus:outline-none">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Nominal (Rp) <span class="text-red-500">*</span></label>
                        <input type="number" id="edit-spp-amount" name="amount" required min="1000" max="99999999"
                               class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-sky-500 focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Jatuh Tempo Tgl</label>
                        <input type="number" id="edit-spp-due" name="due_day" required min="1" max="28"
                               class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-sky-500 focus:outline-none">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Bulan Mulai</label>
                        <select id="edit-spp-month" name="start_month"
                                class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-sky-500 focus:outline-none">
                            @foreach(['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'] as $i => $m)
                                <option value="{{ $i+1 }}">{{ $m }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-1">Tahun Mulai</label>
                        <input type="number" id="edit-spp-year" name="start_year" min="2020" max="2099"
                               class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-sky-500 focus:outline-none">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Aturan Siswa Baru</label>
                    <select id="edit-spp-offset" name="new_student_start_offset"
                            class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-sky-500 focus:outline-none">
                        <option value="">Ikuti aturan normal</option>
                        <option value="0">Mulai bulan masuk (offset 0)</option>
                        <option value="1">Mulai bulan berikutnya (offset 1)</option>
                        <option value="2">Mulai 2 bulan setelah masuk (offset 2)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Catatan</label>
                    <textarea id="edit-spp-notes" name="notes" rows="2" maxlength="500"
                              class="w-full border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-sky-500 focus:outline-none"></textarea>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('modal-edit-spp-rule').classList.add('hidden')"
                            class="px-4 py-2 text-sm text-slate-600 border rounded-lg hover:bg-slate-50">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm bg-sky-600 hover:bg-sky-700 text-white rounded-lg font-medium">Perbarui</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    function openEditBillingType(id, name, desc, recurring, sortOrder) {
        document.getElementById('edit-bt-name').value = name;
        document.getElementById('edit-bt-desc').value = desc;
        document.getElementById('edit-bt-recurring').value = recurring ? '1' : '0';
        document.getElementById('edit-bt-sort').value = sortOrder;
        document.getElementById('form-edit-billing-type').action =
            '/finance/settings/billing-types/' + id;
        document.getElementById('modal-edit-billing-type').classList.remove('hidden');
    }

    function openEditSppRule(id, name, amount, dueDay, startMonth, startYear, offset, notes) {
        document.getElementById('edit-spp-name').value = name;
        document.getElementById('edit-spp-amount').value = amount;
        document.getElementById('edit-spp-due').value = dueDay;
        document.getElementById('edit-spp-month').value = startMonth;
        document.getElementById('edit-spp-year').value = startYear;
        document.getElementById('edit-spp-offset').value = offset === null ? '' : offset;
        document.getElementById('edit-spp-notes').value = notes;
        document.getElementById('form-edit-spp-rule').action =
            '/finance/settings/spp-rules/' + id;
        document.getElementById('modal-edit-spp-rule').classList.remove('hidden');
    }
    </script>
</x-app-layout>
