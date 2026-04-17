<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class VerifyOtpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'celular' => ['required', 'string', 'regex:/^[1-9]{2}9?[0-9]{8}$/'],
            'codigo' => ['required', 'string', 'digits:6'],
        ];
    }

    public function messages(): array
    {
        return [
            'celular.required' => 'O número de celular é obrigatório.',
            'celular.regex' => 'Informe um celular brasileiro válido.',
            'codigo.required' => 'O código de verificação é obrigatório.',
            'codigo.digits' => 'O código deve ter exatamente 6 dígitos.',
        ];
    }
}
