<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

use App\Services\AuthVaultKeyProvider;
use Exception;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;
use UnexpectedValueException;

class JWTCheck
{

    public function __construct(private AuthVaultKeyProvider $authkey){}
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {


        $token = $request->bearerToken();

        if($token === null){
            return response()->json(['message'=>'Bearer token not send'],401);
        } 

        try {
            $publicKey = $this->authkey->getPublicKey();
        } catch (Exception $e) {
            return response()->json(['message'=>$e->getMessage()],503);
        }

        try {
            $payload = JWT::decode($token, new Key($publicKey,'RS512'));
        } catch (ExpiredException $e) {
            return response()->json(['message'=>'Bearer token expired'],401);
        } catch (SignatureInvalidException $e){
            return response()->json(['message'=>"Signature's bearer token invalid"],401);
        } catch (UnexpectedValueException $e){
            return response()->json(['message'=>"Format's bearer token invalid "],400);
        } catch(\Throwable $t){
            return response()->json(['message'=>'Error'],401);
        }

        if($payload->aud !== 'student-portal'){
            return response()->json(['message'=>'Bearer token invalid to this service'],403);
        }


        $request->attributes->set('jwt_payload',$payload);

        return $next($request);
    }
}


