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
        'base_url'=>env('AUTHVAULT_BASE_URL'),
        'public_key_url'=>'/api/auth/public-key',
        'token_service_url'=>'/api/service/token'
    ],

    'studentportal'=>[
        'base_url'=>env('STUDENTPORTAL_BASE_URL'),
        'student_status_url'=>'/api/internal/students/{id}/status',
        'student_debt_update_url'=>'/api/internal/students/{id}/debt-status',
    ],

    'auditlog'=>[
        'base_url'=>env('AUDITLOG_BASE_URL'),
        'send_events_url'=>'/api/internal/events'
    ]

];
