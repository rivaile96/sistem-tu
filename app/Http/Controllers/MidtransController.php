<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SppBill;
use App\Models\PosOrder;
use Illuminate\Support\Facades\Log;

class MidtransController extends Controller
{
    public function callback(Request $request)
    {
        // 1. Ambil data dari notifikasi Midtrans
        $serverKey = config('services.midtrans.server_key');
        $hashed = hash("sha512", $request->order_id . $request->status_code . $request->gross_amount . $serverKey);

        // 2. Validasi Security (Pastikan pengirimnya benar-benar Midtrans)
        if ($hashed !== $request->signature_key) {
            return response()->json(['message' => 'Invalid Signature'], 403);
        }

        $transactionStatus = $request->transaction_status;
        $orderId = $request->order_id;

        // 3. Cek Jenis Transaksi berdasarkan Format ID
        // Jika depannya 'SPP-', berarti bayar sekolah. Jika 'TRX-', berarti POS.
        if (str_contains($orderId, 'SPP-')) {
            $this->handleSppPayment($orderId, $transactionStatus);
        } elseif (str_contains($orderId, 'TRX-')) {
            $this->handlePosPayment($orderId, $transactionStatus);
        }

        return response()->json(['message' => 'Callback received']);
    }

    // Logic Update SPP
    private function handleSppPayment($orderId, $status)
    {
        $bill = SppBill::where('midtrans_order_id', $orderId)->first();
        
        if (!$bill) return;

        if ($status == 'capture' || $status == 'settlement') {
            $bill->update([
                'status' => 'LUNAS',
                'payment_method' => 'Midtrans Otomatis',
                'paid_at' => now()
            ]);
        } elseif ($status == 'expire' || $status == 'cancel' || $status == 'deny') {
            $bill->update(['status' => 'BELUM']); // Reset jadi bisa bayar ulang
        }
    }

    // Logic Update POS
    private function handlePosPayment($orderId, $status)
    {
        $order = PosOrder::where('transaction_code', $orderId)->first(); // Asumsi transaction_code = midtrans order id
        
        if (!$order) return;

        if ($status == 'capture' || $status == 'settlement') {
            $order->update([
                'payment_status' => 'PAID',
                'redemption_status' => 'PENDING', // Udah bayar, siap diambil (QR Aktif)
            ]);
            // Stok sudah dikurangi saat checkout, jadi aman.
        } elseif ($status == 'expire' || $status == 'cancel') {
            $order->update(['payment_status' => 'CANCELLED']);
            
            // PENTING: Balikin stok karena batal beli
            foreach ($order->items as $item) {
                $item->item->increment('stock', $item->quantity);
            }
        }
    }
}