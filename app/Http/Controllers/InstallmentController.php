<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InstallmentController extends Controller
{
    public function index(Request $request, int $orderId): JsonResponse
    {
        // TODO: implement in Prompt 4
        return response()->json(['data' => []]);
    }
}
