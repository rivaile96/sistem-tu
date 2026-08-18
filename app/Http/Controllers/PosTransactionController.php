<?php

namespace App\Http\Controllers;

use App\Models\PosItem;
use App\Models\PosOrder;     // Model Transaksi Header
use App\Models\PosOrderItem; // Model Detail Item
use App\Models\Student;      // <--- MODEL SISWA (PENTING)
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

        // Include kelas_id so we can resolve kelas name in the view
        $students = Student::with('kelas')
                           ->where('status', 'active')
                           ->orderBy('name')
                           ->get(['id', 'name', 'nis', 'kelas_id'])
                           ->map(fn($s) => [
                               'id'         => $s->id,
                               'name'       => $s->name,
                               'nis'        => $s->nis,
                               'class_name' => optional($s->kelas)->nama_kelas ?? '-',
                           ]);

        return view('pos.transaction.index', compact('items', 'students'));
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
            'student_id' => 'nullable|exists:students,id', // <--- Validasi ID Siswa
        ]);

        try {
            // Mulai Database Transaction (Safety First!)
            DB::beginTransaction();

            // A. Tentukan Status & Hitung Angka
            // Jika uang yg dimasukkan >= total, maka LUNAS (Cash).
            $isCash = $request->payment_amount >= $request->total_amount;
            
            // --- LOGIC PENTING: VALIDASI HUTANG ---
            // Kalau bukan Cash (berarti Hutang/QRIS), WAJIB pilih siswa.
            // Gak boleh ngutang tanpa identitas.
            if (!$isCash && empty($request->student_id)) {
                throw new \Exception("Untuk pembayaran Hutang/QRIS, WAJIB memilih nama siswa!");
            }

            $statusPayment = $isCash ? 'PAID' : 'UNPAID';
            
            // Jika Lunas, barang langsung status COMPLETED (bawa pulang).
            // Jika Pending, barang status PENDING (ambil nanti setelah bayar/scan).
            $statusRedemption = $isCash ? 'COMPLETED' : 'PENDING';

            // Hitung kembalian
            $changeAmount = $isCash ? ($request->payment_amount - $request->total_amount) : 0;

            // B. Simpan Header Transaksi (PosOrder)
            $order = PosOrder::create([
                'user_id' => Auth::id(), // Kasir
                'student_id' => $request->student_id, // <--- Simpan ID Siswa (Null kalau Cash Anonim)
                'transaction_code' => 'TRX-' . time() . Str::upper(Str::random(3)),
                'total_amount' => $request->total_amount,
                'payment_amount' => $request->payment_amount, 
                'change_amount' => $changeAmount,
                'payment_status' => $statusPayment,
                'redemption_status' => $statusRedemption,
                'qr_token' => Str::uuid(),
            ]);

            // C. Simpan Detail Item & Kurangi Stok
            foreach ($request->cart as $cartItem) {
                // Lock baris database (PENTING: Mencegah race condition/stok minus)
                $itemDB = PosItem::lockForUpdate()->find($cartItem['id']);

                // Cek stok lagi di backend
                if ($itemDB->stock < $cartItem['qty']) {
                    throw new \Exception("Stok barang '{$itemDB->name}' tidak cukup! Sisa: {$itemDB->stock}");
                }

                // Kurangi Stok Master
                $itemDB->decrement('stock', $cartItem['qty']);

                // Simpan ke tabel detail
                PosOrderItem::create([
                    'pos_order_id' => $order->id,
                    'pos_item_id' => $itemDB->id,
                    'quantity' => $cartItem['qty'],
                    'price_at_transaction' => $itemDB->price, // Harga dikunci saat transaksi
                ]);
            }

            // Commit Transaksi
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Transaksi Berhasil!',
                'change' => number_format($changeAmount, 0, ',', '.'),
                'trx_id' => $order->id,
                'transaction_code' => $order->transaction_code
            ]);

        } catch (\Exception $e) {
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
        // Ambil data transaksi beserta item, user (kasir), DAN student (siswa)
        $transaction = PosOrder::with(['items.item', 'user', 'student'])->findOrFail($id);

        return view('pos.transaction.print', compact('transaction'));
    }
}