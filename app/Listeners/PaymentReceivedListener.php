<?php

namespace App\Listeners;

use App\Events\PaymentReceived;
use App\Jobs\PushPaymentToKaffahJob;
use App\Models\StudentBill;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * PaymentReceivedListener — konsumen event PaymentReceived di TU.
 *
 * Dispatch PushPaymentToKaffahJob ke queue setelah payment settlement.
 * Job akan POST ke Kaffah Finance /api/internal/finance/payment-received.
 *
 * Implements ShouldQueue sehingga listener sendiri diproses async,
 * memastikan Midtrans webhook response tetap cepat.
 */
class PaymentReceivedListener implements ShouldQueue
{
    public string $queue = 'default';

    public function handle(PaymentReceived $event): void
    {
        // Resolve billing_type dari student_bills.type — default SPP
        $bill        = StudentBill::find($event->billId);
        $billingType = $bill?->type ?? 'SPP';

        // Use dispatch() helper with the job instance
        dispatch(PushPaymentToKaffahJob::fromEvent($event, $billingType));
    }

    public function failed(PaymentReceived $event, \Throwable $exception): void
    {
        \Illuminate\Support\Facades\Log::error('PaymentReceivedListener: gagal dispatch job', [
            'bill_id'  => $event->billId,
            'order_id' => $event->orderId,
            'error'    => $exception->getMessage(),
        ]);
    }
}
