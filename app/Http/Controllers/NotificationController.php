<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $notifications = $request->user()
            ->notifications()
            ->latest()
            ->paginate(20);

        return response()->json([
            'data' => $notifications->getCollection()->map(fn ($n) => [
                'id'       => $n->id,
                'tipo'     => $n->tipo,
                'titulo'   => $n->titulo,
                'mensagem' => $n->mensagem,
                'ref_type' => $n->ref_type,
                'ref_id'   => $n->ref_id,
                'lida'     => $n->lida,
                'created_at' => $n->created_at,
            ]),
            'meta' => [
                'current_page' => $notifications->currentPage(),
                'last_page'    => $notifications->lastPage(),
                'total'        => $notifications->total(),
                'unread'       => $request->user()->notifications()->where('lida', false)->count(),
            ],
        ]);
    }

    public function markRead(Request $request): JsonResponse
    {
        $request->validate([
            'ids'   => ['nullable', 'array'],
            'ids.*' => ['integer'],
        ]);

        $query = $request->user()->notifications()->where('lida', false);

        if ($request->filled('ids')) {
            $query->whereIn('id', $request->ids);
        }

        $query->update(['lida' => true]);

        return response()->json(['message' => 'Notificações marcadas como lidas.']);
    }

    public function subscribe(Request $request): JsonResponse
    {
        $request->validate([
            'subscription'           => ['required', 'array'],
            'subscription.endpoint'  => ['required', 'string', 'url'],
            'subscription.keys'      => ['required', 'array'],
            'subscription.keys.auth' => ['required', 'string'],
            'subscription.keys.p256dh' => ['required', 'string'],
        ]);

        $request->user()->update([
            'push_subscription' => $request->subscription,
        ]);

        return response()->json(['message' => 'Push subscription salva.']);
    }
}
