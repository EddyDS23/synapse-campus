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

    'authvault'=>[
        'base_url' => env('AUTHVAULT_BASE_URL'),
        'login_url' => '/api/login',
        'logout_url' => '/api/logout',
        'exchange_token_url' => '/api/token/exchange',
        'refresh_url' => '/api/token/refresh-with-token',
    ],

    'studentportal'=>[
        'base_url'=>env('STUDENTPORTAL_BASE_URL'),
        'profile_url'=>'/api/profile',
        'schedule_url'=>'/api/schedule',
        'subjects_url'=>'/api/subjects',
        'academic_status_url'=>'/api/academic-status'
    ],
];
