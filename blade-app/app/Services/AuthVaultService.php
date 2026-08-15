<?php

namespace App\Services;

use App\Exceptions\InvalidRequestException;
use App\Exceptions\InvalidCredentialsException;
use App\Exceptions\ServiceUnavailableException;
use App\Exceptions\TokenExpiredException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
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

        return $response->json();
    }

    public function helperSession(array $data)
    {
        $token = $data['token'];
        return [
            'access_token' => $token,
            'refresh_token' => $data['refresh_token'],
            'token_expiry' => $this->getTokenExpiry($token),
            'email' => $this->extractEmail($token),
            'roles' => $this->extractRoles($token)
        ];
    }

    public function logout(string $access_token): bool
    {

        try {
            $response = Http::withToken($access_token)->post(config('services.authvault.base_url') . config('services.authvault.logout_url'));
        } catch (\Throwable $th) {
            return false;
        }

        if ($response->serverError()) {
            return false;
        }

        if ($response->status() === 401) {
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
                'token' => $exchange_result['token'],
                'access_token' => $new_session['token'],
                'refresh_token' => $new_session['refresh_token']
            ];
        }

        if ($response->status() === 422) {
            throw new InvalidRequestException("Audience invalido");
        }

        return [
            'token' => $response->json('token'),
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
        $data = json_decode(base64_decode(str_pad($parts[1], strlen($parts[1]) % 4, '=')), true);
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

    // ─── Helpers privados de request ─────────────────────────────────────────────

    private function authedRequest(string $method, string $path, array $body = []): array
    {
        try {
            $response = Http::withToken(session('access_token'))
                ->{$method}(config('services.authvault.base_url') . $path, $body);
        } catch (\Throwable $th) {
            throw new ServiceUnavailableException('AuthVault no disponible');
        }

        if ($response->serverError()) {
            throw new ServiceUnavailableException('AuthVault no disponible');
        }

        if ($response->status() === 401) {
            throw new TokenExpiredException('Sesión expirada');
        }

        if (!$response->successful()) {
            throw new \App\Exceptions\BusinessException(
                $response->json('message') ?? 'Error en la operación',
                $response->status()
            );
        }

        return $response->json() ?? [];
    }

    // ─── Sessions ─────────────────────────────────────────────────────────────────

    public function getSessions(): array
    {
        return $this->authedRequest('get', '/api/session');
    }

    public function deleteSession(string $id): array
    {
        try {
            $response = Http::withToken(session('access_token'))
                ->delete(config('services.authvault.base_url') . '/api/session/' . $id);
        } catch (\Throwable $th) {
            throw new ServiceUnavailableException('AuthVault no disponible');
        }

        if ($response->serverError()) {
            throw new ServiceUnavailableException('AuthVault no disponible');
        }

        return [];
    }

    // ─── Audit logs ───────────────────────────────────────────────────────────────

    public function getAuditLogs(): array
    {
        return $this->authedRequest('get', '/api/audit-logs');
    }

    // ─── 2FA ──────────────────────────────────────────────────────────────────────

    public function enable2fa(): array
    {
        return $this->authedRequest('post', '/api/2fa/enable');
    }

    public function verify2fa(string $code): array
    {
        return $this->authedRequest('post', '/api/2fa/verify', ['code' => $code]);
    }

    public function disable2fa(string $code): array
    {
        return $this->authedRequest('post', '/api/2fa/disable', ['code' => $code]);
    }

    // ─── Login 2FA (desde el flujo de login) ─────────────────────────────────────

    public function login2fa(string $email, string $code): array
    {
        try {
            $response = Http::post(
                config('services.authvault.base_url') . '/api/login/2fa',
                ['email' => $email, 'code' => $code]
            );
        } catch (\Throwable $th) {
            throw new ServiceUnavailableException('AuthVault no disponible');
        }

        if ($response->serverError()) {
            throw new ServiceUnavailableException('AuthVault no disponible');
        }

        if (!$response->successful()) {
            throw new InvalidCredentialsException('Código 2FA inválido o expirado');
        }

        return $this->helperSession($response->json());
    }

    // ─── Admin: roles ─────────────────────────────────────────────────────────────

    public function assignRole(string $userId, string $role): array
    {
        return $this->authedRequest('post', '/api/users/' . $userId . '/roles', ['role' => $role]);
    }

    public function revokeRole(string $userId, string $role): array
    {
        try {
            $response = Http::withToken(session('access_token'))
                ->delete(config('services.authvault.base_url') . '/api/users/' . $userId . '/roles/' . $role);
        } catch (\Throwable $th) {
            throw new ServiceUnavailableException('AuthVault no disponible');
        }

        if ($response->serverError()) {
            throw new ServiceUnavailableException('AuthVault no disponible');
        }

        return $response->json() ?? [];
    }

    // ─── Admin: security status de usuario ───────────────────────────────────────

    public function getUserSecurityStatus(string $userId): array
    {
        return $this->authedRequest('get', '/api/users/' . $userId . '/security-status');
    }

    // ─── OAuth redirect URL ───────────────────────────────────────────────────────

    public function getOAuthRedirectUrl(string $provider): string
    {
        try {
            $response = Http::get(
                config('services.authvault.base_url') . '/api/auth/' . $provider . '/redirect'
            );
        } catch (\Throwable $th) {
            throw new ServiceUnavailableException('AuthVault no disponible');
        }

        if (!$response->successful()) {
            throw new ServiceUnavailableException('No se pudo obtener URL de OAuth');
        }

        return $response->json('url');
    }

    public function handleOAuthCallback(string $provider, array $params): array
    {
        try {
            $response = Http::get(
                config('services.authvault.base_url') . '/api/auth/' . $provider . '/callback',
                $params
            );
        } catch (\Throwable $th) {
            throw new ServiceUnavailableException('AuthVault no disponible');
        }

        if (!$response->successful()) {
            throw new InvalidCredentialsException('Error en la autenticación con ' . $provider);
        }

        return $this->helperSession($response->json());
    }
}
