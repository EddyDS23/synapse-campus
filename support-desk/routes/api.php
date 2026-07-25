<?php

use App\Http\Controllers\TicketController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


// Endpoints for requester
Route::patch('/tickets/{id}/reopen',[TicketController::class,'reopen'])->middleware(['jwt_check','check.scope:support:tickets:comment']);
Route::middleware(['jwt_check','check.scope:support:tickets:read'])->group(function(){
    Route::get('/tickets/my',[TicketController::class, 'getMyTickets']);
    Route::get('/tickets',[TicketController::class,'getAll']);
    Route::get('/tickets/{id}',[TicketController::class,'getOne']);
    
});


// Endpoints for agents
Route::post('/tickets', [TicketController::class,'create'])->middleware(['jwt_check','check.scope:support:tickets:create']);
Route::post('/tickets/{id}/comments',[TicketController::class,'comment'])->middleware(['jwt_check','check.scope:support:tickets:comment']);
Route::patch('/tickets/{id}/assign',[TicketController::class, 'assign'])->middleware(['jwt_check','check.scope:support:tickets:assign']);
Route::patch('/tickets/{id}/status', [TicketController::class,'status'])->middleware(['jwt_check','check.scope:support:tickets:close']);

// Endpoint for agent with role security_admin
Route::get('/tickets/{id}/security-status', [TicketController::class,'security_context'])->middleware(['jwt_check','check.scope:support:tickets:read']);