<x-app-layout>
    <x-slot name="header">
        Detail Keuangan Siswa
    </x-slot>

    <div class="space-y-6">
        
        <!-- BACK BUTTON -->
        <div>
            <a href="{{ route('students.index') }}" 
               class="inline-flex items-center gap-2 text-xs font-bold text-slate-500 hover:text-[#178BC1] transition-colors bg-white px-3.5 py-2 rounded-xl border border-slate-200 shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                    <line x1="19" y1="12" x2="5" y2="12"></line>
                    <polyline points="12 19 5 12 12 5"></polyline>
                </svg>
                <span>Kembali ke Daftar Siswa</span>
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- LEFT COLUMN: PROFIL & RINGKASAN TUNGGAKAN -->
            <div class="space-y-6">
                
                <!-- PROFIL CARD -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden text-center p-6">
                    <div class="w-20 h-20 mx-auto bg-blue-50 border-2 border-blue-200 rounded-2xl flex items-center justify-center text-[#178BC1] text-3xl font-black mb-3">
                        {{ strtoupper(substr($student->name, 0, 1)) }}
                    </div>
                    <h2 class="text-lg font-bold text-slate-800">{{ $student->name }}</h2>
                    <p class="text-xs text-slate-400 font-mono mt-0.5">NISN: {{ $student->nisn ?? ($student->nis ?? '-') }} • Kelas {{ $student->class ?? ($student->class_name ?? 'Umum') }}</p>
                    
                    <div class="mt-3">
                        <span class="px-2.5 py-1 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-lg text-xs font-bold inline-block">
                            Siswa Terdaftar Aktif
                        </span>
                    </div>

                    <div class="mt-6 border-t border-slate-100 pt-4 text-left space-y-3 text-xs">
                        <div class="flex justify-between items-center">
                            <span class="text-slate-500">Nama Wali / Ortu</span>
                            <span class="font-bold text-slate-700">{{ $student->parent_name ?? ($student->father_name ?? '-') }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-slate-500">No. WhatsApp Ortu</span>
                            <span class="font-bold text-slate-700">{{ $student->parent_phone ?? '-' }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-slate-500">Tanggal Masuk</span>
                            <span class="font-bold text-slate-700">
                                @if($student->enrolled_at)
                                    <span class="text-emerald-700">{{ $student->enrolled_at->format('d M Y') }}</span>
                                @else
                                    <span class="text-amber-600">Belum disetel</span>
                                @endif
                            </span>
                        </div>

                        @auth
                            @if(auth()->user()->role === 'admin')
                                <div class="pt-3 border-t border-slate-100">
                                    <form method="POST" action="{{ route('students.enrolled-at.update', $student->id) }}" class="space-y-2">
                                        @csrf
                                        <label class="block text-[11px] font-bold text-slate-600 uppercase tracking-wider">
                                            Enrollment Date (Admin Only)
                                        </label>
                                        <div class="flex gap-2">
                                            <input type="date" 
                                                   name="enrolled_at"
                                                   value="{{ $student->enrolled_at ? $student->enrolled_at->format('Y-m-d') : '' }}"
                                                   class="flex-1 border border-slate-200 rounded-xl px-2.5 py-1.5 text-xs focus:ring-2 focus:ring-[#178BC1]/20 focus:border-[#178BC1] focus:outline-none bg-slate-50">
                                            <button type="submit"
                                                    class="bg-[#178BC1] hover:bg-[#126da3] text-white text-xs font-bold px-3 py-1.5 rounded-xl transition-all shadow-sm">
                                                Simpan
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            @endif
                        @endauth
                    </div>
                </div>

                <!-- RINGKASAN KEWAJIBAN -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
                    <h3 class="font-bold text-slate-800 text-sm flex items-center gap-2 mb-4">
                        <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <rect x="2" y="5" width="20" height="14" rx="2"></rect>
                            <line x1="2" y1="10" x2="22" y2="10"></line>
                        </svg>
                        Ringkasan Kewajiban
                    </h3>
                    
                    <div class="space-y-3">
                        <div class="p-3 bg-purple-50 rounded-xl border border-purple-200">
                            <p class="text-[10px] text-purple-700 font-bold uppercase tracking-wider">Hutang Kasir POS / Kantin</p>
                            <p class="text-lg font-bold text-purple-800 mt-0.5">Rp {{ number_format($debtPos ?? 0, 0, ',', '.') }}</p>
                        </div>

                        @php
                            $totalBillUnpaid = $student->bills->where('status', 'UNPAID')->sum('amount');
                            $debtPosAmount = $debtPos ?? 0;
                        @endphp
                        <div class="p-3 bg-amber-50 rounded-xl border border-amber-200">
                            <p class="text-[10px] text-amber-700 font-bold uppercase tracking-wider">Tunggakan Sekolah (SPP dll)</p>
                            <p class="text-lg font-bold text-amber-800 mt-0.5">Rp {{ number_format($totalBillUnpaid, 0, ',', '.') }}</p>
                        </div>

                        <div class="pt-3 border-t border-slate-100 flex justify-between items-center">
                            <span class="text-xs font-bold text-slate-600">Total Seluruh Kewajiban:</span>
                            <span class="text-base font-black text-rose-600">
                                Rp {{ number_format($debtPosAmount + $totalBillUnpaid, 0, ',', '.') }}
                            </span>
                        </div>
                    </div>
                </div>

            </div>

            <!-- RIGHT COLUMN: TABEL TAGIHAN & HISTORI POS -->
            <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden min-h-[500px]" 
                 x-data="{ activeTab: 'bills' }">
                
                <!-- TABS HEADER -->
                <div class="flex border-b border-slate-200 bg-slate-50/60 p-1.5 gap-1">
                    <button @click="activeTab = 'bills'" 
                            :class="activeTab === 'bills' ? 'bg-white text-[#178BC1] shadow-sm border border-slate-200 font-bold' : 'text-slate-500 hover:text-slate-800 border border-transparent font-medium'"
                            class="flex-1 py-2.5 px-4 rounded-xl text-xs flex items-center justify-center gap-2 transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <rect x="2" y="5" width="20" height="14" rx="2"></rect><line x1="2" y1="10" x2="22" y2="10"></line>
                        </svg>
                        <span>Tagihan Sekolah (SPP / Non-SPP)</span>
                    </button>
                    <button @click="activeTab = 'pos'" 
                            :class="activeTab === 'pos' ? 'bg-white text-[#178BC1] shadow-sm border border-slate-200 font-bold' : 'text-slate-500 hover:text-slate-800 border border-transparent font-medium'"
                            class="flex-1 py-2.5 px-4 rounded-xl text-xs flex items-center justify-center gap-2 transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                        </svg>
                        <span>Riwayat Belanja Kasir POS</span>
                    </button>
                </div>

                <!-- TAB 1: TAGIHAN SEKOLAH -->
                <div x-show="activeTab === 'bills'" class="p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-bold text-slate-800">Daftar Tagihan Siswa</h3>
                        <a href="{{ route('bills.create') }}" class="text-xs font-bold text-[#178BC1] hover:underline flex items-center gap-1">
                            + Buat Tagihan Baru
                        </a>
                    </div>
                    
                    @if($student->bills->count() > 0)
                        <div class="overflow-x-auto border border-slate-200 rounded-xl">
                            <table class="w-full text-sm text-left">
                                <thead class="bg-slate-50/80 text-slate-500 font-bold uppercase text-[10px] tracking-wider border-b border-slate-200">
                                    <tr>
                                        <th class="px-4 py-3">Nama Kewajiban</th>
                                        <th class="px-4 py-3">Jenis</th>
                                        <th class="px-4 py-3">Nominal</th>
                                        <th class="px-4 py-3 text-center">Status</th>
                                        <th class="px-4 py-3 text-right">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @foreach($student->bills as $bill)
                                        <tr class="hover:bg-slate-50/50 transition-colors">
                                            <td class="px-4 py-3">
                                                <p class="font-bold text-slate-800 text-xs">{{ $bill->name }}</p>
                                                @if($bill->due_date)
                                                    <p class="text-[10px] text-slate-400 mt-0.5">Jatuh Tempo: {{ \Carbon\Carbon::parse($bill->due_date)->format('d M Y') }}</p>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 text-xs">
                                                <span class="bg-blue-50 text-[#178BC1] border border-blue-200 px-2 py-0.5 rounded-md font-bold text-[10px]">
                                                    {{ $bill->type }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 font-bold text-slate-800 text-xs">
                                                {{ $bill->formatted_amount }}
                                            </td>
                                            <td class="px-4 py-3 text-center">
                                                @if($bill->status === 'PAID')
                                                    <span class="px-2.5 py-1 rounded-lg text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 uppercase tracking-wider">
                                                        Lunas
                                                    </span>
                                                @else
                                                    <span class="px-2.5 py-1 rounded-lg text-[10px] font-bold bg-amber-50 text-amber-700 border border-amber-200 uppercase tracking-wider">
                                                        Belum Lunas
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 text-right">
                                                @if($bill->status == 'UNPAID')
                                                    <button onclick="confirmBillPay('{{ $bill->id }}', '{{ $bill->name }}', '{{ $bill->formatted_amount }}')"
                                                            class="bg-[#178BC1] hover:bg-[#126da3] text-white px-3 py-1.5 rounded-xl text-xs font-bold shadow-sm transition-all">
                                                        Bayar Tunai
                                                    </button>
                                                @else
                                                    <div class="flex items-center justify-end gap-2">
                                                        <span class="text-emerald-700 text-xs font-bold flex items-center gap-1">
                                                            <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                                                <polyline points="20 6 9 17 4 12"></polyline>
                                                            </svg>
                                                            Lunas
                                                        </span>
                                                        <a href="{{ route('bills.print', $bill->id) }}" target="_blank" 
                                                           class="text-slate-600 hover:text-slate-900 bg-slate-100 hover:bg-slate-200 p-1.5 rounded-lg border border-slate-200 transition-colors" 
                                                           title="Cetak Kwitansi">
                                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                                                <polyline points="6 9 6 2 18 2 18 9"></polyline>
                                                                <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
                                                                <rect x="6" y="14" width="12" height="8"></rect>
                                                            </svg>
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
                        <div class="text-center py-12 text-slate-400 border border-dashed border-slate-200 rounded-xl">
                            <svg class="w-10 h-10 mx-auto mb-2 text-slate-300" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                <rect x="2" y="5" width="20" height="14" rx="2"></rect><line x1="2" y1="10" x2="22" y2="10"></line>
                            </svg>
                            <p class="text-xs font-medium text-slate-500">Belum ada tagihan untuk siswa ini.</p>
                        </div>
                    @endif
                </div>

                <!-- TAB 2: RIWAYAT KASIR POS -->
                <div x-show="activeTab === 'pos'" class="p-6" x-cloak>
                    <h3 class="text-sm font-bold text-slate-800 mb-4">Transaksi Kantin & Koperasi</h3>
                    
                    @if(isset($posTransactions) && $posTransactions->count() > 0)
                        <div class="overflow-x-auto border border-slate-200 rounded-xl">
                            <table class="w-full text-sm text-left">
                                <thead class="bg-slate-50/80 text-slate-500 font-bold uppercase text-[10px] tracking-wider border-b border-slate-200">
                                    <tr>
                                        <th class="px-4 py-3">Waktu</th>
                                        <th class="px-4 py-3">Kode TRX</th>
                                        <th class="px-4 py-3">Total Belanja</th>
                                        <th class="px-4 py-3 text-center">Status</th>
                                        <th class="px-4 py-3 text-right">Struk</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @foreach($posTransactions as $trx)
                                        <tr class="hover:bg-slate-50/50 transition-colors">
                                            <td class="px-4 py-3 text-xs text-slate-500 font-medium">{{ $trx->created_at->format('d/m/Y H:i') }}</td>
                                            <td class="px-4 py-3 font-mono text-xs font-semibold text-slate-700">{{ $trx->transaction_code }}</td>
                                            <td class="px-4 py-3 font-bold text-slate-800 text-xs">Rp {{ number_format($trx->total_amount, 0, ',', '.') }}</td>
                                            <td class="px-4 py-3 text-center">
                                                @if($trx->payment_status == 'PAID')
                                                    <span class="text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded text-[10px] font-bold">LUNAS</span>
                                                @else
                                                    <span class="text-amber-700 bg-amber-50 border border-amber-200 px-2 py-0.5 rounded text-[10px] font-bold">HUTANG</span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 text-right">
                                                <a href="{{ route('pos.transaction.print', $trx->id) }}" target="_blank" class="text-[#178BC1] hover:underline text-xs font-bold">
                                                    Lihat Struk
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-12 text-slate-400 border border-dashed border-slate-200 rounded-xl">
                            <p class="text-xs font-medium text-slate-500">Belum ada riwayat transaksi kantin / koperasi.</p>
                        </div>
                    @endif
                </div>

            </div>
        </div>
    </div>

    <script>
        function confirmBillPay(id, name, amount) {
            Swal.fire({
                title: 'Konfirmasi Pembayaran Kasir',
                html: `Terima pembayaran tunai untuk <b>${name}</b> sebesar <br><span class="text-2xl font-bold text-[#178BC1] mt-2 block">${amount}</span>`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#178BC1',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Ya, Terima Uang',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
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
