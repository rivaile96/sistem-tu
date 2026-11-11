<?php

namespace App\Services\Doku;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * DokuClient — Klien resmi dan terpusat untuk DOKU Direct API & SNAP.
 *
 * Mendukung:
 * - B2B Access Token via RSA-SHA256 asymmetric signature
 * - SNAP Symmetric transaction signature via HMAC-SHA512
 * - Webhook/Notification signature verification
 * - SNAP Virtual Account creation
 * - SNAP QRIS generation
 * - DOKU Checkout creation
 * - Transaction Status Inquiry
 */
class DokuClient
{
    private DokuConfig $config;
    private static bool $mockMode = false;
    private static ?array $mockResponses = null;

    public function __construct(?DokuConfig $config = null)
    {
        $this->config = $config ?? DokuConfig::fromConfig();
    }

    public static function enableMock(array $customResponses = []): void
    {
        self::$mockMode = true;
        self::$mockResponses = $customResponses;
    }

    public static function disableMock(): void
    {
        self::$mockMode = false;
        self::$mockResponses = null;
    }

    public function getConfig(): DokuConfig
    {
        return $this->config;
    }

    /**
     * Dapatkan B2B Access Token dengan caching otomatis.
     */
    public function getB2bAccessToken(): string
    {
        if (self::$mockMode) {
            return 'mock_doku_b2b_access_token_' . md5($this->config->clientId);
        }

        $cacheKey = 'doku_b2b_token_' . md5($this->config->clientId);
        $cached = Cache::get($cacheKey);
        if ($cached && is_string($cached)) {
            return $cached;
        }

        $timestamp = now()->toIso8601String();
        $stringToSign = $this->config->clientId . '|' . $timestamp;

        $privateKey = $this->config->privateKey;
        if (empty($privateKey)) {
            throw new \RuntimeException('DOKU private key belum dikonfigurasi.');
        }

        if (!$this->config->isPrivateKeyValid()) {
            throw new \RuntimeException('DOKU private key tidak valid atau tidak dapat di-load oleh OpenSSL.');
        }

        $binarySignature = '';
        $signed = openssl_sign($stringToSign, $binarySignature, $privateKey, OPENSSL_ALGO_SHA256);
        if (!$signed) {
            throw new \RuntimeException('Gagal menandatangani B2B token dengan RSA-SHA256: ' . openssl_error_string());
        }

        $signature = base64_encode($binarySignature);
        $url = $this->config->getBaseUrl() . '/authorization/v1/access-token/b2b';

        $response = Http::timeout(20)
            ->withHeaders([
                'X-CLIENT-KEY' => $this->config->clientId,
                'X-TIMESTAMP'  => $timestamp,
                'X-SIGNATURE'  => $signature,
                'Content-Type' => 'application/json',
            ])
            ->post($url, [
                'grantType' => 'client_credentials',
            ]);

        if (!$response->successful()) {
            Log::error('DOKU B2B Access Token request failed', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            throw new \RuntimeException('Gagal mendapatkan DOKU B2B Access Token (HTTP ' . $response->status() . ')');
        }

        $data = $response->json();
        $token = $data['accessToken'] ?? $data['access_token'] ?? null;
        $expiresIn = (int) ($data['expiresIn'] ?? $data['expires_in'] ?? 900);

        if (!$token) {
            throw new \RuntimeException('DOKU response tidak mengembalikan access token.');
        }

        $ttl = max(60, $expiresIn - 60);
        Cache::put($cacheKey, $token, $ttl);

        return $token;
    }

    /**
     * Hitung HMAC-SHA512 SNAP Symmetric Signature untuk transaksi.
     */
    public function generateTransactionSignature(
        string $httpMethod,
        string $relativeUrl,
        string $accessToken,
        array|string $body,
        string $timestamp
    ): string {
        $minifiedBody = is_array($body) ? json_encode($body, JSON_UNESCAPED_SLASHES) : $body;
        $bodyHash = strtolower(hash('sha256', (string) $minifiedBody));

        // Format SNAP: HTTPMethod + ":" + RelativeUrl + ":" + AccessToken + ":" + LowercaseHexSha256(Body) + ":" + Timestamp
        $stringToSign = strtoupper($httpMethod) . ':' . $relativeUrl . ':' . $accessToken . ':' . $bodyHash . ':' . $timestamp;

        return base64_encode(hash_hmac('sha512', $stringToSign, $this->config->clientSecret, true));
    }

    /**
     * Verifikasi signature pada webhook/notification masuk dari DOKU.
     * Menggunakan jalur verifikasi eksplisit terpisah:
     * 1. Direct API V2: HMAC-SHA256 (Headers: Client-Id, Request-Id, Request-Timestamp, Signature)
     * 2. SNAP: HMAC-SHA512 (Headers: X-SIGNATURE, X-TIMESTAMP, X-PARTNER-ID)
     *
     * Catatan Keamanan: HMAC secret SELALU menggunakan clientSecret (Bukan public key).
     */
    public function verifyNotificationSignature(Request $request, string $relativeUrl): bool
    {
        if (self::$mockMode && isset(self::$mockResponses['verify_signature'])) {
            return (bool) self::$mockResponses['verify_signature'];
        }

        $clientSecret = $this->config->clientSecret;
        if (empty($clientSecret)) {
            Log::error('DOKU client secret belum dikonfigurasi untuk verifikasi webhook.');
            return false;
        }

        $rawBody = (string) $request->getContent();

        // 1. Cek Jalur Non-SNAP Direct API V2
        if ($this->isDirectApiV2Notification($request)) {
            return $this->verifyDirectApiV2Signature($request, $relativeUrl, $clientSecret, $rawBody);
        }

        // 2. Cek Jalur SNAP
        if ($this->isSnapNotification($request)) {
            return $this->verifySnapSignature($request, $relativeUrl, $clientSecret, $rawBody);
        }

        Log::warning('DOKU notification missing recognizable signature headers', [
            'relative_url' => $relativeUrl,
            'header_keys'  => array_keys($request->headers->all()),
        ]);

        return false;
    }

    /**
     * Verifikasi Signature DOKU Non-SNAP Direct API V2 (HMAC-SHA256).
     * Referensi: https://developers.doku.com/get-started-with-doku-api/notification/http-notification-sample-non-snap
     */
    public function verifyDirectApiV2Signature(Request $request, string $relativeUrl, string $clientSecret, string $rawBody): bool
    {
        $incomingSignature = $request->header('Signature') ?? $request->header('signature');
        $timestamp = $request->header('Request-Timestamp') ?? $request->header('request-timestamp');
        $clientId = $request->header('Client-Id') ?? $request->header('client-id');
        $requestId = $request->header('Request-Id') ?? $request->header('request-id') ?? '';

        if (empty($incomingSignature) || empty($timestamp) || empty($clientId)) {
            Log::warning('DOKU Direct API V2 notification missing required headers', [
                'has_signature' => !empty($incomingSignature),
                'has_timestamp' => !empty($timestamp),
                'has_client_id' => !empty($clientId),
            ]);
            return false;
        }

        // Verifikasi kecocokan Client-Id dengan konfigurasi merchant
        if (!empty($this->config->clientId) && $clientId !== $this->config->clientId) {
            Log::warning('DOKU Direct API V2 Client-Id mismatch', [
                'expected' => $this->config->clientId,
                'received' => $clientId,
            ]);
            return false;
        }

        // Component Digest: Base64(SHA256(RawBody))
        $digest = base64_encode(hash('sha256', $rawBody, true));

        // String to sign:
        // Client-Id:{Client-Id}\nRequest-Id:{Request-Id}\nRequest-Timestamp:{Request-Timestamp}\nRequest-Target:{Request-Target}\nDigest:{Digest}
        $stringToSign = "Client-Id:" . $clientId . "\n"
            . "Request-Id:" . $requestId . "\n"
            . "Request-Timestamp:" . $timestamp . "\n"
            . "Request-Target:" . $relativeUrl . "\n"
            . "Digest:" . $digest;

        $expectedWithPrefix = 'HMACSHA256=' . base64_encode(hash_hmac('sha256', $stringToSign, $clientSecret, true));
        $expectedWithoutPrefix = base64_encode(hash_hmac('sha256', $stringToSign, $clientSecret, true));

        if (hash_equals($expectedWithPrefix, $incomingSignature) || hash_equals($expectedWithoutPrefix, $incomingSignature)) {
            return true;
        }

        Log::warning('DOKU Direct API V2 signature mismatch', [
            'relative_url' => $relativeUrl,
            'timestamp'    => $timestamp,
        ]);

        return false;
    }

    /**
     * Verifikasi Signature DOKU SNAP (HMAC-SHA512).
     * Referensi: https://developers.doku.com/accept-payments/direct-api/snap/integration-guide
     */
    public function verifySnapSignature(Request $request, string $relativeUrl, string $clientSecret, string $rawBody): bool
    {
        $incomingSignature = $request->header('X-SIGNATURE') ?? $request->header('x-signature');
        $timestamp = $request->header('X-TIMESTAMP') ?? $request->header('x-timestamp');
        $partnerId = $request->header('X-PARTNER-ID') ?? $request->header('x-partner-id');

        if (empty($incomingSignature) || empty($timestamp)) {
            Log::warning('DOKU SNAP notification missing X-SIGNATURE or X-TIMESTAMP');
            return false;
        }

        // Optional partner ID check
        if (!empty($partnerId) && !empty($this->config->clientId) && $partnerId !== $this->config->clientId) {
            Log::warning('DOKU SNAP X-PARTNER-ID mismatch', [
                'expected' => $this->config->clientId,
                'received' => $partnerId,
            ]);
            return false;
        }

        $bodyHash = strtolower(hash('sha256', $rawBody));

        // Authorization header token if present
        $authHeader = $request->header('Authorization', '');
        $token = '';
        if (str_starts_with($authHeader, 'Bearer ')) {
            $token = substr($authHeader, 7);
        }

        // Try standard SNAP pattern with token: POST:{url}:{token}:{bodyHash}:{timestamp}
        if (!empty($token)) {
            $stringToSignWithToken = 'POST:' . $relativeUrl . ':' . $token . ':' . $bodyHash . ':' . $timestamp;
            $expectedSignatureWithToken = base64_encode(hash_hmac('sha512', $stringToSignWithToken, $clientSecret, true));

            if (hash_equals($expectedSignatureWithToken, $incomingSignature)) {
                return true;
            }
        }

        // SNAP symmetric notification pattern without token: POST:{url}:{bodyHash}:{timestamp}
        $stringToSignNoToken = 'POST:' . $relativeUrl . ':' . $bodyHash . ':' . $timestamp;
        $expectedSignatureNoToken = base64_encode(hash_hmac('sha512', $stringToSignNoToken, $clientSecret, true));

        if (hash_equals($expectedSignatureNoToken, $incomingSignature)) {
            return true;
        }

        Log::warning('DOKU SNAP signature mismatch', [
            'relative_url' => $relativeUrl,
            'timestamp'    => $timestamp,
        ]);

        return false;
    }

    public function isDirectApiV2Notification(Request $request): bool
    {
        return $request->hasHeader('Signature') || $request->hasHeader('Request-Timestamp');
    }

    public function isSnapNotification(Request $request): bool
    {
        return $request->hasHeader('X-SIGNATURE') || $request->hasHeader('X-TIMESTAMP');
    }

    /**
     * Buat Virtual Account via DOKU Direct API / SNAP.
     *
     * @param array{
     *   order_id: string,
     *   amount: int|float,
     *   customer_name: string,
     *   customer_phone?: string,
     *   customer_email?: string,
     *   bank: string,
     *   expiry_minutes?: int
     * } $params
     */
    public function createVirtualAccount(array $params): array
    {
        $orderId = (string) $params['order_id'];
        $amount  = (int) $params['amount'];
        $bank    = strtolower($params['bank'] ?? 'bca');
        $expiryMinutes = (int) ($params['expiry_minutes'] ?? 1440); // 24 hours

        if (self::$mockMode || !$this->config->isConfigured()) {
            $prefix = match ($bank) {
                'bca'     => '8012',
                'bni'     => '8808',
                'bri'     => '1280',
                'mandiri' => '8956',
                default   => '9000',
            };
            $vaNumber = $prefix . str_pad(substr(preg_replace('/\D/', '', $orderId) ?: '12345678', 0, 12), 12, '0', STR_PAD_RIGHT);

            return [
                'success'         => true,
                'order_id'        => $orderId,
                'va_number'       => $vaNumber,
                'payment_code'    => $vaNumber,
                'amount'          => $amount,
                'bank'            => strtoupper($bank),
                'expiry_time'     => now()->addMinutes($expiryMinutes)->toIso8601String(),
                'reference_id'    => 'DOKU-VA-' . $orderId,
                'raw'             => ['mock' => true],
            ];
        }

        $bankEndpointMap = [
            'bca'     => '/bca-virtual-account/v2/payment-code',
            'bni'     => '/bni-virtual-account/v2/payment-code',
            'bri'     => '/bri-virtual-account/v2/payment-code',
            'mandiri' => '/mandiri-virtual-account/v2/payment-code',
            'bsi'     => '/bsm-virtual-account/v2/payment-code',
            'bsm'     => '/bsm-virtual-account/v2/payment-code',
            'cimb'    => '/cimb-virtual-account/v2/payment-code',
            'danamon' => '/danamon-virtual-account/v2/payment-code',
        ];

        $relativeUrl = $bankEndpointMap[$bank] ?? '/bca-virtual-account/v2/payment-code';
        $timestamp = gmdate('Y-m-d\TH:i:s\Z');
        $requestId = (string) Str::uuid();

        $payload = [
            'order' => [
                'amount'         => (int) $amount,
                'invoice_number' => $orderId,
            ],
            'virtual_account_info' => [
                'expired_time'    => $expiryMinutes,
                'reusable_status' => false,
                'info1'           => 'KAFFAH',
            ],
            'customer' => [
                'name'  => substr($params['customer_name'] ?? 'Kaffah Student', 0, 30),
                'email' => $params['customer_email'] ?? 'finance@kaffah.sch.id',
            ],
        ];

        if ($bank === 'bni') {
            $payload['virtual_account_info']['merchant_unique_reference'] = substr(preg_replace('/\D/', '', $orderId) ?: (string) time(), -12);
        }

        $minifiedBody = json_encode($payload, JSON_UNESCAPED_SLASHES);
        $digest = base64_encode(hash('sha256', $minifiedBody, true));
        $stringToSign = "Client-Id:" . $this->config->clientId . "\n"
            . "Request-Id:" . $requestId . "\n"
            . "Request-Timestamp:" . $timestamp . "\n"
            . "Request-Target:" . $relativeUrl . "\n"
            . "Digest:" . $digest;
        $signature = 'HMACSHA256=' . base64_encode(hash_hmac('sha256', $stringToSign, $this->config->clientSecret, true));

        $response = Http::timeout(25)
            ->withHeaders([
                'Client-Id'         => $this->config->clientId,
                'Request-Id'        => $requestId,
                'Request-Timestamp' => $timestamp,
                'Signature'         => $signature,
                'Content-Type'      => 'application/json',
            ])
            ->post($this->config->getBaseUrl() . $relativeUrl, $payload);

        if (!$response->successful()) {
            Log::error('DOKU Create VA Error', ['body' => $response->body(), 'status' => $response->status()]);
            throw new \RuntimeException('DOKU Virtual Account gagal dibuat: ' . ($response->json('error.message') ?? $response->body()));
        }

        $resData = $response->json();
        $vaData = $resData['virtual_account_info'] ?? ($resData['virtualAccountData'] ?? $resData);
        $vaNumber = $vaData['virtual_account_number'] ?? ($vaData['virtualAccountNo'] ?? ($vaData['vaNumber'] ?? ''));

        return [
            'success'      => true,
            'order_id'     => $orderId,
            'va_number'    => $vaNumber,
            'payment_code' => $vaNumber,
            'amount'       => $amount,
            'bank'         => strtoupper($bank),
            'expiry_time'  => $vaData['expired_date_utc'] ?? ($vaData['expiredDate'] ?? now()->addMinutes($expiryMinutes)->toIso8601String()),
            'reference_id' => $requestId,
            'how_to_pay'   => $vaData['how_to_pay_page'] ?? null,
            'raw'          => $resData,
        ];
    }

    /**
     * Buat QRIS dinamis via DOKU SNAP.
     *
     * @param array{
     *   order_id: string,
     *   amount: int|float,
     *   customer_name: string,
     *   expiry_minutes?: int
     * } $params
     */
    public function generateQris(array $params): array
    {
        $orderId = (string) $params['order_id'];
        $amount  = (int) $params['amount'];
        $expiryMinutes = (int) ($params['expiry_minutes'] ?? 1440); // 24 hours default

        if (self::$mockMode || !$this->config->isConfigured()) {
            $qrContent = '00020101021226600016ID.DOKU.WWW01189360091100000000000215' . $orderId . '520458125303360540' . $amount . '5802ID5911KAFFAH SCH6007BANDUNG6304ABCD';
            return [
                'success'      => true,
                'order_id'     => $orderId,
                'qr_content'   => $qrContent,
                'qr_url'       => 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' . urlencode($qrContent),
                'amount'       => $amount,
                'expiry_time'  => now()->addMinutes($expiryMinutes)->toIso8601String(),
                'reference_id' => 'DOKU-QR-' . $orderId,
                'raw'          => ['mock' => true],
            ];
        }

        $token = $this->getB2bAccessToken();
        $timestamp = now()->toIso8601String();
        $relativeUrl = '/snap-adapter/b2b/v1.0/qr/qr-mpm-generate';

        $payload = [
            'partnerReferenceNo' => $orderId,
            'amount' => [
                'value'    => number_format($amount, 2, '.', ''),
                'currency' => 'IDR',
            ],
            'merchantId'   => $this->config->clientId,
            'terminalId'   => 'A01',
            'validityPeriod'=> now()->addMinutes($expiryMinutes)->toIso8601String(),
            'additionalInfo'=> [
                'postalCode' => '40115',
            ],
        ];

        $signature = $this->generateTransactionSignature('POST', $relativeUrl, $token, $payload, $timestamp);

        $response = Http::timeout(25)
            ->withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'X-TIMESTAMP'   => $timestamp,
                'X-SIGNATURE'   => $signature,
                'X-PARTNER-ID'  => $this->config->clientId,
                'X-EXTERNAL-ID' => (string) Str::uuid(),
                'CHANNEL-ID'    => 'H2H',
                'Content-Type'  => 'application/json',
            ])
            ->post($this->config->getBaseUrl() . $relativeUrl, $payload);

