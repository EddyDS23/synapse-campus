<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

use Illuminate\Support\Facades\Log;

use RuntimeException;

class AuthVaultServiceClient
{

    private const  CACHE_TOKEN_SERVICE = 'authvault_service_token';

    public function getTokenService(): ?string
    {

        $cached = Cache::get(SELF::CACHE_TOKEN_SERVICE);

        if ($cached !== null) {
            return $cached;
        }

        try {
            $response = Http::post(
                config('services.authvault.base_url') . config('services.authvault.token_service_url'),
                ['client_id' => config('app.client_id'), 'client_secret' => config('app.client_secret')]
            );
        } catch (ConnectionException $e) {
            Log::alert('Timeout connection to AuthVault', ['message' => $e->getMessage()]);
            return null;
        }

        if (!$response->successful()) {
            Log::error('Could not obtain service token', ['message' => $response->body()]);
            return null;
        }

        $token = $response->json('token');

        Cache::add(SELF::CACHE_TOKEN_SERVICE, $token, now()->addMinutes(58));
        return $token;
    }


    public function getUserSecurityStatus(string $userId): array
    {
        $token = $this->getTokenService();

        if (!$token) {
            Log::alert('Cannot obtain service token for AuthVault');
            return [];
        }

        
        try {
            $response =  Http::withToken($token)->get(config('services.authvault.base_url') . str_replace('{id}', $userId, config('services.authvault.user_security_status_url')));
        } catch (ConnectionException $e) {
            Log::alert('TimeOut from authvaul', ['message' => $e->getMessage()]);
            return [];
        }

        if (!$response->successful()) {
            Log::alert('Cannot get security status in authvaul', ['message' => $response->body()]);
            return [];
        }

        return $response->json();
    }
}
