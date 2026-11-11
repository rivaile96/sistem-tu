<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | DOKU Payment Gateway Configuration (Official Single Gateway)
    |--------------------------------------------------------------------------
    */
    'doku' => [
        'client_id'        => env('DOKU_CLIENT_ID'),
        'client_secret'    => env('DOKU_CLIENT_SECRET'),
        'private_key'      => env('DOKU_PRIVATE_KEY'),
        'public_key'       => env('DOKU_PUBLIC_KEY'),
        'is_production'    => (bool) env('DOKU_IS_PRODUCTION', false),
        'base_url'         => env('DOKU_API_BASE_URL', 'https://api-sandbox.doku.com'),
        'notification_url' => env('DOKU_NOTIFICATION_URL', 'http://localhost:8001/api/doku/notification'),
        'timeout_seconds'  => (int) env('DOKU_TIMEOUT_SECONDS', 30),
    ],

    /*
    |--------------------------------------------------------------------------
    | Midtrans Configuration (HISTORICAL ONLY - READ ONLY VERIFICATION)
    |--------------------------------------------------------------------------
    */
    'midtrans' => [
        'server_key'     => env('MIDTRANS_SERVER_KEY'),
        'client_key'     => env('MIDTRANS_CLIENT_KEY'),
        'is_production'  => env('MIDTRANS_IS_PRODUCTION', false),
        'is_sanitized'   => env('MIDTRANS_IS_SANITIZED', true),
        'is_3ds'         => env('MIDTRANS_IS_3DS', true),
    ],

    // TU Sync — service-to-service auth dari Kaffah Backend.
    // Secret harus sama dengan TU_SYNC_SECRET di kaffah-backend/.env.
    // Fail-closed: jika kosong, semua request ke /api/internal/sync-student ditolak.
    'tu_sync' => [
        'secret' => env('TU_SYNC_SECRET'),
    ],

    // Kaffah Backend — internal service integration (TU -> Central).
    // SEC-05: Tanpa fallback URL tebakan. Wajib dikonfigurasi eksplisit via KAFFAH_CORE_URL.
    'kaffah' => [
        'url'        => env('KAFFAH_CORE_URL'),
        'api_secret' => env('KAFFAH_API_SECRET'),
    ],

];
