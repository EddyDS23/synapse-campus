<?php

namespace App\Services;

use App\Exceptions\BusinessException;
use App\Exceptions\ServiceUnavailableException;
use Illuminate\Support\Facades\Http;

class SupportDeskService
{
    public function __construct(
        private AuthVaultService $authVault
    ) {
    }

    // ─── Token management ────────────────────────────────────────────────────

    private function getToken(): string
    {
        $token = session('tokens.support-desk');

        if ($token) {
            return $token;
        }

        $result = $this->authVault->exchangeToken(
            session('access_token'),
            'support-desk',
            session('refresh_token')
        );

        if ($result['access_token'] !== null) {
            session([
                'access_token'  => $result['access_token'],
                'refresh_token' => $result['refresh_token'],
            ]);
        }

        session([
            'tokens.support-desk' => $result['token'],
        ]);

        return $result['token'];
    }

    // ─── HTTP request ────────────────────────────────────────────────────────

    private function request(
        string $method,
        string $path,
        array $query = [],
        array $body = []
    ): array {
        $token = $this->getToken();

        $url = config('services.supportdesk.base_url') . $path;

        try {
            $response = Http::withToken($token)
                ->withQueryParameters($query)
                ->{$method}($url, $body);
        } catch (\Throwable $th) {
            throw new ServiceUnavailableException(
                'SupportDesk no disponible'
            );
        }

        if ($response->status() === 401) {
            session()->forget('tokens.support-desk');

            $token = $this->getToken();

            try {
                $response = Http::withToken($token)
                    ->withQueryParameters($query)
                    ->{$method}($url, $body);
            } catch (\Throwable $th) {
                throw new ServiceUnavailableException(
                    'SupportDesk no disponible'
                );
            }
        }

        if ($response->status() === 422 || $response->status() === 409) {
            throw new BusinessException(
                $response->json('message') ?? 'Operación no permitida',
                $response->status()
            );
        }

        if (!$response->successful()) {
            throw new ServiceUnavailableException(
                'SupportDesk no disponible'
            );
        }

        return $response->json();
    }

    // ─── Tickets ─────────────────────────────────────────────────────────────

    public function getMyTickets(?string $status = null): array
    {
        return $this->request(
            'get',
            '/api/tickets/my',
            $status
                ? ['status' => $status]
                : []
        );
    }

    public function getAllTickets(array $filters = []): array
    {
        return $this->request(
            'get',
            '/api/tickets',
            array_filter($filters)
        );
    }

    public function getTicket(string $id): array
    {
        return $this->request(
            'get',
            '/api/tickets/' . $id
        );
    }

    public function createTicket(array $data): array
    {
        return $this->request(
            'post',
            '/api/tickets',
            [],
            $data
        );
    }

    public function addComment(
        string $ticketId,
        string $body,
        bool $isInternal = false
    ): array {
        return $this->request(
            'post',
            '/api/tickets/' . $ticketId . '/comments',
            [],
            [
                'body'        => $body,
                'is_internal' => $isInternal,
            ]
        );
    }

    public function assignTicket(
        string $ticketId,
        string $assigneeId
    ): array {
        return $this->request(
            'patch',
            '/api/tickets/' . $ticketId . '/assign',
            [],
            [
                'assignee_id' => $assigneeId,
            ]
        );
    }

    public function updateStatus(
        string $ticketId,
        string $status
    ): array {
        return $this->request(
            'patch',
            '/api/tickets/' . $ticketId . '/status',
            [],
            [
                'status' => $status,
            ]
        );
    }

    public function reopenTicket(string $ticketId): array
    {
        return $this->request(
            'patch',
            '/api/tickets/' . $ticketId . '/reopen'
        );
    }

    public function getSecurityContext(string $ticketId): array
    {
        return $this->request(
            'get',
            '/api/tickets/' . $ticketId . '/security-status'
        );
    }
}