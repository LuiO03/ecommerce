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
        'token' => env('POSTMARK_TOKEN'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
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

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI'),
    ],

    'niubiz' => [
        'merchant_id' => env('NIUBIZ_MERCHANT_ID'),
        'user' => env('NIUBIZ_USER'),
        'password' => env('NIUBIZ_PASSWORD'),
        'url_api' => env('NIUBIZ_URL_API'),
        'url_js' => env('NIUBIZ_URL_JS'),
        'countable' => env('NIUBIZ_COUNTABLE', true),
        'webhook_secret' => env('NIUBIZ_WEBHOOK_SECRET'),
        'dev_simulation' => env('NIUBIZ_DEV_SIMULATION', false),
    ],

    'culqi' => [
        'public_key' => env('CULQI_PUBLIC_KEY'),
        'secret_key' => env('CULQI_SECRET_KEY'),
        'base_url' => env('CULQI_BASE_URL', 'https://api.culqi.com'),
        'checkout_url' => env('CULQI_CHECKOUT_URL', 'https://checkout.culqi.com/js/v4'),
        'webhook_secret' => env('CULQI_WEBHOOK_SECRET'),
        'dev_simulation' => env('CULQI_DEV_SIMULATION', false),
    ],

    'mercadopago' => [
        'public_key' => env('MERCADO_PAGO_PUBLIC_KEY'),
        'secret_key' => env('MERCADO_PAGO_SECRET_KEY'),
        'base_url' => env('MERCADO_PAGO_BASE_URL', 'https://api.mercadopago.com'),
        'checkout_url' => env('MERCADO_PAGO_CHECKOUT_URL', 'https://sdk.mercadopago.com/js/v2'),
        'webhook_secret' => env('MERCADO_PAGO_WEBHOOK_SECRET'),
        'dev_simulation' => env('MERCADO_PAGO_DEV_SIMULATION', false),
    ],


];
