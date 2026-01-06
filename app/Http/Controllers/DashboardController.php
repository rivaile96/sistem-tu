<?php

namespace App\Http\Controllers;

use App\Models\PosOrder;
use App\Models\SppBill;
use App\Models\Student;
use App\Models\PosItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. Ringkasan Hari Ini
        $today = now()->format('Y-m-d');
        
        // Total uang masuk (SPP + POS) hari ini
        $sppToday = SppBill::whereDate('paid_at', $today)->sum('amount');
        $posToday = PosOrder::whereDate('created_at', $today)
                            ->where('status', 'PAID') // Asumsi status PAID
                            ->sum('total_amount');
        
        $totalIncomeToday = $sppToday + $posToday;

        // 2. Data Widget
        // Siswa belum bayar bulan ini (Misal: Bulan 'Januari 2024')
        // Nanti kita buat dinamis, sekarang hardcode dulu sesuai seeder
        $currentMonth = 'Februari 2024'; 
        $unpaidStudents = SppBill::where('month', $currentMonth)
                                 ->where('status', '!=', 'LUNAS')
                                 ->count();

        // Stok Menipis (Di bawah 10)
        $lowStockItems = PosItem::where('stock', '<=', 10)->get();

        // 3. Kirim data ke View
        return view('dashboard.index', compact(
            'totalIncomeToday',
            'sppToday',
            'posToday',
            'unpaidStudents',
            'lowStockItems'
        ));
    }
}