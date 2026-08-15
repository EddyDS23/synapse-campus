<?php

namespace App\Providers;

use App\Services\HealthService;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('layouts.app', function ($view) {
            $health = app(HealthService::class)->check();
            $view->with('ecosystemHealth', $health);
        });
    }
}
