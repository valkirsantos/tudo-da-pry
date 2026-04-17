<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BroadcastController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        // TODO: implement in Prompt 5
        return response()->json(['data' => null], 201);
    }

    public function index(Request $request): JsonResponse
    {
        // TODO: implement in Prompt 5
        return response()->json(['data' => []]);
    }
}
