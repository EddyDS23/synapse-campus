<?php

namespace App\Http\Controllers;

use App\Models\ApiSession;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Laravel\Socialite\Socialite;
use PHPOpenSourceSaver\JWTAuth\JWTAuth;

use Illuminate\Support\Str;

use App\Models\User;


class OAuthController extends Controller
{

    public function __construct(private JWTAuth $jwt){}
    

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

        $token = $this->jwt->fromUser($user);
        $jti = $this->jwt->setToken($token)->getPayload()->get('jti');
        $exp = $this->jwt->setToken($token)->getPayload()->get('exp');

        $refreshTokenPlain = Str::random(64);
        $refreshTokenHash = hash('sha256',$refreshTokenPlain);

        ApiSession::create([
            'user_id'=>$user->id,
            'jti'=>$jti,
            'ip_address'=>$request->ip(),
            'device'=>$request->userAgent(),
            'refresh_token'=>$refreshTokenHash,
            'refresh_expires_at'=>now()->addDays(7),
            'expires_at'=>\Carbon\Carbon::createFromTimestamp($exp),
        ]);

        AuditLog::create([
            'user_id'=>$user->id,
            'action'=>'login_' . $provider,
            'ip_address'=>$request->ip(),
            'service' => 'auth-vault',
            'resource_type' => 'user',
            'resource_id' => $user_db->id,
        ]);

        return response()->json(['token'=>$token,'refresh_token'=>$refreshTokenPlain]);



    }

}
