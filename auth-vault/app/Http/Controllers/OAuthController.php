<?php

namespace App\Http\Controllers;

use App\Models\ApiSession;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Laravel\Socialite\Socialite;

use App\Models\User;

class OAuthController extends Controller
{

    public function redirect(Request $request, string $provider){

        return Socialite::driver($provider)->stateless()->redirect();

    }

    public function callback(Request $request, string $provider){

        $user_provider = Socialite::driver($provider)->stateless()->user();

        $user_db = User::where('provider_id',$user_provider->getId())->first();

        if($user_db == null){
            $user = User::create([
                'name'=>$user_provider->getName(),
                'email'=>$user_provider->getEmail(),
                'provider'=>'github',
                'provider_id'=>$user_provider->getId(),
                'password'=>null
            ]);
        }else{
            $user = $user_db;
        }

        $new_token = $user->createToken('auth-token');
        $token_id = $new_token->accessToken->id;
        $token = $new_token->plainTextToken;


        ApiSession::create([
            'user_id'=>$user->id,
            'token_id'=>$token_id,
            'ip_address'=>$request->ip(),
            'device'=>$request->userAgent()
        ]);

        AuditLog::create([
            'user_id'=>$user->id,
            'action'=>'login_' . $provider,
            'ip_address'=>$request->ip()
        ]);

        return response()->json(['user'=>$user,'token'=>$token]);



    }

}
