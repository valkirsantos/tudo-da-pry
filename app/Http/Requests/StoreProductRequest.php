<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->role === 'vendedor';
    }

    /**
     * @return array<string, array<int|string, mixed>>
     */
    public function rules(): array
    {
        return [
            'nome' => ['required', 'string', 'max:255'],
            'descricao' => ['required', 'string', 'max:1000'],
            'preco' => ['required', 'numeric', 'min:0.01', 'max:9999999.99'],
            'estoque' => ['required', 'integer', 'min:0', 'max:999999'],
            'categoria' => ['required', 'string', 'in:bolsas,sapatos,perfumes,relogios,outros'],
            'fotos' => ['required', 'array', 'min:1', 'max:10'],
            'fotos.*' => ['string', 'max:500'], // S3 paths
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nome.required' => 'O nome do produto é obrigatório.',
            'nome.max' => 'O nome não pode exceder 255 caracteres.',
            'descricao.required' => 'A descrição é obrigatória.',
            'descricao.max' => 'A descrição não pode exceder 1000 caracteres.',
            'preco.required' => 'O preço é obrigatório.',
            'preco.numeric' => 'O preço deve ser um número válido.',
            'preco.min' => 'O preço deve ser maior que 0.',
            'estoque.required' => 'A quantidade em estoque é obrigatória.',
            'estoque.integer' => 'O estoque deve ser um número inteiro.',
            'estoque.min' => 'O estoque não pode ser negativo.',
            'categoria.required' => 'A categoria é obrigatória.',
            'categoria.in' => 'A categoria selecionada é inválida.',
            'fotos.required' => 'Pelo menos uma foto é obrigatória.',
            'fotos.min' => 'Pelo menos uma foto é obrigatória.',
            'fotos.max' => 'Máximo 10 fotos permitidas.',
        ];
    }
}
