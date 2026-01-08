<?php

namespace App\Http\Controllers;

use App\Models\PosItem;
use App\Models\PosTransaction;
use App\Models\PosTransactionItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PosTransactionController extends Controller
{
    // 1. Tampilkan Halaman Kasir
    public function index()
    {
        // Kita ambil semua barang yang stoknya ada
        // Dikirim ke View buat diolah sama AlpineJS (Pencarian Instant)
        $items = PosItem::where('stock', '>', 0)->orderBy('name')->get();
        
        return view('pos.transaction.index', compact('items'));
    }

    // 2. Proses Simpan Transaksi (Checkout)
    public function store(Request $request)
    {
        $request->validate([
            'cart' => 'required|array',
            'cart.*.id' => 'required|exists:pos_items,id',
            'cart.*.qty' => 'required|integer|min:1',
            'payment_amount' => 'required|numeric',
            'total_amount' => 'required|numeric',
        ]);

        // Gunakan DB Transaction biar aman (kalau gagal 1, gagal semua)
        try {
            DB::beginTransaction();

            // A. Simpan Header Transaksi
            $transaction = PosTransaction::create([
                'user_id' => Auth::id(), // Siapa kasirnya
                'transaction_code' => 'TRX-' . time(), // Kode unik
                'total_amount' => $request->total_amount,
                'payment_amount' => $request->payment_amount,
                'change_amount' => $request->payment_amount - $request->total_amount,
                'status' => 'LUNAS',
            ]);

            // B. Simpan Detail Item & Kurangi Stok
            foreach ($request->cart as $cartItem) {
                // Ambil data barang asli dari DB (buat safety harga)
                $itemDB = PosItem::lockForUpdate()->find($cartItem['id']);

                if ($itemDB->stock < $cartItem['qty']) {
                    throw new \Exception("Stok barang {$itemDB->name} tidak cukup!");
                }

                // Kurangi Stok
                $itemDB->decrement('stock', $cartItem['qty']);

                // Simpan ke tabel detail
                PosTransactionItem::create([
                    'pos_transaction_id' => $transaction->id,
                    'pos_item_id' => $itemDB->id,
                    'quantity' => $cartItem['qty'],
                    'price_per_item' => $itemDB->price,
                    'subtotal' => $itemDB->price * $cartItem['qty'],
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Transaksi Berhasil!',
                'change' => number_format($transaction->change_amount, 0, ',', '.'),
                'trx_id' => $transaction->id
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }
}