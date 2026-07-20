<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Fine;
use App\Services\AuditLogServiceClient;
use App\Services\AuthVaultServiceClient;
use App\Services\StudentPortalServiceClient;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

use Illuminate\Support\Facades\DB;

class FineController extends Controller
{

    public function __construct(
        private AuthVaultServiceClient $authvault,
         private AuditLogServiceClient $auditlog,
          private StudentPortalServiceClient $studentportal
          ){}
    
    public function getFinesByStudent(Request $request):JsonResponse{

        $status = $request->query('status');
        $perPage = 15;

        $payload = $request->attributes->get('jwt_payload');
        $sub = $payload->sub;

        $query = Fine::query();
        $query->where('borrower_id',$sub);
        if($status !== null){
            $query->where('status',$status);
        }

        $fines_pending = $query->paginate($perPage);

        return response()->json([
            'fines'=>$fines_pending->items(),
            'meta'=>[
                'perPage'=>$perPage,
                'currentPage'=>$fines_pending->currentPage(),
                'totalPage'=>$fines_pending->lastPage(),
                'total'=>$fines_pending->total()
            ]
        ],200);

    }

    public function pay(Request $request, string $id):JsonResponse{

        $payload = $request->attributes->get('jwt_payload');
        $sub = $payload->sub;

        $fine = Fine::where('id',$id)->first();

        if($fine === null){
            return response()->json(['message','fine not found'],404);
        }

        if($fine->borrower_id !== $sub){
            return response()->json(['messsage'=>'student isnt owner of this fine'],403);
        }

        if($fine->status === 'paid'){
            return response()->json(['message'=>'already fine paid'],409);
        }

        $fineData = null;
        try {
            DB::transaction(function() use ($request,&$fine,&$fineData,$sub){

                $fine->update([
                    'paid_at'=>now(),
                    'status'=>'paid',

                ]);

                $fineData = [
                    'service'=>'library-core',
                    'action'=>'fine.paid',
                    'resource_type'=>'fines',
                    'resource_id'=>$fine->id,
                    'ip_address'=>$request->ip(),
                    'metadata'=>[
                        'user_agent'=>$request->userAgent(),
                        'amount'=>$fine->amount,
                        'loan_id'=>$fine->loan_id,
                        'paid_at'=>now(),
                    ]
                ];

                AuditLog::create($fineData);

            });
        } catch (\Throwable $th) {
            return response()->json(['message'=>'Fine cannot paid'],503);
        }

        $token = $this->authvault->getTokenService();

        $notified = $this->studentportal->updateDebt($token,$sub,false);
        $fine->update(['paid_notified'=>$notified]);

        $fineData['actor_id'] = $sub;

        $this->auditlog->sendLog($token,$fineData);
        
        return response()->json(['message'=>'Fine paid'],200);

    }   

}
