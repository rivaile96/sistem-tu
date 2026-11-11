<x-app-layout>
    <x-slot name="header">
        Command Center
    </x-slot>

    <!-- ApexCharts CDN -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <div class="space-y-6">
        
        <!-- 1. WELCOME HERO CARD (SPMB MATCHING) -->
        <div class="flex flex-col md:flex-row items-center justify-between p-6 bg-white border shadow-sm rounded-2xl border-slate-100 gap-4">
            <div class="flex items-center gap-4">
                <div class="flex items-center justify-center w-12 h-12 bg-blue-50 text-[#178BC1] font-black rounded-xl text-2xl border border-blue-100 shrink-0">
                    K
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-slate-800">
                        Halo, {{ Auth::user()->name ? explode(' ', Auth::user()->name)[0] : 'Admin' }}!
                    </h1>
                    <p class="mt-1 text-xs text-slate-500 font-medium">
                        Ringkasan arus kas keuangan sekolah, tagihan siswa, dan transaksi harian Kaffah Islamic School.
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    Sistem Berjalan Normal
                </span>
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-blue-50 text-[#178BC1] border border-blue-200">
                    DOKU Sandbox Ready
                </span>
            </div>
        </div>

        <!-- 2. STAT CARDS (SOLID SPMB DESIGN - NO GRADIENTS) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            
            <!-- CARD 1: TOTAL PEMASUKAN HARI INI -->
            <div class="p-6 bg-white border shadow-sm rounded-2xl border-slate-100 hover:border-slate-200 transition-all">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Pemasukan</p>
                        <h3 class="text-2xl font-bold text-slate-800 mt-1">
                            Rp {{ number_format($totalIncomeToday, 0, ',', '.') }}
                        </h3>
                        <p class="text-xs text-slate-500 mt-1 font-medium">Hari ini (Kasir POS + Tagihan)</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-blue-50 text-[#178BC1] flex items-center justify-center shrink-0 border border-blue-100">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <line x1="12" y1="1" x2="12" y2="23"></line>
                            <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- CARD 2: PEMASUKAN TUNAI (CASH) -->
            <div class="p-6 bg-white border shadow-sm rounded-2xl border-slate-100 hover:border-slate-200 transition-all">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Tunai (Cash)</p>
                        <h3 class="text-2xl font-bold text-emerald-600 mt-1">
                            Rp {{ number_format($totalCashToday, 0, ',', '.') }}
                        </h3>
                        <p class="text-xs text-slate-500 mt-1 font-medium">Laci Kasir TU & Kantin</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0 border border-emerald-100">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <rect x="2" y="6" width="20" height="12" rx="2"></rect>
                            <circle cx="12" cy="12" r="2"></circle>
                            <path d="M6 12h.01M18 12h.01"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- CARD 3: ONLINE (DOKU GATEWAY) -->
            <div class="p-6 bg-white border shadow-sm rounded-2xl border-slate-100 hover:border-slate-200 transition-all">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Online Gateway</p>
                        <h3 class="text-2xl font-bold text-blue-600 mt-1">
                            Rp {{ number_format($billMidtransToday, 0, ',', '.') }}
                        </h3>
                        <p class="text-xs text-slate-500 mt-1 font-medium">DOKU Direct API & VA</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center shrink-0 border border-sky-100">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <rect x="2" y="5" width="20" height="14" rx="2"></rect>
                            <line x1="2" y1="10" x2="22" y2="10"></line>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- CARD 4: SISWA BELUM BAYAR -->
            <div class="p-6 bg-white border shadow-sm rounded-2xl border-slate-100 hover:border-slate-200 transition-all">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Tagihan Tertunda</p>
                        <h3 class="text-2xl font-bold text-amber-600 mt-1">
                            {{ $unpaidStudents }} <span class="text-sm font-normal text-slate-500">Siswa</span>
                        </h3>
                        <p class="text-xs text-slate-500 mt-1 font-medium">Perlu tindak lanjut tagihan</p>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0 border border-amber-100">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                            <line x1="12" y1="9" x2="12" y2="13"></line>
                            <line x1="12" y1="17" x2="12.01" y2="17"></line>
                        </svg>
                    </div>
                </div>
            </div>

        </div>

        <!-- 3. GRAFIK KEUANGAN (SPMB COLOR SCHEME #178BC1) -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- AREA CHART: 7 HARI TERAKHIR -->
            <div class="lg:col-span-2 p-6 bg-white border shadow-sm rounded-2xl border-slate-100">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-2">
                        <div class="p-2 bg-blue-50 text-[#178BC1] rounded-xl">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline>
                                <polyline points="17 6 23 6 23 12"></polyline>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-800">Tren Pemasukan 7 Hari Terakhir</h3>
                            <p class="text-xs text-slate-400">Total pemasukan harian dari seluruh kanal</p>
                        </div>
                    </div>
                    <span class="text-xs font-bold text-[#178BC1] bg-blue-50 px-2.5 py-1 rounded-lg border border-blue-100">
                        Statistik 7 Hari
                    </span>
                </div>
                <div id="weeklyChart" class="w-full h-72"></div>
            </div>

            <!-- DONUT CHART: KOMPOSISI PEMBAYARAN BULAN INI -->
            <div class="p-6 bg-white border shadow-sm rounded-2xl border-slate-100 flex flex-col justify-between">
                <div>
                    <div class="flex items-center gap-2 mb-6">
                        <div class="p-2 bg-blue-50 text-[#178BC1] rounded-xl">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="10"></circle>
                                <path d="M12 2a10 10 0 0 1 10 10h-10z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-800">Metode Pembayaran</h3>
                            <p class="text-xs text-slate-400">Komposisi kanal bulan ini</p>
                        </div>
                    </div>
                    <div id="paymentMethodChart" class="w-full flex items-center justify-center"></div>
                </div>

                <div class="mt-4 pt-4 border-t border-slate-100 grid grid-cols-2 gap-2 text-center">
                    <div class="p-2 rounded-xl bg-slate-50">
                        <p class="text-[10px] uppercase font-bold text-slate-400">Tunai (Cash)</p>
                        <p class="text-xs font-bold text-slate-800 mt-0.5">Rp {{ number_format($monthlyCash, 0, ',', '.') }}</p>
                    </div>
                    <div class="p-2 rounded-xl bg-slate-50">
                        <p class="text-[10px] uppercase font-bold text-slate-400">Online Gateway</p>
                        <p class="text-xs font-bold text-slate-800 mt-0.5">Rp {{ number_format($monthlyMidtrans, 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>

        </div>

        <!-- 4. BOTTOM GRID: LIVE ACTIVITY FEED & LOW STOCK ALERTS -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            
            <!-- AKTIVITAS TRANSAKSI TERBARU -->
            <div class="bg-white border shadow-sm rounded-2xl border-slate-100 overflow-hidden">
                <div class="p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-[#178BC1]" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polyline points="12 6 12 12 16 14"></polyline>
                        </svg>
                        <h3 class="font-bold text-slate-800 text-sm">Aktivitas Transaksi Terbaru</h3>
                    </div>
                    <a href="{{ route('bills.index') }}" class="text-xs text-[#178BC1] hover:underline font-bold">
                        Lihat Semua Tagihan →
                    </a>
                </div>

                <div class="divide-y divide-slate-100">
                    @forelse($recentActivities as $act)
                        <div class="p-4 flex items-center justify-between hover:bg-slate-50/60 transition-colors">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 {{ $act['type'] === 'POS' ? 'bg-emerald-50 text-emerald-600 border border-emerald-100' : 'bg-blue-50 text-[#178BC1] border border-blue-100' }}">
                                    @if($act['type'] === 'POS')
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                            <circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                                        </svg>
                                    @else
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                            <rect x="2" y="5" width="20" height="14" rx="2"></rect><line x1="2" y1="10" x2="22" y2="10"></line>
                                        </svg>
                                    @endif
                                </div>
                                <div>
                                    <p class="text-xs font-bold text-slate-800">{{ $act['desc'] }}</p>
                                    <div class="flex items-center gap-2 mt-0.5">
                                        <span class="text-[10px] text-slate-400 font-medium">{{ \Carbon\Carbon::parse($act['time'])->diffForHumans() }}</span>
                                        <span class="text-[10px] px-1.5 py-0.5 rounded font-bold uppercase {{ str_contains($act['method'], 'Online') ? 'bg-sky-50 text-sky-700 border border-sky-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200' }}">
                                            {{ $act['method'] }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-xs font-bold text-slate-800">Rp {{ number_format($act['amount'], 0, ',', '.') }}</p>
                                <span class="text-[10px] font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-200 inline-block mt-0.5">
                                    LUNAS
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center text-xs text-slate-400">
                            Belum ada aktivitas transaksi yang tercatat hari ini.
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- PERINGATAN STOK KANTIN MENIPIS -->
            <div class="bg-white border shadow-sm rounded-2xl border-slate-100 overflow-hidden">
                <div class="p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                            <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                            <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                            <line x1="12" y1="22.08" x2="12" y2="12"></line>
                        </svg>
                        <h3 class="font-bold text-slate-800 text-sm">Stok Kantin Menipis</h3>
                    </div>
                    <a href="{{ route('pos.items.index') }}" class="text-xs text-[#178BC1] hover:underline font-bold">
                        Master Barang →
                    </a>
                </div>

                <div class="divide-y divide-slate-100">
                    @forelse($lowStockItems as $item)
                        <div class="p-4 flex items-center justify-between hover:bg-slate-50/60 transition-colors">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 border border-amber-100 flex items-center justify-center shrink-0 font-bold text-xs">
                                    {{ substr($item->name, 0, 1) }}
                                </div>
                                <div>
                                    <p class="text-xs font-bold text-slate-800">{{ $item->name }}</p>
                                    <p class="text-[10px] text-slate-400 mt-0.5">Rp {{ number_format($item->price, 0, ',', '.') }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="px-2.5 py-1 text-xs font-bold rounded-lg {{ $item->stock <= 2 ? 'bg-rose-50 text-rose-700 border border-rose-200' : 'bg-amber-50 text-amber-700 border border-amber-200' }}">
                                    Sisa {{ $item->stock }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center text-xs text-slate-400">
                            <div class="flex flex-col items-center gap-2">
                                <svg class="w-8 h-8 text-emerald-400" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24">
                                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                                </svg>
                                <span class="font-bold text-slate-600">Semua stok barang aman dan terkendali.</span>
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>

        </div>

    </div>

    <!-- CHARTS INITIALIZATION SCRIPT (FIGTREE FONT & SPMB #178BC1) -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            
            // 1. AREA CHART (7 HARI TERAKHIR)
            var weeklyOptions = {
                series: [{
                    name: 'Total Pemasukan',
                    data: [
                        @foreach($chartData as $data)
                            {{ $data['total'] }},
                        @endforeach
                    ]
                }],
                chart: {
                    type: 'area',
                    height: 280,
                    fontFamily: 'Figtree, sans-serif',
                    toolbar: { show: false },
                    animations: { enabled: true, easing: 'easeinout', speed: 600 }
                },
                colors: ['#178BC1'],
                fill: {
                    type: 'solid',
                    opacity: 0.1
                },
                dataLabels: { enabled: false },
                stroke: { curve: 'smooth', width: 2.5 },
                xaxis: {
                    categories: [
                        @foreach($chartData as $data)
                            "{{ $data['day'] }}",
                        @endforeach
                    ],
                    axisBorder: { show: false },
                    axisTicks: { show: false },
                    labels: {
                        style: {
                            colors: '#94a3b8',
                            fontSize: '11px',
                            fontWeight: 600
                        }
                    }
                },
                yaxis: {
                    labels: {
                        style: {
                            colors: '#94a3b8',
                            fontSize: '11px'
                        },
                        formatter: function (value) {
                            return "Rp " + new Intl.NumberFormat('id-ID').format(value);
                        }
                    }
                },
                tooltip: {
                    theme: 'dark',
                    y: {
                        formatter: function (value) {
                            return "Rp " + new Intl.NumberFormat('id-ID').format(value);
                        }
                    }
                },
                grid: {
                    borderColor: '#f1f5f9',
                    strokeDashArray: 3,
                }
            };
            var weeklyChart = new ApexCharts(document.querySelector("#weeklyChart"), weeklyOptions);
            weeklyChart.render();


            // 2. DONUT CHART (CASH VS ONLINE)
            var totalCash = {{ $monthlyCash }};
            var totalMidtrans = {{ $monthlyMidtrans }};
            
            if(totalCash === 0 && totalMidtrans === 0) {
                totalCash = 1; // Visual placeholder jika kosong
            }

            var pieOptions = {
                series: [totalCash, totalMidtrans],
                labels: ['Tunai (Cash)', 'Online Gateway'],
                chart: {
                    type: 'donut',
                    height: 220,
                    fontFamily: 'Figtree, sans-serif',
                },
                colors: ['#178BC1', '#0B93D8'],
                plotOptions: {
                    pie: {
                        donut: {
                            size: '70%',
                            labels: {
                                show: true,
                                name: {
                                    fontSize: '11px',
                                    fontWeight: 600,
                                    color: '#64748b'
                                },
                                value: {
                                    fontSize: '14px',
                                    fontWeight: 700,
                                    color: '#1e293b',
                                    formatter: function (val) {
                                        return "Rp " + new Intl.NumberFormat('id-ID', { notation: "compact" }).format(val);
                                    }
                                },
                                total: {
                                    show: true,
                                    label: 'Total Bulan Ini',
                                    fontSize: '10px',
                                    fontWeight: 600,
                                    color: '#94a3b8',
                                    formatter: function (w) {
                                        let total = w.globals.seriesTotals.reduce((a, b) => a + b, 0);
                                        return "Rp " + new Intl.NumberFormat('id-ID', { notation: "compact" }).format(total);
                                    }
                                }
                            }
                        }
                    }
                },
                dataLabels: { enabled: false },
                legend: { 
                    position: 'bottom',
                    fontSize: '11px',
                    fontWeight: 600,
                    labels: { colors: '#64748b' }
                },
                tooltip: {
                    theme: 'dark',
                    y: {
                        formatter: function (value) {
                            return "Rp " + new Intl.NumberFormat('id-ID').format(value);
                        }
                    }
                }
            };
            var pieChart = new ApexCharts(document.querySelector("#paymentMethodChart"), pieOptions);
            pieChart.render();
        });
    </script>
</x-app-layout>
