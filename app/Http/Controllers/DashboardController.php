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
        // 1. DATA CARD ATAS
        // Total Pemasukan Hari Ini (POS + SPP)
        $incomePosToday = PosOrder::whereDate('created_at', today())->where('payment_status', 'PAID')->sum('payment_amount');
        $incomeBillToday = StudentBill::whereDate('updated_at', today())->where('status', 'PAID')->sum('amount');
        $totalIncomeToday = $incomePosToday + $incomeBillToday;

        // Siswa Nunggak (Yang punya minimal 1 tagihan UNPAID)
        $unpaidStudents = Student::whereHas('bills', function($q) {
            $q->where('status', 'UNPAID');
        })->count();

        // Stok Menipis (Kurang dari 5)
        $lowStockItems = PosItem::where('stock', '<=', 5)->orderBy('stock', 'asc')->limit(5)->get();

        // 2. DATA GRAFIK 7 HARI TERAKHIR
        $chartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $pos = PosOrder::whereDate('created_at', $date)->where('payment_status', 'PAID')->sum('payment_amount');
            $bill = StudentBill::whereDate('updated_at', $date)->where('status', 'PAID')->sum('amount');
            
            $chartData[] = [
                'day' => $date->format('D'), // Senin, Selasa...
                'total' => $pos + $bill
            ];
        }
        // Cari nilai tertinggi buat skala grafik
        $maxIncome = collect($chartData)->max('total') ?: 1; 

        // 3. LOG AKTIVITAS TERBARU (Gabungan POS & SPP)
        // Ambil 5 transaksi POS terakhir
        $latestPos = PosOrder::with('user')->latest()->limit(5)->get()->map(function($item) {
            return [
                'time' => $item->created_at,
                'desc' => 'POS - Transaksi #' . $item->id,
                'amount' => $item->total_amount,
                'type' => 'POS'
            ];
        });

        // Ambil 5 pembayaran SPP terakhir
        $latestBill = StudentBill::with('student')->where('status', 'PAID')->latest('updated_at')->limit(5)->get()->map(function($item) {
            return [
                'time' => $item->updated_at,
                'desc' => 'SPP - ' . $item->student->name,
                'amount' => $item->amount,
                'type' => 'BILL'
            ];
        });

        // Gabung dan sort by waktu, ambil 5 teratas
        $recentActivities = $latestPos->merge($latestBill)->sortByDesc('time')->take(5);

        return view('dashboard', compact(
            'totalIncomeToday', 'unpaidStudents', 'lowStockItems', 
            'chartData', 'maxIncome', 'recentActivities'
        ));
    }
}