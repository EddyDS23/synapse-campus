<?php

namespace App\Services;

use App\Exceptions\InvalidRequestException;
use App\Exceptions\ServiceUnavailableException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use PHPOpenSourceSaver\JWTAuth\Exceptions\TokenInvalidException;

class StudentPortalService
{

    public function __construct(private AuthVaultService $authvault) {}

    private function getToken(): string
    {

        $token = session('tokens.student-portal');

        if ($token) {
            return $token;
        }

        $exchange = $this->authvault->exchangeToken(
            session('access_token'),
            'student-portal',
            session('refresh_token')
        );

        if ($exchange['access_token'] !== null) {
            session([
                'access_token' => $exchange['access_token'],
                'refresh_token' => $exchange['refresh_token'],
            ]);
        }


        session(['tokens.student-portal' => $exchange['token']]);

        return $exchange['token'];
    }

    public function getProfile(): array
    {   
        Log::info( config('services.studentportal.profile_url'));
        return $this->request('get', config('services.studentportal.profile_url'));
    }

    public function getSchedule(): array
    {
        return $this->request('get', config('services.studentportal.schedule_url'));
    }

    public function getSubject(): array
    {
        return $this->request('get', config('services.studentportal.subjects_url'));
    }

    public function getAcademicStatus(): array
    {
        return $this->request('get', config('services.studentportal.academic_status_url'));
    }

    private function request(string $method, string $path): array
    {

        $token = $this->getToken();
        Log::info( config('services.studentportal.base_url') . $path);
        $response = Http::withToken($token)
            ->{$method}(config('services.studentportal.base_url') . $path);

        if ($response->status() === 401) {
            session()->forget('tokens.student-portal');
            $token = $this->getToken();

            try {
                $response = Http::withToken($token)
                    ->{$method}(config('services.studentportal.base_url') . $path);
            } catch (\Throwable $th) {
                throw new ServiceUnavailableException("StudentPortal no disponible");
            }
        }

        if (!$response->successful()) {
            Log::warning('StudentPortal respondió con error', [
                'path'   => $path,
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            throw new ServiceUnavailableException("StudentPortal no disponible");
        }

        return $response->json();
    }
}
