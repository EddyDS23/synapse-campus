<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

use RuntimeException;

class AuthVaultServiceClient
{


    private const  CACHE_KEY_TOKEN = 'authvault_service_token';
    private const  CACHE_KEY_TEACHER = 'teacher_basic_info';

    public function resolveTeacherName(string $teacherId): ?string
    {

        $cacheKey = self::CACHE_KEY_TEACHER . ":{$teacherId}";


        $teacherCache = Cache::get($cacheKey);


        if ($teacherCache !== null) {
            return $teacherCache['name'];
        }

        $tokenService = Cache::get(self::CACHE_KEY_TOKEN);


        if ($tokenService === null) {
            try {
                $response = Http::post(config('authvault.base_url') . config('authvault.service_token_url'), ['client_id' => config('authvault.service_id'), 'client_secret' => config('authvault.service_secret')]);
                $tokenService = $response->json('token');
                if (!$response->successful()) {
                    return $teacherId;
                }

                $tokenService = $response->json('token');
                Cache::add(self::CACHE_KEY_TOKEN, $tokenService, 900);
            } catch (ConnectionException $e) {
                return $teacherId;
            }
        }

        try {
            $response = Http::withToken($tokenService)->get(config('authvault.base_url') . str_replace('{id}', $teacherId, config('authvault.user_basic_info_url')));
        } catch (ConnectionException $e) {
            return $teacherId;
        }


        if (!$response->successful()) {
            return $teacherId;
        }

        $teacher = $response->json();

        Cache::put($cacheKey, $teacher, now()->addDay());

        return $teacher['name'];
    }
}
