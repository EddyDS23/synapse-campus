<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

use App\Models\User;

class InternalController extends Controller
{
    
    public function security_status(Request $request, string $id): JsonResponse{

        $user = User::where('id',$id)->first();

        if($user === null){
            return response()->json([],404);
        }

        $last_login = $user->auditLogs()
            ->whereIn('action',['login','login2fa','login_github'])
            ->latest('created_at')
            ->first();

        $active_sessions = $user->apiSessions()->count();

        return response()->json([
            'user_id'=>$user->id,
            'two_factor_enabled'=>$user->two_factor_enabled,
            'active_sessions'=> $active_sessions,
            'last_login'=> $last_login->created_at,
            'account_blocked'=> $user->unblocked_at !== null && $user->unblocked_at > now(),
        ],200);

    }   

}
