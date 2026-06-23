<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Mobile API Key
    |--------------------------------------------------------------------------
    |
    | Required for all /api requests through EnsureApiKey middleware.
    | Send using header: X-API-KEY.
    |
    */
    'mobile_key' => env('MOBILE_API_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | API Rate Limits (Per Minute)
    |--------------------------------------------------------------------------
    */
    'rate_limits' => [
        'public_per_minute' => (int) env('API_RATE_LIMIT_PUBLIC', 120),
        'authenticated_per_minute' => (int) env('API_RATE_LIMIT_AUTHENTICATED', 180),
        'auth_sensitive_per_minute' => (int) env('API_RATE_LIMIT_AUTH_SENSITIVE', 20),
        'midtrans_webhook_per_minute' => (int) env('API_RATE_LIMIT_MIDTRANS_WEBHOOK', 120),
    ],

    /*
    |--------------------------------------------------------------------------
    | Idempotency
    |--------------------------------------------------------------------------
    */
    'idempotency' => [
        'ttl_seconds' => (int) env('IDEMPOTENCY_TTL_SECONDS', 600),
        'max_body_bytes' => (int) env('IDEMPOTENCY_MAX_BODY_BYTES', 65535),
    ],

    /*
    |--------------------------------------------------------------------------
    | Schedule
    |--------------------------------------------------------------------------
    */
    'schedule' => [
        'timezone' => env('SCHEDULE_TIMEZONE', 'Asia/Jakarta'),
    ],
];
