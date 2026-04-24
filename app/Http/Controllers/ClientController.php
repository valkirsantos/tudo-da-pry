<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = User::where('role', 'cliente')->where('ativo', true);

        if ($q = $request->string('q')->trim()->value()) {
            $query->where(function ($qb) use ($q) {
                $qb->where('nome', 'ilike', "%{$q}%")
                   ->orWhere('celular', 'like', "%{$q}%");
            });
        }

        $clients = $query
            ->select(['id', 'nome', 'celular', 'created_at'])
            ->orderBy('nome')
            ->paginate((int) $request->input('per_page', 15));

        return response()->json([
            'data' => $clients->items(),
            'meta' => [
                'total'        => $clients->total(),
                'current_page' => $clients->currentPage(),
                'last_page'    => $clients->lastPage(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'nome'    => ['required', 'string', 'max:255'],
            'celular' => ['required', 'string', 'regex:/^[1-9]{2}9?[0-9]{8}$/', 'unique:users,celular'],
        ], [
            'nome.required'    => 'O nome é obrigatório.',
            'celular.required' => 'O celular é obrigatório.',
            'celular.regex'    => 'Celular inválido.',
            'celular.unique'   => 'Este celular já está cadastrado.',
        ]);

        $user = User::create([
            'nome'    => $data['nome'],
            'celular' => $data['celular'],
            'role'    => 'cliente',
            'ativo'   => true,
        ]);

        return response()->json([
            'data' => [
                'id'      => $user->id,
                'nome'    => $user->nome,
                'celular' => $user->celular,
            ],
        ], 201);
    }
}
