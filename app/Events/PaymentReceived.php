<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * PaymentReceived — domain event dipicu saat tagihan siswa berhasil dibayar.
 *
 * Dipicu oleh: MidtransController setelah webhook settlement/capture terverifikasi.
 *
 * Konsumen masa depan:
 *   - Kaffah Finance → journal entry otomatis
 *   - General Ledger posting
 *
 * PENTING:
 *   - Event ini TIDAK melakukan accounting/journal sendiri (task terpisah).
 *   - Payload TIDAK mengandung: server key, payment token, snap token, password.
 *   - Semua nilai sudah terverifikasi server-side sebelum event ini di-dispatch.
 *
 * Payload:
 *   bill_id        — ID tagihan di student_bills
 *   student_id     — ID siswa di student_profiles
 *   nis            — NIS siswa (cross-system identity)
 *   amount         — Nominal yang dibayar (DECIMAL string, bukan float)
 *   payment_method — Metode pembayaran (MIDTRANS, dll)
 *   provider       — Provider gateway (midtrans)
 *   order_id       — Midtrans order ID
 *   paid_at        — Timestamp pembayaran
 */
class PaymentReceived
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly int    $billId,
        public readonly int    $studentId,
        public readonly string $nis,
        public readonly string $amount,       // string DECIMAL — bukan float
        public readonly string $paymentMethod,
        public readonly string $provider,
        public readonly string $orderId,
        public readonly string $paidAt,
    ) {}

    /**
     * Konversi ke array untuk queue payload / logging.
     * TIDAK mengandung secret.
     */
    public function toArray(): array
    {
        return [
            'bill_id'        => $this->billId,
            'student_id'     => $this->studentId,
            'nis'            => $this->nis,
            'amount'         => $this->amount,
            'payment_method' => $this->paymentMethod,
            'provider'       => $this->provider,
            'order_id'       => $this->orderId,
            'paid_at'        => $this->paidAt,
        ];
    }
}
