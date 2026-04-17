<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentProofController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        // TODO: implement in Prompt 5
        return response()->json(['upload_url' => null, 'proof_id' => null], 201);
    }

    public function index(Request $request): JsonResponse
    {
        // TODO: implement in Prompt 5
        return response()->json(['data' => []]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        // TODO: implement in Prompt 5
        return response()->json(['data' => null]);
    }
}
