<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
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
            'nome' => ['sometimes', 'string', 'max:255'],
            'descricao' => ['sometimes', 'string', 'max:1000'],
            'preco' => ['sometimes', 'numeric', 'min:0.01', 'max:9999999.99'],
            'estoque' => ['sometimes', 'integer', 'min:0', 'max:999999'],
            'categoria' => ['sometimes', 'string', 'in:bolsas,sapatos,perfumes,relogios,outros'],
            'fotos_add' => ['array', 'max:10'],
            'fotos_add.*' => ['string', 'max:500'], // S3 paths to add
            'fotos_remove' => ['array'],
            'fotos_remove.*' => ['integer'], // photo IDs to remove
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'nome.string' => 'O nome deve ser um texto.',
            'nome.max' => 'O nome não pode exceder 255 caracteres.',
            'descricao.string' => 'A descrição deve ser um texto.',
            'descricao.max' => 'A descrição não pode exceder 1000 caracteres.',
            'preco.numeric' => 'O preço deve ser um número válido.',
            'preco.min' => 'O preço deve ser maior que 0.',
            'estoque.integer' => 'O estoque deve ser um número inteiro.',
            'estoque.min' => 'O estoque não pode ser negativo.',
            'categoria.in' => 'A categoria selecionada é inválida.',
            'fotos_add.max' => 'Máximo 10 fotos podem ser adicionadas.',
            'fotos_remove' => 'IDs de fotos inválidos.',
        ];
    }
}
