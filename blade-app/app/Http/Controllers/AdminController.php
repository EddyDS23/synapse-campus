<?php

namespace App\Http\Controllers;

use App\Exceptions\BusinessException;
use App\Exceptions\ServiceUnavailableException;
use App\Helpers\RoleHelper;
use App\Services\AuthVaultService;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function __construct(private AuthVaultService $authVault) {}

    // ─── Dashboard admin ──────────────────────────────────────────────────────

    public function dashboard(): mixed
    {
        if (!RoleHelper::hasAnyRole(['security_admin', 'super_admin', 'academic_admin'])) {
            abort(403);
        }

        return view('admin.dashboard');
    }

    // ─── Gestión de roles ─────────────────────────────────────────────────────

    public function users(): mixed
    {
        if (!RoleHelper::hasAnyRole(['security_admin', 'super_admin'])) {
            abort(403);
        }
        $data = ['roles'=>['student','teacher','librarian','support_agent','academic_admin','security_admin','super_admin']];
        return view('admin.users',$data);
    }

    public function assignRole(Request $request): mixed
    {
        if (!RoleHelper::hasAnyRole(['security_admin', 'super_admin'])) {
            abort(403);
        }

        $validated = $request->validate([
            'user_id' => ['required', 'string'],
            'role'    => ['required', 'string', 'in:student,teacher,librarian,support_agent,academic_admin,security_admin,super_admin'],
        ]);

        try {
            $this->authVault->assignRole($validated['user_id'], $validated['role']);
        } catch (BusinessException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        } catch (ServiceUnavailableException $e) {
            return back()->withErrors(['error' => 'No se pudo asignar el rol.']);
        }

        return back()->with('success', 'Rol asignado correctamente.');
    }

    public function revokeRole(Request $request, string $userId, string $role): mixed
    {
        if (!RoleHelper::hasAnyRole(['security_admin', 'super_admin'])) {
            abort(403);
        }

        try {
            $this->authVault->revokeRole($userId, $role);
        } catch (ServiceUnavailableException $e) {
            return back()->withErrors(['error' => 'No se pudo revocar el rol.']);
        }

        return back()->with('success', 'Rol revocado correctamente.');
    }

    // ─── Security status de usuario ───────────────────────────────────────────

    public function userSecurityStatus(Request $request): mixed
    {
        if (!RoleHelper::hasAnyRole(['security_admin', 'super_admin'])) {
            abort(403);
        }

        $userId = $request->query('user_id');
        $data   = null;

        if ($userId) {
            try {
                $data = $this->authVault->getUserSecurityStatus($userId);
            } catch (BusinessException $e) {
                return back()->withErrors(['error' => $e->getMessage()]);
            } catch (ServiceUnavailableException $e) {
                return back()->withErrors(['error' => 'No se pudo obtener el estado de seguridad.']);
            }
        }

        return view('admin.users', [
            'userId' => $userId,
            'data'   => $data,
            'roles'  => ['student', 'teacher', 'librarian', 'support_agent', 'academic_admin', 'security_admin', 'super_admin'],
        ]);
    }

    // ─── Audit logs (admin) ───────────────────────────────────────────────────

    public function audit(): mixed
    {
        if (!RoleHelper::hasAnyRole(['security_admin', 'super_admin', 'academic_admin'])) {
            abort(403);
        }

        try {
            $data = $this->authVault->getAuditLogs();
        } catch (ServiceUnavailableException $e) {
            return view('errors.service')->with('message', 'AuthVault no disponible.');
        }

        return view('admin.audit', [
            'logs' => $data['logs']['data'] ?? [],
            'meta' => $data['logs'] ?? [],
        ]);
    }
}