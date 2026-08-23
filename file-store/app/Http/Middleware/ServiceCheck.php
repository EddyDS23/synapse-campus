<?php

namespace App\Http\Middleware;

use App\Services\AuthVaultKeyProvider;
use Closure;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\SignatureInvalidException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use UnexpectedValueException;

class ServiceCheck
{

    public function __construct(private AuthVaultKeyProvider $authKey) {}
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string $scope): Response
    {
        $token = $request->bearerToken();

        if ($token === null) {
            return response()->json(['message' => 'Bearer token no enviado'], 401);
        }

        try {
            $publicKey = $this->authKey->getPublicKey();
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 503);
        }

        try {
            $payload = JWT::decode($token, new Key($publicKey, 'RS256'));
        } catch (ExpiredException $e) {
            return response()->json(['message' => 'Token expirado'], 401);
        } catch (SignatureInvalidException $e) {
            return response()->json(['message' => 'Firma del token inválida'], 401);
        } catch (UnexpectedValueException $e) {
            return response()->json(['message' => 'Formato del token inválido'], 400);
        } catch (\Throwable $t) {
            return response()->json(['message' => 'Token inválido'], 401);
        }

        $type = $payload->type ?? null;
        if ($type !== 'service') {
            return response()->json(['message' => 'Token de servicio inválido'], 403);
        }

        if (!in_array($scope, $payload->scopes, true)) {
            return response()->json(['message' => 'Scopes insuficientes'], 403);
        }

        $request->attributes->set('jwt_payload', $payload);

        return $next($request);
    }
}
