<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;
use UnexpectedValueException;

use App\Services\AuthVaultKeyProvider;

class ServiceCheck
{

    public function __construct(private AuthVaultKeyProvider $authKey){}

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string $scope): Response
    {

        $token = $request->bearerToken();

        if($token === null){
            return response()->json(['message'=>'Bearer token not send'],401);
        }

        try {
            $publicKey = $this->authKey->getPublicKey();
        } catch (\Exception $e) {
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
        
        $type = $payload->type ?? null;
        if($type !== 'service'){
            return response()->json(['message'=>'This token is invalid'],403);
        }


        if(!in_array($scope,$payload->scopes,true)){
            return response()->json(['message'=>'This token havent enought scopes'],403);
        }

        $request->attributes->set('jwt_payload',$payload);


        return $next($request);
    }
}
