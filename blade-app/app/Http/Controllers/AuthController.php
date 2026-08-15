<?php

namespace App\Http\Controllers;

use App\Exceptions\InvalidCredentialsException;
use App\Exceptions\ServiceUnavailableException;
use App\Services\AuthVaultService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{

    public function __construct(private AuthVaultService $authvault) {}

    public function showLogin(Request $request)
    {

        if (session('access_token')) {
            return redirect('/dashboard');
        }

        return view('auth.login');
    }

    public function login(Request $request): mixed
    {
        $validated = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        try {
            $data = $this->authvault->login($validated['email'], $validated['password']);
        } catch (ServiceUnavailableException $e) {
            return back()->withErrors(['email' => 'Servicio no disponible. Intenta más tarde.'])->withInput();
        } catch (InvalidCredentialsException $e) {
            return back()->withErrors(['email' => 'Email o contraseña incorrectos.'])->withInput();
        }

    
        if (!empty($data['two_factor_required'])) {
            session(['pending_2fa_email' => $data['email']]);
            return redirect()->route('login.2fa');
        }

        session($this->authvault->helperSession($data));

        return redirect()->route('dashboard');
    }

    public function logout(Request $request)
    {

        $access_token = session('access_token');

        session()->flush();

        $this->authvault->logout($access_token);

        return redirect('/login');
    }


    // ─── Login 2FA ────────────────────────────────────────────────────────────────

    public function show2fa(): mixed
    {
        // Solo accesible si hay un email pendiente de 2FA en sesión
        if (!session('pending_2fa_email')) {
            return redirect()->route('login');
        }

        return view('auth.2fa-login');
    }

    public function login2fa(Request $request): mixed
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'min:6', 'max:10'],
        ]);

        $email = session('pending_2fa_email');

        if (!$email) {
            return redirect()->route('login');
        }

        try {
            $result = $this->authvault->login2fa($email, $validated['code']);
        } catch (ServiceUnavailableException $e) {
            return back()->withErrors(['code' => 'Servicio no disponible.'])->withInput();
        } catch (InvalidCredentialsException $e) {
            return back()->withErrors(['code' => 'Código inválido o expirado.'])->withInput();
        }

        session()->forget('pending_2fa_email');

        session([
            'access_token'  => $result['access_token'],
            'refresh_token' => $result['refresh_token'],
            'token_expiry'  => $result['token_expiry'],
            'email'         => $result['email'],
            'roles'         => $result['roles'],
        ]);

        return redirect()->route('dashboard');
    }

    // ─── OAuth ────────────────────────────────────────────────────────────────────

    public function oauthRedirect(string $provider): mixed
{
    try {
        $url = $this->authvault->getOAuthRedirectUrl($provider);
    } catch (ServiceUnavailableException $e) {
        return redirect()->route('login')
            ->withErrors(['email' => 'No se pudo conectar con GitHub.']);
    }

    return redirect()->away($url);
}

    public function oauthCallback(Request $request): mixed
    {
        try {
            $result = $this->authvault->handleOAuthCallback(
                $request->route('provider'),
                $request->only(['code', 'state'])
            );
        } catch (ServiceUnavailableException $e) {
            return redirect()->route('login')
                ->withErrors(['email' => 'No se pudo completar el login con GitHub.']);
        } catch (InvalidCredentialsException $e) {
            return redirect()->route('login')
                ->withErrors(['email' => 'Error en la autenticación con GitHub.']);
        }

        session($result);
        return redirect()->route('dashboard');
    }
}
