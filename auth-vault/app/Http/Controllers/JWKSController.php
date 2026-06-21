<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class JWKSController extends Controller
{
    
    public function publicKey(Request $request){
        
        $publicKey = file_get_contents(storage_path('certs/jwt-rsa-4096-public.pem'));

            return response($publicKey,200)->header('Content-Type','text/plain');

    }

    public function jwks():JsonResponse{

        $publicKeyPem = file_get_contents(storage_path('certs/jwt-rsa-4096-public.pem'));
        $publicKeyResource = openssl_pkey_get_public($publicKeyPem);
        
        $details = openssl_pkey_get_details($publicKeyResource);

        return response()->json([
            'keys'=>[
                [
                'kty'=>'RSA',
                'use'=>'sig',
                'alg'=>'RS256',
                'kid'=>'auth-vault-key-1',
                'n'=>$this->base64UrlEncode($details['rsa']['n']),
                'e'=>$this->base64UrlEncode($details['rsa']['e'])
                ]
            ]
        ]);
    }

    private function base64UrlEncode($data):string{
        return rtrim(str_replace(['+','-'],['-','_'],base64_encode($data)),'=');
    }

}
