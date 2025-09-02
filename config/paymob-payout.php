<?php

return [
    'environment' => env('PAYMOB_PAYOUT_ENVIRONMENT', 'staging'),

    'staging' => [
        'base_url' => 'https://stagingpayouts.paymobsolutions.com/api/secure/',
    ],

    'production' => [
        'base_url' => 'https://payouts.paymobsolutions.com/api/secure/',
    ],

    'credentials' => [
        'client_id' => env('PAYMOB_PAYOUT_CLIENT_ID'),
        'client_secret' => env('PAYMOB_PAYOUT_CLIENT_SECRET'),
        'username' => env('PAYMOB_PAYOUT_USERNAME'),
        'password' => env('PAYMOB_PAYOUT_PASSWORD'),
    ],

    'cache' => [
        'token_key' => 'paymob_payout_token',
        'token_ttl' => 3500,
    ],

    'timeout' => env('PAYMOB_PAYOUT_TIMEOUT', 30),
];
