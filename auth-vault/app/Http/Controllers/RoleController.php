<?php

namespace App\Http\Controllers;

use App\Services\AuditLogServiceClient;
use Illuminate\Http\Request;
use PHPOpenSourceSaver\JWTAuth\JWTAuth;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Models\Role;
use App\Models\User;
use App\Models\AuditLog;


class RoleController extends Controller
{
    public function __construct(
        private JWTAuth $jwt,
        private AuditLogServiceClient $auditLog
    ) {}

    public function assign(Request $request, string $id): JsonResponse
    {
        $roleName = $request->input('role');
        $token = $this->jwt->getToken();
        $sub = $this->jwt->setToken($token)->getClaim('sub');

        $role = Role::where('name', $roleName)->first();

        if ($role == null) {
            return response()->json(['message' => 'Role not found'], 404);
        }

        $user = User::where('id', $id)->first();

        if ($user == null) {
            return response()->json(['message' => 'User not found'], 404);
        }

        if ($user->roles->contains('name', $role->name)) {
            return response()->json(['message' => 'User already has that role'], 409);
        }

        $user->roles()->attach($role->id);

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'role_assigned',
            'ip_address' => $request->ip(),
            'service' => 'auth-vault',
            'resource_type' => 'user_role',
            'resource_id' => $user->id,
            'metadata' => ['role' => $role->name],
        ]);

        $this->auditLog->sendLog([
            'actor_id' => $sub,
            'service' => 'auth-vault',
            'action' => 'role.assigned',
            'resource_type' => 'user_role',
            'resource_id' => $user->id,
            'ip_address' => $request->ip(),
            'metadata' => [
                'role' => $role->name,
                'assigned_to' => $user->id,
                'assigned_by' => $sub,
            ],
        ]);

        return response()->json(['message' => 'Updated user role'], 200);
    }

    public function revoke(Request $request, string $id, string $role): JsonResponse
    {
        $token = $this->jwt->getToken();
        $sub = $this->jwt->setToken($token)->getClaim('sub');

        $role = Role::where('name', $role)->first();

        if ($role == null) {
            return response()->json(['message' => 'Role not found'], 404);
        }

        $user = User::where('id', $id)->first();

        if ($user == null) {
            return response()->json(['message' => 'User not found'], 404);
        }

        if (!$user->roles->contains('name', $role->name)) {
            return response()->json(['message' => "User hasn't that role"], 200);
        }

        $user->roles()->detach($role->id);

        AuditLog::create([
            'user_id' => $request->user()->id,
            'action' => 'role_revoke',
            'ip_address' => $request->ip(),
            'service' => 'auth-vault',
            'resource_type' => 'user_role',
            'resource_id' => $user->id,
            'metadata' => ['role' => $role->name],
        ]);

        $this->auditLog->sendLog([
            'actor_id' => $sub,
            'service' => 'auth-vault',
            'action' => 'role.revoked',
            'resource_type' => 'user_role',
            'resource_id' => $user->id,
            'ip_address' => $request->ip(),
            'metadata' => [
                'role' => $role->name,
                'revoked_from' => $user->id,
                'revoked_by' => $sub,
            ],
        ]);

        return response()->json(['message' => 'Revoke user role'], 200);
    }
}