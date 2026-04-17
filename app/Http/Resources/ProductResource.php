<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nome' => $this->nome,
            'descricao' => $this->descricao,
            'preco' => $this->preco,
            'estoque' => $this->estoque,
            'categoria' => $this->categoria,
            'ativo' => $this->ativo,
            'fotos' => $this->when(
                $this->relationLoaded('photos'),
                fn () => $this->photos->map(fn ($photo) => [
                    'id' => $photo->id,
                    'url' => app('s3-presigned')->generateDownloadUrl($photo->path_s3),
                    'ordem' => $photo->ordem,
                ])
            ),
            'created_at' => $this->created_at,
        ];
    }
}
