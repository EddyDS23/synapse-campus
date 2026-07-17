<?php

namespace App\Http\Controllers;

use App\Services\AuthVaultServiceClient;
use App\Services\StudentPortalServiceClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

use App\Models\Book;
use App\Models\Loan;
use App\Models\AuditLog;
use App\Services\AuditLogServiceClient;

class LoanController extends Controller
{

    public function __construct(
            private AuthVaultServiceClient $authvault,
            private StudentPortalServiceClient $studentportal,
            private AuditLogServiceClient $auditlog){}

    public function index(Request $request, string $id):JsonResponse{
        
        $payload = $request->attributes->get('jwt_payload');
        $student_id = $payload->sub;

        $token = $this->authvault->getTokenService();

        $studentStatus = $this->studentportal->statusStudent($token,$student_id);
        if($studentStatus['status'] !== 'active'){
            return response()->json(['message'=>'This user cant loan books because isnt active'],422);
        }

        if($studentStatus['has_debt'] === true){
            return response()->json(['message'=>'This user cant loan books because has debt'],422);
        }


        $book = Book::where('id',$id)->first();

        if($book === null){
            return response()->json(['Book not found'],404);
        }

        if($book->stock_available <= 0){
            return response()->json(['Stock not available'],422);
        } 

        $book->decrement('stock_available',1);

        $loan =Loan::create([
            'book_id'=>$book->id,
            'borrower_id'=>$student_id,
            'borrowed_at'=>now(),
            'due_at'=>now()->addDays((int)config('app.due_at'))
        ]);

        $audit_data = [
            'actor_id'=>$student_id,
            'service'=>'library-core',
            'action'=>'loan.create',
            'resource_type'=>'loans',
            'resource_id'=>$loan->id,
            'ip_address'=>$request->ip(),
            'metadata'=>[
                'user_agent'=>$request->userAgent(),                
            ]
        ];

        AuditLog::create($audit_data);

        $this->auditlog->sendLog($token,$audit_data);

        return response()->json(['message'=>'Loan succesful'],200);

    }    

}
