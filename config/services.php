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

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],
    'fcm' => [
        // Firebase service-account key (Firebase console > Project settings >
        // Service accounts > Generate new private key). Never commit this file.
        'credentials' => env('FIREBASE_CREDENTIALS', storage_path('app/firebase-credentials.json')),

        // Alternative to the file, for hosts such as Railway where uploading a file is
        // awkward: put the whole key here, either as raw JSON or base64-encoded JSON.
        'credentials_json' => env('FIREBASE_CREDENTIALS_JSON'),

        // Local hour (0-23, in each user's own timezone) for the daily push.
        'daily_hour' => (int) env('FCM_DAILY_HOUR', 8),
    ],
];
