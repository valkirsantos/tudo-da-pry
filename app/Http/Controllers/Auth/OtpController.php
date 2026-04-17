<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OtpController extends Controller
{
    public function send(Request $request): JsonResponse
    {
        // TODO: implement in Prompt 2
        return response()->json(['message' => 'Código enviado', 'expires_in' => 300]);
    }

    public function verify(Request $request): JsonResponse
    {
        // TODO: implement in Prompt 2
        return response()->json(['token' => null, 'user' => null]);
    }
}
