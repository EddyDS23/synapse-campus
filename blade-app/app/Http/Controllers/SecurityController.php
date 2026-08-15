<?php

namespace App\Http\Controllers;

use App\Exceptions\BusinessException;
use App\Exceptions\InvalidCredentialsException;
use App\Exceptions\ServiceUnavailableException;
use App\Services\AuthVaultService;
use Illuminate\Http\Request;

class SecurityController extends Controller
{
    public function __construct(private AuthVaultService $authVault) {}

    // ─── Vista principal de seguridad ─────────────────────────────────────────

    public function index(): mixed
    {
        try {
            $sessions  = $this->authVault->getSessions();
            $auditLogs = $this->authVault->getAuditLogs();
        } catch (ServiceUnavailableException $e) {
            return view('errors.service')->with('message', 'AuthVault no disponible en este momento.');
        }

        return view('security.index', [
            'sessions'  => $sessions['sessions'] ?? [],
            'auditLogs' => $auditLogs['logs']['data'] ?? [],
            'logsMeta'  => $auditLogs['logs'] ?? [],
        ]);
    }

    // ─── Sesiones ─────────────────────────────────────────────────────────────

    public function deleteSession(string $id): mixed
    {
        try {
            $this->authVault->deleteSession($id);
        } catch (ServiceUnavailableException $e) {
            return back()->withErrors(['error' => 'No se pudo cerrar la sesión.']);
        }

        return back()->with('success', 'Sesión cerrada correctamente.');
    }

    // ─── 2FA ──────────────────────────────────────────────────────────────────

    public function enable2fa(): mixed
    {
        try {
            $data = $this->authVault->enable2fa();
        } catch (BusinessException $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        } catch (ServiceUnavailableException $e) {
            return back()->withErrors(['error' => 'No se pudo activar el 2FA.']);
        }

        return view('security.2fa-enable', [
            'qr' => $data['qr'] ?? null,
        ]);
    }

    public function verify2fa(Request $request): mixed
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'digits:6'],
        ]);

        try {
            $result = $this->authVault->verify2fa($validated['code']);
        } catch (BusinessException $e) {
            return back()->withErrors(['code' => $e->getMessage()]);
        } catch (ServiceUnavailableException $e) {
            return back()->withErrors(['code' => 'No se pudo verificar el código.']);
        }

        return view('security.2fa-codes', [
            'codes' => $result['two_factor_recovery_codes'] ?? [],
        ]);
    }

    public function disable2fa(Request $request): mixed
    {
        $validated = $request->validate([
            'code' => ['required', 'string'],
        ]);

        try {
            $this->authVault->disable2fa($validated['code']);
        } catch (BusinessException $e) {
            return back()->withErrors(['code' => $e->getMessage()]);
        } catch (ServiceUnavailableException $e) {
            return back()->withErrors(['code' => 'No se pudo desactivar el 2FA.']);
        }

        return redirect()->route('security.index')
            ->with('success', '2FA desactivado correctamente.');
    }
}