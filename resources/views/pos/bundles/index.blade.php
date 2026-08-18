<x-app-layout>
    <div class="bg-gradient-to-br from-slate-50 to-blue-50 min-h-screen pb-12">

        {{-- Header --}}
        <div class="bg-white border-b border-slate-200 px-8 py-8 shadow-lg relative overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-r from-blue-50/50 to-transparent pointer-events-none"></div>
            <div class="relative z-10">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                    <div class="flex items-center gap-4">
                        <div class="relative">
                            <div class="absolute inset-0 bg-gradient-to-r from-[#0284c7] to-blue-400 rounded-xl blur-lg opacity-30"></div>
                            <div class="relative bg-gradient-to-br from-[#0284c7] to-blue-600 p-3 rounded-xl shadow-lg">
                                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                </svg>
                            </div>
                        </div>
                        <div>
                            <h2 class="text-3xl font-bold text-slate-800 tracking-tight">Daftar Paket Bundling</h2>
                            <p class="text-sm text-slate-500 mt-1">Kelola paket barang untuk sistem POS dan penjualan</p>
                        </div>
                    </div>
                    <button onclick="openModal('modalCreate')" class="relative group">
                        <div class="absolute inset-0 bg-gradient-to-r from-[#0284c7] to-blue-500 rounded-xl blur opacity-75 group-hover:opacity-100 transition-opacity"></div>
                        <div class="relative flex items-center gap-3 bg-gradient-to-r from-[#0284c7] to-blue-500 hover:from-blue-600 hover:to-[#0284c7] text-white px-6 py-3.5 rounded-xl font-bold text-sm shadow-lg shadow-blue-200/50 transition-all transform group-hover:-translate-y-0.5">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            Buat Paket Baru
                        </div>
                    </button>
                </div>
            </div>
        </div>

        <div class="px-8 mt-8">
            {{-- Stats --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-gradient-to-br from-white to-slate-50 rounded-2xl p-6 border border-slate-100 shadow-lg">
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">Total Paket</p>
                    <p class="text-4xl font-black text-slate-800">{{ $totalBundles }}</p>
                </div>
                <div class="bg-gradient-to-br from-white to-slate-50 rounded-2xl p-6 border border-slate-100 shadow-lg">
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">Paket Aktif</p>
                    <p class="text-4xl font-black text-emerald-600">{{ $activeBundles }}</p>
                </div>
                <div class="bg-gradient-to-br from-white to-slate-50 rounded-2xl p-6 border border-slate-100 shadow-lg">
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">Total Produk</p>
                    <p class="text-4xl font-black text-blue-600">{{ $products->count() }}</p>
                </div>
                <div class="bg-gradient-to-br from-white to-slate-50 rounded-2xl p-6 border border-slate-100 shadow-lg">
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">Rata-rata Item</p>
                    <p class="text-4xl font-black text-violet-600">
                        {{ number_format($avgItems, 1) }}
                    </p>
                </div>
            </div>

            {{-- Bundle Grid --}}
            @if($bundles->isEmpty())
            <div class="bg-white rounded-2xl border border-slate-100 shadow-lg p-16 text-center">
                <svg class="w-16 h-16 text-slate-200 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                </svg>
                <p class="text-slate-400 font-medium">Belum ada paket bundling.</p>
                <button onclick="openModal('modalCreate')"
                        class="mt-4 inline-flex items-center gap-2 bg-[#0284c7] text-white px-5 py-2.5 rounded-xl hover:bg-[#0369a1] transition font-medium text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Buat Paket Pertama
                </button>
            </div>
            @else
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                @foreach($bundles as $bundle)
                <div class="group bg-white rounded-2xl border border-slate-100 shadow-lg hover:shadow-xl transition-all duration-300 overflow-hidden">
                    <div class="p-6">
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex-1">
                                <h3 class="font-bold text-slate-800 text-lg leading-tight">{{ $bundle->name }}</h3>
                                @if($bundle->description)
                                <p class="text-sm text-slate-500 mt-1 line-clamp-2">{{ $bundle->description }}</p>
                                @endif
                            </div>
                            <span class="ml-3 shrink-0 px-2.5 py-1 rounded-full text-xs font-bold {{ $bundle->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500' }}">
                                {{ $bundle->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <p class="text-xs text-slate-400 font-medium">Harga Jual</p>
                                <p class="text-xl font-black text-[#0284c7]">Rp {{ number_format($bundle->price, 0, ',', '.') }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-xs text-slate-400 font-medium">Item</p>
                                <p class="text-xl font-black text-slate-700">{{ $bundle->items->count() }}</p>
                            </div>
                        </div>

                        @if($bundle->items->count())
                        <div class="bg-slate-50 rounded-xl p-3 mb-4 space-y-1.5">
                            @foreach($bundle->items->take(3) as $item)
                            <div class="flex justify-between text-xs text-slate-600">
                                <span class="truncate mr-2">{{ $item->product->name ?? 'Produk dihapus' }}</span>
                                <span class="shrink-0 font-medium">×{{ $item->quantity }}</span>
                            </div>
                            @endforeach
                            @if($bundle->items->count() > 3)
                            <p class="text-xs text-slate-400">+{{ $bundle->items->count() - 3 }} item lainnya</p>
                            @endif
                        </div>
                        @endif

                        <div class="flex items-center gap-2">
                                      <a href="{{ route('pos.bundles.generateBillsForm', $bundle->id) }}"
                                       class="flex-1 bg-emerald-500 hover:bg-emerald-600 text-white py-2 rounded-xl text-xs font-bold transition-colors text-center">
                                        Generate Tagihan
                                    </a>
                                    <button onclick="fetchAndEditBundle({{ $bundle->id }})"
                                    class="flex-1 bg-amber-500 hover:bg-amber-600 text-white py-2 rounded-xl text-xs font-bold transition-colors">
                                Edit
                            </button>
                            <button onclick="confirmDeleteBundle('{{ route('pos.bundles.destroy', $bundle) }}', '{{ $bundle->name }}')"
                                    class="flex-1 bg-red-500 hover:bg-red-600 text-white py-2 rounded-xl text-xs font-bold transition-colors">
                                Hapus
                            </button>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            @if($bundles->hasPages())
            <div class="mt-8">{{ $bundles->links() }}</div>
            @endif
            @endif
        </div>
    </div>

{{-- Products data for JS --}}
@php
$productsJson = $products->map(function($p) {
    return [
        'id'    => $p->id,
        'name'  => $p->name,
        'price' => $p->price ?? 0,
        'stock' => $p->stock,
    ];
})->values()->toArray();
@endphp
<script>
const bundleProductsData = @json($productsJson);
</script>

{{-- ===== MODAL CREATE ===== --}}
<div id="modalCreate" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between p-6 border-b border-gray-100 sticky top-0 bg-white z-10">
            <h3 class="text-lg font-bold text-gray-900">Buat Paket Bundling Baru</h3>
            <button onclick="closeModal('modalCreate')" class="text-gray-400 hover:text-gray-600 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <form id="formCreate" class="p-6 space-y-5">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Nama Paket <span class="text-red-500">*</span></label>
                    <input type="text" name="name" placeholder="Nama paket bundling"
                           class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500 transition">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Deskripsi</label>
                    <textarea name="description" rows="2" placeholder="Deskripsi singkat paket"
                              class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500 transition resize-none"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Harga Jual <span class="text-red-500">*</span></label>
                    <input type="number" name="price" placeholder="0" min="0"
                           class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500 transition">
                </div>
                <div class="flex items-end pb-1">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" checked
                               class="w-4 h-4 rounded border-slate-300 text-sky-600 focus:ring-sky-500">
                        <span class="text-sm font-semibold text-slate-700">Paket aktif</span>
                    </label>
                </div>
            </div>

            {{-- Items --}}
            <div>
                <div class="flex items-center justify-between mb-2">
                    <label class="text-sm font-semibold text-slate-700">Komposisi Barang <span class="text-red-500">*</span></label>
                    <span class="text-xs text-slate-400" id="createItemCount">1 item</span>
                </div>
                <div id="createItemsContainer" class="space-y-3">
                    <div class="create-item-row flex items-center gap-2 bg-slate-50 rounded-lg p-3">
                        <select name="products[]"
                                class="flex-1 border border-slate-300 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-sky-500 transition">
                            <option value="">-- Pilih Barang --</option>
                            @foreach($products as $p)
                            <option value="{{ $p->id }}">{{ $p->name }} (Stok: {{ $p->stock }})</option>
                            @endforeach
                        </select>
                        <input type="number" name="quantities[]" value="1" min="1" placeholder="Qty"
                               class="w-20 border border-slate-300 rounded-lg px-2 py-2 text-sm text-center focus:outline-none focus:ring-2 focus:ring-sky-500 transition">
                        <button type="button" onclick="removeCreateRow(this)" class="text-slate-400 hover:text-red-500 transition-colors p-1" disabled>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>
                <button type="button" onclick="addCreateRow()"
                        class="mt-3 w-full py-2.5 border-2 border-dashed border-slate-300 hover:border-sky-400 text-slate-500 hover:text-sky-600 rounded-lg text-sm font-medium transition-all">
                    + Tambah Barang
                </button>
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
                        class="px-6 py-2 bg-[#0284c7] hover:bg-[#0369a1] text-white rounded-xl text-sm font-medium transition-colors">
                    Simpan Paket
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ===== MODAL EDIT ===== --}}
<div id="modalEdit" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl mx-4 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between p-6 border-b border-gray-100 sticky top-0 bg-white z-10">
            <h3 class="text-lg font-bold text-gray-900">Edit Paket Bundling</h3>
            <button onclick="closeModal('modalEdit')" class="text-gray-400 hover:text-gray-600 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        <form id="formEdit" class="p-6 space-y-5">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Nama Paket <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="editName"
                           class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500 transition">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Deskripsi</label>
                    <textarea name="description" id="editDescription" rows="2"
                              class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500 transition resize-none"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Harga Jual <span class="text-red-500">*</span></label>
                    <input type="number" name="price" id="editPrice" min="0"
                           class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-sky-500 transition">
                </div>
                <div class="flex items-end pb-1">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_active" id="editIsActive" value="1"
                               class="w-4 h-4 rounded border-slate-300 text-sky-600 focus:ring-sky-500">
                        <span class="text-sm font-semibold text-slate-700">Paket aktif</span>
                    </label>
                </div>
            </div>

            {{-- Items --}}
            <div>
                <div class="flex items-center justify-between mb-2">
                    <label class="text-sm font-semibold text-slate-700">Komposisi Barang <span class="text-red-500">*</span></label>
                    <span class="text-xs text-slate-400" id="editItemCount">0 item</span>
                </div>
                <div id="editItemsContainer" class="space-y-3"></div>
                <button type="button" onclick="addEditRow()"
                        class="mt-3 w-full py-2.5 border-2 border-dashed border-slate-300 hover:border-sky-400 text-slate-500 hover:text-sky-600 rounded-lg text-sm font-medium transition-all">
                    + Tambah Barang
                </button>
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
                        class="px-6 py-2 bg-[#0284c7] hover:bg-[#0369a1] text-white rounded-xl text-sm font-medium transition-colors">
                    Update Paket
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

// ── Build product options HTML ──
function buildProductOptions(selectedId) {
    let html = '<option value="">-- Pilih Barang --</option>';
    bundleProductsData.forEach(p => {
        const sel = p.id == selectedId ? ' selected' : '';
        html += `<option value="${p.id}"${sel}>${p.name} (Stok: ${p.stock})</option>`;
    });
    return html;
}

// ── Item row HTML ──
function itemRowHTML(productId, qty, prefix) {
    return `
    <div class="${prefix}-item-row flex items-center gap-2 bg-slate-50 rounded-lg p-3">
        <select name="products[]" class="flex-1 border border-slate-300 rounded-lg px-3 py-2 text-sm bg-white focus:outline-none focus:ring-2 focus:ring-sky-500 transition">
            ${buildProductOptions(productId)}
        </select>
        <input type="number" name="quantities[]" value="${qty || 1}" min="1" placeholder="Qty"
               class="w-20 border border-slate-300 rounded-lg px-2 py-2 text-sm text-center focus:outline-none focus:ring-2 focus:ring-sky-500 transition">
        <button type="button" onclick="removeRow(this)" class="text-slate-400 hover:text-red-500 transition-colors p-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>
    </div>`;
}

function removeRow(btn) {
    const container = btn.closest('[id$="ItemsContainer"]') || btn.closest('.space-y-3');
    btn.closest('[class*="item-row"]').remove();
    updateItemCount(container);
}
function removeCreateRow(btn) {
    const container = document.getElementById('createItemsContainer');
    if (container.querySelectorAll('.create-item-row').length > 1) {
        btn.closest('.create-item-row').remove();
        updateCreateItemCount();
    }
}
function updateCreateItemCount() {
    const n = document.getElementById('createItemsContainer').querySelectorAll('.create-item-row').length;
    document.getElementById('createItemCount').textContent = n + ' item';
}
function updateEditItemCount() {
    const n = document.getElementById('editItemsContainer').querySelectorAll('.edit-item-row').length;
    document.getElementById('editItemCount').textContent = n + ' item';
}
function updateItemCount(container) {
    const id = container?.id;
    if (id === 'createItemsContainer') updateCreateItemCount();
    else updateEditItemCount();
}

function addCreateRow() {
    const container = document.getElementById('createItemsContainer');
    container.insertAdjacentHTML('beforeend', itemRowHTML(null, 1, 'create'));
    // fix: first row remove button should be enabled when >1 rows
    const rows = container.querySelectorAll('.create-item-row');
    rows.forEach(r => r.querySelector('button').disabled = rows.length === 1);
    updateCreateItemCount();
}
function addEditRow() {
    document.getElementById('editItemsContainer').insertAdjacentHTML('beforeend', itemRowHTML(null, 1, 'edit'));
    updateEditItemCount();
}

// ── Create ──
document.getElementById('formCreate').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = document.getElementById('btnCreateSubmit');
    btn.disabled = true; btn.textContent = 'Menyimpan...';
    document.getElementById('createErrors').classList.add('hidden');
    const formData = new FormData(this);
    if (!this.querySelector('[name="is_active"]').checked) formData.delete('is_active');
    try {
        const res = await fetch('{{ route("pos.bundles.store") }}', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            body: formData
        });
        const data = await res.json();
        if (res.ok && data.success) {
            closeModal('modalCreate'); this.reset();
            // reset item rows back to 1
            document.getElementById('createItemsContainer').innerHTML = itemRowHTML(null, 1, 'create').replace('onclick="removeRow(this)"', 'onclick="removeCreateRow(this)"').replace('<button type="button" onclick="removeCreateRow(this)"', '<button type="button" onclick="removeCreateRow(this)" disabled');
            updateCreateItemCount();
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
        Swal.fire({ icon: 'error', title: 'Error!', text: 'Koneksi bermasalah.' });
    } finally { btn.disabled = false; btn.textContent = 'Simpan Paket'; }
});

// ── Fetch & Edit ──
async function fetchAndEditBundle(id) {
    try {
        const res = await fetch(`/pos/bundles/${id}/edit`, { headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken } });
        const data = await res.json();
        const b = data.bundle;
        document.getElementById('editName').value        = b.name || '';
        document.getElementById('editDescription').value = b.description || '';
        document.getElementById('editPrice').value       = b.price || 0;
        document.getElementById('editIsActive').checked  = !!b.is_active;
        // Build item rows
        const container = document.getElementById('editItemsContainer');
        container.innerHTML = '';
        const items = data.items || [];
        if (items.length === 0) {
            container.insertAdjacentHTML('beforeend', itemRowHTML(null, 1, 'edit'));
        } else {
            items.forEach(item => {
                container.insertAdjacentHTML('beforeend', itemRowHTML(item.product_id, item.quantity, 'edit'));
            });
        }
        updateEditItemCount();
        document.getElementById('formEdit').dataset.id = id;
        document.getElementById('editErrors').classList.add('hidden');
        openModal('modalEdit');
    } catch(e) {
        Swal.fire({ icon: 'error', title: 'Error!', text: 'Gagal memuat data paket.' });
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
    if (!this.querySelector('[name="is_active"]').checked) formData.delete('is_active');
    try {
        const res = await fetch(`/pos/bundles/${id}`, {
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
        Swal.fire({ icon: 'error', title: 'Error!', text: 'Koneksi bermasalah.' });
    } finally { btn.disabled = false; btn.textContent = 'Update Paket'; }
});

// ── Delete ──
function confirmDeleteBundle(url, nama) {
    Swal.fire({
        title: 'Hapus Paket?',
        html: `Paket <strong>"${nama}"</strong> akan dihapus permanen.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e11d48',
        cancelButtonColor: '#94a3b8',
        confirmButtonText: 'Ya, Hapus Permanen',
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
                    Swal.fire({ icon: 'error', title: 'Gagal!', text: data.message || 'Gagal menghapus paket' });
                }
            }).catch(() => Swal.fire({ icon: 'error', title: 'Error!', text: 'Koneksi bermasalah.' }));
        }
    });
}
</script>
</x-app-layout>
