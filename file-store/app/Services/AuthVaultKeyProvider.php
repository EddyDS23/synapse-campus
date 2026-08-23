<?php 

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class AuthVaultKeyProvider
{

    private const CACHE_KEY = 'authvault_public_key';
    private const CACHE_TTL = 3600;

    public function getPublicKey():string{

        $cached = Cache::get(self::CACHE_KEY);

        if($cached !== null){
            return $cached;
        }

        $response = Http::get(config('authvault.base_url') . config('authvault.public_key_path'));

        if(!$response->successful()){
            throw new RuntimeException('Couldnt be obtained public key of AuthVault');
        }

        $publicKey = $response->body();

        Cache::put(self::CACHE_KEY,$publicKey, self::CACHE_TTL);

        return $publicKey;

    }


}


?>