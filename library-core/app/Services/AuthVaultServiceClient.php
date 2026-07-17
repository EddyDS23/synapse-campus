<?php 

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Illuminate\Support\Facades\Log;

class AuthVaultServiceClient{

    private const CACHE_TOKEN_SERVICE = 'authvault_token_service';

    public function getTokenService(): ?string{

        $cached = Cache::get(SELF::CACHE_TOKEN_SERVICE);

        if($cached !== null){
            return $cached;
        }

        try {
            $response = Http::post(config('services.authvault.base_url') . config('services.authvault.token_service_url'),
            ['client_id'=>config('app.client_id'),'client_secret'=>config('app.client_secret')]);
        } catch (ConnectionException $e) {
            return new RuntimeException('Timeout connection to authvault',503);
        }

        if(!$response->successful()){
            Log::error('Error couldnt obteined token service',["message"=>$response->body()]);
            return new RuntimeException('Couldnt obteined token service',503);

        }

        $token = $response->json('token');

        Cache::add(SELF::CACHE_TOKEN_SERVICE,$token,now()->addMinutes(58));
        return $token;
    }

}