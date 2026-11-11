<?php

namespace App\Services\Doku;

/**
 * DokuConfig — Nilai konfigurasi terpusat untuk DOKU Direct API & SNAP.
 */
class DokuConfig
{
    public readonly string $clientId;
    public readonly string $clientSecret;
    public readonly string $privateKey;
    public readonly string $publicKey;
    public readonly bool   $isProduction;
    public readonly string $notificationUrl;

    public function __construct(
        ?string $clientId = null,
        ?string $clientSecret = null,
        ?string $privateKey = null,
        ?string $publicKey = null,
        ?bool   $isProduction = null,
        ?string $notificationUrl = null,
    ) {
        $rawKey = $privateKey ?? config('services.doku.private_key');
        $this->clientId        = $clientId ?? (string) config('services.doku.client_id', '');
        $this->clientSecret    = $clientSecret ?? (string) config('services.doku.client_secret', '');
        $this->privateKey      = self::formatPrivateKey($rawKey);
        $this->publicKey       = $publicKey ?? (string) config('services.doku.public_key', '');
        $this->isProduction    = $isProduction ?? (bool) config('services.doku.is_production', false);
        $this->notificationUrl = $notificationUrl ?? (string) config('services.doku.notification_url', '');
    }

    public static function fromConfig(): self
    {
        return new self();
    }

    public function getClientId(): string
    {
        return $this->clientId;
    }

    public function getClientSecret(): string
    {
        return $this->clientSecret;
    }

    public function isProduction(): bool
    {
        return $this->isProduction;
    }

    public function getBaseUrl(): string
    {
        return (string) config(
            'services.doku.base_url',
            $this->isProduction ? 'https://api.doku.com' : 'https://api-sandbox.doku.com'
        );
    }

    public function isConfigured(): bool
    {
        return !empty($this->clientId) && !empty($this->clientSecret);
    }

    /**
     * Periksa apakah RSA private key dapat di-load oleh OpenSSL.
     */
    public function isPrivateKeyValid(): bool
    {
        if (empty($this->privateKey)) {
            return false;
        }

        $res = openssl_pkey_get_private($this->privateKey);
        return $res !== false;
    }

    public static function formatPrivateKey(?string $key): string
    {
        $key = trim((string) $key);
        if (empty($key)) {
            // Cek file default sandbox di storage/keys/
            $defaultKey = storage_path('keys/doku_sandbox_private.pem');
            if (file_exists($defaultKey) && is_readable($defaultKey)) {
                return (string) file_get_contents($defaultKey);
            }
            return '';
        }

        // If file path given
        if (file_exists($key) && is_readable($key)) {
            return (string) file_get_contents($key);
        }

        // If contains PEM header
        if (str_contains($key, '-----BEGIN')) {
            return $key;
        }

        // If base64 encoded raw key or plain text without headers
        return "-----BEGIN RSA PRIVATE KEY-----\n" . chunk_split($key, 64, "\n") . "-----END RSA PRIVATE KEY-----\n";
    }
}
