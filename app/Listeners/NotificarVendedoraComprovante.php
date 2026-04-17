<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\ComprovantEnviado;
use App\Models\Notification;
use App\Models\User;

class NotificarVendedoraComprovante
{
    public function handle(ComprovantEnviado $event): void
    {
        $proof   = $event->proof;
        $order   = $proof->order;
        $vendedorId = $order->created_by;

        // Use the seller who created the order, or fall back to any active vendor
        if ($vendedorId === null) {
            $vendedor = User::where('role', 'vendedor')->where('ativo', true)->first();
            $vendedorId = $vendedor?->id;
        }

        if ($vendedorId === null) {
            return;
        }

        $tipo = $proof->installment_id ? 'parcela' : 'à vista';

        Notification::create([
            'user_id'      => $vendedorId,
            'tipo'         => 'comprovante_enviado',
            'titulo'       => 'Novo comprovante recebido',
            'mensagem'     => "Um comprovante de pagamento ({$tipo}) foi enviado para o pedido #{$order->id}.",
            'ref_type'     => 'payment_proof',
            'ref_id'       => $proof->id,
            'lida'         => false,
            'enviada_push' => false,
        ]);
    }
}
