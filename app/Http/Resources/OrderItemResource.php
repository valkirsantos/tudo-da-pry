<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'product_id'    => $this->product_id,
            'product'       => $this->when(
                $this->relationLoaded('product'),
                fn () => [
                    'id'        => $this->product->id,
                    'nome'      => $this->product->nome,
                    'categoria' => $this->product->categoria,
                ]
            ),
            'quantidade'    => $this->quantidade,
            'preco_unitario'=> $this->preco_unitario,
            'subtotal'      => $this->subtotal,
        ];
    }
}
