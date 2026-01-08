<?php

namespace App\Http\Controllers;

use App\Models\PosItem;
use App\Models\PosOrder;       // <--- PAKE MODEL YANG BENAR
use App\Models\PosOrderItem;   // <--- PAKE MODEL YANG BENAR
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PosTransactionController extends Controller
{
    // 1. Tampilkan Halaman Kasir
    public function index()
    {
        // Ambil barang yang stoknya ada saja
        $items = PosItem::where('stock', '>', 0)->orderBy('name')->get();
        return view('pos.transaction.index', compact('items'));
    }

    // 2. Proses Checkout (Cash & Online)
    public function store(Request $request)
    {
        $request->validate([
            'cart' => 'required|array',
            'cart.*.id' => 'required|exists:pos_items,id',
            'cart.*.qty' => 'required|integer|min:1',
            'payment_amount' => 'required|numeric',
            'total_amount' => 'required|numeric',
        ]);

        try {
            DB::beginTransaction();

            // A. Logic Tentukan Status
            // Kalau bayar >= total, berarti CASH/LUNAS. Kalau 0, berarti ONLINE/PENDING.
            $isCash = $request->payment_amount >= $request->total_amount;
            
            $statusPayment = $isCash ? 'PAID' : 'UNPAID';
            $statusRedemption = $isCash ? 'COMPLETED' : 'PENDING'; // Kalau cash langsung bawa pulang

            // B. Buat Header Transaksi
            $order = PosOrder::create([
                'user_id' => Auth::id(),
                'transaction_code' => 'TRX-' . time() . Str::upper(Str::random(3)), // Unik
                'total_amount' => $request->total_amount,
                'payment_status' => $statusPayment,      // PAID / UNPAID
                'redemption_status' => $statusRedemption, // COMPLETED / PENDING
                'qr_token' => Str::uuid(), // Untuk QR Code pengambilan nanti
            ]);

            // C. Simpan Detail & Kurangi Stok
            foreach ($request->cart as $cartItem) {
                // Lock stok biar gak rebutan
                $itemDB = PosItem::lockForUpdate()->find($cartItem['id']);

                if ($itemDB->stock < $cartItem['qty']) {
                    throw new \Exception("Stok barang {$itemDB->name} habis saat anda checkout!");
                }

                $itemDB->decrement('stock', $cartItem['qty']);

                PosOrderItem::create([
                    'pos_order_id' => $order->id,
                    'pos_item_id' => $itemDB->id,
                    'quantity' => $cartItem['qty'],
                    'price_at_transaction' => $itemDB->price, // Simpan harga saat beli
                ]);
            }

            // D. Jika Online, Generate Midtrans Token (Opsional, Logic Tambahan)
            // Note: Untuk sekarang kita fokus simpan dulu. Logic snap token POS bisa ditambahkan nanti 
            // di tombol "Bayar" history jika flow-nya O2O (Order Online, Ambil Offline).
            
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Transaksi Berhasil!',
                'change' => $isCash ? number_format($request->payment_amount - $request->total_amount, 0, ',', '.') : 0,
                'trx_id' => $order->id
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 400);
        }
    }
}