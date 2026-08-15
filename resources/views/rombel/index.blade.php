<x-app-layout>
<x-slot name="header">Rombel</x-slot>

<div class="p-6">
    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-xl font-semibold text-slate-800">Rombel</h1>
            <p class="text-sm text-slate-500 mt-0.5">Manajemen rombongan belajar per tahun ajaran</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('tahun-ajaran.index') }}"
               class="bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 px-4 py-2 rounded-lg text-sm font-medium inline-flex items-center gap-2 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                Kelola Tahun Ajaran
            </a>
            <button onclick="openModal('modalCreate')"
                    class="bg-sky-600 hover:bg-sky-700 text-white px-4 py-2 rounded-lg text-sm font-medium inline-flex items-center gap-2 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Tambah Rombel
            </button>
        </div>
    </div>

    @if(!$tahunAjaranAktif)
        <div class="bg-amber-50 border border-amber-200 text-amber-800 rounded-lg p-4 mb-6 flex items-start gap-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-amber-500 mt-0.5 shrink-0" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
            </svg>
            <div>
                <p class="text-sm font-medium">Belum ada tahun ajaran aktif</p>
                <p class="text-xs mt-0.5">Silakan <a href="{{ route('tahun-ajaran.index') }}" class="underline font-medium hover:text-amber-900">kelola tahun ajaran</a> terlebih dahulu.</p>
            </div>
        </div>
    @endif

    {{-- Filter --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-4 mb-4">
        <form method="GET" action="{{ route('rombel.index') }}" class="flex items-center gap-3">
            <label for="tahun_ajaran_id" class="text-sm font-medium text-slate-600 whitespace-nowrap">Tahun Ajaran:</label>
            <select id="tahun_ajaran_id" name="tahun_ajaran_id" onchange="this.form.submit()"
                    class="border border-slate-300 rounded-lg px-3 py-1.5 text-sm text-slate-800 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent transition bg-white">
                <option value="">-- Semua --</option>
                @foreach($semuaTahunAjaran as $ta)
                    <option value="{{ $ta->id }}" {{ $tahunAjaranId == $ta->id ? 'selected' : '' }}>
                        {{ $ta->nama }}{{ $ta->is_aktif ? ' (Aktif)' : '' }}
                    </option>
                @endforeach
            </select>
            @if($tahunAjaranId)
                <a href="{{ route('rombel.index') }}" class="text-xs text-slate-400 hover:text-slate-600 transition-colors">Reset</a>
            @endif
        </form>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6">
        @if($rombels->isEmpty())
            <div class="text-center py-12">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-slate-300 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <p class="text-slate-500 text-sm">Belum ada rombel untuk filter ini.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-slate-100">
                            <th class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wide pb-3 pr-4">#</th>
                            <th class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wide pb-3 pr-4">Nama Rombel</th>
                            <th class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wide pb-3 pr-4">Kelas</th>
                            <th class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wide pb-3 pr-4">Tahun Ajaran</th>
                            <th class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wide pb-3 pr-4">Wali Kelas</th>
                            <th class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wide pb-3 pr-4">Siswa</th>
                            <th class="text-left text-xs font-semibold text-slate-500 uppercase tracking-wide pb-3 pr-4">Status</th>
                            <th class="text-right text-xs font-semibold text-slate-500 uppercase tracking-wide pb-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach($rombels as $i => $rombel)
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            <td class="py-3 pr-4 text-slate-400">{{ $i + 1 }}</td>
                            <td class="py-3 pr-4 font-medium text-slate-800">{{ $rombel->nama_rombel }}</td>
                            <td class="py-3 pr-4 text-slate-600">{{ $rombel->kelas->nama_kelas ?? '-' }}</td>
                            <td class="py-3 pr-4 text-slate-600">{{ $rombel->tahunAjaran->nama ?? '-' }}</td>
                            <td class="py-3 pr-4 text-slate-600">{{ $rombel->wali_kelas ?: '-' }}</td>
                            <td class="py-3 pr-4 text-slate-600">{{ $rombel->student_rombels_count }}</td>
                            <td class="py-3 pr-4">
                                @if($rombel->is_aktif)
                                    <span class="bg-green-100 text-green-700 rounded-full px-2 py-0.5 text-xs font-medium">Aktif</span>
                                @else
                                    <span class="bg-slate-100 text-slate-500 rounded-full px-2 py-0.5 text-xs font-medium">Nonaktif</span>
                                @endif
                            </td>
                            <td class="py-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('rombel.show', $rombel) }}"
                                       class="bg-sky-50 hover:bg-sky-100 text-sky-700 px-3 py-1.5 rounded-lg text-xs font-medium transition-colors">
                                        Lihat
                                    </a>
                                    <button onclick="fetchAndEditRombel({{ $rombel->id }})"
                                            class="bg-amber-500 hover:bg-amber-600 text-white px-3 py-1.5 rounded-lg text-xs font-medium transition-colors">
                                        Edit
                                    </button>
                                    <button onclick="confirmDeleteRombel('{{ route('rombel.destroy', $rombel) }}', '{{ $rombel->nama_rombel }}')"
                                            class="bg-red-600 hover:bg-red-700 text-white px-3 py-1.5 rounded-lg text-xs font-medium transition-colors">
                                        Hapus
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

{{-- ===== DATA for JS ===== --}}
<script>
const rombelTahunAjaranData = @json($semuaTahunAjaran->map(fn($ta) => ['id' => $ta->id, 'nama' => $ta->nama, 'is_aktif' => $ta->is_aktif]));
const rombelKelasData       = @json($kelasAktif->map(fn($k) => ['id' => $k->id, 'nama_kelas' => $k->nama_kelas, 'tingkat' => $k->tingkat]));
</script>

{{-- ===== MODAL CREATE ===== --}}
<div id="modalCreate" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between p-6 border-b border-gray-100">
            <h3 class="text-lg font-bold text-gray-900">Tambah Rombel</h3>
            <button onclick="closeModal('modalCreate')" class="text-gray-400 hover:text-gray-600 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <form id="formCreate" class="p-6 space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Tahun Ajaran <span class="text-red-500">*</span></label>
                <select name="tahun_ajaran_id" id="createTahunAjaran"
                        class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent transition">
                    <option value="">-- Pilih Tahun Ajaran --</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Kelas <span class="text-red-500">*</span></label>
                <select name="kelas_id" id="createKelas"
                        class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent transition">
                    <option value="">-- Pilih Kelas --</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Nama Rombel <span class="text-red-500">*</span></label>
                <input type="text" name="nama_rombel" placeholder="X IPA 1 - A"
                       class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent transition">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Wali Kelas</label>
                <input type="text" name="wali_kelas" placeholder="Nama wali kelas"
                       class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent transition">
            </div>
            <div class="flex items-center gap-2">
                <input type="checkbox" name="is_aktif" id="createIsAktif" value="1" checked
                       class="w-4 h-4 rounded border-slate-300 text-sky-600 focus:ring-sky-500">
                <label for="createIsAktif" class="text-sm text-slate-700">Rombel aktif</label>
            </div>

            <div id="createErrors" class="hidden bg-red-50 border border-red-200 rounded-xl p-4">
                <ul id="createErrorList" class="list-disc list-inside text-sm text-red-600 space-y-1"></ul>
            </div>

            <div class="flex items-center justify-end gap-3 pt-2 border-t border-gray-100">
                <button type="button" onclick="closeModal('modalCreate')"
                        class="px-4 py-2 border border-gray-300 text-gray-700 rounded-xl hover:bg-gray-50 text-sm font-medium transition-colors">
                    Batal
                </button>
                <button type="submit" id="btnCreateSubmit"
                        class="px-6 py-2 bg-sky-600 hover:bg-sky-700 text-white rounded-xl text-sm font-medium transition-colors">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ===== MODAL EDIT ===== --}}
