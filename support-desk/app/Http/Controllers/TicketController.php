<?php

namespace App\Http\Controllers;

use App\Http\Requests\AssignTicketRequest;
use App\Http\Requests\StoreCommentRequest;
use App\Http\Requests\StoreTicketRequest;
use App\Http\Requests\UpdateTicketStatusRequest;
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

    private array $validTransaction = [
        'open' => ['in_progress', 'on_hold'],
        'in_progress' => ['on_hold', 'resolved', 'closed'],
        'on_hold' => ['in_progress', 'resolved'],
        'resolve' => ['open', 'closed'],
        'closed' => []
    ];


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

    public function assign(AssignTicketRequest $request, string $ticketId): JsonResponse
    {

        $payload = $request->attributes->get('jwt_payload');
        $sub = $payload->sub;

        $ticket = Ticket::where('id', $ticketId)->first();

        if ($ticket === null) {
            return response()->json(['message' => 'Not found'], 404);
        }


        $assignee_id = $request->validated('assignee_id');
        $ticketDataLog = null;
        $old_ticket_status = $ticket->status;

        try {
            DB::transaction(function () use ($request, $sub, $old_ticket_status, $ticket, $assignee_id, &$ticketDataLog) {

                $ticket->update([
                    'assignee_id' => $assignee_id,
                    'status' => ($ticket->status !== 'open') ? $ticket->status : 'in_progress'
                ]);

                $ticketDataLog = [
                    'actor_id' => $sub,
                    'service' => 'support-desk',
                    'action' => 'ticket.assignee_agent.updated',
                    'resource_type' => 'tickets',
                    'resource_id' => $ticket->id,
                    'ip_address' => $request->ip(),
                    'metadata' => [
                        'user_agent' => $request->userAgent(),
                        'old_status' => $old_ticket_status,
                        'new_status' => $ticket->status,
                        'assignee_id' => $assignee_id
                    ]
                ];

                AuditLog::create($ticketDataLog);
            });
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Cannot assigned agent'], 503);
        }

        $token = $this->authvault->getTokenService();
        if ($ticketDataLog !== null && $token !== null) {
            $this->auditlog->sendLog($token, $ticketDataLog);
        }

        return response()->json(['message' => 'Agent assigneed'], 200);
    }

    public function comment(StoreCommentRequest $request, string $id): JsonResponse
    {

        $payload = $request->attributes->get('jwt_payload');
        $sub = $payload->sub;
        $isAgent = $this->isAgent($payload);

        $data = $request->validated();
        $is_internal = $data['is_internal'];
        $ticket = Ticket::where('id', $id)->first();

        if ($ticket === null) {
            return response()->json(['message' => 'Not found'], 404);
        }

        if ($ticket->status === 'closed') {
            return response()->json(['message' => "Ticket closed can't comment"], 422);
        }

        if (!$isAgent) {
            $is_internal = false;
            if ($ticket->requester_id !== $sub) {
                return response()->json(['message' => 'Now Allowed'], 403);
            }
        }

        $ticketCommentDataLog = null;
        try {
            DB::transaction(function () use ($request, $sub, $ticket, $is_internal, $data, &$ticketCommentDataLog) {

                $comment = TicketComment::create([
                    'ticket_id' => $ticket->id,
                    'author_id' => $sub,
                    'body' => $data['body'],
                    'is_internal' => $is_internal
                ]);

                $ticketCommentDataLog = [
                    'actor_id' => $sub,
                    'service' => 'support-desk',
                    'action' => 'ticket.comment.created',
                    'resource_type' => 'ticket_comment',
                    'resource_id' => $comment->id,
                    'ip_address' => $request->ip(),
                    'metadata' => [
                        'user_agent' => $request->userAgent(),
                        'is_internal' => $is_internal,
                    ]
                ];

                AuditLog::create($ticketCommentDataLog);
            });
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Cannot comment in ticket'], 503);
        }

        $token = $this->authvault->getTokenService();
        if ($ticketCommentDataLog !== null && $token !== null) {
            $this->auditlog->sendLog($token, $ticketCommentDataLog);
        }



        return response()->json(['message' => 'Comment added'], 200);
    }

    public function status(UpdateTicketStatusRequest $request, string $ticketId): JsonResponse
    {

        $payload = $request->attributes->get('jwt_payload');
        $sub = $payload->sub;

        $new_status = $request->validated('status');

        $ticket = Ticket::where('id', $ticketId)->first();

        if ($ticket === null) {
            return response()->json(['message' => 'Not found'], 404);
        }

        if (!in_array($new_status, $this->validTransaction[$ticket->status])) {
            return response()->json(['message' => 'Invalid status transition'], 422);
        }

        $old_ticket_status = $ticket->status;
        $ticketDataLog = null;

        try {
            DB::transaction(function () use ($request, $sub, $new_status, $ticket, $old_ticket_status, &$ticketDataLog) {

                switch ($new_status) {
                    case 'on_hold':
                        $ticket->update([
                            'status'=>$new_status,
                        ]);
                    case 'resolved':
                        $ticket->update([
                            'status'=>$new_status,
                            'resolved_at'=>now()
                        ]);
                        break;
                    case 'closed':
                        $ticket->update([
                            'status'=>$new_status,
                            'closed_at'=>now()
                        ]);
                }


                $ticketDataLog = [
                    'actor_id' => $sub,
                    'service' => 'support-desk',
                    'action' => 'ticket.status.update',
                    'resource_type' => 'tickets',
                    'resource_id' => $ticket->id,
                    'ip_address' => $request->ip(),
                    'metadata' => [
                        'user_agent' => $request->userAgent(),
                        'old_ticket_status' => $old_ticket_status,
                        'new_ticket_status' => $ticket->status,
                    ]
                ];


                AuditLog::create($ticketDataLog);
            });
        } catch (\Throwable $th) {
            return response()->json(['message' => 'Cannot update status of ticket'], 503);
        }

        $token = $this->authvault->getTokenService();

        if ($ticketDataLog !== null && $token !== null) {
            $this->auditlog->sendLog($token, $ticketDataLog);
        }

        return response()->json(['message' => 'Status updated'], 200);
    }


    public function reopen(Request $request, string $id):JsonResponse{
        
        $payload = $request->attributes->get('jwt_payload');
        
        $ticket = Ticket::find($id);

        if($ticket === null){
            return response()->json(['message'=>'Not found'],404);
        }

        if($this->isAgent($payload)){
            return response()->json(['message'=>'Cannot reopen tickets'],422);
        }

        $sub = $payload->sub;
        if($ticket->requester_id !== $sub){
            return response()->json(['message'=>'Cannot reopen ticket'],403);
        }

        if($ticket->status !== 'resolved'){
             return response()->json(['mesage'=>'Ticket cannot reopen is closed or in progress'],422);
        }
        
        $ticketDataLog = null;
        try {
            DB::transaction(function() use ($request,$ticket,$sub,&$ticketDataLog){

                $ticket->update([
                    'status'=>'open',
                    'resolved_at'=>null,

                ]);

                $ticketDataLog = [
                    'actor_id' => $sub,
                    'service' => 'support-desk',
                    'action' => 'ticket.reopen',
                    'resource_type' => 'tickets',
                    'resource_id' => $ticket->id,
                    'ip_address' => $request->ip(),
                    'metadata' => [
                        'user_agent' => $request->userAgent(),
                    ]
                ];

                AuditLog::create($ticketDataLog);

            });
        } catch (\Throwable $th) {
            return response()->json(['message'=>'Cannot reopen ticker,try later'],503);
        }

        $token = $this->authvault->getTokenService();
        if($ticketDataLog !== null && $token !== null){
            $this->auditlog->sendLog($token,$ticketDataLog);
        }

        return response()->json(['message'=>'Ticket reopen'],200);

    }

    private function isAgent(object $payload): bool
    {

        $roles = (array) $payload->roles ?? [];

        return array_intersect($roles, ['support_agent', 'super_admin', 'academic_admin', 'security_admin']) !== [];
    }
}
