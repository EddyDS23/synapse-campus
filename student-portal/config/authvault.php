<?php

    return [

        'base_url'=>env('AUTHVAULT_BASE_URL'),
        'public_key_path'=>'/api/auth/public-key',
        'service_id'=>env('AUTHVAULT_SERVICE_ID'),   
        'service_secret'=>env('AUTHVAULT_SERVICE_SECRET'),
        'service_cache_user'=>env('AUTHVAULT_BASIC_INFO_USERS_CACHE_TTL'),
        'service_token_url'=>'/api/service/token',
        'user_basic_info_url'=>'/api/internal/users/{id}/basic-info',
    ]

?>