<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdminCreateUserRequest;
use App\Models\AuditLog;
use App\Services\AuditLogServiceClient;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AdminController extends Controller
{

    public function __construct(
        private AuditLogServiceClient $auditLog
    ) {}

    public function createUser(AdminCreateUserRequest $request): JsonResponse
    {

        $admin = $request->user();

        $data = $request->validated();

        $temporary_password = Str::password(12);

        $user = null;
        $log = null;

        try {
            DB::transaction(function () use ($data,$admin,$request,$temporary_password, &$log, &$user) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $temporary_password,
                'must_change_password'=>true
            ]);

            $role = Role::where('name', $data['role'])->first();

            $user->roles()->attach($role->id);

            $log = [
                'user_id' => $admin->id,
                'action' => 'user.created_by_admin',
                'ip_address' => $request->ip(),
                'service' => 'auth-vault',
                'resource_type' => 'user',
                'resource_id' => $user->id,
                'metadata' => [
                    'email' => $user->email,
                    'name' => $user->name,
                ]
            ];

            AuditLog::create($log);
        });
        } catch (\Throwable $th) {
            return response()->json(['message'=>'Couldnt create user'],502);
        }
        
        if($log != null){
            $this->auditLog->sendLog($log);
        }

        return response()->json([
            'name' => $user != null ? $user->id : null,
            'email' => $user != null ? $user->email : null,
            'temporary_password' => $temporary_password,
            'must_change_password'=>true
        ], 201);
    }
}
