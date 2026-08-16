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
        // 1. DATA KEUANGAN HARI INI (Detailed Flow)
        // ==========================================================

        // A. Pemasukan dari Kantin/POS — uses created_at (POS transaction lifecycle)
        $posToday = PosOrder::whereDate('created_at', today())
                            ->where('payment_status', 'PAID')
                            ->sum('payment_amount');

        // B. Pemasukan Tagihan Sekolah - TUNAI
        // Phase 2.4: changed from updated_at → paid_at (canonical payment timestamp).
        // whereNotNull('paid_at') excludes historical PAID records with no known payment time.
        $billCashToday = StudentBill::whereDate('paid_at', today())
                                    ->whereNotNull('paid_at')
                                    ->where('status', 'PAID')
                                    ->where('payment_method', 'CASH')
                                    ->sum('amount');

        // C. Pemasukan Tagihan Sekolah - MIDTRANS (Payment Gateway)
        $billMidtransToday = StudentBill::whereDate('paid_at', today())
                                        ->whereNotNull('paid_at')
                                        ->where('status', 'PAID')
                                        ->where('payment_method', 'MIDTRANS')
                                        ->sum('amount');

        // TOTAL SEMUA (Untuk Card Utama)
        $totalIncomeToday = $posToday + $billCashToday + $billMidtransToday;

        // TOTAL CASH ONLY (POS + SPP Manual)
        $totalCashToday = $posToday + $billCashToday;

        // ==========================================================
        // 2. DATA KEUANGAN BULAN INI (Analisa Arus Uang)
        // ==========================================================

        // Phase 2.4: changed from updated_at → paid_at.
        // Records with paid_at = NULL are excluded (unknown payment timestamp).
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

        // ==========================================================
        // 3. OPERATIONAL ALERTS
        // ==========================================================

        // Siswa Nunggak (Yang punya minimal 1 tagihan UNPAID)
        $unpaidStudents = Student::whereHas('bills', function($q) {
            $q->where('status', 'UNPAID');
        })->count();

        // Stok Kantin Menipis (Kurang dari 5)
        $lowStockItems = PosItem::where('stock', '<=', 5)
                                ->orderBy('stock', 'asc')
                                ->limit(5)
                                ->get();

        // ==========================================================
        // 4. GRAFIK 7 HARI TERAKHIR
        // ==========================================================
        $chartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);

            // Pemasukan POS — uses created_at (POS transaction lifecycle, not changed)
            $pos = PosOrder::whereDate('created_at', $date)
                           ->where('payment_status', 'PAID')
                           ->sum('payment_amount');

            // Pemasukan Bill — Phase 2.4: changed from updated_at → paid_at.
            // whereNotNull excludes historical records with unknown payment date.
            $bill = StudentBill::whereDate('paid_at', $date)
                               ->whereNotNull('paid_at')
                               ->where('status', 'PAID')
                               ->sum('amount');

            $chartData[] = [
                'day'   => $date->format('D'), // Mon, Tue, Wed...
                'total' => $pos + $bill
            ];
        }
        // Skala Grafik (Max Value)
        $maxIncome = collect($chartData)->max('total') ?: 1;

        // ==========================================================
        // 5. LOG AKTIVITAS TERBARU (Live Feed)
        // ==========================================================

        // Feed POS — uses created_at (correct: POS transaction timestamp)
        $latestPos = PosOrder::with('user')->latest()->limit(5)->get()->map(function($item) {
            return [
                'time'   => $item->created_at,
                'desc'   => 'Jajan Kantin #' . $item->id,
                'amount' => $item->total_amount,
                'type'   => 'POS',
                'method' => 'Cash' // POS default Cash
            ];
        });

        // Feed Bill — Phase 2.4: order by paid_at, display paid_at.
        // Only includes records with a known paid_at (whereNotNull).
        // Historical PAID records with paid_at = NULL are excluded from the live feed
        // because their actual payment time is unknown and fabricating it is incorrect.
        $latestBill = StudentBill::with('student')
                                 ->where('status', 'PAID')
                                 ->whereNotNull('paid_at')
                                 ->latest('paid_at')
                                 ->limit(5)
                                 ->get()
                                 ->map(function($item) {
                                     $methodLabel = $item->payment_method == 'MIDTRANS'
                                         ? 'Online (App)'
                                         : 'Tunai (TU)';
                                     return [
                                         'time'   => $item->paid_at,   // canonical payment time
                                         'desc'   => 'Bayar Tagihan - ' . $item->student->name,
                                         'amount' => $item->amount,
                                         'type'   => 'BILL',
                                         'method' => $methodLabel
                                     ];
                                 });

        // Gabung, Sort by Time, Ambil 5 Teratas
        $recentActivities = collect($latestPos)->merge(collect($latestBill))
                                               ->sortByDesc('time')
                                               ->take(5);

        // ==========================================================
        // RETURN VIEW
        // ==========================================================
        return view('dashboard', compact(
            'totalIncomeToday',
            'billMidtransToday',
            'totalCashToday',
            'monthlyCash',
            'monthlyMidtrans',
            'unpaidStudents',
            'lowStockItems',
            'chartData',
            'maxIncome',
            'recentActivities'
        ));
    }
}
