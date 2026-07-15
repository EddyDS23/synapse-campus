<?php

namespace App\Http\Controllers;

use App\Models\ApiSession;
use App\Services\AuditLogServiceClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use PHPOpenSourceSaver\JWTAuth\JWTAuth;

use App\Models\AuditLog;

class SessionController extends Controller
{
    public function __construct(
        private JWTAuth $jwt,
        private AuditLogServiceClient $auditLog
    ) {}

    public function get(Request $request): JsonResponse
    {
        $user = $request->user();

        $sessions_current = $user->apiSessions()->orderBy('created_at', 'desc')->get();

        return response()->json(['user' => $user->id, 'sessions' => $sessions_current], 200);
    }

    public function delete(Request $request, string $id): JsonResponse
    {
        $user = $request->user();
        $token = $this->jwt->getToken();
        $sub = $this->jwt->setToken($token)->getClaim('sub');

        $session = ApiSession::where('id', $id)->first();

        if ($session == null) {
            return response()->json([], 404);
        }

        $jti = $session->jti;

        $ttl = now()->diffInSeconds($session->expires_at, false);

        if ($ttl > 0) {
            Cache::put("session_revoked:{$session->jti}", true, $ttl);
        }

        $session->delete();

        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'session_revoked',
            'ip_address' => $request->ip(),
            'service' => 'auth-vault',
            'resource_type' => 'api_session',
            'resource_id' => $id,
        ]);

        $this->auditLog->sendLog([
            'actor_id' => $sub,
            'service' => 'auth-vault',
            'action' => 'session.deleted',
            'resource_type' => 'api_session',
            'resource_id' => $jti,
            'ip_address' => $request->ip(),
            'metadata' => [
                'user_agent'=>$request->userAgent(),
            ],
        ]);

        return response()->json([], 200);
    }
}