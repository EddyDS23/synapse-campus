<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use PHPOpenSourceSaver\JWTAuth\JWT;


class CheckScope
{

    public function __construct(private JWT $jwt){}

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next,string ...$scopes): Response
    {

        $payload = $this->jwt->payload();
        $userScopes = $payload->get('scopes');

        if(empty(array_intersect($scopes, $userScopes))){
            return response()->json(['message'=>'Insufficient scope'],403);
        }


        return $next($request);
    }
}
