<?php

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\ConnectionException;

class AuthVaultKeyProvider{

    private const CACHE_PUBLIC_KEY = 'authvault_public_key';

    public function getKey(): ?string{
        
        $cached = Cache::get(SELF::CACHE_PUBLIC_KEY);

        if($cached !== null){
            return $cached;
        }

        try{
            $response = Http::get(config('services.authvault.base_url').config('services.authvault.public_key_url'));
        }catch(ConnectionException $e){
            throw new RuntimeException('Timeout connection to authvault ');
        }

        if(!$response->successful()){
            throw new RuntimeException('Couldnt be obtained public key of AuthVault');
        }

        $publicKey = $response->body();

        Cache::add(SELF::CACHE_PUBLIC_KEY,$publicKey,now()->addHour());

        return $publicKey;
    }

}