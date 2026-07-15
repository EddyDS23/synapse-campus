<?php

namespace App\Http\Controllers;

use App\Http\Requests\JWTExchangeRequest;
use App\Http\Requests\Login2faRequest;
use App\Services\AuditLogServiceClient;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RefreshAccessTokenRequest;
use App\Models\ApiSession;
use App\Models\AuditLog;
use PragmaRX\Google2FALaravel\Google2FA;

use Illuminate\Support\Str;

use App\Models\User;
use App\Models\LoginAttempt;

class AuthController extends Controller
{
    public function __construct(
        private Google2FA $google2fa,
        private \PHPOpenSourceSaver\JWTAuth\JWTAuth $jwt,
        private AuditLogServiceClient $auditLog
    ) {}

    public function register(RegisterRequest $request): JsonResponse
    {
        $user = User::create($request->validated());

        $token = $this->jwt->fromUser($user);
        $jti = $this->jwt->setToken($token)->getPayload()->get('jti');
        $exp = $this->jwt->setToken($token)->getPayload()->get('exp');

        $refreshTokenPlain = Str::random(64);
        $refreshTokenHash = hash('sha256', $refreshTokenPlain);

        $session = ApiSession::create([
            'user_id' => $user->id,
            'jti' => $jti,
            'ip_address' => $request->ip(),
            'device' => $request->userAgent(),
            'refresh_token' => $refreshTokenHash,
            'refresh_expires_at' => now()->addDays(7),
            'expires_at' => \Carbon\Carbon::createFromTimestamp($exp),
        ]);

        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'user.registered',
            'ip_address' => $request->ip(),
            'service' => 'auth-vault',
            'resource_type' => 'user',
            'resource_id' => $user->id,
        ]);

        $this->auditLog->sendLog([
            'service' => 'auth-vault',
            'action' => 'user.registered',
            'resource_type' => 'user',
            'resource_id' => $user->id,
            'ip_address' => $request->ip(),
            'metadata' => [
                'email' => $user->email,
            ],
        ]);

        return response()->json(['token' => $token, 'refresh_token' => $refreshTokenPlain], 201);
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $user_db = User::where('email', $request->validated('email'))->first();

        if ($user_db == null) {
            return response()->json(['message' => 'User not found'], 404);
        }

        if ($user_db->unblocked_at !== null && $user_db->unblocked_at > now()) {
            return response()->json(['message' => 'Your account is blocked to 30 minutes'], 403);
        }

        $token = $this->jwt->attempt($request->validated());

        if ($token === false) {
            $attempt = LoginAttempt::create([
                'email' => $request->validated('email'),
                'ip_address' => $request->ip(),
                'reason' => 'Login failed',
                'failed_at' => now()
            ]);

            $this->auditLog->sendLog([
                'actor_id' => $user_db->id,
                'service' => 'auth-vault',
                'action' => 'user.login_failed',
                'resource_type' => 'login_attempt',
                'resource_id' => $attempt->id,
                'ip_address' => $request->ip(),
                'metadata' => [
                    'failed_at' => now(),
                ],
            ]);

            $attemps = LoginAttempt::where('email', $request->validated('email'))
                ->where('failed_at', '>=', now()->subMinutes(15))
                ->count();

            if ($attemps >= env('MAX_ATTEMPTS_LOGIN', 5)) {
                $user_db->update([
                    'unblocked_at' => now()->addMinutes(30)
                ]);

                $this->auditLog->sendLog([
                    'actor_id' => $user_db->id,
                    'service' => 'auth-vault',
                    'action' => 'user.blocked',
                    'resource_type' => 'users',
                    'resource_id' => $user_db->id,
                    'ip_address' => $request->ip(),
                    'metadata' => [
                        'blocked_at' => now(),
                        'attempts' => $attemps,
                    ],
                ]);
            }

            return response()->json(['message' => 'Credentials Invalided'], 422);
        }

        $jti = $this->jwt->setToken($token)->getPayload()->get('jti');
        $exp = $this->jwt->setToken($token)->getPayload()->get('exp');
        $sub = $this->jwt->setToken($token)->getPayload()->get('sub');

        if ($user_db->two_factor_enabled == true) {
            return response()->json(['two_factor_required' => true, 'email' => $user_db->email]);
        }

        $refreshTokenPlain = Str::random(64);
        $refreshTokenHash = hash('sha256', $refreshTokenPlain);

        $apiSession = ApiSession::create([
            'user_id' => $user_db->id,
            'jti' => $jti,
            'ip_address' => $request->ip(),
            'device' => $request->userAgent(),
            'refresh_token' => $refreshTokenHash,
            'refresh_expires_at' => now()->addDays(7),
            'expires_at' => \Carbon\Carbon::createFromTimestamp($exp),
        ]);

        $audit = AuditLog::create([
            'user_id' => $user_db->id,
            'action' => 'login',
            'ip_address' => $request->ip(),
            'service' => 'auth-vault',
            'resource_type' => 'user',
            'resource_id' => $user_db->id,
        ]);

        $this->auditLog->sendLog([
            'actor_id' => $sub,
            'service' => 'auth-vault',
            'action' => 'api_session.created',
            'resource_type' => 'api_sessions',
            'resource_id' => $apiSession->id,
            'ip_address' => $request->ip(),
            'metadata' => [
                'user_agent' => $request->userAgent(),
                'roles' => $this->jwt->setToken($token)->getPayload()->get('roles'),
            ],
        ]);

        $this->auditLog->sendLog([
            'actor_id' => $sub,
            'service' => 'auth-vault',
            'action' => 'user.login_successful',
            'resource_type' => 'audit_logs',
            'resource_id' => $audit->id,
            'ip_address' => $request->ip(),
            'metadata' => [
                'user_agent' => $request->userAgent(),
                '2fa_used' => false,
            ],
        ]);

        return response()->json([
            "token" => $token,
            "refresh_token" => $refreshTokenPlain,
        ]);
    }

    public function login2fa(Login2faRequest $request): JsonResponse
    {
        $user_db = User::where('email', $request->validated('email'))->first();

        if (!$user_db) {
            return response()->json(['message' => 'User not found'], 404);
        }

        if (!$user_db->two_factor_enabled) {
            return response()->json(['message' => 'User not activate two factor authentication'], 400);
        }

        if (!$this->google2fa->verifyKey($user_db->two_factor_secret, $request->code)) {
            $codes = $user_db->two_factor_recovery_codes;
            if (in_array($request->code, $codes)) {
                $key = array_search($request->code, $codes);
                unset($codes[$key]);
                $user_db->two_factor_recovery_codes = array_values($codes);
                $user_db->save();
            } else {
                return response()->json([], 422);
            }
        }

        $token = $this->jwt->fromUser($user_db);
        $jti = $this->jwt->setToken($token)->getPayload()->get('jti');
        $exp = $this->jwt->setToken($token)->getPayload()->get('exp');
        $sub = $this->jwt->setToken($token)->getPayload()->get('sub');

        $refreshTokenPlain = Str::random(64);
        $refreshTokenHash = hash('sha256', $refreshTokenPlain);

        $apiSession = ApiSession::create([
            'user_id' => $user_db->id,
            'jti' => $jti,
            'ip_address' => $request->ip(),
            'device' => $request->userAgent(),
            'refresh_token' => $refreshTokenHash,
            'refresh_expires_at' => now()->addDays(7),
            'expires_at' => \Carbon\Carbon::createFromTimestamp($exp),
        ]);

        $audit = AuditLog::create([
            'user_id' => $user_db->id,
            'action' => 'login2fa',
            'ip_address' => $request->ip(),
            'service' => 'auth-vault',
            'resource_type' => 'user',
            'resource_id' => $user_db->id,
        ]);

        $this->auditLog->sendLog([
            'actor_id' => $sub,
            'service' => 'auth-vault',
            'action' => 'api_session.created',
            'resource_type' => 'api_sessions',
            'resource_id' => $apiSession->id,
            'ip_address' => $request->ip(),
            'metadata' => [
                'user_agent' => $request->userAgent(),
                'roles' => $this->jwt->setToken($token)->getPayload()->get('roles'),
            ],
        ]);

        $this->auditLog->sendLog([
            'actor_id' => $sub,
            'service' => 'auth-vault',
            'action' => 'user.login_successful',
            'resource_type' => 'audit_logs',
            'resource_id' => $audit->id,
            'ip_address' => $request->ip(),
            'metadata' => [
                'user_agent' => $request->userAgent(),
                '2fa_used' => true,
            ],
        ]);

        return response()->json([
            'token' => $token,
            'refresh_token' => $refreshTokenPlain,
        ]);
    }

    public function logout(Request $request): JsonResponse
    {   
        $token = $this->jwt->getToken();
        $payload = $this->jwt->setToken($token)->getPayload();
        $jti = $payload->get('jti');
        $sub = $payload->get('sub');

        $this->jwt->invalidate($token);

        AuditLog::create([
            'user_id' => $sub,
            'action' => 'user.logout',
            'ip_address' => $request->ip(),
            'service' => 'auth-vault',
            'resource_type' => 'api_session',
            'resource_id' => $jti,
        ]);

        $this->auditLog->sendLog([
            'actor_id' => $sub,
            'service' => 'auth-vault',
            'action' => 'user.logout',
            'resource_type' => 'api_session',
            'resource_id' => $jti,
            'ip_address' => $request->ip(),
            'metadata' => [
                'user_agent'=>$request->userAgent(),
                'jti'=>$jti
            ],
        ]);

        return response()->json(['message' => 'Logged out']);
    }

    public function logoutAll(Request $request): JsonResponse
    {
        $token = $this->jwt->getToken();
        $payload = $this->jwt->setToken($token)->getPayload();
        $jti = $payload->get('jti');
        $sub = $payload->get('sub');

        $request->user()->update([
            'tokens_invalidated_at' => now(),
        ]);

        $this->auditLog->sendLog([
            'actor_id' => $sub,
            'service' => 'auth-vault',
            'action' => 'user.logout_all',
            'resource_type' => 'users',
            'resource_id' => $sub,
            'ip_address' => $request->ip(),
            'metadata' => [
                'user_agent'=>$request->userAgent(),
                'jti'=>$jti
            ],
        ]);

        return response()->json(['message' => 'Logged out from all devices']);
    }

    public function refreshWithToken(RefreshAccessTokenRequest $request): JsonResponse
    {
        $refresh_token = $request->validated('refresh_token');
        $refresh_token_hash = hash('sha256', $refresh_token);

        $session = ApiSession::where('refresh_token', $refresh_token_hash)->first();

        if ($session === null || $session->refresh_expires_at < now()) {
            return response()->json(["message" => "Invalid or expired refresh token"], 401);
        }

        $user = $session->user;
        $old_jti = $session->jti;

        $new_token_access = $this->jwt->fromUser($user);
        $new_jti = $this->jwt->setToken($new_token_access)->getPayload()->get('jti');
        $new_exp = $this->jwt->setToken($new_token_access)->getPayload()->get('exp');
        $sub = $this->jwt->setToken($new_token_access)->getPayload()->get('sub');

        $new_token_refresh = Str::random(64);
        $new_token_refresh_hash = hash('sha256', $new_token_refresh);

        $session->update([
            'jti' => $new_jti,
            'expires_at' => \Carbon\Carbon::createFromTimestamp($new_exp),
            'refresh_token' => $new_token_refresh_hash,
            'refresh_expires_at' => now()->addDays(7)
        ]);

        $this->auditLog->sendLog([
            'actor_id' => $sub,
            'service' => 'auth-vault',
            'action' => 'token.refreshed',
            'resource_type' => 'api_session',
            'resource_id' => $new_jti,
            'ip_address' => $request->ip(),
            'metadata' => [
                'old_jti' => $old_jti,
                'new_jti' => $new_jti,
                'user_agent' => $request->userAgent(),
            ],
        ]);

        return response()->json([
            "token" => $new_token_access,
            "refresh_token" => $new_token_refresh
        ]);
    }

    public function exchangeToken(JWTExchangeRequest $request): JsonResponse
    {
        $user = $request->user();
        $audience = $request->validated('audience');
        $token = $this->jwt->getToken();
        $payload = $this->jwt->setToken($token)->getPayload();
        $jti = $payload->get('jti');
        $sub = $payload->get('sub');

        $token_exchange = $this->jwt->claims(['aud' => $audience])->fromUser($user);
        $new_jti = $this->jwt->setToken($token_exchange)->getPayload()->get('jti');
        $scopes = $this->jwt->setToken($token_exchange)->getPayload()->get('scopes');

        $this->auditLog->sendLog([
            'actor_id' => $sub,
            'service' => 'auth-vault',
            'action' => 'token.exchanged',
            'resource_type' => 'api_session',
            'resource_id' => $jti,
            'ip_address' => $request->ip(),
            'metadata' => [
                'target_service' => $audience,
                'scopes' => $scopes,
                'new_jti' => $new_jti,
            ],
        ]);

        return response()->json(['token' => $token_exchange]);
    }
}