<?php

namespace App\Http\Controllers;

use App\Models\PosOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PosReportController extends Controller
{
    // Tampilkan Riwayat dengan Filter & Ringkasan
    public function index(Request $request)
    {
        // 1. Setup Query Dasar
        $query = PosOrder::with(['user', 'student'])->latest();

        // 2. Filter Tanggal (Default: Hari Ini)
        $startDate = $request->start_date ?? date('Y-m-d');
        $endDate = $request->end_date ?? date('Y-m-d');
        
        // Tambahkan jam biar mencakup 00:00 - 23:59
        $query->whereBetween('created_at', ["$startDate 00:00:00", "$endDate 23:59:59"]);

        // 3. Filter Status (Lunas / Hutang)
        if ($request->has('status') && $request->status != '') {
            $query->where('payment_status', $request->status);
        }

        // 4. Hitung Ringkasan (Summary Cards)
        // Kita clone query biar nggak ganggu pagination
        $summaryQuery = clone $query;
        
        $totalOmset = $summaryQuery->sum('total_amount');
        
        // Hitung Uang Masuk (Hanya dari yang PAID + Pelunasan sebagian)
        // Simplenya: payment_amount adalah uang yang diterima
        $totalCashIn = $summaryQuery->sum('payment_amount');
        
        // Hitung Piutang (Total Tagihan dari transaksi UNPAID)
        $totalUnpaid = $summaryQuery->where('payment_status', 'UNPAID')->sum('total_amount');

        // 5. Ambil Data Tabel (Pagination)
        $transactions = $query->paginate(10)->withQueryString();

        return view('pos.history.index', compact(
            'transactions', 'totalOmset', 'totalCashIn', 'totalUnpaid', 'startDate', 'endDate'
        ));
    }

    // Proses Pelunasan Hutang
    public function repay(Request $request, $id)
    {
        try {
            DB::beginTransaction();
            
            $transaction = PosOrder::findOrFail($id);
            
            if ($transaction->payment_status == 'PAID') {
                return back()->with('error', 'Transaksi ini sudah lunas!');
            }

            // Update Status jadi LUNAS
            $transaction->update([
                'payment_status' => 'PAID',
                'payment_amount' => $transaction->total_amount, // Anggap dibayar pas
                'change_amount' => 0,
                // Opsional: Catat tanggal pelunasan/updated_at akan berubah otomatis
            ]);

            DB::commit();
            return back()->with('success', 'Hutang berhasil dilunasi!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memproses: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        // Detail transaksi kalau perlu (opsional)
    }
}