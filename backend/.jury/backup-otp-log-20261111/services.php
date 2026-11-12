<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
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

    'firebase' => [
        'api_key' => env('FIREBASE_API_KEY'),
        'auth_domain' => env('FIREBASE_AUTH_DOMAIN'),
        'project_id' => env('FIREBASE_PROJECT_ID'),
        'storage_bucket' => env('FIREBASE_STORAGE_BUCKET'),
        'messaging_sender_id' => env('FIREBASE_MESSAGING_SENDER_ID'),
        'app_id' => env('FIREBASE_APP_ID'),
    ],

    'midtrans' => [
        'server_key' => env('MIDTRANS_SERVER_KEY'),
        'client_key' => env('MIDTRANS_CLIENT_KEY'),
        'is_production' => env('MIDTRANS_IS_PRODUCTION', false),
    ],

    // OTP pendaftaran: "wa" via Fonnte, "email" via SMTP Laravel.
    'fonnte' => [
        'token' => env('FONNTE_TOKEN'),
    ],
    'otp' => [
        // true = kode ditampilkan di layar bila pengiriman gagal.
        // Bawaan: NONAKTIF (production-safe). Bila diaktifkan pun kode hanya
        // ditampilkan saat APP_DEBUG=true.
        'demo' => (bool) env('OTP_DEMO', false),
        // true = MODE TES LOKAL: bila kredensial belum ada, kode OTP hanya
        // ditulis ke storage/logs/laravel.log (tidak dikirim & tidak tampil
        // di layar). Aktifkan dari Panel Admin > OTP, jangan di produksi.
        'local_fallback' => (bool) env('OTP_LOCAL_FALLBACK', false),
    ],

];
