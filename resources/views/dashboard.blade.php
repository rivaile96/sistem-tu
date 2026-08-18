<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Executive Dashboard') }}
        </h2>
    </x-slot>

    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    {{-- BUG FIX: div wrapper sebelumnya langsung ditutup (</div>) sebelum konten --}}
    <div class="bg-gray-50/50 space-y-8">

        {{-- ── Kartu Summary 4 kolom ── --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">

            {{-- Total Income Hari Ini --}}
            <div class="bg-gradient-to-br from-[#38bdf8] via-[#0ea5e9] to-[#0284c7] p-6 rounded-2xl text-white shadow-xl shadow-sky-200 relative overflow-hidden group">
                <div class="relative z-10">
                    <div class="flex items-center justify-between mb-4">
                        <div class="p-3 bg-white/10 rounded-xl backdrop-blur-sm">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <span class="text-xs font-medium bg-white/20 px-2 py-1 rounded text-sky-50">Total Income</span>
                    </div>
                    <h3 class="text-3xl font-extrabold tracking-tight">Rp {{ number_format($totalIncomeToday, 0, ',', '.') }}</h3>
                    <p class="text-sm text-sky-200 mt-1 flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                        Hari ini
                    </p>
                </div>
                <div class="absolute -bottom-4 -right-4 w-24 h-24 bg-white/10 rounded-full group-hover:scale-150 transition-transform duration-500"></div>
            </div>

            {{-- Tunai (Cash) --}}
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 hover:shadow-lg transition-shadow duration-300">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-emerald-50 rounded-xl">
                        <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    </div>
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Tunai (Cash)</span>
                </div>
                <h3 class="text-2xl font-bold text-gray-800">Rp {{ number_format($totalCashToday, 0, ',', '.') }}</h3>
                <p class="text-xs text-gray-500 mt-2">Dari Laci Kasir (TU + POS)</p>
            </div>

            {{-- Online / Midtrans --}}
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 hover:shadow-lg transition-shadow duration-300">
                <div class="flex items-center justify-between mb-4">
                    <div class="p-3 bg-blue-50 rounded-xl">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                    </div>
                    <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">Online (App)</span>
                </div>
                <h3 class="text-2xl font-bold text-gray-800">Rp {{ number_format($billMidtransToday, 0, ',', '.') }}</h3>
                <p class="text-xs text-gray-500 mt-2">Masuk via Payment Gateway</p>
            </div>

            {{-- Tagihan Macet --}}
            <div class="bg-gradient-to-br from-rose-500 to-rose-600 p-6 rounded-2xl text-white shadow-lg shadow-rose-200 relative overflow-hidden">
                <div class="relative z-10">
                    <div class="flex items-center justify-between mb-2">
                        <span class="font-bold text-rose-100 text-sm">Tagihan Macet</span>
                        @if($unpaidStudents > 0)
                        <div class="bg-white/20 px-2 py-1 rounded text-xs font-bold animate-pulse">Action Needed</div>
                        @endif
                    </div>
                    <h3 class="text-3xl font-bold">{{ $unpaidStudents }} <span class="text-lg font-normal opacity-80">Siswa</span></h3>
                    <p class="text-sm text-rose-100 mt-1">Belum Lunas</p>
                </div>
                <div class="absolute -right-2 -bottom-4 opacity-20 transform rotate-12">
                    <svg class="w-24 h-24" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
                </div>
            </div>

        </div>

        {{-- ── Konten Utama ── --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

            {{-- Kolom kiri: Chart + Live Feed --}}
            <div class="lg:col-span-2 space-y-8">

                {{-- Grafik Mingguan --}}
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                    <div class="flex justify-between items-center mb-6">
                        <div>
                            <h3 class="text-lg font-bold text-gray-800">Tren Pemasukan Mingguan</h3>
                            <p class="text-sm text-gray-400">7 Hari Terakhir</p>
                        </div>
                    </div>
                    <div id="weeklyChart" class="w-full h-80"></div>
                </div>

                {{-- Live Feed Transaksi --}}
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
                        <h3 class="font-bold text-gray-800 flex items-center gap-2">
                            <span class="relative flex h-3 w-3">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
                            </span>
                            Live Feed Transaksi
                        </h3>
                        {{-- BUG FIX: link "Lihat Semua" sebelumnya href="#" --}}
                        <a href="{{ route('pos.history.index') }}" class="text-xs text-blue-600 hover:text-blue-800 font-medium">Lihat Semua →</a>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="bg-gray-50 text-gray-500 font-medium border-b border-gray-100">
                                <tr>
                                    <th class="px-6 py-3">Waktu</th>
                                    <th class="px-6 py-3">Keterangan</th>
                                    <th class="px-6 py-3">Metode</th>
                                    <th class="px-6 py-3 text-right">Nominal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @forelse($recentActivities as $log)
                                <tr class="hover:bg-blue-50/30 transition group">
                                    <td class="px-6 py-4 text-gray-500 whitespace-nowrap">
                                        {{-- BUG FIX: null-safe — $log['time'] sudah difilter di controller --}}
                                        {{ $log['time']?->format('H:i') ?? '-' }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="font-medium text-gray-800">{{ $log['desc'] }}</div>
                                        <div class="text-xs text-gray-400">{{ $log['type'] }} Transaction</div>
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($log['method'] === 'Online (App)')
                                            <span class="inline-flex items-center gap-1 bg-blue-100 text-blue-700 px-2 py-1 rounded text-xs font-bold border border-blue-200">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                                                Online
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 bg-emerald-100 text-emerald-700 px-2 py-1 rounded text-xs font-bold border border-emerald-200">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                                Cash
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 font-bold text-gray-700 text-right group-hover:text-blue-600 transition">
                                        Rp {{ number_format($log['amount'], 0, ',', '.') }}
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-8 text-center text-gray-400">Belum ada aktivitas hari ini.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

            {{-- Kolom kanan: Pie Chart + Stok Menipis --}}
            <div class="space-y-6">

                {{-- Analisa Metode Bayar --}}
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                    <h3 class="font-bold text-gray-800 mb-1">Analisa Metode Bayar (Bulan Ini)</h3>
                    {{-- BUG FIX: label diperjelas — mencakup POS + SPP --}}
                    <p class="text-xs text-gray-400 mb-4">Tagihan Sekolah + Kantin</p>
                    <div id="paymentMethodChart" class="w-full flex justify-center"></div>
                    <div class="mt-4 space-y-2">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Tunai (Cash + POS):</span>
                            <span class="font-bold text-gray-800">Rp {{ number_format($monthlyCashTotal, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500">Online (Midtrans):</span>
                            <span class="font-bold text-gray-800">Rp {{ number_format($monthlyMidtrans, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>

                {{-- Stok Menipis --}}
                <div class="bg-gradient-to-br from-orange-50 to-white p-6 rounded-2xl shadow-sm border border-orange-100">
                    <h3 class="font-bold text-orange-800 mb-4 flex items-center gap-2">
                        <div class="bg-orange-100 p-1.5 rounded-lg">
                            <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        </div>
                        Stok Menipis!
                    </h3>
                    <div class="space-y-3">
                        @forelse($lowStockItems as $item)
                            <div class="flex items-center gap-3 p-3 bg-white rounded-xl shadow-sm border border-gray-100 hover:border-orange-200 transition">
                                <div class="w-2 h-2 rounded-full {{ $item->stock <= 0 ? 'bg-red-500' : 'bg-orange-500 animate-pulse' }}"></div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-bold text-gray-700 truncate">{{ $item->name }}</p>
                                    <p class="text-xs {{ $item->stock <= 0 ? 'text-red-500' : 'text-orange-500' }} font-medium">
                                        {{ $item->stock <= 0 ? 'Habis!' : 'Sisa: ' . $item->stock . ' pcs' }}
                                    </p>
                                </div>
                                <a href="{{ route('pos.items.edit', $item->id) }}"
                                   class="text-xs bg-orange-50 text-orange-600 px-2 py-1 rounded hover:bg-orange-100 transition flex-shrink-0">
                                    Restock
                                </a>
                            </div>
                        @empty
                            <div class="text-center py-4">
                                <svg class="w-10 h-10 text-green-200 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <p class="text-sm text-gray-500">Stok Aman Terkendali!</p>
                            </div>
                        @endforelse
                    </div>
                </div>

            </div>
        </div>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {

            // 1. GRAFIK MINGGUAN (AREA CHART)
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
                    height: 320,
                    fontFamily: 'Inter, sans-serif',
                    toolbar: { show: false },
                    animations: { enabled: true, easing: 'easeinout', speed: 800 }
                },
                colors: ['#0ea5e9'],
                fill: {
                    type: 'gradient',
                    gradient: { shadeIntensity: 1, opacityFrom: 0.7, opacityTo: 0.2, stops: [0, 90, 100] }
                },
                dataLabels: { enabled: false },
                stroke: { curve: 'smooth', width: 3 },
                xaxis: {
                    categories: [
                        @foreach($chartData as $data)
                            "{{ $data['day'] }}",
                        @endforeach
                    ],
                    axisBorder: { show: false },
                    axisTicks: { show: false }
                },
                yaxis: {
                    labels: {
                        formatter: function (value) {
                            return "Rp " + new Intl.NumberFormat('id-ID', { notation: 'compact' }).format(value);
                        }
                    }
                },
                tooltip: {
                    y: {
                        formatter: function (value) {
                            return "Rp " + new Intl.NumberFormat('id-ID').format(value);
                        }
                    }
                },
                grid: { borderColor: '#f3f4f6', strokeDashArray: 4 }
            };
            var weeklyChart = new ApexCharts(document.querySelector("#weeklyChart"), weeklyOptions);
            weeklyChart.render();

            // 2. GRAFIK PIE — BUG FIX: pakai monthlyCashTotal (sudah termasuk POS)
            var totalCash     = {{ $monthlyCashTotal }};
            var totalMidtrans = {{ $monthlyMidtrans }};

            // Kalau dua-duanya 0, dummy biar tidak error render
            if (totalCash === 0 && totalMidtrans === 0) {
                totalCash = 1;
            }

            var pieOptions = {
                series: [totalCash, totalMidtrans],
                labels: ['Tunai (Cash + POS)', 'Online (Midtrans)'],
                chart: {
                    type: 'donut',
                    height: 250,
                    fontFamily: 'Inter, sans-serif',
                },
                colors: ['#0ea5e9', '#3b82f6'],
                plotOptions: {
                    pie: {
                        donut: {
                            size: '65%',
                            labels: {
                                show: true,
                                total: {
                                    show: true,
                                    label: 'Total',
                                    formatter: function (w) {
                                        let total = w.globals.seriesTotals.reduce((a, b) => a + b, 0);
                                        // Kurangi dummy 1 kalau keduanya 0 aslinya
                                        if ({{ $monthlyCashTotal }} === 0 && {{ $monthlyMidtrans }} === 0) total = 0;
                                        return "Rp " + new Intl.NumberFormat('id-ID', { notation: 'compact' }).format(total);
                                    }
                                }
                            }
                        }
                    }
                },
                dataLabels: { enabled: false },
                legend: { position: 'bottom' },
                tooltip: {
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
