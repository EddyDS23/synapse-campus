<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

use App\Http\Middleware\CheckRole;
use App\Http\Middleware\CheckScope;
use App\Http\Middleware\JWTCheck;
use App\Http\Middleware\ServiceAuth;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->redirectGuestsTo((fn()=>null));
        $middleware->alias([
            'role'=>CheckRole::class,
            'jwt_check'=>JWTCheck::class,
            'service.auth'=>ServiceAuth::class,
            'scope'=>CheckScope::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
