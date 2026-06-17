<?php

namespace App\Http\Controllers;

use App\Http\Requests\Login2faRequest;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use App\Models\ApiSession;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use PragmaRX\Google2FALaravel\Google2FA;

use App\Models\User;
use App\Models\LoginAttempt;

class AuthController extends Controller
{
    public function __construct(private Google2FA $google2fa){}

    public function register(RegisterRequest $request):JsonResponse{
 
        $user = User::create($request->validated());

        $new_token = $user->createToken('auth-token');
        $token_id = $new_token->accessToken->id;
        $token = $new_token->plainTextToken;

        ApiSession::create([
            'user_id'=>$user->id,
            'token_id'=>$token_id,
            'ip_address'=>$request->ip(),
            'device'=>$request->userAgent()
        ]);

        return response()->json(['user'=>$user,'token'=>$token],201);
    }


    public function login(LoginRequest $request): JsonResponse{
        
        $user_db = User::where('email',$request->validated('email'))->first();
        
        if($user_db == null){
            return response()->json(['message'=>'User not found'],404);
        }

        if($user_db->unblocked_at !== null && $user_db->unblocked_at > now()){
            return response()->json(['message'=>'Your account is blocked to 30 minutes'],403);
        }

        if(!Auth::attempt($request->validated())){
            LoginAttempt::create([
                'email'=>$request->validated('email'),
                'ip_address'=>$request->ip(),
                'reason'=>'Login failed',
                'failed_at'=>now()
            ]);

            $attemps = LoginAttempt::where('email',$request->validated('email'))
                ->where('failed_at','>=',now()->subMinutes(15))
                ->count();


            if($attemps >= env('MAX_ATTEMPTS_LOGIN',5)){
                $user_db->update([
                    'unblocked_at'=> now()->addMinutes(30)
                ]);
            }

            return response()->json(['message'=>'Credentials Invalided'],422);
        }

        if($user_db->two_factor_enabled == true){
            return response()->json(['two_factor_required'=>true,'email'=>$user_db->email],);
        }

        /** @var User $user */
        $user = Auth::user();
        $new_token = $user->createToken('auth-token');
        $token = $new_token->plainTextToken;
        $token_id = $new_token->accessToken->id;
        
        ApiSession::create([
            'user_id'=>$user->id,
            'token_id'=>$token_id,
            'ip_address'=>$request->ip(),
            'device'=>$request->userAgent()
            ]);

        AuditLog::create([
            'user_id'=>$user->id,
            'action'=>'login',
            'ip_address'=>$request->ip()
        ]);

        return response()->json(['user'=>$user,"token"=>$token]);

    }

    public function login2fa(Login2faRequest $request): JsonResponse{

        $user_db = User::where('email',$request->validated('email'))->first();

        if(!$user_db){
            return response()->json(['message'=>'User not found'],404);
        }

        if(!$user_db->two_factor_enabled){
            return response()->json(['message'=>'User not activate two factor authentication'],400);
        }

        if(!$this->google2fa->verifyKey($user_db->two_factor_secret, $request->code)){
            $codes = $user_db->two_factor_recovery_codes;
            if(in_array($request->code,$codes)){
                $key = array_search($request->code,$codes);
                unset($codes[$key]);
                $user_db->two_factor_recovery_codes = array_values($codes);
                $user_db->save();
            }else{
                return response()->json([],422);
            }  
        }

        $new_token = $user_db->createToken('auth-token');
        $token = $new_token->plainTextToken;
        $token_id = $new_token->accessToken->id;


        ApiSession::create([
            'user_id'=>$user_db->id,
            'token_id'=>$token_id,
            'ip_address'=>$request->ip(),
            'device'=>$request->userAgent()
            ]);

        
        AuditLog::create([
            'user_id'=>$user_db->id,
            'action'=>'login2fa',
            'ip_address'=>$request->ip()
        ]);

        return response()->json(['user'=>$user_db,'token'=>$token]);

    }


    public function logout(Request $request ): JsonResponse{

        $request->user()->currentAccessToken()->delete();

        return response()->json(['message'=>'Logget out']);
    }

    public function logoutAll(Request $request):JsonResponse{
        
        $request->user()->tokens()->delete();

        return response()->json(['message'=>'Logget out from all devices']);
    }

    public function refresh(Request $request):JsonResponse{

        $request->user()->currentAccessToken()->delete();

        $new_token = $request->user()->createToken('auth-token')->plainTextToken;

        return response()->json(["newToken"=>$new_token]);
    }
}


