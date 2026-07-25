<?php

use App\Http\Controllers\TicketController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::patch('/tickets/{id}/reopen',[TicketController::class,'reopen'])->middleware(['jwt_check','check.scope:support:tickets:comment']);
Route::middleware(['jwt_check','check.scope:support:tickets:read'])->group(function(){
    Route::get('/tickets/my',[TicketController::class, 'getMyTickets']);
    Route::get('/tickets',[TicketController::class,'getAll']);
    Route::get('/tickets/{id}',[TicketController::class,'getOne']);
    
});

Route::post('/tickets', [TicketController::class,'create'])->middleware(['jwt_check','check.scope:support:tickets:create']);
Route::post('/tickets/{id}/comments',[TicketController::class,'comment'])->middleware(['jwt_check','check.scope:support:tickets:comment']);
Route::patch('/tickets/{id}/assign',[TicketController::class, 'assign'])->middleware(['jwt_check','check.scope:support:tickets:assign']);
Route::patch('/tickets/{id}/status', [TicketController::class,'status'])->middleware(['jwt_check','check.scope:support:tickets:close']);

