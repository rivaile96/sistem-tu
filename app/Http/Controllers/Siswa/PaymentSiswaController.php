<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\StudentBill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Midtrans\Config as MidtransConfig;
use Midtrans\Snap;

class PaymentSiswaController extends Controller
{
    public function __construct()
    {
        MidtransConfig::$serverKey    = config('services.midtrans.server_key');
        MidtransConfig::$isProduction = config('services.midtrans.is_production', false);
        MidtransConfig::$isSanitized  = config('services.midtrans.is_sanitized', true);
        MidtransConfig::$is3ds        = config('services.midtrans.is_3ds', true);
    }

    /**
     * Buat Midtrans Snap token untuk 1 tagihan.
     * Dipanggil via AJAX dari dashboard siswa.
     */
    public function createToken(Request $request, StudentBill $bill)
    {
        $student = Auth::guard('siswa')->user();

        if ((int) $bill->student_id !== (int) $student->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if ($bill->status === 'PAID') {
            return response()->json(['error' => 'Tagihan ini sudah dibayar.'], 400);
        }

        // Order ID unik: BILL-{id}-{random}-{timestamp}
        $orderId = 'BILL-' . $bill->id . '-' . Str::random(6) . '-' . time();

        $billName = $bill->name ?: ('Tagihan ' . ($bill->bill_month ?? '') . '/' . ($bill->bill_year ?? ''));

        $params = [
            'transaction_details' => [
                'order_id'     => $orderId,
                'gross_amount' => (int) $bill->amount,
            ],
            'customer_details' => [
                'first_name' => $student->name,
                'phone'      => $student->parent_phone ?? '',
            ],
            'item_details' => [
                [
                    'id'       => 'bill-' . $bill->id,
                    'price'    => (int) $bill->amount,
                    'quantity' => 1,
                    'name'     => mb_substr($billName, 0, 50), // Midtrans max 50 char
                ],
            ],
            'callbacks' => [
                'finish' => route('siswa.payment.success'),
            ],
        ];

        try {
            $snapToken = Snap::getSnapToken($params);

            // Simpan token ke record tagihan untuk referensi
            $bill->update(['payment_token' => $snapToken]);

            return response()->json([
                'snap_token'    => $snapToken,
                'client_key'    => config('services.midtrans.client_key'),
                'order_id'      => $orderId,
                'is_production' => config('services.midtrans.is_production', false),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Gagal membuat token pembayaran: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Midtrans server-to-server webhook callback.
     * Route ini di-exclude dari CSRF di bootstrap/app.php.
     */
    public function callback(Request $request)
    {
        $payload = $request->all();

        // Verifikasi signature key Midtrans
        $orderId      = $payload['order_id'] ?? '';
        $statusCode   = $payload['status_code'] ?? '';
        $grossAmount  = $payload['gross_amount'] ?? '';
        $serverKey    = config('services.midtrans.server_key');
        $expectedSig  = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

        if ($expectedSig !== ($payload['signature_key'] ?? '')) {
            // Kalau sandbox/test notification dari Midtrans dashboard, tetap return 200
            // supaya "Test notification URL" di dashboard bisa sukses.
            // Untuk transaksi nyata, signature wajib valid.
            \Log::warning('Midtrans callback: invalid signature', ['order_id' => $orderId]);
            return response()->json(['message' => 'OK'], 200);
        }

        $transactionStatus = $payload['transaction_status'] ?? '';
        $fraudStatus       = $payload['fraud_status'] ?? 'accept';

        // Ekstrak bill ID dari order_id: BILL-{id}-random-timestamp
        preg_match('/^BILL-(\d+)-/', $orderId, $matches);
        $billId = $matches[1] ?? null;

        if (!$billId) {
            return response()->json(['message' => 'Invalid order_id format'], 400);
        }

        $bill = StudentBill::find($billId);
        if (!$bill) {
            return response()->json(['message' => 'Bill not found'], 404);
        }

        // Update status berdasarkan respons Midtrans
        if (in_array($transactionStatus, ['capture', 'settlement']) && $fraudStatus === 'accept') {
            $bill->update([
                'status'         => 'PAID',
                'payment_method' => $payload['payment_type'] ?? 'midtrans',
            ]);
        } elseif (in_array($transactionStatus, ['deny', 'expire', 'cancel'])) {
            // Reset token agar bisa coba bayar lagi
            $bill->update([
                'payment_method' => null,
                'payment_token'  => null,
            ]);
        }
        // 'pending' → biarkan status tetap UNPAID

        return response()->json(['message' => 'OK']);
    }

    /**
     * Halaman struk/bukti pembayaran.
     */
    public function struk(StudentBill $bill)
    {
        $student = Auth::guard('siswa')->user();

        if ((int) $bill->student_id !== (int) $student->id) {
            abort(403);
        }

        if ($bill->status !== 'PAID') {
            return redirect()->route('siswa.dashboard');
        }

        $schoolName    = \App\Models\Setting::where('key', 'school_name')->value('value') ?? 'Sekolah';
        $schoolAddress = \App\Models\Setting::where('key', 'school_address')->value('value') ?? '';

        return view('siswa.struk', compact('bill', 'student', 'schoolName', 'schoolAddress'));
    }

    /**
     * Halaman redirect setelah Midtrans Snap selesai.
     */
    public function success(Request $request)
    {
        return redirect()->route('siswa.dashboard')
                         ->with('success', 'Pembayaran berhasil diproses! Tagihan akan diperbarui setelah konfirmasi dari bank.');
    }
}
