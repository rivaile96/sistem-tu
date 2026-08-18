<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PosOrder;
use Illuminate\Support\Facades\Log;

class MidtransController extends Controller
{
    public function callback(Request $request)
    {
        $serverKey = config('services.midtrans.server_key');
        $hashed    = hash('sha512',
            $request->order_id . $request->status_code . $request->gross_amount . $serverKey
        );

        if ($hashed !== $request->signature_key) {
            return response()->json(['message' => 'Invalid Signature'], 403);
        }

        $transactionStatus = $request->transaction_status;
        $orderId           = $request->order_id;

        if (str_contains($orderId, 'SPP-')) {
            // Phase 6B-4: Legacy SPP Midtrans flow retired.
            // spp_bills table has been dropped. Log the callback and return OK
            // so Midtrans does not retry. No writes performed.
            Log::info('MidtransController: legacy SPP callback received — ignored (spp_bills retired)', [
                'order_id' => $orderId,
                'status'   => $transactionStatus,
            ]);
        } elseif (str_contains($orderId, 'TRX-')) {
            $this->handlePosPayment($orderId, $transactionStatus);
        }

        return response()->json(['message' => 'Callback received']);
    }

    private function handlePosPayment(string $orderId, string $status): void
    {
        $order = PosOrder::where('transaction_code', $orderId)->first();

        if (! $order) return;

        if (in_array($status, ['capture', 'settlement'])) {
            $order->update([
                'payment_status'    => 'PAID',
                'redemption_status' => 'PENDING',
            ]);
        } elseif (in_array($status, ['expire', 'cancel'])) {
            $order->update(['payment_status' => 'CANCELLED']);

            foreach ($order->items as $item) {
                $item->item->increment('stock', $item->quantity);
            }
        }
    }
}