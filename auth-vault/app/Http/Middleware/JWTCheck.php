<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Cache;
use PHPOpenSourceSaver\JWTAuth\JWT;

class JWTCheck
{

    public function __construct(private JWT $jwt) {}

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        $user = $request->user();

        $payload = $this->jwt->payload();
        $iat = $payload->get('iat');
        $jti = $payload->get('jti');

        if(Cache::has("session_revoked:{$jti}")){
            return response()->json(['message'=>'Session Revoked'],401);
        }
        
        if($user->tokens_invalidated_at !== null && $iat < $user->tokens_invalidated_at->timestamp){
            return response()->json(['message'=>'Token invalidated'],401);
        }

        return $next($request);
        
    }
}
