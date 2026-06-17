<?php

use App\Http\Controllers\AuditLogController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\OAuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\TwoFactorController;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


//Endpoints Publicos
Route::post('/register',[AuthController::class, 'register'])->middleware('throttle:register');
Route::post('/login', [AuthController::class,'login'])->middleware('throttle:login');
Route::post('/login/2fa', [AuthController::class, 'login2fa']);
Route::get('/auth/{provider}/redirect',[OAuthController::class,'redirect']);
Route::get('/auth/{provider}/callback',[OAuthController::class,'callback']);

//Endpoints Protegidos
Route::middleware('auth:sanctum')->group(function(){
    Route::get('/users/me',[UserController::class,'me']);
    Route::post('/logout',[AuthController::class,'logout']);
    Route::post('/logout/all',[AuthController::class,'logoutAll']);
    Route::post('/token/refresh',[AuthController::class,'refresh']);
    Route::post('/2fa/enable', [TwoFactorController::class, 'enable']);
    Route::post('/2fa/disable', [TwoFactorController::class, 'disable']);
    Route::post('/2fa/verify', [TwoFactorController::class, 'verify']);
    Route::get('/session',[SessionController::class, 'get']);
    Route::delete('/session/{id}',[SessionController::class, 'delete']);
    Route::get('/audit-logs', [AuditLogController::class, 'get_audit_user']);
});

Route::middleware(['auth:sanctum','role:admin'])->group(function(){
    Route::post('/users/{id}/roles',[RoleController::class,'assign']);
    Route::delete('/users/{id}/roles/{role}',[RoleController::class,'revoke']);
});

