<?php

namespace App\Http\Controllers;

use App\Models\PosItem;
use App\Models\PosOrder;
use App\Models\PosOrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PosTransactionController extends Controller
{
    // 1. Halaman Kasir (Tampilkan Barang)
    public function index()
    {
        // Ambil semua barang yang stoknya ada
        $items = PosItem::where('is_active', true)
                        ->where('stock', '>', 0)
                        ->get();

        return view('pos.transaction', compact('items'));
    }

    // 2. Proses Simpan Transaksi (Checkout)
    public function store(Request $request)
    {
        $request->validate([
            'cart' => 'required|array', // Data keranjang dikirim sebagai Array JSON
            'total_amount' => 'required|numeric',
            'payment_method' => 'required|string',
        ]);

        DB::transaction(function () use ($request) {
            // A. Buat Order Header
            $order = PosOrder::create([
                'user_id' => Auth::id() ?? 1, // Fallback ID 1 jika belum login
                'transaction_code' => 'TRX-' . time(),
                'total_amount' => $request->total_amount,
                'status' => 'PAID', // Asumsi langsung lunas di kasir
                // Nanti bisa tambah kolom payment_method di tabel pos_orders jika perlu
            ]);

            // B. Simpan Detail Item & Kurangi Stok
            foreach ($request->cart as $item) {
                PosOrderItem::create([
                    'pos_order_id' => $order->id,
                    'pos_item_id' => $item['id'],
                    'quantity' => $item['qty'],
                    'price_at_transaction' => $item['price'],
                ]);

                // Kurangi Stok Real di Database
                PosItem::where('id', $item['id'])->decrement('stock', $item['qty']);
            }
        });

        return response()->json(['status' => 'success', 'message' => 'Transaksi Berhasil!']);
    }
}