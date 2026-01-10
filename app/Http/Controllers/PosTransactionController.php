<?php

namespace App\Http\Controllers;

use App\Models\PosItem;
use App\Models\PosOrder;     // Model Transaksi Header
use App\Models\PosOrderItem; // Model Detail Item
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PosTransactionController extends Controller
{
    /**
     * 1. Tampilkan Halaman Kasir
     */
    public function index()
    {
        // Ambil barang yang stoknya > 0 saja untuk ditampilkan di grid
        $items = PosItem::where('stock', '>', 0)
                        ->orderBy('name')
                        ->get();

        return view('pos.transaction.index', compact('items'));
    }

    /**
     * 2. Proses Checkout (Cash & Online/Hutang)
     */
    public function store(Request $request)
    {
        // Validasi Input dari Frontend
        $request->validate([
            'cart' => 'required|array',
            'cart.*.id' => 'required|exists:pos_items,id',
            'cart.*.qty' => 'required|integer|min:1',
            'payment_amount' => 'required|numeric',
            'total_amount' => 'required|numeric',
        ]);

        try {
            // Mulai Database Transaction (Safety First!)
            DB::beginTransaction();

            // A. Tentukan Status & Hitung Angka
            // Jika uang yg dimasukkan >= total, maka LUNAS (Cash).
            // Jika 0 atau kurang, maka HUTANG/QRIS (Pending).
            $isCash = $request->payment_amount >= $request->total_amount;
            
            $statusPayment = $isCash ? 'PAID' : 'UNPAID';
            
            // Jika Lunas, barang langsung status COMPLETED (bawa pulang).
            // Jika Pending, barang status PENDING (ambil nanti setelah bayar/scan).
            $statusRedemption = $isCash ? 'COMPLETED' : 'PENDING';

            // Hitung kembalian untuk disimpan di database
            // Jika mode hutang, kembalian dianggap 0
            $changeAmount = $isCash ? ($request->payment_amount - $request->total_amount) : 0;

            // B. Simpan Header Transaksi (PosOrder)
            $order = PosOrder::create([
                'user_id' => Auth::id(), // ID Kasir yang login
                'transaction_code' => 'TRX-' . time() . Str::upper(Str::random(3)), // Kode Unik
                'total_amount' => $request->total_amount,
                
                // --- UPDATE PENTING: Simpan Detail Pembayaran ---
                'payment_amount' => $request->payment_amount, 
                'change_amount' => $changeAmount,
                // ------------------------------------------------
                
                'payment_status' => $statusPayment,
                'redemption_status' => $statusRedemption,
                'qr_token' => Str::uuid(), // Token unik untuk QR Code pengambilan
            ]);

            // C. Simpan Detail Item & Kurangi Stok
            foreach ($request->cart as $cartItem) {
                // Lock baris database (PENTING: Mencegah race condition)
                $itemDB = PosItem::lockForUpdate()->find($cartItem['id']);

                // Cek stok lagi di backend untuk memastikan
                if ($itemDB->stock < $cartItem['qty']) {
                    throw new \Exception("Stok barang '{$itemDB->name}' tidak cukup! Sisa: {$itemDB->stock}");
                }

                // Kurangi Stok Master
                $itemDB->decrement('stock', $cartItem['qty']);

                // Simpan ke tabel detail (PosOrderItem)
                PosOrderItem::create([
                    'pos_order_id' => $order->id,
                    'pos_item_id' => $itemDB->id,
                    'quantity' => $cartItem['qty'],
                    'price_at_transaction' => $itemDB->price, // Harga dikunci saat transaksi terjadi
                ]);
            }

            // Commit Transaksi (Simpan permanen)
            DB::commit();

            // Return response sukses ke JavaScript
            return response()->json([
                'success' => true,
                'message' => 'Transaksi Berhasil!',
                'change' => number_format($changeAmount, 0, ',', '.'), // Format kembalian
                'trx_id' => $order->id,
                'transaction_code' => $order->transaction_code
            ]);

        } catch (\Exception $e) {
            // Jika ada error, batalkan semua perubahan DB (Rollback)
            DB::rollBack();
            return response()->json([
                'success' => false, 
                'message' => 'Gagal: ' . $e->getMessage()
            ], 400);
        }
    }

    /**
     * 3. Cetak Struk (Thermal Style)
     */
    public function printStruk($id)
    {
        // Ambil data transaksi beserta detail item dan user (kasir)
        $transaction = PosOrder::with(['items.item', 'user'])->findOrFail($id);

        return view('pos.transaction.print', compact('transaction'));
    }
}