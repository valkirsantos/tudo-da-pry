<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        // TODO: implement in Prompt 3
        return response()->json(['data' => []]);
    }

    public function show(int $id): JsonResponse
    {
        // TODO: implement in Prompt 3
        return response()->json(['data' => null]);
    }

    public function store(Request $request): JsonResponse
    {
        // TODO: implement in Prompt 3
        return response()->json(['data' => null], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        // TODO: implement in Prompt 3
        return response()->json(['data' => null]);
    }

    public function destroy(int $id): JsonResponse
    {
        // TODO: implement in Prompt 3
        return response()->json(null, 204);
    }
}
