<?php

namespace App\Http\Controllers;

use App\Services\AuthVaultServiceClient;
use App\Services\StudentPortalServiceClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

use App\Models\Book;
use App\Models\Loan;
use App\Models\Fine;
use App\Models\AuditLog;
use App\Models\Renewal;
use App\Services\AuditLogServiceClient;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Psr\SimpleCache\CacheInterface;

class LoanController extends Controller
{

    public function __construct(
        private AuthVaultServiceClient $authvault,
        private StudentPortalServiceClient $studentportal,
        private AuditLogServiceClient $auditlog
    ) {}

    public function index(Request $request, string $id): JsonResponse
    {

        $payload = $request->attributes->get('jwt_payload');
        $student_id = $payload->sub;

        $token = $this->authvault->getTokenService();

        $studentStatus = $this->studentportal->statusStudent($token, $student_id);
        if ($studentStatus['status'] !== 'active') {
            return response()->json(['message' => 'This user cant loan books because isnt active'], 422);
        }

        if ($studentStatus['has_debt'] === true) {
            return response()->json(['message' => 'This user cant loan books because has debt'], 422);
        }


        $book = Book::where('id', $id)->first();

        if ($book === null) {
            return response()->json(['Book not found'], 404);
        }

        if ($book->stock_available <= 0) {
            return response()->json(['Stock not available'], 422);
        }

        $book->decrement('stock_available', 1);

        $loan = Loan::create([
            'book_id' => $book->id,
            'borrower_id' => $student_id,
            'borrowed_at' => now(),
            'due_at' => now()->addDays((int)config('app.due_at'))
        ]);

        $audit_data = [
            'actor_id' => $student_id,
            'service' => 'library-core',
            'action' => 'loan.create',
            'resource_type' => 'loans',
            'resource_id' => $loan->id,
            'ip_address' => $request->ip(),
            'metadata' => [
                'user_agent' => $request->userAgent(),
            ]
        ];

        AuditLog::create($audit_data);

        $this->auditlog->sendLog($token, $audit_data);

        return response()->json(['message' => 'Loan succesful'], 200);
    }

    public function getLoansUser(Request $request): JsonResponse
    {

        $payload = $request->attributes->get('jwt_payload');
        $sub = $payload->sub;

        $loans = Loan::where('borrower_id', $sub)->where('status', 'active')->get();

        return response()->json(['loans' => $loans], 200);
    }

    public function renew(Request $request, string $id): JsonResponse
    {

        $payload = $request->attributes->get('jwt_payload');
        $sub = $payload->sub;

        $loan = Loan::where('id', $id)->first();

        if ($loan === null) {
            return response()->json(['message' => 'Loan not found'], 404);
        }

        if ($loan->borrower_id !== $sub) {
            return response()->json(['message' => 'student isnt owner this loan'], 403);
        }


        $fineCount = Fine::where('borrower_id', $sub)->where('status', 'pending')->count();

        if ($fineCount > 0) {
            return response()->json(['message' => 'student has outstanding debt'], 409);
        }

        if ($loan->renew_count >= (int)config('app.book_renew_limit')) {
            return response()->json(['message' => 'the renewed limit has been reached'], 409);
        }

        $new_due_at = now()->addDays((int)config('app.due_at'));
        $old_due_at = $loan->due_at;

        try {
            DB::transaction(function () use ($loan, $old_due_at, $new_due_at, $sub) {

                $loan->update([
                    'renew_count' => $loan->renew_count + 1,
                    'due_at' => $new_due_at,
                ]);

                Renewal::create([
                    'loan_id' => $loan->id,
                    'borrower_id' => $sub,
                    'previous_due_at' => $old_due_at,
                    'new_due_at' => $new_due_at
                ]);
            });
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Failed to renew load, try later'], 500);
        }

        $data = [
            'actor_id' => $sub,
            'service' => 'library-core',
            'action' => 'loan.renewed',
            'resource_type' => 'loans',
            'resource_id' => $loan->id,
            'ip_address' => $request->ip(),
            'metadata' => [
                'previous_due_at' => $old_due_at,
                'new_due_at' => $new_due_at,
            ]
        ];

        AuditLog::create($data);

        $token = $this->authvault->getTokenService();
        $this->auditlog->sendLog($token, $data);

        return response()->json(['message' => 'Loan renewed'], 200);
    }

    public function returnBook(Request $request, string $id): JsonResponse
    {

        $payload = $request->attributes->get('jwt_payload');
        $sub = $payload->sub;

        $loan = Loan::where('id', $id)->first();

        if ($loan === null) {
            return response()->json(['message' => 'Loan not found'], 404);
        }

        if ($loan->borrower_id !== $sub) {
            return response()->json(['message' => 'student isnt owner this loan'], 403);
        }

        if ($loan->status === 'returned') {
            return response()->json(['message' => 'This loan has been finished'], 409);
        }

        $days_overdue = 0;
        if ($loan->due_at < now()) {
            $days_overdue = ceil(now()->diffInHours($loan->due_at) / 24) * -1;
        }
    
        $fineData=null;
        $loanData=[];
        $fineAmount =0;
        $fine = null;
        try {
            DB::transaction(function () use ($request, $loan, $sub, $days_overdue, &$fineData, &$loanData, &$fineAmount,&$fine) {

                $loan->update([
                    'status' => 'returned',
                    'returned_at' => now(),
                ]);

                $book = Book::where("id",$loan->book_id);

                $book->increment('stock_available',1);
                
                $loanData = [
                    'actor_id' => $sub,
                    'service' => 'library-core',
                    'action' => 'loan.returned',
                    'resource_type' => 'loans',
                    'resource_id' => $loan->id,
                    'ip_address' => $request->ip(),
                    'metadata' => ['user_agent' => $request->userAgent()],
                ];

                AuditLog::create($loanData);


                if ($days_overdue > 0) {
                  
                    $fineAmount = $days_overdue * (int)config('app.fine_per_day');
                    $fine = Fine::create([
                        'loan_id' => $loan->id,
                        'borrower_id' => $sub,
                        'amount' => $fineAmount
                    ]);

                    $fineData = [
                        'actor_id' => $sub,
                        'service' => 'library-core',
                        'action' => 'fine.create',
                        'resource_type' => 'fine',
                        'resource_id' => $fine->id,
                        'ip_address' => $request->ip(),
                        'metadata' => [
                            'user_agent' => $request->userAgent(),
                            'borrower_id'=>$fine->borrower_id,
                            'amount'=>$fine->amount,
                            'days_overdue'=>$days_overdue
                            ],
                    ];

                    AuditLog::create($fineData);
                    
                }
            });
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Failed to returned book'], 503);
        }


        $token = $this->authvault->getTokenService();
        if($fine !== null){
            $notified = $this->studentportal->updateDebt($token,$sub,true);
            $fine->update([
                'debt_notified'=>$notified
            ]);
        }
        

        $this->auditlog->sendLog($token,$loanData);
        if($fineData !== null){
            $this->auditlog->sendLog($token,$fineData);
            return response()->json([
                'message'=>'Loan returned',
                'fine_generated'=>true,
                'fine_amount'=>(float)$fineAmount
                ],200);
        }

        return response()->json(['message'=>'Loan returned'],200);

    }
}
