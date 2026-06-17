<?php

namespace App\Http\Controllers;

use App\Http\Requests\Login2faRequest;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use PragmaRX\Google2FALaravel\Google2FA;
use BaconQrCode\Writer;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use Illuminate\Support\Str;
use App\Http\Requests\TwoFactorVerifyRequest;


class TwoFactorController extends Controller
{

    public function __construct(private Google2FA $google2fa){}
    
    public function enable(Request $request):JsonResponse{

        $user = $request->user();

        if($user->two_factor_enabled == true){
            return response()->json(['message'=>'This user already has actived 2fa',409]);
        }

        $secret = $this->google2fa->generateSecretKey();
        $user->two_factor_secret = $secret;
        $user->save();


        $inlineUrl = $this->google2fa->getQRCodeUrl(config('app.name'),$user->email,$secret);

        $renderer = new ImageRenderer(
            new RendererStyle(200),
            new SvgImageBackEnd()
        );

        $writer = new Writer($renderer);

        $svg = $writer->writeString($inlineUrl);
        
        $encode = base64_encode($svg);


        AuditLog::create([
            'user_id'=>$user->id,
            'action'=>'2fa_enabled',
            'ip_address'=>$request->ip()
        ]);

        return response()->json(['qr'=>$encode]);

    }

    public function disable(TwoFactorVerifyRequest $request): JsonResponse{

        $user = $request->user();

        if(!$user->two_factor_enabled){
            return response()->json(['message'=>'User not activate two factor authentication'],400);
        }

        if(!$this->google2fa->verifyKey($user->two_factor_secret, $request->code)){
            return response()->json([],422);
        }

        $user->two_factor_secret=null;
        $user->two_factor_recovery_codes=null;
        $user->two_factor_enabled=false;
        $user->save();


        AuditLog::create([
            'user_id'=>$user->id,
            'action'=>'2fa_disable',
            'ip_address'=>$request->ip()
        ]);

        return response()->json(['message'=>'Two factor authentication is disable'],200);

    }

    public function verify(TwoFactorVerifyRequest $request):JsonResponse{

        $user = $request->user();

        if($user->two_factor_secret == null){
            return response()->json(['message'=>'This user havent activate two factor'],400);
        }

        if($user->two_factor_enabled == true){
            return response()->json(['message' => 'Already has activated two factor'],409);
        }


        if(!$this->google2fa->verifyKey($user->two_factor_secret,$request->code)){
             return response()->json([],422);
        }

        $codes = [];
        for($i = 1; $i <= 8; $i++){
            $codes[] = Str::random(10);
        }
        
        $user->two_factor_recovery_codes=$codes;
        $user->two_factor_enabled=true;
        $user->save();


        AuditLog::create([
            'user_id'=>$user->id,
            'action'=>'2fa_verified',
            'ip_address'=>$request->ip()
        ]);
        
        return response()->json(['message'=>'Activated success', 'two_factor_recovery_codes'=>$codes],200);

    }

}
