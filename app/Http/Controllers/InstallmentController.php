<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Resources\InstallmentResource;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InstallmentController extends Controller
{
    public function index(Request $request, int $orderId): JsonResponse
    {
        $user  = $request->user();
        $order = Order::findOrFail($orderId);

        if ($user->isCliente() && $order->user_id !== $user->id) {
            return response()->json(['error' => true, 'message' => 'Pedido não encontrado.'], 404);
        }

        return response()->json([
            'data' => InstallmentResource::collection($order->installments),
        ]);
    }
}
