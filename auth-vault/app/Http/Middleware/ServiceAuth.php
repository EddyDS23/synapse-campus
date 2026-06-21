<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use PHPOpenSourceSaver\JWTAuth\JWT;


class ServiceAuth
{

    public function __construct(private JWT $jwt){}
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string $scope): Response
    {

        try {
            $payload = $this->jwt->parseToken()->checkOrFail();
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 401);
        }

        if($payload->get('type') !== 'service'){
            return response()->json(['message' => 'Invalid service token'],402);
        }

        if(!in_array($scope,$payload->get('scopes'))){
            return response()->json(['message'=>'Invalid service token'],403);
        }

        return $next($request);
    }
}
