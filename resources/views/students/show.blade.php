<x-app-layout>
    <div class="space-y-6">
        
        <a href="{{ route('students.index') }}" class="inline-flex items-center gap-2 text-gray-500 hover:text-blue-600 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Kembali ke Daftar Siswa
        </a>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <div class="space-y-6">
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden text-center p-6">
                    <div class="w-24 h-24 mx-auto bg-gradient-to-br from-blue-500 to-indigo-600 rounded-full flex items-center justify-center text-white text-3xl font-bold mb-4 shadow-lg shadow-blue-200">
                        {{ substr($student->name, 0, 1) }}
                    </div>
                    <h2 class="text-xl font-bold text-gray-800">{{ $student->name }}</h2>
                    <p class="text-sm text-gray-500 font-mono mb-2">{{ $student->nis }} • {{ optional($student->kelas)->nama_kelas ?? '-' }}</p>
                    <span class="px-3 py-1 rounded-full text-xs font-bold border {{ $student->statusBadgeClass }}">{{ $student->status_label }}</span>

                    <div class="mt-6 border-t border-gray-100 pt-4 text-left space-y-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Wali Kelas</span>
                            <span class="font-medium">-</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">No. HP Ortu</span>
                            <span class="font-medium">{{ $student->parent_phone ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Tempat Lahir</span>
                            <span class="font-medium">{{ $student->birth_place ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Tanggal Lahir</span>
                            <span class="font-medium">
                                {{ $student->birth_date ? $student->birth_date->format('d/m/Y') : '-' }}
                            </span>
                        </div>
                    </div>

                    {{-- Info password login siswa --}}
                    @if($student->birth_date)
                    <div class="mt-4 p-3 bg-blue-50 rounded-xl border border-blue-100 text-left">
                        <p class="text-xs text-blue-600 font-bold uppercase mb-1">Password Login Siswa</p>
                        <p class="text-sm font-mono font-bold text-blue-800">{{ $student->birth_date->format('dmy') }}</p>
                        <p class="text-xs text-blue-500 mt-0.5">Format: ddmmyy (tanggal lahir)</p>
                    </div>
                    @endif

                    <div class="mt-4">
                        <a href="{{ route('students.edit', $student->id) }}"
                           class="w-full inline-flex items-center justify-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-4 py-2.5 rounded-xl transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                            Edit Data Siswa
                        </a>
                    </div>
                </div>

                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="font-bold text-gray-800 flex items-center gap-2 mb-4">
                        <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Tagihan & Hutang
                    </h3>
                    
                    <div class="space-y-3">
                        <div class="p-3 bg-purple-50 rounded-xl border border-purple-100">
                            <p class="text-xs text-purple-600 font-bold uppercase mb-1">Hutang Kantin/Koperasi</p>
                            <p class="text-xl font-bold text-purple-700">Rp {{ number_format($debtPos, 0, ',', '.') }}</p>
                        </div>

                        @php
                            $totalBillUnpaid = $student->bills->where('status', 'UNPAID')->sum('amount');
                        @endphp
                        <div class="p-3 bg-orange-50 rounded-xl border border-orange-100">
                            <p class="text-xs text-orange-600 font-bold uppercase mb-1">Tunggakan Sekolah (SPP dll)</p>
                            <p class="text-xl font-bold text-orange-700">Rp {{ number_format($totalBillUnpaid, 0, ',', '.') }}</p>
                        </div>

                        <div class="pt-2 border-t border-gray-100">
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-bold text-gray-600">Total Kewajiban</span>
                                <span class="text-lg font-bold text-red-600">Rp {{ number_format($debtPos + $totalBillUnpaid, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 min-h-[500px]" x-data="{ tab: 'bills' }">
                
                <div class="flex border-b border-gray-100">
                    <button @click="tab = 'bills'" 
                            :class="tab === 'bills' ? 'border-blue-500 text-blue-600 bg-blue-50/50' : 'border-transparent text-gray-500 hover:text-gray-700'"
                            class="flex-1 py-4 text-sm font-bold border-b-2 transition">
                        📄 Tagihan Sekolah (SPP/Gedung)
                    </button>
                    <button @click="tab = 'pos'" 
                            :class="tab === 'pos' ? 'border-purple-500 text-purple-600 bg-purple-50/50' : 'border-transparent text-gray-500 hover:text-gray-700'"
                            class="flex-1 py-4 text-sm font-bold border-b-2 transition">
                        🛒 Riwayat Jajan (POS)
                    </button>
                </div>

                <div x-show="tab === 'bills'" class="p-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">Daftar Tagihan Siswa</h3>
                    
                    @if($student->bills->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left">
                                <thead class="bg-gray-50 text-gray-500 font-bold uppercase text-xs">
                                    <tr>
                                        <th class="px-4 py-3 rounded-l-lg">Keterangan</th>
                                        <th class="px-4 py-3">Jenis</th>
                                        <th class="px-4 py-3">Nominal</th>
                                        <th class="px-4 py-3 text-center">Status</th>
                                        <th class="px-4 py-3 text-right rounded-r-lg">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach($student->bills as $bill)
                                    <tr class="hover:bg-gray-50 transition">
                                        <td class="px-4 py-3 font-medium text-gray-800">{{ $bill->name }}</td>
                                        <td class="px-4 py-3 text-xs">
                                            <span class="bg-gray-100 px-2 py-1 rounded text-gray-600">{{ $bill->type }}</span>
                                        </td>
                                        <td class="px-4 py-3 font-bold text-gray-700">{{ $bill->formatted_amount }}</td>
                                        <td class="px-4 py-3 text-center">
                                            <span class="px-2.5 py-1 rounded-full text-xs font-bold border {{ $bill->status_color }}">
                                                {{ $bill->status == 'UNPAID' ? 'BELUM LUNAS' : 'LUNAS' }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            @if($bill->status == 'UNPAID')
                                                {{--
                                                    PHASE 1.5 — XSS fix:
                                                    Values are stored in data-* attributes (HTML-escaped by Blade {{ }}).
                                                    No Blade variable is interpolated into a JavaScript string literal.
                                                    The click handler is attached via addEventListener in the script below.
                                                --}}
                                                <button type="button"
                                                        class="btn-bill-pay bg-blue-600 hover:bg-blue-700 text-white px-3 py-1.5 rounded-lg text-xs font-bold shadow-md hover:shadow-lg transition transform active:scale-95"
                                                        data-bill-id="{{ $bill->id }}"
                                                        data-bill-name="{{ $bill->name }}"
                                                        data-bill-amount="{{ $bill->formatted_amount }}">
                                                    Bayar Sekarang
                                                </button>
                                            @else
                                                <div class="flex items-center justify-end gap-2">
                                                    <span class="text-green-600 text-xs font-bold flex items-center gap-1">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                        Lunas
                                                    </span>
                                                    <a href="{{ route('bills.print', $bill->id) }}" target="_blank" 
                                                       class="text-gray-500 hover:text-gray-800 bg-gray-100 hover:bg-gray-200 p-1.5 rounded border border-gray-200 transition" 
                                                       title="Cetak Kwitansi">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                                    </a>
                                                </div>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-10 text-gray-400">
                            <svg class="w-12 h-12 mx-auto mb-2 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            <p>Belum ada tagihan untuk siswa ini.</p>
                        </div>
                    @endif
                </div>

                <div x-show="tab === 'pos'" class="p-6" style="display: none;">
                    <h3 class="text-lg font-bold text-gray-800 mb-4">Transaksi Kantin & Koperasi</h3>
                    
                    @if($posTransactions->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm text-left">
                                <thead class="bg-purple-50 text-purple-800 font-bold uppercase text-xs">
                                    <tr>
                                        <th class="px-4 py-3 rounded-l-lg">Tanggal</th>
                                        <th class="px-4 py-3">Kode TRX</th>
                                        <th class="px-4 py-3">Total</th>
                                        <th class="px-4 py-3 text-center">Status</th>
                                        <th class="px-4 py-3 text-right rounded-r-lg">Lihat</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach($posTransactions as $trx)
                                    <tr class="hover:bg-purple-50/30 transition">
                                        <td class="px-4 py-3 text-gray-500">{{ $trx->created_at->format('d/m/Y H:i') }}</td>
                                        <td class="px-4 py-3 font-mono text-xs">{{ $trx->transaction_code }}</td>
                                        <td class="px-4 py-3 font-bold text-gray-800">Rp {{ number_format($trx->total_amount, 0, ',', '.') }}</td>
                                        <td class="px-4 py-3 text-center">
                                            @if($trx->payment_status == 'PAID')
                                                <span class="text-green-600 bg-green-100 px-2 py-0.5 rounded text-[10px] font-bold">LUNAS</span>
                                            @else
                                                <span class="text-purple-600 bg-purple-100 px-2 py-0.5 rounded text-[10px] font-bold animate-pulse">HUTANG</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-right">
                                            <a href="{{ route('pos.transaction.print', $trx->id) }}" target="_blank" class="text-blue-500 hover:underline text-xs">Struk</a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-10 text-gray-400">
                            <p>Belum ada riwayat belanja.</p>
                        </div>
                    @endif
                </div>

            </div>
        </div>
    </div>

    <script>
        /**
         * PHASE 1.5 — XSS fix.
         *
         * Previously: onclick="confirmBillPay('{bill.id}', '{bill.name}', ...)"
         * Problem:    Blade values interpolated directly into JS string literals.
         *             A name containing ' or " would break the JS string context.
         *
         * Now: values live in data-* HTML attributes (Blade @{{ }} HTML-escapes them).
         *      JS reads them via dataset — the browser decodes HTML entities so the
         *      JS code always receives the original plain-text string as data, never
         *      as executable code.
         *
         * Event delegation on <tbody> handles all rows including any added dynamically.
         */
        document.addEventListener('DOMContentLoaded', function () {
            document.addEventListener('click', function (e) {
                const btn = e.target.closest('.btn-bill-pay');
                if (! btn) return;

                // Read values from data attributes — never from JS string interpolation.
                const billId     = btn.dataset.billId;
                const billName   = btn.dataset.billName;
                const billAmount = btn.dataset.billAmount;

                confirmBillPay(billId, billName, billAmount);
            });
        });

        function confirmBillPay(id, name, amount) {
            Swal.fire({
                title: 'Konfirmasi Pembayaran',
                // Swal.fire html renders the name as text inside <b>.
                // textContent assignment below ensures it is treated as plain text,
                // not executable HTML — preventing stored XSS via bill name.
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#2563eb',
                cancelButtonColor: '#d1d5db',
                confirmButtonText: 'Ya, Terima Uang',
                cancelButtonText: 'Batal',
                didOpen: function () {
                    // Build the modal body using DOM text nodes — no innerHTML with
                    // user-supplied values, so a name like <img onerror=...> is inert.
                    const container = document.createElement('div');

                    const line1    = document.createElement('span');
                    line1.appendChild(document.createTextNode('Terima pembayaran '));
                    const bold = document.createElement('b');
                    bold.textContent = name;          // plain text — not innerHTML
                    line1.appendChild(bold);
                    container.appendChild(line1);

                    container.appendChild(document.createElement('br'));

                    const amountEl = document.createElement('span');
                    amountEl.className = 'text-2xl font-bold text-blue-600';
                    amountEl.textContent = amount;    // plain text — not innerHTML
                    container.appendChild(amountEl);

                    const htmlContainer = document.querySelector('.swal2-html-container');
                    if (htmlContainer) {
                        htmlContainer.innerHTML = '';
                        htmlContainer.appendChild(container);
                    }
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    // Form POST — id is a numeric string from data-bill-id.
                    // CSRF token is injected by Blade @csrf, unchanged.
                    let form = document.createElement('form');
                    form.method = 'POST';
                    form.action = `/bills/${id}/pay`;
                    form.innerHTML = `@csrf`;
                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }
    </script>
</x-app-layout>
