<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{

    public function me(Request $request){
        
        $user = $request->user();

        return response()->json([
            "user"=>$user
        ]);

    }

    public function securityStatus(Request $request, string $id){

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
            'last_login'=> $last_login->created_at ?? null,
            'account_blocked'=> $user->unblocked_at !== null && $user->unblocked_at > now(),
        ],200);

    }

}
