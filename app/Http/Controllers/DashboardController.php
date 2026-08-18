<?php

namespace App\Http\Controllers;

use App\Models\PosOrder;
use App\Models\PosItem;
use App\Models\StudentBill;
use App\Models\Student;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // ==========================================================
        // 1. DATA KEUANGAN HARI INI
        // ==========================================================

        $posToday = PosOrder::whereDate('created_at', today())
                            ->where('payment_status', 'PAID')
                            ->sum('payment_amount');

        $billCashToday = StudentBill::whereDate('paid_at', today())
                                    ->whereNotNull('paid_at')
                                    ->where('status', 'PAID')
                                    ->where('payment_method', 'CASH')
                                    ->sum('amount');

        $billMidtransToday = StudentBill::whereDate('paid_at', today())
                                        ->whereNotNull('paid_at')
                                        ->where('status', 'PAID')
                                        ->where('payment_method', 'MIDTRANS')
                                        ->sum('amount');

        $totalIncomeToday = $posToday + $billCashToday + $billMidtransToday;
        $totalCashToday   = $posToday + $billCashToday;

        // ==========================================================
        // 2. DATA KEUANGAN BULAN INI
        // ==========================================================

        $monthlyCash = StudentBill::whereMonth('paid_at', now()->month)
                                  ->whereYear('paid_at', now()->year)
                                  ->whereNotNull('paid_at')
                                  ->where('status', 'PAID')
                                  ->where('payment_method', 'CASH')
                                  ->sum('amount');

        $monthlyMidtrans = StudentBill::whereMonth('paid_at', now()->month)
                                      ->whereYear('paid_at', now()->year)
                                      ->whereNotNull('paid_at')
                                      ->where('status', 'PAID')
                                      ->where('payment_method', 'MIDTRANS')
                                      ->sum('amount');

        // Tambahkan POS bulan ini ke cash (BUG FIX: pie chart sebelumnya tidak termasuk POS)
        $monthlyPos = PosOrder::whereMonth('created_at', now()->month)
                              ->whereYear('created_at', now()->year)
                              ->where('payment_status', 'PAID')
                              ->sum('payment_amount');

        $monthlyCashTotal = $monthlyCash + $monthlyPos;

        // ==========================================================
        // 3. OPERATIONAL ALERTS
        // ==========================================================

        $unpaidStudents = Student::whereHas('bills', function ($q) {
            $q->where('status', 'UNPAID');
        })->count();

        $lowStockItems = PosItem::where('stock', '<=', 5)
                                ->orderBy('stock', 'asc')
                                ->limit(5)
                                ->get();

        // ==========================================================
        // 4. GRAFIK 7 HARI TERAKHIR
        // ==========================================================

        $chartData = [];
        $days = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);

            $pos = PosOrder::whereDate('created_at', $date)
                           ->where('payment_status', 'PAID')
                           ->sum('payment_amount');

            $bill = StudentBill::whereDate('paid_at', $date)
                               ->whereNotNull('paid_at')
                               ->where('status', 'PAID')
                               ->sum('amount');

            // BUG FIX: label hari bahasa Indonesia
            $chartData[] = [
                'day'   => $days[$date->dayOfWeek],
                'total' => $pos + $bill,
            ];
        }

        // ==========================================================
        // 5. LIVE FEED — hanya transaksi PAID
        // ==========================================================

        // BUG FIX: tambah filter payment_status = PAID agar hutang tidak masuk feed
        $latestPos = PosOrder::with('user')
                             ->where('payment_status', 'PAID')
                             ->latest()
                             ->limit(5)
                             ->get()
                             ->map(fn ($item) => [
                                 'time'   => $item->created_at,
                                 'desc'   => 'Jajan Kantin #' . $item->transaction_code,
                                 'amount' => $item->payment_amount,
                                 'type'   => 'POS',
                                 'method' => 'Cash',
                             ]);

        $latestBill = StudentBill::with('student')
                                 ->where('status', 'PAID')
                                 ->whereNotNull('paid_at')
                                 ->latest('paid_at')
                                 ->limit(5)
                                 ->get()
                                 ->map(fn ($item) => [
                                     'time'   => $item->paid_at,
                                     'desc'   => 'Bayar Tagihan - ' . optional($item->student)->name,
                                     'amount' => $item->amount,
                                     'type'   => 'BILL',
                                     'method' => $item->payment_method === 'MIDTRANS' ? 'Online (App)' : 'Tunai (TU)',
                                 ]);

        $recentActivities = collect($latestPos)
            ->merge($latestBill)
            ->filter(fn ($log) => $log['time'] !== null) // BUG FIX: null-safe
            ->sortByDesc('time')
            ->take(5)
            ->values();

        // ==========================================================
        // RETURN VIEW
        // ==========================================================
        return view('dashboard', compact(
            'totalIncomeToday',
            'billMidtransToday',
            'totalCashToday',
            'monthlyCash',
            'monthlyCashTotal',
            'monthlyMidtrans',
            'monthlyPos',
            'unpaidStudents',
            'lowStockItems',
            'chartData',
            'recentActivities'
        ));
    }
}