<div id="modalEdit" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md mx-4 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between p-6 border-b border-gray-100">
            <h3 class="text-lg font-bold text-gray-900">Edit Rombel</h3>
            <button onclick="closeModal('modalEdit')" class="text-gray-400 hover:text-gray-600 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <form id="formEdit" class="p-6 space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Tahun Ajaran <span class="text-red-500">*</span></label>
                <select name="tahun_ajaran_id" id="editTahunAjaran"
                        class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent transition">
                    <option value="">-- Pilih Tahun Ajaran --</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Kelas <span class="text-red-500">*</span></label>
                <select name="kelas_id" id="editKelas"
                        class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent transition">
                    <option value="">-- Pilih Kelas --</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Nama Rombel <span class="text-red-500">*</span></label>
                <input type="text" name="nama_rombel" id="editNamaRombel"
                       class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent transition">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1.5">Wali Kelas</label>
                <input type="text" name="wali_kelas" id="editWaliKelas"
                       class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500 focus:border-transparent transition">
            </div>
            <div class="flex items-center gap-2">
                <input type="checkbox" name="is_aktif" id="editIsAktif" value="1"
                       class="w-4 h-4 rounded border-slate-300 text-sky-600 focus:ring-sky-500">
                <label for="editIsAktif" class="text-sm text-slate-700">Rombel aktif</label>
            </div>

            <div id="editErrors" class="hidden bg-red-50 border border-red-200 rounded-xl p-4">
                <ul id="editErrorList" class="list-disc list-inside text-sm text-red-600 space-y-1"></ul>
            </div>

            <div class="flex items-center justify-end gap-3 pt-2 border-t border-gray-100">
                <button type="button" onclick="closeModal('modalEdit')"
                        class="px-4 py-2 border border-gray-300 text-gray-700 rounded-xl hover:bg-gray-50 text-sm font-medium transition-colors">
                    Batal
                </button>
                <button type="submit" id="btnEditSubmit"
                        class="px-6 py-2 bg-sky-600 hover:bg-sky-700 text-white rounded-xl text-sm font-medium transition-colors">
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
    m.classList.remove('hidden');
    m.classList.add('flex');
    document.body.style.overflow = 'hidden';
}
function closeModal(id) {
    const m = document.getElementById(id);
    m.classList.add('hidden');
    m.classList.remove('flex');
    document.body.style.overflow = '';
}
document.querySelectorAll('[id^="modal"]').forEach(modal => {
    modal.addEventListener('click', function(e) { if (e.target === this) closeModal(this.id); });
});

