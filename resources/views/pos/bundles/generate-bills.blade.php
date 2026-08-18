<x-app-layout>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <div class="bg-gradient-to-br from-slate-50 to-blue-50 min-h-screen pb-12">

        {{-- Header --}}
        <div class="bg-white border-b border-slate-200 px-8 py-8 shadow-lg relative overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-r from-blue-50/50 to-transparent pointer-events-none"></div>
            <div class="relative z-10 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <h2 class="text-3xl font-bold text-slate-800 tracking-tight">Generate Tagihan Paket</h2>
                    <p class="text-sm text-slate-500 mt-1">Buat tagihan dari bundle <span class="font-semibold text-[#0284c7]">{{ $bundle->name }}</span> untuk siswa yang dipilih</p>
                </div>
                <a href="{{ route('pos.bundles.index') }}"
                   class="flex items-center gap-2 bg-white border border-gray-200 text-gray-700 px-5 py-2.5 rounded-xl hover:border-[#0284c7]/50 hover:shadow-md transition-all font-medium text-sm shadow-sm">
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Kembali
                </a>
            </div>
        </div>

        <div class="px-8 mt-8">

            {{-- Validation errors --}}
            @if($errors->any())
            <div class="bg-red-50 border border-red-200 rounded-xl px-5 py-4 mb-6">
                <div class="flex items-center gap-2 mb-2">
                    <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="font-bold text-red-700">Terdapat kesalahan:</span>
                </div>
                <ul class="list-disc list-inside text-sm text-red-600 space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">

                {{-- LEFT: Bundle detail --}}
                <div class="xl:col-span-1 space-y-6">

                    {{-- Bundle summary card --}}
                    <div class="bg-white rounded-2xl shadow border border-slate-100 overflow-hidden">
                        <div class="px-6 py-4 border-b border-slate-100 bg-gradient-to-r from-blue-50 to-white">
                            <h3 class="font-bold text-slate-800 flex items-center gap-2">
                                <svg class="w-5 h-5 text-[#0284c7]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                </svg>
                                Detail Bundle
                            </h3>
                        </div>
                        <div class="px-6 py-4 space-y-3">
                            <div>
                                <p class="text-xs text-slate-400 font-semibold uppercase tracking-wide">Nama Paket</p>
                                <p class="font-bold text-slate-800 text-lg">{{ $bundle->name }}</p>
                            </div>
                            @if($bundle->description)
                            <div>
                                <p class="text-xs text-slate-400 font-semibold uppercase tracking-wide">Deskripsi</p>
                                <p class="text-sm text-slate-600">{{ $bundle->description }}</p>
                            </div>
                            @endif
                        </div>

                        {{-- Items list --}}
                        <div class="border-t border-slate-100">
                            <div class="px-6 py-3 bg-slate-50">
                                <p class="text-xs font-bold text-slate-500 uppercase tracking-wide">Rincian Item</p>
                            </div>
                            @php $bundleTotal = 0; @endphp
                            @forelse($bundle->items as $item)
                                @php
                                    $price    = $item->product->price ?? 0;
                                    $subtotal = $price * $item->quantity;
                                    $bundleTotal += $subtotal;
                                @endphp
                                <div class="flex items-center justify-between px-6 py-3 border-b border-slate-50 last:border-0">
                                    <div>
                                        <p class="text-sm font-medium text-slate-800">{{ $item->product->name }}</p>
                                        <p class="text-xs text-slate-400">{{ $item->quantity }} x Rp {{ number_format($price, 0, ',', '.') }}</p>
                                    </div>
                                    <p class="text-sm font-bold text-slate-700">Rp {{ number_format($subtotal, 0, ',', '.') }}</p>
                                </div>
                            @empty
                                <div class="px-6 py-4 text-sm text-slate-400 italic">Bundle ini belum memiliki item.</div>
                            @endforelse
                            <div class="flex items-center justify-between px-6 py-4 bg-blue-50">
                                <p class="text-sm font-bold text-slate-700">Total Bundle</p>
                                <p class="text-lg font-extrabold text-[#0284c7]" id="bundleTotalDisplay">
                                    Rp {{ number_format($bundleTotal, 0, ',', '.') }}
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Discount card --}}
                    <div class="bg-white rounded-2xl shadow border border-slate-100 overflow-hidden">
                        <div class="px-6 py-4 border-b border-slate-100 bg-gradient-to-r from-amber-50 to-white">
                            <h3 class="font-bold text-slate-800 flex items-center gap-2">
                                <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path>
                                </svg>
                                Diskon (Opsional)
                            </h3>
                        </div>
                        <div class="px-6 py-5 space-y-4">
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Nominal Diskon (Rp)</label>
                                <input type="number" name="discount_amount" id="discountInput" form="generateForm"
                                       min="0" max="{{ $bundleTotal }}" value="{{ old('discount_amount', 0) }}"
                                       placeholder="0"
                                       class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-[#0284c7] focus:ring-2 focus:ring-[#0284c7]/20 text-sm outline-none transition">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Keterangan Diskon</label>
                                <input type="text" name="discount_note" form="generateForm"
                                       value="{{ old('discount_note') }}"
                                       placeholder="Contoh: Beasiswa, potongan alumni..."
                                       class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-[#0284c7] focus:ring-2 focus:ring-[#0284c7]/20 text-sm outline-none transition">
                            </div>
                            {{-- Final amount preview --}}
                            <div class="bg-green-50 border border-green-200 rounded-xl px-4 py-3">
                                <p class="text-xs text-green-600 font-semibold uppercase tracking-wide mb-1">Total yang Ditagih</p>
                                <p class="text-xl font-extrabold text-green-700" id="finalAmountDisplay">
                                    Rp {{ number_format($bundleTotal, 0, ',', '.') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- RIGHT: Student picker --}}
                <div class="xl:col-span-2">
                    <form id="generateForm" method="POST" action="{{ route('pos.bundles.generateBills', $bundle->id) }}">
                        @csrf

                        <div class="bg-white rounded-2xl shadow border border-slate-100 overflow-hidden">
                            <div class="px-6 py-4 border-b border-slate-100 bg-gradient-to-r from-slate-50 to-white">
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                                    <h3 class="font-bold text-slate-800">Pilih Siswa</h3>
                                    <div class="flex items-center gap-2">
                                        {{-- Filter by kelas --}}
                                        <select id="kelasFilter"
                                                class="text-sm border border-slate-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#0284c7]/20 focus:border-[#0284c7] outline-none bg-white">
                                            <option value="">Semua Kelas</option>
                                            @foreach($kelasList as $kelas)
                                                <option value="{{ $kelas->id }}">{{ $kelas->nama_kelas }}</option>
                                            @endforeach
                                        </select>
                                        {{-- Search --}}
                                        <input type="text" id="studentSearch" placeholder="Cari nama / NIS..."
                                               class="text-sm border border-slate-200 rounded-lg px-3 py-2 focus:ring-2 focus:ring-[#0284c7]/20 focus:border-[#0284c7] outline-none w-48">
                                    </div>
                                </div>
                                {{-- Select all --}}
                                <div class="flex items-center gap-2 mt-3">
                                    <input type="checkbox" id="selectAll" class="rounded border-slate-300 text-[#0284c7]">
                                    <label for="selectAll" class="text-sm font-medium text-slate-600 cursor-pointer">Pilih semua yang terlihat</label>
                                    <span class="ml-auto text-xs text-slate-400" id="selectedCount">0 siswa dipilih</span>
                                </div>
                            </div>

                            {{-- Student list --}}
                            <div class="divide-y divide-slate-50 max-h-[520px] overflow-y-auto" id="studentList">
                                @forelse($students as $student)
                                    <label class="student-row flex items-center gap-4 px-6 py-3.5 hover:bg-slate-50 cursor-pointer transition"
                                           data-kelas-id="{{ $student->kelas_id }}"
                                           data-name="{{ strtolower($student->name) }}"
                                           data-nis="{{ $student->nis }}">
                                        <input type="checkbox" name="student_ids[]" value="{{ $student->id }}"
                                               class="student-checkbox rounded border-slate-300 text-[#0284c7] w-4 h-4 shrink-0">
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-semibold text-slate-800 truncate">{{ $student->name }}</p>
                                            <p class="text-xs text-slate-400">
                                                NIS: {{ $student->nis ?? '-' }}
                                                &nbsp;·&nbsp;
                                                {{ optional($student->kelas)->nama_kelas ?? '-' }}
                                            </p>
                                        </div>
                                        <span class="text-xs px-2 py-0.5 rounded-full font-semibold shrink-0
                                            {{ $student->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                                            {{ $student->status === 'active' ? 'Aktif' : 'Calon' }}
                                        </span>
                                    </label>
                                @empty
                                    <div class="px-6 py-8 text-center text-slate-400 text-sm">Tidak ada siswa aktif/calon.</div>
                                @endforelse
                            </div>

                            {{-- Footer / submit --}}
                            <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-4">
                                <p class="text-sm text-slate-500">
                                    Total: <span class="font-bold text-slate-800" id="footerCount">0</span> siswa dipilih
                                </p>
                                <button type="submit" id="submitBtn"
                                        class="flex items-center gap-2 bg-[#0284c7] hover:bg-[#0369a1] text-white px-8 py-3 rounded-xl font-bold text-sm shadow-lg shadow-blue-200/50 transition disabled:opacity-50 disabled:cursor-not-allowed"
                                        disabled>
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    Generate Tagihan
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
    const bundleTotal  = {{ $bundleTotal }};
    const discountInput = document.getElementById('discountInput');
    const finalDisplay  = document.getElementById('finalAmountDisplay');
    const checkboxes    = document.querySelectorAll('.student-checkbox');
    const selectAll     = document.getElementById('selectAll');
    const submitBtn     = document.getElementById('submitBtn');
    const selectedCount = document.getElementById('selectedCount');
    const footerCount   = document.getElementById('footerCount');
    const kelasFilter   = document.getElementById('kelasFilter');
    const searchInput   = document.getElementById('studentSearch');

    // ── Discount preview ─────────────────────────────────────────────────────
    function updateFinalAmount() {
        const disc  = Math.max(0, parseFloat(discountInput.value) || 0);
        const final = Math.max(0, bundleTotal - disc);
        finalDisplay.textContent = 'Rp ' + final.toLocaleString('id-ID');
    }
    discountInput.addEventListener('input', updateFinalAmount);

    // ── Selection counter ────────────────────────────────────────────────────
    function updateCount() {
        const n = document.querySelectorAll('.student-checkbox:checked').length;
        selectedCount.textContent = n + ' siswa dipilih';
        footerCount.textContent   = n;
        submitBtn.disabled = n === 0;
    }
    checkboxes.forEach(cb => cb.addEventListener('change', updateCount));

    // ── Select all (visible only) ─────────────────────────────────────────────
    selectAll.addEventListener('change', function () {
        document.querySelectorAll('.student-row:not([style*="display: none"]) .student-checkbox').forEach(cb => {
            cb.checked = this.checked;
        });
        updateCount();
    });

    // ── Filter by kelas ──────────────────────────────────────────────────────
    function applyFilters() {
        const kelasId = kelasFilter.value;
        const search  = searchInput.value.toLowerCase();
        document.querySelectorAll('.student-row').forEach(row => {
            const matchKelas  = !kelasId || row.dataset.kelasId === kelasId;
            const matchSearch = !search  || row.dataset.name.includes(search) || row.dataset.nis.includes(search);
            row.style.display = (matchKelas && matchSearch) ? '' : 'none';
        });
        // Uncheck select-all when filter changes
        selectAll.checked = false;
    }
    kelasFilter.addEventListener('change', applyFilters);
    searchInput.addEventListener('input', applyFilters);

    // ── Confirm before submit ────────────────────────────────────────────────
    document.getElementById('generateForm').addEventListener('submit', function (e) {
        e.preventDefault();
        const n    = document.querySelectorAll('.student-checkbox:checked').length;
        const disc = Math.max(0, parseFloat(discountInput.value) || 0);
        const fin  = Math.max(0, bundleTotal - disc);
        Swal.fire({
            title: 'Konfirmasi Generate',
            html: `Akan membuat <strong>${n} tagihan</strong><br>
                   Paket: <strong>{{ $bundle->name }}</strong><br>
                   Nominal: <strong>Rp ${fin.toLocaleString('id-ID')}</strong>${disc > 0 ? '<br>Diskon: Rp ' + disc.toLocaleString('id-ID') : ''}`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#0284c7',
            cancelButtonColor: '#94a3b8',
            confirmButtonText: 'Ya, Generate!',
            cancelButtonText: 'Batal',
        }).then(result => {
            if (result.isConfirmed) this.submit();
        });
    });
    </script>
</x-app-layout>
