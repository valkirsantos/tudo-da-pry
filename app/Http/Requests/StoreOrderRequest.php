<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'items'                 => ['required', 'array', 'min:1'],
            'items.*.product_id'    => ['required', 'integer', 'exists:products,id'],
            'items.*.quantidade'    => ['required', 'integer', 'min:1'],
            'tipo_pagamento'        => ['required', 'in:pix,dinheiro,parcelado'],
            'num_parcelas'          => ['required_if:tipo_pagamento,parcelado', 'nullable', 'integer', 'min:2', 'max:5'],
            'dia_vencimento'        => ['required_if:tipo_pagamento,parcelado', 'nullable', 'integer', 'min:1', 'max:31'],
            'endereco_entrega'      => ['nullable', 'array'],
            'observacoes'           => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'items.required'                => 'Adicione ao menos um produto.',
            'items.min'                     => 'Adicione ao menos um produto.',
            'items.*.product_id.exists'     => 'Produto não encontrado.',
            'items.*.quantidade.min'        => 'A quantidade mínima é 1.',
            'tipo_pagamento.required'       => 'Informe a forma de pagamento.',
            'tipo_pagamento.in'             => 'Forma de pagamento inválida.',
            'num_parcelas.required_if'      => 'Informe o número de parcelas.',
            'num_parcelas.min'              => 'Mínimo 2 parcelas.',
            'num_parcelas.max'              => 'Máximo 5 parcelas.',
            'dia_vencimento.required_if'    => 'Informe o dia de vencimento.',
            'dia_vencimento.min'            => 'O dia deve ser entre 1 e 31.',
            'dia_vencimento.max'            => 'O dia deve ser entre 1 e 31.',
        ];
    }
}
