<?php

namespace App\Http\Controllers;

use App\Http\Requests\JWTExchangeRequest;
use App\Http\Requests\Login2faRequest;
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
    public function __construct(private Google2FA $google2fa, private \PHPOpenSourceSaver\JWTAuth\JWTAuth $jwt) {}

    public function register(RegisterRequest $request): JsonResponse
    {

        $user = User::create($request->validated());

        $token = $this->jwt->fromUser($user);
        $jti = $this->jwt->setToken($token)->getPayload()->get('jti');
        $exp = $this->jwt->setToken($token)->getPayload()->get('exp');

        $refreshTokenPlain = Str::random(64);
        $refreshTokenHash = hash('sha256', $refreshTokenPlain);

        ApiSession::create([
            'user_id' => $user->id,
            'jti' => $jti,
            'ip_address' => $request->ip(),
            'device' => $request->userAgent(),
            'refresh_token' => $refreshTokenHash,
            'refresh_expires_at' => now()->addDays(7),
            'expires_at' => \Carbon\Carbon::createFromTimestamp($exp),
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
        $jti = $this->jwt->setToken($token)->getPayload()->get('jti');
        $exp = $this->jwt->setToken($token)->getPayload()->get('exp');

        if ($token === false) {
            LoginAttempt::create([
                'email' => $request->validated('email'),
                'ip_address' => $request->ip(),
                'reason' => 'Login failed',
                'failed_at' => now()
            ]);

            $attemps = LoginAttempt::where('email', $request->validated('email'))
                ->where('failed_at', '>=', now()->subMinutes(15))
                ->count();


            if ($attemps >= env('MAX_ATTEMPTS_LOGIN', 5)) {
                $user_db->update([
                    'unblocked_at' => now()->addMinutes(30)
                ]);
            }

            return response()->json(['message' => 'Credentials Invalided'], 422);
        }

        if ($user_db->two_factor_enabled == true) {
            return response()->json(['two_factor_required' => true, 'email' => $user_db->email],);
        }

        $refreshTokenPlain = Str::random(64);
        $refreshTokenHash = hash('sha256', $refreshTokenPlain);


        ApiSession::create([
            'user_id' => $user_db->id,
            'jti' => $jti,
            'ip_address' => $request->ip(),
            'device' => $request->userAgent(),
            'refresh_token' => $refreshTokenHash,
            'refresh_expires_at' => now()->addDays(7),
            'expires_at' => \Carbon\Carbon::createFromTimestamp($exp),
        ]);

        AuditLog::create([
            'user_id' => $user_db->id,
            'action' => 'login',
            'ip_address' => $request->ip(),
            'service' => 'auth-vault',
            'resource_type' => 'user',
            'resource_id' => $user_db->id,
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

        $refreshTokenPlain = Str::random(64);
        $refreshTokenHash = hash('sha256', $refreshTokenPlain);

        ApiSession::create([
            'user_id' => $user_db->id,
            'jti' => $jti,
            'ip_address' => $request->ip(),
            'device' => $request->userAgent(),
            'refresh_token' => $refreshTokenHash,
            'refresh_expires_at' => now()->addDays(7),
            'expires_at' => \Carbon\Carbon::createFromTimestamp($exp),
        ]);


        AuditLog::create([
            'user_id' => $user_db->id,
            'action' => 'login2fa',
            'ip_address' => $request->ip(),
            'service' => 'auth-vault',
            'resource_type' => 'user',
            'resource_id' => $user_db->id,
        ]);

        return response()->json([
            'token' => $token,
            'refresh_token' => $refreshTokenPlain,
        ]);
    }


    public function logout(Request $request): JsonResponse
    {

        $this->jwt->invalidate($this->jwt->getToken());

        return response()->json(['message' => 'Logget out']);
    }

    public function logoutAll(Request $request): JsonResponse
    {

        $request->user()->update([
            'tokens_invalidated_at' => now(),
        ]);

        return response()->json(['message' => 'Logget out from all devices']);
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
        $new_token_access = $this->jwt->fromUser($user);
        $new_jti = $this->jwt->setToken($new_token_access)->getPayload()->get('jti');
        $new_exp = $this->jwt->setToken($new_token_access)->getPayload()->get('exp');


        $new_token_refresh = Str::random(64);
        $new_token_refresh_hash = hash('sha256', $new_token_refresh);

        $session->update([
            'jti' => $new_jti,
            'expires_at' => \Carbon\Carbon::createFromTimestamp($new_exp),
            'refresh_token' => $new_token_refresh_hash,
            'refresh_expires_at' => now()->addDays(7)
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

        $token_exchange = $this->jwt->claims(['aud' => $audience])->fromUser($user);

        return response()->json(['token' => $token_exchange]);
    }
}
