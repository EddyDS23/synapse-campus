<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['authvault.jwt'])->group(function(){
    Route::get('/payload', function(Request $request){
        return response()->json(['claims'=>$request->attributes->get('jwt_payload')],200);
    });
});



Route::get('/home',function(){
    return response()->json(['message'=>'Im alive']);
});

