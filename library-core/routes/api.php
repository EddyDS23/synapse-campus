<?php

use App\Http\Controllers\BookController;
use App\Http\Controllers\LoanController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['jwt_check','check.scope:library:books:read'])->group(function(){
    Route::get('/books',[BookController::class,'getAll']);
    Route::get('/books/{id}',[BookController::class,'getOne']);
});

Route::middleware(['jwt_check','check.scope:library:loans:create'])->group(function(){
    Route::post('/loans/{id}',[LoanController::class, 'index']);
});

Route::get('/loans/my', [LoanController::class,'getLoansUser'])->middleware(['jwt_check','check.scope:library:loans:read']);

Route::post('/loans/{id}/renew',[LoanController::class,'renew'])->middleware(['jwt_check','check.scope:library:loans:renew']);

