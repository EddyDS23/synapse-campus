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
        $scopes = $request->attributes->get('jwt_payload')->scopes;

        if (empty($scopes) || !in_array($scope, $scopes, true)) {
            return response()->json(['message' => 'Scopes insuficientes'], 403);
        }

        return $next($request);
    }
}
