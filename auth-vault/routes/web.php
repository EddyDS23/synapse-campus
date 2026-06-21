<?php

use App\Http\Controllers\JWKSController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/.well-known/jwks.json',[JWKSController::class,'jwks']);
