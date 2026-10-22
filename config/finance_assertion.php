<?php

/**
 * Finance Assertion Verification Configuration — TU Backend
 *
 * Public key disimpan di file terpisah (storage/keys/) bukan di .env,
 * karena RSA PEM multiline tidak kompatibel dengan dotenv parser.
 *
 * KEY ROTATION:
 *   Update file di TU_KAFFAH_FINANCE_ASSERTION_KEY_PATH bersamaan dengan
 *   rotasi private key di Kaffah.
 */
return [
    /*
     * RSA public key dari Kaffah untuk verifikasi assertion signature.
     * Dibaca dari file path — tidak disimpan langsung di .env.
     */
    'public_key' => (function () {
        $path = env('TU_KAFFAH_FINANCE_ASSERTION_KEY_PATH', '');
        if (empty($path)) {
            return '';
        }
        $fullPath = str_starts_with($path, '/') ? $path : base_path($path);
        return file_exists($fullPath) ? file_get_contents($fullPath) : '';
    })(),

    /*
     * Audience yang diterima TU.
     */
    'expected_audience' => 'tu-finance',

    /*
     * Algorithm yang diterima — explicit whitelist mencegah algorithm confusion.
     */
    'expected_algorithm' => 'RS256',

    /*
     * Clock skew tolerance (detik).
     */
    'clock_skew_seconds' => 30,

    /*
     * TU finance token expiry (jam).
     */
    'finance_token_expiry_hours' => (int) env('TU_FINANCE_TOKEN_EXPIRY_HOURS', 24),

    /*
     * Nonce TTL di database (menit).
     */
    'nonce_ttl_minutes' => 10,
];
