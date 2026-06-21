<?php

namespace App\Http\Controllers;

use App\Http\Requests\ServiceTokenRequest;
use Illuminate\Http\Request;
use App\Models\ServiceClient;
use Illuminate\Http\JsonResponse;
use PHPOpenSourceSaver\JWTAuth\JWTAuth;
use Illuminate\Support\Facades\Hash;

class ServiceTokenController extends Controller
{

    public function __construct(private JWTAuth $jwt){}
    
    public function token(ServiceTokenRequest $request):JsonResponse{

        $client_id = $request->validated('client_id');

        $serviceClient = ServiceClient::where('client_id',$client_id)->first();

        if($serviceClient === null || !Hash::check($request->validated('client_secret'),$serviceClient->client_secret)){
            return response()->json(['message'=>'Invalid client credentials'],401);
        }

        $token = $this->jwt->fromUser($serviceClient);

        return response()->json(['token'=>$token],200);

    }

}
