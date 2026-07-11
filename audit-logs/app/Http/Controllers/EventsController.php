<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EventsController extends Controller
{

    public function events(Request $request):JsonResponse{


        $service = $request->query('service') ?? null;
        $actor_id = $request->query('actor_id')?? null;
        $action = $request->query('action') ?? null;
        $from = $request->query('from') ?? null;
        $to = $request->query('to') ?? null;
        $perPage = $request->query('per_page',15);

        $query = AuditLog::query();

        if($actor_id !== null){
            $query->where('actor_id',$actor_id);
        }

        if($service !== null){
            $query->where('service',$service);
        }

        if($action !== null){
            $query->where('action',$action);
        }

        if($from !== null){
            $query->whereDate('created_at','>=',$from);
        }


        if($to !== null){
            $query->whereDate('created_at','<=',$to);
        }

        $result = $query->paginate($perPage);

        $data = [
            'data'=>$result->items(),
            'meta'=>[
                'current_page'=>$result->currentPage(),
                'last_page'=>$result->lastPage(),
                'per_page'=>$result->perPage(),
                'total'=>$result->total(),
            ]
        ];

        return response()->json($data,200);

    }

}
