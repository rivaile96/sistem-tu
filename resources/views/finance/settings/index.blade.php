<x-app-layout>
    <x-slot name="header">
        Konfigurasi Finance
    </x-slot>

    <div class="space-y-8" x-data="{ tab: 'billing-types' }">

        <!-- HEADER & NAVIGATION -->
        <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
            <div>
                <h1 class="flex items-center gap-3 text-2xl font-black text-slate-800">
                    <div class="bg-[#178BC1] p-2.5 rounded-xl text-white shadow-sm shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <line x1="4" y1="21" x2="4" y2="14"></line><line x1="4" y1="10" x2="4" y2="3"></line>
                            <line x1="12" y1="21" x2="12" y2="12"></line><line x1="12" y1="8" x2="12" y2="3"></line>
                            <line x1="20" y1="21" x2="20" y2="16"></line><line x1="20" y1="12" x2="20" y2="3"></line>
                            <line x1="1" y1="14" x2="7" y2="14"></line><line x1="9" y1="8" x2="15" y2="8"></line>
                            <line x1="17" y1="16" x2="23" y2="16"></line>
                        </svg>
                    </div>
                    Konfigurasi Finance & Billing
                </h1>
                <p class="mt-1 text-xs text-slate-500 font-medium">
                    Atur master jenis tagihan dan parameter tarif SPP bulanan sebelum menjalankan billing engine.
                </p>
            </div>

            <div class="flex items-center gap-2.5">
                <a href="{{ route('finance.spp.generate.index') }}" 
                   class="inline-flex items-center gap-2 bg-[#178BC1] hover:bg-[#126da3] text-white px-4 py-2.5 rounded-xl font-bold text-xs shadow-sm transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                        <path d="M21 2v6h-6"></path><path d="M3 12a9 9 0 0 1 15-6.7L21 8"></path>
                        <path d="M3 22v-6h6"></path><path d="M21 12a9 9 0 0 1-15 6.7L3 16"></path>
                    </svg>
                    <span>Ke SPP Billing Engine →</span>
                </a>
            </div>
        </div>

        <!-- TABS (SPMB MATCHING) -->
        <div class="flex border-b border-slate-200 gap-2">
            <button @click="tab = 'billing-types'" 
                    :class="tab === 'billing-types' ? 'border-[#178BC1] text-[#178BC1] font-bold' : 'border-transparent text-slate-400 hover:text-slate-600 font-semibold'"
                    class="flex items-center gap-2 pb-3.5 px-3 border-b-2 text-xs transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                    <rect x="2" y="5" width="20" height="14" rx="2"></rect><line x1="2" y1="10" x2="22" y2="10"></line>
                </svg>
                <span>Master Jenis Tagihan</span>
                <span class="px-2 py-0.5 rounded-md text-[10px] bg-blue-50 text-[#178BC1] border border-blue-200">
                    {{ count($billingTypes) }}
                </span>
            </button>

            <button @click="tab = 'spp-rules'" 
                    :class="tab === 'spp-rules' ? 'border-[#178BC1] text-[#178BC1] font-bold' : 'border-transparent text-slate-400 hover:text-slate-600 font-semibold'"
                    class="flex items-center gap-2 pb-3.5 px-3 border-b-2 text-xs transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                    <polyline points="14 2 14 8 20 8"></polyline>
                </svg>
                <span>Aturan Tarif SPP</span>
                <span class="px-2 py-0.5 rounded-md text-[10px] bg-blue-50 text-[#178BC1] border border-blue-200">
                    {{ count($sppRules) }}
                </span>
            </button>
        </div>

        <!-- ============================================================ -->
        <!-- TAB 1: BILLING TYPES -->
        <!-- ============================================================ -->
        <div x-show="tab === 'billing-types'" class="space-y-6">
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="p-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h2 class="text-base font-bold text-slate-800">Master Jenis Tagihan</h2>
                        <p class="text-xs text-slate-400 mt-0.5">Daftar kode dan tipe tagihan yang dapat dikenakan ke siswa.</p>
                    </div>
                    <button onclick="document.getElementById('modal-add-billing-type').classList.remove('hidden')"
                            class="inline-flex items-center gap-2 bg-[#178BC1] hover:bg-[#126da3] text-white text-xs font-bold px-4 py-2.5 rounded-xl transition-all shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line>
                        </svg>
                        <span>Tambah Jenis Tagihan</span>
                    </button>
                </div>

                <div class="overflow-x-auto min-h-[300px]">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-slate-50/80 text-slate-500 uppercase font-bold text-[10px] tracking-wider border-b border-slate-200">
                            <tr>
                                <th class="px-6 py-3.5 text-center w-14">#</th>
                                <th class="px-6 py-3.5">Kode</th>
                                <th class="px-6 py-3.5">Nama Tagihan</th>
                                <th class="px-6 py-3.5">Sifat Tagihan</th>
                                <th class="px-6 py-3.5">Status</th>
                                <th class="px-6 py-3.5 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($billingTypes as $bt)
                                <tr class="hover:bg-slate-50/50 transition-colors {{ $bt->is_active ? '' : 'opacity-60' }}">
                                    <td class="px-6 py-4 text-center text-xs font-bold text-slate-400">{{ $bt->sort_order }}</td>
                                    <td class="px-6 py-4 font-mono font-bold text-xs text-[#178BC1]">{{ $bt->code }}</td>
                                    <td class="px-6 py-4">
                                        <p class="text-xs font-bold text-slate-800">{{ $bt->name }}</p>
                                        @if($bt->description)
                                            <p class="text-[11px] text-slate-400 mt-0.5">{{ $bt->description }}</p>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($bt->is_recurring)
                                            <span class="px-2.5 py-1 text-[10px] font-bold rounded-lg bg-blue-50 text-[#178BC1] border border-blue-200">
                                                Bulanan (Recurring)
                                            </span>
                                        @else
                                            <span class="px-2.5 py-1 text-[10px] font-bold rounded-lg bg-slate-100 text-slate-600 border border-slate-200">
                                                Sekali Bayar (One-time)
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($bt->is_active)
                                            <span class="px-2.5 py-1 text-[10px] font-bold rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                Aktif
                                            </span>
                                        @else
                                            <span class="px-2.5 py-1 text-[10px] font-bold rounded-lg bg-rose-50 text-rose-700 border border-rose-200">
                                                Nonaktif
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <button onclick="openEditBillingType('{{ $bt->id }}', '{{ addslashes($bt->name) }}', '{{ addslashes($bt->description ?? '') }}', {{ $bt->is_recurring ? 1 : 0 }}, {{ $bt->sort_order }})"
                                                    class="p-1.5 text-slate-600 hover:text-slate-900 bg-slate-100 hover:bg-slate-200 rounded-lg border border-slate-200 transition-colors"
                                                    title="Edit">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                                </svg>
                                            </button>

                                            <form method="POST" action="{{ route('finance.settings.billing-types.toggle', $bt->id) }}">
                                                @csrf
                                                <button type="submit" 
                                                        class="px-2.5 py-1 text-[10px] font-bold rounded-lg border transition-colors {{ $bt->is_active ? 'bg-rose-50 text-rose-700 border-rose-200 hover:bg-rose-100' : 'bg-emerald-50 text-emerald-700 border-emerald-200 hover:bg-emerald-100' }}">
                                                    {{ $bt->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center text-slate-400 text-xs">
                                        Belum ada jenis tagihan yang didaftarkan.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- ============================================================ -->
        <!-- TAB 2: SPP RULES -->
        <!-- ============================================================ -->
        <div x-show="tab === 'spp-rules'" class="space-y-6" x-cloak>
            <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="p-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h2 class="text-base font-bold text-slate-800">Aturan Tarif SPP</h2>
                        <p class="text-xs text-slate-400 mt-0.5">Konfigurasi nominal tagihan bulanan per tahun ajaran dan rombel.</p>
                    </div>
                    <button onclick="document.getElementById('modal-add-spp-rule').classList.remove('hidden')"
                            class="inline-flex items-center gap-2 bg-[#178BC1] hover:bg-[#126da3] text-white text-xs font-bold px-4 py-2.5 rounded-xl transition-all shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line>
                        </svg>
                        <span>Tambah Aturan SPP</span>
                    </button>
                </div>

                <div class="overflow-x-auto min-h-[300px]">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-slate-50/80 text-slate-500 uppercase font-bold text-[10px] tracking-wider border-b border-slate-200">
                            <tr>
                                <th class="px-6 py-3.5">Nama Aturan</th>
                                <th class="px-6 py-3.5">Tahun Ajaran</th>
                                <th class="px-6 py-3.5">Cakupan Rombel</th>
                                <th class="px-6 py-3.5">Tarif Bulanan</th>
                                <th class="px-6 py-3.5">Jatuh Tempo</th>
                                <th class="px-6 py-3.5">Status</th>
                                <th class="px-6 py-3.5 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($sppRules as $rule)
                                <tr class="hover:bg-slate-50/50 transition-colors {{ $rule->is_active ? '' : 'opacity-60' }}">
                                    <td class="px-6 py-4">
                                        <p class="text-xs font-bold text-slate-800">{{ $rule->name }}</p>
                                        @if($rule->notes)
                                            <p class="text-[11px] text-slate-400 mt-0.5">{{ $rule->notes }}</p>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-xs font-mono font-bold text-slate-700">{{ $rule->academic_year }}</td>
                                    <td class="px-6 py-4 text-xs">
                                        @if($rule->class_scope)
                                            <span class="px-2 py-0.5 rounded-md bg-blue-50 text-[#178BC1] font-bold border border-blue-200 text-[10px]">
                                                Kelas {{ $rule->class_scope }}
                                            </span>
                                        @else
                                            <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-600 font-bold border border-slate-200 text-[10px]">
                                                Semua Kelas
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 font-bold text-slate-800 text-xs">
                                        Rp {{ number_format($rule->amount, 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4 text-xs text-slate-500">
                                        Tgl {{ $rule->due_day }} tiap bulan
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($rule->is_active)
                                            <span class="px-2.5 py-1 text-[10px] font-bold rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                Aktif
                                            </span>
                                        @else
                                            <span class="px-2.5 py-1 text-[10px] font-bold rounded-lg bg-rose-50 text-rose-700 border border-rose-200">
                                                Nonaktif
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <button onclick="openEditSppRule('{{ $rule->id }}', '{{ addslashes($rule->name) }}', {{ $rule->amount }}, {{ $rule->due_day }}, {{ $rule->start_month }}, {{ $rule->start_year }}, '{{ $rule->new_student_start_offset }}', '{{ addslashes($rule->notes ?? '') }}')"
                                                    class="p-1.5 text-slate-600 hover:text-slate-900 bg-slate-100 hover:bg-slate-200 rounded-lg border border-slate-200 transition-colors"
                                                    title="Edit">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                                    <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                                                    <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path>
                                                </svg>
                                            </button>

                                            <form method="POST" action="{{ route('finance.settings.spp-rules.toggle', $rule->id) }}">
                                                @csrf
                                                <button type="submit" 
                                                        class="px-2.5 py-1 text-[10px] font-bold rounded-lg border transition-colors {{ $rule->is_active ? 'bg-rose-50 text-rose-700 border-rose-200 hover:bg-rose-100' : 'bg-emerald-50 text-emerald-700 border-emerald-200 hover:bg-emerald-100' }}">
                                                    {{ $rule->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center text-slate-400 text-xs">
                                        Belum ada aturan tarif SPP yang didaftarkan.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

    <!-- ============================================================ -->
    <!-- MODAL 1: TAMBAH BILLING TYPE -->
    <!-- ============================================================ -->
    <div id="modal-add-billing-type" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-sm hidden">
        <div class="bg-white rounded-2xl shadow-xl border border-slate-200 max-w-lg w-full p-6 mx-4">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <h3 class="text-base font-bold text-slate-800">Tambah Jenis Tagihan Baru</h3>
                <button onclick="document.getElementById('modal-add-billing-type').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                        <line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>
            
            <form method="POST" action="{{ route('finance.settings.billing-types.store') }}" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Kode Unik <span class="text-rose-500">*</span></label>
                    <input type="text" name="code" required maxlength="30" placeholder="Contoh: SPP_BULANAN"
                           class="w-full px-3 py-2 text-xs font-mono font-bold text-slate-800 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#178BC1]/20 focus:border-[#178BC1]">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Nama Tagihan <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" required maxlength="100" placeholder="Contoh: SPP Bulanan Reguler"
                           class="w-full px-3 py-2 text-xs font-medium text-slate-800 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#178BC1]/20 focus:border-[#178BC1]">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Deskripsi</label>
                    <textarea name="description" rows="2" maxlength="255" placeholder="Keterangan singkat..."
                              class="w-full px-3 py-2 text-xs font-medium text-slate-800 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#178BC1]/20 focus:border-[#178BC1]"></textarea>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Sifat Tagihan</label>
                        <select name="is_recurring" class="w-full px-3 py-2 text-xs font-semibold text-slate-700 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#178BC1]/20 focus:border-[#178BC1]">
                            <option value="0">Sekali Bayar</option>
                            <option value="1">Bulanan (Recurring)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Urutan</label>
                        <input type="number" name="sort_order" value="10" min="0" max="999"
                               class="w-full px-3 py-2 text-xs font-medium text-slate-800 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#178BC1]/20 focus:border-[#178BC1]">
                    </div>
                </div>
                
                <div class="flex justify-end gap-2.5 pt-3 border-t border-slate-100">
                    <button type="button" onclick="document.getElementById('modal-add-billing-type').classList.add('hidden')"
                            class="px-4 py-2 text-xs font-bold text-slate-600 border border-slate-200 rounded-xl hover:bg-slate-50">
                        Batal
                    </button>
                    <button type="submit" 
                            class="px-5 py-2 text-xs font-bold bg-[#178BC1] hover:bg-[#126da3] text-white rounded-xl shadow-sm">
                        Simpan Jenis Tagihan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ============================================================ -->
    <!-- MODAL 2: EDIT BILLING TYPE -->
    <!-- ============================================================ -->
    <div id="modal-edit-billing-type" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-sm hidden">
        <div class="bg-white rounded-2xl shadow-xl border border-slate-200 max-w-lg w-full p-6 mx-4">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <h3 class="text-base font-bold text-slate-800">Edit Jenis Tagihan</h3>
                <button onclick="document.getElementById('modal-edit-billing-type').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                        <line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>
            
            <form id="form-edit-billing-type" method="POST" class="mt-4 space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Nama Tagihan <span class="text-rose-500">*</span></label>
                    <input type="text" id="edit-bt-name" name="name" required maxlength="100"
                           class="w-full px-3 py-2 text-xs font-medium text-slate-800 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#178BC1]/20 focus:border-[#178BC1]">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Deskripsi</label>
                    <textarea id="edit-bt-desc" name="description" rows="2" maxlength="255"
                              class="w-full px-3 py-2 text-xs font-medium text-slate-800 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#178BC1]/20 focus:border-[#178BC1]"></textarea>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Sifat Tagihan</label>
                        <select id="edit-bt-recurring" name="is_recurring" class="w-full px-3 py-2 text-xs font-semibold text-slate-700 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#178BC1]/20 focus:border-[#178BC1]">
                            <option value="0">Sekali Bayar</option>
                            <option value="1">Bulanan (Recurring)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Urutan</label>
                        <input type="number" id="edit-bt-sort" name="sort_order" min="0" max="999"
                               class="w-full px-3 py-2 text-xs font-medium text-slate-800 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#178BC1]/20 focus:border-[#178BC1]">
                    </div>
                </div>
                
                <div class="flex justify-end gap-2.5 pt-3 border-t border-slate-100">
                    <button type="button" onclick="document.getElementById('modal-edit-billing-type').classList.add('hidden')"
                            class="px-4 py-2 text-xs font-bold text-slate-600 border border-slate-200 rounded-xl hover:bg-slate-50">
                        Batal
                    </button>
                    <button type="submit" 
                            class="px-5 py-2 text-xs font-bold bg-[#178BC1] hover:bg-[#126da3] text-white rounded-xl shadow-sm">
                        Perbarui Jenis
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ============================================================ -->
    <!-- MODAL 3: TAMBAH SPP RULE -->
    <!-- ============================================================ -->
    <div id="modal-add-spp-rule" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-sm hidden">
        <div class="bg-white rounded-2xl shadow-xl border border-slate-200 max-w-lg w-full p-6 mx-4">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <h3 class="text-base font-bold text-slate-800">Tambah Aturan Tarif SPP Baru</h3>
                <button onclick="document.getElementById('modal-add-spp-rule').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                        <line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>
            
            <form method="POST" action="{{ route('finance.settings.spp-rules.store') }}" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Nama Aturan <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" required maxlength="100" placeholder="Contoh: SPP Kelas 1 Reguler 2026/2027"
                           class="w-full px-3 py-2 text-xs font-medium text-slate-800 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#178BC1]/20 focus:border-[#178BC1]">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Tahun Ajaran <span class="text-rose-500">*</span></label>
                        <input type="text" name="academic_year" required maxlength="9" placeholder="2026/2027"
                               class="w-full px-3 py-2 text-xs font-mono font-bold text-slate-800 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#178BC1]/20 focus:border-[#178BC1]">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Cakupan Kelas</label>
                        <input type="text" name="class_scope" maxlength="10" placeholder="Kosongkan jika semua"
                               class="w-full px-3 py-2 text-xs font-medium text-slate-800 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#178BC1]/20 focus:border-[#178BC1]">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Nominal Bulanan (Rp) <span class="text-rose-500">*</span></label>
                        <input type="number" name="amount" required min="1000" max="99999999" placeholder="Contoh: 350000"
                               class="w-full px-3 py-2 text-xs font-bold text-slate-800 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#178BC1]/20 focus:border-[#178BC1]">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Jatuh Tempo (Tgl 1-28) <span class="text-rose-500">*</span></label>
                        <input type="number" name="due_day" value="10" required min="1" max="28"
                               class="w-full px-3 py-2 text-xs font-bold text-slate-800 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#178BC1]/20 focus:border-[#178BC1]">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Bulan Mulai</label>
                        <select name="start_month" class="w-full px-3 py-2 text-xs font-semibold text-slate-700 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#178BC1]/20 focus:border-[#178BC1]">
                            @foreach(['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'] as $i => $m)
                                <option value="{{ $i+1 }}" {{ $i+1 === 7 ? 'selected' : '' }}>{{ $m }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Tahun Mulai</label>
                        <input type="number" name="start_year" value="{{ date('Y') }}" min="2020" max="2099"
                               class="w-full px-3 py-2 text-xs font-medium text-slate-800 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#178BC1]/20 focus:border-[#178BC1]">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Aturan Siswa Baru (Offset)</label>
                    <select name="new_student_start_offset" class="w-full px-3 py-2 text-xs font-semibold text-slate-700 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#178BC1]/20 focus:border-[#178BC1]">
                        <option value="">Ikuti aturan normal (default)</option>
                        <option value="0">Mulai bulan masuk (offset 0)</option>
                        <option value="1">Mulai bulan berikutnya (offset 1)</option>
                        <option value="2">Mulai 2 bulan setelah masuk (offset 2)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Catatan</label>
                    <textarea name="notes" rows="2" maxlength="500" placeholder="Catatan tambahan..."
                              class="w-full px-3 py-2 text-xs font-medium text-slate-800 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#178BC1]/20 focus:border-[#178BC1]"></textarea>
                </div>
                
                <div class="flex justify-end gap-2.5 pt-3 border-t border-slate-100">
                    <button type="button" onclick="document.getElementById('modal-add-spp-rule').classList.add('hidden')"
                            class="px-4 py-2 text-xs font-bold text-slate-600 border border-slate-200 rounded-xl hover:bg-slate-50">
                        Batal
                    </button>
                    <button type="submit" 
                            class="px-5 py-2 text-xs font-bold bg-[#178BC1] hover:bg-[#126da3] text-white rounded-xl shadow-sm">
                        Simpan Aturan SPP
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ============================================================ -->
    <!-- MODAL 4: EDIT SPP RULE -->
    <!-- ============================================================ -->
    <div id="modal-edit-spp-rule" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-sm hidden">
        <div class="bg-white rounded-2xl shadow-xl border border-slate-200 max-w-lg w-full p-6 mx-4">
            <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                <h3 class="text-base font-bold text-slate-800">Edit Aturan Tarif SPP</h3>
                <button onclick="document.getElementById('modal-edit-spp-rule').classList.add('hidden')" class="text-slate-400 hover:text-slate-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                        <line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>
            
            <form id="form-edit-spp-rule" method="POST" class="mt-4 space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Nama Aturan <span class="text-rose-500">*</span></label>
                    <input type="text" id="edit-spp-name" name="name" required maxlength="100"
                           class="w-full px-3 py-2 text-xs font-medium text-slate-800 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#178BC1]/20 focus:border-[#178BC1]">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Nominal Bulanan (Rp) <span class="text-rose-500">*</span></label>
                        <input type="number" id="edit-spp-amount" name="amount" required min="1000" max="99999999"
                               class="w-full px-3 py-2 text-xs font-bold text-slate-800 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#178BC1]/20 focus:border-[#178BC1]">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Jatuh Tempo (Tgl 1-28) <span class="text-rose-500">*</span></label>
                        <input type="number" id="edit-spp-due" name="due_day" required min="1" max="28"
                               class="w-full px-3 py-2 text-xs font-bold text-slate-800 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#178BC1]/20 focus:border-[#178BC1]">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Bulan Mulai</label>
                        <select id="edit-spp-month" name="start_month" class="w-full px-3 py-2 text-xs font-semibold text-slate-700 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#178BC1]/20 focus:border-[#178BC1]">
                            @foreach(['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'] as $i => $m)
                                <option value="{{ $i+1 }}">{{ $m }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Tahun Mulai</label>
                        <input type="number" id="edit-spp-year" name="start_year" min="2020" max="2099"
                               class="w-full px-3 py-2 text-xs font-medium text-slate-800 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#178BC1]/20 focus:border-[#178BC1]">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Aturan Siswa Baru (Offset)</label>
                    <select id="edit-spp-offset" name="new_student_start_offset" class="w-full px-3 py-2 text-xs font-semibold text-slate-700 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#178BC1]/20 focus:border-[#178BC1]">
                        <option value="">Ikuti aturan normal (default)</option>
                        <option value="0">Mulai bulan masuk (offset 0)</option>
                        <option value="1">Mulai bulan berikutnya (offset 1)</option>
                        <option value="2">Mulai 2 bulan setelah masuk (offset 2)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1">Catatan</label>
                    <textarea id="edit-spp-notes" name="notes" rows="2" maxlength="500"
                              class="w-full px-3 py-2 text-xs font-medium text-slate-800 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-[#178BC1]/20 focus:border-[#178BC1]"></textarea>
                </div>
                
                <div class="flex justify-end gap-2.5 pt-3 border-t border-slate-100">
                    <button type="button" onclick="document.getElementById('modal-edit-spp-rule').classList.add('hidden')"
                            class="px-4 py-2 text-xs font-bold text-slate-600 border border-slate-200 rounded-xl hover:bg-slate-50">
                        Batal
                    </button>
                    <button type="submit" 
                            class="px-5 py-2 text-xs font-bold bg-[#178BC1] hover:bg-[#126da3] text-white rounded-xl shadow-sm">
                        Perbarui Aturan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- JAVASCRIPT HANDLERS -->
    <script>
    function openEditBillingType(id, name, desc, recurring, sortOrder) {
        document.getElementById('edit-bt-name').value = name;
        document.getElementById('edit-bt-desc').value = desc;
        document.getElementById('edit-bt-recurring').value = recurring ? '1' : '0';
        document.getElementById('edit-bt-sort').value = sortOrder;
        document.getElementById('form-edit-billing-type').action = '/finance/settings/billing-types/' + id;
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
        document.getElementById('form-edit-spp-rule').action = '/finance/settings/spp-rules/' + id;
        document.getElementById('modal-edit-spp-rule').classList.remove('hidden');
    }
    </script>
</x-app-layout>
