<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeliveryController extends Controller
{
    public function update(Request $request, int $id): JsonResponse
    {
        // TODO: implement in Prompt 4
        return response()->json(['data' => null]);
    }
}
