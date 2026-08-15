<?php

use App\Http\Controllers\EventsController;
use App\Http\Controllers\InternalController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

Route::get("/health",function(Request $request){
    
    try {
        DB::connection()->getPdo();
        $db=true;
    } catch (\Throwable $th) {
        $db=false;
    }

    if($db){
        $code = 200;
        $status = "ok";
        $db_status = "connected";
    }else{
        $code = 503;
        $status = "degraded";
        $db_status = "disconnected";
    }

    return response()->json([
        'status'=>$status,
        'service'=>config("app.name"),
        'db'=> $db_status
    ],$code);

});





