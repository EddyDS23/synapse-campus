<?php

use App\Http\Controllers\InternalController;
use App\Http\Controllers\StudentController;
use Illuminate\Http\Request;
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


Route::get('/health',function(){
    return response()->json(['message'=>'Im alive from Student Portal']);
});

