<?php

namespace App\Http\Middleware;

use App\Services\AuthVaultKeyProvider;
use Closure;
use Exception;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\SignatureInvalidException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use UnexpectedValueException;

class JWTCheck
{
    public function __construct(private AuthVaultKeyProvider $authkey) {}
    

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if ($token === null) {
            return response()->json(['message' => 'Bearer token no enviado'], 401);
        }

        try {
            $publicKey = $this->authkey->getPublicKey();
        } catch (Exception $e) {
            return response()->json(['message' => $e->getMessage()], 503);
        }

        try {
            $payload = JWT::decode($token, new Key($publicKey, 'RS512'));
        } catch (ExpiredException $e) {
            return response()->json(['message' => 'Token expirado'], 401);
        } catch (SignatureInvalidException $e) {
            return response()->json(['message' => 'Firma del token inválida'], 401);
        } catch (UnexpectedValueException $e) {
            return response()->json(['message' => 'Formato del token inválido'], 400);
        } catch (\Throwable $t) {
            return response()->json(['message' => 'Token inválido'], 401);
        }

        if ($payload->aud !== 'file-store') {
            return response()->json(['message' => 'Token inválido para este servicio'], 403);
        }

        if ($payload->sub === null) {
            return response()->json(['message' => 'Token incompleto'], 403);
        }

        $request->attributes->set('jwt_payload', $payload);

        return $next($request);
    }
}
