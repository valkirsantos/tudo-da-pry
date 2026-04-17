<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InstallmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'numero_parcela' => $this->numero_parcela,
            'valor'          => $this->valor,
            'data_vencimento'=> $this->data_vencimento?->format('Y-m-d'),
            'status'         => $this->status,
            'validado_em'    => $this->validado_em,
        ];
    }
}
