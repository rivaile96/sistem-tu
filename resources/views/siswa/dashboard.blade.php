<!DOCTYPE html>
<html lang="id" x-data="{ tab: 'unpaid' }">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Portal Siswa — {{ $student->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    {{-- Midtrans Snap.js Sandbox --}}
    <script
        src="https://app.sandbox.midtrans.com/snap/snap.js"
        data-client-key="{{ config('services.midtrans.client_key') }}">
    </script>
    <style>
        [x-cloak] { display: none !important; }
        .bill-card { transition: box-shadow 0.15s ease, transform 0.15s ease; }
        .bill-card:active { transform: scale(0.98); }
    </style>
</head>
<body class="bg-gray-50 min-h-screen pb-8">

    {{-- ===== HEADER ===== --}}
    <div class="bg-gradient-to-r from-sky-500 via-sky-600 to-blue-700 px-4 pt-12 pb-20">
        <div class="max-w-md mx-auto flex items-center justify-between">
            <div class="flex items-center gap-3">
                {{-- Avatar initials --}}
                <div class="w-12 h-12 rounded-2xl bg-white/20 backdrop-blur flex items-center justify-center text-white font-bold text-lg shadow-inner">
                    {{ strtoupper(substr($student->name, 0, 1)) }}
                </div>
                <div>
                    <p class="text-white font-bold text-base leading-tight">{{ $student->name }}</p>
                    <p class="text-sky-200 text-xs mt-0.5">NIS: {{ $student->nis }}</p>
                    <p class="text-sky-300 text-xs">{{ optional($student->kelas)->nama_kelas ?? $schoolName }}</p>
                </div>
            </div>
            <form method="POST" action="{{ route('siswa.logout') }}">
                @csrf
                <button type="submit"
                        class="flex items-center gap-1.5 bg-white/15 hover:bg-white/25 text-white text-xs font-medium px-3 py-2 rounded-xl transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    Keluar
                </button>
            </form>
        </div>
    </div>

    <div class="max-w-md mx-auto px-4">

        {{-- Flash Messages --}}
        @if (session('success'))
            <div class="mt-4 bg-green-50 border border-green-200 rounded-2xl px-4 py-3 flex items-start gap-2">
                <svg class="w-5 h-5 text-green-500 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <p class="text-green-700 text-sm font-medium">{{ session('success') }}</p>
            </div>
        @endif

        {{-- ===== SUMMARY CARDS ===== --}}
        <div class="grid grid-cols-2 gap-3 -mt-12 mb-5">
            {{-- Belum Bayar --}}
            <div class="bg-white rounded-2xl p-4 shadow-xl border border-gray-100">
                <div class="flex items-center gap-1.5 mb-2">
                    <div class="w-2 h-2 rounded-full bg-red-400"></div>
                    <p class="text-xs text-gray-500 font-semibold uppercase tracking-wide">Belum Bayar</p>
                </div>
                <p class="text-lg font-extrabold text-red-600 leading-tight">
                    Rp {{ number_format($totalUnpaid, 0, ',', '.') }}
                </p>
                <p class="text-xs text-gray-400 mt-1">{{ $unpaidBills->count() }} tagihan</p>
            </div>
            {{-- Sudah Bayar --}}
            <div class="bg-white rounded-2xl p-4 shadow-xl border border-gray-100">
                <div class="flex items-center gap-1.5 mb-2">
                    <div class="w-2 h-2 rounded-full bg-green-400"></div>
                    <p class="text-xs text-gray-500 font-semibold uppercase tracking-wide">Sudah Bayar</p>
                </div>
                <p class="text-lg font-extrabold text-green-600 leading-tight">
                    Rp {{ number_format($totalPaid, 0, ',', '.') }}
                </p>
                <p class="text-xs text-gray-400 mt-1">{{ $paidBills->count() }} tagihan</p>
            </div>
        </div>

        {{-- ===== TAGIHAN BULAN INI ===== --}}
        <div class="mt-5">
            <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-2">
                Tagihan {{ \Carbon\Carbon::now()->locale('id')->isoFormat('MMMM YYYY') }}
            </h2>
            @if($thisMonthBills->isEmpty())
                <div class="bg-white rounded-2xl px-4 py-3 text-sm text-gray-400 text-center border border-gray-100">
                    Tidak ada tagihan bulan ini
                </div>
            @elseif($thisMonthBills->where('status', '!=', 'PAID')->isEmpty())
                <div class="bg-green-50 border border-green-100 rounded-2xl px-4 py-3 flex items-center gap-2">
                    <span class="text-green-500 text-lg">✅</span>
                    <span class="text-sm font-medium text-green-700">Semua tagihan bulan ini sudah lunas!</span>
                </div>
            @else
                <div class="bg-white rounded-2xl border border-gray-100 overflow-hidden divide-y divide-gray-50">
                    @foreach($thisMonthBills as $mb)
                    <div class="flex items-center justify-between px-4 py-3">
                        <div>
                            <p class="text-sm font-medium text-gray-800">{{ $mb->name }}</p>
                            <p class="text-xs text-gray-400">Rp {{ number_format($mb->amount, 0, ',', '.') }}</p>
                        </div>
                        @if($mb->status === 'PAID')
                            <span class="text-xs font-semibold bg-green-100 text-green-700 px-2.5 py-1 rounded-full">Lunas</span>
                        @else
                            <span class="text-xs font-semibold bg-red-100 text-red-700 px-2.5 py-1 rounded-full">Belum Bayar</span>
                        @endif
                    </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- ===== TABS ===== --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="flex border-b border-gray-100">
                <button
                    @click="tab = 'unpaid'"
                    :class="tab === 'unpaid'
                        ? 'border-b-2 border-sky-600 text-sky-600 font-bold bg-sky-50/50'
                        : 'text-gray-400 hover:text-gray-600'"
                    class="flex-1 py-3.5 text-sm transition-all duration-150"
                >
                    Belum Lunas
                    <span class="ml-1 text-xs">({{ $unpaidBills->count() }})</span>
                </button>
                <button
                    @click="tab = 'paid'"
                    :class="tab === 'paid'
                        ? 'border-b-2 border-sky-600 text-sky-600 font-bold bg-sky-50/50'
                        : 'text-gray-400 hover:text-gray-600'"
                    class="flex-1 py-3.5 text-sm transition-all duration-150"
                >
                    Lunas
                    <span class="ml-1 text-xs">({{ $paidBills->count() }})</span>
                </button>
            </div>

            {{-- ===== BELUM LUNAS ===== --}}
            <div x-show="tab === 'unpaid'" class="divide-y divide-gray-50">
                @php $unpaidGrouped = $unpaidBills->groupBy('bill_year'); @endphp
                @forelse($unpaidGrouped as $year => $yearBills)
                    <div class="px-4 pt-3 pb-1">
                        <p class="text-xs text-gray-400 font-semibold uppercase tracking-widest">{{ $year }}</p>
                    </div>
                    @foreach($yearBills as $bill)
                        <div
                            class="bill-card flex items-center px-4 py-4 gap-3 cursor-pointer hover:bg-sky-50/40 active:bg-sky-100/60"
                            onclick="initBayar({{ $bill->id }}, '{{ addslashes($bill->name ?? 'Tagihan') }}', {{ (int)$bill->amount }})"
                        >
                            {{-- Left: icon --}}
                            <div class="w-10 h-10 rounded-xl bg-red-100 flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                            </div>
                            {{-- Center: info --}}
                            <div class="flex-1 min-w-0">
                                <p class="font-semibold text-gray-900 text-sm truncate">{{ $bill->name ?? 'Tagihan ' . $bill->bill_month . '/' . $bill->bill_year }}</p>
                                <p class="text-xs text-gray-400 mt-0.5">
                                    @if($bill->due_date)
                                        Jatuh tempo {{ \Carbon\Carbon::parse($bill->due_date)->isoFormat('D MMM Y') }}
                                    @elseif($bill->bill_month && $bill->bill_year)
                                        {{ \Carbon\Carbon::createFromDate($bill->bill_year, $bill->bill_month, 1)->isoFormat('MMMM Y') }}
                                    @else
                                        —
                                    @endif
                                </p>
                            </div>
                            {{-- Right: amount + badge --}}
                            <div class="text-right shrink-0">
                                @if($bill->original_amount && $bill->original_amount != $bill->amount)
                                    <p class="text-xs text-gray-400 line-through">Rp {{ number_format($bill->original_amount, 0, ',', '.') }}</p>
                                @endif
                                <p class="font-bold text-gray-900 text-sm">Rp {{ number_format($bill->amount, 0, ',', '.') }}</p>
                                <span class="inline-block mt-1 bg-red-100 text-red-600 text-xs px-2 py-0.5 rounded-full font-semibold">
                                    {{ $bill->status === 'PARTIAL' ? 'Sebagian' : 'Belum Bayar' }}
                                </span>
                            </div>
                            <svg class="w-4 h-4 text-sky-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                            </svg>
                        </div>
                    @endforeach
                @empty
                    <div class="text-center py-16 px-4">
                        <p class="text-5xl mb-3">🎉</p>
                        <p class="text-gray-700 font-bold text-base">Semua tagihan lunas!</p>
                        <p class="text-gray-400 text-sm mt-1">Tidak ada tagihan yang perlu dibayar.</p>
                    </div>
                @endforelse
            </div>

            {{-- ===== LUNAS ===== --}}
            <div x-show="tab === 'paid'" class="divide-y divide-gray-50">
                @php $paidGrouped = $paidBills->groupBy('bill_year'); @endphp
                @forelse($paidGrouped as $year => $yearBills)
                    <div class="px-4 pt-3 pb-1">
                        <p class="text-xs text-gray-400 font-semibold uppercase tracking-widest">{{ $year }}</p>
                    </div>
                    @foreach($yearBills as $bill)
                        <div class="flex items-center px-4 py-4 gap-3">
                            {{-- Left: icon --}}
                            <div class="w-10 h-10 rounded-xl bg-green-100 flex items-center justify-center shrink-0">
                                <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            {{-- Center: info --}}
                            <div class="flex-1 min-w-0">
                                <p class="font-semibold text-gray-900 text-sm truncate">{{ $bill->name ?? 'Tagihan ' . $bill->bill_month . '/' . $bill->bill_year }}</p>
                                <p class="text-xs text-gray-400 mt-0.5">
                                    {{-- Phase 2.4: use paid_at as canonical payment date --}}
                        @if($bill->paid_at)
                            Dibayar {{ $bill->paid_at->isoFormat('D MMM Y') }}
                        @else
                            Tanggal pembayaran tidak tersedia
                        @endif
                                    @if($bill->payment_method)
                                        · <span class="capitalize">{{ $bill->payment_method }}</span>
                                    @endif
                                </p>
                                <a href="{{ route('siswa.tagihan.struk', $bill->id) }}" class="text-xs text-sky-500 font-medium mt-0.5 block">Lihat Struk →</a>
                            </div>
                            {{-- Right: amount + badge --}}
                            <div class="text-right shrink-0">
                                <p class="font-bold text-gray-900 text-sm">Rp {{ number_format($bill->amount, 0, ',', '.') }}</p>
                                <span class="inline-block mt-1 bg-green-100 text-green-600 text-xs px-2 py-0.5 rounded-full font-semibold">
                                    Lunas
                                </span>
                            </div>
                        </div>
                    @endforeach
                @empty
                    <div class="text-center py-16 px-4">
                        <p class="text-gray-400 text-sm">Belum ada riwayat pembayaran.</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- ===== INFO FOOTER ===== --}}
        <p class="text-center text-gray-400 text-xs mt-6">
            {{ $schoolName }} &middot; Portal Siswa
        </p>

    </div>

    {{-- ===== PAYMENT BOTTOM SHEET (Alpine) ===== --}}
    <div
        x-data="{ open: false, billId: null, billName: '', amount: 0, loading: false }"
        x-on:open-payment.window="open = true; billId = $event.detail.id; billName = $event.detail.name; amount = $event.detail.amount"
        x-show="open"
        x-cloak
        class="fixed inset-0 z-50 flex items-end"
    >
        {{-- Backdrop --}}
        <div
            class="absolute inset-0 bg-black/50 backdrop-blur-sm"
            @click="open = false"
        ></div>

        {{-- Sheet --}}
        <div
            class="relative w-full bg-white rounded-t-3xl shadow-2xl px-6 py-6 max-w-md mx-auto"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="translate-y-full"
            x-transition:enter-end="translate-y-0"
        >
            <div class="w-10 h-1 bg-gray-200 rounded-full mx-auto mb-5"></div>

            <h3 class="text-lg font-bold text-gray-900 mb-1">Konfirmasi Pembayaran</h3>
            <p class="text-gray-500 text-sm mb-5" x-text="billName"></p>

            <div class="bg-sky-50 rounded-2xl p-4 mb-5 flex items-center justify-between">
                <p class="text-sm text-sky-700 font-medium">Total Tagihan</p>
                <p class="text-xl font-extrabold text-sky-700" x-text="'Rp ' + amount.toLocaleString('id-ID')"></p>
            </div>

            <p class="text-xs text-gray-400 mb-5 text-center">
                Pembayaran diproses melalui Midtrans. Mendukung transfer bank, kartu kredit, QRIS, dan e-wallet.
            </p>

            <div class="space-y-3">
                <button
                    @click="loading = true; prosesPayment(billId, () => { loading = false; open = false; })"
                    :disabled="loading"
                    class="w-full bg-sky-600 hover:bg-sky-700 disabled:bg-sky-300 text-white font-bold py-3.5 rounded-xl transition-colors flex items-center justify-center gap-2"
                >
                    <template x-if="loading">
                        <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                        </svg>
                    </template>
                    <span x-text="loading ? 'Memproses...' : 'Bayar Sekarang'"></span>
                </button>
                <button
                    @click="open = false"
                    class="w-full border border-gray-200 text-gray-500 font-medium py-3 rounded-xl hover:bg-gray-50 transition-colors text-sm"
                >
                    Batal
                </button>
            </div>
        </div>
    </div>

    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // Dipanggil saat card tagihan diklik — buka bottom sheet via Alpine custom event
        function initBayar(billId, billName, amount) {
            window.dispatchEvent(new CustomEvent('open-payment', {
                detail: { id: billId, name: billName, amount: amount }
            }));
        }

        // Dipanggil saat tombol "Bayar Sekarang" diklik di bottom sheet
        async function prosesPayment(billId, onDone) {
            try {
                const res = await fetch(`/siswa/tagihan/${billId}/pay`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept':        'application/json',
                        'Content-Type':  'application/json',
                    },
                });

                const data = await res.json();

                if (!res.ok) {
                    alert('Gagal: ' + (data.error || 'Terjadi kesalahan, coba lagi.'));
                    onDone();
                    return;
                }

                onDone(); // tutup sheet sebelum Snap terbuka

                snap.pay(data.snap_token, {
                    onSuccess: function(result) {
                        window.location.href = '/siswa/payment/success';
                    },
                    onPending: function(result) {
                        alert('Pembayaran pending. Selesaikan pembayaran sesuai instruksi yang diberikan.');
                        window.location.reload();
                    },
                    onError: function(result) {
                        alert('Pembayaran gagal: ' + (result.status_message || 'Terjadi kesalahan.'));
                        window.location.reload();
                    },
                    onClose: function() {
                        // Pengguna tutup popup tanpa bayar — tidak perlu action
                    }
                });

            } catch (e) {
                alert('Error jaringan: ' + e.message);
                onDone();
            }
        }
    </script>

</body>
</html>
