<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Events\PedidoStatusAtualizado;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\StoreSellerOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Notification;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\InstallmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function __construct(private readonly InstallmentService $installmentService) {}

    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = Order::with(['user', 'items.product', 'installments'])->latest();

        if ($user->isCliente()) {
            $query->where('user_id', $user->id);
        }

        if ($request->filled('status')) {
            $query->where('status_pedido', $request->input('status'));
        }

        $orders = $query->paginate(15);

        return response()->json([
            'data' => OrderResource::collection($orders),
            'meta' => [
                'current_page' => $orders->currentPage(),
                'last_page'    => $orders->lastPage(),
                'per_page'     => $orders->perPage(),
                'total'        => $orders->total(),
            ],
        ]);
    }

    public function store(StoreOrderRequest $request): JsonResponse
    {
        $data = $request->validated();
        $user = $request->user();

        try {
            $order = DB::transaction(fn () => $this->buildOrder($data, $user->id, null));
        } catch (\DomainException $e) {
            return response()->json(['error' => true, 'message' => $e->getMessage()], 422);
        }

        $order->load(['user', 'items.product', 'installments']);

        return response()->json(['data' => new OrderResource($order)], 201);
    }

    public function show(int $id): JsonResponse
    {
        $user = request()->user();
        $order = Order::with(['user', 'items.product', 'installments', 'paymentProofs'])->findOrFail($id);

        if ($user->isCliente() && $order->user_id !== $user->id) {
            return response()->json(['error' => true, 'message' => 'Pedido não encontrado.'], 404);
        }

        return response()->json(['data' => new OrderResource($order)]);
    }

    public function storeBySeller(StoreSellerOrderRequest $request): JsonResponse
    {
        $data     = $request->validated();
        $seller   = $request->user();

        try {
            $order = DB::transaction(function () use ($data, $seller) {
                $client = User::firstOrCreate(
                    ['celular' => $data['celular']],
                    ['nome' => $data['nome'] ?? 'Cliente', 'role' => 'cliente', 'ativo' => true]
                );

                $order = $this->buildOrder($data, $client->id, $seller->id);

                Notification::create([
                    'user_id'      => $client->id,
                    'tipo'         => 'novo_pedido',
                    'titulo'       => 'Novo pedido criado',
                    'mensagem'     => "Um novo pedido #{$order->id} foi criado para você pela vendedora.",
                    'ref_type'     => 'order',
                    'ref_id'       => $order->id,
                    'lida'         => false,
                    'enviada_push' => false,
                ]);

                return $order;
            });
        } catch (\DomainException $e) {
            return response()->json(['error' => true, 'message' => $e->getMessage()], 422);
        }

        $order->load(['user', 'items.product', 'installments']);

        return response()->json(['data' => new OrderResource($order)], 201);
    }

    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'status_pedido' => ['required', 'in:aguardando_pagamento,confirmado,separando,em_entrega,entregue,cancelado'],
        ]);

        $order          = Order::findOrFail($id);
        $statusAnterior = $order->status_pedido;

        $order->update(['status_pedido' => $request->status_pedido]);

        PedidoStatusAtualizado::dispatch($order, $statusAnterior);

        $order->load(['user', 'items.product', 'installments']);

        return response()->json(['data' => new OrderResource($order)]);
    }

    private function buildOrder(array $data, int $userId, ?int $sellerId): Order
    {
        $total     = 0.0;
        $itemsData = [];

        foreach ($data['items'] as $item) {
            $product = Product::lockForUpdate()->findOrFail($item['product_id']);

            if ($product->estoque < $item['quantidade']) {
                throw new \DomainException("Estoque insuficiente para \"{$product->nome}\".");
            }

            $subtotal    = (float) $product->preco * $item['quantidade'];
            $total      += $subtotal;
            $itemsData[] = [
                'product_id'     => $product->id,
                'quantidade'     => $item['quantidade'],
                'preco_unitario' => $product->preco,
                'subtotal'       => $subtotal,
            ];

            $product->decrement('estoque', $item['quantidade']);
        }

        $order = Order::create([
            'user_id'          => $userId,
            'created_by'       => $sellerId,
            'total'            => $total,
            'tipo_pagamento'   => $data['tipo_pagamento'],
            'num_parcelas'     => $data['num_parcelas'] ?? null,
            'dia_vencimento'   => $data['dia_vencimento'] ?? null,
            'status_pedido'    => 'aguardando_pagamento',
            'status_pagamento' => 'pendente',
            'endereco_entrega' => $data['endereco_entrega'] ?? null,
            'observacoes'      => $data['observacoes'] ?? null,
        ]);

        $order->items()->createMany($itemsData);

        if ($data['tipo_pagamento'] === 'parcelado') {
            $this->installmentService->generate($order);
        }

        return $order;
    }
}
