<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

use Firebase\JWT\JWT;
use Firebase\JWT\SignatureInvalidException;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\Key;
use UnexpectedValueException;

use App\Services\AuthVaultKeyProvider;
use Exception;
use Throwable;


class ServiceAuth
{

    public function __construct(private AuthVaultKeyProvider $authkey){}

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next,string $scope): Response
    {

        $token = $request->bearerToken();

        if($token === null){
            return response()->json(['message'=>'Bearer token not send',401]);
        }

        try {
            $publicKey = $this->authkey->getPublicKey();
        } catch (Exception $e) {
            return response()->json(['message'=>$e->getMessage()],503);
        }
        

        try {
            $payload = JWT::decode($token, new Key($publicKey,'RS512'));
        } catch (ExpiredException $e) {
            return response()->json(['message'=>'Token expired'],401);
        }catch (SignatureInvalidException $e){
            return response()->json(["message"=>"Signature's Token invalid"],401);
        }catch (UnexpectedValueException $e){
            return response()->json(['message'=>"Format's token invalid"],400);
        }catch (Throwable $th){
            return response()->json(['message'=>'Error'],401);
        }

        $type = $payload->type ?? null;
        if($type !== 'service'){
            return response()->json([],403);
        }

        if(empty($payload->scopes) || !in_array($scope,$payload->scopes,true)){
            return response()->json([],403);
        }

        $request->attributes->set('jwt_payload',$payload);

        return $next($request);
    }
}
