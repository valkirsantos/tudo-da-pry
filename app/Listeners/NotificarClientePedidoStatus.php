<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\PedidoStatusAtualizado;
use App\Models\Notification;

class NotificarClientePedidoStatus
{
    private const LABELS = [
        'aguardando_pagamento' => 'Pedido aguardando pagamento',
        'confirmado'           => 'Pedido confirmado',
        'separando'            => 'Seu pedido está sendo separado',
        'em_entrega'           => 'Seu pedido saiu para entrega',
        'entregue'             => 'Pedido entregue',
        'cancelado'            => 'Pedido cancelado',
    ];

    public function handle(PedidoStatusAtualizado $event): void
    {
        $status = $event->order->status_pedido;
        $titulo = self::LABELS[$status] ?? 'Status do pedido atualizado';

        Notification::create([
            'user_id'      => $event->order->user_id,
            'tipo'         => 'status_pedido',
            'titulo'       => $titulo,
            'mensagem'     => "Seu pedido #{$event->order->id} foi atualizado para: {$status}.",
            'ref_type'     => 'order',
            'ref_id'       => $event->order->id,
            'lida'         => false,
            'enviada_push' => false,
        ]);
    }
}
