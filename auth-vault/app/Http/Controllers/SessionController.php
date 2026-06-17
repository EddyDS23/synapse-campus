<?php

namespace App\Http\Controllers;

use App\Models\ApiSession;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

class SessionController extends Controller
{
    public function get(Request $request): JsonResponse
    {

        $user = $request->user();

        $sessions_current = $user->apiSessions()->orderBy('created_at', 'desc')->get();

        return response()->json(['user' => $user->id,'sessions' => $sessions_current], 200);

    }

    public function delete(Request $request, int $id): JsonResponse
    {

        $user = $request->user();


        $session = $user->apiSessions()->where('id', $id)->first();

        if ($session == null) {
            return response()->json([], 404);
        }

        PersonalAccessToken::find($session->token_id)->delete();
        $session->delete();

        return response()->json([], 200);
    }



}
