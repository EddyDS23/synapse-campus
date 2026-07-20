<?php

use App\Http\Controllers\BookController;
use App\Http\Controllers\FineController;
use App\Http\Controllers\InventoryController;
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

Route::post('/loans/{id}/return', [LoanController::class,'returnBook'])->middleware(['jwt_check']);

Route::get('/fines/my', [FineController::class,'getFinesByStudent'])->middleware(['jwt_check','check.scope:library:fines:read']);
Route::post('/fines/{id}/pay',[FineController::class,'pay'])->middleware(['jwt_check','check.scope:library:fines:pay']);


Route::middleware(['jwt_check','check.scope:library:inventory:manage'])->group(function(){
    Route::post('/books',[InventoryController::class,'createBook']);
    Route::patch('/books/{id}', [InventoryController::class,'updateBook']);
    Route::patch('/books/{id}/stock', [InventoryController::class,'updateBookStock']);
});
