<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'user'            => $this->when(
                $this->relationLoaded('user'),
                fn () => [
                    'id'      => $this->user->id,
                    'nome'    => $this->user->nome,
                    'celular' => $this->user->celular,
                ]
            ),
            'total'           => $this->total,
            'tipo_pagamento'  => $this->tipo_pagamento,
            'num_parcelas'    => $this->num_parcelas,
            'dia_vencimento'  => $this->dia_vencimento,
            'status_pedido'   => $this->status_pedido,
            'status_pagamento'=> $this->status_pagamento,
            'endereco_entrega'=> $this->endereco_entrega,
            'observacoes'     => $this->observacoes,
            'items'           => OrderItemResource::collection($this->whenLoaded('items')),
            'installments'    => InstallmentResource::collection($this->whenLoaded('installments')),
            'payment_proofs'  => $this->whenLoaded('paymentProofs', fn () =>
                $this->paymentProofs->map(fn ($proof) => [
                    'id'             => $proof->id,
                    'installment_id' => $proof->installment_id,
                    'status'         => $proof->status,
                    'nome_arquivo'   => $proof->nome_arquivo,
                    'created_at'     => $proof->created_at,
                ])
            ),
            'created_at'      => $this->created_at,
        ];
    }
}
