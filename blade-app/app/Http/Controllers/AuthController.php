<?php

namespace App\Http\Controllers;

use App\Exceptions\InvalidCredentialsException;
use App\Exceptions\ServiceUnavailableException;
use App\Services\AuthVaultService;
use Illuminate\Http\Request;

class AuthController extends Controller
{

    public function __construct(private AuthVaultService $authvault){}
    
    public function showLogin(Request $request){

        if(session('access_token')){
            return redirect('/dashboard');
        }

        return view('auth.login');

    }

    public function login(Request $request){

        $validated = $request->validate([
            'email'=>['required','email'],
            'password'=>['required','string']
        ]);

        try {
            $result = $this->authvault->login($validated['email'],$validated['password']);
        } catch (ServiceUnavailableException $e) {
            return back()->withErrors(['email'=>'Servicio no disponible'])->withInput();
        } catch (InvalidCredentialsException $e){
            return back()->withErrors(['email'=>'Credenciales Invalidas'])->withInput();
        }

        session($result);

        return redirect('/dashboard');

    }

    public function logout(Request $request){

        $access_token = session('access_token');

        session()->flush();

        $this->authvault->logout($access_token);

        return redirect('/login');

    }

}
