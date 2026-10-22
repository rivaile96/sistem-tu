<?php

namespace App\Jobs;

use App\Events\PaymentReceived;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * PushPaymentToKaffahJob — Kirim event PaymentReceived ke Kaffah Finance.
 *
 * Di-dispatch oleh PaymentReceivedListener setelah TU webhook settlement.
 *
 * Retry policy:
 *   5x dengan backoff eksponensial untuk toleransi Kaffah sementara down.
 *   TU payment tetap PAID — job ini tidak mempengaruhi status tagihan TU.
 *
 * Idempotency:
 *   Kaffah endpoint menerima order_id yang sama → 208 idempotent → no second journal.
 *
 * Security:
 *   Payload hanya berisi finance event data.
 *   TIDAK mengandung: server key, snap token, app_password, password.
 */
class PushPaymentToKaffahJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 5;
    public int $timeout = 30;

    public function backoff(): array
    {
        return [60, 300, 900, 1800, 3600];
    }

    public function __construct(
        private readonly int    $billId,
        private readonly string $nis,
        private readonly string $amount,
        private readonly string $paymentMethod,
        private readonly string $provider,
        private readonly string $orderId,
        private readonly string $paidAt,
        private readonly string $billingType,
    ) {}

    /**
     * Build from PaymentReceived event.
     */
    public static function fromEvent(PaymentReceived $event, string $billingType = 'SPP'): self
    {
        return new self(
            billId:        $event->billId,
            nis:           $event->nis,
            amount:        $event->amount,
            paymentMethod: $event->paymentMethod,
            provider:      $event->provider,
            orderId:       $event->orderId,
            paidAt:        $event->paidAt,
            billingType:   $billingType,
        );
    }

    public function handle(): void
    {
        $url    = rtrim(config('services.kaffah.url', ''), '/') . '/api/internal/finance/payment-received';
        $secret = config('services.kaffah.api_secret');

        if (empty($url) || empty($secret)) {
            Log::error('PushPaymentToKaffahJob: konfigurasi Kaffah tidak lengkap', [
                'order_id' => $this->orderId,
            ]);
            $this->fail(new \RuntimeException('Kaffah URL/secret tidak dikonfigurasi.'));
            return;
        }

        $payload = [
            'event'          => 'payment.received',
            'version'        => 1,
            'nis'            => $this->nis,
            'bill_id'        => (string) $this->billId,
            'amount'         => $this->amount,
            'currency'       => 'IDR',
            'payment_method' => $this->paymentMethod,
            'provider'       => $this->provider,
            'order_id'       => $this->orderId,
            'paid_at'        => $this->paidAt,
            'billing_type'   => $this->billingType,
        ];

        $response = Http::timeout(25)
            ->withHeaders(['X-KAFFAH-SERVICE-KEY' => $secret])
            ->post($url, $payload);

        // 200/201 = success, 208 = idempotent (already processed)
        if ($response->successful() || $response->status() === 208) {
            Log::info('PushPaymentToKaffahJob: berhasil', [
                'order_id'   => $this->orderId,
                'nis'        => $this->nis,
                'http_status'=> $response->status(),
            ]);
            return;
        }

        // 4xx client errors → fail permanently (config/data problem, retrying won't help)
        if ($response->clientError()) {
            Log::error('PushPaymentToKaffahJob: client error — fail permanen', [
                'order_id'    => $this->orderId,
                'http_status' => $response->status(),
                'body'        => $response->body(),
            ]);
            $this->fail(new \RuntimeException(
                "Kaffah Finance menolak event: HTTP {$response->status()}"
            ));
            return;
        }

        // 5xx → throw untuk trigger retry
        throw new \RuntimeException(
            "Kaffah Finance error sementara: HTTP {$response->status()}"
        );
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('PushPaymentToKaffahJob: semua retry gagal', [
            'order_id'   => $this->orderId,
            'nis'        => $this->nis,
            'error'      => $exception->getMessage(),
        ]);
    }
}
