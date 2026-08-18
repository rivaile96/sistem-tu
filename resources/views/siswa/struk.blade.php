<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Pembayaran — {{ $bill->name ?? 'Tagihan' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');
        body { font-family: 'Inter', sans-serif; }

        @media print {
            .no-print { display: none !important; }
            body { background: white !important; }
            .print-card { box-shadow: none !important; border: 1px solid #e5e7eb !important; }
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen pb-10">

    {{-- ===== HEADER ===== --}}
    <div class="bg-gradient-to-r from-sky-500 via-sky-600 to-blue-700 px-4 pt-10 pb-16 no-print">
        <div class="max-w-md mx-auto flex items-center gap-3">
            <a href="{{ route('siswa.dashboard') }}"
               class="flex items-center gap-1.5 bg-white/15 hover:bg-white/25 text-white text-xs font-medium px-3 py-2 rounded-xl transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                </svg>
                Kembali
            </a>
            <div>
                <p class="text-white font-bold text-base leading-tight">Bukti Pembayaran</p>
                <p class="text-sky-200 text-xs mt-0.5">{{ $schoolName }}</p>
            </div>
        </div>
    </div>

    {{-- Print-only header (hidden on screen) --}}
    <div class="hidden print:block text-center pt-6 pb-2">
        <p class="text-lg font-bold text-gray-900">{{ $schoolName }}</p>
        @if($schoolAddress)
            <p class="text-xs text-gray-500 mt-0.5">{{ $schoolAddress }}</p>
        @endif
        <p class="text-sm font-semibold text-gray-700 mt-1">BUKTI PEMBAYARAN</p>
    </div>

    <div class="max-w-md mx-auto px-4 -mt-8 no-print-offset">

        {{-- ===== STRUK CARD ===== --}}
        <div class="print-card bg-white rounded-3xl shadow-xl shadow-gray-200/60 overflow-hidden">

            {{-- Top decorative strip --}}
            <div class="h-1.5 bg-gradient-to-r from-sky-400 via-blue-500 to-sky-600"></div>

            <div class="px-6 pt-6 pb-8">

                {{-- LUNAS Badge --}}
                <div class="flex justify-center mb-5">
                    <div class="flex items-center gap-2 bg-green-50 border-2 border-green-200 rounded-2xl px-5 py-2.5">
                        <div class="w-6 h-6 rounded-full bg-green-500 flex items-center justify-center">
                            <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <span class="text-green-700 font-bold text-base tracking-wide">LUNAS</span>
                    </div>
                </div>

                {{-- Bill Name --}}
                <p class="text-center text-gray-900 font-bold text-xl leading-snug mb-1">
                    {{ $bill->name ?? ('Tagihan ' . $bill->bill_month . '/' . $bill->bill_year) }}
                </p>

                {{-- Amount --}}
                @if($bill->original_amount && $bill->original_amount != $bill->amount)
                <div class="mb-3 text-center space-y-0.5">
                    <p class="text-gray-400 text-sm line-through">Rp {{ number_format($bill->original_amount, 0, ',', '.') }}</p>
                    <p class="text-red-500 text-xs">Diskon Rp {{ number_format($bill->discount_amount, 0, ',', '.') }}{{ $bill->discount_note ? ' — ' . $bill->discount_note : '' }}</p>
                </div>
                @endif
                <p class="text-center text-green-600 font-extrabold text-3xl mb-6">
                    Rp {{ number_format($bill->amount, 0, ',', '.') }}
                </p>

                {{-- Divider with scissors icon --}}
                <div class="flex items-center gap-3 mb-6">
                    <div class="flex-1 border-t-2 border-dashed border-gray-200"></div>
                    <svg class="w-4 h-4 text-gray-300 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M5.5 13a3.5 3.5 0 01-.369-6.98 1 1 0 00.232-1.952 3.5 3.5 0 00-2.702 5.706L.22 12.634a.75.75 0 001.06 1.06l2.5-2.5a3.5 3.5 0 002.72-3.694zM5.5 11a2 2 0 100-4 2 2 0 000 4zM14.5 7a3.5 3.5 0 01.369 6.98 1 1 0 00-.232 1.952 3.5 3.5 0 002.702-5.706l2.441-2.88a.75.75 0 00-1.06-1.06l-2.5 2.5A3.5 3.5 0 0114.5 7zm0 4a2 2 0 100-4 2 2 0 000 4zM10 12a.75.75 0 01.75.75v2.5a.75.75 0 01-1.5 0v-2.5A.75.75 0 0110 12zM10 9.25a.75.75 0 01-.75-.75v-2.5a.75.75 0 011.5 0v2.5a.75.75 0 01-.75.75z"/>
                    </svg>
                    <div class="flex-1 border-t-2 border-dashed border-gray-200"></div>
                </div>

                {{-- Info Rows --}}
                <div class="space-y-3">

                    {{-- Nama Siswa --}}
                    <div class="flex items-start justify-between gap-4">
                        <span class="text-xs text-gray-400 shrink-0 pt-0.5 w-28">Nama Siswa</span>
                        <span class="text-sm font-semibold text-gray-800 text-right">{{ $student->name }}</span>
                    </div>

                    {{-- NIS --}}
                    <div class="flex items-start justify-between gap-4">
                        <span class="text-xs text-gray-400 shrink-0 pt-0.5 w-28">NIS</span>
                        <span class="text-sm font-semibold text-gray-800 text-right">{{ $student->nis }}</span>
                    </div>

                    {{-- Kelas --}}
                    @if($student->kelas_id)
                    <div class="flex items-start justify-between gap-4">
                        <span class="text-xs text-gray-400 shrink-0 pt-0.5 w-28">Kelas</span>
                        <span class="text-sm font-semibold text-gray-800 text-right">{{ optional($student->kelas)->nama_kelas ?? '-' }}</span>
                    </div>
                    @endif

                    {{-- Divider --}}
                    <div class="border-t border-gray-100 my-1"></div>

                    {{-- Metode Bayar --}}
                    <div class="flex items-start justify-between gap-4">
                        <span class="text-xs text-gray-400 shrink-0 pt-0.5 w-28">Metode Bayar</span>
                        <span class="text-sm font-semibold text-gray-800 text-right capitalize">
                            {{ $bill->payment_method ?? 'Cash' }}
                        </span>
                    </div>

                    {{-- Tanggal Bayar --}}
                    <div class="flex items-start justify-between gap-4">
                        <span class="text-xs text-gray-400 shrink-0 pt-0.5 w-28">Tanggal Bayar</span>
                        <span class="text-sm font-semibold text-gray-800 text-right">
                            {{-- Phase 2.4: paid_at is the canonical payment timestamp. --}}
                @if($bill->paid_at)
                    {{ \Carbon\Carbon::parse($bill->paid_at)->locale('id')->isoFormat('D MMMM YYYY, HH:mm') }}
                @else
                    <span class="italic text-gray-400">Tanggal pembayaran tidak tersedia</span>
                @endif
                        </span>
                    </div>

                    {{-- No. Transaksi --}}
                    <div class="flex items-start justify-between gap-4">
                        <span class="text-xs text-gray-400 shrink-0 pt-0.5 w-28">No. Transaksi</span>
                        <span class="text-xs font-mono font-semibold text-gray-600 text-right break-all">
                            {{ $bill->payment_token ?? 'CASH-' . $bill->id }}
                        </span>
                    </div>

                </div>

                {{-- Bottom divider --}}
                <div class="border-t-2 border-dashed border-gray-200 mt-6 mb-4"></div>

                {{-- Footer text --}}
                <p class="text-center text-xs text-gray-400 leading-relaxed">
                    {{ $schoolName }}<br>
                    Terima kasih atas pembayaran Anda.
                </p>

            </div>
        </div>

        {{-- ===== ACTION BUTTONS ===== --}}
        <div class="mt-5 flex flex-col gap-3 no-print">

            {{-- Cetak --}}
            <button onclick="window.print()"
                    class="w-full flex items-center justify-center gap-2 bg-sky-600 hover:bg-sky-700 active:bg-sky-800 text-white font-semibold text-sm py-3.5 rounded-2xl transition-colors shadow-md shadow-sky-200">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2M6 14h12v8H6v-8z"/>
                </svg>
                Cetak / Screenshot
            </button>

            {{-- Kembali ke Dashboard --}}
            <a href="{{ route('siswa.dashboard') }}"
               class="w-full flex items-center justify-center gap-2 bg-white hover:bg-gray-50 active:bg-gray-100 text-gray-700 font-semibold text-sm py-3.5 rounded-2xl transition-colors border border-gray-200">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                </svg>
                Kembali ke Dashboard
            </a>

        </div>

    </div>

</body>
</html>
