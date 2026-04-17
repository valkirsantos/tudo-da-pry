<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        // TODO: implement in Prompt 5
        return response()->json(['data' => []]);
    }

    public function markRead(Request $request): JsonResponse
    {
        // TODO: implement in Prompt 5
        return response()->json(['message' => 'ok']);
    }

    public function subscribe(Request $request): JsonResponse
    {
        // TODO: implement in Prompt 5
        return response()->json(['message' => 'subscribed']);
    }
}
