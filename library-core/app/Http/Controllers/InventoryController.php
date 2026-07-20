<?php

namespace App\Http\Controllers;

use App\Http\Requests\BookCreateRequest;
use App\Http\Requests\BookUpdateRequest;
use App\Http\Requests\BookUpdateStockRequest;
use App\Models\AuditLog;
use App\Models\Book;
use App\Models\Loan;
use App\Services\AuditLogServiceClient;
use App\Services\AuthVaultServiceClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{

    public function __construct(
        private AuthVaultServiceClient $authvault,
        private AuditLogServiceClient $auditlog
    ) {}

    public function createBook(BookCreateRequest $request): JsonResponse
    {

        $payload = $request->attributes->get('jwt_payload');
        $sub = $payload->sub;

        $data = $request->validated();
        $data['stock_available'] = $data['stock_total'];

        $dataBookLog = null;
        try {
            DB::transaction(function () use ($request, $data, $sub, &$dataBookLog) {
                $book = Book::create($data);


                $dataBookLog = [
                    'actor_id' => $sub,
                    'service' => 'library-core',
                    'action' => 'book.created',
                    'resource_type' => 'books',
                    'resource_id' => $book->id,
                    'ip_address' => $request->ip(),
                    'metadata' => [
                        'user_agent' => $request->userAgent(),
                        'user_id' => $sub
                    ]
                ];

                AuditLog::create($dataBookLog);
            });
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Cannot add book'], 503);
        }

        if ($dataBookLog !== null) {
            $token = $this->authvault->getTokenService();
            $this->auditlog->sendLog($token, $dataBookLog);
        }


        return response()->json(['message' => 'Book added succesful'], 201);
    }

    public function updateBook(BookUpdateRequest $request, string $id): JsonResponse
    {

        $payload = $request->attributes->get('jwt_payload');
        $sub = $payload->sub;

        $data = $request->validated();

        $book = Book::where('id', $id)->first();

        if ($book === null) {
            return response()->json(['message' => 'Book not found'], 404);
        }
        $before = $book->getOriginal();
        $dataBookLog = null;
        try {
            DB::transaction(function () use ($request, $sub, $data, $before, $book, &$dataBookLog) {

                $book->update($data);

                $dataBookLog = [
                    'actor_id' => $sub,
                    'service' => 'library-core',
                    'action' => 'book.updated',
                    'resource_type' => 'books',
                    'resource_id' => $book->id,
                    'ip_address' => $request->ip(),
                    'metadata' => [
                        'before' => $before,
                        'after' => $book->getChanges(),
                        'user_agent' => $request->userAgent()
                    ]
                ];

                AuditLog::create($dataBookLog);
            });
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Cannot update book'], 503);
        }


        if ($dataBookLog !== null) {
            $token = $this->authvault->getTokenService();
            $this->auditlog->sendLog($token, $dataBookLog);
        }

        return response()->json(['message' => 'Book updated succesful'], 200);
    }


    public function updateBookStock(BookUpdateStockRequest $request, string $id): JsonResponse
    {

        $payload = $request->attributes->get('jwt_payload');
        $sub = $payload->sub;

        $adjust_stock = $request->validated('stock_total');

        $book = Book::where('id', $id)->first();

        if ($book === null) {
            return response()->json(['message' => 'Book not found'], 404);
        }

        $stock_current = $book->stock_total;
        $before = $book->getOriginal();

        $books_loan = Loan::where('book_id', $book->id)->where('status', 'active')->count();

        $dataStockLog = null;

        try {
            DB::transaction(function () use ($request,$before,$adjust_stock ,$sub,$stock_current, $books_loan, $book,&$dataStockLog) {
                if ($adjust_stock > 0) {

                    $stock_total_new = $stock_current + $adjust_stock;
                    $stock_available_new = $stock_total_new - $books_loan;

                    $book->update([
                        'stock_total' => $stock_total_new,
                        'stock_available' => $stock_available_new
                    ]);

                    $dataStockLog = [
                        'actor_id'=>$sub,
                        'service'=>'library-core',
                        'action'=>'book.updated.stock.increment',
                        'resource_type'=>'books',
                        'resource_id'=>$book->id,
                        'ip_address'=>$request->ip(),
                        'metadata'=>[
                            'before'=>$before,
                            'after'=>$book->getChanges(),
                            'user_agent'=>$request->userAgent()
                        ]
                    ];

                } else {

                    $stock_total_new = $stock_current + $adjust_stock;
                    if ($stock_total_new < $books_loan) {
                       throw new \InvalidArgumentException('Book loans is minor that new stock');
                    }
                    $stock_available_new = $stock_total_new - $books_loan;

                    $book->update([
                        'stock_total' => $stock_total_new,
                        'stock_available' => $stock_available_new
                    ]);

                    $dataStockLog = [
                        'actor_id'=>$sub,
                        'service'=>'library-core',
                        'action'=>'book.updated.stock.decrement',
                        'resource_type'=>'books',
                        'resource_id'=>$book->id,
                        'ip_address'=>$request->ip(),
                        'metadata'=>[
                            'before'=>$before,
                            'after'=>$book->getChanges(),
                            'user_agent'=>$request->userAgent()
                        ]
                    ];
                }
            });
        } catch (\InvalidArgumentException $e){
            return response()->json(['message'=>$e->getMessage()],422);
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Cannot update stock'], 503);
        }

        if ($dataStockLog !== null) {
            $token = $this->authvault->getTokenService();
            $this->auditlog->sendLog($token, $dataStockLog);
        }


        return response()->json(['message' => 'Stock updated succesful'], 200);
    }
}
