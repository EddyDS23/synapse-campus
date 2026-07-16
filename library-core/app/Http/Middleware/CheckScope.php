<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckScope
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string $scope): Response
    {

        $payload = $request->attributes->get('jwt_payload');

        $scopes = $payload->scopes;

        if( empty($scopes) || !in_array($scope,$scopes,true)){
            return response()->json(['message'=>'Token havent scopes available or enoughts']);
        }

        return $next($request);
    }
}
