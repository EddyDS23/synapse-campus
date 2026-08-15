<?php

use App\Http\Controllers\InternalController;
use App\Http\Controllers\StudentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::middleware(['authvault.jwt'])->group(function(){
    Route::get('/payload', function(Request $request){
        return response()->json(['claims'=>$request->attributes->get('jwt_payload')],200);
    });
});

Route::middleware(['authvault.jwt','scope.check:student:profile:read'])->group(function(){
    Route::get('/profile', [StudentController::class,'profile']);
});


Route::middleware(['authvault.jwt','scope.check:student:schedule:read'])->group(function(){
    Route::get('/schedule', [StudentController::class, 'schedule']);
});

Route::middleware(['authvault.jwt','scope.check:student:subjects:read'])->group(function(){
    Route::get('/subjects', [StudentController::class, 'subjects']);
});

Route::middleware(['authvault.jwt','scope.check:student:notice:read'])->group(function(){
    Route::get('/notice', [StudentController::class, 'notice']);
});

Route::middleware(['authvault.jwt','scope.check:student:status:read'])->group(function(){
    Route::get('/academic-status', [StudentController::class, 'academic_status']);
});

Route::middleware(['service.check:internal:student-status:read'])->group(function(){
    Route::get('/internal/students/{id}/status',[InternalController::class,'student_status']);
});

Route::patch('/internal/students/{id}/debt-status',[InternalController::class,'updateDebt'])
        ->middleware(['service.check:internal:student-debt:write']);


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

