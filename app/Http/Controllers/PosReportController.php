<?php

namespace App\Http\Controllers;

use App\Models\PosOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PosReportController extends Controller
{
    public function index(Request $request)
    {
        // 1. Filter tanggal (default: hari ini)
        $startDate = $request->start_date ?? date('Y-m-d');
        $endDate   = $request->end_date   ?? date('Y-m-d');

        // 2. Base query dengan eager load
        $baseQuery = PosOrder::with(['user', 'student'])->withCount('items')
            ->whereBetween('created_at', [
                "$startDate 00:00:00",
                "$endDate 23:59:59",
            ]);

        // 3. Filter status opsional
        if ($request->filled('status')) {
            $baseQuery->where('payment_status', $request->status);
        }

        // BUG FIX: Hitung summary dari query terpisah — bukan clone yang
        // sudah termutasi. Setiap aggregate pakai query builder segar
        // dengan kondisi yang sama agar angka tidak saling terkontaminasi.
        $summaryBase = function () use ($startDate, $endDate, $request) {
            $q = PosOrder::whereBetween('created_at', [
                "$startDate 00:00:00",
                "$endDate 23:59:59",
            ]);
            if ($request->filled('status')) {
                $q->where('payment_status', $request->status);
            }
            return $q;
        };

        // Total omset: semua transaksi dalam rentang & filter
        $totalOmset = (clone $summaryBase())->sum('total_amount');

        // Uang masuk: hanya dari transaksi PAID
        $totalCashIn = (clone $summaryBase())
            ->where('payment_status', 'PAID')
            ->sum('payment_amount');

        // Piutang: total dari transaksi UNPAID
        $totalUnpaid = (clone $summaryBase())
            ->where('payment_status', 'UNPAID')
            ->sum('total_amount');

        // 4. Data tabel dengan pagination
        $transactions = $baseQuery->latest()->paginate(10)->withQueryString();

        return view('pos.history.index', compact(
            'transactions',
            'totalOmset',
            'totalCashIn',
            'totalUnpaid',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Pelunasan hutang POS.
     * Hanya bisa dilakukan pada transaksi UNPAID.
     */
    public function repay(Request $request, $id)
    {
        try {
            DB::beginTransaction();

            $transaction = PosOrder::findOrFail($id);

            if ($transaction->payment_status === 'PAID') {
                return back()->with('error', 'Transaksi ini sudah lunas!');
            }

            $transaction->update([
                'payment_status' => 'PAID',
                'payment_amount' => $transaction->total_amount,
                'change_amount'  => 0,
            ]);

            DB::commit();
            return back()->with('success', 'Hutang berhasil dilunasi!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memproses pelunasan: ' . $e->getMessage());
        }
    }
}
