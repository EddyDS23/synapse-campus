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
    public function handle(Request $request, Closure $next,string ...$scopes): Response
    {

        $userScopes = $request->attributes->get('jwt_payload')->scopes;

        if(empty(array_intersect($scopes, $userScopes))){
            return response()->json(['message'=>'Insufficient scope'],403);
        }

        return $next($request);
    }
}
