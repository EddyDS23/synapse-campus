<?php

namespace App\Services;

use App\Models\ServiceClient;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use PHPOpenSourceSaver\JWTAuth\JWTAuth;

class AuditLogServiceClient
{
    private const CACHE_KEY_TOKEN = 'auditlog_service_token';

    public function __construct(private JWTAuth $jwt) {}

    public function sendLog(array $data): bool
    {
        $tokenService = Cache::get(self::CACHE_KEY_TOKEN);

        if ($tokenService === null) {
            $serviceClient = ServiceClient::where('client_id', 'auth-vault')->first();
            $tokenService = $this->jwt->fromUser($serviceClient);
            Cache::put(self::CACHE_KEY_TOKEN, $tokenService, now()->addMinutes(58));
        }

        try {
            $response = Http::withToken($tokenService)
                ->post(
                    config('services.audit_log.base_url') . config('services.audit_log.events_url'),
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