<?php

use App\Http\Controllers\EventsController;
use App\Http\Controllers\InternalController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::middleware(['jwt_check','scope.check:audit:events:read'])->group(function(){
    Route::get('events',[EventsController::class,'events']);
});

Route::middleware(['service.check:audit:events:write'])->group(function(){
    Route::post('internal/events',[InternalController::class,'register_events']);
});

Route::middleware(['service.check:audit:events:read'])->group(function(){
    Route::get('internal/events',[InternalController::class,'events']);
});





