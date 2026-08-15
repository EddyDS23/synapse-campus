<?php

namespace App\Services;

use App\Exceptions\BusinessException;
use App\Exceptions\ServiceUnavailableException;
use Illuminate\Support\Facades\Http;

class LibraryCoreService
{
    public function __construct(
        private AuthVaultService $authVault
    ) {}

    // ─── Token management ────────────────────────────────────────────────────

    private function getToken(): string
    {
        $token = session('tokens.library-core');

        if ($token) {
            return $token;
        }

        $result = $this->authVault->exchangeToken(
            session('access_token'),
            'library-core',
            session('refresh_token')
        );

        if ($result['access_token'] !== null) {
            session([
                'access_token'  => $result['access_token'],
                'refresh_token' => $result['refresh_token'],
            ]);
        }

        session([
            'tokens.library-core' => $result['token'],
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

        $url = config('services.librarycore.base_url') . $path;

        try {
            $response = Http::withToken($token)
                ->withQueryParameters($query)
                ->{$method}($url, $body);
        } catch (\Throwable $th) {
            throw new ServiceUnavailableException(
                'LibraryCore no disponible'
            );
        }

        if ($response->status() === 401) {
            session()->forget('tokens.library-core');

            $token = $this->getToken();

            try {
                $response = Http::withToken($token)
                    ->withQueryParameters($query)
                    ->{$method}($url, $body);
            } catch (\Throwable $th) {
                throw new ServiceUnavailableException(
                    'LibraryCore no disponible'
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
                'LibraryCore no disponible'
            );
        }

        return $response->json();
    }

    // ─── Books ───────────────────────────────────────────────────────────────

    public function getBooks(array $filters = []): array
    {
        return $this->request(
            'get',
            '/api/books',
            array_filter($filters)
        );
    }

    public function getBook(string $id): array
    {
        return $this->request(
            'get',
            '/api/books/' . $id
        );
    }

    // ─── Loans ───────────────────────────────────────────────────────────────

    public function requestLoan(string $bookId): array
    {
        return $this->request(
            'post',
            '/api/loans/' . $bookId
        );
    }

    public function getMyLoans(): array
    {
        return $this->request(
            'get',
            '/api/loans/my'
        );
    }

    public function renewLoan(string $loanId): array
    {
        return $this->request(
            'post',
            '/api/loans/' . $loanId . '/renew'
        );
    }

    public function returnBook(string $loanId): array
    {
        return $this->request(
            'post',
            '/api/loans/' . $loanId . '/return'
        );
    }

    // ─── Fines ───────────────────────────────────────────────────────────────

    public function getMyFines(?string $status = null): array
    {
        return $this->request(
            'get',
            '/api/fines/my',
            $status
                ? ['status' => $status]
                : []
        );
    }

    public function payFine(string $fineId): array
    {
        return $this->request(
            'post',
            '/api/fines/' . $fineId . '/pay'
        );
    }


    public function createBook(array $data): array
    {
        return $this->request('post', '/api/books', $data);
    }

    public function updateBook(string $id, array $data): array
    {
        return $this->request('patch', '/api/books/' . $id, $data);
    }

    public function updateStock(string $id, int $adjustment): array
    {
        return $this->request('patch', '/api/books/' . $id . '/stock', [
            'stock_total' => $adjustment,
        ]);
    }
}
