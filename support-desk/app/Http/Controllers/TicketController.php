<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCommentRequest;
use App\Http\Requests\StoreTicketRequest;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

use App\Models\Ticket;
use App\Models\TicketComment;
use App\Services\AuditLogServiceClient;
use App\Services\AuthVaultServiceClient;
use Illuminate\Support\Facades\DB;

use function PHPUnit\Framework\isEmpty;

class TicketController extends Controller
{

    public function __construct(private AuditLogServiceClient $auditlog, private AuthVaultServiceClient $authvault) {}

    public function getOne(Request $request, string $id): JsonResponse
    {

        $payload = $request->attributes->get('jwt_payload');
        $isAgent = $this->isAgent($payload);
        $ticket = Ticket::find($id);

        if ($ticket === null) {
            return response()->json(['message' => 'Ticket not found'], 404);
        }

        if (!$isAgent) {
            if ($ticket->requester_id !== $payload->sub) {
                return response()->json(['message' => 'Cannot view ticker'], 403);
            }
        }

        $ticket->load([
            'category',
            'comments' => function ($query) use ($isAgent) {
                if (!$isAgent) {
                    $query->where('is_internal', false);
                }
            }
        ]);

        return response()->json($ticket, 200);
    }

    public function getAll(Request $request): JsonResponse
    {

        $payload = $request->attributes->get('jwt_payload');
        $isAgent = $this->isAgent($payload);
        if (!$isAgent) {
            return response()->json(['message' => 'Not allowed'], 403);
        }

        $perPage = 15;

        $status = $request->query('status');
        $priority = $request->query('priority');
        $category_id = $request->query('category_id');
        $assignee_id = $request->query('assignee_id');

        $query = Ticket::query();

        if ($status !== null) {
            $query->where('status', $status);
        }

        if ($priority !== null) {
            $query->where('priority', $priority);
        }

        if ($category_id !== null) {
            $query->where('assignee_id', $category_id);
        }

        if ($assignee_id !== null) {
            $query->where('assignee_id', $assignee_id); 
        }

        $tickets = $query->with('category')->paginate($perPage);

        return response()->json([
            'metadata' => [
                'perPage' => $tickets->perPage(),
                'currentPage' => $tickets->currentPage(),
                'totalPage' => $tickets->lastPage(),
                'total' => $tickets->total()
            ],
            'data' => $tickets->items()
        ], 200);
    }


    public function getMyTickets(Request $request): JsonResponse
    {

        $perPage = 15;

        $payload = $request->attributes->get('jwt_payload');
        $sub = $payload->sub;

        $status = $request->query('status');

        $query = Ticket::query();

        if ($status !== null) {
            $query->where('status', $status);
        }

        $query->where('requester_id', $sub);

        $query->select([
            'priority',
            'status',
            'title',
            'description',
            'resolved_at',
            'closed_at'
        ]);


        $tickets = $query->paginate($perPage);

        return response()->json([
            'metadata' => [
                'perPage' => $tickets->perPage(),
                'currentPage' => $tickets->currentPage(),
                'totalPage' => $tickets->lastPage(),
                'total' => $tickets->total(),
            ],
            'data' => $tickets->items(),
        ], 200);
    }


    public function create(StoreTicketRequest $request): JsonResponse
    {

        $payload = $request->attributes->get('jwt_payload');
        $sub = $payload->sub;
        $data = $request->validated();

        $ticketDataLog = null;

        try {
            DB::transaction(function () use ($request, $data, $sub, &$ticketDataLog) {

                $data['requester_id'] = $sub;

                $ticket = Ticket::create($data);

                $ticketDataLog = [
                    'actor_id' => $sub,
                    'service' => 'support-desk',
                    'action' => 'ticket.created',
                    'resource_type' => 'tickets',
                    'resource_id' => $ticket->id,
                    'ip_address' => $request->ip(),
                    'metadata' => [
                        'user_agent' => $request->userAgent()
                    ]
                ];

                AuditLog::create($ticketDataLog);
            });
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Cannot create ticket'], 503);
        }

        $token = $this->authvault->getTokenService();

        if ($token !== null && $ticketDataLog !== null) {
            $this->auditlog->sendLog($token, $ticketDataLog);
        }


        return response()->json(['message' => 'Ticket created'], 201);
    }

    private function isAgent(object $payload): bool
    {

        $roles = (array) $payload->roles ?? [];

        return array_intersect($roles, ['support_agent', 'super_admin', 'academic_admin', 'security_admin']) !== [];
    }

    public function comment(StoreCommentRequest $request, string $id):JsonResponse{

        $payload = $request->attributes->get('jwt_payload');
        $sub = $payload->sub;
        $isAgent = $this->isAgent($payload);

        $data = $request->validated();
        $is_internal = $data['is_internal'];
        $ticket = Ticket::where('id',$id)->first();

        if($ticket === null){
            return response()->json(['message'=>'Not found'],404);
        }

        if($ticket->status === 'closed'){
            return response()->json(['message'=>"Ticket closed can't comment"],422);
        }

        if(!$isAgent){
            $is_internal=false;
            if($ticket->requester_id !== $sub){
                return response()->json(['message'=>'Now Allowed'],403);
            }
        }

        $ticketCommentDataLog = [];
        try {
            DB::transaction(function() use ($request,$sub,$ticket,$is_internal,$data, &$ticketCommentDataLog){

                $comment = TicketComment::create([
                    'ticket_id'=>$ticket->id,
                    'author_id'=>$sub,
                    'body'=>$data['body'],
                    'is_internal'=>$is_internal
                ]);

                $ticketCommentDataLog = [
                    'actor_id'=>$sub,
                    'service'=>'support-desk',
                    'action'=>'ticket.comment.created',
                    'resource_type'=>'ticket_comment',
                    'resource_id'=>$comment->id,
                    'ip_address'=>$request->ip(),
                    'metadata'=>[
                        'user_agent'=>$request->userAgent(),
                        'is_internal'=>$is_internal,
                    ]
                ];

                AuditLog::create($ticketCommentDataLog);

            });
        } catch (\Throwable $th) {
            return response()->json(['message'=>'Cannot comment in ticket'],503);
        }

        $token = $this->authvault->getTokenService();
        if(!isEmpty($ticketCommentDataLog) || $token !== null){
            $this->auditlog->sendLog($token,$ticketCommentDataLog);
        }

        

        return response()->json(['message'=>'Comment added'],200);
    }
}
