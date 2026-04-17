<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function sales(Request $request): JsonResponse
    {
        // TODO: implement in Prompt 5
        return response()->json(['data' => null]);
    }

    public function installments(Request $request): JsonResponse
    {
        // TODO: implement in Prompt 5
        return response()->json(['data' => null]);
    }
}
