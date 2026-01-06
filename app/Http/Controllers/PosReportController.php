<?php

namespace App\Http\Controllers;

use App\Models\PosOrder;
use Illuminate\Http\Request;

class PosReportController extends Controller
{
    public function index(Request $request)
    {
        $query = PosOrder::with(['user', 'items.item'])->latest();

        // Filter Tanggal (Default: Hari ini)
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('created_at', [
                $request->start_date . ' 00:00:00', 
                $request->end_date . ' 23:59:59'
            ]);
        }

        // Search Kode Transaksi
        if ($request->has('search')) {
            $query->where('transaction_code', 'like', '%' . $request->search . '%');
        }

        $orders = $query->paginate(10);

        return view('pos.history.index', compact('orders'));
    }

    // Untuk melihat detail item via Ajax/Modal nanti
    public function show($id)
    {
        $order = PosOrder::with(['items.item', 'user'])->findOrFail($id);
        return response()->json($order);
    }
}