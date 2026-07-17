<?php

namespace App\Services;

use Exception;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

use RuntimeException;
class StudentPortalServiceClient{

    public function statusStudent(string $token, string $id): array{
        
        try {
            $response = Http::withToken($token)->get(config('services.studentportal.base_url'). str_replace('{id}',$id,config('services.studentportal.student_status_url')));
        } catch (ConnectionException $e) {
             throw new RuntimeException('Timeout connection to StudentPortal',503);
        }


        if(!$response->successful()){
            throw new RuntimeException('Couldnt obteined status to student',503);
        }
        

        return $response->json();

    }


}