        if (!$response->successful()) {
            Log::error('DOKU Generate QRIS Error', ['body' => $response->body(), 'status' => $response->status()]);
            throw new \RuntimeException('DOKU QRIS gagal dibuat: ' . ($response->json('responseMessage') ?? $response->body()));
        }

        $resData = $response->json();
        $qrContent = $resData['qrContent'] ?? $resData['qr_content'] ?? '';

        return [
            'success'      => true,
            'order_id'     => $orderId,
            'qr_content'   => $qrContent,
            'qr_url'       => 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' . urlencode($qrContent),
            'amount'       => $amount,
            'expiry_time'  => $resData['validityPeriod'] ?? now()->addMinutes($expiryMinutes)->toIso8601String(),
            'reference_id' => $resData['referenceNo'] ?? $orderId,
            'raw'          => $resData,
        ];
    }

    /**
     * Buat Sesi Pembayaran DOKU Checkout (Hosted Payment Page / Web).
     *
     * @param array{
     *   order_id: string,
     *   amount: int|float,
     *   customer_name: string,
     *   customer_email?: string,
     *   callback_url?: string,
     *   expiry_minutes?: int
     * } $params
     */
    public function createCheckout(array $params): array
    {
        $orderId = (string) $params['order_id'];
        $amount  = (int) $params['amount'];
        $expiryMinutes = (int) ($params['expiry_minutes'] ?? 1440);

        if (self::$mockMode || !$this->config->isConfigured()) {
            return [
                'success'      => true,
                'order_id'     => $orderId,
                'payment_url'  => 'https://sandbox.doku.com/checkout/link/mock-' . md5($orderId),
                'amount'       => $amount,
                'expiry_time'  => now()->addMinutes($expiryMinutes)->toIso8601String(),
                'reference_id' => 'DOKU-CHK-' . $orderId,
                'raw'          => ['mock' => true],
            ];
        }

        $timestamp = gmdate('Y-m-d\TH:i:s\Z');
        $requestId = (string) Str::uuid();
        $relativeUrl = '/checkout/v1/payment';

        $payload = [
            'order' => [
                'amount'         => $amount,
                'invoice_number' => $orderId,
                'currency'       => 'IDR',
                'callback_url'   => $params['callback_url'] ?? $this->config->notificationUrl,
                'auto_redirect'  => true,
            ],
            'payment' => [
                'payment_due_date' => $expiryMinutes,
            ],
            'customer' => [
                'name'  => $params['customer_name'] ?? 'Kaffah Student',
                'email' => $params['customer_email'] ?? 'finance@kaffah.sch.id',
            ],
        ];

        $minifiedBody = json_encode($payload, JSON_UNESCAPED_SLASHES);
        $digest = base64_encode(hash('sha256', $minifiedBody, true));
        $stringToSign = "Client-Id:" . $this->config->clientId . "\n"
            . "Request-Id:" . $requestId . "\n"
            . "Request-Timestamp:" . $timestamp . "\n"
            . "Request-Target:" . $relativeUrl . "\n"
            . "Digest:" . $digest;
        $signature = 'HMACSHA256=' . base64_encode(hash_hmac('sha256', $stringToSign, $this->config->clientSecret, true));

        $response = Http::timeout(25)
            ->withHeaders([
                'Client-Id'         => $this->config->clientId,
                'Request-Id'        => $requestId,
                'Request-Timestamp' => $timestamp,
                'Signature'         => $signature,
                'Content-Type'      => 'application/json',
            ])
            ->post($this->config->getBaseUrl() . $relativeUrl, $payload);

        if (!$response->successful()) {
            Log::error('DOKU Create Checkout Error', ['body' => $response->body(), 'status' => $response->status()]);
            throw new \RuntimeException('DOKU Checkout gagal dibuat: ' . $response->body());
        }

        $resData = $response->json();
        $paymentUrl = $resData['response']['payment']['url'] ?? '';

        return [
            'success'      => true,
            'order_id'     => $orderId,
            'payment_url'  => $paymentUrl,
            'amount'       => $amount,
            'expiry_time'  => now()->addMinutes($expiryMinutes)->toIso8601String(),
            'reference_id' => $resData['response']['uuid'] ?? $orderId,
            'raw'          => $resData,
        ];
    }

    /**
     * Batalkan sesi pembayaran pada DOKU gateway.
     *
     * @param mixed $order PaymentOrder|array
     * @return array{success: bool, status: string, raw: mixed}
     */
    public function cancelOrder(mixed $order): array
    {
        $orderId = is_array($order) ? ($order['order_id'] ?? '') : ($order->order_id ?? '');

        if (self::$mockMode) {
            if (isset(self::$mockResponses['cancel_order'])) {
                return self::$mockResponses['cancel_order'];
            }
            return [
                'success' => true,
                'status'  => 'CANCELLED',
                'raw'     => ['mock' => true, 'order_id' => $orderId],
            ];
        }

        if (!$this->config->isConfigured()) {
            return [
                'success' => false,
                'status'  => 'UNCONFIGURED',
                'raw'     => ['error' => 'DOKU credentials unconfigured'],
            ];
        }

        $lastResponseStatus = null;
        $lastResponseBody = null;

        try {
            // 1. Cek inquiry terlebih dahulu untuk mendapatkan original_request_id
            $inquiry = $this->inquiryOrder($orderId);
            $origReqId = $inquiry['raw']['transaction']['original_request_id'] ?? null;

            if ($origReqId) {
                $relativeUrl = '/checkout/v3/cancellations';
                $timestamp = gmdate('Y-m-d\TH:i:s\Z');
                $requestId = (string) Str::uuid();
                $cancelPayload = [
                    'order'   => ['invoice_number' => $orderId],
                    'payment' => ['original_request_id' => $origReqId],
                    'note'    => 'Customer cancelled / switched payment method',
                ];
                $minifiedBody = json_encode($cancelPayload, JSON_UNESCAPED_SLASHES);
                $digest = base64_encode(hash('sha256', $minifiedBody, true));
                $stringToSign = "Client-Id:" . $this->config->clientId . "\n"
                    . "Request-Id:" . $requestId . "\n"
                    . "Request-Timestamp:" . $timestamp . "\n"
                    . "Request-Target:" . $relativeUrl . "\n"
                    . "Digest:" . $digest;
                $signature = 'HMACSHA256=' . base64_encode(hash_hmac('sha256', $stringToSign, $this->config->clientSecret, true));

                $response = Http::timeout(15)
                    ->withHeaders([
                        'Client-Id'         => $this->config->clientId,
                        'Request-Id'        => $requestId,
                        'Request-Timestamp' => $timestamp,
                        'Signature'         => $signature,
                        'Content-Type'      => 'application/json',
                    ])
                    ->post($this->config->getBaseUrl() . $relativeUrl, $cancelPayload);

                $lastResponseStatus = $response->status();
                $lastResponseBody = $response->body();

                if ($response->successful()) {
                    return [
                        'success' => true,
                        'status'  => 'CANCELLED',
                        'raw'     => $response->json(),
                    ];
                }

                Log::info('DokuClient::cancelOrder checkout cancellations response', [
                    'order_id' => $orderId,
                    'status'   => $response->status(),
                    'body'     => $response->body(),
                ]);
            }

            // 2. Alternatif: coba SNAP QR expire bila token tersedia
            if ($this->config->isPrivateKeyValid()) {
                try {
                    $token = $this->getB2bAccessToken();
                    $timestamp = now()->toIso8601String();
                    $relativeUrl = '/snap-adapter/b2b/v1.0/qr/qr-expire';

                    $payload = [
                        'partnerReferenceNo' => $orderId,
                        'reason'             => 'Customer switched payment method',
                    ];

                    $signature = $this->generateTransactionSignature('POST', $relativeUrl, $token, $payload, $timestamp);

                    $response = Http::timeout(15)
                        ->withHeaders([
                            'Authorization' => 'Bearer ' . $token,
                            'X-TIMESTAMP'   => $timestamp,
                            'X-SIGNATURE'   => $signature,
                            'X-PARTNER-ID'  => $this->config->clientId,
                            'X-EXTERNAL-ID' => (string) Str::uuid(),
                            'CHANNEL-ID'    => 'H2H',
                            'Content-Type'  => 'application/json',
                        ])
                        ->post($this->config->getBaseUrl() . $relativeUrl, $payload);

                    $lastResponseStatus = $response->status();
                    $lastResponseBody = $response->body();

                    if ($response->successful()) {
                        return [
                            'success' => true,
                            'status'  => 'CANCELLED',
                            'raw'     => $response->json(),
                        ];
                    }
                } catch (\Throwable $snapErr) {
                    Log::info('DokuClient::cancelOrder snap expire skipped: ' . $snapErr->getMessage());
                }
            }

            // Provider tidak mengonfirmasi pembatalan (misal HTTP 400 / fitur belum aktif di sandbox)
            // Sesuai aturan keselamatan: jangan anggap HTTP 400 sebagai keberhasilan!
            Log::warning('DokuClient::cancelOrder provider rejected cancellation', [
                'order_id'    => $orderId,
                'http_status' => $lastResponseStatus,
                'body'        => $lastResponseBody,
            ]);

            return [
                'success'     => false,
                'status'      => 'REJECTED',
                'http_status' => $lastResponseStatus,
                'raw'         => ['status' => 'REJECTED', 'message' => 'Provider rejected or does not support cancellation'],
            ];

        } catch (\Throwable $e) {
            Log::warning('DokuClient::cancelOrder exception (network timeout or connection error)', [
                'order_id' => $orderId,
                'error'    => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'status'  => 'UNCERTAIN',
                'error'   => $e->getMessage(),
                'raw'     => ['error' => $e->getMessage()],
            ];
        }
    }

    /**
     * Periksa status transaksi ke DOKU gateway.
     *
     * @param string $orderId
     * @return array
     */
    public function inquiryOrder(string $orderId): array
    {
        if (self::$mockMode || !$this->config->isConfigured()) {
            return [
                'success'        => true,
                'order_id'       => $orderId,
                'payment_status' => 'PENDING',
                'raw'            => ['mock' => true],
            ];
        }

        $relativeUrl = '/orders/v1/status/' . $orderId;
        $timestamp = gmdate('Y-m-d\TH:i:s\Z');
        $requestId = (string) Str::uuid();

        $stringToSign = "Client-Id:" . $this->config->clientId . "\n"
            . "Request-Id:" . $requestId . "\n"
            . "Request-Timestamp:" . $timestamp . "\n"
            . "Request-Target:" . $relativeUrl;
        $signature = 'HMACSHA256=' . base64_encode(hash_hmac('sha256', $stringToSign, $this->config->clientSecret, true));

        try {
            $response = Http::timeout(15)
                ->withHeaders([
                    'Client-Id'         => $this->config->clientId,
                    'Request-Id'        => $requestId,
                    'Request-Timestamp' => $timestamp,
                    'Signature'         => $signature,
                ])
                ->get($this->config->getBaseUrl() . $relativeUrl);

            if ($response->successful()) {
                $data = $response->json();
                $status = $data['transaction']['status'] ?? ($data['status'] ?? 'PENDING');
                return [
                    'success'        => true,
                    'order_id'       => $orderId,
                    'payment_status' => strtoupper($status),
                    'raw'            => $data,
                ];
            }

            return [
                'success'        => false,
                'order_id'       => $orderId,
                'payment_status' => 'UNKNOWN',
                'raw'            => $response->json() ?? $response->body(),
            ];
        } catch (\Throwable $e) {
            Log::error('DokuClient::inquiryOrder error', ['order_id' => $orderId, 'error' => $e->getMessage()]);
            return [
                'success'        => false,
                'order_id'       => $orderId,
                'payment_status' => 'ERROR',
                'error'          => $e->getMessage(),
            ];
        }
    }
}
