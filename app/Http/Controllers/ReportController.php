<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Installment;
use App\Models\NotificationBroadcast;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function sales(Request $request): JsonResponse
    {
        $periodo = $request->input('periodo', 'hoje');

        [$inicio, $fim] = $this->parsePeriodo($periodo);

        $pedidos = Order::whereBetween('created_at', [$inicio, $fim])
            ->whereNotIn('status_pedido', ['cancelado'])
            ->with('items.product');

        $total        = (clone $pedidos)->sum('total');
        $count        = (clone $pedidos)->count();
        $ticketMedio  = $count > 0 ? $total / $count : 0;

        $pedidosAbertos = Order::whereNotIn('status_pedido', ['entregue', 'cancelado'])->count();

        $parcelasVencendo = Installment::whereDate('data_vencimento', '<=', now()->addDays(2))
            ->where('status', 'pendente')
            ->count();

        $notificacoes = NotificationBroadcast::whereBetween('enviado_em', [$inicio, $fim])->sum('total_enviados');

        // vendas por categoria
        $porCategoria = OrderItem::join('products', 'order_items.product_id', '=', 'products.id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->whereBetween('orders.created_at', [$inicio, $fim])
            ->whereNotIn('orders.status_pedido', ['cancelado'])
            ->select('products.categoria', DB::raw('SUM(order_items.subtotal) as total'))
            ->groupBy('products.categoria')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($r) => ['categoria' => $r->categoria, 'total' => (float) $r->total]);

        return response()->json([
            'data' => [
                'total'              => (float) $total,
                'ticket_medio'       => round((float) $ticketMedio, 2),
                'pedidos'            => $count,
                'pedidos_abertos'    => $pedidosAbertos,
                'parcelas_vencendo'  => $parcelasVencendo,
                'notificacoes'       => (int) $notificacoes,
                'inadimplencia'      => (float) Installment::where('status', 'atrasado')->sum('valor'),
                'por_categoria'      => $porCategoria,
            ],
        ]);
    }

    public function installments(Request $request): JsonResponse
    {
        $periodo = $request->input('periodo');
        $status  = $request->input('status');

        $query = Installment::with(['order.user']);

        if ($periodo) {
            [$inicio, $fim] = $this->parsePeriodo($periodo);
            $query->whereBetween('data_vencimento', [$inicio, $fim]);
        }

        if ($status) {
            $query->where('status', $status);
        }

        $installments = $query->orderBy('data_vencimento')->paginate(50);

        return response()->json([
            'data' => $installments->items(),
            'meta' => [
                'total'        => $installments->total(),
                'current_page' => $installments->currentPage(),
                'last_page'    => $installments->lastPage(),
            ],
        ]);
    }

    /** @return array{Carbon, Carbon} */
    private function parsePeriodo(string $periodo): array
    {
        if ($periodo === 'hoje') {
            return [now()->startOfDay(), now()->endOfDay()];
        }

        // formato YYYY-MM
        if (preg_match('/^\d{4}-\d{2}$/', $periodo)) {
            $date = Carbon::createFromFormat('Y-m', $periodo);
            return [$date->startOfMonth()->copy(), $date->copy()->endOfMonth()];
        }

        // fallback: mês atual
        return [now()->startOfMonth(), now()->endOfMonth()];
    }
}
