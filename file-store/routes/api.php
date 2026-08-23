<?php

use App\Http\Controllers\FileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::post('/files',[FileController::class,'upload'])->middleware(['authvault.jwt','scope.check:files:upload']);
Route::get('/files/{id}',[FileController::class,'download'])->middleware(['authvault.jwt','scope.check:files:read']);
Route::delete('/files/{id}',[FileController::class,'delete'])->middleware(['authvault.jwt','scope.check:files:delete']);

Route::get('/health',function(){

    try {
        DB::connection()->getPdo();
        $db = true;
    } catch (\Throwable $th) {
        $db = false;
    }

    return response()->json([
        'status'=>$db ? 'ok' : 'degraded',
        'service'=>config('app.name'),
        'db'=>$db ? 'connected' : 'disconnected',
    ],$db ? 200 : 503);

});