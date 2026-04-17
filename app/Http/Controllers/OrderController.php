<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        // TODO: implement in Prompt 4
        return response()->json(['data' => []]);
    }

    public function store(Request $request): JsonResponse
    {
        // TODO: implement in Prompt 4
        return response()->json(['data' => null], 201);
    }

    public function show(int $id): JsonResponse
    {
        // TODO: implement in Prompt 4
        return response()->json(['data' => null]);
    }

    public function storeBySeller(Request $request): JsonResponse
    {
        // TODO: implement in Prompt 4
        return response()->json(['data' => null], 201);
    }

    public function updateStatus(Request $request, int $id): JsonResponse
    {
        // TODO: implement in Prompt 4
        return response()->json(['data' => null]);
    }
}
