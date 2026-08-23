<?php

return [
    'base_url' => env('AUTHVAULT_BASE_URL'),
    'public_key_path' => '/api/auth/public-key',
    'service_token_url' => '/api/service/token',
    'service_id' => env('CLIENT_ID'),
    'service_secret' => env('CLIENT_SECRET'),
];