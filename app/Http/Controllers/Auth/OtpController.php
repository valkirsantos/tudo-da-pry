<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Exceptions\TooManyOtpAttemptsException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\SendOtpRequest;
use App\Http\Requests\Auth\VerifyOtpRequest;
use App\Jobs\SendOtpSms;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Http\JsonResponse;

class OtpController extends Controller
{
    public function __construct(private readonly OtpService $otpService) {}

    public function send(SendOtpRequest $request): JsonResponse
    {
        $celular = $request->validated('celular');

        $codigo = $this->otpService->generate($celular);

        SendOtpSms::dispatch($celular, $codigo);

        return response()->json([
            'message' => 'Código enviado',
            'expires_in' => 300,
        ]);
    }

    public function verify(VerifyOtpRequest $request): JsonResponse
    {
        ['celular' => $celular, 'codigo' => $codigo] = $request->validated();

        try {
            $valido = $this->otpService->verify($celular, $codigo);
        } catch (TooManyOtpAttemptsException $e) {
            return response()->json([
                'error' => true,
                'message' => $e->getMessage(),
            ], 429);
        }

        if (! $valido) {
            return response()->json([
                'error' => true,
                'message' => 'Código inválido ou expirado.',
            ], 422);
        }

        $user = User::firstOrCreate(
            ['celular' => $celular],
            ['nome' => 'Cliente', 'role' => 'cliente', 'ativo' => true],
        );

        $token = $user->createToken('pwa-token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'nome' => $user->nome,
                'celular' => $user->celular,
                'role' => $user->role,
            ],
        ]);
    }
}
