<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class HealthService
{
    private const CACHE_KEY = 'ecosystem_health';
    private const CACHE_TTL = 30;

    private array $services = [
        'AuthVault'     => 'authvault',
        'StudentPortal' => 'studentportal',
        'LibraryCore'   => 'librarycore',
        'SupportDesk'   => 'supportdesk',
        'AuditLog'      => 'auditlog',
    ];

    public function check(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return $this->fetchAll();
        });
    }

    private function fetchAll(): array
    {
        $responses = Http::pool(function ($pool) {
            foreach ($this->services as $name => $key) {
                $pool->as($name)->timeout(3)->get(
                    config("services.{$key}.base_url") . '/api/health'
                );
            }
        });

        $results = [];
        foreach ($this->services as $name => $key) {
            try {
                $response = $responses[$name];
                $results[$name] = $response->successful() && $response->json('status') === 'ok'
                    ? 'online'
                    : 'degraded';
            } catch (\Throwable) {
                $results[$name] = 'offline';
            }
        }

        return $results;
    }
}