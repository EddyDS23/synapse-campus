<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class AuditLogServiceClient
{
    private const CACHE_KEY_TOKEN = 'auditlog_service_token';

    public function sendLog(array $data): bool
    {
        $tokenService = Cache::get(self::CACHE_KEY_TOKEN);

        if ($tokenService === null) {
            try {
                $response = Http::post(
                    config('authvault.base_url') . config('authvault.service_token_url'),
                    [
                        'client_id' => config('authvault.service_id'),
                        'client_secret' => config('authvault.service_secret'),
                    ]
                );

                if (!$response->successful()) {
                    return false;
                }

                $tokenService = $response->json('token');
                Cache::put(self::CACHE_KEY_TOKEN, $tokenService, now()->addMinutes(58));
            } catch (ConnectionException $e) {
                return false;
            }
        }

        try {
            $response = Http::withToken($tokenService)
                ->post(
                    config('auditlog.base_url') . config('auditlog.events_url'),
                    $data
                );
        } catch (ConnectionException $e) {
            return false;
        }

        if (!$response->successful()) {
            return false;
        }

        return true;
    }
}