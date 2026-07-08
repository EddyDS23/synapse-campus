<?php

use App\Services\AuthVaultKeyProvider;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/public-key',[AuthVaultKeyProvider::class,'getPublicKey']);
