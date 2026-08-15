<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('login',function(Request $request){
            return Limit::perMinute(5)->by($request->input('email') .'|'.$request->ip());
        });

        RateLimiter::for('register',function(Request $request){
            return Limit::perMinute(10)->by($request->ip());
        });

        RateLimiter::for("2fa_login", function(Request $request){
            return Limit::perMinute(5)->by($request->input("email"));
        });

        RateLimiter::for("refresh_token",function(Request $request){
            return Limit::perMinute(20)->by($request->ip());
        });

        RateLimiter::for("exchange_token",function(Request $request){
            return Limit::perMinute(30)->by($request->ip());
        });

        RateLimiter::for("service_token",function(Request $request){
            return Limit::perMinute(10)->by($request->ip());
        });

    }
}
