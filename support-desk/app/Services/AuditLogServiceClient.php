<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class AuditLogServiceClient
{

    public function sendLog(string $token,array $data):bool{

        try {
            $response = Http::withToken($token)
                ->post(
                    config('services.auditlog.base_url') . config('services.auditlog.send_events_url'),
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