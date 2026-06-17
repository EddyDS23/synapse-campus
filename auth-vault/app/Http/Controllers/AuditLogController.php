<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function get_audit_user(Request $request): JsonResponse
    {

        $user = $request->user();

        $logs = $user->auditLogs()->orderBy('created_at', 'desc')->paginate(15);

        return response()->json(['logs' => $logs], 200);
    }

}
