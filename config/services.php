<?php

return [

    'anthropic' => [
        'api_key' => env('ANTHROPIC_API_KEY'),
        'model' => env('ANTHROPIC_MODEL', 'claude-sonnet-5'),
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI'),
    ],


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

    'gemini' => [
        'api_key' => env('GEMINI_API_KEY'),
        'model' => env('GEMINI_MODEL', 'gemini-flash-latest'),
    ],

    'flutterwave' => [
        'secret' => env('FLW_SECRET_KEY'),
        'webhook_secret' => env('FLW_WEBHOOK_SECRET'),
    ],

    'paystack' => [
        'secret' => env('PAYSTACK_SECRET_KEY'),
        'public' => env('PAYSTACK_PUBLIC_KEY'),
    ],

    // Oseultimate - primary VTU provider (airtime, data, cable)
    'vtu' => [
        'base_url' => env('VTU_BASE_URL', 'https://oseultimate.com/api'),
        'token' => env('VTU_TOKEN', ''),
        'margin_percent' => (float) env('VTU_MARGIN_PERCENT', 4),
    ],

    // vtu.ng - secondary provider, currently used as airtime failover only.
    // network_map lets you correct the airtime service_id vtu.ng expects for
    // each network, in case it differs from Oseultimate's slugs. Test once
    // real credentials are in, then adjust the right side if a purchase fails.
    'vtung' => [
        'base_url' => env('VTUNG_BASE_URL', 'https://vtu.ng/wp-json'),
        'username' => env('VTUNG_USERNAME'),
        'password' => env('VTUNG_PASSWORD'),
        'user_pin' => env('VTUNG_USER_PIN'),
        'network_map' => [
            'mtn' => env('VTUNG_NETWORK_MTN', 'mtn'),
            'glo' => env('VTUNG_NETWORK_GLO', 'glo'),
            'airtel' => env('VTUNG_NETWORK_AIRTEL', 'airtel'),
            '9mobile' => env('VTUNG_NETWORK_9MOBILE', '9mobile'),
        ],
    ],

];
