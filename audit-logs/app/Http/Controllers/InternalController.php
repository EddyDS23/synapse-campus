<?php

namespace App\Http\Controllers;

use App\Http\Requests\EventsRequest;
use App\Models\AuditLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InternalController extends Controller
{
    
    public function register_events(EventsRequest $request):JsonResponse{

        
        $data = $request->validated();

        AuditLog::create($data);

        return response()->json([],201);
    }

}
