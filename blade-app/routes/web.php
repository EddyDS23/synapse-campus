<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\StudentController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn()=> redirect('/dashboard'));

Route::get('/login',   [AuthController::class, 'showLogin'])->name('login');
Route::post('/login',  [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware('auth.session')->group(function(){
    Route::get('/dashboard', fn() => view('dashboard'));

    //Student Portal
    Route::get('/profile',  [StudentController::class, 'profile']);
    Route::get('/schedule', [StudentController::class, 'schedule']);
    Route::get('/subjects', [StudentController::class, 'subjects']);
    Route::get('/status',   [StudentController::class, 'status']);
});