function buildTaOptions(selectEl, selectedId) {
    selectEl.innerHTML = '<option value="">-- Pilih Tahun Ajaran --</option>';
    rombelTahunAjaranData.forEach(ta => {
        const opt = document.createElement('option');
        opt.value = ta.id;
        opt.textContent = ta.nama + (ta.is_aktif ? ' (Aktif)' : '');
        if (ta.id == selectedId) opt.selected = true;
        selectEl.appendChild(opt);
    });
}
function buildKelasOptions(selectEl, selectedId) {
    selectEl.innerHTML = '<option value="">-- Pilih Kelas --</option>';
    rombelKelasData.forEach(k => {
        const opt = document.createElement('option');
        opt.value = k.id;
        opt.textContent = k.nama_kelas;
        if (k.id == selectedId) opt.selected = true;
        selectEl.appendChild(opt);
    });
}

// Populate selects on page load
buildTaOptions(document.getElementById('createTahunAjaran'), null);
buildKelasOptions(document.getElementById('createKelas'), null);
buildTaOptions(document.getElementById('editTahunAjaran'), null);
buildKelasOptions(document.getElementById('editKelas'), null);

// ── Create ──
document.getElementById('formCreate').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = document.getElementById('btnCreateSubmit');
    btn.disabled = true; btn.textContent = 'Menyimpan...';
    document.getElementById('createErrors').classList.add('hidden');

    const formData = new FormData(this);
    if (!this.querySelector('[name="is_aktif"]').checked) formData.delete('is_aktif');

    try {
        const res = await fetch('{{ route("rombel.store") }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            body: formData
        });
        const data = await res.json();
        if (res.ok && data.success) {
            closeModal('modalCreate');
            this.reset();
            Swal.fire({ icon: 'success', title: 'Berhasil!', text: data.message, timer: 2000, showConfirmButton: false, toast: true, position: 'top-end' });
            setTimeout(() => location.reload(), 2000);
        } else if (res.status === 422) {
            const errs = data.errors ? Object.values(data.errors).flat() : [data.message];
            document.getElementById('createErrorList').innerHTML = errs.map(e => `<li>${e}</li>`).join('');
            document.getElementById('createErrors').classList.remove('hidden');
        } else {
            Swal.fire({ icon: 'error', title: 'Gagal!', text: data.message || 'Terjadi kesalahan' });
        }
    } catch(err) {
        Swal.fire({ icon: 'error', title: 'Error!', text: 'Koneksi bermasalah, coba lagi.' });
    } finally {
        btn.disabled = false; btn.textContent = 'Simpan';
    }
});

// ── Fetch & Edit ──
async function fetchAndEditRombel(id) {
    try {
        const res = await fetch(`/rombel/${id}/edit`, {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken }
        });
        const data = await res.json();
        const r = data.rombel;
        buildTaOptions(document.getElementById('editTahunAjaran'), r.tahun_ajaran_id);
        buildKelasOptions(document.getElementById('editKelas'), r.kelas_id);
        document.getElementById('editNamaRombel').value  = r.nama_rombel || '';
        document.getElementById('editWaliKelas').value   = r.wali_kelas || '';
        document.getElementById('editIsAktif').checked   = !!r.is_aktif;
        document.getElementById('formEdit').dataset.id   = id;
        document.getElementById('editErrors').classList.add('hidden');
        openModal('modalEdit');
    } catch(e) {
        Swal.fire({ icon: 'error', title: 'Error!', text: 'Gagal memuat data rombel.' });
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
    if (!this.querySelector('[name="is_aktif"]').checked) formData.delete('is_aktif');

    try {
        const res = await fetch(`/rombel/${id}`, {
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
            const errs = data.errors ? Object.values(data.errors).flat() : [data.message];
            document.getElementById('editErrorList').innerHTML = errs.map(e => `<li>${e}</li>`).join('');
            document.getElementById('editErrors').classList.remove('hidden');
        } else {
            Swal.fire({ icon: 'error', title: 'Gagal!', text: data.message || 'Terjadi kesalahan' });
        }
    } catch(err) {
        Swal.fire({ icon: 'error', title: 'Error!', text: 'Koneksi bermasalah, coba lagi.' });
    } finally {
        btn.disabled = false; btn.textContent = 'Update';
    }
});

// ── Delete ──
function confirmDeleteRombel(url, nama) {
    Swal.fire({
        title: 'Hapus Rombel?',
        text: `Rombel "${nama}" akan dihapus permanen!`,
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
                    Swal.fire({ icon: 'error', title: 'Gagal!', text: data.message || 'Gagal menghapus rombel' });
                }
            }).catch(() => {
                Swal.fire({ icon: 'error', title: 'Error!', text: 'Koneksi bermasalah.' });
            });
        }
    });
}
</script>
</x-app-layout>
