<?php

namespace App\Http\Controllers;

use App\Models\ApiSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\PersonalAccessToken;

use App\Models\AuditLog;

class SessionController extends Controller
{
    public function get(Request $request): JsonResponse
    {

        $user = $request->user();

        $sessions_current = $user->apiSessions()->orderBy('created_at', 'desc')->get();

        return response()->json(['user' => $user->id, 'sessions' => $sessions_current], 200);
    }

    public function delete(Request $request, string $id): JsonResponse
    {

        $user = $request->user();

        $session = $user->apiSessions()->where('id', $id)->first();

        if ($session == null) {
            return response()->json([], 404);
        }

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
            'resource_id' => $session->id,
        ]);

        return response()->json([], 200);
    }
}
