<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class SendOtpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'celular' => ['required', 'string', 'regex:/^[1-9]{2}9?[0-9]{8}$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'celular.required' => 'O número de celular é obrigatório.',
            'celular.regex' => 'Informe um celular brasileiro válido (somente números, DDD + número).',
        ];
    }
}
