<?php

namespace App\Services;

use App\Exceptions\InvalidRequestException;
use App\Exceptions\InvalidCredentialsException;
use App\Exceptions\ServiceUnavailableException;
use App\Exceptions\TokenExpiredException;
use Illuminate\Support\Facades\Http;
use PhpParser\Token;

class AuthVaultService
{

    public function login(string $email, string $password): array
    {

        try {
            $response = Http::post(
                config('services.authvault.base_url') . config('services.authvault.login_url'),
                [
                    'email' => $email,
                    'password' => $password
                ]
            );
        } catch (\Throwable $th) {
            throw new ServiceUnavailableException("Authvault no disponible");
        }

        if ($response->serverError()) {
            throw new ServiceUnavailableException("Authvault no disponible");
        }

        if (!$response->successful()) {
            throw new InvalidCredentialsException("Credenciales Incorrectas");
        }

        $token = $response->json('token');

        return [
            'access_token'=>$token,
            'refresh_token'=>$response->json('refresh_token'),
            'token_expiry'=>$this->getTokenExpiry($token),
            'email'=>$this->extractEmail($token),
            'roles'=>$this->extractRoles($token)
        ];
    }

    public function logout(string $access_token):bool{

        try {
            $response = Http::withToken($access_token)->post(config('services.authvault.base_url') . config('services.authvault.logout_url'));
        } catch (\Throwable $th) {
            return false;
        }

        if($response->serverError()){
           return false;
        }

        if($response->status() === 401){
            return false;
        }

        return true;

    }

    public function exchangeToken(string $accessToken, string $audience, ?string $refreshToken, bool $retried = false): array
    {

        try {
            $response = Http::withToken($accessToken)->post(config('services.authvault.base_url') . config('services.authvault.exchange_token_url'), [
                'audience' => $audience
            ]);
        } catch (\Throwable $th) {
            throw new ServiceUnavailableException("Authvault no disponible");
        }

        if ($response->serverError()) {
            throw new ServiceUnavailableException("Authvault no disponible");
        }

        if ($response->status() === 401) {
            if ($retried) {
                throw new TokenExpiredException("No se pudo renovar la sesion");
            }
            $new_session = $this->refreshToken($refreshToken);
            $exchange_result = $this->exchangeToken($new_session['token'], $audience, $new_session['refresh_token'], true);

            return [
                'token'=>$exchange_result['token'],
                'access_token'=>$new_session['token'],
                'refresh_token'=>$new_session['refresh_token']
            ];
        }

        if ($response->status() === 422) {
            throw new InvalidRequestException("Audience invalido");
        }

        return [
            'token'=>$response->json('token'),
            'access_token' => null,
            'refresh_token' => null,
        ];
    }


    public function refreshToken(string $refreshToken): array
    {

        try {
            $response = Http::post(config('services.authvault.base_url') . config('services.authvault.refresh_url'), [
                'refresh_token' => $refreshToken
            ]);
        } catch (\Throwable $th) {
            throw new ServiceUnavailableException("Authvault no disponible");
        }

        if ($response->serverError()) {
            throw new ServiceUnavailableException("Authvault no disponible");
        }

        if ($response->status() === 401) {
            throw new TokenExpiredException("Refresh token invalido o expirado");
        }

        if ($response->status() === 422) {
            throw new InvalidRequestException("Request invalido");
        }


        return $response->json();
    }


    private function extractRoles(string $token): array
    {
        $parts = explode('.', $token);
        $data = json_decode(base64_decode(str_pad($parts[1], strlen($parts[1]) % 4, '=')),true);
        return (array)($data['roles'] ?? []);
    }

    private function extractEmail(string $token): string
    {
        $parts = explode('.', $token);
        $data = json_decode(base64_decode(str_pad($parts[1], strlen($parts[1]) % 4, '=')), true);
        return $data['email'];
    }

    private function getTokenExpiry(string $token): int
    {
        $parts = explode('.', $token);
        $payload = json_decode(base64_decode(str_pad($parts[1], strlen($parts[1]) % 4, '=')), true);
        return $payload['exp'] ?? 0;
    }

}
