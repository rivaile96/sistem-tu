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
        
        // A. Pemasukan dari Kantin/POS (Asumsi POS selalu Cash/Tunai)
        $posToday = PosOrder::whereDate('created_at', today())
                            ->where('payment_status', 'PAID')
                            ->sum('payment_amount');

        // B. Pemasukan Tagihan Sekolah (SPP, Gedung, dll) - TUNAI
        $billCashToday = StudentBill::whereDate('updated_at', today())
                                    ->where('status', 'PAID')
                                    ->where('payment_method', 'CASH')
                                    ->sum('amount');

        // C. Pemasukan Tagihan Sekolah - MIDTRANS (Payment Gateway)
        $billMidtransToday = StudentBill::whereDate('updated_at', today())
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
        
        // Kita hitung total bulan ini biar keliatan porsinya
        $monthlyCash = StudentBill::whereMonth('updated_at', now()->month)
                                  ->whereYear('updated_at', now()->year)
                                  ->where('status', 'PAID')
                                  ->where('payment_method', 'CASH')
                                  ->sum('amount');

        $monthlyMidtrans = StudentBill::whereMonth('updated_at', now()->month)
                                      ->whereYear('updated_at', now()->year)
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
            
            // Pemasukan POS
            $pos = PosOrder::whereDate('created_at', $date)
                           ->where('payment_status', 'PAID')
                           ->sum('payment_amount');
            
            // Pemasukan Bill (Gabungan Cash + Midtrans)
            $bill = StudentBill::whereDate('updated_at', $date)
                               ->where('status', 'PAID')
                               ->sum('amount');
            
            $chartData[] = [
                'day' => $date->format('D'), // Mon, Tue, Wed...
                'total' => $pos + $bill
            ];
        }
        // Skala Grafik (Max Value)
        $maxIncome = collect($chartData)->max('total') ?: 1; 


        // ==========================================================
        // 5. LOG AKTIVITAS TERBARU (Live Feed)
        // ==========================================================
        
        // Feed POS
        $latestPos = PosOrder::with('user')->latest()->limit(5)->get()->map(function($item) {
            return [
                'time' => $item->created_at,
                'desc' => 'Jajan Kantin #' . $item->id,
                'amount' => $item->total_amount,
                'type' => 'POS',
                'method' => 'Cash' // POS default Cash
            ];
        });

        // Feed SPP (Sekarang ada label Metodenya)
        $latestBill = StudentBill::with('student')
                                 ->where('status', 'PAID')
                                 ->latest('updated_at')
                                 ->limit(5)
                                 ->get()
                                 ->map(function($item) {
            // Label Method lebih rapi
            $methodLabel = $item->payment_method == 'MIDTRANS' ? 'Online (App)' : 'Tunai (TU)';
            
            return [
                'time' => $item->updated_at,
                'desc' => 'Bayar Tagihan - ' . $item->student->name,
                'amount' => $item->amount,
                'type' => 'BILL',
                'method' => $methodLabel
            ];
        });

        // Gabung, Sort by Time, Ambil 5 Teratas
        $recentActivities = $latestPos->merge($latestBill)->sortByDesc('time')->take(5);

        // ==========================================================
        // RETURN VIEW
        // ==========================================================
        return view('dashboard', compact(
            'totalIncomeToday', 
            'billMidtransToday', // <--- Variabel Baru: Pemasukan Midtrans Hari Ini
            'totalCashToday',    // <--- Variabel Baru: Pemasukan Cash Hari Ini
            'monthlyCash',       // <--- Variabel Baru: Total Cash Bulan Ini
            'monthlyMidtrans',   // <--- Variabel Baru: Total Midtrans Bulan Ini
            'unpaidStudents', 
            'lowStockItems', 
            'chartData', 
            'maxIncome', 
            'recentActivities'
        ));
    }
}