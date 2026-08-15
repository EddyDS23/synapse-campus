<?php

use App\Http\Controllers\AuditLogController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\InternalController;
use App\Http\Controllers\JWKSController;
use App\Http\Controllers\OAuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\ServiceTokenController;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\TwoFactorController;
use Illuminate\Support\Facades\DB;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:api');


//Endpoints Publicos
Route::post('/register',[AuthController::class, 'register'])->middleware('throttle:register');
Route::post('/login', [AuthController::class,'login'])->middleware('throttle:login');
Route::post('/login/2fa', [AuthController::class, 'login2fa'])->middleware('throttle:2fa_login');
Route::post('/token/refresh-with-token',[AuthController::class,'refreshWithToken'])->middleware('throttle:refresh_token');
Route::get('/auth/{provider}/redirect',[OAuthController::class,'redirect']);
Route::get('/auth/{provider}/callback',[OAuthController::class,'callback']);
Route::get('/auth/public-key', [JWKSController::class,'publicKey']);
Route::post('/service/token', [ServiceTokenController::class,'token'])->middleware('throttle:service_token');

//Endpoints Protegidos Usuario
Route::middleware(['auth:api','jwt_check'])->group(function(){
    Route::get('/users/me',[UserController::class,'me']);
    Route::post('/logout',[AuthController::class,'logout']);
    Route::post('/logout/all',[AuthController::class,'logoutAll']);
    Route::post('/2fa/enable', [TwoFactorController::class, 'enable']);
    Route::post('/2fa/disable', [TwoFactorController::class, 'disable']);
    Route::post('/2fa/verify', [TwoFactorController::class, 'verify']);
    Route::get('/session',[SessionController::class, 'get']);
    Route::delete('/session/{id}',[SessionController::class, 'delete']);
    Route::get('/audit-logs', [AuditLogController::class, 'get_audit_user']);
    Route::post('/token/exchange',[AuthController::class, 'exchangeToken'])->middleware('throttle:exchange_token');
});

#Endpoint Protegido Administrador
Route::middleware(['auth:api','jwt_check','role:security_admin,super_admin'])->group(function(){
    Route::post('/users/{id}/roles',[RoleController::class,'assign']);
    Route::delete('/users/{id}/roles/{role}',[RoleController::class,'revoke']);
});

#Endpoint Protegido por Scope de Usuario
Route::middleware(['auth:api','jwt_check','scope:user:security-status:read'])->group(function(){
    Route::get('/users/{id}/security-status',[UserController::class,'securityStatus']);
});


#Endpoint Protegido Servicios
Route::middleware(['service.auth:internal:security-status:read'])->group(function(){
    Route::get('internal/users/{id}/security-status',[InternalController::class,'security_status']);
});

Route::middleware(['service.auth:internal:users:basic-info:read'])->group(function(){
    Route::get('internal/users/{id}/basic-info', [InternalController::class,'basic_info']);
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