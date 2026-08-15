<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\User;

class InternalController extends Controller
{
    
    public function security_status(Request $request, string $id): JsonResponse{
        Log::alert("Open",['message'=>"Iniciando proceso..."]);
        $user = User::where('id',$id)->first();

        if($user === null){
            return response()->json([],404);
        }

        $last_login = $user->auditLogs()
            ->whereIn('action',['login','login2fa','login_github'])
            ->latest('created_at')
            ->first();

        $active_sessions = $user->apiSessions()->count();
        Log::alert("DATA",['user_id'=>$user->id,
            'two_factor_enabled'=>$user->two_factor_enabled,
            'active_sessions'=> $active_sessions,
            'last_login'=> $last_login->created_at,
            'account_blocked'=> $user->unblocked_at !== null && $user->unblocked_at > now(),]);

        return response()->json([
            'user_id'=>$user->id,
            'two_factor_enabled'=>$user->two_factor_enabled,
            'active_sessions'=> $active_sessions,
            'last_login'=> $last_login->created_at,
            'account_blocked'=> $user->unblocked_at !== null && $user->unblocked_at > now(),
        ],200);

    }   


    public function basic_info(Request $request, string $teacherId):JsonResponse{

        $user = User::where('id',$teacherId)->first();


        if($user === null){
            return response()->json([],404);
        }

        return response()->json([
            'id'=>$teacherId,
            'name'=>$user->name,
            'email'=>$user->email
        ],200);


    }

}
