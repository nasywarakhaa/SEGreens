<?php

return [
    'merchant_id' => env('MIDTRANS_MERCHANT_ID', ''),
    'client_key' => env('MIDTRANS_CLIENT_KEY', ''),
    'server_key' => env('MIDTRANS_SERVER_KEY', ''),
    'is_production' => filter_var(env('MIDTRANS_IS_PRODUCTION', false), FILTER_VALIDATE_BOOL),

    /*
    |--------------------------------------------------------------------------
    | Optional Finish Redirect URL
    |--------------------------------------------------------------------------
    |
    | If set, this URL will be passed to Midtrans callbacks.finish so mobile
    | can be redirected to your app/web after payment.
    |
    */
    'finish_redirect_url' => env('MIDTRANS_FINISH_REDIRECT_URL', ''),
];
